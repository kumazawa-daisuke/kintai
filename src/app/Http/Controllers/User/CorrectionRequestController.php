<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Models\CorrectionRequest;
use App\Http\Requests\CorrectionRequestRequest;

class CorrectionRequestController extends Controller
{
    // 申請一覧の表示
    public function index(Request $request)
    {
        $status = $request->input('status', 'pending');

        // ガード判定（同時ログインは前提としない）
        $isAdmin = Auth::guard('admin')->check();

        $query = CorrectionRequest::with(['user', 'attendance'])
            ->when($status, fn($q) => $q->where('status', $status));

        if ($isAdmin) {
            // 管理者 → 全件表示
        } else {
            // 一般ユーザー → 自分の申請のみ
            $query->where('user_id', Auth::guard('web')->id());
        }

        $requests = $query->orderByDesc('created_at')->get();

        return view('correction_request_index', [
            'requests' => $requests,
            'isAdmin' => $isAdmin,
            'status' => $status,
        ]);
    }
    // 申請の保存
    public function store(CorrectionRequestRequest $request)
    {
        $user = Auth::guard('web')->user();

        // 1. 日付情報取得
        $date = $request->date ?? null;

        // 2. 勤怠レコードが無ければ作成
        if ($request->filled('attendance_id')) {
            $attendance = \App\Models\Attendance::with('breakTimes')->find($request->attendance_id);
        } else {
            // 既に同じ日の勤怠があればそれを使う
            $attendance = \App\Models\Attendance::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'date' => $date,
                ],
                [
                    'clock_in' => null,
                    'clock_out' => null,
                    'status' => 'off',
                ]
            );
        }

        // 3. 休憩情報などはそのままでOK
        $breaks_before = $attendance->breakTimes->map(function($break) {
            return [
                'break_start' => $break->break_start,
                'break_end'   => $break->break_end,
            ];
        })->values()->all();

        // 4. 休憩データ(after)
        $breaks_after = [];
        if ($request->has('break_times')) {
            foreach ($request->input('break_times') as $break) {
                if (!empty($break['break_start']) || !empty($break['break_end'])) {
                    $breaks_after[] = [
                        'break_start' => $break['break_start'],
                        'break_end'   => $break['break_end'],
                    ];
                }
            }
        }

        // 5. CorrectionRequestの作成
        \App\Models\CorrectionRequest::create([
            'attendance_id'      => $attendance->id,
            'date'               => $attendance->date,
            'user_id'            => $user->id,
            'request_type'       => 'edit',
            'reason'             => $request->reason,
            'status'             => 'pending',
            'clock_in_before'    => $attendance->clock_in ?? null,
            'clock_in_after'     => $request->clock_in,
            'clock_out_before'   => $attendance->clock_out ?? null,
            'clock_out_after'    => $request->clock_out,
            'reason_before'        => $attendance->reason ?? null,
            'reason_after'         => $request->reason,
            'breaks_before'      => $breaks_before ? json_encode($breaks_before, JSON_UNESCAPED_UNICODE) : null,
            'breaks_after'       => $breaks_after ? json_encode($breaks_after, JSON_UNESCAPED_UNICODE) : null,
        ]);

        return redirect()->route('correction_request.index')->with('success', '修正申請を登録しました');
    }
}

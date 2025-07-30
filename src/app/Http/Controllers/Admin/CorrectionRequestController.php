<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CorrectionRequest;

class CorrectionRequestController extends Controller
{
    // 申請承認処理例
    public function approve(CorrectionRequest $attendance_correct_request)
    {
        $request = $attendance_correct_request;

        // 1. Attendance本体を取得
        $attendance = $request->attendance;

        // ★ ここでattendanceがなければ新規作成
        if (!$attendance) {
            $attendance = new \App\Models\Attendance();
            $attendance->user_id = $request->user_id;
            $attendance->date = $request->date;
        }

        // 1. 出勤・退勤・備考 上書き
        if ($request->clock_in_after) {
            $attendance->clock_in = $request->clock_in_after;
        }
        if ($request->clock_out_after) {
            $attendance->clock_out = $request->clock_out_after;
        }
        if (!empty($request->reason_after)) {
            $attendance->reason = $request->reason_after;
        } elseif (!empty($request->reason)) {
            $attendance->reason = $request->reason;
        }
        $attendance->status = 'finished'; // 任意のデフォルトステータス

        // 2. 休憩時間も上書き
        $breaks = [];
        if ($request->breaks_after) {
            $attendance->save(); // attendance_idを取得するため先に保存
            $attendance->breakTimes()->delete();
            $breaks = json_decode($request->breaks_after, true);
            foreach ($breaks as $break) {
                if (!empty($break['break_start']) || !empty($break['break_end'])) {
                    $attendance->breakTimes()->create([
                        'break_start' => $break['break_start'] ?? null,
                        'break_end'   => $break['break_end'] ?? null,
                    ]);
                }
            }
        } else {
            $attendance->save(); // breakTimes()が使えるように保存
            $attendance->breakTimes()->delete();
        }

        // 3. 合計時間
        $totalBreakMinutes = 0;
        foreach ($breaks as $break) {
            if (!empty($break['break_start']) && !empty($break['break_end'])) {
                $start = \Carbon\Carbon::parse($break['break_start']);
                $end   = \Carbon\Carbon::parse($break['break_end']);
                $totalBreakMinutes += $end->diffInMinutes($start);
            }
        }
        $attendance->break_time = sprintf('%02d:%02d', floor($totalBreakMinutes / 60), $totalBreakMinutes % 60);

        if ($attendance->clock_in && $attendance->clock_out) {
            $workMinutes = \Carbon\Carbon::parse($attendance->clock_out)
                ->diffInMinutes(\Carbon\Carbon::parse($attendance->clock_in));
            $realWork = $workMinutes - $totalBreakMinutes;
            $attendance->total_time = sprintf('%02d:%02d', floor($realWork / 60), $realWork % 60);
        } else {
            $attendance->total_time = null;
        }

        $attendance->save();

        if (!$request->attendance_id) {
            $request->attendance_id = $attendance->id;
        }

        $request->status = 'approved';
        $request->save();

        return redirect()->route('correction_request.index', ['status' => 'pending'])
            ->with('success', '承認しました');
    }

    public function show($id)
    {
        $request = CorrectionRequest::with(['user', 'attendance'])->findOrFail($id);
        return view('admin_requests_show', compact('request'));
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\CorrectionRequestRequest;
use Carbon\Carbon;
use App\Models\Attendance;
use App\Models\User;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->input('date') ? Carbon::parse($request->input('date')) : Carbon::today();

        $attendances = Attendance::with(['user', 'breakTimes'])
            ->whereDate('date', $date->format('Y-m-d'))
            ->whereNotNull('clock_in')
            ->whereNotNull('clock_out')
            ->get();

        return view('admin_attendance_index', compact('attendances', 'date'));
    }

    public function show($id, Request $request)
    {
        $user_id = $request->input('user_id');
        $date = $request->input('date');

        // 勤怠データ取得
        if ($id != 0) {
            $attendance = Attendance::with('user', 'breakTimes')->findOrFail($id);
            // idから勤怠データ取得、userはリレーションでOK
            $user = $attendance->user;
        } else {
            // 新規、まだ勤怠データなし
            $attendance = new Attendance([
                'user_id' => $user_id,
                'date' => $date,
                'clock_in' => null,
                'clock_out' => null,
                'reason' => null,
            ]);
            // ユーザー取得
            $user = User::find($user_id);
        }

        return view('admin_attendance_show', [
            'attendance' => $attendance,
            'user' => $user,
            'date' => $date,
        ]);
    }

    public function update(CorrectionRequestRequest $request, $id)
    {
        $attendance = Attendance::findOrFail($id);

        // 勤怠データ自体を更新
        $attendance->update([
            'clock_in' => $request->input('clock_in'),
            'clock_out' => $request->input('clock_out'),
            'reason' => $request->input('reason'), 
        ]);

        // --- 休憩時間の更新 ---
        // 既存のbreak_timesを全削除
        $attendance->breakTimes()->delete();

        // 新しくリクエストから受け取った休憩データをinsert
        if ($request->has('break_times')) {
            foreach ($request->input('break_times') as $break) {
                if (!empty($break['break_start']) && !empty($break['break_end'])) {
                    $attendance->breakTimes()->create([
                        'break_start' => $break['break_start'],
                        'break_end'   => $break['break_end'],
                    ]);
                }
            }
        }

        $attendance->load('breakTimes');
        $attendance->recalculateTimes();
        $attendance->save();

        return redirect()->route('admin.attendances.show', $attendance->id)
                        ->with('success', '勤怠データを更新しました');
    }

    public function store(CorrectionRequestRequest $request)
    {
        $attendance = new Attendance([
            'user_id'   => $request->input('user_id'),
            'date'      => $request->input('date'),
            'clock_in'  => $request->input('clock_in'),
            'clock_out' => $request->input('clock_out'),
            'reason'    => $request->input('reason'),
            'status'    => 'finished',
        ]);
        $attendance->save();

        if ($request->has('break_times')) {
            foreach ($request->input('break_times') as $break) {
                if (!empty($break['break_start']) && !empty($break['break_end'])) {
                    $attendance->breakTimes()->create([
                        'break_start' => $break['break_start'],
                        'break_end'   => $break['break_end'],
                    ]);
                }
            }
        }

        $attendance->load('breakTimes');
        $attendance->recalculateTimes();
        $attendance->save();

        return redirect()->route('admin.attendances.show', $attendance->id)
                        ->with('success', '新規勤怠を登録しました');
    }
}

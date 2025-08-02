<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    // 出勤登録画面の表示
    public function index()
    {
        $user = Auth::user();

        // 今日の勤怠レコードを取得（なければstatus=off）
        $today = Carbon::today()->toDateString();
        $attendance = Attendance::where('user_id', $user->id)->where('date', $today)->first();

        // ステータス判定
        if (!$attendance) {
            $status = 'off';
        } else {
            $status = $attendance->status; // working, break, finished
        }

        // 現在時刻
        $currentTime = Carbon::now()->format('H:i');

        return view('attendance_create', [
            'status' => $status,
            'currentTime' => $currentTime,
        ]);
    }

    // 出勤
    public function start(Request $request)
    {
        $user = Auth::user();
        $today = Carbon::today()->toDateString();

        // 既に今日の勤怠レコードがあるか判定
        $alreadyExists = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->exists();

        if ($alreadyExists) {
            // すでに出勤している場合、エラーを返す
            return redirect()->route('attendance.index')
                ->with('error', '本日はすでに出勤しています。');
        }

        // 出勤（初回だけ実行）
        Attendance::create([
            'user_id' => $user->id,
            'date' => $today,
            'clock_in' => Carbon::now()->format('H:i'),
            'status' => 'working',
        ]);

        return redirect()->route('attendance.index')
            ->with('success', '出勤しました。');
    }

    // 退勤
    public function finish(Request $request)
    {
        $user = Auth::user();
        $today = Carbon::today()->toDateString();
        $attendance = Attendance::where('user_id', $user->id)->where('date', $today)->first();
        if ($attendance && !$attendance->clock_out) {
            $attendance->status = 'finished';
            $attendance->clock_out = Carbon::now()->format('H:i');
            $attendance->recalculateTimes();
            $attendance->save();

            return redirect()->route('attendance.index')->with('success', '退勤しました。');
        }
        // すでに退勤済みの場合
        return redirect()->route('attendance.index')->with('error', '本日はすでに退勤しています。');
    }

    // 休憩入
    public function breakIn(Request $request)
    {
        $user = Auth::user();
        $today = Carbon::today()->toDateString();
        $attendance = Attendance::where('user_id', $user->id)->where('date', $today)->first();

        if ($attendance) {
            // 休憩中レコードが未完了（endがnull）のものがあれば新規作成しない
            $unfinishedBreak = $attendance->breakTimes()->whereNull('break_end')->first();
            if ($unfinishedBreak) {
                return redirect()->route('attendance.index')->with('error', '未完了の休憩があります');
            }
            // 新しい休憩開始
            BreakTime::create([
                'attendance_id' => $attendance->id,
                'break_start' => Carbon::now()->format('H:i'),
            ]);
            $attendance->status = 'break';
            $attendance->save();
        }

        return redirect()->route('attendance.index');
    }

    // 休憩戻
    public function breakOut(Request $request)
    {
        $user = Auth::user();
        $today = Carbon::today()->toDateString();
        $attendance = Attendance::where('user_id', $user->id)->where('date', $today)->first();

        if ($attendance) {
            // 未完了の休憩（endがnull）の最新を取得
            $unfinishedBreak = $attendance->breakTimes()->whereNull('break_end')->latest()->first();
            if ($unfinishedBreak) {
                $unfinishedBreak->break_end = Carbon::now()->format('H:i');
                $unfinishedBreak->save();
            }
            $attendance->status = 'working';
            $attendance->recalculateTimes();
            $attendance->save();
        }
        return redirect()->route('attendance.index');
    }

    public function list(Request $request)
    {
        $user = Auth::user();

        // 表示したい月を決定
        $yearMonth = $request->input('month', now()->format('Y-m'));
        [$year, $month] = explode('-', $yearMonth);

        $firstDay = Carbon::create($year, $month, 1);
        $lastDay = $firstDay->copy()->endOfMonth();

        // その月の全日付リストを生成
        $dates = [];
        $current = $firstDay->copy();
        while ($current->lte($lastDay)) {
            $dates[] = $current->copy();
            $current->addDay();
        }

        // 勤怠データを取得（dateをキーに）
        $attendances = Attendance::where('user_id', $user->id)
            ->whereBetween('date', [$firstDay->toDateString(), $lastDay->toDateString()])
            ->where('status', '!=', 'off')
            ->with('breakTimes')
            ->get()
            ->keyBy('date');

        // Blade用に整形データを作成
        $records = [];
        foreach ($dates as $date) {
            $attendance = $attendances->get($date->toDateString());

            // clock_in, clock_outもbladeで直接書けるなら不要。どうしても変数で渡したいときは下記
            $clockIn = $attendance && $attendance->clock_in
                ? Carbon::parse($attendance->clock_in)->format('H:i')
                : '';
            $clockOut = $attendance && $attendance->clock_out
                ? Carbon::parse($attendance->clock_out)->format('H:i')
                : '';

            $records[] = [
                'date'        => $date,
                'attendance'  => $attendance,
                'clock_in'    => $clockIn,
                'clock_out'   => $clockOut,
                // 'break_sum'や'work_sum'は不要！モデルのアクセサで呼ぶ
            ];
        }

        $currentMonth = $yearMonth;

        return view('attendance_index', compact('records', 'currentMonth'));
    }

    public function show($id, Request $request)
    {
        $user = Auth::user();

        if ($id != 0) {
            $attendance = Attendance::with(['user', 'breakTimes', 'correctionRequests'])->where('user_id', $user->id)->findOrFail($id);
        } else {
            $date = $request->input('date');
            $attendance = new Attendance([
                'user_id' => $user->id,
                'date' => $date,
                'clock_in' => null,
                'clock_out' => null,
                'reason' => null,
            ]);
            $attendance->setRelation('breakTimes', collect());
            $attendance->setRelation('correctionRequests', collect());
            $attendance->setRelation('user', $user);
        }

        return view('attendance_show', compact('attendance'));
    }
}

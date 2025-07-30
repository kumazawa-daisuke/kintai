<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Attendance;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Response;

class UserController extends Controller
{
    // スタッフ一覧（管理者用）
    public function index()
    {
        // 全ユーザーを取得（必要に応じて条件追加）
        $users = User::orderBy('name')->get();

        // 管理者用bladeに渡す
        return view('admin_users_index', compact('users'));
    }

    // スタッフ別勤怠一覧（管理者用）    
    public function attendances(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $currentMonth = $request->input('month', Carbon::now()->format('Y-m'));
        $start = Carbon::parse($currentMonth . '-01');
        $end = $start->copy()->endOfMonth();

        // 月の日付配列を作成
        $monthDates = [];
        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            $monthDates[] = $date->copy();
        }

        // 勤怠データ取得（休憩時間含む）
        $attendances = Attendance::with('breakTimes')
            ->where('user_id', $user->id)
            ->whereBetween('date', [$start->format('Y-m-d'), $end->format('Y-m-d')])
            ->get();

        // 勤怠ごとに再計算と表示用整形を実施
        foreach ($attendances as $a) {
            $a->recalculateTimes();

            $a->break_sum = !empty($a->break_time)
                ? Carbon::createFromFormat('H:i', $a->break_time)->format('G:i')
                : '';

            $a->work_sum = !empty($a->total_time)
                ? Carbon::createFromFormat('H:i', $a->total_time)->format('G:i')
                : '';
        }

        $prevMonth = $start->copy()->subMonth()->format('Y-m');
        $nextMonth = $start->copy()->addMonth()->format('Y-m');

        // CSV出力処理
        if ($request->has('csv')) {
            $filename = $user->name . '_' . $currentMonth . '_attendance.csv';
            $csvHeader = ['日付', '出勤', '退勤', '休憩', '合計'];
            $csvData = [];

            foreach ($monthDates as $date) {
                $att = $attendances->firstWhere('date', $date->format('Y-m-d'));
                $csvData[] = [
                    $date->format('Y/m/d') . '(' . ['日','月','火','水','木','金','土'][$date->dayOfWeek] . ')',
                    $att && $att->clock_in ? Carbon::parse($att->clock_in)->format('H:i') : '',
                    $att && $att->clock_out ? Carbon::parse($att->clock_out)->format('H:i') : '',
                    $att && !empty($att->break_time) ? Carbon::createFromFormat('H:i', $att->break_time)->format('G:i') : '',
                    $att && !empty($att->total_time) ? Carbon::createFromFormat('H:i', $att->total_time)->format('G:i') : '',
                ];
            }

            $callback = function () use ($csvHeader, $csvData) {
                $file = fopen('php://output', 'w');
                fwrite($file, "\xEF\xBB\xBF"); // BOM for Excel
                fputcsv($file, $csvHeader);
                foreach ($csvData as $row) {
                    fputcsv($file, $row);
                }
                fclose($file);
            };

            return Response::stream($callback, 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"$filename\"",
            ]);
        }

        return view('admin_users_attendance', [
            'user'         => $user,
            'attendances'  => $attendances,
            'monthDates'   => $monthDates,
            'currentMonth' => $currentMonth,
            'prevMonth'    => $prevMonth,
            'nextMonth'    => $nextMonth,
        ]);
    }
}

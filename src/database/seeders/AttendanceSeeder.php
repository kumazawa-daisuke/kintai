<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;
use App\Models\User;

class AttendanceSeeder extends Seeder
{
    public function run()
    {
        $userIds = User::pluck('id')->toArray();

        // 7月・9月の全日付 + 8月25～31日
        $period = [];

        // 7月
        $start = Carbon::parse('2025-07-01');
        $end = Carbon::parse('2025-07-31');
        while ($start <= $end) {
            $period[] = $start->copy();
            $start->addDay();
        }

        // 9月
        $start = Carbon::parse('2025-09-01');
        $end = Carbon::parse('2025-09-30');
        while ($start <= $end) {
            $period[] = $start->copy();
            $start->addDay();
        }

        // 8月25日～31日
        $start = Carbon::parse('2025-08-25');
        $end = Carbon::parse('2025-08-31');
        while ($start <= $end) {
            $period[] = $start->copy();
            $start->addDay();
        }

        foreach ($userIds as $userId) {
            // 月ごとに8日分ランダムで休みを決める（8月は除外）
            $holidays = [];
            foreach ([7, 9] as $m) {
                $daysInMonth = Carbon::create(2025, $m, 1)->daysInMonth;
                $monthDays = range(1, $daysInMonth);
                shuffle($monthDays);
                $holidays[$m] = array_slice($monthDays, 0, 8); // 先頭8日を休み
            }

            foreach ($period as $date) {
                $month = intval($date->format('m'));
                $day = intval($date->format('d'));

                // 7,9月は休み判定、8月25-31は常に出勤
                if (($month === 7 || $month === 9) && in_array($day, $holidays[$month])) {
                    continue;
                }

                $clockIn = Carbon::parse($date->toDateString() . ' 09:00');
                $clockOut = Carbon::parse($date->toDateString() . ' 18:00');
                $totalMinutes = $clockOut->diffInMinutes($clockIn);

                // 休憩回数を決定（0～2回）
                $breakCount = rand(0, 2);
                $breakTotalMinutes = $breakCount * 30;
                $totalTime = sprintf('%02d:%02d', floor(($totalMinutes - $breakTotalMinutes) / 60), ($totalMinutes - $breakTotalMinutes) % 60);

                // 勤怠レコード作成
                $attendance = Attendance::create([
                    'user_id'    => $userId,
                    'date'       => $date->toDateString(),
                    'clock_in'   => $clockIn->format('H:i'),
                    'clock_out'  => $clockOut->format('H:i'),
                    'total_time' => $totalTime,
                    'status'     => 'finished'
                ]);

                // 休憩を作成（最大2回、1回30分、ランダムに時刻を割り振る）
                if ($breakCount > 0) {
                    $breakStarts = [];
                    if ($breakCount === 1) {
                        // 休憩1回 → 12:00固定
                        $breakStarts[] = $clockIn->copy()->addHours(3); // 09:00 + 3h = 12:00
                    } else {
                        // 休憩2回 → 11:00と15:00
                        $breakStarts[] = $clockIn->copy()->addHours(2); // 09:00 + 2h = 11:00
                        $breakStarts[] = $clockIn->copy()->addHours(6); // 09:00 + 6h = 15:00
                    }

                    foreach ($breakStarts as $start) {
                        $breakEnd = $start->copy()->addMinutes(30);
                        BreakTime::create([
                            'attendance_id' => $attendance->id,
                            'break_start'   => $start->format('H:i'),
                            'break_end'     => $breakEnd->format('H:i'),
                        ]);
                    }
                }
            }
        }
    }
}

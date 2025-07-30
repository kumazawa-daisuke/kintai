<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;

class AttendanceSeeder extends Seeder
{
    public function run()
    {
        $faker = \Faker\Factory::create('ja_JP');

        // 件数を20に増やす
        for ($i = 1; $i <= 20; $i++) {
            foreach ([1, 2] as $userId) {
                // 日付は7月1日～7月20日
                $date = Carbon::parse("2025-07-01")->addDays($i - 1);

                // 出勤・退勤時間を日ごとにランダム生成
                $clockIn = (clone $date)->setTime(rand(8,10), [0,15,30,45][rand(0,3)]);
                $workHours = rand(7,9); // 7〜9時間勤務
                $clockOut = (clone $clockIn)->copy()->addHours($workHours);

                // 休憩回数を1〜3回でランダム
                $breakCount = rand(1, 3);
                $breakTotalMinutes = 0;
                $breaks = [];

                // 勤務時間の1/3〜1/2ぐらいのタイミングで休憩を挿入
                $breakStart = (clone $clockIn)->addMinutes(rand(90,120));
                for ($b = 1; $b <= $breakCount; $b++) {
                    // 各休憩の長さもばらつきを
                    $breakMinutes = rand(10, 50);
                    $breakEnd = (clone $breakStart)->addMinutes($breakMinutes);

                    // 勤務時間内なら追加
                    if ($breakEnd < $clockOut) {
                        $breakTotalMinutes += $breakMinutes;
                        $breaks[] = [
                            'break_start' => $breakStart->format('H:i'),
                            'break_end'   => $breakEnd->format('H:i'),
                        ];
                        // 次の休憩は+1〜2時間後
                        $breakStart = (clone $breakEnd)->addMinutes(rand(60,120));
                    }
                }

                $totalMinutes = $clockOut->diffInMinutes($clockIn) - $breakTotalMinutes;
                $totalTime = sprintf('%02d:%02d', floor($totalMinutes / 60), $totalMinutes % 60);

                $attendance = Attendance::create([
                    'user_id'    => $userId,
                    'date'       => $date->toDateString(),
                    'clock_in'   => $clockIn->format('H:i'),
                    'clock_out'  => $clockOut->format('H:i'),
                    'total_time' => $totalTime,
                    'status'     => 'finished'
                ]);

                // break_times登録
                foreach ($breaks as $break) {
                    BreakTime::create([
                        'attendance_id' => $attendance->id,
                        'break_start'   => $break['break_start'],
                        'break_end'     => $break['break_end'],
                    ]);
                }
            }
        }
    }
}

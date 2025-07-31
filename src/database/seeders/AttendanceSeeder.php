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
        $faker = \Faker\Factory::create('ja_JP');

        $userIds = User::pluck('id')->toArray();

        foreach ($userIds as $userId) {
            for ($i = 1; $i <= 20; $i++) {
                $date = Carbon::parse("2025-07-01")->addDays($i - 1);
                $clockIn = (clone $date)->setTime(rand(8, 10), [0, 15, 30, 45][rand(0, 3)]);
                $workHours = rand(7, 9);
                $clockOut = (clone $clockIn)->copy()->addHours($workHours);

                $breakCount = rand(1, 3);
                $breakTotalMinutes = 0;
                $breaks = [];

                $breakStart = (clone $clockIn)->addMinutes(rand(90, 120));
                for ($b = 1; $b <= $breakCount; $b++) {
                    $breakMinutes = rand(10, 50);
                    $breakEnd = (clone $breakStart)->addMinutes($breakMinutes);
                    if ($breakEnd < $clockOut) {
                        $breakTotalMinutes += $breakMinutes;
                        $breaks[] = [
                            'break_start' => $breakStart->format('H:i'),
                            'break_end'   => $breakEnd->format('H:i'),
                        ];
                        $breakStart = (clone $breakEnd)->addMinutes(rand(60, 120));
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

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CorrectionRequest;
use App\Models\User;
use App\Models\Attendance;

class CorrectionRequestSeeder extends Seeder
{
    public function run()
    {
        $user = User::first(); // 適当なユーザーを1人取得
        $attendance = Attendance::first(); // 適当な勤怠を1件取得

        CorrectionRequest::create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'request_type' => 'clock_in',
            'before_value' => '09:00',
            'after_value' => '09:30',
            'reason' => '寝坊のため',
            'status' => 'pending',
            'approved_by' => null,
        ]);

        CorrectionRequest::create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'request_type' => 'clock_out',
            'before_value' => '18:00',
            'after_value' => '19:00',
            'reason' => '残業申請',
            'status' => 'approved',
            'approved_by' => $user->id,
        ]);
    }
}

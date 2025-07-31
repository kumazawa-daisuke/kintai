<?php

namespace Database\Factories;

use App\Models\CorrectionRequest;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Database\Eloquent\Factories\Factory;

class CorrectionRequestFactory extends Factory
{
    protected $model = CorrectionRequest::class;

    public function definition()
    {
        // 既存のUserとAttendanceを利用
        $user = User::inRandomOrder()->first() ?? User::factory()->create();
        $attendance = Attendance::where('user_id', $user->id)->inRandomOrder()->first()
            ?? Attendance::factory()->create(['user_id' => $user->id]);

        return [
            'attendance_id'      => $attendance->id,
            'user_id'            => $user->id,
            'date'               => $attendance->date,
            'request_type'       => 'edit',
            'reason'             => $this->faker->sentence(3),
            'status'             => 'pending',
            'approved_by'        => null,
            'clock_in_before'    => $attendance->clock_in,
            'clock_in_after'     => $this->faker->time('H:i'),
            'clock_out_before'   => $attendance->clock_out,
            'clock_out_after'    => $this->faker->time('H:i'),
            'breaks_before'      => json_encode([]),
            'breaks_after'       => json_encode([]),
        ];
    }
}

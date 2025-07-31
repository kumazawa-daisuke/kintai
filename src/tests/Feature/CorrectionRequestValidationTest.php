<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CorrectionRequestValidationTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $attendance;

    public function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate:fresh --seed');
        $this->user = User::first();
        $this->user->markEmailAsVerified();
        $this->attendance = Attendance::where('user_id', $this->user->id)->first();
    }

    /** @test */
    public function 修正申請時_理由が未入力の場合_バリデーションエラーになる()
    {
        $response = $this->actingAs($this->user)->post('/stamp_correction_request/list', [
            'attendance_id' => $this->attendance->id,
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'reason' => '',
        ]);
        $response->assertSessionHasErrors('reason');
    }

    /** @test */
    public function 修正申請時_出勤時刻が不正な場合_バリデーションエラーになる()
    {
        $response = $this->actingAs($this->user)->post('/stamp_correction_request/list', [
            'attendance_id' => $this->attendance->id,
            'clock_in' => '25:00', // 不正な値
            'clock_out' => '18:00',
            'reason' => 'テスト理由',
        ]);
        $response->assertSessionHasErrors('clock_in');
    }

    /** @test */
    public function 修正申請時_退勤時刻が不正な場合_バリデーションエラーになる()
    {
        $response = $this->actingAs($this->user)->post('/stamp_correction_request/list', [
            'attendance_id' => $this->attendance->id,
            'clock_in' => '09:00',
            'clock_out' => '99:99', // 不正な値
            'reason' => 'テスト理由',
        ]);
        $response->assertSessionHasErrors('clock_out');
    }

    /** @test */
    public function 修正申請時_正しいデータなら申請できる()
    {
        $response = $this->actingAs($this->user)->post('/stamp_correction_request/list', [
            'attendance_id' => $this->attendance->id,
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'reason' => '定時で退勤',
        ]);
        $response->assertRedirect(route('correction_request.index'));

        $this->assertDatabaseHas('correction_requests', [
            'user_id' => $this->user->id,
            'attendance_id' => $this->attendance->id,
            'reason' => '定時で退勤',
        ]);
    }
}

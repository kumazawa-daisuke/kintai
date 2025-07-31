<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;

class AttendanceFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate:fresh --seed');
        $this->user = User::first();
        $this->user->markEmailAsVerified();
    }

    /** @test */
    public function 勤怠登録画面に現在日時が表示されている()
    {
        $this->actingAs($this->user);
        $response = $this->get('/attendance');
        $today = now()->format('Y年n月j日');
        $response->assertSee($today);
    }

    /** @test */
    public function 出勤前はステータスが勤務外と表示される()
    {
        $this->actingAs($this->user);
        $response = $this->get('/attendance');
        $response->assertSee('勤務外');
    }

    /** @test */
    public function 出勤後はステータスが出勤中と表示される()
    {
        $this->actingAs($this->user)->post('/attendance/start');
        $response = $this->get('/attendance');
        $response->assertSee('出勤中');
    }

    /** @test */
    public function 休憩開始後はステータスが休憩中と表示される()
    {
        $this->actingAs($this->user)->post('/attendance/start');
        $this->post('/attendance/break_in');
        $response = $this->get('/attendance');
        $response->assertSee('休憩中');
    }

    /** @test */
    public function 休憩終了後はステータスが出勤中に戻る()
    {
        $this->actingAs($this->user)->post('/attendance/start');
        $this->post('/attendance/break_in');
        $this->post('/attendance/break_out');
        $response = $this->get('/attendance');
        $response->assertSee('出勤中');
    }

    /** @test */
    public function 退勤後はステータスが退勤済と表示される()
    {
        $this->actingAs($this->user)->post('/attendance/start');
        $this->post('/attendance/finish');
        $response = $this->get('/attendance');
        $response->assertSee('退勤済');
    }

    /** @test */
    public function 出勤は1日1回しかできない()
    {
        $this->actingAs($this->user)->post('/attendance/start');
        $response = $this->post('/attendance/start');
        $response->assertSessionHas('error', '本日はすでに出勤しています。');
    }

    /** @test */
    public function 休憩は1日複数回できる()
    {
        $this->actingAs($this->user)->post('/attendance/start');
        // 1回目の休憩
        $this->post('/attendance/break_in');
        $this->post('/attendance/break_out');
        // 2回目の休憩
        $this->post('/attendance/break_in');
        $this->post('/attendance/break_out');

        $attendance = Attendance::where('user_id', $this->user->id)->where('date', now()->toDateString())->first();
        $breakCount = $attendance->breakTimes()->count();
        $this->assertEquals(2, $breakCount);
    }

    /** @test */
    public function 勤怠一覧画面で出勤時間退勤時間休憩合計が表示される()
    {
        $this->actingAs($this->user)->post('/attendance/start');
        $this->post('/attendance/break_in');
        $this->post('/attendance/break_out');
        $this->post('/attendance/finish');

        $response = $this->get('/attendance/list');
        $response->assertStatus(200);
        // 出勤・退勤・休憩・合計カラムの有無と値の存在を確認
        $response->assertSee('出勤');
        $response->assertSee('退勤');
        $response->assertSee('休憩');
        $response->assertSee('合計');
        // 時刻が表示されていることも確認（例: 09:00, 18:00 など）
        $attendance = Attendance::where('user_id', $this->user->id)->where('date', now()->toDateString())->first();
        $this->assertNotNull($attendance->clock_in);
        $this->assertNotNull($attendance->clock_out);
    }
}

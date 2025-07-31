<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class AttendanceListTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    public function setUp(): void
    {
        parent::setUp();

        // マイグレーション＆シーディング
        $this->artisan('migrate:fresh --seed');
        $this->user = User::first();
        $this->user->markEmailAsVerified();
    }

    /** @test */
    public function 勤怠一覧で自身の勤怠情報がすべて表示される()
    {
        $attendances = Attendance::where('user_id', $this->user->id)->take(3)->get();
        $response = $this->actingAs($this->user)->get('/attendance/list');

        $response->assertStatus(200);

        foreach ($attendances as $attendance) {
            $response->assertSee((new Carbon($attendance->date))->format('m/d'));
            $response->assertSee(substr($attendance->clock_in, 0, 5));
            $response->assertSee(substr($attendance->clock_out, 0, 5));
        }
    }

    /** @test */
    public function 勤怠一覧から詳細画面に遷移できて内容も正しい()
    {
        $attendance = Attendance::where('user_id', $this->user->id)->first();

        // 詳細ボタンのURL（一覧画面上でリンクが出ているかも同時に検証）
        $listResponse = $this->actingAs($this->user)->get('/attendance/list');
        $listResponse->assertSee('/attendance/' . $attendance->id);

        // 詳細画面で内容を確認
        $detailResponse = $this->actingAs($this->user)
            ->get('/attendance/' . $attendance->id . '?date=' . $attendance->date);

        $detailResponse->assertStatus(200);
        $detailResponse->assertSee($this->user->name);
        $detailResponse->assertSee((new Carbon($attendance->date))->format('Y-m-d'));
        $detailResponse->assertSee(substr($attendance->clock_in, 0, 5));
        $detailResponse->assertSee(substr($attendance->clock_out, 0, 5));
    }

    /** @test */
    public function 勤怠一覧で前月が表示される()
    {
        $prevMonth = Carbon::now()->subMonth();
        $response = $this->actingAs($this->user)
            ->get('/attendance/list?month=' . $prevMonth->format('Y-m'));
        $response->assertStatus(200);
        $response->assertSee($prevMonth->format('Y/m'));
    }

    /** @test */
    public function 勤怠一覧で翌月が表示される()
    {
        $nextMonth = Carbon::now()->addMonth();
        $response = $this->actingAs($this->user)
            ->get('/attendance/list?month=' . $nextMonth->format('Y-m'));
        $response->assertStatus(200);
        $response->assertSee($nextMonth->format('Y/m'));
    }
}

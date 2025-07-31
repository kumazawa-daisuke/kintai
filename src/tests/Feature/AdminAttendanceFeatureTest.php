<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Admin;
use App\Models\Attendance;
use App\Models\CorrectionRequest;

class AdminAttendanceFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // 必ずここで全シーディング
        $this->seed();

        // 共通管理者（admin@example.com想定）
        $this->admin = Admin::where('email', 'admin@example.com')->first();
        $this->assertNotNull($this->admin, '管理者が見つかりません');
    }

    /** @test */
    public function 管理者は勤怠一覧画面を表示できる()
    {
        $response = $this->actingAs($this->admin, 'admin')->get('/admin/attendance/list');
        $response->assertStatus(200);
        $response->assertSee('勤怠一覧'); // タイトルや目印
    }

    /** @test */
    public function 管理者は勤怠詳細画面を表示できる()
    {
        $attendance = Attendance::first();
        $this->assertNotNull($attendance, '勤怠データが見つかりません');

        $response = $this->actingAs($this->admin, 'admin')->get('/admin/attendance/' . $attendance->id);
        $response->assertStatus(200);
        $response->assertSee($attendance->date);
    }

    /** @test */
    public function 管理者は勤怠情報を編集できる()
    {
        $attendance = Attendance::first();
        $this->assertNotNull($attendance, '勤怠データが見つかりません');

        $data = [
            'clock_in' => '10:00',
            'clock_out' => '19:00',
            'status' => 'finished',
            'reason' => '編集テスト',
            // 他必要なカラムがあれば追記
        ];

        $response = $this->actingAs($this->admin, 'admin')->put('/admin/attendance/' . $attendance->id, $data);

        $response->assertRedirect(); // 正常リダイレクト
        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'clock_in' => '10:00:00',
            'clock_out' => '19:00:00',
            'reason' => '編集テスト',
        ]);
    }

    /** @test */
    public function 管理者は修正申請を承認でき勤怠に反映される()
    {
        // CorrectionRequestFactoryで1件作成（pending状態）
        $request = CorrectionRequest::factory()->create([
            'status' => 'pending',
            'clock_in_after' => '09:30:00',
            'clock_out_after' => '18:30:00',
        ]);

        $attendance = $request->attendance;
        $this->assertNotNull($attendance);

        // 申請承認（POST）
        $response = $this->actingAs($this->admin, 'admin')->post('/admin/stamp_correction_request/approve/' . $request->id);

        $response->assertRedirect();

        // 勤怠が修正申請内容で更新されていること
        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'clock_in' => '09:30:00',
            'clock_out' => '18:30:00',
        ]);

        // 申請が承認済み
        $this->assertDatabaseHas('correction_requests', [
            'id' => $request->id,
            'status' => 'approved',
        ]);
    }
}

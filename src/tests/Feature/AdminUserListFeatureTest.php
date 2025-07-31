<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Admin;
use App\Models\User;

class AdminUserListFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->admin = Admin::first();
    }

    /** @test */
    public function 管理者はユーザー一覧を閲覧できる()
    {
        $response = $this->actingAs($this->admin, 'admin')->get('/admin/staff/list');
        $response->assertStatus(200);
        $response->assertSee('名前');
        $response->assertSee('メールアドレス');
    }

    /** @test */
    public function 管理者は特定ユーザーの勤怠一覧を閲覧できる()
    {
        $user = User::first();
        $response = $this->actingAs($this->admin, 'admin')->get('/admin/attendance/staff/' . $user->id);
        $response->assertStatus(200);
        $response->assertSee($user->name);
    }
}

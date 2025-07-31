<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

class AdminLoginTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 管理者ログイン画面が表示される()
    {
        $response = $this->get('/admin/login');
        $response->assertStatus(200);
        $response->assertSee('ログイン');
    }

    /** @test */
    public function 正しい情報を入力すれば管理者ログインできる()
    {
        $admin = Admin::create([
            'name' => '管理者',
            'email' => 'admin@example.com',
            'password' => Hash::make('adminpass'),
        ]);

        $response = $this->post('/admin/login', [
            'email' => 'admin@example.com',
            'password' => 'adminpass',
        ]);

        $response->assertRedirect('/admin/attendance/list');
        $this->assertAuthenticated('admin');
    }

    /** @test */
    public function パスワードが誤っている場合_認証エラーになる()
    {
        $admin = Admin::create([
            'name' => '管理者',
            'email' => 'admin@example.com',
            'password' => Hash::make('adminpass'),
        ]);

        $response = $this->post('/admin/login', [
            'email' => 'admin@example.com',
            'password' => 'wrongpass',
        ]);

        // 認証失敗時、emailにエラーが入るのが一般的
        $response->assertSessionHasErrors(['email']);
        $this->assertGuest('admin');
    }

    /** @test */
    public function メールアドレスが未入力の場合_バリデーションエラーが出る()
    {
        $response = $this->post('/admin/login', [
            'email' => '',
            'password' => 'adminpass'
        ]);
        $response->assertSessionHasErrors('email');
    }

    /** @test */
    public function パスワードが未入力の場合_バリデーションエラーが出る()
    {
        $response = $this->post('/admin/login', [
            'email' => 'admin@example.com',
            'password' => ''
        ]);
        $response->assertSessionHasErrors('password');
    }

    /** @test */
    public function パスワードが8文字未満の場合_認証エラーになる()
    {
        $admin = Admin::create([
            'name' => '管理者',
            'email' => 'admin@example.com',
            'password' => Hash::make('adminpass'),
        ]);

        $response = $this->post('/admin/login', [
            'email' => 'admin@example.com',
            'password' => '1234567', // 8文字未満
        ]);

        // 認証失敗時、emailにエラーが入るのが一般的
        $response->assertSessionHasErrors(['email']);
        $this->assertGuest('admin');
    }
}

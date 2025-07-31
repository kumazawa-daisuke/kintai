<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function メールアドレスが未入力の場合_バリデーションエラーが出る()
    {
        $response = $this->post('/login', [
            'email' => '',
            'password' => 'password123'
        ]);

        $response->assertSessionHasErrors('email');
    }

    /** @test */
    public function パスワードが未入力の場合_バリデーションエラーが出る()
    {
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => ''
        ]);

        $response->assertSessionHasErrors('password');
    }

    /** @test */
    public function 登録情報と一致しない場合_認証エラーになる()
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('correct_password')
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'wrong_password'
        ]);

        $response->assertSessionHasErrors([
            'email' => 'ログイン情報が登録されていません',
        ]);
    }

    /** @test */
    public function パスワードが8文字未満の場合_認証エラーになる()
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123')
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => '1234567', // 8文字未満
        ]);

        // 認証失敗時にエラーメッセージが出ることを確認
        $response->assertSessionHasErrors(['email']);
    }

    /** @test */
    public function 正しい情報を入力すればログインできる()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123')
        ]);
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);
        $response->assertRedirect('/attendance'); // 遷移先はプロジェクト仕様に合わせて
        $this->assertAuthenticated();
    }
}

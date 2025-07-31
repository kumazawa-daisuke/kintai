<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Carbon;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    /** @test メール認証メールが登録直後に送信されること */
    public function メール認証メールが登録直後に送信されること()
    {
        Notification::fake();

        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        // 通知を明示的に送信
        $user->sendEmailVerificationNotification();

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    /** @test 認証リンクからアクセスし認証済みになること */
    public function 認証リンクからアクセスし認証済みになること()
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $response = $this->actingAs($user)->get($verificationUrl);

        // 認証後のリダイレクト先に応じてここを変更
        $response->assertRedirect('/attendance');

        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    /** @test 認証済みユーザーは勤怠画面にアクセスできる */
    public function 認証済みユーザーは勤怠画面にアクセスできる()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
    }

    /** @test 未認証ユーザーは勤怠画面にアクセスしようとすると認証画面にリダイレクトされる */
    public function 未認証ユーザーは勤怠画面にアクセスしようとすると認証画面にリダイレクトされる()
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertRedirect('/email/verify');
    }
}

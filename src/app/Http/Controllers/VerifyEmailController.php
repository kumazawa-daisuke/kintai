<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Support\Facades\Auth;

class VerifyEmailController extends Controller
{
    public function __invoke(EmailVerificationRequest $request)
    {
        $user = $request->user();

        // ★ 明示的に web ガードでログイン
        Auth::guard('web')->loginUsingId($user->id);
        
        // 認証完了
        $request->fulfill();

        return redirect()->route('attendance.index')->with('success', 'メール認証が完了しました');
    }
}
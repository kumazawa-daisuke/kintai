<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\LoginRequest;

class LoginController extends Controller
{
    // ログイン画面表示
    public function show()
    {
        return view('auth.login');
    }

    // ログイン処理
    public function login(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');

        // ★ webガードを明示的に指定する
        if (Auth::guard('web')->attempt($credentials)) {
            $request->session()->regenerate();

            // webガードで再取得
            $user = Auth::guard('web')->user();

            if (!$user->hasVerifiedEmail()) {
                $user->sendEmailVerificationNotification();
                return redirect()->route('verification.notice');
            }

            return redirect()->route('attendance.index');
        }

        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => 'ログイン情報が登録されていません']);
    }

    // ログアウト処理
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // ログアウト後、ログイン画面へ
        return redirect()->route('login.form');
    }
}

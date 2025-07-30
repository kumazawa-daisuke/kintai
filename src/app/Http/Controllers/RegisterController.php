<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class RegisterController extends Controller
{
    // 登録フォーム表示
    public function show()
    {
        return view('auth.register');
    }

    // 新規登録処理
    public function register(RegisterRequest $request)
    {
        $data = $request->validated();

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
            'role'     => 'user',
        ]);

        // 明示的に web ガードでログインさせる
        Auth::guard('web')->login($user);

        // メール認証通知を送信
        if (method_exists($user, 'sendEmailVerificationNotification')) {
            $user->sendEmailVerificationNotification();
        }

        return redirect()->route('verification.notice')
            ->with('success', 'ユーザー登録が完了しました。認証メールを確認してください。');
    }

}

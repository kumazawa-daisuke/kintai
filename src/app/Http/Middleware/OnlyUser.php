<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class OnlyUser
{
    public function handle($request, Closure $next)
    {
        if (!Auth::check() || Auth::guard('admin')->check()) {
            return redirect()->route('login.form'); // 一般ログイン画面にリダイレクト
        }

        return $next($request);
    }
}
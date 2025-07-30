<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;

class SetSessionCookieByGuard
{
    public function handle($request, Closure $next)
    {
        // 管理者ログイン中 → admin_session
        if (Auth::guard('admin')->check()) {
            Config::set('session.cookie', 'admin_session');
        }
        // 一般ログイン中 → web_session
        elseif (Auth::guard('web')->check()) {
            Config::set('session.cookie', 'web_session');
        }
        // 未ログインだがURLから推測
        elseif ($request->is('admin/*')) {
            Config::set('session.cookie', 'admin_session');
        } else {
            Config::set('session.cookie', 'web_session');
        }

        return $next($request);
    }
}

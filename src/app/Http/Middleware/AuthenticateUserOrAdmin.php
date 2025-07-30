<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class AuthenticateUserOrAdmin
{
    public function handle($request, Closure $next)
    {
        if (Auth::guard('web')->check() || Auth::guard('admin')->check()) {
            return $next($request);
        }

        if ($request->is('admin/*')) {
            return redirect()->route('admin.login.form');
        }

        return redirect()->route('login.form');
    }
}

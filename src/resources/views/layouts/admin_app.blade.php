<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>勤怠管理（管理者）</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('css/admin_app.css') }}">
    @yield('css')
</head>
<body>
    @php
        $isAdminLogin = Request::is('admin/login');
    @endphp

    <header class="site-header">
        <div class="header-inner">
            <img src="{{ asset('images/logo.svg') }}" alt="COACHTECH" class="logo-img">
            {{-- 管理者ログイン画面以外でグローバルナビ表示 --}}
            @unless($isAdminLogin)
                <nav class="global-nav">
                    <a href="{{ route('admin.attendances.index') }}" class="nav-link">勤怠一覧</a>
                    <a href="{{ route('admin.users.index') }}" class="nav-link">スタッフ一覧</a>
                    <a href="{{ route('correction_request.index') }}" class="nav-link">申請一覧</a>
                    <form id="admin-logout-form" action="{{ route('admin.logout') }}" method="POST" style="display:inline;">
                        @csrf
                        <button type="submit" class="nav-link logout-btn" style="background:none; border:none; cursor:pointer;">ログアウト</button>
                    </form>
                </nav>
            @endunless
        </div>
    </header>

    <main>
        @yield('content')
    </main>
</body>
</html>

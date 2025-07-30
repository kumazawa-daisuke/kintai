<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>勤怠管理</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    @yield('css')
</head>
<body>
    <header class="site-header">
        <div class="header-inner">
            <img src="{{ asset('images/logo.svg') }}" alt="COACHTECH" class="logo-img">
            @if (
                !Request::is('login')
                && !Request::is('register')
                && !Request::is('email/*'))
                <nav class="global-nav">
                    <ul>
                        <li>
                            <a href="{{ route('attendance.index') }}">勤怠</a>
                        </li>
                        <li>
                            <a href="{{ route('attendance.list') }}">勤怠一覧</a>
                        </li>
                        <li>
                            <a href="{{ route('correction_request.index') }}">申請</a>
                        </li>
                        <li>
                            <form action="{{ route('logout') }}" method="POST" style="display:inline;">
                                @csrf
                                <button type="submit" class="logout-btn">ログアウト</button>
                            </form>
                        </li>
                    </ul>
                </nav>
            @endif
        </div>
    </header>

    <main>
        @yield('content')
    </main>
</body>
</html>

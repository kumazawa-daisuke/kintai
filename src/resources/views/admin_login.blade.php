@extends('layouts.admin_app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin_login.css') }}">
@endsection

@section('content')
    <div class="admin-login-wrap">
        <h1 class="admin-login-title">管理者ログイン</h1>
        <form action="{{ route('admin.login') }}" method="POST" class="admin-login-form" novalidate>
            @csrf

            <div class="form-group">
                <label for="email">メールアドレス</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus>
                @error('email')
                    <span class="form-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="password">パスワード</label>
                <input id="password" type="password" name="password" required>
                @error('password')
                    <span class="form-error">{{ $message }}</span>
                @enderror
            </div>

            <button type="submit" class="admin-login-btn">管理者ログインする</button>
        </form>
    </div>
@endsection

@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
@endsection

@section('content')
    <h1 class="login-title">ログイン</h1>
    <form action="{{ route('login') }}" method="POST" class="login-form" novalidate>
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

        <button type="submit" class="login-btn">ログインする</button>
    </form>
    <div class="register-link">
        <a href="{{ route('register.form') }}">会員登録はこちら</a>
    </div>
@endsection

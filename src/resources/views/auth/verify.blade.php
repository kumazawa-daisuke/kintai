@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/verify.css') }}">
@endsection

@section('content')
    <div class="verify-content">
        @if (session('message'))
            <div class="alert-message">
                {{ session('message') }}
            </div>
        @endif

        <div class="verify-message">
            <strong>登録していただいたメールアドレスに認証メールを送信しました。</strong><br>
            メール認証を完了してください。
        </div>

        <div class="verify-action">
            <a href="http://localhost:8025" target="_blank" class="verify-btn">
                認証はこちらから
            </a>
        </div>

        <form method="POST" action="{{ route('verification.send') }}" class="verify-resend-form">
            @csrf
            <button type="submit" class="verify-resend-link">認証メールを再送する</button>
        </form>
    </div>
@endsection

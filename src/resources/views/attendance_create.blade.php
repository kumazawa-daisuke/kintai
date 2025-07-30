@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance_create.css') }}">
@endsection

@section('content')
    <div class="attendance-status">
        {{-- 勤務ステータス表示 --}}
        @if($status === 'off')
            <span class="status-badge">勤務外</span>
        @elseif($status === 'working')
            <span class="status-badge">出勤中</span>
        @elseif($status === 'break')
            <span class="status-badge">休憩中</span>
        @elseif($status === 'finished')
            <span class="status-badge">退勤済</span>
        @endif
    </div>
    <div class="attendance-date">
        @php
            $week = ['日', '月', '火', '水', '木', '金', '土'];
            $today = \Carbon\Carbon::today();
            $w = $week[$today->dayOfWeek];
        @endphp
        {{ $today->format('Y年n月j日') }}（{{ $w }}）
    </div>
    <div class="attendance-time">
        {{ $currentTime }}
    </div>

    <div class="attendance-action">
        {{-- ステータスで切り替え --}}
        @if($status === 'off')
            <form method="POST" action="{{ route('attendance.start') }}">
                @csrf
                <button type="submit" class="attendance-btn btn-black">出勤</button>
            </form>
        @elseif($status === 'working')
            <form method="POST" action="{{ route('attendance.finish') }}" style="display: inline-block;">
                @csrf
                <button type="submit" class="attendance-btn btn-black">退勤</button>
            </form>
            <form method="POST" action="{{ route('attendance.break_in') }}" style="display: inline-block;">
                @csrf
                <button type="submit" class="attendance-btn btn-white">休憩入</button>
            </form>
        @elseif($status === 'break')
            <form method="POST" action="{{ route('attendance.break_out') }}">
                @csrf
                <button type="submit" class="attendance-btn btn-white">休憩戻</button>
            </form>
        @elseif($status === 'finished')
            <div class="finished-message">お疲れ様でした。</div>
        @endif
    </div>
@endsection

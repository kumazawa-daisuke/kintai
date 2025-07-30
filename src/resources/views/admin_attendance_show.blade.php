@extends('layouts.admin_app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin_attendance_show.css') }}">
@endsection

@section('content')
<div class="attendance-detail-bg">
    <div class="attendance-detail-title-area">
        <div class="attendance-detail-title">勤怠詳細</div>
    </div>
    <div class="attendance-detail-container">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="error-list">
                    @foreach ($errors->all() as $error)
                        <li class="error-item">{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form
            id="attendance-detail-form"
            action="{{ $attendance->id ? route('admin.attendances.update', $attendance->id) : route('admin.attendances.store') }}"
            method="POST"
            class="attendance-detail-form"
        >
            @csrf
            @if($attendance->id)
                @method('PUT')
            @endif

            <div class="attendance-detail-row">
                <div class="attendance-detail-label">名前</div>
                <div class="attendance-detail-value">
                    {{ $user->name ?? '---' }}
                </div>
            </div>
            <div class="attendance-detail-divider"></div>

            <div class="attendance-detail-row">
                <div class="attendance-detail-label">日付</div>
                <div class="attendance-detail-value">
                    <div class="date-table">
                        <div class="date-cell year">
                            {{ $attendance->date ? \Carbon\Carbon::parse($attendance->date)->format('Y年') : '--年' }}
                        </div>
                        <div class="date-cell day">
                            {{ $attendance->date ? \Carbon\Carbon::parse($attendance->date)->format('n月j日') : '--月--日' }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="attendance-detail-divider"></div>

            <div class="attendance-detail-row">
                <div class="attendance-detail-label">出勤・退勤</div>
                <div class="attendance-detail-value time-fields">
                    <input type="time" class="input-time" name="clock_in" value="{{ old('clock_in', $attendance->clock_in ?? '') }}">
                    <span class="time-tilde">～</span>
                    <input type="time" class="input-time" name="clock_out" value="{{ old('clock_out', $attendance->clock_out ?? '') }}">
                </div>
            </div>
            <div class="attendance-detail-divider"></div>

            @php
                $breakTimes = [];
                foreach ($attendance->breakTimes->sortBy('break_start')->values() as $break) {
                    $breakTimes[] = [
                        'break_start' => $break->break_start ? \Carbon\Carbon::parse($break->break_start)->format('H:i') : '',
                        'break_end'   => $break->break_end   ? \Carbon\Carbon::parse($break->break_end)->format('H:i') : '',
                    ];
                }
                $breakRowCount = max(1, count($breakTimes) + 1);
            @endphp

            @for($i = 0; $i < $breakRowCount; $i++)
                <div class="attendance-detail-row">
                    <div class="attendance-detail-label">
                        {{ $i === 0 ? '休憩' : '休憩' . ($i + 1) }}
                    </div>
                    <div class="attendance-detail-value time-fields">
                        <input
                            type="time"
                            class="input-time"
                            name="break_times[{{ $i }}][break_start]"
                            value="{{ old('break_times.'.$i.'.break_start', $breakTimes[$i]['break_start'] ?? '') }}"
                        >
                        <span class="time-tilde">～</span>
                        <input
                            type="time"
                            class="input-time"
                            name="break_times[{{ $i }}][break_end]"
                            value="{{ old('break_times.'.$i.'.break_end', $breakTimes[$i]['break_end'] ?? '') }}"
                        >
                    </div>
                </div>
                <div class="attendance-detail-divider"></div>
            @endfor

            <div class="attendance-detail-row">
                <div class="attendance-detail-label">備考</div>
                <div class="attendance-detail-value">
                    <textarea class="input-textarea" name="reason" rows="2">{{ old('reason', $attendance->reason ?? '') }}</textarea>
                </div>
            </div>

            <input type="hidden" name="attendance_id" value="{{ $attendance->id }}">
            <input type="hidden" name="user_id" value="{{ $attendance->user_id }}">
            <input type="hidden" name="date" value="{{ $attendance->date }}">
        </form>
    </div>

    <div class="attendance-detail-btn-block">
        <button type="submit" form="attendance-detail-form" class="edit-btn">
            {{ $attendance->id ? '修正' : '登録' }}
        </button>
    </div>
</div>
@endsection

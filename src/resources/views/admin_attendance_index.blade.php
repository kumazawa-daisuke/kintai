@extends('layouts.admin_app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin_attendance_index.css') }}">
@endsection

@section('content')
<div class="attendance-list-container">
    <h2 class="attendance-list-title">{{ $date->format('Y年n月j日') }}の勤怠</h2>

    <div class="attendance-list-controls-outer">
        <div class="attendance-list-controls">
            <form action="" method="GET">
                <button type="submit" name="date" value="{{ $date->copy()->subDay()->format('Y-m-d') }}" class="month-btn left">
                    <span class="arrow">&#8592;</span> 前日
                </button>
            </form>
            <div class="date-picker-box">
                <span class="calendar-icon">&#128197;</span>
                <form action="" method="GET" class="date-form">
                    <input type="date" name="date" value="{{ $date->format('Y-m-d') }}" class="date-input" onchange="this.form.submit()">
                </form>
            </div>
            <form action="" method="GET">
                <button type="submit" name="date" value="{{ $date->copy()->addDay()->format('Y-m-d') }}" class="month-btn right">
                    翌日 <span class="arrow">&#8594;</span>
                </button>
            </form>
        </div>
    </div>

    <div class="attendance-table-wrapper">
        <table class="attendance-table">
            <thead>
                <tr>
                    <th>名前</th>
                    <th>出勤</th>
                    <th>退勤</th>
                    <th>休憩</th>
                    <th>合計</th>
                    <th>詳細</th>
                </tr>
            </thead>
            <tbody>
                @forelse($attendances as $attendance)
                    <tr>
                        <td>{{ $attendance->user->name ?? '-' }}</td>
                        <td>{{ $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '-' }}</td>
                        <td>{{ $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '-' }}</td>
                        <td>{{ $attendance->break_sum }}</td>
                        <td>{{ $attendance->work_sum }}</td>
                        <td>
                            <a href="{{ route('admin.attendances.show', $attendance->id ?? 0) }}?user_id={{ $attendance->user_id ?? '' }}&date={{ $attendance->date ?? $date->toDateString() }}" class="detail-link">詳細</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="empty-message">該当データなし</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

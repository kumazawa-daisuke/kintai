@extends('layouts.admin_app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin_users_attendance.css') }}">
@endsection

@section('content')
<div class="attendance-list-container">
    <h2 class="attendance-list-title">{{ $user->name }}さんの勤怠</h2>

    <div class="attendance-list-controls-outer">
        <form method="GET" class="month-navigation-form">
            <div class="attendance-list-controls">
                <a href="{{ route('admin.attendance.staff', ['id' => $user->id, 'month' => $prevMonth]) }}" class="month-btn left">
                    <span class="arrow">&#8592;</span> 前月
                </a>

                <div class="date-picker-box">
                    <span class="calendar-icon">&#128197;</span>
                    <input type="month" name="month" value="{{ $currentMonth }}" class="date-input"
                        onchange="location.href='{{ route('admin.attendance.staff', ['id' => $user->id]) }}?month=' + this.value;">
                </div>

                <a href="{{ route('admin.attendance.staff', ['id' => $user->id, 'month' => $nextMonth]) }}" class="month-btn right">
                    翌月 <span class="arrow">&#8594;</span>
                </a>
            </div>
        </form>
    </div>

    <div class="attendance-table-wrapper">
        <table class="attendance-table">
            <thead>
                <tr>
                    <th>日付</th>
                    <th>出勤</th>
                    <th>退勤</th>
                    <th>休憩</th>
                    <th>合計</th>
                    <th>詳細</th>
                </tr>
            </thead>
            <tbody>
                @foreach($monthDates as $date)
                    @php
                        $attendance = $attendances->firstWhere('date', $date->format('Y-m-d'));
                    @endphp
                    <tr>
                        <td>{{ $date->format('m/d') }}({{ ['日','月','火','水','木','金','土'][$date->dayOfWeek] }})</td>
                        <td>{{ $attendance && $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '' }}</td>
                        <td>{{ $attendance && $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '' }}</td>
                        <td>{{ $attendance->break_sum ?? '' }}</td>
                        <td>{{ $attendance->work_sum ?? '' }}</td>
                        <td>
                            <a href="{{ route('admin.attendances.show', $attendance ? $attendance->id : 0) }}?date={{ $date->toDateString() }}&user_id={{ $user->id }}" class="detail-link">
                                詳細
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="csv-btn-outer">
        <form method="get" class="csv-export-form">
            <input type="hidden" name="month" value="{{ $currentMonth }}">
            <input type="hidden" name="csv" value="1">
            <button type="submit" class="csv-btn">CSV出力</button>
        </form>
    </div>
</div>
@endsection

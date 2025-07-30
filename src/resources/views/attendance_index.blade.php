@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance_index.css') }}">
@endsection

@php
    $current = \Carbon\Carbon::createFromFormat('Y-m', $currentMonth);
    $prevMonth = $current->copy()->subMonth()->format('Y-m');
    $nextMonth = $current->copy()->addMonth()->format('Y-m');

    function displayTime($time) {
        return (!empty($time) && preg_match('/^\d{2}:\d{2}$/', $time))
            ? \Carbon\Carbon::createFromFormat('H:i', $time)->format('G:i')
            : (!empty($time) ? $time : '');
    }
@endphp

@section('content')
<div class="attendance-list-container">
    <h2 class="attendance-list-title">勤怠一覧</h2>
    <div class="attendance-list-controls-outer">
        <div class="attendance-list-controls">
            <a class="month-btn left" href="{{ route('attendance.list', ['month' => $prevMonth]) }}" id="prev-month">
                <span class="arrow">&#8592;</span> 前月
            </a>
            <div class="month-label">
                <span class="calendar-icon">&#128197;</span>
                <span class="month-text">{{ str_replace('-', '/', $currentMonth) }}</span>
            </div>
            <a class="month-btn right" href="{{ route('attendance.list', ['month' => $nextMonth]) }}" id="next-month">
                翌月 <span class="arrow">&#8594;</span>
            </a>
        </div>
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
                @foreach($records as $row)
                    @php
                        $attendance = $row['attendance'];
                        $date = $row['date'];
                    @endphp
                    <tr>
                        <td>{{ $date->format('m/d') }}({{ ['日','月','火','水','木','金','土'][$date->dayOfWeek] }})</td>
                        <td>{{ $attendance && $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '' }}</td>
                        <td>{{ $attendance && $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '' }}</td>
                        <td>{{ displayTime($row['break_time']) }}</td>
                        <td>{{ displayTime($row['total_time']) }}</td>
                        <td>
                            <a href="{{ $attendance
                                ? route('attendance.show', $attendance->id)
                                : route('attendance.show', ['id' => 0, 'date' => $date->toDateString()]) }}"
                                class="detail-link">
                                詳細
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

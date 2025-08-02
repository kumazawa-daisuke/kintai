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
                    @foreach (collect($errors->all())->unique() as $error)
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
                <div class="attendance-detail-value name-value">
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
                    <input
                        type="time"
                        class="input-time"
                        name="clock_in"
                        value="{{ old('clock_in', $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '') }}"
                    >
                    <span class="time-tilde">～</span>
                    <input
                        type="time"
                        class="input-time"
                        name="clock_out"
                        value="{{ old('clock_out', $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '') }}"
                    >
                </div>
            </div>
            <div class="attendance-detail-divider"></div>

            @php
                // old値優先（バリデーションエラー後）: それ以外はDB値
                $breakTimes = old('break_times');
                if (is_null($breakTimes)) {
                    // DBから取得（編集画面や初回表示時）
                    $breakTimes = [];
                    foreach ($attendance->breakTimes->sortBy('break_start') as $b) {
                        $breakTimes[] = [
                            'break_start' => $b->break_start ? \Carbon\Carbon::parse($b->break_start)->format('H:i') : '',
                            'break_end'   => $b->break_end ? \Carbon\Carbon::parse($b->break_end)->format('H:i') : '',
                        ];
                    }
                }

                // 空行が複数ある場合は1行だけに
                $filtered = [];
                $blankCount = 0;
                foreach ($breakTimes as $row) {
                    $isBlank = (empty($row['break_start']) && empty($row['break_end']));
                    if ($isBlank) {
                        // 最初の空行だけ残す
                        if ($blankCount == 0) {
                            $filtered[] = ['break_start' => '', 'break_end' => ''];
                            $blankCount++;
                        }
                        // 2個目以降の空行はスキップ
                    } else {
                        $filtered[] = [
                            'break_start' => $row['break_start'] ?? '',
                            'break_end' => $row['break_end'] ?? '',
                        ];
                    }
                }
                // 休憩ゼロ件なら必ず空欄1行追加
                if (count($filtered) == 0) {
                    $filtered[] = ['break_start' => '', 'break_end' => ''];
                }
            @endphp

            @foreach($filtered as $i => $break)
                <div class="attendance-detail-row">
                    <div class="attendance-detail-label">
                        {{ $i === 0 ? '休憩' : '休憩' . ($i + 1) }}
                    </div>
                    <div class="attendance-detail-value time-fields">
                        <input
                            type="time"
                            class="input-time"
                            name="break_times[{{ $i }}][break_start]"
                            value="{{ $break['break_start'] }}"
                        >
                        <span class="time-tilde">～</span>
                        <input
                            type="time"
                            class="input-time"
                            name="break_times[{{ $i }}][break_end]"
                            value="{{ $break['break_end'] }}"
                        >
                    </div>
                </div>
                <div class="attendance-detail-divider"></div>
            @endforeach

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
            修正
        </button>
    </div>
</div>
@endsection

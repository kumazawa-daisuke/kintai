@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance_show.css') }}">
@endsection

@section('content')
<div class="attendance-detail-bg">
    <div class="attendance-detail-title-area">
        <div class="attendance-detail-title">勤怠詳細</div>
    </div>
    <div class="attendance-detail-container">
        @if ($errors->any())
            <div class="alert alert-danger error-box">
                <ul class="error-list">
                    @foreach (collect($errors->all())->unique() as $error)
                        <li class="error-list-item">{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @php
            // 承認待ち申請があればそちらを優先
            $pendingRequest = $attendance->correctionRequests->where('status', 'pending')->sortByDesc('created_at')->first();
            $isPending = !is_null($pendingRequest);

            $clockIn = $isPending ? ($pendingRequest->clock_in_after ?? '') : ($attendance->clock_in ?? '');
            $clockOut = $isPending ? ($pendingRequest->clock_out_after ?? '') : ($attendance->clock_out ?? '');

            // 休憩履歴（申請中のものがあればそちら優先）
            $breaks = [];
            if ($isPending && !empty($pendingRequest->breaks_after)) {
                $breaks = is_array($pendingRequest->breaks_after)
                    ? $pendingRequest->breaks_after
                    : json_decode($pendingRequest->breaks_after, true);
            } elseif ($attendance->breakTimes->count()) {
                $breaks = $attendance->breakTimes->sortBy('break_start')->map(function($b) {
                    return [
                        'break_start' => $b->break_start,
                        'break_end' => $b->break_end,
                    ];
                })->toArray();
            }
            // 入力済み＋1行（空欄）を用意
            $breakRowCount = max(1, count($breaks) + 1);
        @endphp

        <form action="{{ route('correction_request.store') }}" method="POST" class="attendance-detail-form" id="correction-request">
            @csrf
            {{-- 名前 --}}
            <div class="attendance-detail-row">
                <div class="attendance-detail-label">名前</div>
                <div class="attendance-detail-value name-value">
                    {{ $attendance->user->name ?? '---' }}
                </div>
            </div>
            <div class="attendance-detail-divider"></div>
            {{-- 日付 --}}
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
            {{-- 出勤・退勤 --}}
            <div class="attendance-detail-row">
                <div class="attendance-detail-label">出勤・退勤</div>
                <div class="attendance-detail-value time-fields">
                    <input type="time" class="input-time" name="clock_in"
                        value="{{ old('clock_in', $clockIn ? \Carbon\Carbon::parse($clockIn)->format('H:i') : '') }}"
                        @if($isPending) disabled @endif>
                    <span class="time-tilde">～</span>
                    <input type="time" class="input-time" name="clock_out"
                        value="{{ old('clock_out', $clockOut ? \Carbon\Carbon::parse($clockOut)->format('H:i') : '') }}"
                        @if($isPending) disabled @endif>
                </div>
            </div>
            <div class="attendance-detail-divider"></div>
            {{-- 休憩履歴 --}}
            @for($i = 0; $i < $breakRowCount; $i++)
                <div class="attendance-detail-row">
                    <div class="attendance-detail-label">
                        {{ $i === 0 ? '休憩' : '休憩' . ($i+1) }}
                    </div>
                    <div class="attendance-detail-value time-fields">
                        <input
                            type="time"
                            class="input-time"
                            name="break_times[{{ $i }}][break_start]"
                            value="{{ old('break_times.'.$i.'.break_start', isset($breaks[$i]['break_start']) && $breaks[$i]['break_start'] ? \Carbon\Carbon::parse($breaks[$i]['break_start'])->format('H:i') : '') }}"
                            @if($isPending) disabled @endif
                        >
                        <span class="time-tilde">～</span>
                        <input
                            type="time"
                            class="input-time"
                            name="break_times[{{ $i }}][break_end]"
                            value="{{ old('break_times.'.$i.'.break_end', isset($breaks[$i]['break_end']) && $breaks[$i]['break_end'] ? \Carbon\Carbon::parse($breaks[$i]['break_end'])->format('H:i') : '') }}"
                            @if($isPending) disabled @endif
                        >
                    </div>
                </div>
                <div class="attendance-detail-divider"></div>
            @endfor

            {{-- 備考 --}}
            <div class="attendance-detail-row">
                <div class="attendance-detail-label">備考</div>
                <div class="attendance-detail-value">
                    <textarea class="input-textarea" name="reason" rows="2" @if($isPending) disabled @endif>{{ old('reason', $isPending ? ($pendingRequest->reason_after ?? $pendingRequest->reason ?? '') : ($attendance->reason ?? '')) }}</textarea>
                </div>
            </div>
            {{-- Hiddenパラメータ --}}
            <input type="hidden" name="attendance_id" value="{{ $attendance->id ?? '' }}">
            <input type="hidden" name="date" value="{{ $attendance->date ?? (request('date') ?? '') }}">
            <input type="hidden" name="request_type" value="edit">
        </form>
    </div>

    <div class="attendance-detail-btn-block-outer">
        @if(!$isPending)
            <div class="attendance-detail-btn-block center-btn">
                <button type="submit" form="correction-request" class="edit-btn large-btn">修正</button>
            </div>
        @endif
    </div>
    @if($isPending)
        <div class="pending-msg-outside right-msg">
            <span class="correction-waiting-alert">※承認待ちのため修正はできません。</span>
        </div>
    @endif
</div>
@endsection

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
                    @foreach ($errors->all() as $error)
                        <li class="error-list-item">{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @php
            $pendingRequest = $attendance->correctionRequests()
                ->where('status', 'pending')
                ->orderByDesc('created_at')
                ->first();
            $isPending = !!$pendingRequest;

            // 休憩配列をセット
            if ($isPending && !empty($pendingRequest->breaks_after)) {
                $breaks = collect(json_decode($pendingRequest->breaks_after, true));
            } else {
                $breaks = $attendance->breakTimes ? $attendance->breakTimes->sortBy('break_start')->values() : collect();
                // オブジェクト→配列化（フォーム用）
                $breaks = $breaks->map(function($b) {
                    return [
                        'break_start' => $b->break_start ?? '',
                        'break_end' => $b->break_end ?? '',
                    ];
                });
            }

            // 入力済み＋1行（空欄）を用意
            $breakArr = $breaks->toArray();
            $breakRowCount = max(1, count($breakArr) + 1);
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
                        value="{{ $isPending ? ($pendingRequest->clock_in_after ?? '') : ($attendance->clock_in ?? '') }}"
                        @if($isPending) disabled @endif>
                    <span class="time-tilde">～</span>
                    <input type="time" class="input-time" name="clock_out"
                        value="{{ $isPending ? ($pendingRequest->clock_out_after ?? '') : ($attendance->clock_out ?? '') }}"
                        @if($isPending) disabled @endif>
                </div>
            </div>
            <div class="attendance-detail-divider"></div>
            {{-- 休憩履歴 --}}
            @for($i = 0; $i < $breakRowCount; $i++)
                <div class="attendance-detail-row">
                    <div class="attendance-detail-label">
                        @if($i === 0)
                            休憩
                        @else
                            休憩{{ $i+1 }}
                        @endif
                    </div>
                    <div class="attendance-detail-value time-fields">
                        <input
                            type="time"
                            class="input-time"
                            name="break_times[{{ $i }}][break_start]"
                            value="{{ old('break_times.'.$i.'.break_start', $breakArr[$i]['break_start'] ?? '') }}"
                            @if($isPending) disabled @endif
                        >
                        <span class="time-tilde">～</span>
                        <input
                            type="time"
                            class="input-time"
                            name="break_times[{{ $i }}][break_end]"
                            value="{{ old('break_times.'.$i.'.break_end', $breakArr[$i]['break_end'] ?? '') }}"
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
                    <textarea class="input-textarea" name="reason" rows="2" @if($isPending) readonly @endif>{{ $isPending ? ($pendingRequest->reason_after ?? $pendingRequest->reason ?? '') : old('reason', $attendance->reason ?? '') }}</textarea>
                </div>
            </div>
            {{-- Hiddenパラメータ --}}
            @if(!empty($attendance->id))
                <input type="hidden" name="attendance_id" value="{{ $attendance->id }}">
                <input type="hidden" name="date" value="{{ $attendance->date }}">
            @else
                <input type="hidden" name="attendance_id" value="">
                <input type="hidden" name="date" value="{{ $attendance->date ?? (request('date') ?? '') }}">
            @endif
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

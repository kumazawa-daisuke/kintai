@extends('layouts.admin_app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin_requests_show.css') }}">
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

        {{-- 名前 --}}
        <div class="attendance-detail-row">
            <div class="attendance-detail-label">名前</div>
            <div class="attendance-detail-value value-2col">
                <div class="col-left"><span>{{ $request->user->name ?? '---' }}</span></div>
                <div class="col-right"></div>
            </div>
        </div>
        <div class="attendance-detail-divider"></div>

        {{-- 日付 --}}
        <div class="attendance-detail-row">
            <div class="attendance-detail-label">日付</div>
            <div class="attendance-detail-value value-2col">
                <div class="col-left">
                    <span>{{ $request->attendance && $request->attendance->date ? \Carbon\Carbon::parse($request->attendance->date)->format('Y年') : '--年' }}</span>
                </div>
                <div class="col-right">
                    <span>{{ $request->attendance && $request->attendance->date ? \Carbon\Carbon::parse($request->attendance->date)->format('n月j日') : '--月--日' }}</span>
                </div>
            </div>
        </div>
        <div class="attendance-detail-divider"></div>

        {{-- 出勤・退勤 --}}
        @php
            $clockIn = $request->clock_in_after ?? ($request->attendance->clock_in ?? null);
            $clockOut = $request->clock_out_after ?? ($request->attendance->clock_out ?? null);
        @endphp
        <div class="attendance-detail-row">
            <div class="attendance-detail-label">出勤・退勤</div>
            <div class="attendance-detail-value value-3col">
                <div class="col-left">
                    <span>{{ $clockIn ? \Carbon\Carbon::parse($clockIn)->format('H:i') : '' }}</span>
                </div>
                <div class="col-tilde">～</div>
                <div class="col-right">
                    <span>{{ $clockOut ? \Carbon\Carbon::parse($clockOut)->format('H:i') : '' }}</span>
                </div>
            </div>
        </div>
        <div class="attendance-detail-divider"></div>

        {{-- 休憩履歴 --}}
        @php
            // breaks_afterがなければattendanceのbreakTimesを使う
            $breaks_raw = $request->breaks_after ?? ($request->attendance->breakTimes ?? null);
            // 文字列だった場合json_decode
            if (is_string($breaks_raw)) {
                $breaks = json_decode($breaks_raw, true);
            } elseif ($breaks_raw instanceof \Illuminate\Support\Collection) {
                // Eloquentコレクションなら配列化
                $breaks = $breaks_raw->toArray();
            } else {
                $breaks = $breaks_raw;
            }
            if (!is_array($breaks) || is_null($breaks)) {
                $breaks = [];
            }
            $breakRows = max(1, count($breaks));
        @endphp

        @for($i = 0; $i < $breakRows; $i++)
            <div class="attendance-detail-row">
                <div class="attendance-detail-label">{{ $i === 0 ? '休憩' : '休憩'.($i+1) }}</div>
                <div class="attendance-detail-value value-3col">
                    <div class="col-left">
                        <span>
                            @if(isset($breaks[$i]['break_start']) && $breaks[$i]['break_start'])
                                {{ \Carbon\Carbon::parse($breaks[$i]['break_start'])->format('H:i') }}
                            @endif
                        </span>
                    </div>
                    <div class="col-tilde">～</div>
                    <div class="col-right">
                        <span>
                            @if(isset($breaks[$i]['break_end']) && $breaks[$i]['break_end'])
                                {{ \Carbon\Carbon::parse($breaks[$i]['break_end'])->format('H:i') }}
                            @endif
                        </span>
                    </div>
                </div>
            </div>
            <div class="attendance-detail-divider"></div>
        @endfor

        {{-- 備考 --}}
        <div class="attendance-detail-row note-row">
            <div class="attendance-detail-label">備考</div>
            <div class="attendance-detail-value value-2col">
                <div class="col-left"><span>{{ $request->reason ?? '-' }}</span></div>
                <div class="col-right"></div>
            </div>
        </div>
    </div>
    {{-- 承認ボタン --}}
    <div class="attendance-detail-btn-block-outer">
        <div class="attendance-detail-btn-block center-btn">
            <form action="{{ route('admin.requests.approve', ['attendance_correct_request' => $request->id]) }}" method="POST">
                @csrf
                @if($request->status === 'pending')
                    <button type="submit" class="edit-btn large-btn">承認</button>
                @else
                    <button type="button" class="edit-btn large-btn approved-btn" disabled>承認済み</button>
                @endif
            </form>
        </div>
    </div>
</div>
@endsection

@php
    $layout = Auth::guard('admin')->check() ? 'layouts.admin_app' : 'layouts.app';
@endphp

@extends($layout)

@section('css')
<link rel="stylesheet" href="{{ asset('css/correction_request_index.css') }}">
@endsection

@section('content')
<div class="request-list-bg">
    <div class="request-list-container">
        <div class="request-list-title">申請一覧</div>

        <div class="request-list-tabs">
            <a href="{{ route('correction_request.index', ['status' => 'pending']) }}" 
               class="request-tab {{ $status === 'pending' ? 'active' : '' }}">承認待ち</a>
            <a href="{{ route('correction_request.index', ['status' => 'approved']) }}" 
               class="request-tab {{ $status === 'approved' ? 'active' : '' }}">承認済み</a>
        </div>

        <div class="request-list-divider"></div>

        <div class="request-table-wrapper">
            <table class="request-table">
                <thead>
                    <tr>
                        <th>状態</th>
                        <th>名前</th>
                        <th>対象日時</th>
                        <th>申請理由</th>
                        <th>申請日時</th>
                        <th>詳細</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requests as $req)
                        <tr>
                            <td>
                                @if($req->status === 'pending')
                                    <span class="status-badge pending">承認待ち</span>
                                @elseif($req->status === 'approved')
                                    <span class="status-badge approved">承認済</span>
                                @else
                                    <span class="status-badge rejected">却下</span>
                                @endif
                            </td>
                            <td>{{ $req->user->name ?? '---' }}</td>
                            <td>
                                {{ optional($req->attendance)->date
                                    ? \Carbon\Carbon::parse($req->attendance->date)->format('Y/m/d')
                                    : ($req->date ? \Carbon\Carbon::parse($req->date)->format('Y/m/d') : '---') }}
                            </td>
                            <td>{{ $req->reason }}</td>
                            <td>{{ $req->created_at->format('Y/m/d') }}</td>
                            <td>
                                @if($req->attendance)
                                    @if($isAdmin)
                                        {{-- 管理者は専用のルートへ --}}
                                        <a href="{{ route('admin.requests.show', $req->id) }}" class="detail-link">詳細</a>
                                    @else
                                        {{-- 一般ユーザーは自分の勤怠詳細へ --}}
                                        <a href="{{ route('attendance.show', $req->attendance->id) }}" class="detail-link">詳細</a>
                                    @endif
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="no-requests">申請はありません</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

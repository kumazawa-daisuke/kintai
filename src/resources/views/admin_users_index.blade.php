@extends('layouts.admin_app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin_users_index.css') }}">
@endsection

@section('content')
<div class="attendance-list-container">
    <h2 class="attendance-list-title">スタッフ一覧</h2>
    <div class="attendance-table-wrapper">
        <table class="attendance-table">
            <thead>
                <tr>
                    <th scope="col">名前</th>
                    <th scope="col">メールアドレス</th>
                    <th scope="col">月次勤怠</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    <tr>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>
                            <a href="{{ route('admin.attendance.staff', ['id' => $user->id, 'month' => now()->format('Y-m')]) }}"
                            class="detail-link">詳細</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="no-data">該当データなし</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

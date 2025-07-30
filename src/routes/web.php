<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\User\AttendanceController;
use App\Http\Controllers\User\CorrectionRequestController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\LoginController as AdminLoginController;
use App\Http\Controllers\Admin\AttendanceController as AdminAttendanceController;
use App\Http\Controllers\Admin\CorrectionRequestController as AdminCorrectionRequestController;
use App\Http\Controllers\VerifyEmailController;

// 一般ユーザー用
// 会員登録
Route::get('/register', [RegisterController::class, 'show'])->name('register.form');
Route::post('/register', [RegisterController::class, 'register'])->name('register');

// ログイン・ログアウト
Route::get('/login', [LoginController::class, 'show'])->name('login.form');
Route::post('/login', [LoginController::class, 'login'])->name('login');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// メール認証関連ルート
Route::get('/email/verify/{id}/{hash}', VerifyEmailController::class)
    ->middleware(['signed'])
    ->name('verification.verify');

Route::middleware(['auth'])->group(function () {
    // メール認証通知画面
    Route::get('/email/verify', function () {
        return view('auth.verify');
    })->name('verification.notice');

    // 認証メール再送
    Route::post('/email/verification-notification', function (Request $request) {
        $request->user()->sendEmailVerificationNotification();
        return back()->with('message', '認証メールを再送しました');
    })->name('verification.send');
});

Route::middleware(['auth', 'verified', 'only.user'])->group(function () {
    // 出勤登録画面（出勤ボタン等）と勤怠登録（create, store）
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/attendance/start', [AttendanceController::class, 'start'])->name('attendance.start');
    Route::post('/attendance/finish', [AttendanceController::class, 'finish'])->name('attendance.finish');
    Route::post('/attendance/break_in', [AttendanceController::class, 'breakIn'])->name('attendance.break_in');
    Route::post('/attendance/break_out', [AttendanceController::class, 'breakOut'])->name('attendance.break_out');
    // 勤怠一覧
    Route::get('/attendance/list', [AttendanceController::class, 'list'])->name('attendance.list');
    // 勤怠詳細
    Route::get('/attendance/{id}', [AttendanceController::class, 'show'])->name('attendance.show');
    // 申請作成
    Route::post('/stamp_correction_request/list', [CorrectionRequestController::class, 'store'])->name('correction_request.store');
});

// ------------------------------------------------------------

// 管理者ログイン（未ログインでもアクセス可）
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AdminLoginController::class, 'show'])->name('login.form');
    Route::post('/login', [AdminLoginController::class, 'login'])->name('login');
});

// 管理者専用（ログイン後のみアクセス可能）
Route::prefix('admin')->name('admin.')->middleware('only.admin')->group(function () {
    // 管理者用ログアウト
    Route::post('/logout', [AdminLoginController::class, 'logout'])->name('logout');
    // 勤怠一覧（全社員）
    Route::get('/attendance/list', [AdminAttendanceController::class, 'index'])->name('attendances.index');
    // 管理者用
    Route::post('/attendance/list', [AdminAttendanceController::class, 'store'])->name('attendances.store');
    // 勤怠詳細
    Route::get('/attendance/{id}', [AdminAttendanceController::class, 'show'])->name('attendances.show');
    // 勤怠修正（update）
    Route::put('/attendance/{id}', [AdminAttendanceController::class, 'update'])->name('attendances.update');
    // スタッフ一覧
    Route::get('/staff/list', [AdminUserController::class, 'index'])->name('users.index');
    // スタッフ別勤怠一覧
    Route::get('attendance/staff/{id}', [AdminUserController::class, 'attendances'])->name('attendance.staff');
    // 管理者の申請詳細
    Route::get('/stamp_correction_request/approve/{attendance_correct_request}', [AdminCorrectionRequestController::class, 'show'])->name('requests.show');
    // 修正申請承認
    Route::post('/stamp_correction_request/approve/{attendance_correct_request}', [AdminCorrectionRequestController::class, 'approve'])->name('requests.approve');
});

// 一般ユーザー用 管理者共用　申請一覧
Route::get('/stamp_correction_request/list', [CorrectionRequestController::class, 'index'])
    ->middleware(['auth.user.or.admin'])
    ->name('correction_request.index');
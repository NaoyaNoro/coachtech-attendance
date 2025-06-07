<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\WorkController;
use App\Http\Controllers\CorrectController;
use App\Http\Controllers\ApplyController;
use App\Http\Controllers\AdminAttendanceController;
use App\Http\Controllers\AdminCorrectController;
use App\Http\Controllers\AdminApproveController;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('stamp_correction_request/list', function (Request $request) {
    if (Auth::guard('admin')->check()) {
        return app(AdminApproveController::class)->approve_list($request);
    } elseif (Auth::guard('web')->check()) {
        return app(ApplyController::class)->apply_list($request);
    }
    return redirect()->route('login');
});


Route::middleware('auth:web', 'not.admin')->group(function () {
    //勤怠に関するルート
    Route::get('/', [UserController::class, 'index']);
    Route::post('/clock_in',[WorkController::class,'clock_in']);
    Route::post('/clock_out', [WorkController::class, 'clock_out']);
    Route::post('/break_start', [WorkController::class, 'break_start']);
    Route::post('/break_end', [WorkController::class, 'break_end']);

    //勤怠一覧に関するルート
    Route::get('/attendance/list',[AttendanceController::class,'attendance_list'])->name('attendance.list');

    //勤怠詳細に関するルート
    Route::get('attendance/{id}', [AttendanceController::class, 'attendance_detail']);

    //申請に関するルート
    Route::post('attendance/correct', [CorrectController::class, 'coorect_request']);


    Route::get('/email/verify', function () {
        return view('auth.verify-email');
    })->middleware('auth')->name('verification.notice');

    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();
        return redirect('/'); // 認証後の遷移先
    })->middleware(['auth', 'signed'])->name('verification.verify');

    Route::post('/email/verification-notification', function (Request $request) {
        $request->user()->sendEmailVerificationNotification();
        return back()->with('message', '確認メールを再送信しました。');
    })->middleware(['throttle:6,1'])->name('verification.send');
});

Route::get('/admin/login',[UserController::class,'login']);
Route::post('/admin/login', [AuthenticatedSessionController::class, 'store']);
Route::get('/test', [UserController::class, 'test']);



Route::middleware('auth:admin')->group(function(){
    //日々の勤怠りスト
    Route::get('/admin/attendance/list',[AdminAttendanceController::class, 'admin_list'])->name('admin.attendance.list');

    //スタッフリスト一覧
    Route::get('/admin/staff/list', [AdminAttendanceController::class, 'staff_list']);

    //月次詳細に関するルート
    Route::get('/admin/attendance/month/list/{id}', [AdminAttendanceController::class, 'staff_attendance_list'])->name('admin.attendance.month.list');

    //勤怠詳細に関するルート
    Route::get('/admin/attendance/{id}', [AdminCorrectController::class, 'attendance_detail']);

    //申請に関するルート
    Route::post('admin/attendance/correct', [AdminCorrectController::class, 'admin_coorect']);

    //承認に関するルート
    Route::get('/stamp_correction_request/approve/{attendance_correct_request}',[AdminApproveController::class,'confirm_approval']);

    Route::post('admin/approve/{attendance_correct_request}', [AdminApproveController::class, 'approve']);

    //ログアウトの処理
    Route::post('/admin/logout', [UserController::class, 'logout']);

    //csv出力
    Route::get('/admin/attendance/export/csv', [AdminAttendanceController::class, 'export_csv']);
});





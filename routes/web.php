<?php

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\AttendanceController as AdminAttendanceController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\DesignationController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\LeaveController as AdminLeaveController;
use App\Http\Controllers\Admin\PayrollController;
use App\Http\Controllers\Admin\SalaryController;
use App\Http\Controllers\Employee\AttendanceController as EmpAttendanceController;
use App\Http\Controllers\Employee\AuthController as EmpAuthController;
use App\Http\Controllers\Employee\DashboardController as EmpDashboardController;
use App\Http\Controllers\Employee\LeaveController as EmpLeaveController;
use App\Http\Controllers\Employee\ProfileController as EmpProfileController;
use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;

// ─── Root redirect ────────────────────────────────────────────────────────────
Route::get('/', function () {
    if (Auth::guard('admin')->check()) {
        return redirect()->route('admin.dashboard');
    }
    if (Auth::guard('employee')->check()) {
        return redirect()->route('employee.dashboard');
    }
    return redirect()->route('login');
})->name('home');

// ─── Shared Login / Logout ────────────────────────────────────────────────────
Route::get('/login',  [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout',[AuthController::class, 'logout'])->name('logout');

// ─── Admin Routes ─────────────────────────────────────────────────────────────
Route::middleware('admin.auth')->prefix('admin')->name('admin.')->group(function () {

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::post('/attendance/break',        [AdminAttendanceController::class, 'addBreak'])->name('attendance.break.add');
    Route::delete('/attendance/break/{break}', [AdminAttendanceController::class, 'deleteBreak'])->name('attendance.break.delete');

    // Departments
    Route::post('departments/quick-store', [DepartmentController::class, 'quickStore'])->name('departments.quick-store');
    Route::resource('departments', DepartmentController::class)->except(['show']);
    Route::post('departments/{department}/toggle-status', [DepartmentController::class, 'toggleStatus'])
        ->name('departments.toggle-status');

    // Designations
    Route::post('designations/quick-store', [DesignationController::class, 'quickStore'])->name('designations.quick-store');
    Route::resource('designations', DesignationController::class)->except(['show']);
    Route::post('designations/{designation}/toggle-status', [DesignationController::class, 'toggleStatus'])
        ->name('designations.toggle-status');

    // Employees
    Route::resource('employees', EmployeeController::class);
    Route::post('employees/{employee}/reset-password', [EmployeeController::class, 'resetPassword'])
        ->name('employees.reset-password');

    // Leave
    Route::prefix('leave')->name('leave.')->group(function () {
        Route::get('/',                     [AdminLeaveController::class, 'index'])->name('index');
        Route::get('/create',               [AdminLeaveController::class, 'create'])->name('create');
        Route::post('/',                    [AdminLeaveController::class, 'store'])->name('store');
        Route::get('/{leave}',              [AdminLeaveController::class, 'show'])->name('show');
        Route::post('/{leave}/approve',     [AdminLeaveController::class, 'approve'])->name('approve');
        Route::post('/{leave}/reject',      [AdminLeaveController::class, 'reject'])->name('reject');
    });

    // Salary
    Route::prefix('salary')->name('salary.')->group(function () {
        Route::get('/',                   [SalaryController::class, 'index'])->name('index');
        Route::get('/{employee}/manage',  [SalaryController::class, 'create'])->name('create');
        Route::post('/{employee}',        [SalaryController::class, 'store'])->name('store');
        Route::get('/{employee}/history', [SalaryController::class, 'history'])->name('history');
    });

    // Payroll
    Route::get('/payroll', [PayrollController::class, 'index'])->name('payroll.index');

    // Attendance
    Route::prefix('attendance')->name('attendance.')->group(function () {
        Route::get('/',              [AdminAttendanceController::class, 'index'])->name('index');
        Route::get('/live-status',   [AdminAttendanceController::class, 'liveStatus'])->name('live-status');
        Route::post('/mark',         [AdminAttendanceController::class, 'mark'])->name('mark');
    });

    // Notifications ← no extra prefix, name() group handles admin. prefix
    Route::get('/notifications',           [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
    Route::get('/notifications/{id}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
});

// ─── Employee Routes ──────────────────────────────────────────────────────────
Route::middleware('employee.auth')->prefix('employee')->name('employee.')->group(function () {

    // Password change + logout (no force.password.change middleware)
    Route::get('/change-password',  [EmpAuthController::class, 'showChangePassword'])->name('password.change');
    Route::post('/change-password', [EmpAuthController::class, 'updatePassword'])->name('password.update');
    Route::post('/logout',          [EmpAuthController::class, 'logout'])->name('logout');

    // Protected routes
    Route::middleware('force.password.change')->group(function () {
        Route::get('/dashboard', [EmpDashboardController::class, 'index'])->name('dashboard');

        Route::post('/attendance/break-out', [EmpAttendanceController::class, 'breakOut'])->name('attendance.breakout');
        Route::post('/attendance/break-in',  [EmpAttendanceController::class, 'breakIn'])->name('attendance.breakin');
        Route::get('/attendance/live-status', [EmpAttendanceController::class, 'liveStatus'])->name('attendance.live-status');

        Route::get('/profile',        [EmpProfileController::class, 'index'])->name('profile');
        Route::post('/profile/phone', [EmpProfileController::class, 'updatePhone'])->name('profile.phone');

        Route::get('/attendance',      [EmpAttendanceController::class, 'index'])->name('attendance.index');
        Route::get('/attendance/charts', [EmpAttendanceController::class, 'charts'])->name('attendance.charts');
        Route::post('/attendance/in',  [EmpAttendanceController::class, 'checkIn'])->name('attendance.checkin');
        Route::post('/attendance/out', [EmpAttendanceController::class, 'checkOut'])->name('attendance.checkout');

        Route::get('/leave',                       [EmpLeaveController::class, 'index'])->name('leave.index');
        Route::get('/leave/apply',                 [EmpLeaveController::class, 'create'])->name('leave.create');
        Route::post('/leave',                      [EmpLeaveController::class, 'store'])->name('leave.store');
        Route::delete('/leave/{leave}/cancel',     [EmpLeaveController::class, 'cancel'])->name('leave.cancel');

        // Notifications ← same fix, no double prefix
        Route::get('/notifications',           [NotificationController::class, 'index'])->name('notifications.index');
        Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
        Route::get('/notifications/{id}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    });
});
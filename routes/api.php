<?php

use App\Http\Controllers\Api\V1\Admin\AttendanceController as AdminAttendanceController;
use App\Http\Controllers\Api\V1\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Api\V1\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Api\V1\Admin\DepartmentController as AdminDepartmentController;
use App\Http\Controllers\Api\V1\Admin\DesignationController as AdminDesignationController;
use App\Http\Controllers\Api\V1\Admin\EmployeeController as AdminEmployeeController;
use App\Http\Controllers\Api\V1\Admin\LeaveController as AdminLeaveController;
use App\Http\Controllers\Api\V1\Admin\PayrollController as AdminPayrollController;
use App\Http\Controllers\Api\V1\Admin\SalaryController as AdminSalaryController;
use App\Http\Controllers\Api\V1\Employee\AttendanceController as EmployeeAttendanceController;
use App\Http\Controllers\Api\V1\Employee\AuthController as EmployeeAuthController;
use App\Http\Controllers\Api\V1\Employee\DashboardController as EmployeeDashboardController;
use App\Http\Controllers\Api\V1\Employee\LeaveController as EmployeeLeaveController;
use App\Http\Controllers\Api\V1\Employee\ProfileController as EmployeeProfileController;
use App\Http\Controllers\Api\V1\NotificationController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    // ── Admin auth (public) ───────────────────────────────────────────────────
    Route::prefix('admin/auth')->group(function () {
        Route::post('login', [AdminAuthController::class, 'login'])->middleware('throttle:10,1');
    });

    // ── Employee auth (public) ────────────────────────────────────────────────
    Route::prefix('employee/auth')->group(function () {
        Route::post('login', [EmployeeAuthController::class, 'login'])->middleware('throttle:10,1');
    });

    // ── Admin protected routes ────────────────────────────────────────────────
    Route::prefix('admin')->middleware('auth:api_admin')->group(function () {
        Route::prefix('auth')->group(function () {
            Route::get('me', [AdminAuthController::class, 'me']);
            Route::post('refresh', [AdminAuthController::class, 'refresh']);
            Route::post('logout', [AdminAuthController::class, 'logout']);
        });

        Route::get('dashboard', [AdminDashboardController::class, 'index']);

        Route::apiResource('employees', AdminEmployeeController::class);
        Route::post('employees/{employee}/reset-password', [AdminEmployeeController::class, 'resetPassword']);

        Route::apiResource('departments', AdminDepartmentController::class);
        Route::post('departments/{department}/toggle-status', [AdminDepartmentController::class, 'toggleStatus']);

        Route::apiResource('designations', AdminDesignationController::class);
        Route::post('designations/{designation}/toggle-status', [AdminDesignationController::class, 'toggleStatus']);

        Route::get('leaves', [AdminLeaveController::class, 'index']);
        Route::post('leaves', [AdminLeaveController::class, 'store']);
        Route::get('leaves/{leave}', [AdminLeaveController::class, 'show']);
        Route::post('leaves/{leave}/approve', [AdminLeaveController::class, 'approve']);
        Route::post('leaves/{leave}/reject', [AdminLeaveController::class, 'reject']);

        Route::get('attendance/daily', [AdminAttendanceController::class, 'daily']);
        Route::get('attendance/monthly', [AdminAttendanceController::class, 'monthly']);
        Route::get('attendance/statistics', [AdminAttendanceController::class, 'statistics']);
        Route::get('attendance/charts', [AdminAttendanceController::class, 'charts']);
        Route::get('attendance/live-status', [AdminAttendanceController::class, 'liveStatus']);
        Route::post('attendance/mark', [AdminAttendanceController::class, 'mark']);
        Route::post('attendance/breaks', [AdminAttendanceController::class, 'addBreak']);
        Route::delete('attendance/breaks/{break}', [AdminAttendanceController::class, 'deleteBreak']);

        Route::get('salary', [AdminSalaryController::class, 'index']);
        Route::get('salary/{employee}', [AdminSalaryController::class, 'show']);
        Route::post('salary/{employee}', [AdminSalaryController::class, 'store']);

        Route::get('payroll', [AdminPayrollController::class, 'index']);

        Route::get('notifications', [NotificationController::class, 'index']);
        Route::get('notifications/unread-count', [NotificationController::class, 'unreadCount']);
        Route::post('notifications/read-all', [NotificationController::class, 'markAllRead']);
        Route::post('notifications/{id}/read', [NotificationController::class, 'markRead']);
    });

    // ── Employee protected routes ─────────────────────────────────────────────
    Route::prefix('employee')->group(function () {
        Route::prefix('auth')->middleware('auth:api_employee')->group(function () {
            Route::get('me', [EmployeeAuthController::class, 'me']);
            Route::post('refresh', [EmployeeAuthController::class, 'refresh']);
            Route::post('logout', [EmployeeAuthController::class, 'logout']);
            Route::post('change-password', [EmployeeAuthController::class, 'changePassword']);
        });

        Route::middleware(['auth:api_employee', 'api.password'])->group(function () {
            Route::get('dashboard', [EmployeeDashboardController::class, 'index']);

            Route::get('profile', [EmployeeProfileController::class, 'show']);
            Route::patch('profile/phone', [EmployeeProfileController::class, 'updatePhone']);

            Route::get('attendance', [EmployeeAttendanceController::class, 'index']);
            Route::get('attendance/charts', [EmployeeAttendanceController::class, 'charts']);
            Route::post('attendance/check-in', [EmployeeAttendanceController::class, 'checkIn']);
            Route::post('attendance/check-out', [EmployeeAttendanceController::class, 'checkOut']);
            Route::post('attendance/break/start', [EmployeeAttendanceController::class, 'startBreak']);
            Route::post('attendance/break/end', [EmployeeAttendanceController::class, 'endBreak']);
            Route::get('attendance/live-status', [EmployeeAttendanceController::class, 'liveStatus']);

            Route::get('leaves', [EmployeeLeaveController::class, 'index']);
            Route::get('leaves/balance', [EmployeeLeaveController::class, 'balance']);
            Route::post('leaves', [EmployeeLeaveController::class, 'store']);
            Route::get('leaves/{leave}', [EmployeeLeaveController::class, 'show']);
            Route::delete('leaves/{leave}', [EmployeeLeaveController::class, 'cancel']);

            Route::get('notifications', [NotificationController::class, 'index']);
            Route::get('notifications/unread-count', [NotificationController::class, 'unreadCount']);
            Route::post('notifications/read-all', [NotificationController::class, 'markAllRead']);
            Route::post('notifications/{id}/read', [NotificationController::class, 'markRead']);
        });
    });
});

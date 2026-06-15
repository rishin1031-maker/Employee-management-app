<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\DesignationController;
use App\Http\Controllers\Admin\EmployeeController;
use Illuminate\Support\Facades\Route;

// Guest routes
Route::middleware('guest:admin')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('admin.login');
    Route::post('/login', [AuthController::class, 'login'])->name('admin.login.post');
});

// Admin authenticated routes
Route::middleware('admin.auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('admin.dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('admin.logout');

    Route::resource('departments', DepartmentController::class)->except(['show']);
    Route::post('departments/{department}/toggle-status', [DepartmentController::class, 'toggleStatus'])
        ->name('departments.toggle-status');

    Route::resource('designations', DesignationController::class)->except(['show']);
    Route::post('designations/{designation}/toggle-status', [DesignationController::class, 'toggleStatus'])
        ->name('designations.toggle-status');
        
    Route::resource('employees', EmployeeController::class);
});
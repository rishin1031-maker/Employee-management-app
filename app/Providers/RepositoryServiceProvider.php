<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

// Contracts
use App\Contracts\Repositories\EmployeeRepositoryInterface;
use App\Contracts\Repositories\DepartmentRepositoryInterface;
use App\Contracts\Repositories\DesignationRepositoryInterface;
use App\Contracts\Repositories\LeaveRepositoryInterface;
use App\Contracts\Repositories\AttendanceRepositoryInterface;
use App\Contracts\Repositories\SalaryRepositoryInterface;
use App\Contracts\Repositories\PayrollRepositoryInterface;
use App\Contracts\Repositories\NotificationRepositoryInterface;

// Implementations
use App\Repositories\EmployeeRepository;
use App\Repositories\DepartmentRepository;
use App\Repositories\DesignationRepository;
use App\Repositories\LeaveRepository;
use App\Repositories\AttendanceRepository;
use App\Repositories\SalaryRepository;
use App\Repositories\PayrollRepository;
use App\Repositories\NotificationRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(EmployeeRepositoryInterface::class,    EmployeeRepository::class);
        $this->app->bind(DepartmentRepositoryInterface::class,  DepartmentRepository::class);
        $this->app->bind(DesignationRepositoryInterface::class, DesignationRepository::class);
        $this->app->bind(LeaveRepositoryInterface::class,       LeaveRepository::class);
        $this->app->bind(AttendanceRepositoryInterface::class,  AttendanceRepository::class);
        $this->app->bind(SalaryRepositoryInterface::class,      SalaryRepository::class);
        $this->app->bind(PayrollRepositoryInterface::class,     PayrollRepository::class);
        $this->app->bind(NotificationRepositoryInterface::class,NotificationRepository::class);
    }
}
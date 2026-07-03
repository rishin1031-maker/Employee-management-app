<?php

namespace App\Providers;

use App\Models\Admin;
use App\Models\Attendance;
use App\Models\AttendanceBreak;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\Salary;
use App\Models\SalaryHistory;
use App\Observers\ActivityLogObserver;
use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)
                ->by($request->user()?->id ?: $request->ip());
        });

        $this->registerActivityLogObservers();
    }

    private function registerActivityLogObservers(): void
    {
        $observer = ActivityLogObserver::class;

        Admin::observe($observer);
        Employee::observe($observer);
        Department::observe($observer);
        Designation::observe($observer);
        LeaveRequest::observe($observer);
        LeaveBalance::observe($observer);
        Attendance::observe($observer);
        AttendanceBreak::observe($observer);
        Salary::observe($observer);
        SalaryHistory::observe($observer);
    }
}

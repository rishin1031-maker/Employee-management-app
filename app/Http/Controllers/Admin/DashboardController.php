<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\LeaveRequest;

class DashboardController extends Controller
{
    public function index()
    {
        $totalEmployees    = Employee::count();
        $activeEmployees   = Employee::where('status', 'active')->count();
        $inactiveEmployees = Employee::where('status', 'inactive')->count();
        $totalDepartments  = Department::count();
        $totalDesignations = Designation::count();
        $pendingLeaves     = LeaveRequest::where('status', 'pending')->count();

        // Today's attendance summary
        $todayPresent   = Attendance::where('date', today())->where('status', 'present')->count();
        $todayAbsent    = Attendance::where('date', today())->where('status', 'absent')->count();
        $todayNotMarked = $activeEmployees - Attendance::where('date', today())->count();

        $recentEmployees = Employee::with(['department', 'designation'])
                            ->latest()->take(5)->get();

        $recentLeaves = LeaveRequest::with('employee')
                            ->where('status', 'pending')
                            ->latest()->take(5)->get();

        return view('dashboard.index', compact(
            'totalEmployees', 'activeEmployees', 'inactiveEmployees',
            'totalDepartments', 'totalDesignations', 'pendingLeaves',
            'todayPresent', 'todayAbsent', 'todayNotMarked',
            'recentEmployees', 'recentLeaves'
        ));
    }
}
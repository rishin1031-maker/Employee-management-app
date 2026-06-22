<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AttendanceService;
use App\Services\DepartmentService;
use App\Services\DesignationService;
use App\Services\EmployeeService;
use App\Services\LeaveService;

class DashboardController extends Controller
{
    public function __construct(
        private EmployeeService    $employeeService,
        private DepartmentService  $departmentService,
        private DesignationService $designationService,
        private LeaveService       $leaveService,
        private AttendanceService  $attendanceService,
    ) {}

    public function index()
    {
        $employeeStats = $this->employeeService->getDashboardStats();
        $activeCount   = $employeeStats['active'];

        $totalDepartments  = $this->departmentService->getPaginated(1000)->total();
        $totalDesignations = $this->designationService->getPaginated(1000)->total();
        $pendingLeaves     = $this->leaveService->getPendingCount();
        $recentLeaves      = $this->leaveService->getRecentPending(5);
        $todayStats        = $this->attendanceService->getTodayStats($activeCount);

        return view('dashboard.index', [
            'totalEmployees'    => $employeeStats['total'],
            'activeEmployees'   => $activeCount,
            'inactiveEmployees' => $employeeStats['inactive'],
            'totalDepartments'  => $totalDepartments,
            'totalDesignations' => $totalDesignations,
            'pendingLeaves'     => $pendingLeaves,
            'todayPresent'      => $todayStats['present'],
            'todayAbsent'       => $todayStats['absent'],
            'todayNotMarked'    => $todayStats['not_marked'],
            'recentEmployees'   => $employeeStats['recent'],
            'recentLeaves'      => $recentLeaves,
        ]);
    }
}
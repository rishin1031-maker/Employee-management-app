<?php

namespace App\Services;

use App\Contracts\Repositories\AttendanceRepositoryInterface;
use App\Contracts\Repositories\EmployeeRepositoryInterface;
use App\Contracts\Repositories\LeaveRepositoryInterface;
use App\Models\Employee;

class DashboardService
{
    public function __construct(
        private EmployeeService $employeeService,
        private DepartmentService $departmentService,
        private DesignationService $designationService,
        private LeaveService $leaveService,
        private AttendanceService $attendanceService,
        private EmployeeRepositoryInterface $employeeRepo,
        private LeaveRepositoryInterface $leaveRepo,
        private AttendanceRepositoryInterface $attendanceRepo,
    ) {}

    public function getAdminDashboardData(): array
    {
        $employeeStats = $this->employeeService->getDashboardStats();
        $activeCount   = $employeeStats['active'];
        $todayStats    = $this->attendanceService->getTodayStats($activeCount);

        return [
            'totalEmployees'    => $employeeStats['total'],
            'activeEmployees'   => $activeCount,
            'inactiveEmployees' => $employeeStats['inactive'],
            'totalDepartments'  => $this->departmentService->getPaginated([], 1000)->total(),
            'totalDesignations' => $this->designationService->getPaginated([], 1000)->total(),
            'pendingLeaves'     => $this->leaveService->getPendingCount(),
            'todayPresent'      => $todayStats['present'],
            'todayAbsent'       => $todayStats['absent'],
            'todayNotMarked'    => $todayStats['not_marked'],
            'recentEmployees'   => $employeeStats['recent'],
            'recentLeaves'      => $this->leaveService->getRecentPending(5),
        ];
    }

    public function getEmployeeDashboardData(int $employeeId): array
    {
        $employee = $this->employeeRepo->findWithRelations(
            $employeeId,
            ['department', 'designation', 'salary', 'leaveBalance']
        );

        $today = $this->attendanceService->getTodayForEmployee($employeeId);
        if ($today) {
            $today->load('breaks');
        }

        $employee->setRelation('todayAttendance', $today);

        return [
            'employee'         => $employee,
            'leaveStats'       => $this->leaveRepo->getStatsForEmployee($employeeId),
            'recentLeaves'     => $this->leaveRepo->getRecentForEmployee($employeeId, 5),
            'recentAttendance' => $this->attendanceRepo->getRecentForEmployee($employeeId, 7),
            'liveStats'        => $this->attendanceService->getLiveStats($today),
        ];
    }

    public function getEmployeeApiDashboardData(Employee $employee): array
    {
        $today = $this->attendanceService->getTodayForEmployee($employee->id);
        if ($today) {
            $today->load('breaks');
        }

        return [
            'employee'          => $employee->load(['department', 'designation']),
            'today_attendance'  => $today,
            'live_stats'        => $this->attendanceService->getLiveStats($today),
            'leave_balance'     => $this->leaveService->getOrCreateBalance($employee->id),
            'pending_leaves'    => $this->leaveRepo->countByEmployeeAndStatus($employee->id, 'pending'),
            'recent_attendance' => $this->attendanceRepo->getRecentForEmployee($employee->id, 7),
            'recent_leaves'     => $this->leaveRepo->getRecentForEmployee($employee->id, 5),
        ];
    }
}

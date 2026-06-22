<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Services\AttendanceService;
use App\Services\LeaveService;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __construct(
        private LeaveService      $leaveService,
        private AttendanceService $attendanceService,
    ) {}

    public function index()
    {
        $employee = Auth::guard('employee')->user()
            ->load(['department', 'designation', 'salary', 'leaveBalance']);

        $today = $this->attendanceService->getTodayForEmployee($employee->id);
        if ($today) {
            $today->load('breaks');
        }

        $leaveStats = [
            'pending'  => $employee->leaveRequests()->where('status', 'pending')->count(),
            'approved' => $employee->leaveRequests()->where('status', 'approved')->count(),
            'rejected' => $employee->leaveRequests()->where('status', 'rejected')->count(),
        ];

        $recentLeaves     = $employee->leaveRequests()->latest()->take(5)->get();
        $recentAttendance = $employee->attendances()
                                ->with('breaks')
                                ->orderByDesc('date')
                                ->take(7)
                                ->get();

        // Attach today's attendance to employee object for blade
        $employee->setRelation('todayAttendance', $today);

        return view('employee.dashboard.index', compact(
            'employee', 'leaveStats', 'recentLeaves', 'recentAttendance'
        ));
    }
}
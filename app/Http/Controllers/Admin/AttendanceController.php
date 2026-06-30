<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceBreak;
use App\Services\AttendanceService;
use App\Services\DepartmentService;
use App\Services\DesignationService;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function __construct(
        private AttendanceService $attendanceService,
        private DepartmentService $departmentService,
        private DesignationService $designationService,
    ) {}

    public function index(Request $request)
    {
        $activeView = $request->get('view', 'daily');
        $date       = $request->get('date', today()->toDateString());
        $month      = $request->get('month', now()->format('Y-m'));
        $filters    = $request->only(['search', 'department_id', 'designation_id', 'employee_id']);

        $employees     = $this->attendanceService->getDailyReport($date, $filters);
        $monthlyReport = $activeView === 'monthly'
            ? $this->attendanceService->getMonthlyReport($month, $filters)
            : [];

        $departments  = $this->departmentService->getActive();
        $designations = $this->designationService->getActiveWithDepartment();
        $statistics   = $this->attendanceService->getAdminStatistics($filters);

        return view('admin.attendance.index', compact(
            'employees',
            'date',
            'month',
            'activeView',
            'monthlyReport',
            'departments',
            'designations',
            'filters',
            'statistics',
        ));
    }

    public function mark(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'date'        => 'required|date',
            'status'      => 'required|in:present,absent,half_day,on_leave',
            'check_in'    => 'nullable|date_format:H:i',
            'check_out'   => 'nullable|date_format:H:i',
            'note'        => 'nullable|string|max:255',
        ]);

        $this->attendanceService->markAttendanceByAdmin(
            $request->employee_id,
            $request->date,
            $request->only(['status', 'check_in', 'check_out', 'note'])
        );

        return back()->with('success', 'Attendance marked successfully.');
    }

    public function addBreak(Request $request)
    {
        $request->validate([
            'attendance_id' => 'required|exists:attendances,id',
            'break_out'     => 'required|date_format:H:i',
            'break_in'      => 'nullable|date_format:H:i|after:break_out',
        ]);

        $this->attendanceService->addBreakByAdmin(
            $request->attendance_id,
            $request->break_out,
            $request->break_in
        );

        return back()->with('success', 'Break added successfully.');
    }

    public function deleteBreak(AttendanceBreak $break)
    {
        $this->attendanceService->deleteBreak($break->id);
        return back()->with('success', 'Break removed.');
    }
}

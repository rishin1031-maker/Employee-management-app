<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceBreak;
use App\Services\AttendanceService;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function __construct(private AttendanceService $attendanceService) {}

    public function index(Request $request)
    {
        $activeView = $request->get('view', 'daily');
        $date       = $request->get('date', today()->toDateString());
        $month      = $request->get('month', now()->format('Y-m'));

        $employees     = $this->attendanceService->getDailyReport($date);
        $monthlyReport = $activeView === 'monthly'
            ? $this->attendanceService->getMonthlyReport($month)
            : [];

        return view('admin.attendance.index', compact(
            'employees', 'date', 'month', 'activeView', 'monthlyReport'
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

        $attendance = \App\Models\Attendance::findOrFail($request->attendance_id);

        $this->attendanceService->addBreakByAdmin(
            $attendance->id,
            $attendance->employee_id,
            $attendance->date->toDateString(),
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
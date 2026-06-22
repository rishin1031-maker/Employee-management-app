<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Services\AttendanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    public function __construct(private AttendanceService $attendanceService) {}

    public function index(Request $request)
    {
        $employee = Auth::guard('employee')->user();
        $month    = $request->get('month', now()->format('Y-m'));
        $result   = $this->attendanceService->getMonthlyForEmployee($employee->id, $month);
        $today    = $this->attendanceService->getTodayForEmployee($employee->id);

        return view('employee.attendance.index', [
            'attendances' => $result['attendances'],
            'summary'     => $result['summary'],
            'today'       => $today,
            'month'       => $month,
        ]);
    }

    public function checkIn()
    {
        try {
            $att = $this->attendanceService->checkIn(Auth::guard('employee')->id());
            return back()->with('success', 'Checked in at ' . $att->check_in->format('h:i A'));
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function checkOut()
    {
        try {
            $att = $this->attendanceService->checkOut(Auth::guard('employee')->id());
            return back()->with('success', 'Checked out at ' . $att->check_out->format('h:i A') . '. Net hours: ' . $att->net_hours_worked);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function breakOut()
    {
        try {
            $this->attendanceService->startBreak(Auth::guard('employee')->id());
            return back()->with('success', 'Break started at ' . now()->format('h:i A'));
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function breakIn()
    {
        try {
            $break = $this->attendanceService->endBreak(Auth::guard('employee')->id());
            return back()->with('success', 'Break ended. Duration: ' . $break->duration_label);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    private function getTodayForEmployee(int $employeeId)
    {
        return $this->attendanceService->getTodayStats($employeeId);
    }
}
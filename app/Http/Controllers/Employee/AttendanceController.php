<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Services\AttendanceService;
use App\Services\AttendanceTimeCalculator;
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

    public function checkOut(Request $request)
    {
        try {
            $employee   = Auth::guard('employee')->user();
            $attendance = $this->attendanceService->getTodayForEmployee($employee->id);

            if (!$attendance) {
                return back()->with('error', 'No check-in record found for today.');
            }

            if ($attendance->check_out) {
                return back()->with('error', 'You have already checked out today.');
            }

            $stats      = $this->attendanceService->getLiveStats($attendance);
            $isComplete = $stats['is_complete'];

            if (!$isComplete) {
                $request->validate([
                    'early_reason' => 'required|string|min:5|max:300',
                ], [
                    'early_reason.required' => 'Please provide a reason for early checkout.',
                    'early_reason.min'      => 'Reason must be at least 5 characters.',
                ]);
            }

            $att = $this->attendanceService->checkOut(
                $employee->id,
                $isComplete ? null : $request->early_reason
            );

            $msg = 'Checked out at ' . now()->format('h:i A') . '. Net hours: ' . $att->net_hours_worked;
            if (!$isComplete) {
                $msg .= ' (Early checkout recorded)';
            }

            return back()->with('success', $msg);

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function breakOut(Request $request)
    {
        try {
            $break = $this->attendanceService->startBreak(Auth::guard('employee')->id());
            $attendance = $this->attendanceService->getTodayForEmployee(Auth::guard('employee')->id());
            $stats = $this->attendanceService->getLiveStats($attendance);

            if ($request->expectsJson()) {
                return response()->json([
                    'success'     => true,
                    'message'     => 'Break started at ' . $break->break_out->format('h:i A'),
                    'server_time' => now()->timestamp,
                    ...$this->livePayload($attendance, $stats),
                ]);
            }

            return back()->with('success', 'Break started at ' . $break->break_out->format('h:i A') . '. Enjoy your break!');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return back()->with('error', $e->getMessage());
        }
    }

    public function breakIn(Request $request)
    {
        try {
            $break = $this->attendanceService->endBreak(Auth::guard('employee')->id());
            $attendance = $this->attendanceService->getTodayForEmployee(Auth::guard('employee')->id());
            $stats = $this->attendanceService->getLiveStats($attendance);

            if ($request->expectsJson()) {
                return response()->json([
                    'success'     => true,
                    'message'     => 'Break ended. Duration: ' . $break->duration_label,
                    'server_time' => now()->timestamp,
                    ...$this->livePayload($attendance, $stats),
                ]);
            }

            return back()->with('success', 'Break ended. Duration: ' . $break->duration_label);
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return back()->with('error', $e->getMessage());
        }
    }

    public function liveStatus()
    {
        $employee   = Auth::guard('employee')->user();
        $attendance = $this->attendanceService->getTodayForEmployee($employee->id);

        if (!$attendance) {
            return response()->json(['checked_in' => false]);
        }

        $stats = $this->attendanceService->getLiveStats($attendance);

        return response()->json([
            'checked_in'  => true,
            'checked_out' => $attendance->check_out !== null,
            'check_in_time' => $attendance->check_in->format('h:i A'),
            'server_time' => now()->timestamp,
            ...$this->livePayload($attendance, $stats),
        ]);
    }

    private function livePayload(?\App\Models\Attendance $attendance, array $stats): array
    {
        return [
            'on_break'                => $stats['on_break'],
            'net_seconds'             => $stats['net_seconds'],
            'total_break_seconds'     => $stats['total_break_seconds'],
            'completed_break_seconds' => $stats['completed_break_seconds'],
            'active_break_seconds'    => $stats['active_break_seconds'],
            'remaining_seconds'       => $stats['remaining_seconds'],
            'progress_percent'        => $stats['progress_percent'],
            'is_complete'             => $stats['is_complete'],
            'break_count'             => $stats['break_count'],
            'active_break_since'      => $stats['active_break_since'],
            'target_seconds'          => AttendanceTimeCalculator::TARGET_SECONDS,
        ];
    }
}

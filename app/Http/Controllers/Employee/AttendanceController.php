<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AttendanceBreak;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $employee = Auth::guard('employee')->user();
        $month    = $request->get('month', now()->format('Y-m'));

        [$year, $mon] = explode('-', $month);

        $attendances = Attendance::where('employee_id', $employee->id)
            ->whereYear('date', $year)
            ->whereMonth('date', $mon)
            ->orderBy('date')
            ->get();

        $today = Attendance::where('employee_id', $employee->id)
            ->where('date', today()->toDateString())
            ->first();

        $summary = [
            'present'  => $attendances->where('status', 'present')->count(),
            'absent'   => $attendances->where('status', 'absent')->count(),
            'half_day' => $attendances->where('status', 'half_day')->count(),
            'on_leave' => $attendances->where('status', 'on_leave')->count(),
        ];

        return view('employee.attendance.index', compact('attendances', 'today', 'summary', 'month'));
    }

    public function checkIn()
    {
        $employee = Auth::guard('employee')->user();
        $existing = Attendance::where('employee_id', $employee->id)
            ->where('date', today()->toDateString())->first();

        if ($existing) {
            return back()->with('error', 'You have already checked in today.');
        }

        Attendance::create([
            'employee_id' => $employee->id,
            'date'        => today()->toDateString(),
            'check_in'    => now(),
            'status'      => 'present',
            'marked_by'   => 'self',
        ]);

        return back()->with('success', 'Checked in at ' . now()->format('h:i A'));
    }

    public function checkOut()
    {
        $employee   = Auth::guard('employee')->user();
        $attendance = Attendance::where('employee_id', $employee->id)
            ->where('date', today()->toDateString())
            ->whereNull('check_out')
            ->first();

        if (!$attendance) {
            return back()->with('error', 'No active check-in found for today.');
        }

        $attendance->update(['check_out' => now()]);
        return back()->with('success', 'Checked out at ' . now()->format('h:i A') . '. Hours worked: ' . $attendance->fresh()->hours_worked);
    }

    public function breakOut()
    {
        $employee   = Auth::guard('employee')->user();
        $attendance = Attendance::where('employee_id', $employee->id)
            ->where('date', today()->toDateString())
            ->whereNull('check_out')
            ->first();
    
        if (!$attendance) {
            return back()->with('error', 'You must check in before taking a break.');
        }
    
        // Check if already on break
        if ($attendance->on_break) {
            return back()->with('error', 'You are already on a break.');
        }
    
        AttendanceBreak::create([
            'attendance_id' => $attendance->id,
            'employee_id'   => $employee->id,
            'break_out'     => now(),
            'marked_by'     => 'self',
        ]);
    
        return back()->with('success', 'Break started at ' . now()->format('h:i A') . '. Enjoy your break!');
    }
    
    public function breakIn()
    {
        $employee   = Auth::guard('employee')->user();
        $attendance = Attendance::where('employee_id', $employee->id)
            ->where('date', today()->toDateString())
            ->first();
    
        if (!$attendance) {
            return back()->with('error', 'No attendance record found for today.');
        }
    
        $activeBreak = AttendanceBreak::where('attendance_id', $attendance->id)
            ->whereNull('break_in')
            ->latest()
            ->first();
    
        if (!$activeBreak) {
            return back()->with('error', 'No active break found.');
        }
    
        $activeBreak->update(['break_in' => now()]);
    
        $duration = $activeBreak->fresh()->duration_label;
        return back()->with('success', 'Break ended. Duration: ' . $duration);
    }
}

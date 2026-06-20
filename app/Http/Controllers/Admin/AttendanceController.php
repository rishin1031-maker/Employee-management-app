<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Http\Request;
use App\Models\AttendanceBreak;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $activeView = $request->get('view', 'daily');
        $date       = $request->get('date', today()->toDateString());
        $month      = $request->get('month', now()->format('Y-m'));
    
        $employees = Employee::where('status', 'active')
            ->with(['attendances' => fn($q) => $q->where('date', $date)->with('breaks')])
            ->orderBy('name')
            ->get();
    
        // Monthly report data
        $monthlyReport = [];
        if ($activeView === 'monthly') {
            [$year, $mon] = explode('-', $month);
            $workingDays = \Carbon\Carbon::createFromDate($year, $mon, 1)->daysInMonth;
    
            foreach ($employees as $emp) {
                $records = \App\Models\Attendance::where('employee_id', $emp->id)
                    ->whereYear('date', $year)
                    ->whereMonth('date', $mon)
                    ->get();
    
                $monthlyReport[] = [
                    'employee'   => $emp,
                    'present'    => $records->where('status', 'present')->count(),
                    'absent'     => $records->where('status', 'absent')->count(),
                    'half_day'   => $records->where('status', 'half_day')->count(),
                    'on_leave'   => $records->where('status', 'on_leave')->count(),
                    'not_marked' => $workingDays - $records->count(),
                ];
            }
        }
    
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
            'check_out'   => 'nullable|date_format:H:i|after:check_in',
            'note'        => 'nullable|string|max:255',
        ]);

        $checkIn  = $request->check_in  ? $request->date . ' ' . $request->check_in  . ':00' : null;
        $checkOut = $request->check_out ? $request->date . ' ' . $request->check_out . ':00' : null;

        Attendance::updateOrCreate(
            ['employee_id' => $request->employee_id, 'date' => $request->date],
            [
                'check_in'  => $checkIn,
                'check_out' => $checkOut,
                'status'    => $request->status,
                'marked_by' => 'admin',
                'note'      => $request->note,
            ]
        );

        return back()->with('success', 'Attendance marked successfully.');
    }

    public function resetPassword(Request $request, Employee $employee)
    {
        $request->validate([
            'new_password' => 'required|min:8|confirmed',
        ]);

        $employee->update([
            'password'             => \Illuminate\Support\Facades\Hash::make($request->new_password),
            'must_change_password' => true,
        ]);

        return back()->with('success', "Password reset for {$employee->name}. They will be prompted to change it on next login.");
    }

    public function addBreak(Request $request)
    {
        $request->validate([
            'attendance_id' => 'required|exists:attendances,id',
            'break_out'     => 'required|date_format:H:i',
            'break_in'      => 'nullable|date_format:H:i|after:break_out',
        ]);

        $attendance = \App\Models\Attendance::findOrFail($request->attendance_id);
        $date       = $attendance->date->toDateString();

        \App\Models\AttendanceBreak::create([
            'attendance_id' => $attendance->id,
            'employee_id'   => $attendance->employee_id,
            'break_out'     => $date . ' ' . $request->break_out . ':00',
            'break_in'      => $request->break_in ? $date . ' ' . $request->break_in . ':00' : null,
            'marked_by'     => 'admin',
        ]);

        return back()->with('success', 'Break added successfully.');
    }

    public function deleteBreak(\App\Models\AttendanceBreak $break)
    {
        $break->delete();
        return back()->with('success', 'Break removed.');
    }
}

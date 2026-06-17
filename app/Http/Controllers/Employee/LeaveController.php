<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class LeaveController extends Controller
{
    public function index()
    {
        $employee = Auth::guard('employee')->user();
        $leaves   = $employee->leaveRequests()->latest()->paginate(10);
        $balance  = $employee->leaveBalance ?? $this->createBalance($employee->id);

        return view('employee.leave.index', compact('leaves', 'balance'));
    }

    public function create()
    {
        $employee = Auth::guard('employee')->user();
        $balance  = $employee->leaveBalance ?? $this->createBalance($employee->id);

        return view('employee.leave.create', compact('balance'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'type'      => 'required|in:casual,sick,annual',
            'from_date' => 'required|date|after_or_equal:today',
            'to_date'   => 'required|date|after_or_equal:from_date',
            'reason'    => 'required|string|max:500',
        ]);

        $employee  = Auth::guard('employee')->user();
        $from      = Carbon::parse($request->from_date);
        $to        = Carbon::parse($request->to_date);
        $days      = $from->diffInWeekdays($to) + 1;
        $balance   = $employee->leaveBalance ?? $this->createBalance($employee->id);

        $remaining = $balance->remaining[$request->type];
        if ($days > $remaining) {
            return back()->withErrors(['type' => "Insufficient {$request->type} leave balance. You have {$remaining} day(s) remaining."])
                         ->withInput();
        }

        // Check for overlapping requests
        $overlap = LeaveRequest::where('employee_id', $employee->id)
            ->whereIn('status', ['pending', 'approved'])
            ->where(function ($q) use ($request) {
                $q->whereBetween('from_date', [$request->from_date, $request->to_date])
                  ->orWhereBetween('to_date',   [$request->from_date, $request->to_date]);
            })->exists();

        if ($overlap) {
            return back()->withErrors(['from_date' => 'You already have a leave request for this date range.'])
                         ->withInput();
        }

        LeaveRequest::create([
            'employee_id' => $employee->id,
            'type'        => $request->type,
            'from_date'   => $request->from_date,
            'to_date'     => $request->to_date,
            'days'        => $days,
            'reason'      => $request->reason,
            'status'      => 'pending',
        ]);

        return redirect()->route('employee.leave.index')
            ->with('success', 'Leave request submitted successfully.');
    }

    private function createBalance(int $employeeId): LeaveBalance
    {
        return LeaveBalance::create([
            'employee_id'  => $employeeId,
            'year'         => now()->year,
            'casual_total' => 12, 'casual_used' => 0,
            'sick_total'   => 10, 'sick_used'   => 0,
            'annual_total' => 15, 'annual_used' => 0,
        ]);
    }
    public function cancel(LeaveRequest $leave)
    {
        $employee = Auth::guard('employee')->user();
    
        if ($leave->employee_id !== $employee->id) {
            return back()->with('error', 'Unauthorized action.');
        }
    
        if ($leave->status !== 'pending') {
            return back()->with('error', 'Only pending leave requests can be cancelled.');
        }
    
        $leave->delete();
    
        return back()->with('success', 'Leave request cancelled successfully.');
    }
}

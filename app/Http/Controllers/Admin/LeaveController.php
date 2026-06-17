<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class LeaveController extends Controller
{
    public function index(Request $request)
    {
        $query = LeaveRequest::with(['employee.department', 'actionedBy']);

        if ($request->status) {
            $query->where('status', $request->status);
        }
        if ($request->employee_id) {
            $query->where('employee_id', $request->employee_id);
        }
        if ($request->type) {
            $query->where('type', $request->type);
        }

        $leaves    = $query->latest()->paginate(15)->withQueryString();
        $employees = Employee::orderBy('name')->get();

        return view('admin.leave.index', compact('leaves', 'employees'));
    }

    public function show(LeaveRequest $leave)
    {
        $leave->load(['employee.department', 'employee.designation', 'actionedBy']);
        return view('admin.leave.show', compact('leave'));
    }

    public function approve(Request $request, LeaveRequest $leave)
    {
        $request->validate(['admin_note' => 'nullable|string|max:500']);

        if ($leave->status !== 'pending') {
            return back()->with('error', 'This request has already been actioned.');
        }

        $leave->update([
            'status'      => 'approved',
            'admin_note'  => $request->admin_note,
            'actioned_by' => Auth::guard('admin')->id(),
            'actioned_at' => now(),
        ]);

        // Deduct from leave balance
        $balance = LeaveBalance::firstOrCreate(
            ['employee_id' => $leave->employee_id, 'year' => $leave->from_date->year],
            ['casual_total' => 12, 'sick_total' => 10, 'annual_total' => 15]
        );

        $col = $leave->type . '_used';
        $balance->increment($col, $leave->days);

        return redirect()->route('admin.leave.index')->with('success', 'Leave approved successfully.');
    }

    public function reject(Request $request, LeaveRequest $leave)
    {
        $request->validate(['admin_note' => 'nullable|string|max:500']);

        if ($leave->status !== 'pending') {
            return back()->with('error', 'This request has already been actioned.');
        }

        $leave->update([
            'status'      => 'rejected',
            'admin_note'  => $request->admin_note,
            'actioned_by' => Auth::guard('admin')->id(),
            'actioned_at' => now(),
        ]);

        return redirect()->route('admin.leave.index')->with('success', 'Leave rejected.');
    }

    public function create()
    {
        $employees = Employee::where('status', 'active')->orderBy('name')->get();
        return view('admin.leave.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'type'        => 'required|in:casual,sick,annual',
            'from_date'   => 'required|date',
            'to_date'     => 'required|date|after_or_equal:from_date',
            'reason'      => 'required|string|max:500',
            'status'      => 'required|in:pending,approved,rejected',
        ]);

        $days = Carbon::parse($request->from_date)->diffInWeekdays(Carbon::parse($request->to_date)) + 1;

        $leave = LeaveRequest::create([
            'employee_id'      => $request->employee_id,
            'type'             => $request->type,
            'from_date'        => $request->from_date,
            'to_date'          => $request->to_date,
            'days'             => $days,
            'reason'           => $request->reason,
            'status'           => $request->status,
            'actioned_by'      => $request->status !== 'pending' ? Auth::guard('admin')->id() : null,
            'actioned_at'      => $request->status !== 'pending' ? now() : null,
            'created_by_admin' => true,
        ]);

        if ($request->status === 'approved') {
            $balance = LeaveBalance::firstOrCreate(
                ['employee_id' => $request->employee_id, 'year' => now()->year],
                ['casual_total' => 12, 'sick_total' => 10, 'annual_total' => 15]
            );
            $balance->increment($request->type . '_used', $days);
        }

        return redirect()->route('admin.leave.index')->with('success', 'Leave created successfully.');
    }
}

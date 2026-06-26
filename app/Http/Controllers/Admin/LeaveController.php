<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use App\Services\EmployeeService;
use App\Services\LeaveService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LeaveController extends Controller
{
    public function __construct(
        private LeaveService    $leaveService,
        private EmployeeService $employeeService,
    ) {}

    public function index()
    {
        $leaves    = $this->leaveService->getPaginatedForAdmin(request()->all());
        $employees = $this->employeeService->getActiveEmployees() ?? collect();
        return view('admin.leave.index', compact('leaves', 'employees'));
    }

    public function show(LeaveRequest $leave)
    {
        $leave = $this->leaveService->getLeaveForAdminShow($leave->id);
        return view('admin.leave.show', compact('leave'));
    }

    public function approve(Request $request, LeaveRequest $leave)
    {
        $request->validate(['admin_note' => 'nullable|string|max:500']);

        try {
            $this->leaveService->approveLeave($leave->id, Auth::guard('admin')->id(), $request->admin_note);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('admin.leave.index')->with('success', 'Leave approved successfully.');
    }

    public function reject(Request $request, LeaveRequest $leave)
    {
        $request->validate(['admin_note' => 'nullable|string|max:500']);

        try {
            $this->leaveService->rejectLeave($leave->id, Auth::guard('admin')->id(), $request->admin_note);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('admin.leave.index')->with('success', 'Leave rejected.');
    }

    public function create()
    {
        $employees = $this->employeeService->getActiveEmployees() ?? collect();
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

        $this->leaveService->createLeaveByAdmin($request->all(), Auth::guard('admin')->id());
        return redirect()->route('admin.leave.index')->with('success', 'Leave created successfully.');
    }
}

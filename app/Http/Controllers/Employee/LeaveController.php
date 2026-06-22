<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use App\Services\LeaveService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LeaveController extends Controller
{
    public function __construct(private LeaveService $leaveService) {}

    public function index()
    {
        $employee = Auth::guard('employee')->user();
        $leaves   = $this->leaveService->getEmployeeLeaves($employee->id);
        $balance  = $this->leaveService->getOrCreateBalance($employee->id);

        return view('employee.leave.index', compact('leaves', 'balance'));
    }

    public function create()
    {
        $employee = Auth::guard('employee')->user();
        $balance  = $this->leaveService->getOrCreateBalance($employee->id);

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

        try {
            $this->leaveService->applyLeave(
                Auth::guard('employee')->id(),
                $request->only(['type', 'from_date', 'to_date', 'reason'])
            );

            return redirect()->route('employee.leave.index')
                ->with('success', 'Leave request submitted successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['type' => $e->getMessage()])->withInput();
        }
    }

    public function cancel(LeaveRequest $leave)
    {
        try {
            $this->leaveService->cancelLeave($leave->id, Auth::guard('employee')->id());
            return back()->with('success', 'Leave request cancelled successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
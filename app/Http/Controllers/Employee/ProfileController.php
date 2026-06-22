<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Services\EmployeeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function __construct(private EmployeeService $employeeService) {}

    public function index()
    {
        $employee = Auth::guard('employee')->user()
            ->load(['department', 'designation', 'salary', 'salaryHistories', 'leaveBalance']);

        return view('employee.profile.index', compact('employee'));
    }

    public function updatePhone(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|max:20|regex:/^[0-9+\-\s()]+$/',
        ]);

        $this->employeeService->updatePhone(Auth::guard('employee')->user(), $request->phone);
        return back()->with('success', 'Phone number updated successfully.');
    }
}
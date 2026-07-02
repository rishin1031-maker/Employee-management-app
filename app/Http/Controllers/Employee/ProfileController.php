<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Services\EmployeeService;
use App\Services\SalaryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function __construct(
        private EmployeeService $employeeService,
        private SalaryService $salaryService,
    ) {}

    public function index()
    {
        $employee = $this->employeeService->getEmployeeForProfile(
            Auth::guard('employee')->id()
        );

        $earnedSalary = $this->salaryService->getEarnedSalaryForEmployee(
            $employee,
            now()->format('Y-m')
        );

        return view('employee.profile.index', compact('employee', 'earnedSalary'));
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

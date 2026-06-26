<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use App\Services\EmployeeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function __construct(
        private EmployeeService $employeeService,
        private AuthService $authService,
    ) {}

    public function showChangePassword()
    {
        return view('employee.auth.change-password');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password'         => 'required|min:8|confirmed',
        ]);

        $employee = Auth::guard('employee')->user();

        if (!$this->authService->verifyEmployeePassword($employee, $request->current_password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        $this->employeeService->changePassword($employee, $request->password);

        return redirect()->route('employee.dashboard')
            ->with('success', 'Password changed successfully.');
    }

    public function logout(Request $request)
    {
        Auth::guard('employee')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login')->with('success', 'Logged out successfully.');
    }
}

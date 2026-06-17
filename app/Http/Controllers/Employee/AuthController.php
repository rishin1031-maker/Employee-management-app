<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showChangePassword()
    {
        return view('employee.auth.change-password');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password'         => 'required|min:8|confirmed',
        ], [
            'password.confirmed' => 'New password and confirmation do not match.',
            'password.min'       => 'Password must be at least 8 characters.',
        ]);

        $employee = Auth::guard('employee')->user();

        if (!Hash::check($request->current_password, $employee->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        $employee->update([
            'password'             => Hash::make($request->password),
            'must_change_password' => false,
        ]);

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

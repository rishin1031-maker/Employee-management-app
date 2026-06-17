<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::guard('admin')->check()) {
            return redirect()->route('admin.dashboard');
        }
        if (Auth::guard('employee')->check()) {
            return redirect()->route('employee.dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'login'    => 'required|string',
            'password' => 'required',
        ]);

        $login    = $request->input('login');
        $password = $request->input('password');
        $remember = $request->boolean('remember');

        // Try admin login (by email)
        if (filter_var($login, FILTER_VALIDATE_EMAIL)) {
            if (Auth::guard('admin')->attempt(['email' => $login, 'password' => $password], $remember)) {
                $request->session()->regenerate();
                return redirect()->route('admin.dashboard')->with('success', 'Welcome back!');
            }
        }

        // Try employee login (by employee_id like EMP001)
        if (preg_match('/^EMP\d+$/i', strtoupper($login))) {
            $employee = Employee::where('employee_id', strtoupper($login))->first();
            if ($employee && Auth::guard('employee')->attempt(
                ['employee_id' => strtoupper($login), 'password' => $password], $remember
            )) {
                $request->session()->regenerate();
                $employee->update(['last_login_at' => now()]);
                return redirect()->route('employee.dashboard');
            }
        }

        return back()
            ->withErrors(['login' => 'Invalid credentials. Use your email (admin) or Employee ID (e.g. EMP001).'])
            ->withInput($request->only('login'));
    }

    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();
        Auth::guard('employee')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login')->with('success', 'Logged out successfully.');
    }
}

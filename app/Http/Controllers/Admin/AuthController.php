<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ActivityLogService;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function __construct(
        private AuthService $authService,
        private ActivityLogService $activityLog,
    ) {}

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

        $guard = $this->authService->resolveWebLoginGuard(
            $request->input('login'),
            $request->input('password'),
            $request->boolean('remember')
        );

        if ($guard === 'admin') {
            $request->session()->regenerate();

            return redirect()->route('admin.dashboard')->with('success', 'Welcome back!');
        }

        if ($guard === 'employee') {
            $request->session()->regenerate();

            return redirect()->route('employee.dashboard');
        }

        return back()
            ->withErrors(['login' => 'Invalid credentials. Use your email (admin) or Employee ID (e.g. EMP001).'])
            ->withInput($request->only('login'));
    }

    public function logout(Request $request)
    {
        if ($admin = Auth::guard('admin')->user()) {
            $this->activityLog->logAuth('logout', $admin, 'admin');
        }

        if ($employee = Auth::guard('employee')->user()) {
            $this->activityLog->logAuth('logout', $employee, 'employee');
        }

        Auth::guard('admin')->logout();
        Auth::guard('employee')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login')->with('success', 'Logged out successfully.');
    }
}

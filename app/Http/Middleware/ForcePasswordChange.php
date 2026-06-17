<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ForcePasswordChange
{
    public function handle(Request $request, Closure $next)
    {
        $employee = Auth::guard('employee')->user();

        if ($employee && $employee->must_change_password) {
            // Allow only the change-password route
            if (!$request->routeIs('employee.password.change', 'employee.password.update', 'admin.logout', 'employee.logout')) {
                return redirect()->route('employee.password.change')
                    ->with('warning', 'You must change your password before continuing.');
            }
        }

        return $next($request);
    }
}
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiForcePasswordChange
{
    public function handle(Request $request, Closure $next): Response
    {
        $employee = auth('api_employee')->user();

        if ($employee && $employee->must_change_password) {
            return response()->json([
                'success' => false,
                'message' => 'You must change your password before continuing.',
                'code'    => 'password_change_required',
            ], 403);
        }

        return $next($request);
    }
}

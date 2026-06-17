<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $employee = Auth::guard('employee')->user()
            ->load(['department', 'designation', 'salary', 'leaveBalance', 'todayAttendance']);

        $leaveStats = [
            'pending'  => $employee->leaveRequests()->where('status', 'pending')->count(),
            'approved' => $employee->leaveRequests()->where('status', 'approved')->count(),
            'rejected' => $employee->leaveRequests()->where('status', 'rejected')->count(),
        ];

        $recentLeaves     = $employee->leaveRequests()->latest()->take(5)->get();
        $recentAttendance = $employee->attendances()
                                ->orderByDesc('date')->take(7)->get();

        return view('employee.dashboard.index', compact(
            'employee', 'leaveStats', 'recentLeaves', 'recentAttendance'
        ));
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;

class DashboardController extends Controller
{
    public function index()
    {
        $totalEmployees    = Employee::count();
        $activeEmployees   = Employee::where('status', 'active')->count();
        $inactiveEmployees = Employee::where('status', 'inactive')->count();
        $totalDepartments  = Department::count();
        $totalDesignations = Designation::count();
        $recentEmployees   = Employee::with(['department', 'designation'])
                                ->latest()->take(5)->get();

        return view('dashboard.index', compact(
            'totalEmployees', 'activeEmployees', 'inactiveEmployees',
            'totalDepartments', 'totalDesignations', 'recentEmployees'
        ));
    }
}
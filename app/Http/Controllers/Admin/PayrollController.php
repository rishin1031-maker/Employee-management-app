<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Employee;
use App\Models\SalaryHistory;
use Illuminate\Http\Request;

class PayrollController extends Controller
{
    public function index(Request $request)
    {
        $year = $request->get('year', now()->year);

        // Monthly total payroll for the year
        $monthlyTotals = [];
        for ($m = 1; $m <= 12; $m++) {
            $total = SalaryHistory::whereYear('effective_from', $year)
                ->whereMonth('effective_from', $m)
                ->sum('net_salary');
            $monthlyTotals[] = round($total, 2);
        }

        // Employee-wise salary comparison (current salary)
        $employeeSalaries = Employee::with('salary')
            ->where('status', 'active')
            ->whereHas('salary')
            ->get()
            ->map(fn($e) => [
                'name'   => $e->name,
                'emp_id' => $e->employee_id,
                'net'    => round($e->salary->net_salary, 2),
                'gross'  => round($e->salary->gross_salary, 2),
            ])
            ->sortByDesc('net')
            ->values();

        // Department-wise payroll cost
        $deptPayroll = Department::with(['employees.salary'])
            ->get()
            ->map(fn($d) => [
                'name' => $d->name,
                'total' => round(
                    $d->employees->filter(fn($e) => $e->salary)
                                 ->sum(fn($e) => $e->salary->net_salary), 2
                ),
            ])
            ->sortByDesc('total')
            ->values();

        // Monthly table data (last 6 months)
        $monthlyTable = SalaryHistory::with('employee')
            ->whereYear('effective_from', $year)
            ->orderByDesc('effective_from')
            ->get()
            ->groupBy(fn($h) => $h->effective_from->format('Y-m'));

        $years = range(now()->year - 3, now()->year);

        return view('admin.payroll.index', compact(
            'monthlyTotals', 'employeeSalaries', 'deptPayroll',
            'monthlyTable', 'year', 'years'
        ));
    }
}

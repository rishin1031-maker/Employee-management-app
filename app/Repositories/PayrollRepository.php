<?php

namespace App\Repositories;

use App\Contracts\Repositories\PayrollRepositoryInterface;
use App\Models\Department;
use App\Models\Employee;
use App\Models\SalaryHistory;
use Illuminate\Support\Collection;

class PayrollRepository implements PayrollRepositoryInterface
{
    public function getMonthlyTotals(int $year): array
    {
        $totals = [];
        for ($m = 1; $m <= 12; $m++) {
            $totals[] = round(
                SalaryHistory::whereYear('effective_from', $year)
                             ->whereMonth('effective_from', $m)
                             ->sum('net_salary'), 2
            );
        }
        return $totals;
    }

    public function getEmployeeSalaryComparison(): Collection
    {
        return Employee::with('salary')
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
    }

    public function getDepartmentPayrollCost(): Collection
    {
        return Department::with(['employees.salary'])
                         ->get()
                         ->map(fn($d) => [
                             'name'  => $d->name,
                             'total' => round(
                                 $d->employees->filter(fn($e) => $e->salary)
                                              ->sum(fn($e) => $e->salary->net_salary), 2
                             ),
                         ])
                         ->sortByDesc('total')
                         ->values();
    }

    public function getMonthlyChangeLog(int $year): Collection
    {
        return SalaryHistory::with('employee')
                            ->whereYear('effective_from', $year)
                            ->orderByDesc('effective_from')
                            ->get()
                            ->groupBy(fn($h) => $h->effective_from->format('Y-m'));
    }
}
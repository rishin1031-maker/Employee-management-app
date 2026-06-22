<?php

namespace App\Contracts\Repositories;

use Illuminate\Support\Collection;

interface PayrollRepositoryInterface
{
    public function getMonthlyTotals(int $year): array;
    public function getEmployeeSalaryComparison(): Collection;
    public function getDepartmentPayrollCost(): Collection;
    public function getMonthlyChangeLog(int $year): Collection;
}
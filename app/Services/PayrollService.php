<?php

namespace App\Services;

use App\Contracts\Repositories\PayrollRepositoryInterface;

class PayrollService
{
    public function __construct(
        private PayrollRepositoryInterface $payrollRepo
    ) {}

    public function getPayrollData(int $year): array
    {
        return [
            'monthlyTotals'    => $this->payrollRepo->getMonthlyTotals($year),
            'employeeSalaries' => $this->payrollRepo->getEmployeeSalaryComparison(),
            'deptPayroll'      => $this->payrollRepo->getDepartmentPayrollCost(),
            'monthlyTable'     => $this->payrollRepo->getMonthlyChangeLog($year),
        ];
    }
}
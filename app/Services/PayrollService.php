<?php

namespace App\Services;

use App\Contracts\Repositories\PayrollRepositoryInterface;

class PayrollService
{
    public function __construct(
        private PayrollRepositoryInterface $payrollRepo,
        private SalaryService $salaryService,
    ) {}

    public function getPayrollData(int $year, string $month): array
    {
        $earnedPayroll = $this->salaryService->getEarnedPayrollForMonth($month);

        return [
            'monthlyTotals'        => $this->salaryService->getYearlyEarnedTotals($year),
            'employeeSalaries'     => $earnedPayroll['employees']->map(fn ($row) => [
                'name'        => $row['name'],
                'emp_id'      => $row['emp_code'],
                'net'         => $row['earned_net'],
                'gross'       => $row['earned_gross'],
                'base_net'    => $row['base_net'],
                'work_hours'  => $row['work_hours'],
                'progress'    => $row['progress_percent'],
            ])->values(),
            'deptPayroll'          => $this->payrollRepo->getDepartmentEarnedPayrollCost($earnedPayroll['employees']),
            'monthlyTable'         => $this->payrollRepo->getMonthlyChangeLog($year),
            'earnedPayroll'        => $earnedPayroll['employees'],
            'totalEarnedNet'       => $earnedPayroll['total_earned_net'],
        ];
    }
}

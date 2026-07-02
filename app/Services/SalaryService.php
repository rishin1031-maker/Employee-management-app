<?php

namespace App\Services;

use App\Contracts\Repositories\SalaryRepositoryInterface;
use App\Jobs\SendSalaryUpdatedJob;
use App\Models\Employee;
use App\Models\Salary;
use Illuminate\Pagination\LengthAwarePaginator;

class SalaryService
{
    public function __construct(
        private SalaryRepositoryInterface $salaryRepo,
        private SalaryCalculator $salaryCalculator,
    ) {}

    public function getEmployeesWithSalary(array $filters): LengthAwarePaginator
    {
        return $this->salaryRepo->getEmployeesWithSalary($filters);
    }

    public function getCurrentSalary(int $employeeId): ?Salary
    {
        return $this->salaryRepo->getCurrentSalary($employeeId);
    }

    public function getEarnedSalaryForEmployee(Employee $employee, string $month): ?array
    {
        $salary = $employee->relationLoaded('salary')
            ? $employee->salary
            : $this->getCurrentSalary($employee->id);

        if (!$salary) {
            return null;
        }

        $workHours = $this->salaryCalculator->getMonthlyWorkHoursForEmployee($employee->id, $month);

        return $this->salaryCalculator->calculateFromWorkHours($salary, $workHours);
    }

    public function getEarnedPayrollForMonth(string $month): array
    {
        $employees = $this->salaryRepo->getActiveEmployeesWithSalary();
        $rows      = [];
        $totalNet  = 0;

        foreach ($employees as $employee) {
            $earned = $this->getEarnedSalaryForEmployee($employee, $month);
            if (!$earned) {
                continue;
            }

            $rows[] = array_merge($earned, [
                'employee_id' => $employee->id,
                'name'        => $employee->name,
                'emp_code'    => $employee->employee_id,
                'department'  => $employee->department?->name ?? '—',
            ]);

            $totalNet += $earned['earned_net'];
        }

        return [
            'employees'        => collect($rows)->sortByDesc('earned_net')->values(),
            'total_earned_net' => round($totalNet, 2),
        ];
    }

    public function getYearlyEarnedTotals(int $year): array
    {
        $totals = [];

        for ($month = 1; $month <= 12; $month++) {
            $monthKey = sprintf('%04d-%02d', $year, $month);

            if ($monthKey > now()->format('Y-m')) {
                $totals[] = 0;
                continue;
            }

            $totals[] = $this->getEarnedPayrollForMonth($monthKey)['total_earned_net'];
        }

        return $totals;
    }

    public function updateSalary(Employee $employee, array $data): Salary
    {
        $fields = [
            'hra',
            'transport',
            'medical',
            'pf_deduction',
            'tax_deduction',
            'other_allowance',
            'other_deduction',
        ];

        foreach ($fields as $field) {
            $data[$field] = $data[$field] ?? 0;
        }

        $salary = $this->salaryRepo->upsertSalary($employee->id, $data);

        $gross = $data['basic']
            + $data['hra']
            + $data['transport']
            + $data['medical']
            + $data['other_allowance'];
        $net = $gross
            - $data['pf_deduction']
            - $data['tax_deduction']
            - $data['other_deduction'];

        $this->salaryRepo->recordHistory($employee->id, $salary->id, array_merge($data, [
            'gross_salary' => $gross,
            'net_salary'   => $net,
        ]));

        SendSalaryUpdatedJob::dispatch($employee, $salary->fresh());

        return $salary;
    }

    public function getHistory(Employee $employee): LengthAwarePaginator
    {
        return $this->salaryRepo->getHistoryForEmployee($employee->id);
    }

    public function getApiShowData(Employee $employee): array
    {
        return [
            'current' => $this->getCurrentSalary($employee->id),
            'history' => $this->getHistory($employee),
        ];
    }

    public function getManagePageData(Employee $employee): array
    {
        return [
            'current' => $this->getCurrentSalary($employee->id),
            'history' => $this->getHistory($employee)->getCollection()->take(5),
        ];
    }
}

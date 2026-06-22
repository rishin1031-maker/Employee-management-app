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
        private SalaryRepositoryInterface $salaryRepo
    ) {}

    public function getEmployeesWithSalary(array $filters): LengthAwarePaginator
    {
        return $this->salaryRepo->getEmployeesWithSalary($filters);
    }

    public function getCurrentSalary(int $employeeId): ?Salary
    {
        return $this->salaryRepo->getCurrentSalary($employeeId);
    }

    public function updateSalary(Employee $employee, array $data): Salary
    {
        // Fill nullable fields with 0
        $fields = ['hra','transport','medical','pf_deduction','tax_deduction','other_allowance','other_deduction'];
        foreach ($fields as $field) {
            $data[$field] = $data[$field] ?? 0;
        }

        $salary = $this->salaryRepo->upsertSalary($employee->id, $data);

        // Compute gross/net
        $gross = $data['basic'] + $data['hra'] + $data['transport'] + $data['medical'] + $data['other_allowance'];
        $net   = $gross - $data['pf_deduction'] - $data['tax_deduction'] - $data['other_deduction'];

        // Record history
        $this->salaryRepo->recordHistory($employee->id, $salary->id, array_merge($data, [
            'gross_salary' => $gross,
            'net_salary'   => $net,
        ]));

        // Notify employee
        SendSalaryUpdatedJob::dispatch($employee, $salary->fresh());

        return $salary;
    }

    public function getHistory(Employee $employee): LengthAwarePaginator
    {
        return $this->salaryRepo->getHistoryForEmployee($employee->id);
    }
}
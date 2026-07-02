<?php

namespace App\Contracts\Repositories;

use App\Models\Employee;
use App\Models\Salary;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface SalaryRepositoryInterface extends BaseRepositoryInterface
{
    public function getEmployeesWithSalary(array $filters = [], int $perPage = 15): LengthAwarePaginator;
    public function getCurrentSalary(int $employeeId): ?Salary;
    public function upsertSalary(int $employeeId, array $data): Salary;
    public function getHistoryForEmployee(int $employeeId, int $perPage = 10): LengthAwarePaginator;
    public function recordHistory(int $employeeId, int $salaryId, array $data): void;
    public function getActiveEmployeesWithSalary(): Collection;
}
<?php

namespace App\Repositories;

use App\Contracts\Repositories\SalaryRepositoryInterface;
use App\Models\Employee;
use App\Models\Salary;
use App\Models\SalaryHistory;
use Illuminate\Pagination\LengthAwarePaginator;

class SalaryRepository extends BaseRepository implements SalaryRepositoryInterface
{
    public function __construct(Salary $model)
    {
        parent::__construct($model);
    }

    public function getEmployeesWithSalary(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Employee::with(['salary', 'department'])->where('status', 'active');

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('employee_id', 'like', '%' . $filters['search'] . '%');
            });
        }

        return $query->orderBy('name')->paginate($perPage)->withQueryString();
    }

    public function getCurrentSalary(int $employeeId): ?Salary
    {
        return Salary::where('employee_id', $employeeId)->first();
    }

    public function upsertSalary(int $employeeId, array $data): Salary
    {
        return Salary::updateOrCreate(
            ['employee_id' => $employeeId],
            array_merge($data, ['employee_id' => $employeeId])
        );
    }

    public function getHistoryForEmployee(int $employeeId, int $perPage = 10): LengthAwarePaginator
    {
        return SalaryHistory::where('employee_id', $employeeId)
                            ->orderByDesc('effective_from')
                            ->paginate($perPage);
    }

    public function recordHistory(int $employeeId, int $salaryId, array $data): void
    {
        SalaryHistory::create(array_merge($data, [
            'employee_id' => $employeeId,
            'salary_id'   => $salaryId,
        ]));
    }
}
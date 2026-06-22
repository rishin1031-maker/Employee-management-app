<?php

namespace App\Repositories;

use App\Contracts\Repositories\EmployeeRepositoryInterface;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class EmployeeRepository extends BaseRepository implements EmployeeRepositoryInterface
{
    public function __construct(Employee $model)
    {
        parent::__construct($model);
    }

    public function getAllWithRelations(): Collection
    {
        return Employee::with(['department', 'designation'])->get();
    }

    public function paginateWithFilters(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        $query = Employee::with(['department', 'designation']);

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('email', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('employee_id', 'like', '%' . $filters['search'] . '%');
            });
        }

        if (!empty($filters['department_id'])) {
            $query->where('department_id', $filters['department_id']);
        }

        if (!empty($filters['designation_id'])) {
            $query->where('designation_id', $filters['designation_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->latest()->paginate($perPage)->withQueryString();
    }

    public function findByEmployeeId(string $employeeId): ?Employee
    {
        return Employee::where('employee_id', $employeeId)->first();
    }

    public function getActiveEmployees(): Collection
    {
        return Employee::where('status', 'active')->orderBy('name')->get();
    }

    public function generateEmployeeId(): string
    {
        $last = Employee::whereNotNull('employee_id')
                        ->orderByDesc('id')
                        ->value('employee_id');

        if (!$last) return 'EMP001';

        $num = (int) substr($last, 3);
        return 'EMP' . str_pad($num + 1, 3, '0', STR_PAD_LEFT);
    }

    public function countByStatus(string $status): int
    {
        return Employee::where('status', $status)->count();
    }

    public function getRecentEmployees(int $limit = 5): Collection
    {
        return Employee::with(['department', 'designation'])
                       ->latest()
                       ->take($limit)
                       ->get();
    }
}
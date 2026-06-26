<?php

namespace App\Contracts\Repositories;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface EmployeeRepositoryInterface extends BaseRepositoryInterface
{
    public function getAllWithRelations(): Collection;
    public function paginateWithFilters(array $filters, int $perPage = 10): LengthAwarePaginator;
    public function findByEmployeeId(string $employeeId): ?Employee;
    public function getActiveEmployees(): Collection;
    public function generateEmployeeId(): string;
    public function countByStatus(string $status): int;
    public function getRecentEmployees(int $limit = 5): Collection;
    public function updateLastLoginAt(int $id): void;
    public function findWithRelations(int $id, array $relations): Employee;
}
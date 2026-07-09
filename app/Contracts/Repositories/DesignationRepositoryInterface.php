<?php

namespace App\Contracts\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface DesignationRepositoryInterface extends BaseRepositoryInterface
{
    public function paginateWithCounts(array $filters = [], int $perPage = 10): LengthAwarePaginator;
    public function getActive(): Collection;
    public function getActiveWithDepartment(): Collection;
    public function toggleStatus(int $id): bool;
    public function syncStatusByDepartment(int $departmentId, string $status): void;
    public function hasEmployees(int $id): bool;
    public function findWithDepartment(int $id): \App\Models\Designation;
    public function findWithDepartmentAndEmployeeCount(int $id): \App\Models\Designation;
}
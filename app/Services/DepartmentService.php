<?php

namespace App\Services;

use App\Contracts\Repositories\DepartmentRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class DepartmentService
{
    public function __construct(
        private DepartmentRepositoryInterface $departmentRepo
    ) {}

    public function getPaginated(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return $this->departmentRepo->paginateWithCounts($filters, $perPage);
    }

    public function getActive(): Collection
    {
        return $this->departmentRepo->getActive();
    }

    public function create(array $data): \App\Models\Department
    {
        return $this->departmentRepo->create($data);
    }

    public function update(int $id, array $data): \App\Models\Department
    {
        return $this->departmentRepo->update($id, $data);
    }

    public function delete(int $id): bool
    {
        if ($this->departmentRepo->hasEmployees($id)) {
            throw new \Exception('Cannot delete department with assigned employees.');
        }
        return $this->departmentRepo->delete($id);
    }

    public function toggleStatus(int $id): bool
    {
        return $this->departmentRepo->toggleStatus($id);
    }
}
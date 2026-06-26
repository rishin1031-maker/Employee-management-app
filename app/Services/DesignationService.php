<?php

namespace App\Services;

use App\Contracts\Repositories\DesignationRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class DesignationService
{
    public function __construct(
        private DesignationRepositoryInterface $designationRepo
    ) {}

    public function getPaginated(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return $this->designationRepo->paginateWithCounts($filters, $perPage);
    }

    public function getActive(): Collection
    {
        return $this->designationRepo->getActive();
    }

    public function getActiveWithDepartment(): Collection
    {
        return $this->designationRepo->getActiveWithDepartment();
    }

    public function create(array $data): \App\Models\Designation
    {
        return $this->designationRepo->create($data);
    }

    public function update(int $id, array $data): \App\Models\Designation
    {
        return $this->designationRepo->update($id, $data);
    }

    public function delete(int $id): bool
    {
        if ($this->designationRepo->hasEmployees($id)) {
            throw new \Exception('Cannot delete designation with assigned employees.');
        }
        return $this->designationRepo->delete($id);
    }

    public function toggleStatus(int $id): bool
    {
        return $this->designationRepo->toggleStatus($id);
    }

    public function getWithDepartment(int $id): \App\Models\Designation
    {
        return $this->designationRepo->findWithDepartment($id);
    }

    public function getWithDetails(int $id): \App\Models\Designation
    {
        return $this->designationRepo->findWithDepartmentAndEmployeeCount($id);
    }
}
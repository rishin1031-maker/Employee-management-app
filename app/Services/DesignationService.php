<?php

namespace App\Services;

use App\Contracts\Repositories\DesignationRepositoryInterface;
use App\Models\Department;
use App\Models\Designation;
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

    public function create(array $data): Designation
    {
        $this->ensureDepartmentAllowsActiveStatus(
            (int) $data['department_id'],
            $data['status'] ?? 'active'
        );

        return $this->designationRepo->create($data);
    }

    public function update(int $id, array $data): Designation
    {
        $designation = $this->designationRepo->findWithDepartment($id);

        $this->ensureDepartmentAllowsActiveStatus(
            (int) ($data['department_id'] ?? $designation->department_id),
            $data['status'] ?? $designation->status
        );

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
        $designation = $this->designationRepo->findWithDepartment($id);
        $newStatus = $designation->status === 'active' ? 'inactive' : 'active';

        $this->ensureDepartmentAllowsActiveStatus((int) $designation->department_id, $newStatus);

        return $this->designationRepo->toggleStatus($id);
    }

    private function ensureDepartmentAllowsActiveStatus(int $departmentId, string $status): void
    {
        if ($status !== 'active') {
            return;
        }

        $department = Department::findOrFail($departmentId);

        if ($department->status !== 'active') {
            throw new \Exception('Cannot activate designation while its department is inactive.');
        }
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
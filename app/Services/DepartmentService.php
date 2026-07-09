<?php

namespace App\Services;

use App\Contracts\Repositories\DepartmentRepositoryInterface;
use App\Contracts\Repositories\DesignationRepositoryInterface;
use App\Models\Department;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class DepartmentService
{
    public function __construct(
        private DepartmentRepositoryInterface $departmentRepo,
        private DesignationRepositoryInterface $designationRepo,
    ) {}

    public function getPaginated(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return $this->departmentRepo->paginateWithCounts($filters, $perPage);
    }

    public function getActive(): Collection
    {
        return $this->departmentRepo->getActive();
    }

    public function create(array $data): Department
    {
        return $this->departmentRepo->create($data);
    }

    public function update(int $id, array $data): Department
    {
        $department = $this->departmentRepo->findOrFail($id);
        $previousStatus = $department->status;

        $updated = $this->departmentRepo->update($id, $data);

        if (isset($data['status']) && $data['status'] !== $previousStatus) {
            $this->designationRepo->syncStatusByDepartment($id, $data['status']);
        }

        return $updated;
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
        $department = $this->departmentRepo->findOrFail($id);
        $newStatus = $department->status === 'active' ? 'inactive' : 'active';

        $this->departmentRepo->update($id, ['status' => $newStatus]);
        $this->designationRepo->syncStatusByDepartment($id, $newStatus);

        return true;
    }
}
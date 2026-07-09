<?php

namespace App\Repositories;

use App\Contracts\Repositories\DesignationRepositoryInterface;
use App\Models\Designation;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class DesignationRepository extends BaseRepository implements DesignationRepositoryInterface
{
    public function __construct(Designation $model)
    {
        parent::__construct($model);
    }

    public function paginateWithCounts(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $query = Designation::with('department')->withCount('employees');

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhereHas('department', fn ($d) => $d->where('name', 'like', '%' . $search . '%'));
            });
        }

        if (!empty($filters['department_id'])) {
            $query->where('department_id', $filters['department_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->latest()->paginate($perPage)->withQueryString();
    }

    public function getActive(): Collection
    {
        return Designation::where('status', 'active')
                          ->whereHas('department', fn ($q) => $q->where('status', 'active'))
                          ->orderBy('name')
                          ->get();
    }

    public function getActiveWithDepartment(): Collection
    {
        return Designation::with('department')
                          ->where('status', 'active')
                          ->whereHas('department', fn ($q) => $q->where('status', 'active'))
                          ->orderBy('name')
                          ->get();
    }

    public function toggleStatus(int $id): bool
    {
        $desig = $this->findOrFail($id);
        $desig->update([
            'status' => $desig->status === 'active' ? 'inactive' : 'active',
        ]);
        return true;
    }

    public function syncStatusByDepartment(int $departmentId, string $status): void
    {
        Designation::where('department_id', $departmentId)->update(['status' => $status]);
    }

    public function hasEmployees(int $id): bool
    {
        return Designation::findOrFail($id)->employees()->exists();
    }

    public function findWithDepartment(int $id): Designation
    {
        return Designation::with('department')->findOrFail($id);
    }

    public function findWithDepartmentAndEmployeeCount(int $id): Designation
    {
        return Designation::with('department')->withCount('employees')->findOrFail($id);
    }
}
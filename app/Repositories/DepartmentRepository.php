<?php

namespace App\Repositories;

use App\Contracts\Repositories\DepartmentRepositoryInterface;
use App\Models\Department;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class DepartmentRepository extends BaseRepository implements DepartmentRepositoryInterface
{
    public function __construct(Department $model)
    {
        parent::__construct($model);
    }

    public function paginateWithCounts(int $perPage = 10): LengthAwarePaginator
    {
        return Department::withCount(['designations', 'employees'])
                         ->latest()
                         ->paginate($perPage);
    }

    public function getActive(): Collection
    {
        return Department::where('status', 'active')
                         ->orderBy('name')
                         ->get();
    }

    public function toggleStatus(int $id): bool
    {
        $dept = $this->findOrFail($id);
        $dept->update([
            'status' => $dept->status === 'active' ? 'inactive' : 'active',
        ]);
        return true;
    }

    public function hasEmployees(int $id): bool
    {
        return Department::findOrFail($id)->employees()->exists();
    }
}
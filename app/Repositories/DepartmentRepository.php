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

    public function paginateWithCounts(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $query = Department::withCount(['designations', 'employees']);

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->latest()->paginate($perPage)->withQueryString();
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
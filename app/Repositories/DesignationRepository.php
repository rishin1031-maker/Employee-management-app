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

    public function paginateWithCounts(int $perPage = 10): LengthAwarePaginator
    {
        return Designation::with('department')
                          ->withCount('employees')
                          ->latest()
                          ->paginate($perPage);
    }

    public function getActive(): Collection
    {
        return Designation::where('status', 'active')
                          ->orderBy('name')
                          ->get();
    }

    public function getActiveWithDepartment(): Collection
    {
        return Designation::with('department')
                          ->where('status', 'active')
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

    public function hasEmployees(int $id): bool
    {
        return Designation::findOrFail($id)->employees()->exists();
    }
}
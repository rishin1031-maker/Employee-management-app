<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Api\Support\ApiTransformer;
use App\Http\Requests\StoreDepartmentRequest;
use App\Http\Requests\UpdateDepartmentRequest;
use App\Models\Department;
use App\Services\DepartmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DepartmentController extends ApiController
{
    public function __construct(private DepartmentService $departmentService) {}

    public function index(Request $request): JsonResponse
    {
        $departments = $this->departmentService->getPaginated(
            $request->only(['search', 'status'])
        );

        return $this->success(
            ApiTransformer::paginated($departments, fn ($d) => ApiTransformer::department($d))
        );
    }

    public function store(StoreDepartmentRequest $request): JsonResponse
    {
        $department = $this->departmentService->create($request->validated());

        return $this->success(ApiTransformer::department($department), 'Department created.', 201);
    }

    public function show(Department $department): JsonResponse
    {
        $department->loadCount(['designations', 'employees']);

        return $this->success(ApiTransformer::department($department));
    }

    public function update(UpdateDepartmentRequest $request, Department $department): JsonResponse
    {
        $updated = $this->departmentService->update($department->id, $request->validated());

        return $this->success(ApiTransformer::department($updated), 'Department updated.');
    }

    public function destroy(Department $department): JsonResponse
    {
        try {
            $this->departmentService->delete($department->id);

            return $this->success(null, 'Department deleted.');
        } catch (\Exception $e) {
            return $this->fromException($e);
        }
    }

    public function toggleStatus(Department $department): JsonResponse
    {
        $this->departmentService->toggleStatus($department->id);

        return $this->success(
            ApiTransformer::department($department->fresh()->loadCount(['designations', 'employees'])),
            'Department status updated.'
        );
    }
}

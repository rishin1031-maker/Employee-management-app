<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Api\Support\ApiTransformer;
use App\Http\Requests\StoreDesignationRequest;
use App\Http\Requests\UpdateDesignationRequest;
use App\Models\Designation;
use App\Services\DesignationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DesignationController extends ApiController
{
    public function __construct(private DesignationService $designationService) {}

    public function index(Request $request): JsonResponse
    {
        $designations = $this->designationService->getPaginated(
            $request->only(['search', 'status', 'department_id'])
        );

        return $this->success(
            ApiTransformer::paginated($designations, fn ($d) => ApiTransformer::designation($d))
        );
    }

    public function store(StoreDesignationRequest $request): JsonResponse
    {
        $designation = $this->designationService->create($request->validated());

        return $this->success(
            ApiTransformer::designation($this->designationService->getWithDepartment($designation->id)),
            'Designation created.',
            201
        );
    }

    public function show(Designation $designation): JsonResponse
    {
        return $this->success(
            ApiTransformer::designation($this->designationService->getWithDetails($designation->id))
        );
    }

    public function update(UpdateDesignationRequest $request, Designation $designation): JsonResponse
    {
        $this->designationService->update($designation->id, $request->validated());

        return $this->success(
            ApiTransformer::designation($this->designationService->getWithDepartment($designation->id)),
            'Designation updated.'
        );
    }

    public function destroy(Designation $designation): JsonResponse
    {
        try {
            $this->designationService->delete($designation->id);

            return $this->success(null, 'Designation deleted.');
        } catch (\Exception $e) {
            return $this->fromException($e);
        }
    }

    public function toggleStatus(Designation $designation): JsonResponse
    {
        $this->designationService->toggleStatus($designation->id);

        return $this->success(
            ApiTransformer::designation($this->designationService->getWithDetails($designation->id)),
            'Designation status updated.'
        );
    }
}

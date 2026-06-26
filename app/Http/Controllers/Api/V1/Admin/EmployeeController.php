<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Api\Support\ApiTransformer;
use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Models\Employee;
use App\Services\EmployeeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EmployeeController extends ApiController
{
    public function __construct(private EmployeeService $employeeService) {}

    public function index(Request $request): JsonResponse
    {
        $employees = $this->employeeService->getPaginatedEmployees($request->all());

        return $this->success(
            ApiTransformer::paginated($employees, fn ($e) => ApiTransformer::employee($e, true))
        );
    }

    public function store(StoreEmployeeRequest $request): JsonResponse
    {
        $employee = $this->employeeService->createEmployee(
            $request->validated(),
            $request->hasFile('image') ? $request->file('image') : null
        );

        return $this->success(
            ApiTransformer::employee(
                $this->employeeService->getEmployeeWithRelations($employee->id, ['department', 'designation']),
                true
            ),
            'Employee created.',
            201
        );
    }

    public function show(Employee $employee): JsonResponse
    {
        $employee = $this->employeeService->getEmployeeWithRelations(
            $employee->id,
            ['department', 'designation']
        );

        return $this->success(
            ApiTransformer::employee($employee, true)
        );
    }

    public function update(UpdateEmployeeRequest $request, Employee $employee): JsonResponse
    {
        $updated = $this->employeeService->updateEmployee(
            $employee->id,
            $request->validated(),
            $request->hasFile('image') ? $request->file('image') : null
        );

        return $this->success(
            ApiTransformer::employee(
                $this->employeeService->getEmployeeWithRelations($updated->id, ['department', 'designation']),
                true
            ),
            'Employee updated.'
        );
    }

    public function destroy(Employee $employee): JsonResponse
    {
        $this->employeeService->deleteEmployee($employee->id);

        return $this->success(null, 'Employee deleted.');
    }

    public function resetPassword(Request $request, Employee $employee): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'new_password' => 'required|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation failed.', 422, $validator->errors());
        }

        $this->employeeService->resetPassword($employee->id, $request->new_password);

        return $this->success(null, 'Password reset successfully.');
    }
}

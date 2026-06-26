<?php

namespace App\Http\Controllers\Api\V1\Employee;

use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Api\Support\ApiTransformer;
use App\Services\EmployeeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends ApiController
{
    public function __construct(private EmployeeService $employeeService) {}

    public function show(): JsonResponse
    {
        $employee = $this->employeeService->getEmployeeForProfile(
            Auth::guard('api_employee')->id()
        );

        return $this->success(ApiTransformer::employee($employee, true));
    }

    public function updatePhone(Request $request): JsonResponse
    {
        $data = $request->validate([
            'phone' => 'required|string|max:20|regex:/^[0-9+\-\s()]+$/',
        ]);

        $employee = $this->employeeService->updatePhone(
            Auth::guard('api_employee')->user(),
            $data['phone']
        );

        return $this->success(ApiTransformer::employee($employee, true), 'Phone number updated.');
    }
}

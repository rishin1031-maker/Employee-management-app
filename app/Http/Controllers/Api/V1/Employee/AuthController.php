<?php

namespace App\Http\Controllers\Api\V1\Employee;

use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Api\Support\ApiTransformer;
use App\Models\Employee;
use App\Services\AuthService;
use App\Services\EmployeeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AuthController extends ApiController
{
    public function __construct(
        private EmployeeService $employeeService,
        private AuthService $authService,
    ) {}

    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'login'    => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation failed.', 422, $validator->errors());
        }

        $login    = $request->input('login');
        $password = $request->input('password');
        $credentials = $this->authService->buildApiEmployeeCredentials($login, $password);

        if ($credentials === null) {
            return $this->error('Use email or Employee ID (e.g. EMP001) to login.', 422);
        }

        if (!$token = auth('api_employee')->attempt($credentials)) {
            return $this->error('Invalid credentials.', 401);
        }

        /** @var Employee $employee */
        $employee = auth('api_employee')->user();
        $this->authService->recordEmployeeLogin($employee);

        return $this->success(
            ApiTransformer::tokenResponse(
                $token,
                auth('api_employee')->user(),
                fn ($u) => ApiTransformer::employee($u, true),
                (int) config('jwt.ttl', 60)
            ),
            'Login successful.'
        );
    }

    public function me(): JsonResponse
    {
        return $this->success(
            ApiTransformer::employee(auth('api_employee')->user(), true)
        );
    }

    public function refresh(): JsonResponse
    {
        $token = auth('api_employee')->refresh();

        return $this->success(
            ApiTransformer::tokenResponse(
                $token,
                auth('api_employee')->user(),
                fn ($u) => ApiTransformer::employee($u, true),
                (int) config('jwt.ttl', 60)
            ),
            'Token refreshed.'
        );
    }

    public function logout(): JsonResponse
    {
        auth('api_employee')->logout();

        return $this->success(null, 'Logged out successfully.');
    }

    public function changePassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'password'         => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation failed.', 422, $validator->errors());
        }

        /** @var Employee $employee */
        $employee = auth('api_employee')->user();

        if (!$this->authService->verifyEmployeePassword($employee, $request->current_password)) {
            return $this->error('Current password is incorrect.', 422, [
                'current_password' => ['Current password is incorrect.'],
            ]);
        }

        $this->employeeService->changePassword($employee, $request->password);

        return $this->success(null, 'Password changed successfully.');
    }
}

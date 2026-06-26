<?php

namespace App\Services;

use App\Contracts\Repositories\EmployeeRepositoryInterface;
use App\Models\Employee;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function __construct(
        private EmployeeRepositoryInterface $employeeRepo,
    ) {}

    public function resolveWebLoginGuard(string $login, string $password, bool $remember): ?string
    {
        if (filter_var($login, FILTER_VALIDATE_EMAIL)) {
            if (Auth::guard('admin')->attempt(['email' => $login, 'password' => $password], $remember)) {
                return 'admin';
            }
        }

        if (preg_match('/^EMP\d+$/i', strtoupper($login))) {
            $employeeId = strtoupper($login);
            $employee   = $this->employeeRepo->findByEmployeeId($employeeId);

            if ($employee && Auth::guard('employee')->attempt(
                ['employee_id' => $employeeId, 'password' => $password],
                $remember
            )) {
                $this->employeeRepo->updateLastLoginAt($employee->id);

                return 'employee';
            }
        }

        return null;
    }

    public function buildApiEmployeeCredentials(string $login, string $password): ?array
    {
        if (filter_var($login, FILTER_VALIDATE_EMAIL)) {
            return ['email' => $login, 'password' => $password];
        }

        if (preg_match('/^EMP\d+$/i', strtoupper($login))) {
            return ['employee_id' => strtoupper($login), 'password' => $password];
        }

        return null;
    }

    public function recordEmployeeLogin(Employee $employee): void
    {
        $this->employeeRepo->updateLastLoginAt($employee->id);
    }

    public function verifyEmployeePassword(Employee $employee, string $password): bool
    {
        return Hash::check($password, $employee->password);
    }
}

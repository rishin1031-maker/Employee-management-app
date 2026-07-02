<?php

namespace App\Services;

use App\Contracts\Repositories\EmployeeRepositoryInterface;
use App\Contracts\Repositories\LeaveRepositoryInterface;
use App\Jobs\SendWelcomeEmailJob;
use App\Models\Employee;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;

class EmployeeService
{
    public function __construct(
        private EmployeeRepositoryInterface $employeeRepo,
        private LeaveRepositoryInterface    $leaveRepo,
    ) {}

    public function getPaginatedEmployees(array $filters): LengthAwarePaginator
    {
        return $this->employeeRepo->paginateWithFilters($filters);
    }

    public function createEmployee(array $data, ?UploadedFile $image = null): Employee
    {
        if ($image) {
            $data['image'] = $image->store('employees', 'public');
        }

        $plainPassword         = 'password123';
        $data['employee_id']   = $this->employeeRepo->generateEmployeeId();
        $data['password']      = $plainPassword;
        $data['must_change_password'] = true;

        $employee = $this->employeeRepo->create($data);

        // Initialize leave balance
        $this->leaveRepo->findOrCreateBalance($employee->id, now()->year);

        // Dispatch welcome email job
        SendWelcomeEmailJob::dispatch($employee, $plainPassword);

        return $employee;
    }

    public function updateEmployee(int $id, array $data, ?UploadedFile $image = null): Employee
    {
        $employee = $this->employeeRepo->findOrFail($id);

        if ($image) {
            if ($employee->image) {
                Storage::disk('public')->delete($employee->image);
            }
            $data['image'] = $image->store('employees', 'public');
        }

        return $this->employeeRepo->update($id, $data);
    }

    public function deleteEmployee(int $id): bool
    {
        $employee = $this->employeeRepo->findOrFail($id);

        if ($employee->image) {
            Storage::disk('public')->delete($employee->image);
        }

        return $this->employeeRepo->delete($id);
    }

    public function resetPassword(int $id, string $newPassword): Employee
    {
        return $this->employeeRepo->update($id, [
            'password'             => $newPassword,
            'must_change_password' => true,
        ]);
    }

    public function changePassword(Employee $employee, string $newPassword): Employee
    {
        return $this->employeeRepo->update($employee->id, [
            'password'             => $newPassword,
            'must_change_password' => false,
        ]);
    }

    public function updatePhone(Employee $employee, string $phone): Employee
    {
        return $this->employeeRepo->update($employee->id, ['phone' => $phone]);
    }

    public function getDashboardStats(): array
    {
        return [
            'total'    => $this->employeeRepo->all()->count(),
            'active'   => $this->employeeRepo->countByStatus('active'),
            'inactive' => $this->employeeRepo->countByStatus('inactive'),
            'recent'   => $this->employeeRepo->getRecentEmployees(5),
        ];
    }

    public function getActiveEmployees(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->employeeRepo->getActiveEmployees();
    }

    public function getEmployeeForProfile(int $id): Employee
    {
        return $this->employeeRepo->findWithRelations(
            $id,
            ['department', 'designation', 'salary', 'salaryHistories', 'leaveBalance']
        );
    }

    public function getEmployeeWithRelations(int $id, array $relations): Employee
    {
        return $this->employeeRepo->findWithRelations($id, $relations);
    }
}
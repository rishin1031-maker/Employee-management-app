<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Api\Support\ApiTransformer;
use App\Models\Employee;
use App\Services\EmployeeService;
use App\Services\SalaryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SalaryController extends ApiController
{
    public function __construct(
        private SalaryService $salaryService,
        private EmployeeService $employeeService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $employees = $this->salaryService->getEmployeesWithSalary($request->all());

        return $this->success(ApiTransformer::paginated($employees, function ($emp) {
            return [
                'employee' => ApiTransformer::employee($emp),
                'salary'   => ApiTransformer::salary($emp->salary),
            ];
        }));
    }

    public function show(Employee $employee): JsonResponse
    {
        $employee = $this->employeeService->getEmployeeWithRelations(
            $employee->id,
            ['department', 'designation']
        );
        $salaryData = $this->salaryService->getApiShowData($employee);

        return $this->success([
            'employee' => ApiTransformer::employee($employee, true),
            'current'  => ApiTransformer::salary($salaryData['current']),
            'history'  => $salaryData['history']
                ->map(fn ($h) => [
                    'id'             => $h->id,
                    'basic'          => (float) $h->basic,
                    'gross_salary'   => (float) $h->gross_salary,
                    'net_salary'     => (float) $h->net_salary,
                    'effective_from' => $h->effective_from?->toDateString(),
                    'note'           => $h->note,
                    'created_at'     => $h->created_at?->toIso8601String(),
                ])->values(),
        ]);
    }

    public function store(Request $request, Employee $employee): JsonResponse
    {
        $data = $request->validate([
            'basic'           => 'required|numeric|min:0',
            'hra'             => 'nullable|numeric|min:0',
            'transport'       => 'nullable|numeric|min:0',
            'medical'         => 'nullable|numeric|min:0',
            'pf_deduction'    => 'nullable|numeric|min:0',
            'tax_deduction'   => 'nullable|numeric|min:0',
            'other_allowance' => 'nullable|numeric|min:0',
            'other_deduction' => 'nullable|numeric|min:0',
            'effective_from'  => 'required|date',
            'note'            => 'nullable|string|max:500',
        ]);

        $salary = $this->salaryService->updateSalary($employee, $data);

        return $this->success(ApiTransformer::salary($salary->fresh()), 'Salary updated.', 201);
    }
}

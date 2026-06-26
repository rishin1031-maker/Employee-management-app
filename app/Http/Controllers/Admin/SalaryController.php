<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Services\EmployeeService;
use App\Services\SalaryService;
use Illuminate\Http\Request;

class SalaryController extends Controller
{
    public function __construct(
        private SalaryService $salaryService,
        private EmployeeService $employeeService,
    ) {}

    public function index()
    {
        $employees = $this->salaryService->getEmployeesWithSalary(request()->all());
        return view('admin.salary.index', compact('employees'));
    }

    public function create(Employee $employee)
    {
        $employee = $this->employeeService->getEmployeeWithRelations(
            $employee->id,
            ['designation', 'department']
        );
        $salaryData = $this->salaryService->getManagePageData($employee);

        return view('admin.salary.create', [
            'employee' => $employee,
            'current'  => $salaryData['current'],
            'history'  => $salaryData['history'],
        ]);
    }

    public function store(Request $request, Employee $employee)
    {
        $request->validate([
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

        $this->salaryService->updateSalary($employee, $request->all());
        return redirect()->route('admin.salary.index')
            ->with('success', "Salary updated for {$employee->name}.");
    }

    public function history(Employee $employee)
    {
        $employee = $this->employeeService->getEmployeeWithRelations(
            $employee->id,
            ['department', 'designation']
        );
        $history = $this->salaryService->getHistory($employee);

        return view('admin.salary.history', compact('employee', 'history'));
    }
}

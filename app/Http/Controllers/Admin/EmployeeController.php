<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Models\Employee;
use App\Services\DepartmentService;
use App\Services\DesignationService;
use App\Services\EmployeeService;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function __construct(
        private EmployeeService    $employeeService,
        private DepartmentService  $departmentService,
        private DesignationService $designationService,
    ) {}

    public function index()
    {
        $employees    = $this->employeeService->getPaginatedEmployees(request()->all());
        $departments  = $this->departmentService->getActive();
        $designations = $this->designationService->getActive();
        return view('employees.index', compact('employees', 'departments', 'designations'));
    }

    public function create()
    {
        $departments  = $this->departmentService->getActive();
        $designations = $this->designationService->getActiveWithDepartment();
        return view('employees.create', compact('departments', 'designations'));
    }

    public function store(StoreEmployeeRequest $request)
    {
        $employee = $this->employeeService->createEmployee(
            $request->validated(),
            $request->hasFile('image') ? $request->file('image') : null
        );

        return redirect()->route('admin.employees.index')
            ->with('success', "Employee created. ID: {$employee->employee_id} · Default password: password123");
    }

    public function show(Employee $employee)
    {
        $employee->load(['department', 'designation']);
        return view('employees.show', compact('employee'));
    }

    public function edit(Employee $employee)
    {
        $departments  = $this->departmentService->getActive();
        $designations = $this->designationService->getActiveWithDepartment();
        return view('employees.edit', compact('employee', 'departments', 'designations'));
    }

    public function update(UpdateEmployeeRequest $request, Employee $employee)
    {
        $this->employeeService->updateEmployee(
            $employee->id,
            $request->validated(),
            $request->hasFile('image') ? $request->file('image') : null
        );
        return redirect()->route('admin.employees.index')
            ->with('success', 'Employee updated successfully.');
    }

    public function destroy(Employee $employee)
    {
        $this->employeeService->deleteEmployee($employee->id);
        return redirect()->route('admin.employees.index')
            ->with('success', 'Employee deleted successfully.');
    }

    public function resetPassword(Request $request, Employee $employee)
    {
        $request->validate([
            'new_password' => 'required|min:8|confirmed',
        ]);

        $this->employeeService->resetPassword($employee->id, $request->new_password);

        return back()->with('success', "Password reset for {$employee->name}. They will be prompted to change it on next login.");
    }
}
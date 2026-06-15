<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use Illuminate\Support\Facades\Storage;

class EmployeeController extends Controller
{
    public function index()
    {
        $query = Employee::with(['department', 'designation']);

        if (request('search')) {
            $query->where('name', 'like', '%' . request('search') . '%')
                  ->orWhere('email', 'like', '%' . request('search') . '%');
        }

        if (request('department_id')) {
            $query->where('department_id', request('department_id'));
        }

        if (request('designation_id')) {
            $query->where('designation_id', request('designation_id'));
        }

        if (request('status')) {
            $query->where('status', request('status'));
        }

        $employees    = $query->latest()->paginate(10)->withQueryString();
        $departments  = Department::orderBy('name')->get();
        $designations = Designation::orderBy('name')->get();

        return view('employees.index', compact('employees', 'departments', 'designations'));
    }

    public function create()
    {
        $departments  = Department::where('status', 'active')->orderBy('name')->get();
        $designations = Designation::with('department')
                            ->where('status', 'active')
                            ->orderBy('name')
                            ->get();
        return view('employees.create', compact('departments', 'designations'));
    }

    public function store(StoreEmployeeRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('employees', 'public');
        }

        Employee::create($data);
        return redirect()->route('employees.index')->with('success', 'Employee created successfully.');
    }

    public function show(Employee $employee)
    {
        $employee->load(['department', 'designation']);
        return view('employees.show', compact('employee'));
    }

    public function edit(Employee $employee)
    {
        $departments  = Department::where('status', 'active')->orderBy('name')->get();
        $designations = Designation::with('department')
                            ->where(function ($q) use ($employee) {
                                $q->where('status', 'active')
                                ->orWhere('id', $employee->designation_id);
                            })
                            ->orderBy('name')
                            ->get();
        return view('employees.edit', compact('employee', 'departments', 'designations'));
    }

    public function update(UpdateEmployeeRequest $request, Employee $employee)
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            if ($employee->image) {
                Storage::disk('public')->delete($employee->image);
            }
            $data['image'] = $request->file('image')->store('employees', 'public');
        }

        $employee->update($data);
        return redirect()->route('employees.index')->with('success', 'Employee updated successfully.');
    }

    public function destroy(Employee $employee)
    {
        if ($employee->image) {
            Storage::disk('public')->delete($employee->image);
        }
        $employee->delete();
        return redirect()->route('employees.index')->with('success', 'Employee deleted successfully.');
    }
}
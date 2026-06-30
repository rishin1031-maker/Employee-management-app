<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDepartmentRequest;
use App\Http\Requests\UpdateDepartmentRequest;
use App\Models\Department;
use App\Services\DepartmentService;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function __construct(private DepartmentService $departmentService) {}

    public function index(Request $request)
    {
        $departments = $this->departmentService->getPaginated(
            $request->only(['search', 'status'])
        );

        return view('departments.index', compact('departments'));
    }

    public function create()
    {
        return view('departments.create');
    }

    public function store(StoreDepartmentRequest $request)
    {
        $this->departmentService->create($request->validated());
        return redirect()->route('admin.departments.index')
            ->with('success', 'Department created successfully.');
    }

    public function edit(Department $department)
    {
        return view('departments.edit', compact('department'));
    }

    public function update(UpdateDepartmentRequest $request, Department $department)
    {
        $this->departmentService->update($department->id, $request->validated());
        return redirect()->route('admin.departments.index')
            ->with('success', 'Department updated successfully.');
    }

    public function destroy(Department $department)
    {
        try {
            $this->departmentService->delete($department->id);
            return redirect()->route('admin.departments.index')
                ->with('success', 'Department deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $this->userFacingMessage($e));
        }
    }

    public function toggleStatus(Department $department)
    {
        $this->departmentService->toggleStatus($department->id);
        return back()->with('success', "Department status updated.");
    }

    public function quickStore(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255|unique:departments,name',
            'description' => 'nullable|string|max:1000',
        ]);

        $department = $this->departmentService->create([
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
            'status'      => 'active',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Department created.',
            'department' => [
                'id'   => $department->id,
                'name' => $department->name,
            ],
        ]);
    }
}
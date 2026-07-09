<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDesignationRequest;
use App\Http\Requests\UpdateDesignationRequest;
use App\Models\Designation;
use App\Services\DepartmentService;
use App\Services\DesignationService;
use Illuminate\Http\Request;

class DesignationController extends Controller
{
    public function __construct(
        private DesignationService $designationService,
        private DepartmentService  $departmentService,
    ) {}

    public function index(Request $request)
    {
        $designations = $this->designationService->getPaginated(
            $request->only(['search', 'status', 'department_id'])
        );
        $departments = $this->departmentService->getActive();

        return view('designations.index', compact('designations', 'departments'));
    }

    public function create()
    {
        $departments = $this->departmentService->getActive();
        return view('designations.create', compact('departments'));
    }

    public function store(StoreDesignationRequest $request)
    {
        try {
            $this->designationService->create($request->validated());

            return redirect()->route('admin.designations.index')
                ->with('success', 'Designation created successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $this->userFacingMessage($e));
        }
    }

    public function edit(Designation $designation)
    {
        $departments = $this->departmentService->getActive();
        return view('designations.edit', compact('designation', 'departments'));
    }

    public function update(UpdateDesignationRequest $request, Designation $designation)
    {
        try {
            $this->designationService->update($designation->id, $request->validated());

            return redirect()->route('admin.designations.index')
                ->with('success', 'Designation updated successfully.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $this->userFacingMessage($e));
        }
    }

    public function destroy(Designation $designation)
    {
        try {
            $this->designationService->delete($designation->id);
            return redirect()->route('admin.designations.index')
                ->with('success', 'Designation deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $this->userFacingMessage($e));
        }
    }

    public function toggleStatus(Designation $designation)
    {
        try {
            $this->designationService->toggleStatus($designation->id);

            return back()->with('success', 'Designation status updated.');
        } catch (\Exception $e) {
            return back()->with('error', $this->userFacingMessage($e));
        }
    }

    public function quickStore(Request $request)
    {
        $data = $request->validate([
            'name'          => 'required|string|max:255',
            'department_id' => 'required|exists:departments,id',
        ]);

        try {
            $designation = $this->designationService->create([
                'name'          => $data['name'],
                'department_id' => $data['department_id'],
                'status'        => 'active',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Designation created.',
                'designation' => [
                    'id'            => $designation->id,
                    'name'          => $designation->name,
                    'department_id' => $designation->department_id,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $this->userFacingMessage($e),
            ], 422);
        }
    }
}
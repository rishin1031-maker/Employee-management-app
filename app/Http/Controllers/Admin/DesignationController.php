<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDesignationRequest;
use App\Http\Requests\UpdateDesignationRequest;
use App\Models\Designation;
use App\Services\DepartmentService;
use App\Services\DesignationService;

class DesignationController extends Controller
{
    public function __construct(
        private DesignationService $designationService,
        private DepartmentService  $departmentService,
    ) {}

    public function index()
    {
        $designations = $this->designationService->getPaginated();
        return view('designations.index', compact('designations'));
    }

    public function create()
    {
        $departments = $this->departmentService->getActive();
        return view('designations.create', compact('departments'));
    }

    public function store(StoreDesignationRequest $request)
    {
        $this->designationService->create($request->validated());
        return redirect()->route('admin.designations.index')
            ->with('success', 'Designation created successfully.');
    }

    public function edit(Designation $designation)
    {
        $departments = $this->departmentService->getActive();
        return view('designations.edit', compact('designation', 'departments'));
    }

    public function update(UpdateDesignationRequest $request, Designation $designation)
    {
        $this->designationService->update($designation->id, $request->validated());
        return redirect()->route('admin.designations.index')
            ->with('success', 'Designation updated successfully.');
    }

    public function destroy(Designation $designation)
    {
        try {
            $this->designationService->delete($designation->id);
            return redirect()->route('admin.designations.index')
                ->with('success', 'Designation deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function toggleStatus(Designation $designation)
    {
        $this->designationService->toggleStatus($designation->id);
        return back()->with('success', 'Designation status updated.');
    }
}
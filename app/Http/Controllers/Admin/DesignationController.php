<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDesignationRequest;
use App\Http\Requests\UpdateDesignationRequest;
use App\Models\Department;
use App\Models\Designation;

class DesignationController extends Controller
{
    public function index()
    {
        $designations = Designation::with('department')->withCount('employees')->latest()->paginate(10);
        return view('designations.index', compact('designations'));
    }

    public function create()
    {
        $departments = Department::orderBy('name')->get();
        return view('designations.create', compact('departments'));
    }

    public function store(StoreDesignationRequest $request)
    {
        Designation::create($request->validated());
        return redirect()->route('designations.index')->with('success', 'Designation created successfully.');
    }

    public function edit(Designation $designation)
    {
        $departments = Department::orderBy('name')->get();
        return view('designations.edit', compact('designation', 'departments'));
    }

    public function update(UpdateDesignationRequest $request, Designation $designation)
    {
        $designation->update($request->validated());
        return redirect()->route('designations.index')->with('success', 'Designation updated successfully.');
    }

    public function destroy(Designation $designation)
    {
        if ($designation->employees()->count() > 0) {
            return back()->with('error', 'Cannot delete designation with assigned employees.');
        }
        $designation->delete();
        return redirect()->route('designations.index')->with('success', 'Designation deleted successfully.');
    }

    public function toggleStatus(Designation $designation)
    {
        $designation->update([
            'status' => $designation->status === 'active' ? 'inactive' : 'active'
        ]);
    
        return back()->with('success', "Designation marked as {$designation->status}.");
    }    
}
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Salary;
use App\Models\SalaryHistory;
use Illuminate\Http\Request;

class SalaryController extends Controller
{
    public function index(Request $request)
    {
        $query = Employee::with(['salary', 'department'])
            ->where('status', 'active');

        if ($request->search) {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('employee_id', 'like', '%' . $request->search . '%');
        }

        $employees = $query->orderBy('name')->paginate(15)->withQueryString();
        return view('admin.salary.index', compact('employees'));
    }

    public function create(Employee $employee)
    {
        $current = $employee->salary;
        $history = $employee->salaryHistories()->take(5)->get();
        return view('admin.salary.create', compact('employee', 'current', 'history'));
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

        $data = array_merge(
            $request->only(['basic','hra','transport','medical','pf_deduction','tax_deduction','other_allowance','other_deduction','effective_from','note']),
            ['employee_id' => $employee->id]
        );

        // Fill nullable fields with 0
        foreach (['hra','transport','medical','pf_deduction','tax_deduction','other_allowance','other_deduction'] as $f) {
            $data[$f] = $data[$f] ?? 0;
        }

        $salary = Salary::updateOrCreate(['employee_id' => $employee->id], $data);

        // Compute gross/net for history
        $gross = $data['basic'] + $data['hra'] + $data['transport'] + $data['medical'] + $data['other_allowance'];
        $net   = $gross - $data['pf_deduction'] - $data['tax_deduction'] - $data['other_deduction'];

        SalaryHistory::create(array_merge($data, [
            'salary_id'    => $salary->id,
            'gross_salary' => $gross,
            'net_salary'   => $net,
        ]));

        return redirect()->route('admin.salary.index')
            ->with('success', "Salary updated for {$employee->name}.");
    }

    public function history(Employee $employee)
    {
        $employee->load(['department', 'designation']);
        $history = $employee->salaryHistories()->paginate(10);
        return view('admin.salary.history', compact('employee', 'history'));
    }
}

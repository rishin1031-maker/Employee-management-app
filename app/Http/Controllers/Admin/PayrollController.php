<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\PayrollService;
use Illuminate\Http\Request;

class PayrollController extends Controller
{
    public function __construct(private PayrollService $payrollService) {}

    public function index(Request $request)
    {
        $year  = $request->get('year', now()->year);
        $years = range(now()->year - 3, now()->year);
        $data  = $this->payrollService->getPayrollData($year);

        return view('admin.payroll.index', array_merge($data, compact('year', 'years')));
    }
}
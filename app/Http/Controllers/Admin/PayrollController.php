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
        $year  = (int) $request->get('year', now()->year);
        $month = $request->get('month', now()->format('Y-m'));
        $years = range(now()->year - 3, now()->year);
        $data  = $this->payrollService->getPayrollData($year, $month);

        return view('admin.payroll.index', array_merge($data, compact('year', 'month', 'years')));
    }
}
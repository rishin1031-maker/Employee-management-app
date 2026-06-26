<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Api\ApiController;
use App\Services\PayrollService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PayrollController extends ApiController
{
    public function __construct(private PayrollService $payrollService) {}

    public function index(Request $request): JsonResponse
    {
        $year = (int) $request->get('year', now()->year);

        return $this->success(array_merge(
            ['year' => $year],
            $this->payrollService->getPayrollData($year)
        ));
    }
}

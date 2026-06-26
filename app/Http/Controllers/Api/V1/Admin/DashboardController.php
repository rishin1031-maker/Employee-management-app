<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Api\Support\ApiTransformer;
use App\Services\DashboardService;

class DashboardController extends ApiController
{
    public function __construct(private DashboardService $dashboardService) {}

    public function index(): \Illuminate\Http\JsonResponse
    {
        $data = $this->dashboardService->getAdminDashboardData();

        return $this->success([
            'employees'        => [
                'total'    => $data['totalEmployees'],
                'active'   => $data['activeEmployees'],
                'inactive' => $data['inactiveEmployees'],
                'recent'   => $data['recentEmployees'],
            ],
            'departments'      => $data['totalDepartments'],
            'designations'     => $data['totalDesignations'],
            'pending_leaves'   => $data['pendingLeaves'],
            'attendance_today' => [
                'present'     => $data['todayPresent'],
                'absent'      => $data['todayAbsent'],
                'not_marked'  => $data['todayNotMarked'],
            ],
            'recent_leaves'    => $data['recentLeaves']
                ->map(fn ($l) => ApiTransformer::leave($l))->values(),
            'recent_employees' => $data['recentEmployees']
                ->map(fn ($e) => ApiTransformer::employee($e))->values(),
        ]);
    }
}

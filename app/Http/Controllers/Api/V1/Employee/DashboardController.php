<?php

namespace App\Http\Controllers\Api\V1\Employee;

use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Api\Support\ApiTransformer;
use App\Services\AttendanceTimeCalculator;
use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class DashboardController extends ApiController
{
    public function __construct(private DashboardService $dashboardService) {}

    public function index(): JsonResponse
    {
        $data = $this->dashboardService->getEmployeeApiDashboardData(
            Auth::guard('api_employee')->user()
        );

        return $this->success([
            'employee'          => ApiTransformer::employee($data['employee'], true),
            'today_attendance'  => $data['today_attendance']
                ? ApiTransformer::attendance($data['today_attendance'])
                : null,
            'live_stats'        => array_merge($data['live_stats'], [
                'target_seconds' => AttendanceTimeCalculator::TARGET_SECONDS,
            ]),
            'leave_balance'     => ApiTransformer::leaveBalance($data['leave_balance']),
            'pending_leaves'    => $data['pending_leaves'],
            'recent_attendance' => $data['recent_attendance']
                ->map(fn ($a) => ApiTransformer::attendance($a))
                ->values(),
            'recent_leaves'     => $data['recent_leaves']
                ->map(fn ($l) => ApiTransformer::leave($l))
                ->values(),
        ]);
    }
}

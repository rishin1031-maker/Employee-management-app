<?php

namespace App\Http\Controllers\Api\V1\Employee;

use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Api\Support\ApiTransformer;
use App\Services\AttendanceService;
use App\Services\AttendanceTimeCalculator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends ApiController
{
    public function __construct(private AttendanceService $attendanceService) {}

    public function index(Request $request): JsonResponse
    {
        $employee = Auth::guard('api_employee')->user();
        $month    = $request->get('month', now()->format('Y-m'));
        $result   = $this->attendanceService->getMonthlyForEmployee($employee->id, $month);

        return $this->success([
            'month'       => $month,
            'summary'     => $result['summary'],
            'attendances' => $result['attendances']->map(fn ($a) => ApiTransformer::attendance($a))->values(),
        ]);
    }

    public function checkIn(): JsonResponse
    {
        try {
            $att = $this->attendanceService->checkIn(Auth::guard('api_employee')->id());

            return $this->success(ApiTransformer::attendance($att), 'Checked in successfully.', 201);
        } catch (\Exception $e) {
            return $this->fromException($e);
        }
    }

    public function checkOut(Request $request): JsonResponse
    {
        try {
            $employeeId  = Auth::guard('api_employee')->id();
            $attendance  = $this->attendanceService->getTodayForEmployee($employeeId);

            if (!$attendance) {
                return $this->error('No check-in record found for today.', 404);
            }

            $stats      = $this->attendanceService->getLiveStats($attendance);
            $isComplete = $stats['is_complete'];

            if (!$isComplete) {
                $request->validate([
                    'early_reason' => 'required|string|min:5|max:300',
                ]);
            }

            $att = $this->attendanceService->checkOut(
                $employeeId,
                $isComplete ? null : $request->early_reason
            );

            return $this->success(ApiTransformer::attendance($att->load('breaks')), 'Checked out successfully.');
        } catch (\Exception $e) {
            return $this->fromException($e);
        }
    }

    public function startBreak(): JsonResponse
    {
        try {
            $employeeId = Auth::guard('api_employee')->id();
            $this->attendanceService->startBreak($employeeId);
            $attendance = $this->attendanceService->getTodayForEmployee($employeeId);
            $stats      = $this->attendanceService->getLiveStats($attendance);

            return $this->success([
                'live_stats' => array_merge($stats, ['target_seconds' => AttendanceTimeCalculator::TARGET_SECONDS]),
            ], 'Break started.');
        } catch (\Exception $e) {
            return $this->fromException($e);
        }
    }

    public function endBreak(): JsonResponse
    {
        try {
            $employeeId = Auth::guard('api_employee')->id();
            $break      = $this->attendanceService->endBreak($employeeId);
            $attendance = $this->attendanceService->getTodayForEmployee($employeeId);
            $stats      = $this->attendanceService->getLiveStats($attendance);

            return $this->success([
                'break'      => ApiTransformer::break($break),
                'live_stats' => array_merge($stats, ['target_seconds' => AttendanceTimeCalculator::TARGET_SECONDS]),
            ], 'Break ended.');
        } catch (\Exception $e) {
            return $this->fromException($e);
        }
    }

    public function liveStatus(): JsonResponse
    {
        $status = $this->attendanceService->getEmployeeLiveStatus(Auth::guard('api_employee')->id());

        if (!$status['checked_in'] ?? false) {
            return $this->success(['checked_in' => false]);
        }

        return $this->success($status);
    }
}

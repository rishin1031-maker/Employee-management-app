<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Api\Support\ApiTransformer;
use App\Models\AttendanceBreak;
use App\Services\AttendanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttendanceController extends ApiController
{
    public function __construct(private AttendanceService $attendanceService) {}

    public function daily(Request $request): JsonResponse
    {
        $date = $request->get('date', today()->toDateString());
        $employees = $this->attendanceService->getDailyReport($date);

        $items = $employees->map(function ($emp) {
            $att = $emp->attendances->first();

            return [
                'employee'   => ApiTransformer::employee($emp),
                'attendance' => $att ? ApiTransformer::attendance($att) : null,
            ];
        })->values();

        return $this->success(['date' => $date, 'items' => $items]);
    }

    public function monthly(Request $request): JsonResponse
    {
        $month  = $request->get('month', now()->format('Y-m'));
        $report = $this->attendanceService->getMonthlyReport($month);

        return $this->success(['month' => $month, 'report' => $report]);
    }

    public function mark(Request $request): JsonResponse
    {
        $data = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'date'        => 'required|date',
            'status'      => 'required|in:present,absent,half_day,on_leave',
            'check_in'    => 'nullable|date_format:H:i',
            'check_out'   => 'nullable|date_format:H:i',
            'note'        => 'nullable|string|max:255',
        ]);

        $attendance = $this->attendanceService->markAttendanceByAdmin(
            $data['employee_id'],
            $data['date'],
            $request->only(['status', 'check_in', 'check_out', 'note'])
        );

        return $this->success(
            ApiTransformer::attendance($attendance->load('breaks')),
            'Attendance marked.'
        );
    }

    public function addBreak(Request $request): JsonResponse
    {
        $data = $request->validate([
            'attendance_id' => 'required|exists:attendances,id',
            'break_out'     => 'required|date_format:H:i',
            'break_in'      => 'nullable|date_format:H:i|after:break_out',
        ]);

        $break = $this->attendanceService->addBreakByAdmin(
            $data['attendance_id'],
            $data['break_out'],
            $data['break_in'] ?? null
        );

        return $this->success(ApiTransformer::break($break), 'Break added.', 201);
    }

    public function deleteBreak(AttendanceBreak $break): JsonResponse
    {
        $this->attendanceService->deleteBreak($break->id);

        return $this->success(null, 'Break removed.');
    }
}

<?php

namespace App\Services;

use App\Contracts\Repositories\AttendanceRepositoryInterface;
use App\Models\Attendance;
use App\Models\AttendanceBreak;
use App\Services\AttendanceTimeCalculator;
use Illuminate\Support\Facades\DB;

class AttendanceService
{
    public function __construct(
        private AttendanceRepositoryInterface $attendanceRepo
    ) {}

    public function checkIn(int $employeeId): Attendance
    {
        $existing = $this->attendanceRepo->getTodayForEmployee($employeeId);

        if ($existing) {
            throw new \Exception('You have already checked in today.');
        }

        return $this->attendanceRepo->checkIn($employeeId);
    }

    public function checkOut(int $employeeId, ?string $earlyReason = null): Attendance
    {
        return DB::transaction(function () use ($employeeId, $earlyReason) {
            $attendance = $this->attendanceRepo->getTodayForEmployee($employeeId);

            if (!$attendance) {
                throw new \Exception('No check-in record found for today.');
            }

            if ($attendance->check_out) {
                throw new \Exception('You have already checked out.');
            }

            if ($attendance->on_break) {
                $this->attendanceRepo->endBreak($attendance->id);
            }

            $data = ['check_out' => now()];
            if ($earlyReason) {
                $data['note'] = 'Early checkout: ' . $earlyReason;
            }

            return $this->attendanceRepo->checkOutWithData($employeeId, $data);
        });
    }

    public function startBreak(int $employeeId): AttendanceBreak
    {
        return DB::transaction(function () use ($employeeId) {
            $attendance = $this->attendanceRepo->getTodayForEmployee($employeeId);

            if (!$attendance) {
                throw new \Exception('You must check in before taking a break.');
            }
            if ($attendance->check_out) {
                throw new \Exception('You have already checked out.');
            }

            $attendance->load('breaks');
            if ($attendance->on_break) {
                throw new \Exception('You are already on a break.');
            }

            return $this->attendanceRepo->startBreak($attendance->id, $employeeId, 'self');
        });
    }

    public function endBreak(int $employeeId): AttendanceBreak
    {
        return DB::transaction(function () use ($employeeId) {
            $attendance = $this->attendanceRepo->getTodayForEmployee($employeeId);

            if (!$attendance) {
                throw new \Exception('No attendance record found for today.');
            }

            if (!$attendance->on_break) {
                throw new \Exception('You are not currently on a break.');
            }

            return $this->attendanceRepo->endBreak($attendance->id);
        });
    }

    public function markAttendanceByAdmin(int $employeeId, string $date, array $data): Attendance
    {
        $checkIn  = isset($data['check_in'])  ? $date . ' ' . $data['check_in']  . ':00' : null;
        $checkOut = isset($data['check_out']) ? $date . ' ' . $data['check_out'] . ':00' : null;

        return $this->attendanceRepo->markOrUpdate($employeeId, $date, [
            'check_in'  => $checkIn,
            'check_out' => $checkOut,
            'status'    => $data['status'],
            'marked_by' => 'admin',
            'note'      => $data['note'] ?? null,
        ]);
    }

    public function addBreakByAdmin(int $attendanceId, string $breakOut, ?string $breakIn): AttendanceBreak
    {
        $attendance = $this->attendanceRepo->findAttendanceOrFail($attendanceId);

        return $this->attendanceRepo->addBreak(
            $attendance->id,
            $attendance->employee_id,
            $attendance->date->toDateString() . ' ' . $breakOut . ':00',
            $breakIn ? $attendance->date->toDateString() . ' ' . $breakIn . ':00' : null
        );
    }

    public function deleteBreak(int $breakId): bool
    {
        return $this->attendanceRepo->deleteBreak($breakId);
    }

    public function getMonthlyForEmployee(int $employeeId, string $month): array
    {
        [$year, $mon] = explode('-', $month);
        $attendances  = $this->attendanceRepo->getMonthlyForEmployee($employeeId, $year, $mon);

        return [
            'attendances' => $attendances,
            'summary'     => [
                'present'  => $attendances->where('status', 'present')->count(),
                'absent'   => $attendances->where('status', 'absent')->count(),
                'half_day' => $attendances->where('status', 'half_day')->count(),
                'on_leave' => $attendances->where('status', 'on_leave')->count(),
            ],
        ];
    }

    public function getDailyReport(string $date): \Illuminate\Database\Eloquent\Collection
    {
        return $this->attendanceRepo->getDailyForAllEmployees($date);
    }

    public function getMonthlyReport(string $month): array
    {
        [$year, $mon] = explode('-', $month);
        return $this->attendanceRepo->getMonthlyReport($year, $mon);
    }

    public function getTodayStats(int $activeCount): array
    {
        return [
            'present'    => $this->attendanceRepo->getTodayPresentCount(),
            'absent'     => $this->attendanceRepo->getTodayAbsentCount(),
            'not_marked' => $activeCount - $this->attendanceRepo->getTodayMarkedCount(),
        ];
    }

    public function getTodayForEmployee(int $employeeId): ?Attendance
    {
        return $this->attendanceRepo->getTodayForEmployee($employeeId);
    }

    public function getLiveStats(?Attendance $attendance): array
    {
        if (!$attendance) {
            return AttendanceTimeCalculator::forAttendance(new Attendance());
        }

        $attendance->loadMissing('breaks');

        return AttendanceTimeCalculator::forAttendance($attendance);
    }

    public function buildLivePayload(?Attendance $attendance, array $stats): array
    {
        return [
            'on_break'                => $stats['on_break'],
            'net_seconds'             => $stats['net_seconds'],
            'total_break_seconds'     => $stats['total_break_seconds'],
            'completed_break_seconds' => $stats['completed_break_seconds'],
            'active_break_seconds'    => $stats['active_break_seconds'],
            'remaining_seconds'       => $stats['remaining_seconds'],
            'progress_percent'        => $stats['progress_percent'],
            'is_complete'             => $stats['is_complete'],
            'break_count'             => $stats['break_count'],
            'active_break_since'      => $stats['active_break_since'],
            'target_seconds'          => AttendanceTimeCalculator::TARGET_SECONDS,
        ];
    }

    public function getEmployeeLiveStatus(int $employeeId): array
    {
        $attendance = $this->attendanceRepo->getTodayForEmployee($employeeId);

        if (!$attendance) {
            return ['checked_in' => false];
        }

        $stats = $this->getLiveStats($attendance);

        return [
            'checked_in'    => true,
            'checked_out'   => $attendance->check_out !== null,
            'check_in_time' => $attendance->check_in->format('h:i A'),
            'server_time'   => now()->timestamp,
            ...$this->buildLivePayload($attendance, $stats),
        ];
    }
}

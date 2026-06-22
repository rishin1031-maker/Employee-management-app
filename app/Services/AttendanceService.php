<?php

namespace App\Services;

use App\Contracts\Repositories\AttendanceRepositoryInterface;
use App\Models\Attendance;
use App\Models\AttendanceBreak;

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

    public function checkOut(int $employeeId): Attendance
    {
        $attendance = $this->attendanceRepo->getTodayForEmployee($employeeId);

        if (!$attendance) {
            throw new \Exception('No check-in record found for today.');
        }

        if ($attendance->check_out) {
            throw new \Exception('You have already checked out today.');
        }

        return $this->attendanceRepo->checkOut($employeeId);
    }

    public function startBreak(int $employeeId): AttendanceBreak
    {
        $attendance = $this->attendanceRepo->getTodayForEmployee($employeeId);

        if (!$attendance) {
            throw new \Exception('You must check in before taking a break.');
        }

        if ($attendance->on_break) {
            throw new \Exception('You are already on a break.');
        }

        return $this->attendanceRepo->startBreak($attendance->id, $employeeId, 'self');
    }

    public function endBreak(int $employeeId): AttendanceBreak
    {
        $attendance = $this->attendanceRepo->getTodayForEmployee($employeeId);

        if (!$attendance) {
            throw new \Exception('No attendance record found for today.');
        }

        return $this->attendanceRepo->endBreak($attendance->id);
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

    public function addBreakByAdmin(int $attendanceId, int $employeeId, string $date, string $breakOut, ?string $breakIn): AttendanceBreak
    {
        return $this->attendanceRepo->addBreak(
            $attendanceId,
            $employeeId,
            $date . ' ' . $breakOut . ':00',
            $breakIn ? $date . ' ' . $breakIn . ':00' : null
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
            'not_marked' => $activeCount - \App\Models\Attendance::where('date', today())->count(),
        ];
    }

    public function getTodayForEmployee(int $employeeId): ?\App\Models\Attendance
    {
        return $this->attendanceRepo->getTodayForEmployee($employeeId);
    }
}
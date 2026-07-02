<?php

namespace App\Services;

use App\Contracts\Repositories\AttendanceRepositoryInterface;
use App\Models\Salary;

class SalaryCalculator
{
    public function __construct(
        private AttendanceRepositoryInterface $attendanceRepo,
    ) {}

    public function getMonthlyWorkHoursForEmployee(int $employeeId, string $month): float
    {
        [$year, $mon] = explode('-', $month);
        $attendances  = $this->attendanceRepo->getMonthlyForEmployee($employeeId, (int) $year, (int) $mon);
        $isCurrentMonth = $month === now()->format('Y-m');
        $totalSeconds   = 0;

        foreach ($attendances as $attendance) {
            if (!$attendance->check_in) {
                continue;
            }

            $asOf = $attendance->check_out
                ?? ($isCurrentMonth && $attendance->date->isToday()
                    ? now()
                    : $attendance->date->copy()->endOfDay());

            $totalSeconds += AttendanceTimeCalculator::forAttendance($attendance, $asOf)['net_seconds'];
        }

        return round($totalSeconds / 3600, 2);
    }

    public function calculateFromWorkHours(Salary $salary, float $workHours): array
    {
        $target = AttendanceTimeCalculator::TARGET_MONTHLY_HOURS;
        $ratio  = $target > 0 ? min(1, $workHours / $target) : 0;

        return [
            'work_hours'       => round($workHours, 2),
            'target_hours'     => $target,
            'ratio'            => round($ratio, 4),
            'progress_percent' => min(100, round($ratio * 100, 1)),
            'is_full_month'    => $workHours >= $target,
            'remaining_hours'  => max(0, round($target - $workHours, 2)),
            'base_gross'       => round($salary->gross_salary, 2),
            'base_net'         => round($salary->net_salary, 2),
            'earned_gross'     => round($salary->gross_salary * $ratio, 2),
            'earned_net'       => round($salary->net_salary * $ratio, 2),
        ];
    }
}

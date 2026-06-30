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

    public function getDailyReport(string $date, array $filters = []): \Illuminate\Database\Eloquent\Collection
    {
        return $this->attendanceRepo->getDailyForAllEmployees($date, $filters);
    }

    public function getMonthlyReport(string $month, array $filters = []): array
    {
        [$year, $mon] = explode('-', $month);
        return $this->attendanceRepo->getMonthlyReport($year, $mon, $filters);
    }

    public function getTodayStats(int $activeCount): array
    {
        return [
            'present'    => $this->attendanceRepo->getTodayPresentCount(),
            'absent'     => $this->attendanceRepo->getTodayAbsentCount(),
            'not_marked' => $activeCount - $this->attendanceRepo->getTodayMarkedCount(),
        ];
    }

    public function getAdminStatistics(array $filters = []): array
    {
        $today      = today()->toDateString();
        $weekStart  = now()->startOfWeek(\Carbon\Carbon::MONDAY)->toDateString();
        $monthStart = now()->startOfMonth()->toDateString();

        $activeCount = $this->attendanceRepo->getActiveEmployeeCount($filters);
        $todayCounts = $this->attendanceRepo->getStatusCountsBetween($today, $today, $filters);
        $weekCounts  = $this->attendanceRepo->getStatusCountsBetween($weekStart, $today, $filters);
        $monthCounts = $this->attendanceRepo->getStatusCountsBetween($monthStart, $today, $filters);

        $todayMarked = array_sum($todayCounts);

        return [
            'active_employees' => $activeCount,
            'today' => [
                'present'    => $todayCounts['present'],
                'absent'     => $todayCounts['absent'],
                'half_day'   => $todayCounts['half_day'],
                'on_leave'   => $todayCounts['on_leave'],
                'not_marked' => max(0, $activeCount - $todayMarked),
                'label'      => today()->format('D, d M Y'),
            ],
            'week' => [
                'present' => $weekCounts['present'],
                'label'   => \Carbon\Carbon::parse($weekStart)->format('d M') . ' – ' . today()->format('d M Y'),
            ],
            'month' => [
                'present' => $monthCounts['present'],
                'label'   => now()->format('F Y') . ' (to date)',
            ],
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

    public function getWorkHoursChartData(int $employeeId, string $view, array $params = []): array
    {
        return match ($view) {
            'daily'   => $this->buildDailyChartData($employeeId, $params['date'] ?? today()->toDateString()),
            'weekly'  => $this->buildWeeklyChartData($employeeId, $params['date'] ?? today()->toDateString()),
            'monthly' => $this->buildMonthlyChartData($employeeId, $params['month'] ?? now()->format('Y-m')),
            default   => $this->buildWeeklyChartData($employeeId, today()->toDateString()),
        };
    }

    private function buildDailyChartData(int $employeeId, string $date): array
    {
        $attendance = $this->attendanceRepo->getForEmployeeBetweenDates($employeeId, $date, $date)->first();
        $metrics    = $this->metricsForAttendance($attendance);

        $label = \Carbon\Carbon::parse($date)->format('D, d M Y');

        return [
            'view'        => 'daily',
            'period_label'=> $label,
            'labels'      => [$label],
            'work_hours'  => [$metrics['work_hours']],
            'break_hours' => [$metrics['break_hours']],
            'summary'     => $this->summarizeChartSeries([$metrics], 'daily'),
            'has_data'    => $attendance !== null,
        ];
    }

    private function buildWeeklyChartData(int $employeeId, string $anchorDate): array
    {
        $start = \Carbon\Carbon::parse($anchorDate)->startOfWeek(\Carbon\Carbon::MONDAY);
        $end   = $start->copy()->endOfWeek(\Carbon\Carbon::SUNDAY);

        $records = $this->attendanceRepo->getForEmployeeBetweenDates(
            $employeeId,
            $start->toDateString(),
            $end->toDateString()
        )->keyBy(fn ($a) => $a->date->toDateString());

        $labels     = [];
        $workHours  = [];
        $breakHours = [];
        $metricsList = [];

        for ($day = $start->copy(); $day->lte($end); $day->addDay()) {
            $key        = $day->toDateString();
            $attendance = $records->get($key);
            $metrics    = $this->metricsForAttendance($attendance);

            $labels[]      = $day->format('D, d M');
            $workHours[]   = $metrics['work_hours'];
            $breakHours[]  = $metrics['break_hours'];
            $metricsList[] = $metrics;
        }

        return [
            'view'         => 'weekly',
            'period_label' => $start->format('d M') . ' – ' . $end->format('d M Y'),
            'week_start'   => $start->toDateString(),
            'labels'       => $labels,
            'work_hours'   => $workHours,
            'break_hours'  => $breakHours,
            'summary'      => $this->summarizeChartSeries($metricsList, 'weekly'),
            'has_data'     => $records->isNotEmpty(),
        ];
    }

    private function buildMonthlyChartData(int $employeeId, string $month): array
    {
        $start = \Carbon\Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $end   = $start->copy()->endOfMonth();

        $records = $this->attendanceRepo->getForEmployeeBetweenDates(
            $employeeId,
            $start->toDateString(),
            $end->toDateString()
        )->keyBy(fn ($a) => $a->date->toDateString());

        $labels     = [];
        $workHours  = [];
        $breakHours = [];
        $metricsList = [];

        for ($day = $start->copy(); $day->lte($end); $day->addDay()) {
            $key        = $day->toDateString();
            $attendance = $records->get($key);
            $metrics    = $this->metricsForAttendance($attendance);

            $labels[]      = $day->format('d');
            $workHours[]   = $metrics['work_hours'];
            $breakHours[]  = $metrics['break_hours'];
            $metricsList[] = $metrics;
        }

        return [
            'view'         => 'monthly',
            'period_label' => $start->format('F Y'),
            'labels'       => $labels,
            'work_hours'   => $workHours,
            'break_hours'  => $breakHours,
            'summary'      => $this->summarizeChartSeries($metricsList, 'monthly'),
            'has_data'     => $records->isNotEmpty(),
        ];
    }

    private function metricsForAttendance(?Attendance $attendance): array
    {
        if (!$attendance || !$attendance->check_in) {
            return ['work_hours' => 0, 'break_hours' => 0, 'work_minutes' => 0, 'break_minutes' => 0];
        }

        $asOf  = $attendance->check_out ?? now();
        $stats = AttendanceTimeCalculator::forAttendance($attendance, $asOf);

        return [
            'work_hours'  => round($stats['net_seconds'] / 3600, 2),
            'break_hours' => round($stats['total_break_seconds'] / 3600, 2),
            'work_minutes' => (int) round($stats['net_seconds'] / 60),
            'break_minutes' => (int) round($stats['total_break_seconds'] / 60),
        ];
    }

    private function summarizeChartSeries(array $metricsList, string $view = 'daily'): array
    {
        $totalWork  = round(collect($metricsList)->sum('work_hours'), 2);
        $totalBreak = round(collect($metricsList)->sum('break_hours'), 2);
        $daysWorked = collect($metricsList)->filter(fn ($m) => $m['work_hours'] > 0)->count();
        $targetHours = AttendanceTimeCalculator::targetHoursForView($view);
        $remainingHours = max(0, round($targetHours - $totalWork, 2));

        return [
            'total_work_hours'   => $totalWork,
            'total_break_hours'  => $totalBreak,
            'total_work_minutes' => (int) collect($metricsList)->sum('work_minutes'),
            'total_break_minutes'=> (int) collect($metricsList)->sum('break_minutes'),
            'days_worked'        => $daysWorked,
            'avg_work_hours'     => $daysWorked > 0 ? round($totalWork / $daysWorked, 2) : 0,
            'avg_work_minutes'   => $daysWorked > 0
                ? (int) round(collect($metricsList)->sum('work_minutes') / $daysWorked)
                : 0,
            'target_hours'       => $targetHours,
            'target_minutes'     => AttendanceTimeCalculator::hoursToMinutes($targetHours),
            'target_view'        => $view,
            'remaining_hours'    => $remainingHours,
            'remaining_minutes'  => AttendanceTimeCalculator::hoursToMinutes($remainingHours),
            'target_complete'    => $totalWork >= $targetHours,
            'progress_percent'   => $targetHours > 0
                ? min(100, round(($totalWork / $targetHours) * 100, 1))
                : 0,
        ];
    }
}

<?php

namespace App\Repositories;

use App\Contracts\Repositories\AttendanceRepositoryInterface;
use App\Models\Attendance;
use App\Models\AttendanceBreak;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Collection;

class AttendanceRepository extends BaseRepository implements AttendanceRepositoryInterface
{
    public function __construct(Attendance $model)
    {
        parent::__construct($model);
    }

    public function getTodayForEmployee(int $employeeId): ?Attendance
    {
        return Attendance::where('employee_id', $employeeId)
                         ->where('date', today()->toDateString())
                         ->with('breaks')
                         ->first();
    }

    public function getMonthlyForEmployee(int $employeeId, int $year, int $month): Collection
    {
        return Attendance::where('employee_id', $employeeId)
                         ->with('breaks')
                         ->whereYear('date', $year)
                         ->whereMonth('date', $month)
                         ->orderBy('date')
                         ->get();
    }

    public function getDailyForAllEmployees(string $date, array $filters = []): Collection
    {
        return $this->filteredActiveEmployeesQuery($filters)
            ->with([
                'department',
                'designation',
                'attendances' => fn ($q) => $q->where('date', $date)->with('breaks'),
            ])
            ->orderBy('name')
            ->get();
    }

    public function getMonthlyReport(int $year, int $month, array $filters = []): array
    {
        $employees   = $this->filteredActiveEmployeesQuery($filters)
            ->with(['department', 'designation'])
            ->orderBy('name')
            ->get();
        $workingDays = \Carbon\Carbon::createFromDate($year, $month, 1)->daysInMonth;
        $report      = [];

        foreach ($employees as $emp) {
            $records = Attendance::where('employee_id', $emp->id)
                ->whereYear('date', $year)
                ->whereMonth('date', $month)
                ->get();

            $report[] = [
                'employee'        => $emp,
                'present'         => $records->where('status', 'present')->count(),
                'absent'          => $records->where('status', 'absent')->count(),
                'half_day'        => $records->where('status', 'half_day')->count(),
                'on_leave'        => $records->where('status', 'on_leave')->count(),
                'not_marked'      => $workingDays - $records->count(),
                'early_checkouts' => $records->filter(fn ($r) =>
                    $r->note && str_starts_with($r->note, 'Early checkout:')
                )->count(),
            ];
        }

        return $report;
    }

    private function filteredActiveEmployeesQuery(array $filters)
    {
        $query = Employee::where('status', 'active');

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%')
                  ->orWhere('employee_id', 'like', '%' . $search . '%');
            });
        }

        if (!empty($filters['department_id'])) {
            $query->where('department_id', $filters['department_id']);
        }

        if (!empty($filters['designation_id'])) {
            $query->where('designation_id', $filters['designation_id']);
        }

        if (!empty($filters['employee_id'])) {
            $query->where('id', $filters['employee_id']);
        }

        return $query;
    }

    public function markOrUpdate(int $employeeId, string $date, array $data): Attendance
    {
        return Attendance::updateOrCreate(
            ['employee_id' => $employeeId, 'date' => $date],
            $data
        );
    }

    public function checkIn(int $employeeId): Attendance
    {
        return Attendance::create([
            'employee_id' => $employeeId,
            'date'        => today()->toDateString(),
            'check_in'    => now(),
            'status'      => 'present',
            'marked_by'   => 'self',
        ]);
    }

    public function checkOut(int $employeeId): Attendance
    {
        $attendance = Attendance::where('employee_id', $employeeId)
                                ->where('date', today()->toDateString())
                                ->firstOrFail();
        $attendance->update(['check_out' => now()]);
        return $attendance->fresh()->load('breaks');
    }

    public function startBreak(int $attendanceId, int $employeeId, string $markedBy): AttendanceBreak
    {
        // Serialize break actions per attendance to prevent duplicate zero-duration breaks
        Attendance::where('id', $attendanceId)->lockForUpdate()->firstOrFail();

        if (AttendanceBreak::where('attendance_id', $attendanceId)->whereNull('break_in')->exists()) {
            throw new \Exception('You are already on a break.');
        }

        return AttendanceBreak::create([
            'attendance_id' => $attendanceId,
            'employee_id'   => $employeeId,
            'break_out'     => now(),   // break start
            'break_in'      => null,    // break end (null = still on break)
            'marked_by'     => $markedBy,
        ]);
    }

    public function checkOutWithData(int $employeeId, array $data): Attendance
    {
        $attendance = Attendance::where('employee_id', $employeeId)
                                ->where('date', today()->toDateString())
                                ->firstOrFail();
        $attendance->update($data);
        return $attendance->fresh()->load('breaks');
    }

// endBreak → finds open break (break_in IS NULL) → sets break_in = now()
    public function endBreak(int $attendanceId): AttendanceBreak
    {
        Attendance::where('id', $attendanceId)->lockForUpdate()->firstOrFail();

        $break = AttendanceBreak::where('attendance_id', $attendanceId)
            ->whereNull('break_in')
            ->orderByDesc('break_out')
            ->first();

        if (!$break) {
            throw new \Exception('No active break found to end.');
        }

        $break->update(['break_in' => now()]);  // ← set END time
        return $break->fresh();
    }

    public function addBreak(int $attendanceId, int $employeeId, string $breakOut, ?string $breakIn): AttendanceBreak
    {
        return AttendanceBreak::create([
            'attendance_id' => $attendanceId,
            'employee_id'   => $employeeId,
            'break_out'     => $breakOut,
            'break_in'      => $breakIn,
            'marked_by'     => 'admin',
        ]);
    }

    public function deleteBreak(int $breakId): bool
    {
        return AttendanceBreak::findOrFail($breakId)->delete();
    }

    public function getTodayPresentCount(): int
    {
        return Attendance::where('date', today())->where('status', 'present')->count();
    }

    public function getTodayAbsentCount(): int
    {
        return Attendance::where('date', today())->where('status', 'absent')->count();
    }

    public function getTodayMarkedCount(): int
    {
        return Attendance::where('date', today())->count();
    }

    public function findAttendanceOrFail(int $id): Attendance
    {
        return Attendance::findOrFail($id);
    }

    public function getRecentForEmployee(int $employeeId, int $limit = 7): Collection
    {
        return Attendance::where('employee_id', $employeeId)
                         ->with('breaks')
                         ->orderByDesc('date')
                         ->take($limit)
                         ->get();
    }

    public function getForEmployeeBetweenDates(int $employeeId, string $from, string $to): Collection
    {
        return Attendance::where('employee_id', $employeeId)
                         ->whereBetween('date', [$from, $to])
                         ->with('breaks')
                         ->orderBy('date')
                         ->get();
    }

    public function getActiveEmployeeCount(array $filters = []): int
    {
        return $this->filteredActiveEmployeesQuery($filters)->count();
    }

    public function getStatusCountsBetween(string $from, string $to, array $filters = []): array
    {
        $employeeIds = $this->filteredActiveEmployeesQuery($filters)->pluck('id');

        if ($employeeIds->isEmpty()) {
            return ['present' => 0, 'absent' => 0, 'half_day' => 0, 'on_leave' => 0];
        }

        $counts = Attendance::whereIn('employee_id', $employeeIds)
            ->whereBetween('date', [$from, $to])
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return [
            'present'  => (int) ($counts['present'] ?? 0),
            'absent'   => (int) ($counts['absent'] ?? 0),
            'half_day' => (int) ($counts['half_day'] ?? 0),
            'on_leave' => (int) ($counts['on_leave'] ?? 0),
        ];
    }

    public function getRecordsBetweenForFilteredEmployees(string $from, string $to, array $filters = []): Collection
    {
        $employeeIds = $this->filteredActiveEmployeesQuery($filters)->pluck('id');

        if ($employeeIds->isEmpty()) {
            return new Collection();
        }

        return Attendance::whereIn('employee_id', $employeeIds)
            ->whereBetween('date', [$from, $to])
            ->with('breaks')
            ->orderBy('date')
            ->get();
    }
}
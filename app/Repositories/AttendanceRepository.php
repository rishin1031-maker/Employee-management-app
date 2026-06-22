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

    public function getDailyForAllEmployees(string $date): Collection
    {
        return Employee::where('status', 'active')
                       ->with(['attendances' => fn($q) => $q->where('date', $date)->with('breaks')])
                       ->orderBy('name')
                       ->get();
    }

    public function getMonthlyReport(int $year, int $month): array
    {
        $employees   = Employee::where('status', 'active')->orderBy('name')->get();
        $workingDays = \Carbon\Carbon::createFromDate($year, $month, 1)->daysInMonth;
        $report      = [];

        foreach ($employees as $emp) {
            $records = Attendance::where('employee_id', $emp->id)
                ->whereYear('date', $year)
                ->whereMonth('date', $month)
                ->get();

            $report[] = [
                'employee'   => $emp,
                'present'    => $records->where('status', 'present')->count(),
                'absent'     => $records->where('status', 'absent')->count(),
                'half_day'   => $records->where('status', 'half_day')->count(),
                'on_leave'   => $records->where('status', 'on_leave')->count(),
                'not_marked' => $workingDays - $records->count(),
            ];
        }

        return $report;
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
        return AttendanceBreak::create([
            'attendance_id' => $attendanceId,
            'employee_id'   => $employeeId,
            'break_out'     => now(),
            'marked_by'     => $markedBy,
        ]);
    }

    public function endBreak(int $attendanceId): AttendanceBreak
    {
        $break = AttendanceBreak::where('attendance_id', $attendanceId)
                                ->whereNull('break_in')
                                ->latest()
                                ->firstOrFail();
        $break->update(['break_in' => now()]);
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
}
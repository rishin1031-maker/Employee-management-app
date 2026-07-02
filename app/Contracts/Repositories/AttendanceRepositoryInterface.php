<?php

namespace App\Contracts\Repositories;

use App\Models\Attendance;
use App\Models\AttendanceBreak;
use Illuminate\Database\Eloquent\Collection;

interface AttendanceRepositoryInterface extends BaseRepositoryInterface
{
    public function getTodayForEmployee(int $employeeId): ?Attendance;
    public function getMonthlyForEmployee(int $employeeId, int $year, int $month): Collection;
    public function getDailyForAllEmployees(string $date, array $filters = []): Collection;
    public function getMonthlyReport(int $year, int $month, array $filters = []): array;
    public function markOrUpdate(int $employeeId, string $date, array $data): Attendance;
    public function checkIn(int $employeeId): Attendance;
    public function checkOut(int $employeeId): Attendance;
    public function startBreak(int $attendanceId, int $employeeId, string $markedBy): AttendanceBreak;
    public function endBreak(int $attendanceId): AttendanceBreak;
    public function addBreak(int $attendanceId, int $employeeId, string $breakOut, ?string $breakIn): AttendanceBreak;
    public function deleteBreak(int $breakId): bool;
    public function getTodayPresentCount(): int;
    public function getTodayAbsentCount(): int;
    public function getTodayMarkedCount(): int;
    public function checkOutWithData(int $employeeId, array $data): Attendance;
    public function findAttendanceOrFail(int $id): Attendance;
    public function getRecentForEmployee(int $employeeId, int $limit = 7): Collection;
    public function getForEmployeeBetweenDates(int $employeeId, string $from, string $to): Collection;
    public function getActiveEmployeeCount(array $filters = []): int;
    public function getStatusCountsBetween(string $from, string $to, array $filters = []): array;
    public function getRecordsBetweenForFilteredEmployees(string $from, string $to, array $filters = []): Collection;
}
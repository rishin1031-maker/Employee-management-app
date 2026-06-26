<?php

namespace App\Contracts\Repositories;

use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface LeaveRepositoryInterface extends BaseRepositoryInterface
{
    public function paginateWithFilters(array $filters, int $perPage = 15): LengthAwarePaginator;
    public function getEmployeeLeaves(int $employeeId, int $perPage = 10): LengthAwarePaginator;
    public function getPendingCount(): int;
    public function getRecentPending(int $limit = 5): Collection;
    public function findOrCreateBalance(int $employeeId, int $year): LeaveBalance;
    public function incrementUsed(int $employeeId, string $type, int $days, int $year): void;
    public function hasOverlap(int $employeeId, string $fromDate, string $toDate): bool;
    public function approve(int $id, int $adminId, ?string $note): LeaveRequest;
    public function reject(int $id, int $adminId, ?string $note): LeaveRequest;
    public function findWithAdminDetails(int $id): LeaveRequest;
    public function countByEmployeeAndStatus(int $employeeId, string $status): int;
    public function getRecentForEmployee(int $employeeId, int $limit = 5): Collection;
    public function belongsToEmployee(int $leaveId, int $employeeId): bool;
    public function getStatsForEmployee(int $employeeId): array;
}
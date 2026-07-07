<?php

namespace App\Repositories;

use App\Contracts\Repositories\LeaveRepositoryInterface;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class LeaveRepository extends BaseRepository implements LeaveRepositoryInterface
{
    public function __construct(LeaveRequest $model)
    {
        parent::__construct($model);
    }

    public function paginateWithFilters(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = LeaveRequest::with(['employee.department', 'actionedBy']);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['employee_id'])) {
            $query->where('employee_id', $filters['employee_id']);
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        return $query->latest()->paginate($perPage)->withQueryString();
    }

    public function getEmployeeLeaves(int $employeeId,array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return LeaveRequest::where('employee_id', $employeeId)
                            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
                            ->when($filters['type'] ?? null, fn ($q, $type) => $q->where('type', $type))
                            ->latest()
                            ->paginate($perPage);
    }

    public function getPendingCount(): int
    {
        return LeaveRequest::where('status', 'pending')->count();
    }

    public function getRecentPending(int $limit = 5): Collection
    {
        return LeaveRequest::with('employee')
                           ->where('status', 'pending')
                           ->latest()
                           ->take($limit)
                           ->get();
    }

    public function findOrCreateBalance(int $employeeId, int $year): LeaveBalance
    {
        return LeaveBalance::firstOrCreate(
            ['employee_id' => $employeeId, 'year' => $year],
            [
                'casual_total' => 12, 'casual_used' => 0,
                'sick_total'   => 10, 'sick_used'   => 0,
                'annual_total' => 15, 'annual_used' => 0,
            ]
        );
    }

    public function incrementUsed(int $employeeId, string $type, int $days, int $year): void
    {
        $balance = $this->findOrCreateBalance($employeeId, $year);
        $balance->increment($type . '_used', $days);
    }

    public function hasOverlap(int $employeeId, string $fromDate, string $toDate): bool
    {
        return LeaveRequest::where('employee_id', $employeeId)
            ->whereIn('status', ['pending', 'approved'])
            ->where(function ($q) use ($fromDate, $toDate) {
                $q->whereBetween('from_date', [$fromDate, $toDate])
                  ->orWhereBetween('to_date', [$fromDate, $toDate]);
            })->exists();
    }

    public function approve(int $id, int $adminId, ?string $note): LeaveRequest
    {
        $leave = $this->findOrFail($id);
        $leave->update([
            'status'      => 'approved',
            'admin_note'  => $note,
            'actioned_by' => $adminId,
            'actioned_at' => now(),
        ]);
        return $leave->fresh()->load(['employee', 'actionedBy']);
    }

    public function reject(int $id, int $adminId, ?string $note): LeaveRequest
    {
        $leave = $this->findOrFail($id);
        $leave->update([
            'status'      => 'rejected',
            'admin_note'  => $note,
            'actioned_by' => $adminId,
            'actioned_at' => now(),
        ]);
        return $leave->fresh()->load(['employee', 'actionedBy']);
    }

    public function findWithAdminDetails(int $id): LeaveRequest
    {
        return LeaveRequest::with(['employee.department', 'employee.designation', 'actionedBy'])
                           ->findOrFail($id);
    }

    public function countByEmployeeAndStatus(int $employeeId, string $status): int
    {
        return LeaveRequest::where('employee_id', $employeeId)
                           ->where('status', $status)
                           ->count();
    }

    public function getRecentForEmployee(int $employeeId, int $limit = 5): Collection
    {
        return LeaveRequest::where('employee_id', $employeeId)
                           ->latest()
                           ->take($limit)
                           ->get();
    }

    public function belongsToEmployee(int $leaveId, int $employeeId): bool
    {
        return LeaveRequest::where('id', $leaveId)
                           ->where('employee_id', $employeeId)
                           ->exists();
    }

    public function getStatsForEmployee(int $employeeId): array
    {
        return [
            'pending'  => $this->countByEmployeeAndStatus($employeeId, 'pending'),
            'approved' => $this->countByEmployeeAndStatus($employeeId, 'approved'),
            'rejected' => $this->countByEmployeeAndStatus($employeeId, 'rejected'),
        ];
    }
}
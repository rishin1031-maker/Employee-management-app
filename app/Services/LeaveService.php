<?php

namespace App\Services;

use App\Contracts\Repositories\LeaveRepositoryInterface;
use App\Jobs\SendLeaveAppliedJob;
use App\Jobs\SendLeaveStatusJob;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class LeaveService
{
    public function __construct(
        private LeaveRepositoryInterface $leaveRepo
    ) {}

    public function getPaginatedForAdmin(array $filters): LengthAwarePaginator
    {
        return $this->leaveRepo->paginateWithFilters($filters);
    }

    public function getEmployeeLeaves(int $employeeId): LengthAwarePaginator
    {
        return $this->leaveRepo->getEmployeeLeaves($employeeId);
    }

    public function getOrCreateBalance(int $employeeId): LeaveBalance
    {
        return $this->leaveRepo->findOrCreateBalance($employeeId, now()->year);
    }

    public function applyLeave(int $employeeId, array $data): LeaveRequest
    {
        $from  = Carbon::parse($data['from_date']);
        $to    = Carbon::parse($data['to_date']);
        $days  = $from->diffInWeekdays($to) + 1;

        // Check balance
        $balance   = $this->getOrCreateBalance($employeeId);
        $remaining = $balance->remaining[$data['type']];

        if ($days > $remaining) {
            throw new \Exception(
                "Insufficient {$data['type']} leave balance. You have {$remaining} day(s) remaining."
            );
        }

        // Check overlap
        if ($this->leaveRepo->hasOverlap($employeeId, $data['from_date'], $data['to_date'])) {
            throw new \Exception('You already have a leave request for this date range.');
        }

        $leave = $this->leaveRepo->create([
            'employee_id' => $employeeId,
            'type'        => $data['type'],
            'from_date'   => $data['from_date'],
            'to_date'     => $data['to_date'],
            'days'        => $days,
            'reason'      => $data['reason'],
            'status'      => 'pending',
        ]);

        // Dispatch job
        SendLeaveAppliedJob::dispatch($leave->load('employee'));

        return $leave;
    }

    public function approveLeave(int $id, int $adminId, ?string $note): LeaveRequest
    {
        $leave = $this->leaveRepo->approve($id, $adminId, $note);

        // Deduct balance
        $this->leaveRepo->incrementUsed(
            $leave->employee_id,
            $leave->type,
            $leave->days,
            $leave->from_date->year
        );

        // Notify employee
        SendLeaveStatusJob::dispatch($leave);

        return $leave;
    }

    public function rejectLeave(int $id, int $adminId, ?string $note): LeaveRequest
    {
        $leave = $this->leaveRepo->reject($id, $adminId, $note);

        // Notify employee
        SendLeaveStatusJob::dispatch($leave);

        return $leave;
    }

    public function createLeaveByAdmin(array $data, int $adminId): LeaveRequest
    {
        $days  = Carbon::parse($data['from_date'])->diffInWeekdays(Carbon::parse($data['to_date'])) + 1;

        $leave = $this->leaveRepo->create([
            'employee_id'      => $data['employee_id'],
            'type'             => $data['type'],
            'from_date'        => $data['from_date'],
            'to_date'          => $data['to_date'],
            'days'             => $days,
            'reason'           => $data['reason'],
            'status'           => $data['status'],
            'actioned_by'      => $data['status'] !== 'pending' ? $adminId : null,
            'actioned_at'      => $data['status'] !== 'pending' ? now() : null,
            'created_by_admin' => true,
        ]);

        if ($data['status'] === 'approved') {
            $this->leaveRepo->incrementUsed(
                $data['employee_id'], $data['type'], $days, now()->year
            );
        }

        return $leave;
    }

    public function cancelLeave(int $leaveId, int $employeeId): bool
    {
        $leave = $this->leaveRepo->findOrFail($leaveId);

        if ($leave->employee_id !== $employeeId) {
            throw new \Exception('Unauthorized action.');
        }

        if ($leave->status !== 'pending') {
            throw new \Exception('Only pending leave requests can be cancelled.');
        }

        return $this->leaveRepo->delete($leaveId);
    }

    public function getPendingCount(): int
    {
        return $this->leaveRepo->getPendingCount();
    }

    public function getRecentPending(int $limit = 5): Collection
    {
        return $this->leaveRepo->getRecentPending($limit);
    }
}
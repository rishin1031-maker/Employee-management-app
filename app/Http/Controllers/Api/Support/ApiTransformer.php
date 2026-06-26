<?php

namespace App\Http\Controllers\Api\Support;

use App\Models\Admin;
use App\Models\Attendance;
use App\Models\AttendanceBreak;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\Salary;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ApiTransformer
{
    public static function paginated(LengthAwarePaginator $paginator, callable $transform): array
    {
        return [
            'items' => $paginator->getCollection()->map($transform)->values(),
            'meta'  => [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
            ],
        ];
    }

    public static function admin(Admin $admin): array
    {
        return [
            'id'    => $admin->id,
            'name'  => $admin->name,
            'email' => $admin->email,
        ];
    }

    public static function employee(Employee $employee, bool $detailed = false): array
    {
        $employee->loadMissing(['department', 'designation']);

        $data = [
            'id'          => $employee->id,
            'employee_id' => $employee->employee_id,
            'name'        => $employee->name,
            'email'       => $employee->email,
            'phone'       => $employee->phone,
            'gender'      => $employee->gender,
            'dob'         => $employee->dob?->toDateString(),
            'status'      => $employee->status,
            'image_url'   => $employee->image_url,
            'department'  => $employee->department ? self::department($employee->department) : null,
            'designation' => $employee->designation ? self::designation($employee->designation) : null,
        ];

        if ($detailed) {
            $data['must_change_password'] = $employee->must_change_password;
            $data['last_login_at']        = $employee->last_login_at?->toIso8601String();
        }

        return $data;
    }

    public static function department(Department $department): array
    {
        return [
            'id'          => $department->id,
            'name'        => $department->name,
            'description' => $department->description,
            'status'      => $department->status,
            'designations_count' => $department->designations_count ?? null,
            'employees_count'    => $department->employees_count ?? null,
        ];
    }

    public static function designation(Designation $designation): array
    {
        $designation->loadMissing('department');

        return [
            'id'              => $designation->id,
            'name'            => $designation->name,
            'status'          => $designation->status,
            'department_id'   => $designation->department_id,
            'department_name' => $designation->department?->name,
            'employees_count' => $designation->employees_count ?? null,
        ];
    }

    public static function leave(LeaveRequest $leave): array
    {
        $leave->loadMissing(['employee.department', 'employee.designation']);

        return [
            'id'          => $leave->id,
            'employee_id' => $leave->employee_id,
            'employee'    => $leave->employee ? self::employee($leave->employee) : null,
            'type'        => $leave->type,
            'from_date'   => $leave->from_date?->toDateString(),
            'to_date'     => $leave->to_date?->toDateString(),
            'days'        => $leave->days,
            'reason'      => $leave->reason,
            'status'      => $leave->status,
            'admin_note'  => $leave->admin_note,
            'actioned_at' => $leave->actioned_at?->toIso8601String(),
            'created_at'  => $leave->created_at?->toIso8601String(),
        ];
    }

    public static function leaveBalance(LeaveBalance $balance): array
    {
        return [
            'year'    => $balance->year,
            'casual'  => ['total' => $balance->casual_total,  'used' => $balance->casual_used,  'remaining' => $balance->casual_total - $balance->casual_used],
            'sick'    => ['total' => $balance->sick_total,    'used' => $balance->sick_used,    'remaining' => $balance->sick_total - $balance->sick_used],
            'annual'  => ['total' => $balance->annual_total,  'used' => $balance->annual_used,  'remaining' => $balance->annual_total - $balance->annual_used],
        ];
    }

    public static function attendance(Attendance $attendance): array
    {
        $attendance->loadMissing('breaks');

        return [
            'id'                   => $attendance->id,
            'employee_id'          => $attendance->employee_id,
            'date'                 => $attendance->date?->toDateString(),
            'check_in'             => $attendance->check_in?->toIso8601String(),
            'check_out'            => $attendance->check_out?->toIso8601String(),
            'status'               => $attendance->status,
            'marked_by'            => $attendance->marked_by,
            'note'                 => $attendance->note,
            'net_hours_worked'     => $attendance->net_hours_worked,
            'total_break_minutes'  => $attendance->total_break_minutes,
            'is_eight_hours_complete' => $attendance->is_eight_hours_complete,
            'breaks'               => $attendance->breaks->map(fn ($b) => self::break($b))->values(),
        ];
    }

    public static function break(AttendanceBreak $break): array
    {
        return [
            'id'        => $break->id,
            'break_out' => $break->break_out?->toIso8601String(),
            'break_in'  => $break->break_in?->toIso8601String(),
            'duration'  => $break->duration_label,
            'marked_by' => $break->marked_by,
        ];
    }

    public static function salary(?Salary $salary): ?array
    {
        if (!$salary) {
            return null;
        }

        return [
            'id'              => $salary->id,
            'basic'           => (float) $salary->basic,
            'hra'             => (float) $salary->hra,
            'transport'       => (float) $salary->transport,
            'medical'         => (float) $salary->medical,
            'other_allowance' => (float) $salary->other_allowance,
            'pf_deduction'    => (float) $salary->pf_deduction,
            'tax_deduction'   => (float) $salary->tax_deduction,
            'other_deduction' => (float) $salary->other_deduction,
            'gross_salary'    => (float) $salary->gross_salary,
            'net_salary'      => (float) $salary->net_salary,
            'effective_from'  => $salary->effective_from?->toDateString(),
            'note'            => $salary->note,
        ];
    }

    public static function notification(DatabaseNotification $notification): array
    {
        return [
            'id'         => $notification->id,
            'type'       => class_basename($notification->type),
            'data'       => $notification->data,
            'read_at'    => $notification->read_at?->toIso8601String(),
            'created_at' => $notification->created_at?->toIso8601String(),
        ];
    }

    public static function tokenResponse(string $token, Model $user, callable $transform, int $ttlMinutes): array
    {
        return [
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in'   => $ttlMinutes * 60,
            'user'         => $transform($user),
        ];
    }
}

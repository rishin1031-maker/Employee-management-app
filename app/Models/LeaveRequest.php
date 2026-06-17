<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveRequest extends Model
{
    protected $fillable = [
        'employee_id', 'type', 'from_date', 'to_date', 'days',
        'reason', 'status', 'admin_note', 'actioned_by',
        'actioned_at', 'created_by_admin',
    ];

    protected $casts = [
        'from_date'    => 'date',
        'to_date'      => 'date',
        'actioned_at'  => 'datetime',
        'created_by_admin' => 'boolean',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function actionedBy()
    {
        return $this->belongsTo(Admin::class, 'actioned_by');
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'approved' => 'green',
            'rejected' => 'red',
            default    => 'yellow',
        };
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $fillable = [
        'employee_id', 'date', 'check_in', 'check_out',
        'status', 'marked_by', 'note',
    ];

    protected $casts = [
        'date'      => 'date',
        'check_in'  => 'datetime',
        'check_out' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function getHoursWorkedAttribute(): ?string
    {
        if (!$this->check_in || !$this->check_out) return null;
        $mins = $this->check_in->diffInMinutes($this->check_out);
        return floor($mins / 60) . 'h ' . ($mins % 60) . 'm';
    }
}
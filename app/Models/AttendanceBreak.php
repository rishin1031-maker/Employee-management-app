<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceBreak extends Model
{
    protected $fillable = [
        'attendance_id', 'employee_id',
        'break_out', 'break_in', 'marked_by',
    ];

    protected $casts = [
        'break_out' => 'datetime',
        'break_in'  => 'datetime',
    ];

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    // Duration of this break in minutes
    public function getDurationMinutesAttribute(): ?int
    {
        if (!$this->break_in) return null;
        return (int) $this->break_out->diffInMinutes($this->break_in);
    }

    // Human readable duration e.g. "45m" or "1h 10m"
    public function getDurationLabelAttribute(): string
    {
        $mins = $this->duration_minutes;
        if ($mins === null) return 'Ongoing';
        if ($mins < 60) return $mins . 'm';
        return floor($mins / 60) . 'h ' . ($mins % 60) . 'm';
    }
}
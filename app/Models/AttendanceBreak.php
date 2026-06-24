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
        if (!$this->break_in || !$this->break_out) return null;
    
        $out = \Carbon\Carbon::parse($this->break_out);
        $in  = \Carbon\Carbon::parse($this->break_in);
    
        // break_in must be after break_out
        if ($in->lte($out)) return 0;
    
        return (int) $out->diffInMinutes($in);
    }
    

    // Human readable duration e.g. "45m" or "1h 10m"
    public function getDurationLabelAttribute(): string
    {
        $mins = $this->duration_minutes;
        if ($mins === null) return 'Ongoing';
        if ($mins === 0)    return '< 1m';
        if ($mins < 60)     return $mins . 'm';
        return floor($mins / 60) . 'h ' . ($mins % 60) . 'm';
    }
}
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

    // Add relationship
    public function breaks()
    {
        return $this->hasMany(AttendanceBreak::class);
    }

    // Total break minutes
    public function getTotalBreakMinutesAttribute(): int
    {
        return $this->breaks
            ->filter(fn($b) => $b->break_in !== null)
            ->sum(fn($b) => $b->break_out->diffInMinutes($b->break_in));
    }

    // Is employee currently on break?
    public function getOnBreakAttribute(): bool
    {
        return $this->breaks()
            ->whereNull('break_in')
            ->exists();
    }

    // Active (ongoing) break
    public function activeBreak()
    {
        return $this->hasOne(AttendanceBreak::class)
                    ->whereNull('break_in')
                    ->latest();
    }

    // Net hours worked (total - breaks)
    public function getNetHoursWorkedAttribute(): ?string
    {
        if (!$this->check_in || !$this->check_out) return null;

        $totalMins = $this->check_in->diffInMinutes($this->check_out);
        $breakMins = $this->total_break_minutes;
        $netMins   = max(0, $totalMins - $breakMins);

        return floor($netMins / 60) . 'h ' . ($netMins % 60) . 'm';
    }

    // Keep old hours_worked as gross (without break deduction)
    public function getHoursWorkedAttribute(): ?string
    {
        if (!$this->check_in || !$this->check_out) return null;
        $mins = $this->check_in->diffInMinutes($this->check_out);
        return floor($mins / 60) . 'h ' . ($mins % 60) . 'm';
    }    
}
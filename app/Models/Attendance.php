<?php

namespace App\Models;

use App\Services\AttendanceTimeCalculator;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $fillable = [
        'employee_id', 'date', 'check_in', 'check_out',
        'status', 'marked_by', 'note',
        'continuous_reminder_sent_at', 'continuous_session_anchor_at',
    ];

    protected $casts = [
        'date' => 'date',
        'check_in' => 'datetime',
        'check_out' => 'datetime',
        'continuous_reminder_sent_at' => 'datetime',
        'continuous_session_anchor_at' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function breaks()
    {
        return $this->hasMany(AttendanceBreak::class);
    }

    public function getTotalBreakMinutesAttribute(): int
    {
        $breaks = $this->relationLoaded('breaks')
            ? $this->breaks
            : $this->breaks()->get();

        return AttendanceTimeCalculator::totalBreakMinutes($breaks);
    }

    public function getOnBreakAttribute(): bool
    {
        if ($this->relationLoaded('breaks')) {
            return $this->breaks->contains(fn ($b) => $b->break_in === null);
        }

        return $this->breaks()->whereNull('break_in')->exists();
    }

    public function activeBreak()
    {
        return $this->hasOne(AttendanceBreak::class)
                    ->whereNull('break_in')
                    ->latest('break_out');
    }

    public function getHoursWorkedAttribute(): ?string
    {
        if (!$this->check_in || !$this->check_out) {
            return null;
        }

        $mins = (int) $this->check_in->diffInMinutes($this->check_out);

        return floor($mins / 60) . 'h ' . ($mins % 60) . 'm';
    }

    public function getNetHoursWorkedAttribute(): ?string
    {
        $netMins = $this->net_minutes;

        return $netMins > 0 || ($this->check_in && $this->check_out)
            ? floor($netMins / 60) . 'h ' . ($netMins % 60) . 'm'
            : null;
    }

    public function getNetMinutesAttribute(): int
    {
        if (!$this->check_in || !$this->check_out) {
            return 0;
        }

        return (int) floor(AttendanceTimeCalculator::netSecondsForCompletedDay($this) / 60);
    }

    public function getIsEightHoursCompleteAttribute(): bool
    {
        return $this->net_minutes >= 480;
    }

    public function getRemainingMinutesAttribute(): int
    {
        return max(0, 480 - $this->net_minutes);
    }
}

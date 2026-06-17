<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveBalance extends Model
{
    protected $fillable = [
        'employee_id', 'year',
        'casual_total', 'casual_used',
        'sick_total', 'sick_used',
        'annual_total', 'annual_used',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function getRemainingAttribute(): array
    {
        return [
            'casual' => $this->casual_total - $this->casual_used,
            'sick'   => $this->sick_total   - $this->sick_used,
            'annual' => $this->annual_total - $this->annual_used,
        ];
    }
}
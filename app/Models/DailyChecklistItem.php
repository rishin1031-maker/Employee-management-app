<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyChecklistItem extends Model
{
    protected $fillable = [
        'employee_id',
        'task_date',
        'title',
        'is_completed',
        'completed_at',
        'sort_order',
    ];

    protected $casts = [
        'task_date'    => 'date',
        'is_completed' => 'boolean',
        'completed_at' => 'datetime',
        'sort_order'   => 'integer',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}

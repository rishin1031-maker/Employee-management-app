<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Designation extends Model
{
    protected $fillable = ['name', 'department_id','status'];

    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value !== null ? ucwords(strtolower($value)) : null,
            set: fn (?string $value) => $value !== null ? strtolower(trim($value)) : null,
        );
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }
}
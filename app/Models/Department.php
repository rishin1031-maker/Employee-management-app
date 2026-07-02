<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    protected $fillable = ['name', 'description', 'status'];

    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value !== null ? ucwords(strtolower($value)) : null,
            set: fn (?string $value) => $value !== null ? strtolower(trim($value)) : null,
        );
    }

    public function designations(): HasMany
    {
        return $this->hasMany(Designation::class);
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }
}
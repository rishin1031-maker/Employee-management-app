<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Salary extends Model
{
    protected $fillable = [
        'employee_id', 'basic', 'hra', 'transport', 'medical',
        'pf_deduction', 'tax_deduction', 'other_allowance',
        'other_deduction', 'effective_from', 'note',
    ];

    protected $casts = [
        'effective_from' => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function getGrossSalaryAttribute(): float
    {
        return $this->basic + $this->hra + $this->transport
             + $this->medical + $this->other_allowance;
    }

    public function getNetSalaryAttribute(): float
    {
        return $this->gross_salary - $this->pf_deduction
             - $this->tax_deduction - $this->other_deduction;
    }
}
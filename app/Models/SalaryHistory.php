<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalaryHistory extends Model
{
    protected $fillable = [
        'employee_id', 'salary_id', 'basic', 'hra', 'transport', 'medical',
        'pf_deduction', 'tax_deduction', 'other_allowance', 'other_deduction',
        'gross_salary', 'net_salary', 'effective_from', 'note',
    ];

    protected $casts = [
        'effective_from' => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
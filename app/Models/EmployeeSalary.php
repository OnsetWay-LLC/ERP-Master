<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeSalary extends Model
{
    protected $fillable = [
        'employee_id',
        'salary_mode',
        'bank_account_name',
        'bank_account_number',
        'iban',
        'salary_value',
        'social_security_deduction',
        'insurance_deduction',
        'tax_deduction',
        'effective_from',
        'effective_to',
        'is_active',
    ];

    protected $casts = [
        'salary_value' => 'decimal:2',
        'social_security_deduction' => 'decimal:2',
        'insurance_deduction' => 'decimal:2',
        'tax_deduction' => 'decimal:2',
        'effective_from' => 'date',
        'effective_to' => 'date',
        'is_active' => 'boolean',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
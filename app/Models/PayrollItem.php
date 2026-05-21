<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollItem extends Model
{
    protected $fillable = [
        'payroll_id',
        'employee_id',
        'basic_salary',
        'total_allowances',
        'social_security_deduction',
        'insurance_deduction',
        'tax_deduction',
        'leave_deduction',
        'total_deductions',
        'net_salary',
        'salary_mode',
    ];

    protected $casts = [
        'basic_salary' => 'decimal:2',
        'total_allowances' => 'decimal:2',
        'social_security_deduction' => 'decimal:2',
        'insurance_deduction' => 'decimal:2',
        'tax_deduction' => 'decimal:2',
        'leave_deduction' => 'decimal:2',
        'total_deductions' => 'decimal:2',
        'net_salary' => 'decimal:2',
    ];

    public function payroll()
    {
        return $this->belongsTo(Payroll::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
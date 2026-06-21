<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalesPerson extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'employee_id',
        'user_id',
        'sales_person_name_ar',
        'sales_person_name_en',
        'commission_rate',
        'use_default_account',
        'commission_account_id',
        'payroll_account_id',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'use_default_account' => 'boolean',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function targets()
    {
        return $this->hasMany(SalesPersonTarget::class);
    }

    public function commissionAccount()
    {
        return $this->belongsTo(ChartOfAccount::class, 'commission_account_id');
    }

    public function payrollAccount()
    {
        return $this->belongsTo(ChartOfAccount::class, 'payroll_account_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
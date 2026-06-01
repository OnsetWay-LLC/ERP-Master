<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiscountSetting extends Model
{
    protected $fillable = [
        'company_id',
        'sub_accountant_max_discount',
        'department_manager_max_discount',
        'created_by',
    ];

    protected $casts = [
        'sub_accountant_max_discount' => 'decimal:2',
        'department_manager_max_discount' => 'decimal:2',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
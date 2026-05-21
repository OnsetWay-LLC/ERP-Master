<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollTaxBracket extends Model
{
    protected $fillable = [
        'payroll_tax_setting_id',
        'from_amount',
        'to_amount',
        'rate',
        'sort_order',
    ];

    protected $casts = [
        'from_amount' => 'decimal:2',
        'to_amount' => 'decimal:2',
        'rate' => 'decimal:2',
    ];

    public function setting()
    {
        return $this->belongsTo(PayrollTaxSetting::class, 'payroll_tax_setting_id');
    }
}
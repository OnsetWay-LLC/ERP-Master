<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PayrollTaxSetting extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'single_or_working_wife_exemption',
        'married_not_working_wife_exemption',
        'is_active',
    ];

    protected $casts = [
        'single_or_working_wife_exemption' => 'decimal:2',
        'married_not_working_wife_exemption' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function brackets()
    {
        return $this->hasMany(PayrollTaxBracket::class)
            ->orderBy('sort_order');
    }
}
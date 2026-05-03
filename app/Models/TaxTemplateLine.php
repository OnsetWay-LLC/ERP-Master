<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaxTemplateLine extends Model
{
    protected $fillable = [
        'tax_template_id',
        'type',
        'account_id',
        'tax_rate',
        'amount',
    ];

    public function template()
    {
        return $this->belongsTo(TaxTemplate::class, 'tax_template_id');
    }

    public function account()
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_id');
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesReturnTax extends Model
{
    protected $fillable = [
        'sales_return_id',
        'tax_template_id',
        'tax_template_line_id',
        'title',
        'type',
        'account_id',
        'tax_rate',
        'amount',
    ];

    public function salesReturn() { return $this->belongsTo(SalesReturn::class); }
    public function account() { return $this->belongsTo(ChartOfAccount::class, 'account_id'); }
}
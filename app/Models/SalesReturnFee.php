<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesReturnFee extends Model
{
    protected $fillable = [
        'sales_return_id',
        'fees_template_id',
        'title',
        'type',
        'account_id',
        'fees_rate',
        'amount',
    ];

    public function salesReturn() { return $this->belongsTo(SalesReturn::class); }
    public function account() { return $this->belongsTo(ChartOfAccount::class, 'account_id'); }
}
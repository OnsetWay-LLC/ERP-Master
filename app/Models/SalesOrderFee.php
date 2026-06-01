<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesOrderFee extends Model
{
    protected $fillable = [
        'sales_order_id',
        'fees_template_id',
        'title',
        'type',
        'account_id',
        'fees_rate',
        'amount',
    ];

    public function salesOrder()
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function feesTemplate()
    {
        return $this->belongsTo(FeesTemplate::class);
    }

    public function account()
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_id');
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrderTax extends Model
{
    protected $fillable = [
        'purchase_order_id',
        'type',
        'account_id',
        'tax_rate',
        'amount',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function account()
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_id');
    }
}
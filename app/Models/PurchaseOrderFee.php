<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrderFee extends Model
{
    protected $fillable = [
        'purchase_order_id',
        'fees_template_id',
        'title',
        'type',
        'account_id',
        'fees_rate',
        'amount',
    ];

    public function purchaseOrder() { return $this->belongsTo(PurchaseOrder::class); }
    public function account() { return $this->belongsTo(ChartOfAccount::class, 'account_id'); }
}
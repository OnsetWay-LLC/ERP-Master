<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseReceiptFee extends Model
{
    protected $fillable = [
        'purchase_receipt_id',
        'fees_template_id',
        'title',
        'type',
        'account_id',
        'fees_rate',
        'amount',
    ];

    public function purchaseReceipt()
    {
        return $this->belongsTo(PurchaseReceipt::class);
    }

    public function account()
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_id');
    }
}
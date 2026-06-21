<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseReceiptTax extends Model
{
    protected $fillable = [
        'purchase_receipt_id',
        'tax_template_id',
        'tax_template_line_id',
        'title',
        'type',
        'account_id',
        'tax_rate',
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
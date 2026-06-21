<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseReceipt extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'purchase_order_id',
        'supplier_id',
        'receipt_number',
        'receipt_date',
        'posting_time',
        'total_qty',
        'total',
        'tax_total',
        'fees_total',
        'additional_discount_percentage',
        'additional_discount_amount',
        'grand_total',
        'status',
        'created_by',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseReceiptItem::class);
    }

    public function taxes()
    {
        return $this->hasMany(PurchaseReceiptTax::class);
    }

    public function fees()
    {
        return $this->hasMany(PurchaseReceiptFee::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
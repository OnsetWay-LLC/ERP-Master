<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesReturnItem extends Model
{
    protected $fillable = [
        'sales_return_id',
        'sales_invoice_item_id',
        'item_id',
        'warehouse_id',
        'item_code',
        'item_name_ar',
        'item_name_en',
        'original_qty',
        'returned_qty',
        'rate',
        'amount',
    ];

    public function salesReturn() { return $this->belongsTo(SalesReturn::class); }
    public function salesInvoiceItem() { return $this->belongsTo(SalesInvoiceItem::class); }
    public function item() { return $this->belongsTo(Item::class); }
    public function warehouse() { return $this->belongsTo(Warehouse::class); }
}
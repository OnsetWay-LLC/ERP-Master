<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesInvoiceItem extends Model
{
    protected $fillable = [
        'sales_invoice_id', 'item_id', 'warehouse_id', 'item_code', 
        'item_name_ar', 'item_name_en', 'quantity', 'rate', 'amount'
    ];

    public function item() 
    { return $this->belongsTo(Item::class); }
    public function warehouse() 
    { return $this->belongsTo(Warehouse::class); }
}
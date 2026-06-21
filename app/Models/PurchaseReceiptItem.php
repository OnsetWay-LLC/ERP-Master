<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseReceiptItem extends Model
{
    protected $fillable = [
        'purchase_receipt_id',
        'purchase_order_item_id',
        'item_id',
        'barcode',
        'accepted_warehouse_id',
        'rejected_warehouse_id',
        'accepted_qty',
        'rejected_qty',
        'total_qty',
        'rate',
        'amount',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function acceptedWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'accepted_warehouse_id');
    }

    public function rejectedWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'rejected_warehouse_id');
    }

    public function purchaseOrderItem()
    {
        return $this->belongsTo(PurchaseOrderItem::class, 'purchase_order_item_id');
    }
}
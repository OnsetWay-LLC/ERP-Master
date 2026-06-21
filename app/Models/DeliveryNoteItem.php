<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryNoteItem extends Model
{
    protected $fillable = [
        'delivery_note_id',
        'sales_order_item_id',
        'pick_list_item_id',
        'item_id',
        'warehouse_id',
        'item_code',
        'item_name_ar',
        'item_name_en',
        'quantity',
        'rate',
        'amount',
    ];

    public function deliveryNote() { return $this->belongsTo(DeliveryNote::class); }
    public function item() { return $this->belongsTo(Item::class); }
    public function warehouse() { return $this->belongsTo(Warehouse::class); }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PickListItem extends Model
{
    protected $fillable = [
        'pick_list_id',
        'sales_order_item_id',
        'item_id',
        'warehouse_id',
        'item_code',
        'item_name_ar',
        'item_name_en',
        'required_quantity',
        'picked_quantity',
    ];

    public function pickList()
    {
        return $this->belongsTo(PickList::class);
    }

    public function salesOrderItem()
    {
        return $this->belongsTo(SalesOrderItem::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }
}
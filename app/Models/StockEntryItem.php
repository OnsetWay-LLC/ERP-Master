<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockEntryItem extends Model
{
    protected $fillable = [
        'stock_entry_id',
        'item_id',
        'barcode',
        'source_warehouse_id',
        'target_warehouse_id',
        'quantity',
        'basic_rate',
        'incoming_value',
        'outgoing_value',
        'value_difference',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'basic_rate' => 'decimal:2',
        'incoming_value' => 'decimal:2',
        'outgoing_value' => 'decimal:2',
        'value_difference' => 'decimal:2',
    ];

    public function stockEntry()
    {
        return $this->belongsTo(StockEntry::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function sourceWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'source_warehouse_id');
    }

    public function targetWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'target_warehouse_id');
    }
}
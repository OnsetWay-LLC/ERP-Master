<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WarehouseStock extends Model
{
    protected $fillable = [
        'company_id',
        'item_id',
        'warehouse_id',
        'quantity',
        'average_rate',
        'stock_value',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'average_rate' => 'decimal:2',
        'stock_value' => 'decimal:2',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }
}
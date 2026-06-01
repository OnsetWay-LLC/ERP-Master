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
        'reserved_quantity',
        'average_rate',
        'stock_value',
    ];
 protected $appends = [
        'available_quantity',
    ];
    protected $casts = [
        'quantity' => 'decimal:2',
        'reserved_quantity' => 'decimal:2',
        'average_rate' => 'decimal:2',
        'stock_value' => 'decimal:2',
    ];
 public function getAvailableQuantityAttribute()
    {
        return $this->quantity - $this->reserved_quantity;
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
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
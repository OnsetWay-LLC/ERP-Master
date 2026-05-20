<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockLedger extends Model
{
    protected $table = 'stock_ledger';

    protected $fillable = [
        'company_id',
        'item_id',
        'warehouse_id',
        'entry_date',
        'reference_type',
        'reference_id',
        'quantity_in',
        'quantity_out',
        'balance_qty',
        'basic_rate',
        'stock_value',
        'balance_value',
    ];
      protected $casts = [
        'entry_date' => 'date',
        'quantity_in' => 'decimal:2',
        'quantity_out' => 'decimal:2',
        'balance_qty' => 'decimal:2',
        'basic_rate' => 'decimal:2',
        'stock_value' => 'decimal:2',
        'balance_value' => 'decimal:2',
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
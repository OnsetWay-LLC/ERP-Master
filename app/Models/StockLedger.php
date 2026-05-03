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
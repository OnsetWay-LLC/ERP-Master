<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockEntry extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'series',
        'entry_type',
        'posting_date',
        'posting_time',
        'total_incoming_value',
        'total_outgoing_value',
        'value_difference',
        'status',
        'created_by',
    ];

    protected $casts = [
        'posting_date' => 'date',
        'total_incoming_value' => 'decimal:2',
        'total_outgoing_value' => 'decimal:2',
        'value_difference' => 'decimal:2',
    ];

    public function items()
    {
        return $this->hasMany(StockEntryItem::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
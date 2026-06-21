<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseReturnFee extends Model
{
    protected $fillable = [
        'purchase_return_id',
        'fees_template_id',
        'type',
        'account_head_id',
        'fees_rate',
        'amount',
        'total',
    ];

    protected $casts = [
        'fees_rate' => 'decimal:2',
        'amount' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function purchaseReturn(): BelongsTo
    {
        return $this->belongsTo(PurchaseReturn::class);
    }

    public function feesTemplate(): BelongsTo
    {
        return $this->belongsTo(FeesTemplate::class);
    }

    public function accountHead(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_head_id');
    }
}
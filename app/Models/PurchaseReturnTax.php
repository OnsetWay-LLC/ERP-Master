<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseReturnTax extends Model
{
    protected $fillable = [
        'purchase_return_id',
        'tax_template_line_id',
        'type',
        'account_head_id',
        'tax_rate',
        'amount',
        'total',
    ];

    protected $casts = [
        'tax_rate' => 'decimal:2',
        'amount' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function purchaseReturn(): BelongsTo
    {
        return $this->belongsTo(PurchaseReturn::class);
    }

    public function taxTemplateLine(): BelongsTo
    {
        return $this->belongsTo(TaxTemplateLine::class);
    }

    public function accountHead(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_head_id');
    }
}
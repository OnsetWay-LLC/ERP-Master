<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentEntryReference extends Model
{
    protected $fillable = [
        'payment_entry_id',
        'purchase_invoice_id',
        'invoice_amount',
        'outstanding_before_payment',
        'allocated_amount',
        'outstanding_after_payment',
    ];

    protected $casts = [
        'invoice_amount' => 'decimal:2',
        'outstanding_before_payment' => 'decimal:2',
        'allocated_amount' => 'decimal:2',
        'outstanding_after_payment' => 'decimal:2',
    ];

    public function paymentEntry(): BelongsTo
    {
        return $this->belongsTo(PaymentEntry::class);
    }

    public function purchaseInvoice(): BelongsTo
    {
        return $this->belongsTo(PurchaseInvoice::class);
    }
}
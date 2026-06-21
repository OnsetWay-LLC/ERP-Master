<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseReturn extends Model
{
    protected $fillable = [
        'series',
        'purchase_invoice_id',
        'supplier_id',
        'posting_date',
        'posting_time',
        'payment_due_date',
        'rejected_warehouse_id',
        'target_warehouse_id',
        'use_default_account',
        'purchase_account_id',
        'supplier_account_id',
        'tax_template_id',
        'fees_template_id',
        'apply_additional_discount_on',
        'additional_discount_percentage',
        'additional_discount_amount',
        'total_quantity',
        'total_amount',
        'net_total',
        'tax_total',
        'fees_total',
        'grand_total',
        'outstanding_amount',
        'status',
        'journal_entry_id',
        'created_by',
        'submitted_at',
        'cancelled_at',
    ];

    protected $casts = [
        'posting_date' => 'date',
        'payment_due_date' => 'date',
        'use_default_account' => 'boolean',
        'additional_discount_percentage' => 'decimal:2',
        'additional_discount_amount' => 'decimal:2',
        'total_quantity' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'net_total' => 'decimal:2',
        'tax_total' => 'decimal:2',
        'fees_total' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'outstanding_amount' => 'decimal:2',
        'submitted_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function purchaseInvoice(): BelongsTo
    {
        return $this->belongsTo(PurchaseInvoice::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function rejectedWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'rejected_warehouse_id');
    }

    public function targetWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'target_warehouse_id');
    }

    public function purchaseAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'purchase_account_id');
    }

    public function supplierAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'supplier_account_id');
    }

    public function taxTemplate(): BelongsTo
    {
        return $this->belongsTo(TaxTemplate::class);
    }

    public function feesTemplate(): BelongsTo
    {
        return $this->belongsTo(FeesTemplate::class);
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseReturnItem::class);
    }

    public function taxes(): HasMany
    {
        return $this->hasMany(PurchaseReturnTax::class);
    }

    public function fees(): HasMany
    {
        return $this->hasMany(PurchaseReturnFee::class);
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isSubmitted(): bool
    {
        return $this->status === 'submitted';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }
}
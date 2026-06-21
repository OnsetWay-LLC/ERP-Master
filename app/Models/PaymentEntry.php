<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentEntry extends Model
{
    protected $fillable = [
        'series',
        'posting_date',
        'supplier_id',
        'payment_mode',
        'paid_from_account_id',
        'payable_account_id',
        'paid_amount',
        'reference_no',
        'reference_date',
        'remarks',
        'status',
        'journal_entry_id',
        'created_by',
        'submitted_at',
        'cancelled_at',
    ];

    protected $casts = [
        'posting_date' => 'date',
        'reference_date' => 'date',
        'paid_amount' => 'decimal:2',
        'submitted_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function paidFromAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'paid_from_account_id');
    }

    public function payableAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'payable_account_id');
    }

    public function references(): HasMany
    {
        return $this->hasMany(PaymentEntryReference::class);
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
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
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Asset;

class JournalEntry extends Model
{
   protected $fillable = [
        'company_id', 'entry_number', 'entry_date', 'total_debit', 'total_credit', 
        'posted_at', 'cancelled_at', 'description', 'reversed_entry_id', 'asset_id',
'source_type','status', 'created_by'
    ];
         protected $casts = [
        'entry_date' => 'date',
        'posted_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'reversed_entry_id' => 'integer',
        'asset_id' => 'integer',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function lines()
    {
        return $this->hasMany(JournalEntryLine::class);
    }
     public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
     public function ledgerEntries(): HasMany
    {
        return $this->hasMany(GeneralLedger::class);
    }
    public function reversedEntry()
{
    return $this->belongsTo(JournalEntry::class, 'reversed_entry_id');
}

public function reversalEntries()
{
    return $this->hasMany(JournalEntry::class, 'reversed_entry_id');
}
public function purchaseReturns()
{
    return $this->hasMany(PurchaseReturn::class);
}
public function asset()
{
    return $this->belongsTo(Asset::class);
}
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JournalEntryLine extends Model
//كل سطر = حساب + مبلغ
//debit أو credit
{
    protected $fillable = [
        'company_id',
        'journal_entry_id',
        'account_id',
        'debit',
        'credit',
        'note',
    ];
   public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }

    public function account()
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_id');
    }
}
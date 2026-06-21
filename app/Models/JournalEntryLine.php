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
    'party_type',
    'party_id',
    'debit',
    'credit',
    'note',
];

   

     public function company()
    {
        return $this->belongsTo(Company::class);
    }
   public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }

    public function account()
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_id');
    }
    public function supplier()
{
    return $this->belongsTo(Supplier::class, 'party_id');
}

}
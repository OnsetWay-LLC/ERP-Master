<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GeneralLedger extends Model
{
    protected $table = 'general_ledger';

    protected $fillable = [
        'company_id',
        'journal_entry_id',
        'journal_entry_line_id',
        'account_id',
        'entry_date',
        'debit',
        'credit',
        'balance',
        'description',
        'created_by',
    ];

    protected $casts = [
        'entry_date' => 'date',
    ];

    public function journalEntry()
    {
        return $this->belongsTo(JournalEntry::class);
    }

    public function line()
    {
        return $this->belongsTo(JournalEntryLine::class, 'journal_entry_line_id');
    }

    public function account()
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_id');
    }
    public function creator()
{
    return $this->belongsTo(User::class, 'created_by');
}
}
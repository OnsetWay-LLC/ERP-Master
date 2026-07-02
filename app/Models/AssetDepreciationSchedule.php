<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetDepreciationSchedule extends Model
{
    protected $fillable = [
        'company_id',
        'asset_id',
        'schedule_no',
        'schedule_date',
        'depreciation_amount',
        'status',
        'journal_entry_id',
    ];

    protected $casts = [
        'schedule_date' => 'date',
        'depreciation_amount' => 'decimal:3',
    ];

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    public function journalEntry()
    {
        return $this->belongsTo(JournalEntry::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
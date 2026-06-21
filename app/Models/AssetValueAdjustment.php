<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AssetValueAdjustment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'series',
        'asset_id',
        'asset_category_id',
        'posting_date',
        'finance_book',
        'current_asset_value',
        'new_asset_value',
        'difference_amount',
        'difference_account_id',
        'journal_entry_id',
        'status',
        'created_by',
        'submitted_at',
        'submitted_by',
    ];

    protected $casts = [
        'posting_date' => 'date',
        'current_asset_value' => 'decimal:2',
        'new_asset_value' => 'decimal:2',
        'difference_amount' => 'decimal:2',
        'submitted_at' => 'datetime',
    ];

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    public function assetCategory()
    {
        return $this->belongsTo(AssetCategory::class);
    }

    public function differenceAccount()
    {
        return $this->belongsTo(ChartOfAccount::class, 'difference_account_id');
    }

    public function journalEntry()
    {
        return $this->belongsTo(JournalEntry::class);
    }
}
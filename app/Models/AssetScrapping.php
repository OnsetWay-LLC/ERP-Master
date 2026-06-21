<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AssetScrapping extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'series',
        'asset_id',
        'asset_category_id',
        'scrap_date',
        'asset_cost',
        'accumulated_depreciation_amount',
        'book_value_loss',
        'fixed_asset_account_id',
        'accumulated_depreciation_account_id',
        'loss_on_disposal_account_id',
        'journal_entry_id',
        'status',
        'created_by',
        'submitted_at',
        'submitted_by',
    ];

    protected $casts = [
        'scrap_date' => 'date',
        'asset_cost' => 'decimal:2',
        'accumulated_depreciation_amount' => 'decimal:2',
        'book_value_loss' => 'decimal:2',
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

    public function fixedAssetAccount()
    {
        return $this->belongsTo(ChartOfAccount::class, 'fixed_asset_account_id');
    }

    public function accumulatedDepreciationAccount()
    {
        return $this->belongsTo(ChartOfAccount::class, 'accumulated_depreciation_account_id');
    }

    public function lossOnDisposalAccount()
    {
        return $this->belongsTo(ChartOfAccount::class, 'loss_on_disposal_account_id');
    }

    public function journalEntry()
    {
        return $this->belongsTo(JournalEntry::class);
    }
}
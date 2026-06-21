<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AssetSale extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'series',
        'asset_id',
        'asset_category_id',
        'sell_qty',
        'asset_qty_before_sale',
        'asset_qty_after_sale',
        'posting_date',
        'posting_time',
        'warehouse_id',
        'rate',
        'sale_value',
        'original_asset_cost',
        'accumulated_depreciation',
        'sold_asset_cost',
        'sold_accumulated_depreciation',
        'book_value',
        'profit_amount',
        'loss_amount',
        'payment_mode',
        'receivable_account_id',
        'cash_account_id',
        'bank_account_id',
        'fixed_asset_account_id',
        'accumulated_depreciation_account_id',
        'gain_account_id',
        'loss_account_id',
        'sales_invoice_id',
        'journal_entry_id',
        'status',
        'created_by',
        'submitted_at',
        'submitted_by',
    ];

    protected $casts = [
        'sell_qty' => 'decimal:2',
        'asset_qty_before_sale' => 'decimal:2',
        'asset_qty_after_sale' => 'decimal:2',
        'posting_date' => 'date',
        'rate' => 'decimal:2',
        'sale_value' => 'decimal:2',
        'original_asset_cost' => 'decimal:2',
        'accumulated_depreciation' => 'decimal:2',
        'sold_asset_cost' => 'decimal:2',
        'sold_accumulated_depreciation' => 'decimal:2',
        'book_value' => 'decimal:2',
        'profit_amount' => 'decimal:2',
        'loss_amount' => 'decimal:2',
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

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function journalEntry()
    {
        return $this->belongsTo(JournalEntry::class);
    }

    public function fixedAssetAccount()
    {
        return $this->belongsTo(ChartOfAccount::class, 'fixed_asset_account_id');
    }

    public function accumulatedDepreciationAccount()
    {
        return $this->belongsTo(ChartOfAccount::class, 'accumulated_depreciation_account_id');
    }

    public function gainAccount()
    {
        return $this->belongsTo(ChartOfAccount::class, 'gain_account_id');
    }

    public function lossAccount()
    {
        return $this->belongsTo(ChartOfAccount::class, 'loss_account_id');
    }
}
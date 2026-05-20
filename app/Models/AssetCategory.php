<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class AssetCategory extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'name_ar',
        'name_en',
        'finance_book',
        'depreciation_method',
        'frequency_month',
        'total_depreciation_count',
        'depreciation_posting_day',
        'depreciation_rate',
        'fixed_asset_account_id',
        'accumulated_depreciation_account_id',
        'depreciation_expense_account_id',
        'capital_work_in_progress_account_id',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'frequency_month' => 'integer',
        'total_depreciation_count' => 'integer',
        'depreciation_posting_day' => 'integer',
        'depreciation_rate' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function fixedAssetAccount()
    {
        return $this->belongsTo(ChartOfAccount::class, 'fixed_asset_account_id');
    }

    public function accumulatedDepreciationAccount()
    {
        return $this->belongsTo(ChartOfAccount::class, 'accumulated_depreciation_account_id');
    }

    public function depreciationExpenseAccount()
    {
        return $this->belongsTo(ChartOfAccount::class, 'depreciation_expense_account_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function assets()
{
    return $this->hasMany(Asset::class, 'asset_category_id');
}
public function capitalWorkInProgressAccount()
{
    return $this->belongsTo(ChartOfAccount::class, 'capital_work_in_progress_account_id');
}
}
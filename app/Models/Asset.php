<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Asset extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'series',
        'asset_item_id',
        'asset_category_id',
        'location_id',
        'asset_name_ar',
        'asset_name_en',
        'asset_type',
        'purchase_date',
        'net_purchase_amount',
        'available_for_use_date',
        'asset_quantity',
        'salvage_value',
        'purchase_receipt_id',
        'status',
        'created_by',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'available_for_use_date' => 'date',
        'net_purchase_amount' => 'decimal:2',
        'salvage_value' => 'decimal:2',
        'asset_quantity' => 'integer',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function assetItem()
    {
        return $this->belongsTo(AssetItem::class);
    }

    public function assetCategory()
    {
        return $this->belongsTo(AssetCategory::class);
    }

    public function location()
    {
        return $this->belongsTo(AssetLocation::class, 'location_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
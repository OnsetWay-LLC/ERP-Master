<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetCapitalizationItem extends Model
{
    protected $fillable = [
        'asset_capitalization_id',
        'asset_id',
        'asset_name_ar',
        'asset_name_en',
        'item_code',
        'current_asset_value',
        'asset_value',
    ];

    protected $casts = [
        'current_asset_value' => 'decimal:2',
        'asset_value' => 'decimal:2',
    ];

    public function capitalization()
    {
        return $this->belongsTo(AssetCapitalization::class, 'asset_capitalization_id');
    }

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }
}
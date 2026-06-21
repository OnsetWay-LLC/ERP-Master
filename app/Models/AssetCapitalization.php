<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AssetCapitalization extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'series',
        'target_asset_id',
        'posting_date',
        'posting_time',
        'consumed_asset_total_value',
        'status',
        'created_by',
        'submitted_at',
        'submitted_by',
    ];

    protected $casts = [
        'posting_date' => 'date',
        'consumed_asset_total_value' => 'decimal:2',
        'submitted_at' => 'datetime',
    ];

    public function targetAsset()
    {
        return $this->belongsTo(Asset::class, 'target_asset_id');
    }

    public function items()
    {
        return $this->hasMany(AssetCapitalizationItem::class);
    }
}
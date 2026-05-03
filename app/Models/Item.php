<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Item extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'item_group_id',
        'item_code',
        'name_ar',
        'name_en',
        'barcode',
        'selling_price',
        'purchase_price',
        'currency_code',
        'status',
        'created_by',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function itemGroup()
    {
        return $this->belongsTo(ItemGroup::class);
    }

    public function warehouse()
    {
        return $this->hasOneThrough(
            Warehouse::class,
            ItemGroup::class,
            'id',
            'id',
            'item_group_id',
            'warehouse_id'
        );
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaterialRequestItem extends Model
{
    protected $fillable = [
        'material_request_id',
        'item_id',
        'barcode',
        'warehouse_id',
        'required_by_date',
        'required_qty',
        'ordered_qty',
        'received_qty',
        'status',
    ];

    protected $casts = [
        'required_by_date' => 'date',
    ];

    public function request()
    {
        return $this->belongsTo(MaterialRequest::class, 'material_request_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }
}
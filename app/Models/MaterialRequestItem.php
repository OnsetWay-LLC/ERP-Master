<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaterialRequestItem extends Model
{
    protected $fillable = [
        'material_request_id',
        'item_id',
        'required_qty',
        'ordered_qty',
        'received_qty',
        'status',
    ];

    public function request()
    {
        return $this->belongsTo(MaterialRequest::class, 'material_request_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
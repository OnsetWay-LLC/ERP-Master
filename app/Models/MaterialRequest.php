<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MaterialRequest extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'request_number',
        'request_date',
        'required_by_date',
        'status',
        'sent_to_purchase_order_at',
        'remarks',
        'created_by',
    ];

    protected $casts = [
        'request_date' => 'date',
        'required_by_date' => 'date',
        'sent_to_purchase_order_at' => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(MaterialRequestItem::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
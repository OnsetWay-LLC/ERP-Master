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
        'warehouse_id',
        'status',
        'remarks',
        'created_by',
    ];

    public function items()
    {
        return $this->hasMany(MaterialRequestItem::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrder extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'supplier_id',
        'material_request_id',
        'tax_template_id',
        'order_number',
        'order_date',
        'required_by',
        'net_total',
        'tax_total',
        'additional_discount_percentage',
        'additional_discount_amount',
        'grand_total',
        'total_amount',
        'status',
        'created_by',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function taxTemplate()
    {
        return $this->belongsTo(TaxTemplate::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function taxes()
    {
        return $this->hasMany(PurchaseOrderTax::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
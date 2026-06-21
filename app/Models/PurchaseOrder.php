<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrder extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id', 'supplier_id', 'series', 'posting_date', 'required_by_date',
        'total_quantity', 'net_total', 'tax_total', 'fees_total',
        'discount_percentage', 'discount_amount', 'grand_total','material_request',
        'status', 'created_by',
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
   public function fees() { return $this->hasMany(PurchaseOrderFee::class); }
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalesOrder extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'customer_id',
        'sales_person_id',
        'order_number',
        'order_date',
        'delivery_date',
        'net_total',
        'tax_total',
        'fees_total',
        'discount_percentage',
        'discount_amount',
        'grand_total',
        'status',
        'created_by',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->hasMany(SalesOrderItem::class);
    }

    public function taxes()
    {
        return $this->hasMany(SalesOrderTax::class);
    }

    public function fees()
    {
        return $this->hasMany(SalesOrderFee::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function pickList()
{
    return $this->hasOne(PickList::class);
}
public function deliveryNotes()
{
    return $this->hasMany(DeliveryNote::class);
}
public function salesPerson()
{
    return $this->belongsTo(SalesPerson::class);
}
}
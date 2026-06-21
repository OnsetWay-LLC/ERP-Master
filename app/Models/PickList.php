<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PickList extends Model
{
    protected $fillable = [
        'company_id',
        'sales_order_id',
        'customer_id',
        'pick_list_number',
        'posting_date',
        'status',
        'created_by',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function salesOrder()
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->hasMany(PickListItem::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function deliveryNote()
{
    return $this->hasOne(DeliveryNote::class);
}
}
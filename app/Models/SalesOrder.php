<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\DiscountApprovalRequest;

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
public function discountApprovalRequests()
{
    return $this->hasMany(DiscountApprovalRequest::class);
}

public function pendingDiscountApproval()
{
    return $this->hasOne(DiscountApprovalRequest::class)
        ->whereIn('status', [
            'pending_department_manager_approval',
            'pending_department_manager_decision',
            'pending_cfo_approval',
        ])
        ->latestOfMany();
}

public function rejectedDiscountApproval()
{
    return $this->hasOne(DiscountApprovalRequest::class)
        ->where('status', 'rejected')
        ->latestOfMany();
}

public function approvedDiscountApproval()
{
    return $this->hasOne(DiscountApprovalRequest::class)
        ->where('status', 'approved')
        ->latestOfMany();
}
}
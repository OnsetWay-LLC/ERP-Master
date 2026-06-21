<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryNote extends Model
{
    protected $fillable = [
        'company_id',
        'sales_order_id',
        'pick_list_id',
        'customer_id',
        'delivery_note_number',
        'posting_date',
        'posting_time',
        'total_qty',
        'net_total',
        'tax_total',
        'fees_total',
        'discount_percentage',
        'discount_amount',
        'grand_total',
        'status',
        'created_by',
    ];

    public function company() { return $this->belongsTo(Company::class); }
    public function salesOrder() { return $this->belongsTo(SalesOrder::class); }
    public function pickList() { return $this->belongsTo(PickList::class); }
    public function customer() { return $this->belongsTo(Customer::class); }
    public function items() { return $this->hasMany(DeliveryNoteItem::class); }
    public function taxes() { return $this->hasMany(DeliveryNoteTax::class); }
    public function fees() { return $this->hasMany(DeliveryNoteFee::class); }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'supplier_type',
        'supplier_name_ar',
        'supplier_name_en',
        'email',
        'mobile_number',
        'address_line_1',
        'address_line_2',
        'zip_code',
        'city',
        'state_province',
        'country',
        'opening_balance',
        'opening_balance_journal_entry_id',
        'created_by',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function paymentEntries()
{
    return $this->hasMany(PaymentEntry::class);
}
public function purchaseReturns()
{
    return $this->hasMany(PurchaseReturn::class);
}
}
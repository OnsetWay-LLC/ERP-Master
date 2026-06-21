<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalesReturn extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'sales_invoice_id',
        'customer_id',
        'return_number',
        'posting_date',
        'posting_time',
        'payment_due_date',
        'return_reason',
        'posting_method',
        'sales_account_id',
        'customer_account_id',
        'net_total',
        'tax_total',
        'fees_total',
        'discount_apply_on',
        'discount_percentage',
        'discount_amount',
        'grand_total',
        'outstanding_amount',
        'journal_entry_id',
        'status',
        'created_by',
    ];

    public function company() { return $this->belongsTo(Company::class); }
    public function salesInvoice() { return $this->belongsTo(SalesInvoice::class); }
    public function customer() { return $this->belongsTo(Customer::class); }
    public function items() { return $this->hasMany(SalesReturnItem::class); }
    public function taxes() { return $this->hasMany(SalesReturnTax::class); }
    public function fees() { return $this->hasMany(SalesReturnFee::class); }
    public function journalEntry() { return $this->belongsTo(JournalEntry::class); }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
}
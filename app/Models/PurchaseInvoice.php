<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class PurchaseInvoice extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id','purchase_receipt_id','purchase_order_id',   'invoice_type','supplier_id',
        'invoice_number','posting_date','posting_time','due_date',
        'supplier_invoice_no','supplier_invoice_date',
        'posting_method','payment_mode',
        'stock_account_id','purchase_account_id','supplier_payable_account_id',
        'cash_account_id','bank_account_id',
        'total_qty','net_total','tax_total','fees_total',
        'discount_percentage','discount_amount','grand_total',
        'paid_amount','outstanding_amount',
        'status','journal_entry_id','created_by',
    ];

    public function supplier() { return $this->belongsTo(Supplier::class); }
    public function purchaseReceipt() { return $this->belongsTo(PurchaseReceipt::class); }
    public function purchaseOrder() { return $this->belongsTo(PurchaseOrder::class); }
    public function items() { return $this->hasMany(PurchaseInvoiceItem::class); }
    public function taxes() { return $this->hasMany(PurchaseInvoiceTax::class); }
    public function fees() { return $this->hasMany(PurchaseInvoiceFee::class); }
    public function journalEntry() { return $this->belongsTo(JournalEntry::class); }
    public function supplierPayableAccount()
{
    return $this->belongsTo(ChartOfAccount::class, 'supplier_payable_account_id');
}

public function stockAccount()
{
    return $this->belongsTo(ChartOfAccount::class, 'stock_account_id');
}
public function paymentReferences()
{
    return $this->hasMany(PaymentEntryReference::class);
}
public function paymentEntries()
{
    return $this->hasManyThrough(
        PaymentEntry::class,
        PaymentEntryReference::class,
        'purchase_invoice_id',
        'id',
        'id',
        'payment_entry_id'
    );
}
public function purchaseReturns()
{
    return $this->hasMany(PurchaseReturn::class);
}
public function purchaseAccount()
{
    return $this->belongsTo(ChartOfAccount::class, 'purchase_account_id');
}
public function company()
{
    return $this->belongsTo(Company::class);
}
}
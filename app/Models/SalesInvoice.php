<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalesInvoice extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'customer_id',
        'sales_person_id',
        'sales_order_id',
        'delivery_note_id',
        'invoice_number',
        'posting_date',
        'posting_time',
        'payment_due_date',
        'posting_method',
        'receivable_account_id',
        'sales_account_id',
        'cogs_account_id',
        'stock_account_id',
        'payment_mode',
        'payment_account_id',
        'paid_amount',
        'outstanding_amount',
        'payment_status',
        'credit_note_amount',
        'net_total',
        'tax_total',
        'fees_total',
        'discount_percentage',
        'discount_amount',
        'grand_total',
        'status',
        'is_asset_sale',
        'created_by',
    ];

    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }
    public function salesPerson(): BelongsTo { return $this->belongsTo(SalesPerson::class); }
    public function salesOrder(): BelongsTo { return $this->belongsTo(SalesOrder::class); }
    public function deliveryNote(): BelongsTo { return $this->belongsTo(DeliveryNote::class); }

    public function items(): HasMany { return $this->hasMany(SalesInvoiceItem::class); }
    public function taxes(): HasMany { return $this->hasMany(SalesInvoiceTax::class); }
    public function fees(): HasMany { return $this->hasMany(SalesInvoiceFee::class); }

    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }

    public function discountApprovalRequests(): HasMany
    {
        return $this->hasMany(DiscountApprovalRequest::class);
    }

    public function pendingDiscountApproval()
    {
        return $this->hasOne(DiscountApprovalRequest::class)
            ->where('status', 'pending')
            ->latestOfMany();
    }
    public function receivableAccount(): BelongsTo
{
    return $this->belongsTo(ChartOfAccount::class, 'receivable_account_id');
}

public function salesAccount(): BelongsTo
{
    return $this->belongsTo(ChartOfAccount::class, 'sales_account_id');
}
}
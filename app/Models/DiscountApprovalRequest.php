<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiscountApprovalRequest extends Model
{
    protected $fillable = [
        'company_id',
        'sales_invoice_id',
        'requested_by',
        'approved_by',
        'requested_discount_percentage',
        'allowed_discount_percentage',
        'status',
        'rejection_reason',
        'responded_at',
    ];

    protected $casts = [
        'requested_discount_percentage' => 'decimal:2',
        'allowed_discount_percentage' => 'decimal:2',
        'responded_at' => 'datetime',
    ];

    public function invoice()
    {
        return $this->belongsTo(SalesInvoice::class, 'sales_invoice_id');
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
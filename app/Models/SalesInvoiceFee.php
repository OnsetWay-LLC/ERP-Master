<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesInvoiceFee extends Model
{
    protected $fillable = [
        'sales_invoice_id',
        'fees_template_id',
        'title',
        'type',
        'account_id',
        'fees_rate',
        'amount',
    ];

    public function salesInvoice()
    {
        return $this->belongsTo(SalesInvoice::class);
    }
}
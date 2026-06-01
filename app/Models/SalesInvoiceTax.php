<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesInvoiceTax extends Model
{
    protected $fillable = [
        'sales_invoice_id',
        'tax_template_id',
        'tax_template_line_id', // هذا هو الحقل الذي سبب الخطأ
        'title',
        'type',
        'account_id',
        'tax_rate',
        'amount',
        'total',
    ];

    public function invoice()
    {
        return $this->belongsTo(SalesInvoice::class, 'sales_invoice_id');
    }

    public function account()
    {
        return $this->belongsTo(ChartOfAccount::class);
    }
}
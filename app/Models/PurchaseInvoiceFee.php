<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class PurchaseInvoiceFee extends Model
{
    protected $fillable = [
        'purchase_invoice_id','fees_template_id','title','type',
        'account_id','fees_rate','amount',
    ];

    public function account() { return $this->belongsTo(ChartOfAccount::class, 'account_id'); }
    public function accountHead()
{
    return $this->belongsTo(ChartOfAccount::class, 'account_id');
}
public function template()
{
    return $this->belongsTo(FeesTemplate::class, 'fees_template_id');
}
}
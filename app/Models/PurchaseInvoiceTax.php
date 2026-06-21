<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class PurchaseInvoiceTax extends Model
{
    protected $fillable = [
        'purchase_invoice_id','tax_template_id','tax_template_line_id',
        'title','type','account_id','tax_rate','amount',
    ];

    public function account() { return $this->belongsTo(ChartOfAccount::class, 'account_id'); }
    public function accountHead()
{
    return $this->belongsTo(ChartOfAccount::class, 'account_id');
}
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesOrderTax extends Model
{
    protected $fillable = [
        'sales_order_id',
        'tax_template_id',
        'tax_template_line_id',
        'title',
        'type',
        'account_id',
        'tax_rate',
        'amount',
    ];

    public function salesOrder()
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function taxTemplate()
    {
        return $this->belongsTo(TaxTemplate::class);
    }

    public function taxTemplateLine()
    {
        return $this->belongsTo(TaxTemplateLine::class);
    }

    public function account()
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_id');
    }
}
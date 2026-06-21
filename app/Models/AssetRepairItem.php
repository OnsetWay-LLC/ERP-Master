<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetRepairItem extends Model
{
    protected $fillable = [
        'asset_repair_id',
        'purchase_invoice_id',
        'expense_account_id',
        'payment_account_id',
        'payment_mode',
        'repair_cost',
    ];

    protected $casts = [
        'repair_cost' => 'decimal:2',
    ];

    public function repair()
    {
        return $this->belongsTo(AssetRepair::class, 'asset_repair_id');
    }

    public function purchaseInvoice()
    {
        return $this->belongsTo(PurchaseInvoice::class);
    }

    public function expenseAccount()
    {
        return $this->belongsTo(ChartOfAccount::class, 'expense_account_id');
    }

    public function paymentAccount()
    {
        return $this->belongsTo(ChartOfAccount::class, 'payment_account_id');
    }
}
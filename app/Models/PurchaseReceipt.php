<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseReceipt extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'purchase_order_id',
        'supplier_id',
        'warehouse_id',
        'receipt_number',
        'receipt_date',
        'status',
        'created_by',
    ];

    public function items()
    {
        return $this->hasMany(PurchaseReceiptItem::class);
    }
}
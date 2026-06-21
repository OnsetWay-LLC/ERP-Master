<?php


namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class PurchaseInvoiceItem extends Model
{
    protected $fillable = [
        'purchase_invoice_id','purchase_receipt_item_id','item_id','warehouse_id',
        'item_code','item_name_ar','item_name_en',
        'quantity','rate','amount',
    ];

    public function invoice() { return $this->belongsTo(PurchaseInvoice::class, 'purchase_invoice_id'); }
    public function item() { return $this->belongsTo(Item::class); }
    public function warehouse() { return $this->belongsTo(Warehouse::class); }
}
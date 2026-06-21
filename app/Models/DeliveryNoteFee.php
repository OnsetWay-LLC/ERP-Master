<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryNoteFee extends Model
{
    protected $fillable = [
        'delivery_note_id',
        'fees_template_id',
        'title',
        'type',
        'account_id',
        'fees_rate',
        'amount',
    ];

    public function deliveryNote() { return $this->belongsTo(DeliveryNote::class); }
    public function account() { return $this->belongsTo(ChartOfAccount::class, 'account_id'); }
}
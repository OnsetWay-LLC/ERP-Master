<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryNoteTax extends Model
{
    protected $fillable = [
        'delivery_note_id',
        'tax_template_id',
        'tax_template_line_id',
        'title',
        'type',
        'account_id',
        'tax_rate',
        'amount',
    ];

    public function deliveryNote() { return $this->belongsTo(DeliveryNote::class); }
    public function account() { return $this->belongsTo(ChartOfAccount::class, 'account_id'); }
}
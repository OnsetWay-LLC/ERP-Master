<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FinancialYear extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'year_name',
        'start_date',
        'end_date',
        'closing_date',
        'status',
        'grace_period_end',
        'closed_at',
        'closed_by',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'closing_date' => 'date',
        'grace_period_end' => 'date',
        'closed_at' => 'datetime',
    ];

    public function closer()
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
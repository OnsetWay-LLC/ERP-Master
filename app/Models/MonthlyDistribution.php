<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MonthlyDistribution extends Model
{
    protected $fillable = [
        'company_id',
        'title',
        'fiscal_year',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function lines()
    {
        return $this->hasMany(MonthlyDistributionLine::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
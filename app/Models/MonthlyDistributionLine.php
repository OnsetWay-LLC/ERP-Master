<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MonthlyDistributionLine extends Model
{
    protected $fillable = [
        'monthly_distribution_id',
        'month',
        'percentage',
    ];

    public function monthlyDistribution()
    {
        return $this->belongsTo(MonthlyDistribution::class);
    }
}
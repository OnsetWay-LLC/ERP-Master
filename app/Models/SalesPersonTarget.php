<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesPersonTarget extends Model
{
    protected $fillable = [
    'sales_person_id',
    'item_group_id',
    'monthly_distribution_id',
    'target_amount',
];

    public function salesPerson()
    {
        return $this->belongsTo(SalesPerson::class);
    }

    public function itemGroup()
    {
        return $this->belongsTo(ItemGroup::class);
    }

    public function monthlyDistribution()
    {
        return $this->belongsTo(MonthlyDistribution::class);
    }
}
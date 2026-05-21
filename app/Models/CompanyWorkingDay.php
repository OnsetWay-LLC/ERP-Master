<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyWorkingDay extends Model
{
    protected $fillable = [
        'company_id',
        'day',
        'is_working_day',
    ];

    protected $casts = [
        'is_working_day' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
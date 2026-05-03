<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaxTemplate extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'title',
        'created_by',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

   public function lines()
    {
        return $this->hasMany(TaxTemplateLine::class);
    }
}
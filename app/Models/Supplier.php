<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'supplier_type',
        'supplier_name_ar',
        'supplier_name_en',
        'first_name',
        'last_name',
        'email',
        'mobile_number',
        'address_line_1',
        'address_line_2',
        'zip_code',
        'city',
        'state_province',
        'country',
        'created_by',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
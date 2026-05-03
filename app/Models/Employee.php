<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'department_id',
        'series',
        'full_name',
        'national_id',
        'gender',
        'date_of_joining',
        'status',
        'mobile_number',
        'company_email',
        'address',
        'salary_mode',
        'salary_value',
        'marital_status',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function shifts()
    {
        return $this->hasMany(EmployeeShift::class);
    }

    public function educations()
    {
        return $this->hasMany(EmployeeEducation::class);
    }
    public function user()
{
    return $this->hasOne(User::class);
}
}
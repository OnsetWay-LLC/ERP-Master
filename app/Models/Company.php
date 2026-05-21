<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name_ar',
        'name_en',
        'country',
        'currency_code',
        'established_at',
        'fax',
        'phone',
        'email',
    ];

    protected $casts = [
        'established_at' => 'date',
    ];

    public function departments()
    {
        return $this->hasMany(Department::class);
    }

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }

    public function workingDays()
    {
        return $this->hasMany(CompanyWorkingDay::class);
    }

    public function shifts()
    {
        return $this->hasMany(Shift::class);
    }

    public function payrolls()
    {
        return $this->hasMany(Payroll::class);
    }

    public function users()
    {
        return $this->hasManyThrough(User::class, Employee::class);
    }
    public function leaveTypes()
{
    return $this->hasMany(LeaveType::class);
}
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class Employee extends Model
{
    use SoftDeletes , Notifiable;

    protected $fillable = [
        'company_id',
        'department_id',
        'series',
        'full_name_en',
        'full_name_ar',
        'national_id',
        'gender',
        'date_of_joining',
        'status',
        'job_title',
        'mobile_number',
        'company_email',
        'address',
        'marital_status',
        'wife_working_status',
    ];

    protected $casts = [
        'date_of_joining' => 'date',
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
        return $this->belongsToMany(Shift::class, 'employee_shifts')
            ->withPivot('is_default')
            ->withTimestamps();
    }

    public function educations()
    {
        return $this->hasMany(EmployeeEducation::class);
    }

    public function salaries()
    {
        return $this->hasMany(EmployeeSalary::class);
    }

    public function activeSalary()
    {
        return $this->hasOne(EmployeeSalary::class)->where('is_active', true);
    }

    public function allowances()
    {
        return $this->hasMany(EmployeeAllowance::class);
    }

    public function leaveBalances()
    {
        return $this->hasMany(EmployeeLeaveBalance::class);
    }

    public function leaves()
    {
        return $this->hasMany(EmployeeLeave::class);
    }

    public function payrollItems()
    {
        return $this->hasMany(PayrollItem::class);
    }

    public function user()
    {
        return $this->hasOne(User::class);
    }
      public function routeNotificationForMail(): ?string
    {
        return $this->company_email;
    }
}
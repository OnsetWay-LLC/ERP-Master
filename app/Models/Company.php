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

    public function departments()
    {
        return $this->hasMany(Department::class);
    }

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }

    public function users()
    {
        return $this->hasManyThrough(User::class, Employee::class);
    } //يسمح لنا بالحصول على جميع المستخدمين المرتبطين بالشركة من خلال الموظفين.
}
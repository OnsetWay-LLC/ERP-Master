<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeEducation extends Model
{
    protected $table = 'employee_educations';

    protected $fillable = [
        'employee_id',
        'school_university_ar',
        'school_university_en',
        'qualification_ar',
        'qualification_en',
        'level',
        'year_of_passing',
        'class_percentage',
        'major_ar',
        'major_en',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
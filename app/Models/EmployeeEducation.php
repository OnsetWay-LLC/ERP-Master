<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;




class EmployeeEducation extends Model
{
    protected $table = 'employee_educations';
    protected $fillable = [
        'employee_id',
        'school_university',
        'qualification',
        'level',
        'year_of_passing',
        'class_percentage',
        'major_optional_subject',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
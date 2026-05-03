<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeShift extends Model
{
    protected $table = 'employee_shifts';

    protected $fillable = [
        'employee_id',
        'shift_type_name',
        'start_time',
        'end_time',
        'is_default',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
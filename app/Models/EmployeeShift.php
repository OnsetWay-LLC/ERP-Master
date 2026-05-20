<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeShift extends Model
{
    protected $table = 'employee_shifts';

    protected $fillable = [
         'employee_id',
        'shift_id',
        'is_default',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
      public function shift()
    {
        return $this->belongsTo(Shift::class);
    }
}
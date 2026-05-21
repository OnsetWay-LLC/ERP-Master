<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveTypeRule extends Model
{
    protected $fillable = [
        'leave_type_id',
        'gender',
        'marital_status',
        'days',
        'is_active',
    ];

    protected $casts = [
        'days' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class);
    }
}
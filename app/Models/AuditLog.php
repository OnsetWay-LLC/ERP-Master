<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $fillable = [
        'company_id',
        'user_id',
        'username',
        'operation',
        'method',
        'url',
        'ip_address',
        'performed_at',
    ];

    protected $casts = [
        'performed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
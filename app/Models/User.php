<?php

namespace App\Models;

use App\Notifications\CustomVerifyEmailNotification;
use App\Notifications\CustomResetPasswordNotification;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements MustVerifyEmail, JWTSubject
{
    use Notifiable, HasRoles;

    protected string $guard_name = 'api';

    protected $fillable = [
        'employee_id',
        'name',
        'username',
        'email',
        'password',
        'is_active',
        'failed_attempts',
        'locked_until',
        'is_initial_admin',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'locked_until' => 'datetime',
            'is_active' => 'boolean',
            'is_initial_admin' => 'boolean',
        ];
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new CustomVerifyEmailNotification());
    }
    public function sendPasswordResetNotification($token): void
{
    $this->notify(new CustomResetPasswordNotification($token));
}

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
{
    $primaryRole = $this->roles()->first();

    return [
        'user_id' => $this->id,
        'role_id' => $primaryRole?->id,
        'role_name' => $primaryRole?->name,
        'date' => now()->toDateString(),
    ];
}

}
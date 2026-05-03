<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Password;

class ForgotPasswordService
{
    public function sendResetLink(string $email): void
    {
        $user = User::where('email', $email)->firstOrFail();

        Password::sendResetLink([
            'email' => $user->email,
        ]);
    }
}
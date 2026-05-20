<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class LoginService
{
    public function login(array $data): array
    {
        $login = $data['login'];

        $user = User::where('username', $login)
            ->orWhere('email', $login)
            ->first();

        if (! $user) {
            abort(401, 'Invalid email/username or password.');
        }

        if (! $user->is_active) {
            abort(403, 'Your account is inactive.');
        }

        if ($user->locked_until && now()->lessThan($user->locked_until)) {
            abort(403, 'Your account is locked. Try again after 5 minutes.');
        }

        if (! Hash::check($data['password'], $user->password)) {
            $this->handleFailedLogin($user);

            abort(401, 'Invalid email/username or password.');
        }

        $this->resetLoginAttempts($user);

        $token = JWTAuth::fromUser($user);

        return [
            'user' => $user->load('employee'),
            'token' => $token,
        ];
    }

    protected function handleFailedLogin(User $user): void
    {
        $user->increment('failed_attempts');

        if ($user->failed_attempts >= 3) {
            $user->update([
                'locked_until' => now()->addMinutes(5),
            ]);
        }
    }

    protected function resetLoginAttempts(User $user): void
    {
        $user->update([
            'failed_attempts' => 0,
            'locked_until' => null,
        ]);
    }
}
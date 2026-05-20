<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ResetPasswordService
{
    public function reset(array $data): void
    {
        $record = DB::table('password_reset_tokens')
            ->where('email', $data['email'])
            ->first();

        if (! $record) {
            throw new HttpException(422, 'Invalid or expired reset code.');
        }

        if (! Hash::check($data['token'], $record->token)) {
            throw new HttpException(422, 'Invalid reset code.');
        }

        if (now()->diffInMinutes($record->created_at) > 10) {
            DB::table('password_reset_tokens')
                ->where('email', $data['email'])
                ->delete();

            throw new HttpException(422, 'Reset code has expired.');
        }

        $user = User::where('email', $data['email'])->first();

        if (! $user) {
            throw new HttpException(404, 'User not found.');
        }

        $user->forceFill([
            'password' => Hash::make($data['password']),
            'remember_token' => Str::random(60),
            'failed_attempts' => 0,
            'locked_until' => null,
        ])->save();

        DB::table('password_reset_tokens')
            ->where('email', $data['email'])
            ->delete();

        event(new PasswordReset($user));
    }
}
<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ForgotPasswordService
{
    public function sendCode(array $data): void
    {
        $user = User::where('email', $data['email'])->first();

        if (! $user) {
            throw new HttpException(404, __('auth.user_not_found'));
        }

        $code = (string) random_int(100000, 999999);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            [
                'token' => Hash::make($code),
                'created_at' => now(),
            ]
        );

        Mail::raw("Your reset password code is: {$code}", function ($message) use ($user) {
            $message->to($user->email)
                ->subject('Reset Password Code');
        });
    }
}
<?php

namespace App\Http\Controllers\Web\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PasswordPageController extends Controller
{
    public function showForgotPasswordPage()
    {
        return view('auth.forgot-password');
    }

    public function showResetPasswordPage(string $token, Request $request)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }
}
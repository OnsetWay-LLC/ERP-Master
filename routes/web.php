<?php

use App\Http\Controllers\Web\Auth\PasswordPageController;
use Illuminate\Support\Facades\Route;

Route::get('/forgot-password', [PasswordPageController::class, 'showForgotPasswordPage'])
    ->name('password.request');

Route::get('/reset-password/{token}', [PasswordPageController::class, 'showResetPasswordPage'])
    ->name('password.reset');
<?php

use App\Http\Controllers\Web\Auth\PasswordPageController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\AuthController;

Route::get('/auth/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
    ->middleware(['signed'])
    ->name('verification.verify');
Route::get('/forgot-password', [PasswordPageController::class, 'showForgotPasswordPage'])
    ->name('password.request');

Route::get('/reset-password/{token}', [PasswordPageController::class, 'showResetPasswordPage'])
    ->name('password.reset');

Route::get('/auth/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
    ->middleware(['signed'])
    ->name('verification.verify');
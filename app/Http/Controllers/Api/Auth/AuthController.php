<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\Auth\RegisterResource;
use App\Models\User;
use App\Services\Auth\RegisterService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\Auth\LoginResource;
use App\Services\Auth\LoginService;

class AuthController extends Controller
{
    public function __construct(
        protected RegisterService $registerService,
        protected LoginService $loginService
    ) {}
   

    public function register(RegisterRequest $request): JsonResponse
    {
        $user = $this->registerService->register($request->validated());

        return response()->json([
            'message' => 'Registration successful. Please verify your email.',
            'data' => new RegisterResource($user),
        ], 201);
    }

   public function login(LoginRequest $request): JsonResponse
{
    $result = $this->loginService->login($request->validated());

    return response()->json([
        'data' => new LoginResource($result['user']),
        'token' => $result['token'],
    ]);
}

    public function verifyEmail(Request $request, int $id, string $hash)
{
    $user = User::findOrFail($id);

    if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
        return view('emails.verify-email-result', [
            'message' => 'Invalid verification link.',
        ]);
    }

    if ($user->hasVerifiedEmail()) {
        return view('emails.verify-email-result', [
            'message' => 'Email already verified.',
        ]);
    }

    $user->markEmailAsVerified();

    return view('emails.verify-email-result', [
        'message' => 'Email verified successfully.',
    ]);
}
    public function resendVerificationEmail(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
        ]);

        $user = User::where('email', $request->email)->firstOrFail();

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Email is already verified.',
            ], 422);
        }

        $user->sendEmailVerificationNotification();

        return response()->json([
            'message' => 'Verification email resent successfully.',
        ]);
    }
}
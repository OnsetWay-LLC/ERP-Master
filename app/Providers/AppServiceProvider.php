<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Response;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        RateLimiter::for('login', function (Request $request) {
            $username = strtolower(trim($request->input('username', '')));
            
            // لو مافي username، نستخدم الـ IP
            $key = $username ? 'login:'.$username : 'login:ip:'.$request->ip();
            
            // 3 محاولات في 5 دقائق
            return Limit::perMinutes(5, 3)
                ->by($key)
                // 👇 الانتباه هنا: الترتيب هو ($request, $headers)
                ->response(function (Request $request, array $headers) {
                    return Response::json([
                        'message' => 'Too many login attempts. Please try again in 5 minutes.',
                    ], 429, $headers); // نمرر الـ $headers عشان تطلع قيمة Retry-After الصحيحة
                });
        });
    }
}
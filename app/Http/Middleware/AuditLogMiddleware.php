<?php

namespace App\Http\Middleware;

use App\Models\AuditLog;
use App\Models\Company;
use Closure;
use Illuminate\Http\Request;

class AuditLogMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        $user = auth('api')->user();

        if ($user && in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            $company = Company::query()->first();

            AuditLog::create([
                'company_id' => $company?->id,
                'user_id' => $user->id,
                'username' => $user->username ?? $user->email,
                'operation' => $this->getOperationName($request),
                'method' => $request->method(),
                'url' => $request->path(),
                'ip_address' => $request->ip(),
                'performed_at' => now(),
            ]);
        }

        return $response;
    }

  private function getOperationName(Request $request): string
{
    $method = strtoupper($request->method());

    $path = $request->path();

    // نشيل api/ من البداية لو موجودة
    $path = preg_replace('/^api\//', '', $path);

    $segments = explode('/', $path);

    // اسم الشاشة / الموديول
    $resource = $segments[0] ?? 'System';

    // آخر جزء من الرابط مثل submit / cancel / restore
    $lastSegment = end($segments);

    $resourceName = $this->formatResourceName($resource);

    // حالات خاصة حسب آخر جزء بالرابط
    if ($method === 'POST' && in_array($lastSegment, ['submit', 'cancel', 'restore', 'close', 'reopen'])) {
        return ucfirst($lastSegment) . ' ' . $resourceName;
    }

    // حالات خاصة
    if ($method === 'POST' && str_contains($path, 'generate')) {
        return 'Generate ' . $resourceName;
    }

    if ($method === 'POST' && str_contains($path, 'import')) {
        return 'Import ' . $resourceName;
    }

    // حسب نوع request
    return match ($method) {
        'POST' => 'Create ' . $resourceName,
        'PUT', 'PATCH' => 'Update ' . $resourceName,
        'DELETE' => 'Delete ' . $resourceName,
        default => $method . ' ' . $resourceName,
    };
}

private function formatResourceName(string $resource): string
{
    return collect(explode('-', $resource))
        ->map(fn ($word) => ucfirst($word))
        ->implode(' ');
}
}
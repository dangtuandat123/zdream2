<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware kiểm tra user có phải admin không
 */
class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user() || !$request->user()->is_admin) {
            Log::warning('Unauthorized admin access attempt', [
                'user_id' => $request->user()?->id,
                'ip' => $request->ip(),
                'path' => $request->path(),
            ]);
            abort(403, 'Bạn không có quyền truy cập trang này.');
        }

        // Log admin access for audit
        Log::info('Admin access', [
            'admin_id' => $request->user()->id,
            'path' => $request->path(),
            'method' => $request->method(),
        ]);

        return $next($request);
    }
}

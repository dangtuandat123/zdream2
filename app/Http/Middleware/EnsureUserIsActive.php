<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware: EnsureUserIsActive
 * 
 * Kiểm tra user có bị ban không mỗi request.
 * Nếu is_active = false, logout và redirect về login với thông báo.
 * 
 * Fix cho CRITICAL-01: User bị ban vẫn truy cập được sau khi đã login.
 */
class EnsureUserIsActive
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        
        // Chỉ kiểm tra nếu đã đăng nhập
        if ($user && !$user->is_active) {
            // [FIX loi.md M7] Return JSON for API requests
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'error' => 'Tài khoản của bạn đã bị vô hiệu hóa.',
                ], 403);
            }
            
            // Logout user bị ban
            Auth::logout();
            
            // Invalidate session
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            
            // Redirect về login với thông báo
            return redirect()->route('login')->with('error', 'Tài khoản của bạn đã bị vô hiệu hóa. Vui lòng liên hệ hỗ trợ.');
        }
        
        return $next($request);
    }
}

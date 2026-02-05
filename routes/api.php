<?php

use App\Http\Controllers\Api\InternalApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - EZShot AI
|--------------------------------------------------------------------------
*/

// =============================================
// USER API (Sanctum Authentication)
// [FIX loi.md #5] Added 'active' middleware to check is_active
// =============================================

Route::middleware(['auth:sanctum', 'active'])->group(function () {

    // Get current user info
    Route::get('/user', function (Request $request) {
        return $request->user()->only(['id', 'name', 'email', 'credits']);
    });

    // Get recent images for image picker
    Route::get('/user/recent-images', function (Request $request) {
        $images = $request->user()->generatedImages()
            ->latest()
            ->take(8)
            ->get(['id', 'image_url'])
            ->map(fn($img) => ['id' => $img->id, 'url' => $img->image_url]);

        return response()->json(['images' => $images]);
    });
});

// =============================================
// INTERNAL API (API Key Authentication)
// API-01 FIX: Rate limit 60 requests/phút để ngăn spam
// =============================================

Route::prefix('internal')->middleware('throttle:60,1')->group(function () {

    // Điều chỉnh credits
    Route::post('/wallet/adjust', [InternalApiController::class, 'adjustWallet']);

    // Payment callback (VietQR)
    Route::post('/payment/callback', [InternalApiController::class, 'paymentCallback']);
});

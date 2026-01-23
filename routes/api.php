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
// =============================================

Route::middleware('auth:sanctum')->group(function () {
    
    // Get current user info
    Route::get('/user', function (Request $request) {
        return $request->user()->only(['id', 'name', 'email', 'credits']);
    });
});

// =============================================
// INTERNAL API (API Key Authentication)
// =============================================

Route::prefix('internal')->group(function () {
    
    // Điều chỉnh credits
    Route::post('/wallet/adjust', [InternalApiController::class, 'adjustWallet']);
    
    // Payment callback (VietQR)
    Route::post('/payment/callback', [InternalApiController::class, 'paymentCallback']);
});

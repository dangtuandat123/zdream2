<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StudioController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\Admin\StyleController as AdminStyleController;
use App\Http\Controllers\Admin\StyleOptionController as AdminStyleOptionController;
use App\Http\Controllers\Admin\SettingsController as AdminSettingsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - EZShot AI
|--------------------------------------------------------------------------
*/

// =============================================
// PUBLIC ROUTES
// =============================================

// Home - Gallery Styles
Route::get('/', [HomeController::class, 'index'])->name('home');

// DEBUG: Test OpenRouter models API
Route::get('/debug/models', function () {
    $apiKey = \App\Models\Setting::get('openrouter_api_key');
    $baseUrl = \App\Models\Setting::get('openrouter_base_url', 'https://openrouter.ai/api/v1');
    
    // Direct API call
    $response = \Illuminate\Support\Facades\Http::withHeaders([
        'Authorization' => 'Bearer ' . $apiKey,
    ])->timeout(30)->get($baseUrl . '/models');
    
    $data = $response->json();
    $allModels = $data['data'] ?? [];
    
    // Filter image models
    $imageModels = [];
    foreach ($allModels as $model) {
        $outputModalities = $model['output_modalities'] ?? [];
        if (in_array('image', $outputModalities)) {
            $imageModels[] = [
                'id' => $model['id'],
                'name' => $model['name'] ?? $model['id'],
                'modalities' => $outputModalities,
            ];
        }
    }
    
    // Also show first 5 models with their modalities for debug
    $sample = array_slice(array_map(fn($m) => [
        'id' => $m['id'],
        'modalities' => $m['output_modalities'] ?? [],
    ], $allModels), 0, 10);
    
    return response()->json([
        'api_key_exists' => !empty($apiKey),
        'api_key_prefix' => substr($apiKey ?? '', 0, 20) . '...',
        'total_models' => count($allModels),
        'image_models_count' => count($imageModels),
        'image_models' => $imageModels,
        'sample_modalities' => $sample,
    ]);
});

// =============================================
// AUTHENTICATED USER ROUTES
// =============================================

Route::middleware(['auth', 'verified'])->group(function () {
    
    // Dashboard (từ Breeze)
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Studio - Tạo ảnh
    Route::get('/studio/{style:slug}', [StudioController::class, 'show'])->name('studio.show');

    // Wallet - Ví tiền
    Route::get('/wallet', [WalletController::class, 'index'])->name('wallet.index');

    // Profile (từ Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// =============================================
// ADMIN ROUTES
// =============================================

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    
    // Dashboard Admin
    Route::get('/', function () {
        return view('admin.dashboard');
    })->name('dashboard');

    // CRUD Styles
    Route::resource('styles', AdminStyleController::class);

    // CRUD Style Options (nested)
    Route::resource('styles.options', AdminStyleOptionController::class)
        ->except(['show'])
        ->parameters(['options' => 'option']);

    // Settings
    Route::get('settings', [AdminSettingsController::class, 'index'])->name('settings.index');
    Route::put('settings', [AdminSettingsController::class, 'update'])->name('settings.update');
});

// =============================================
// AUTH ROUTES (từ Breeze)
// =============================================

require __DIR__.'/auth.php';

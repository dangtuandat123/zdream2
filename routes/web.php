<?php

use App\Http\Controllers\HistoryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StudioController;
use App\Http\Controllers\StylesController;
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

// Styles Gallery (with search, filter, pagination)
Route::get('/styles', [StylesController::class, 'index'])->name('styles.index');

// DEBUG: Model data inspection (LOCAL ONLY)
if (app()->environment('local')) {
    Route::get('/debug/models', function () {
        $modelManager = app(\App\Services\ModelManager::class);
        $models = $modelManager->fetchModels(true);
        $grouped = $modelManager->groupByProvider($models);

        return response()->json([
            'total_models' => count($models),
            'providers' => array_keys($grouped),
            'provider_counts' => array_map('count', $grouped),
            'sample_models' => array_slice($models, 0, 3),
        ], 200, [], JSON_PRETTY_PRINT);
    });
}


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

    // Create - Text-to-Image (simple, clean interface)
    Route::get('/create', function () {
        return view('create', [
            'initialPrompt' => request('prompt', ''),
        ]);
    })->name('create');

    // Direct Image Edit Studio
    // Route::get('/edit', function () {
    //     return view('edit.index');
    // })->name('edit.index');

    // Wallet - Ví tiền
    Route::get('/wallet', [WalletController::class, 'index'])->name('wallet.index');

    // History - Lịch sử ảnh đã tạo
    Route::get('/history', [HistoryController::class, 'index'])->name('history.index');
    Route::get('/history/{image}/download', [HistoryController::class, 'download'])->name('history.download');
    Route::delete('/history/{image}', [HistoryController::class, 'destroy'])->name('history.destroy');

    // Profile (từ Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // API-like routes for AJAX calls
    Route::get('/api/user/recent-images', function () {
        $images = auth()->user()->generatedImages()
            ->latest()
            ->take(8)
            ->get(['id', 'storage_path'])
            ->map(fn($img) => ['id' => $img->id, 'url' => $img->image_url]);

        return response()->json(['images' => $images]);
    })->name('api.recent-images');
});

// =============================================
// ADMIN ROUTES
// =============================================

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {

    // Dashboard Admin
    Route::get('/', function () {
        return view('admin.dashboard');
    })->name('dashboard');

    // Styles import
    Route::get('styles/import', [AdminStyleController::class, 'importForm'])->name('styles.import');
    Route::post('styles/import', [AdminStyleController::class, 'importStore'])->name('styles.import.store');

    // CRUD Styles (loại show vì không cần)
    Route::resource('styles', AdminStyleController::class)->except(['show']);

    // CRUD Tags
    Route::resource('tags', App\Http\Controllers\Admin\TagController::class)->except(['show']);

    // CRUD Style Options (nested)
    Route::resource('styles.options', AdminStyleOptionController::class)
        ->except(['show'])
        ->parameters(['options' => 'option']);

    // User Management
    Route::get('users', [App\Http\Controllers\Admin\UserController::class, 'index'])->name('users.index');
    Route::get('users/{user}', [App\Http\Controllers\Admin\UserController::class, 'show'])->name('users.show');
    Route::get('users/{user}/edit', [App\Http\Controllers\Admin\UserController::class, 'edit'])->name('users.edit');
    Route::put('users/{user}', [App\Http\Controllers\Admin\UserController::class, 'update'])->name('users.update');
    Route::post('users/{user}/toggle-status', [App\Http\Controllers\Admin\UserController::class, 'toggleStatus'])->name('users.toggle-status');
    Route::post('users/{user}/adjust-credits', [App\Http\Controllers\Admin\UserController::class, 'adjustCredits'])->name('users.adjust-credits');

    // Transaction History (All)
    Route::get('transactions', [App\Http\Controllers\Admin\TransactionController::class, 'index'])->name('transactions.index');

    // Generated Images (All)  
    Route::get('images', [App\Http\Controllers\Admin\GeneratedImageController::class, 'index'])->name('images.index');
    Route::get('images/{image}', [App\Http\Controllers\Admin\GeneratedImageController::class, 'show'])->name('images.show');
    Route::delete('images/{image}', [App\Http\Controllers\Admin\GeneratedImageController::class, 'destroy'])->name('images.destroy');

    // Settings
    Route::get('settings', [AdminSettingsController::class, 'index'])->name('settings.index');
    Route::put('settings', [AdminSettingsController::class, 'update'])->name('settings.update');
    Route::post('settings/refresh-models', [AdminSettingsController::class, 'refreshModels'])->name('settings.refresh-models');

    // Edit Studio Settings
    Route::get('edit-studio', [App\Http\Controllers\Admin\EditStudioSettingsController::class, 'index'])->name('edit-studio.index');
    Route::put('edit-studio', [App\Http\Controllers\Admin\EditStudioSettingsController::class, 'update'])->name('edit-studio.update');
});

// =============================================
// AUTH ROUTES (từ Breeze)
// =============================================

require __DIR__ . '/auth.php';

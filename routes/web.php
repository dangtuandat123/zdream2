<?php

use App\Http\Controllers\HistoryController;
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

    // History - Lịch sử ảnh đã tạo
    Route::get('/history', [HistoryController::class, 'index'])->name('history.index');
    Route::delete('/history/{image}', [HistoryController::class, 'destroy'])->name('history.destroy');

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

    // CRUD Styles (loại show vì không cần)
    Route::resource('styles', AdminStyleController::class)->except(['show']);

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
});

// =============================================
// AUTH ROUTES (từ Breeze)
// =============================================

require __DIR__.'/auth.php';

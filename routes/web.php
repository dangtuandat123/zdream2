<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StudioController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\Admin\StyleController as AdminStyleController;
use App\Http\Controllers\Admin\StyleOptionController as AdminStyleOptionController;
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
});

// =============================================
// AUTH ROUTES (từ Breeze)
// =============================================

require __DIR__.'/auth.php';

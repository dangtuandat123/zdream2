<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Thêm trường credits vào bảng users
 * 
 * Mở rộng bảng users có sẵn của Laravel để thêm hệ thống ví nội bộ.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // ========================================
            // HỆ THỐNG VÍ (WALLET)
            // ========================================
            $table->decimal('credits', 12, 2)
                  ->default(10.00)                                // Credits miễn phí khi đăng ký
                  ->after('remember_token');
            
            // ========================================
            // TRẠNG THÁI TÀI KHOẢN
            // ========================================
            $table->boolean('is_admin')->default(false)           // Là admin không
                  ->after('credits');
            $table->boolean('is_active')->default(true)           // Tài khoản có hoạt động không
                  ->after('is_admin');                    
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['credits', 'is_admin', 'is_active']);
        });
    }
};

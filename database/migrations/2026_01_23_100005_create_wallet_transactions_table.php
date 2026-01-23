<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Tạo bảng wallet_transactions
 * 
 * Lưu lịch sử giao dịch cộng/trừ credits.
 * Quan trọng cho việc debug và đối soát.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            
            // ========================================
            // LIÊN KẾT
            // ========================================
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();
            
            // ========================================
            // THÔNG TIN GIAO DỊCH
            // ========================================
            $table->enum('type', ['credit', 'debit']);            // credit = cộng, debit = trừ
            $table->decimal('amount', 12, 2);                     // Số tiền giao dịch (luôn dương)
            $table->decimal('balance_before', 12, 2);             // Số dư trước giao dịch
            $table->decimal('balance_after', 12, 2);              // Số dư sau giao dịch
            
            // ========================================
            // LÝ DO & NGUỒN
            // ========================================
            $table->string('reason', 255);                        // VD: "Tạo ảnh Style #5", "Nạp tiền VietQR"
            $table->string('source', 100)->nullable();            // VD: "generation", "vietqr", "admin_adjust"
            $table->string('reference_id', 255)->nullable();      // ID liên quan (generated_image_id, payment_id)
            
            $table->timestamps();
            
            // ========================================
            // INDEXES
            // ========================================
            $table->index('user_id');
            $table->index('type');
            $table->index('created_at');
            $table->index('source');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
    }
};

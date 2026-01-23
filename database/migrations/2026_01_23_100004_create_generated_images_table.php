<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Tạo bảng generated_images
 * 
 * Lưu lịch sử các ảnh đã tạo bởi user.
 * Bao gồm thông tin debug (final_prompt) và trạng thái xử lý.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('generated_images', function (Blueprint $table) {
            $table->id();
            
            // ========================================
            // LIÊN KẾT
            // ========================================
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();
            
            $table->foreignId('style_id')
                  ->nullable()
                  ->constrained('styles')
                  ->nullOnDelete();                               // Xóa Style -> Set null (không mất history)
            
            // ========================================
            // THÔNG TIN PROMPT
            // ========================================
            $table->text('final_prompt');                         // Prompt thực tế đã gửi đến OpenRouter
            $table->json('selected_options')->nullable();         // IDs của style_options đã chọn
            $table->text('user_custom_input')->nullable();        // Nội dung user tự gõ thêm
            
            // ========================================
            // KẾT QUẢ
            // ========================================
            $table->string('storage_path', 500)->nullable();      // Đường dẫn file trên MinIO
            $table->string('openrouter_id', 255)->nullable();     // ID response từ OpenRouter
            
            // ========================================
            // TRẠNG THÁI
            // ========================================
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])
                  ->default('pending');
            $table->text('error_message')->nullable();            // Lỗi nếu failed
            
            // ========================================
            // CHI PHÍ
            // ========================================
            $table->decimal('credits_used', 10, 2)->default(0);   // Số credits đã trừ
            
            $table->timestamps();
            
            // ========================================
            // INDEXES
            // ========================================
            $table->index('user_id');
            $table->index('style_id');
            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('generated_images');
    }
};

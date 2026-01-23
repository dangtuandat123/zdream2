<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Tạo bảng style_options
 * 
 * Lưu các tùy chọn bổ sung cho từng Style.
 * Cho phép admin tạo các dropdown/checkbox để user lựa chọn.
 * 
 * Ví dụ:
 * - Nhóm "Skin": Option "Mịn màng" -> prompt_fragment: ", soft skin texture"
 * - Nhóm "Lighting": Option "Neon" -> prompt_fragment: ", neon cyberpunk lighting"
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('style_options', function (Blueprint $table) {
            $table->id();
            
            // ========================================
            // LIÊN KẾT VỚI STYLE
            // ========================================
            $table->foreignId('style_id')
                  ->constrained('styles')
                  ->cascadeOnDelete();                            // Xóa Style -> Xóa luôn Options
            
            // ========================================
            // THÔNG TIN OPTION
            // ========================================
            $table->string('label', 255);                         // Tên hiển thị (VD: "Làm mịn da")
            $table->string('group_name', 100);                    // Nhóm (VD: "Skin", "Lighting", "Background")
            $table->string('prompt_fragment', 500);               // Đoạn prompt sẽ nối vào base_prompt
            
            // ========================================
            // TÙY CHỌN BỔ SUNG
            // ========================================
            $table->string('icon', 100)->nullable();              // Icon class (optional, cho UI)
            $table->unsignedInteger('sort_order')->default(0);    // Thứ tự trong group
            $table->boolean('is_default')->default(false);        // Có phải option mặc định không
            
            $table->timestamps();
            
            // ========================================
            // INDEXES
            // ========================================
            $table->index(['style_id', 'group_name']);            // Query options theo style và nhóm
            $table->index('sort_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('style_options');
    }
};

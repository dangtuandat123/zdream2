<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Tạo bảng styles
 * 
 * Bảng này lưu trữ các "công thức" tạo ảnh AI.
 * Mỗi Style bao gồm:
 * - Thông tin hiển thị (name, thumbnail, price)
 * - Cấu hình OpenRouter (model_id, base_prompt, config_payload)
 * - Tùy chọn (allow_user_custom_prompt)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('styles', function (Blueprint $table) {
            $table->id();
            
            // ========================================
            // THÔNG TIN HIỂN THỊ
            // ========================================
            $table->string('name', 255);                          // Tên style (VD: Ảnh Tết 2026)
            $table->string('slug', 255)->unique();                // URL-friendly slug
            $table->string('thumbnail_url', 500)->nullable();     // Ảnh bìa hiển thị
            $table->text('description')->nullable();              // Mô tả ngắn cho user
            $table->decimal('price', 10, 2)->default(1.00);       // Giá mỗi lần tạo (Credits)
            
            // ========================================
            // CẤU HÌNH OPENROUTER
            // ========================================
            $table->string('openrouter_model_id', 255);           // VD: google/gemini-2.5-flash-image-preview
            $table->text('base_prompt');                          // Prompt gốc (admin cấu hình)
            
            // JSON config cho OpenRouter (aspect_ratio, image_size, etc.)
            // VD: {"aspect_ratio": "9:16", "output_format": "webp"}
            $table->json('config_payload')->nullable();
            
            // ========================================
            // TÙY CHỌN
            // ========================================
            $table->boolean('is_active')->default(true);          // Style có hiển thị cho user không
            $table->boolean('allow_user_custom_prompt')           // Cho phép user gõ thêm text?
                  ->default(false);
            $table->unsignedInteger('sort_order')->default(0);    // Thứ tự sắp xếp
            
            $table->timestamps();
            
            // ========================================
            // INDEXES
            // ========================================
            $table->index('is_active');
            $table->index('sort_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('styles');
    }
};

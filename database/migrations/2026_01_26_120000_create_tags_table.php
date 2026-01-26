<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Tạo bảng tags
 * 
 * Quản lý tags cho styles (HOT, MỚI, SALE, etc.)
 * Mỗi tag có tên, màu gradient, icon riêng
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);                         // Tên tag (VD: HOT, MỚI)
            $table->string('color_from', 30)->default('orange-500');  // Gradient start
            $table->string('color_to', 30)->default('red-500');       // Gradient end  
            $table->string('icon', 50)->default('fa-fire');           // FontAwesome icon
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index('is_active');
            $table->index('sort_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tags');
    }
};

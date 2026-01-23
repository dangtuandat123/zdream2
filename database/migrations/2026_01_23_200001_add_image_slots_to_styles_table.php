<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Thêm image_slots vào bảng styles
 * 
 * Cho phép admin cấu hình nhiều ô upload ảnh với label tùy chỉnh.
 * VD: [{"key": "person_1", "label": "Ảnh người 1"}, {"key": "person_2", "label": "Ảnh người 2"}]
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('styles', function (Blueprint $table) {
            // JSON array của các image slots
            // Mỗi slot có: key (unique identifier), label (hiển thị)
            // VD: [{"key": "person_1", "label": "Ảnh người 1", "required": true}]
            $table->json('image_slots')->nullable()->after('allow_user_custom_prompt');
        });
    }

    public function down(): void
    {
        Schema::table('styles', function (Blueprint $table) {
            $table->dropColumn('image_slots');
        });
    }
};

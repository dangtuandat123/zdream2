<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Thêm system_images để lưu ảnh hệ thống (background, overlay, etc)
     * cho mỗi Style. Ảnh này sẽ được gửi kèm với ảnh user lên OpenRouter.
     */
    public function up(): void
    {
        Schema::table('styles', function (Blueprint $table) {
            // JSON array chứa system images
            // Format: [{ key, label, description, url }, ...]
            $table->json('system_images')->nullable()->after('image_slots');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('styles', function (Blueprint $table) {
            $table->dropColumn('system_images');
        });
    }
};

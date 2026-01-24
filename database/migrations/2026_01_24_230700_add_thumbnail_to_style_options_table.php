<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Thêm trường thumbnail cho style_options
 * 
 * Cho phép admin upload ảnh thumbnail cho mỗi option.
 * Lưu trong local storage (public disk), không phải MinIO.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('style_options', function (Blueprint $table) {
            $table->string('thumbnail', 255)->nullable()->after('icon');
        });
    }

    public function down(): void
    {
        Schema::table('style_options', function (Blueprint $table) {
            $table->dropColumn('thumbnail');
        });
    }
};

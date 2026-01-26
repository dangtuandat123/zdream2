<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Thêm tag_id vào styles, xóa is_featured/is_new
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('styles', function (Blueprint $table) {
            // Add tag_id foreign key
            $table->foreignId('tag_id')
                  ->nullable()
                  ->after('is_active')
                  ->constrained('tags')
                  ->nullOnDelete();
            
            // Remove old fields (from previous implementation)
            if (Schema::hasColumn('styles', 'is_featured')) {
                $table->dropColumn('is_featured');
            }
            if (Schema::hasColumn('styles', 'is_new')) {
                $table->dropColumn('is_new');
            }
        });
    }

    public function down(): void
    {
        Schema::table('styles', function (Blueprint $table) {
            $table->dropForeign(['tag_id']);
            $table->dropColumn('tag_id');
            
            // Restore old fields
            $table->boolean('is_featured')->default(false)->after('is_active');
            $table->boolean('is_new')->default(false)->after('is_featured');
        });
    }
};

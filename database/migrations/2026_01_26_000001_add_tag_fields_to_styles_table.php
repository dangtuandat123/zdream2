<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Thêm fields cho tag động (HOT/MỚI)
 * 
 * - is_featured: Admin đánh dấu HOT thủ công
 * - is_new: Admin đánh dấu MỚI thủ công
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('styles', function (Blueprint $table) {
            $table->boolean('is_featured')->default(false)->after('is_active');
            $table->boolean('is_new')->default(false)->after('is_featured');
            
            // Index for query optimization
            $table->index('is_featured');
            $table->index('is_new');
        });
    }

    public function down(): void
    {
        Schema::table('styles', function (Blueprint $table) {
            $table->dropIndex(['is_featured']);
            $table->dropIndex(['is_new']);
            $table->dropColumn(['is_featured', 'is_new']);
        });
    }
};

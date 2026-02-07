<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Add is_system column to styles table
 * 
 * System styles are hidden from public gallery and used for
 * special features like text-to-image without preset style.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('styles', function (Blueprint $table) {
            $table->boolean('is_system')->default(false)->after('is_new');
            $table->index('is_system');
        });
    }

    public function down(): void
    {
        Schema::table('styles', function (Blueprint $table) {
            $table->dropIndex(['is_system']);
            $table->dropColumn('is_system');
        });
    }
};

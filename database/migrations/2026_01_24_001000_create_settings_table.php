<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Tạo bảng settings
 * 
 * Lưu các cấu hình hệ thống dạng key-value.
 * VD: openrouter_api_key, site_name, etc.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique();
            $table->text('value')->nullable();
            $table->string('type', 50)->default('string'); // string, text, boolean, json
            $table->string('group', 50)->default('general'); // general, api, appearance
            $table->string('label', 255)->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_encrypted')->default(false); // For sensitive data like API keys
            $table->timestamps();
            
            $table->index('group');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};

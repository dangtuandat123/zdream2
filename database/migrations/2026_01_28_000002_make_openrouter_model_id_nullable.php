<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('styles')) {
            return;
        }

        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        // Avoid doctrine/dbal dependency by using raw SQL.
        DB::statement('ALTER TABLE `styles` MODIFY `openrouter_model_id` VARCHAR(255) NULL');
    }

    public function down(): void
    {
        if (!Schema::hasTable('styles')) {
            return;
        }

        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        DB::statement('ALTER TABLE `styles` MODIFY `openrouter_model_id` VARCHAR(255) NOT NULL');
    }
};

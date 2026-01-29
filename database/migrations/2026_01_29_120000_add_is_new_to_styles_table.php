<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('styles', function (Blueprint $table) {
            if (!Schema::hasColumn('styles', 'is_new')) {
                $table->boolean('is_new')->default(false)->after('is_featured');
                $table->index('is_new');
            }
        });
    }

    public function down(): void
    {
        Schema::table('styles', function (Blueprint $table) {
            if (Schema::hasColumn('styles', 'is_new')) {
                $table->dropIndex(['is_new']);
                $table->dropColumn('is_new');
            }
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('generated_images') && !Schema::hasColumn('generated_images', 'generation_params')) {
            Schema::table('generated_images', function (Blueprint $table) {
                $table->json('generation_params')->nullable()->after('user_custom_input');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('generated_images') && Schema::hasColumn('generated_images', 'generation_params')) {
            Schema::table('generated_images', function (Blueprint $table) {
                $table->dropColumn('generation_params');
            });
        }
    }
};

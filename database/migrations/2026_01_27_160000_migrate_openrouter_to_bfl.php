<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('styles') && !Schema::hasColumn('styles', 'bfl_model_id')) {
            Schema::table('styles', function (Blueprint $table) {
                $table->string('bfl_model_id', 255)->nullable()->after('openrouter_model_id');
            });
        }

        if (Schema::hasTable('generated_images') && !Schema::hasColumn('generated_images', 'bfl_task_id')) {
            Schema::table('generated_images', function (Blueprint $table) {
                $table->string('bfl_task_id', 255)->nullable()->after('openrouter_id');
            });
        }

        if (
            Schema::hasTable('styles') &&
            Schema::hasColumn('styles', 'openrouter_model_id') &&
            Schema::hasColumn('styles', 'bfl_model_id')
        ) {
            DB::table('styles')
                ->whereNull('bfl_model_id')
                ->update(['bfl_model_id' => DB::raw('openrouter_model_id')]);
        }

        if (
            Schema::hasTable('generated_images') &&
            Schema::hasColumn('generated_images', 'openrouter_id') &&
            Schema::hasColumn('generated_images', 'bfl_task_id')
        ) {
            DB::table('generated_images')
                ->whereNull('bfl_task_id')
                ->update(['bfl_task_id' => DB::raw('openrouter_id')]);
        }

        if (Schema::hasTable('settings')) {
            $hasBflKey = DB::table('settings')->where('key', 'bfl_api_key')->exists();
            if (!$hasBflKey) {
                DB::table('settings')->where('key', 'openrouter_api_key')->update([
                    'key' => 'bfl_api_key',
                    'label' => 'BFL API Key',
                    'description' => 'API Key từ Black Forest Labs (BFL) để gọi FLUX',
                    'group' => 'api',
                ]);
            }

            $hasBflBaseUrl = DB::table('settings')->where('key', 'bfl_base_url')->exists();
            if (!$hasBflBaseUrl) {
                DB::table('settings')->where('key', 'openrouter_base_url')->update([
                    'key' => 'bfl_base_url',
                    'label' => 'BFL Base URL',
                    'description' => 'Base URL của BFL API',
                    'group' => 'api',
                ]);
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('styles') && Schema::hasColumn('styles', 'bfl_model_id')) {
            Schema::table('styles', function (Blueprint $table) {
                $table->dropColumn('bfl_model_id');
            });
        }

        if (Schema::hasTable('generated_images') && Schema::hasColumn('generated_images', 'bfl_task_id')) {
            Schema::table('generated_images', function (Blueprint $table) {
                $table->dropColumn('bfl_task_id');
            });
        }

        if (Schema::hasTable('settings')) {
            $hasOpenRouterKey = DB::table('settings')->where('key', 'openrouter_api_key')->exists();
            if (!$hasOpenRouterKey) {
                DB::table('settings')->where('key', 'bfl_api_key')->update([
                    'key' => 'openrouter_api_key',
                    'label' => 'OpenRouter API Key',
                    'description' => 'API Key từ OpenRouter.ai để gọi các model AI',
                    'group' => 'api',
                ]);
            }

            $hasOpenRouterBase = DB::table('settings')->where('key', 'openrouter_base_url')->exists();
            if (!$hasOpenRouterBase) {
                DB::table('settings')->where('key', 'bfl_base_url')->update([
                    'key' => 'openrouter_base_url',
                    'label' => 'OpenRouter Base URL',
                    'description' => 'Base URL của OpenRouter API',
                    'group' => 'api',
                ]);
            }
        }
    }
};

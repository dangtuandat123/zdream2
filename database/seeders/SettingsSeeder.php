<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

/**
 * Seeder: Default Settings
 * 
 * Tạo các settings mặc định cho hệ thống.
 */
class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        // API Settings
        Setting::set('openrouter_api_key', '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', [
            'type' => 'string',
            'group' => 'api',
            'label' => 'OpenRouter API Key',
            'description' => 'API Key từ OpenRouter.ai để gọi các model AI',
            'is_encrypted' => true,
        ]);

        Setting::set('openrouter_base_url', 'https://openrouter.ai/api/v1', [
            'type' => 'string',
            'group' => 'api',
            'label' => 'OpenRouter Base URL',
            'description' => 'Base URL của OpenRouter API',
            'is_encrypted' => false,
        ]);

        // General Settings
        Setting::set('site_name', 'ZDream AI', [
            'type' => 'string',
            'group' => 'general',
            'label' => 'Tên website',
            'description' => 'Tên hiển thị của website',
        ]);

        Setting::set('default_credits', 10, [
            'type' => 'integer',
            'group' => 'general',
            'label' => 'Xu mặc định',
            'description' => 'Số Xu tặng khi user mới đăng ký',
        ]);
    }
}

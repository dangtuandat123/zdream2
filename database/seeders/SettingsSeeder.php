<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

/**
 * Seeder: Default Settings
 * 
 * Tạo các settings mặc định cho hệ thống.
 * LƯU Ý: API keys phải được cấu hình qua Admin hoặc .env, KHÔNG hardcode ở đây!
 */
class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        // API Settings - PLACEHOLDER ONLY (cấu hình thật qua Admin)
        Setting::set('openrouter_api_key', env('OPENROUTER_API_KEY', ''), [
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

        $this->command->warn('⚠️  Nhớ cấu hình OpenRouter API Key qua Admin hoặc .env!');
    }
}

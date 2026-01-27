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
        Setting::set('bfl_api_key', env('BFL_API_KEY', ''), [
            'type' => 'string',
            'group' => 'api',
            'label' => 'BFL API Key',
            'description' => 'API Key từ Black Forest Labs (BFL) để gọi FLUX',
            'is_encrypted' => true,
        ]);

        Setting::set('bfl_base_url', 'https://api.bfl.ai', [
            'type' => 'string',
            'group' => 'api',
            'label' => 'BFL Base URL',
            'description' => 'Base URL của BFL API',
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

        Setting::set('credit_exchange_rate', 1000, [
            'type' => 'integer',
            'group' => 'general',
            'label' => 'Tỉ lệ VND/Xu',
            'description' => 'Số VND tương ứng 1 Xu',
        ]);

        $this->command->warn('⚠️  Nhớ cấu hình BFL API Key qua Admin hoặc .env!');
    }
}

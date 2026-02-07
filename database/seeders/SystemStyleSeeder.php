<?php

namespace Database\Seeders;

use App\Models\Style;
use Illuminate\Database\Seeder;

/**
 * Seeder: Create system Text-to-Image style
 * 
 * This style is used for the /create page (text-to-image without preset style).
 * It's hidden from the public gallery (is_system = true).
 */
class SystemStyleSeeder extends Seeder
{
    public function run(): void
    {
        Style::updateOrCreate(
            ['slug' => Style::SYSTEM_T2I_SLUG],
            [
                'name' => 'Text to Image',
                'slug' => Style::SYSTEM_T2I_SLUG,
                'description' => 'Tạo ảnh AI từ mô tả văn bản. Nhập prompt và để AI biến ý tưởng thành hình ảnh.',
                'thumbnail_url' => null,
                'price' => 5.00, // 5 credits per image
                'bfl_model_id' => 'flux-pro-1.1-ultra', // Default model
                'base_prompt' => '', // No base prompt - user provides everything
                'config_payload' => [
                    'aspect_ratio' => '1:1',
                    'output_format' => 'jpeg',
                    'safety_tolerance' => 2,
                ],
                'is_active' => true,
                'is_system' => true,
                'is_featured' => false,
                'is_new' => false,
                'allow_user_custom_prompt' => true,
                'image_slots' => null,
                'system_images' => null,
                'sort_order' => 0,
            ]
        );

        $this->command->info('✅ System style "Text to Image" created/updated.');
    }
}

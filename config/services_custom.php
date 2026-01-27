<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Black Forest Labs (BFL) FLUX API Configuration
    |--------------------------------------------------------------------------
    |
    | Cấu hình kết nối đến BFL API cho việc tạo ảnh AI.
    | Hệ thống chỉ dùng FLUX models từ BFL.
    |
    */
    'bfl' => [
        'api_key' => env('BFL_API_KEY'),
        'base_url' => env('BFL_BASE_URL', 'https://api.bfl.ai'),
        'timeout' => 120,
        'poll_timeout' => 120,
        'max_dimension' => 1408,
        'min_dimension' => 256,
        'dimension_multiple' => 32,

        // Danh sách models BFL (Jan 2026)
        'models' => [
            [
                'id' => 'flux-2-max',
                'name' => 'FLUX.2 Max',
                'description' => 'FLUX.2 Max (text-to-image)',
                'supports_image_input' => true,
                'supports_aspect_ratio' => false,
                'supports_width_height' => true,
                'supports_seed' => true,
                'supports_steps' => false,
                'supports_guidance' => false,
                'supports_prompt_upsampling' => false,
                'supports_output_format' => true,
                'supports_safety_tolerance' => true,
                'supports_raw' => false,
                'supports_image_prompt_strength' => false,
                'max_input_images' => 8,
                'output_formats' => ['jpeg', 'png'],
                'safety_tolerance' => ['min' => 0, 'max' => 5, 'default' => 2],
            ],
            [
                'id' => 'flux-2-pro',
                'name' => 'FLUX.2 Pro',
                'description' => 'FLUX.2 Pro (text-to-image)',
                'supports_image_input' => true,
                'supports_aspect_ratio' => false,
                'supports_width_height' => true,
                'supports_seed' => true,
                'supports_steps' => false,
                'supports_guidance' => false,
                'supports_prompt_upsampling' => false,
                'supports_output_format' => true,
                'supports_safety_tolerance' => true,
                'supports_raw' => false,
                'supports_image_prompt_strength' => false,
                'max_input_images' => 8,
                'output_formats' => ['jpeg', 'png'],
                'safety_tolerance' => ['min' => 0, 'max' => 5, 'default' => 2],
            ],
            [
                'id' => 'flux-2-flex',
                'name' => 'FLUX.2 Flex',
                'description' => 'FLUX.2 Flex (text-to-image)',
                'supports_image_input' => true,
                'supports_aspect_ratio' => false,
                'supports_width_height' => true,
                'supports_seed' => true,
                'supports_steps' => true,
                'supports_guidance' => true,
                'supports_prompt_upsampling' => true,
                'supports_output_format' => true,
                'supports_safety_tolerance' => true,
                'supports_raw' => false,
                'supports_image_prompt_strength' => false,
                'max_input_images' => 8,
                'output_formats' => ['jpeg', 'png'],
                'steps' => ['min' => 1, 'max' => 50, 'default' => 50],
                'guidance' => ['min' => 1.5, 'max' => 10, 'default' => 5],
                'safety_tolerance' => ['min' => 0, 'max' => 5, 'default' => 2],
            ],
            [
                'id' => 'flux-2-klein-4b',
                'name' => 'FLUX.2 Klein 4B',
                'description' => 'FLUX.2 Klein 4B (text-to-image)',
                'supports_image_input' => true,
                'supports_aspect_ratio' => false,
                'supports_width_height' => true,
                'supports_seed' => true,
                'supports_steps' => false,
                'supports_guidance' => false,
                'supports_prompt_upsampling' => false,
                'supports_output_format' => true,
                'supports_safety_tolerance' => true,
                'supports_raw' => false,
                'supports_image_prompt_strength' => false,
                'max_input_images' => 4,
                'output_formats' => ['jpeg', 'png'],
                'safety_tolerance' => ['min' => 0, 'max' => 5, 'default' => 2],
            ],
            [
                'id' => 'flux-2-klein-9b',
                'name' => 'FLUX.2 Klein 9B',
                'description' => 'FLUX.2 Klein 9B (text-to-image)',
                'supports_image_input' => true,
                'supports_aspect_ratio' => false,
                'supports_width_height' => true,
                'supports_seed' => true,
                'supports_steps' => false,
                'supports_guidance' => false,
                'supports_prompt_upsampling' => false,
                'supports_output_format' => true,
                'supports_safety_tolerance' => true,
                'supports_raw' => false,
                'supports_image_prompt_strength' => false,
                'max_input_images' => 4,
                'output_formats' => ['jpeg', 'png'],
                'safety_tolerance' => ['min' => 0, 'max' => 5, 'default' => 2],
            ],
            [
                'id' => 'flux-kontext-pro',
                'name' => 'FLUX Kontext Pro',
                'description' => 'Kontext (multi-ref, edit/create)',
                'supports_image_input' => true,
                'supports_aspect_ratio' => true,
                'supports_width_height' => false,
                'supports_seed' => true,
                'supports_steps' => false,
                'supports_guidance' => false,
                'supports_prompt_upsampling' => true,
                'supports_output_format' => true,
                'supports_safety_tolerance' => true,
                'supports_raw' => false,
                'supports_image_prompt_strength' => false,
                'max_input_images' => 4,
                'output_formats' => ['jpeg', 'png'],
                'safety_tolerance' => ['min' => 0, 'max' => 6, 'default' => 2],
            ],
            [
                'id' => 'flux-kontext-max',
                'name' => 'FLUX Kontext Max',
                'description' => 'Kontext Max (multi-ref, edit/create)',
                'supports_image_input' => true,
                'supports_aspect_ratio' => true,
                'supports_width_height' => false,
                'supports_seed' => true,
                'supports_steps' => false,
                'supports_guidance' => false,
                'supports_prompt_upsampling' => true,
                'supports_output_format' => true,
                'supports_safety_tolerance' => true,
                'supports_raw' => false,
                'supports_image_prompt_strength' => false,
                'max_input_images' => 4,
                'output_formats' => ['jpeg', 'png'],
                'safety_tolerance' => ['min' => 0, 'max' => 6, 'default' => 2],
            ],
            [
                'id' => 'flux-pro-1.1',
                'name' => 'FLUX 1.1 Pro',
                'description' => 'FLUX 1.1 Pro',
                'supports_image_input' => true,
                'supports_aspect_ratio' => false,
                'supports_width_height' => true,
                'supports_seed' => true,
                'supports_steps' => false,
                'supports_guidance' => false,
                'supports_prompt_upsampling' => true,
                'supports_output_format' => true,
                'supports_safety_tolerance' => true,
                'supports_raw' => false,
                'supports_image_prompt_strength' => false,
                'max_input_images' => 1,
                'uses_image_prompt' => true,
                'output_formats' => ['jpeg', 'png'],
                'safety_tolerance' => ['min' => 0, 'max' => 6, 'default' => 2],
            ],
            [
                'id' => 'flux-pro-1.1-ultra',
                'name' => 'FLUX 1.1 Pro Ultra',
                'description' => 'FLUX 1.1 Pro Ultra',
                'supports_image_input' => true,
                'supports_aspect_ratio' => true,
                'supports_width_height' => false,
                'supports_seed' => true,
                'supports_steps' => false,
                'supports_guidance' => false,
                'supports_prompt_upsampling' => true,
                'supports_output_format' => true,
                'supports_safety_tolerance' => true,
                'supports_raw' => true,
                'supports_image_prompt_strength' => true,
                'max_input_images' => 1,
                'uses_image_prompt' => true,
                'output_formats' => ['jpeg', 'png'],
                'image_prompt_strength' => ['min' => 0, 'max' => 1, 'default' => 0.1],
                'safety_tolerance' => ['min' => 0, 'max' => 6, 'default' => 2],
            ],
            [
                'id' => 'flux-pro',
                'name' => 'FLUX Pro',
                'description' => 'FLUX Pro',
                'supports_image_input' => false,
                'supports_aspect_ratio' => false,
                'supports_width_height' => false,
                'supports_seed' => false,
                'supports_steps' => false,
                'supports_guidance' => false,
                'supports_prompt_upsampling' => false,
                'supports_output_format' => false,
                'supports_safety_tolerance' => false,
                'supports_raw' => false,
                'supports_image_prompt_strength' => false,
                'max_input_images' => 0,
            ],
            [
                'id' => 'flux-dev',
                'name' => 'FLUX Dev',
                'description' => 'FLUX Dev',
                'supports_image_input' => true,
                'supports_aspect_ratio' => false,
                'supports_width_height' => true,
                'supports_seed' => true,
                'supports_steps' => true,
                'supports_guidance' => true,
                'supports_prompt_upsampling' => true,
                'supports_output_format' => true,
                'supports_safety_tolerance' => true,
                'supports_raw' => false,
                'supports_image_prompt_strength' => false,
                'max_input_images' => 1,
                'uses_image_prompt' => true,
                'output_formats' => ['jpeg', 'png'],
                'steps' => ['min' => 1, 'max' => 50, 'default' => 28],
                'guidance' => ['min' => 1.5, 'max' => 5, 'default' => 3],
                'safety_tolerance' => ['min' => 0, 'max' => 6, 'default' => 2],
            ],
        ],

        // Aspect ratios hỗ trợ (UI selection)
        'aspect_ratios' => [
            '1:1' => 'Vuông (1:1)',
            '16:9' => 'Ngang Wide (16:9)',
            '9:16' => 'Dọc Portrait (9:16)',
            '4:3' => 'Ngang (4:3)',
            '3:4' => 'Dọc (3:4)',
            '3:2' => 'Photo (3:2)',
            '2:3' => 'Photo Dọc (2:3)',
            '5:4' => 'Vuông (5:4)',
            '4:5' => 'Instagram (4:5)',
            '21:9' => 'Cinematic (21:9)',
        ],

        // Mapping ratio -> width/height (cho models cần width/height)
        'ratio_dimensions' => [
            '1:1' => ['width' => 1024, 'height' => 1024],
            '16:9' => ['width' => 1344, 'height' => 768],
            '9:16' => ['width' => 768, 'height' => 1344],
            '4:3' => ['width' => 1152, 'height' => 864],
            '3:4' => ['width' => 864, 'height' => 1152],
            '3:2' => ['width' => 1344, 'height' => 896],
            '2:3' => ['width' => 896, 'height' => 1344],
            '5:4' => ['width' => 1280, 'height' => 1024],
            '4:5' => ['width' => 1024, 'height' => 1280],
            '21:9' => ['width' => 1408, 'height' => 608],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | VietQR Configuration
    |--------------------------------------------------------------------------
    |
    | Cấu hình thanh toán qua VietQR (mã QR tĩnh).
    |
    */

    'vietqr' => [
        'bank_id' => env('VIETQR_BANK_ID', '970416'), // MB Bank
        'account_number' => env('VIETQR_ACCOUNT_NUMBER'),
        'account_name' => env('VIETQR_ACCOUNT_NAME'),
        'template' => env('VIETQR_TEMPLATE', 'rdXzPHV'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Internal API Security
    |--------------------------------------------------------------------------
    |
    | Token bí mật để bảo vệ các API nội bộ (cộng/trừ tiền).
    |
    */

    'internal_api_secret' => env('INTERNAL_API_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | Credits Pricing
    |--------------------------------------------------------------------------
    |
    | Cấu hình giá cơ bản cho các loại tạo ảnh.
    |
    */

    'pricing' => [
        'default_credits' => 10.00, // Credits miễn phí khi đăng ký
        'min_credits_per_generation' => 1.00,
    ],

];

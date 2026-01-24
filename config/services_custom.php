<?php

return [

    /*
    |--------------------------------------------------------------------------
    | OpenRouter API Configuration
    |--------------------------------------------------------------------------
    |
    | Cấu hình kết nối đến OpenRouter API cho việc tạo ảnh AI.
    | Các model hỗ trợ modality: image (Gemini, Flux, etc.)
    |
    */

    'openrouter' => [
        'api_key' => env('OPENROUTER_API_KEY'),
        'base_url' => env('OPENROUTER_BASE_URL', 'https://openrouter.ai/api/v1'),
        'timeout' => 120, // seconds - tăng timeout cho image generation
        
        // Các model mặc định hỗ trợ image generation
        'models' => [
            'gemini' => 'google/gemini-2.5-flash-image-preview',
            'flux_schnell' => 'black-forest-labs/flux-1-schnell',
            'flux_pro' => 'black-forest-labs/flux-pro',
        ],
        
        // Aspect ratios hỗ trợ (đồng bộ với ImageGenerator)
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

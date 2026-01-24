<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\Style;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * OpenRouterService
 * 
 * Xử lý việc gọi OpenRouter API để tạo ảnh AI.
 * Hỗ trợ các model: Gemini, Flux, etc.
 * API key được lấy từ database (Settings).
 */
class OpenRouterService
{
    protected string $apiKey;
    protected string $baseUrl;
    protected int $timeout;
    protected int $maxImageBytes = 10485760; // 10MB

    public function __construct()
    {
        // Lấy API key từ database Settings (có cache và decrypt)
        $this->apiKey = Setting::get('openrouter_api_key', config('services_custom.openrouter.api_key', ''));
        $this->baseUrl = Setting::get('openrouter_base_url', config('services_custom.openrouter.base_url', 'https://openrouter.ai/api/v1'));
        $this->baseUrl = rtrim($this->baseUrl, '/');
        $this->timeout = config('services_custom.openrouter.timeout', 120);
    }

    /**
     * Tạo HTTP client với headers chuẩn
     */
    protected function client(): PendingRequest
    {
        return Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
            'HTTP-Referer' => config('app.url'),
            'X-Title' => config('app.name'),
        ])->timeout($this->timeout);
    }

    /**
     * Kiểm tra số dư tài khoản OpenRouter
     * 
     * @return array ['balance' => float, 'usage' => array, 'rate_limit' => array]
     */
    public function checkBalance(): array
    {
        try {
            $response = $this->client()->get($this->baseUrl . '/auth/key');
            
            if (!$response->successful()) {
                Log::error('OpenRouter balance check failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return ['error' => 'Failed to check balance'];
            }
            
            $data = $response->json();
            
            // DEBUG: Log response structure ?? debug c?c model kh?c nhau
            $choices = $data['choices'] ?? [];
            $firstMessage = $choices[0]['message'] ?? [];
            Log::debug('OpenRouter API response structure', [
                'model' => $style->openrouter_model_id,
                'has_choices' => !empty($choices),
                'choices_count' => is_array($choices) ? count($choices) : 0,
                'message_keys' => is_array($firstMessage) ? array_keys($firstMessage) : [],
                'has_images' => isset($firstMessage['images']),
                'images_count' => isset($firstMessage['images']) && is_array($firstMessage['images']) ? count($firstMessage['images']) : 0,
                'content_type' => isset($firstMessage['content']) ? gettype($firstMessage['content']) : null,
            ]);
            
            // Parse response ?? l?y Base64 image
            $imageBase64 = $this->extractImageFromResponse($data);
            
            if (empty($imageBase64)) {
                // Log th?m chi ti?t khi kh?ng extract ???c
                Log::error('Failed to extract image from response', [
                    'model' => $style->openrouter_model_id,
                    'response_preview' => substr(json_encode($data), 0, 2000),
                ]);
                
                return [
                    'success' => false,
                    'error' => 'No image data in response',
                    'final_prompt' => $finalPrompt,
                ];

            return [
                    'success' => false,
                    'error' => 'No image data in response',
                    'final_prompt' => $finalPrompt,
                ];
            }

            return [
                'balance' => $data['data']['credit_balance'] ?? 0,
                'usage' => $data['data']['usage'] ?? [],
                'rate_limit' => $data['data']['rate_limit'] ?? [],
            ];
            
        } catch (\Exception $e) {
            Log::error('OpenRouter balance check exception', ['error' => $e->getMessage()]);
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Fetch danh sách models có khả năng tạo ảnh từ OpenRouter API
     * 
     * Logic filter:
     * 1. Ưu tiên output_modalities chứa 'image' nếu API trả về
     * 2. Fallback: filter theo known image model IDs
     * 
     * @param bool $forceRefresh Bỏ qua cache và fetch mới
     * @return array Danh sách models
     */
    public function fetchImageModels(bool $forceRefresh = false): array
    {
        $cacheKey = 'openrouter_image_models';
        
        if ($forceRefresh) {
            \Illuminate\Support\Facades\Cache::forget($cacheKey);
        }
        
        return \Illuminate\Support\Facades\Cache::remember($cacheKey, 3600, function () {
            try {
                $response = $this->client()->get($this->baseUrl . '/models');
                
                if (!$response->successful()) {
                    Log::error('OpenRouter models fetch failed', [
                        'status' => $response->status(),
                        'body' => $response->body(),
                    ]);
                    return $this->getFallbackModels();
                }
                
                $data = $response->json();
                $models = [];
                
                // Known image generation model patterns (Jan 2026)
                // Mở rộng để bắt nhiều models hơn
                $imageModelPatterns = [
                    // Gemini
                    '-image-', '-image',
                    'gemini-2.0-flash-exp',
                    // FLUX
                    'flux-', 'flux.',
                    // OpenAI
                    'dall-e', 'dalle', 'gpt-5-image', 'gpt-image',
                    // Stability
                    'stable-diffusion', 'sdxl', 'sd3',
                    // Others
                    'ideogram', 'recraft', 'playground', 'imagen', 'leonardo',
                    'midjourney', 'riverflow',
                ];
                
                foreach ($data['data'] ?? [] as $model) {
                    $modelId = strtolower($model['id'] ?? '');
                    $outputModalities = $model['output_modalities'] ?? [];
                    
                    // Check 1: output_modalities chứa 'image'
                    $isImageModel = in_array('image', $outputModalities);
                    
                    // Check 2: model ID match với known patterns
                    if (!$isImageModel) {
                        foreach ($imageModelPatterns as $pattern) {
                            if (str_contains($modelId, $pattern)) {
                                $isImageModel = true;
                                break;
                            }
                        }
                    }
                    
                    if ($isImageModel) {
                        $pricing = $model['pricing'] ?? [];
                        $models[] = [
                            'id' => $model['id'],
                            'name' => $model['name'] ?? $model['id'],
                            'description' => $model['description'] ?? '',
                            'pricing' => $pricing,
                            'prompt_price' => (float) ($pricing['prompt'] ?? 0),
                            'completion_price' => (float) ($pricing['completion'] ?? 0),
                            'context_length' => $model['context_length'] ?? 0,
                            'output_modalities' => $outputModalities,
                            'supports_image_config' => str_contains($modelId, 'gemini'),
                        ];
                    }
                }
                
                // QUAN TRỌNG: Merge với fallback list để đảm bảo có đủ models
                // Vì API /models không trả về output_modalities cho tất cả models
                $fallbackModels = $this->getFallbackModels();
                $existingIds = array_column($models, 'id');
                
                foreach ($fallbackModels as $fallbackModel) {
                    if (!in_array($fallbackModel['id'], $existingIds)) {
                        $models[] = $fallbackModel;
                    }
                }
                
                // Sort by name
                usort($models, fn($a, $b) => strcmp($a['name'], $b['name']));
                
                Log::info('OpenRouter image models fetched', [
                    'from_api' => count($existingIds),
                    'from_fallback' => count($models) - count($existingIds),
                    'total' => count($models),
                ]);
                
                return $models;
                
            } catch (\Exception $e) {
                Log::error('OpenRouter models fetch exception', ['error' => $e->getMessage()]);
                return $this->getFallbackModels();
            }
        });
    }

    /**
     * Fallback models nếu API không khả dụng (theo OpenRouter docs)
     */
    protected function getFallbackModels(): array
    {
        // Danh sách đầy đủ image generation models từ OpenRouter (Jan 2026)
        // Nguồn: https://openrouter.ai/models?output_modalities=image
        // prompt_price: -1 = chưa biết giá (fallback), 0 = miễn phí
        return [
            // === GOOGLE GEMINI ===
            ['id' => 'google/gemini-3-pro-image-preview', 'name' => 'Gemini 3 Pro Image', 'description' => 'Google Gemini 3 Pro - Image Generation', 'supports_image_config' => true, 'prompt_price' => -1],
            ['id' => 'google/gemini-2.5-flash-image', 'name' => 'Gemini 2.5 Flash Image', 'description' => 'Google Gemini 2.5 Flash - Image Generation', 'supports_image_config' => true, 'prompt_price' => -1],
            ['id' => 'google/gemini-2.0-flash-exp:free', 'name' => 'Gemini 2.0 Flash (Free)', 'description' => 'Google Gemini 2.0 Flash - Free tier', 'supports_image_config' => true, 'prompt_price' => 0],
            
            // === OPENAI GPT IMAGE ===
            ['id' => 'openai/gpt-5-image', 'name' => 'GPT-5 Image', 'description' => 'OpenAI GPT-5 Image Generation', 'supports_image_config' => false, 'prompt_price' => -1],
            ['id' => 'openai/gpt-5-image-mini', 'name' => 'GPT-5 Image Mini', 'description' => 'OpenAI GPT-5 Image Mini (Faster)', 'supports_image_config' => false, 'prompt_price' => -1],
            ['id' => 'openai/dall-e-3', 'name' => 'DALL-E 3', 'description' => 'OpenAI DALL-E 3', 'supports_image_config' => false, 'prompt_price' => -1],
            
            // === BLACK FOREST LABS FLUX ===
            ['id' => 'black-forest-labs/flux-1.1-pro', 'name' => 'FLUX 1.1 Pro', 'description' => 'Black Forest Labs FLUX 1.1 Pro', 'supports_image_config' => false, 'prompt_price' => -1],
            ['id' => 'black-forest-labs/flux-pro', 'name' => 'FLUX Pro', 'description' => 'Black Forest Labs FLUX Pro', 'supports_image_config' => false, 'prompt_price' => -1],
            ['id' => 'black-forest-labs/flux-schnell', 'name' => 'FLUX Schnell', 'description' => 'Black Forest Labs FLUX Schnell (Fast)', 'supports_image_config' => false, 'prompt_price' => -1],
            ['id' => 'black-forest-labs/flux-dev', 'name' => 'FLUX Dev', 'description' => 'Black Forest Labs FLUX Dev', 'supports_image_config' => false, 'prompt_price' => -1],
            
            // === STABILITY AI ===
            ['id' => 'stability-ai/stable-diffusion-3', 'name' => 'Stable Diffusion 3', 'description' => 'Stability AI SD3', 'supports_image_config' => false, 'prompt_price' => -1],
            ['id' => 'stability-ai/sdxl', 'name' => 'SDXL', 'description' => 'Stability AI SDXL', 'supports_image_config' => false, 'prompt_price' => -1],
            
            // === IDEOGRAM ===
            ['id' => 'ideogram/ideogram-v2', 'name' => 'Ideogram V2', 'description' => 'Ideogram V2 - Text-to-Image', 'supports_image_config' => false, 'prompt_price' => -1],
            ['id' => 'ideogram/ideogram-v2-turbo', 'name' => 'Ideogram V2 Turbo', 'description' => 'Ideogram V2 Turbo (Fast)', 'supports_image_config' => false, 'prompt_price' => -1],
            
            // === RECRAFT ===
            ['id' => 'recraft/recraft-v3-svg', 'name' => 'Recraft V3 SVG', 'description' => 'Recraft V3 - SVG Generation', 'supports_image_config' => false, 'prompt_price' => -1],
            ['id' => 'recraft/recraft-v3', 'name' => 'Recraft V3', 'description' => 'Recraft V3 Image Generation', 'supports_image_config' => false, 'prompt_price' => -1],
        ];
    }

    /**
     * Tạo ảnh từ Style và options
     * 
     * @param Style $style Style đã chọn
     * @param array $selectedOptionIds Danh sách ID của options đã chọn
     * @param string|null $userCustomInput Nội dung user tự gõ
     * @param string|null $aspectRatio Aspect ratio được user chọn (override style default)
     * @param string|null $imageSize Image size (1K/2K/4K) - chỉ cho Gemini models
     * @param array $inputImages Array of base64 images cho img2img (key => base64)
     * @return array ['success' => bool, 'image_base64' => string|null, 'openrouter_id' => string|null, 'error' => string|null]
     */
    public function generateImage(
        Style $style, 
        array $selectedOptionIds = [], 
        ?string $userCustomInput = null,
        ?string $aspectRatio = null,
        ?string $imageSize = null,
        array $inputImages = []
    ): array {
        try {
            // Build final prompt
            $finalPrompt = $style->buildFinalPrompt($selectedOptionIds, $userCustomInput);

            if (empty($this->apiKey)) {
                Log::warning('OpenRouter API key missing');
                return [
                    'success' => false,
                    'error' => 'Thiếu OpenRouter API key',
                    'final_prompt' => $finalPrompt,
                ];
            }
            
            // Build OpenRouter payload
            $payload = $style->buildOpenRouterPayload($finalPrompt);
            
            // Nếu có input images (img2img), cập nhật message content
            if (!empty($inputImages)) {
                // Lấy image_slots config từ style để có description
                $imageSlots = $style->image_slots ?? [];
                $slotDescriptions = collect($imageSlots)->keyBy('key')->map(fn($s) => $s['description'] ?? '')->toArray();
                
                // Build text prompt với image descriptions
                $imageDescText = '';
                foreach ($inputImages as $key => $imageBase64) {
                    $desc = $slotDescriptions[$key] ?? '';
                    if ($desc) {
                        $imageDescText .= "\n[Image: {$desc}]";
                    }
                }
                
                $contentParts = [
                    [
                        'type' => 'text',
                        'text' => $finalPrompt . $imageDescText,
                    ],
                ];
                
                // Thêm tất cả user input images vào content
                foreach ($inputImages as $key => $imageBase64) {
                    $contentParts[] = [
                        'type' => 'image_url',
                        'image_url' => [
                            'url' => $imageBase64,
                        ],
                    ];
                }
                
                $payload['messages'][0]['content'] = $contentParts;
            }
            
            // Thêm system_images từ Style config (background, overlay, etc)
            $systemImages = $style->system_images ?? [];
            if (!empty($systemImages)) {
                // Build description text cho system images
                $sysDescText = '';
                foreach ($systemImages as $sysImg) {
                    $desc = $sysImg['description'] ?? '';
                    $label = $sysImg['label'] ?? 'System Image';
                    if ($desc) {
                        $sysDescText .= "\n[{$label}: {$desc}]";
                    }
                }
                
                // Đảm bảo content là array
                if (is_string($payload['messages'][0]['content'])) {
                    $payload['messages'][0]['content'] = [
                        ['type' => 'text', 'text' => $payload['messages'][0]['content'] . $sysDescText],
                    ];
                } else {
                    // Append system images description to text
                    $payload['messages'][0]['content'][0]['text'] .= $sysDescText;
                }
                
                // Thêm system images vào content parts
                foreach ($systemImages as $sysImg) {
                    $url = $sysImg['url'] ?? '';
                    if ($url) {
                        // Download và convert sang base64 nếu là URL
                        $base64DataUrl = $this->downloadImageAsBase64($url);
                        if ($base64DataUrl) {
                            $payload['messages'][0]['content'][] = [
                                'type' => 'image_url',
                                'image_url' => [
                                    'url' => $base64DataUrl,
                                ],
                            ];
                        }
                    }
                }
            }
            
            // Override aspect ratio nếu user đã chọn
            // CHÚ Ý: image_config chỉ cho Gemini models
            $isGeminiModel = str_contains(strtolower($style->openrouter_model_id), 'gemini');
            
            if ($aspectRatio) {
                if ($isGeminiModel) {
                    // Gemini: dùng image_config
                    $payload['image_config'] = $payload['image_config'] ?? [];
                    $payload['image_config']['aspect_ratio'] = $aspectRatio;
                } else {
                    // FLUX, DALL-E, v.v.: chèn thông tin vào prompt
                    $aspectText = ", {$aspectRatio} aspect ratio";
                    if (is_string($payload['messages'][0]['content'])) {
                        $payload['messages'][0]['content'] .= $aspectText;
                    } else {
                        $payload['messages'][0]['content'][0]['text'] .= $aspectText;
                    }
                }
            }
            
            // Override image size nếu user đã chọn (CHỈ cho Gemini models)
            if ($imageSize && $isGeminiModel) {
                $payload['image_config'] = $payload['image_config'] ?? [];
                $payload['image_config']['image_size'] = $imageSize;
            }

            // DEBUG: Log payload ?? ???c l?m s?ch ?? tr?nh l? d? li?u nh?y c?m
            Log::debug('OpenRouter request', [
                'model' => $style->openrouter_model_id,
                'prompt_length' => strlen($finalPrompt),
                'has_input_images' => !empty($inputImages),
                'input_images_count' => count($inputImages),
                'api_key_present' => !empty($this->apiKey),
                'base_url' => $this->baseUrl,
                'payload_keys' => array_keys($payload),
                'message_content_type' => is_array($payload['messages'][0]['content']) ? 'array' : 'string',
            ]);
            
            $logPayload = $payload;
            if (isset($logPayload['messages'][0]['content'])) {
                if (is_string($logPayload['messages'][0]['content'])) {
                    $logPayload['messages'][0]['content'] = '[REDACTED]';
                } elseif (is_array($logPayload['messages'][0]['content'])) {
                    foreach ($logPayload['messages'][0]['content'] as $i => $part) {
                        if (isset($part['type']) && $part['type'] === 'text') {
                            $logPayload['messages'][0]['content'][$i]['text'] = '[REDACTED]';
                        }
                        if (isset($part['image_url'])) {
                            $logPayload['messages'][0]['content'][$i]['image_url']['url'] = '[BASE64_IMAGE_TRUNCATED]';
                        }
                    }
                }
            }
            Log::debug('OpenRouter payload structure', [
                'payload' => json_encode($logPayload, JSON_PRETTY_PRINT),
            ]);


            // Tăng PHP execution time để tránh timeout cho các model chậm (GPT-5, v.v.)
            set_time_limit(180); // 3 phút
            
            // Gọi API
            $response = $this->client()->post($this->baseUrl . '/chat/completions', $payload);

            if (!$response->successful()) {
                Log::error('OpenRouter API error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                
                return [
                    'success' => false,
                    'error' => 'API error: ' . $response->status() . ' - ' . $response->body(),
                    'final_prompt' => $finalPrompt,
                ];
            }

            $data = $response->json();
            
            // DEBUG: Log response structure ?? debug c?c model kh?c nhau
            $choices = $data['choices'] ?? [];
            $firstMessage = $choices[0]['message'] ?? [];
            Log::debug('OpenRouter API response structure', [
                'model' => $style->openrouter_model_id,
                'has_choices' => !empty($choices),
                'choices_count' => is_array($choices) ? count($choices) : 0,
                'message_keys' => is_array($firstMessage) ? array_keys($firstMessage) : [],
                'has_images' => isset($firstMessage['images']),
                'images_count' => isset($firstMessage['images']) && is_array($firstMessage['images']) ? count($firstMessage['images']) : 0,
                'content_type' => isset($firstMessage['content']) ? gettype($firstMessage['content']) : null,
            ]);

            // Parse response để lấy Base64 image
            $imageBase64 = $this->extractImageFromResponse($data);
            
            if (empty($imageBase64)) {
                // Log thêm chi tiết khi không extract được
                Log::error('Failed to extract image from response', [
                    'model' => $style->openrouter_model_id,
                    'response_preview' => substr(json_encode($data), 0, 2000),
                ]);
                
                return [
                    'success' => false,
                    'error' => 'No image data in response',
                    'final_prompt' => $finalPrompt,
                ];
            }

            return [
                'success' => true,
                'image_base64' => $imageBase64,
                'openrouter_id' => $data['id'] ?? null,
                'final_prompt' => $finalPrompt,
            ];

        } catch (\Exception $e) {
            Log::error('OpenRouter exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'final_prompt' => $finalPrompt ?? null,
            ];
        }
    }

    /**
     * Extract Base64 image từ OpenRouter response
     * 
     * Theo docs, response có cấu trúc:
     * - choices[0].message.images[] (array of image objects)
     * - Mỗi image có: image_url.url chứa base64 data URL
     */
    protected function extractImageFromResponse(array $data): ?string
    {
        $choices = $data['choices'] ?? [];
        $message = $choices[0]['message'] ?? [];

        // Case 1: images array (OpenRouter standard format)
        if (!empty($message['images'])) {
            $image = $message['images'][0];
            
            // Format theo docs: { image_url: { url: "data:image/..." } }
            if (is_array($image) && isset($image['image_url']['url'])) {
                return $image['image_url']['url'];
            }
            
            // M?t s? SDK tr? v? imageUrl thay v? image_url
            if (is_array($image) && isset($image['imageUrl']['url'])) {
                return $image['imageUrl']['url'];
            }

            // Format: { url: "data:image/..." }
            if (is_array($image) && isset($image['url'])) {
                return $image['url'];
            }
            
            // Format: { data: "base64..." }
            if (is_array($image) && isset($image['data'])) {
                return $image['data'];
            }
            
            // Format: base64 string direct
            if (is_string($image)) {
                return $image;
            }
        }

        // Case 2: content chứa base64 (backup)
        if (!empty($message['content'])) {
            $content = $message['content'];
            
            // Nếu là array (multimodal content)
            if (is_array($content)) {
                foreach ($content as $part) {
                    if (isset($part['type']) && $part['type'] === 'image_url') {
                        return $part['image_url']['url'] ?? ($part['imageUrl']['url'] ?? null);
                    }
                }
            }
            
            // Nếu là base64 string thuần
            if (is_string($content) && $this->isBase64Image($content)) {
                return $content;
            }
            
            // Nếu là URL
            if (is_string($content) && filter_var($content, FILTER_VALIDATE_URL)) {
                return $this->downloadImageAsBase64($content);
            }
        }

        return null;
    }

    /**
     * Kiểm tra string có phải base64 image không
     */
    protected function isBase64Image(string $data): bool
    {
        // Kiểm tra prefix data:image/
        if (str_starts_with($data, 'data:image/')) {
            return true;
        }
        
        // Kiểm tra có valid base64 không
        $decoded = base64_decode($data, true);
        return $decoded !== false && strlen($decoded) > 100;
    }

    /**
     * Download ảnh từ URL và convert sang base64
     */
    protected function downloadImageAsBase64(string $url): ?string
    {
        if (str_starts_with($url, 'data:image/')) {
            return $url;
        }

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }

        $scheme = parse_url($url, PHP_URL_SCHEME);
        if (!in_array($scheme, ['http', 'https'], true)) {
            return null;
        }

        try {
            $response = Http::timeout(30)->get($url);

            if (!$response->successful()) {
                return null;
            }

            $contentType = $response->header('Content-Type') ?? '';
            $mime = strtolower(trim(explode(';', $contentType)[0]));

            if (!str_starts_with($mime, 'image/')) {
                Log::warning('Downloaded file is not an image', [
                    'url' => $url,
                    'content_type' => $contentType,
                ]);
                return null;
            }

            $body = $response->body();
            if (strlen($body) > $this->maxImageBytes) {
                Log::warning('Downloaded image too large', [
                    'url' => $url,
                    'bytes' => strlen($body),
                ]);
                return null;
            }

            return 'data:' . $mime . ';base64,' . base64_encode($body);
        } catch (\Exception $e) {
            Log::warning('Failed to download image', ['url' => $url, 'error' => $e->getMessage()]);
        }

        return null;
    }

    /**
     * Lấy danh sách models có sẵn
     */
    public function getAvailableModels(): array
    {
        return config('services_custom.openrouter.models', []);
    }

    /**
     * Lấy danh sách aspect ratios hỗ trợ
     */
    public function getAspectRatios(): array
    {
        return config('services_custom.openrouter.aspect_ratios', []);
    }
}

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

    public function __construct()
    {
        // Lấy API key từ database Settings (có cache và decrypt)
        $this->apiKey = Setting::get('openrouter_api_key', config('services_custom.openrouter.api_key', ''));
        $this->baseUrl = Setting::get('openrouter_base_url', config('services_custom.openrouter.base_url', 'https://openrouter.ai/api/v1'));
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
     * @param bool $forceRefresh Bỏ qua cache và fetch mới
     * @return array Danh sách models [['id' => ..., 'name' => ..., 'description' => ...], ...]
     */
    public function fetchImageModels(bool $forceRefresh = false): array
    {
        $cacheKey = 'openrouter_image_models';
        
        // Clear cache if force refresh
        if ($forceRefresh) {
            \Illuminate\Support\Facades\Cache::forget($cacheKey);
        }
        
        // Cache for 1 hour
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
                
                // Keywords để nhận dạng image generation models
                $imageKeywords = [
                    'gemini', 'flux', 'dall-e', 'dalle', 'stable-diffusion', 
                    'midjourney', 'imagen', 'ideogram', 'playground', 
                    'recraft', 'leonardo', 'image'
                ];
                
                foreach ($data['data'] ?? [] as $model) {
                    $modelId = strtolower($model['id'] ?? '');
                    $modelName = strtolower($model['name'] ?? '');
                    
                    // Check nếu model ID hoặc name chứa keywords
                    $isImageModel = false;
                    foreach ($imageKeywords as $keyword) {
                        if (str_contains($modelId, $keyword) || str_contains($modelName, $keyword)) {
                            $isImageModel = true;
                            break;
                        }
                    }
                    
                    // Hoặc check output_modalities nếu có
                    $outputModalities = $model['output_modalities'] ?? [];
                    if (in_array('image', $outputModalities)) {
                        $isImageModel = true;
                    }
                    
                    if ($isImageModel) {
                        $models[] = [
                            'id' => $model['id'],
                            'name' => $model['name'] ?? $model['id'],
                            'description' => $model['description'] ?? '',
                            'pricing' => $model['pricing'] ?? [],
                            'context_length' => $model['context_length'] ?? 0,
                        ];
                    }
                }
                
                // Sort by name
                usort($models, fn($a, $b) => strcmp($a['name'], $b['name']));
                
                Log::info('OpenRouter models fetched', ['count' => count($models)]);
                
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
        return [
            ['id' => 'google/gemini-2.5-flash-preview:thinking', 'name' => 'Gemini 2.5 Flash (Thinking)', 'description' => 'Google Gemini 2.5 Flash with thinking capability'],
            ['id' => 'google/gemini-2.0-flash-exp:free', 'name' => 'Gemini 2.0 Flash (Free)', 'description' => 'Google Gemini 2.0 Flash - Free tier'],
            ['id' => 'black-forest-labs/flux-1.1-pro', 'name' => 'FLUX 1.1 Pro', 'description' => 'Black Forest Labs FLUX 1.1 Pro'],
            ['id' => 'black-forest-labs/flux-schnell', 'name' => 'FLUX Schnell', 'description' => 'Black Forest Labs FLUX Schnell (Fast)'],
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
                        $base64 = $this->downloadImageAsBase64($url);
                        if ($base64) {
                            $payload['messages'][0]['content'][] = [
                                'type' => 'image_url',
                                'image_url' => [
                                    'url' => 'data:image/jpeg;base64,' . $base64,
                                ],
                            ];
                        }
                    }
                }
            }
            
            // Override aspect ratio nếu user đã chọn
            if ($aspectRatio) {
                $payload['image_config'] = $payload['image_config'] ?? [];
                $payload['image_config']['aspect_ratio'] = $aspectRatio;
                
                // Với Flux model, chèn aspect ratio vào prompt
                if (str_contains($style->openrouter_model_id, 'flux')) {
                    if (is_string($payload['messages'][0]['content'])) {
                        $payload['messages'][0]['content'] .= ", {$aspectRatio} aspect ratio";
                    } else {
                        $payload['messages'][0]['content'][0]['text'] .= ", {$aspectRatio} aspect ratio";
                    }
                }
            }
            
            // Override image size nếu user đã chọn (chỉ cho Gemini models)
            if ($imageSize && str_contains($style->openrouter_model_id, 'gemini')) {
                $payload['image_config'] = $payload['image_config'] ?? [];
                $payload['image_config']['image_size'] = $imageSize;
            }

            Log::info('OpenRouter request', [
                'model' => $style->openrouter_model_id,
                'prompt_length' => strlen($finalPrompt),
                'has_input_images' => !empty($inputImages),
                'input_images_count' => count($inputImages),
            ]);

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
            
            // Parse response để lấy Base64 image
            $imageBase64 = $this->extractImageFromResponse($data);
            
            if (empty($imageBase64)) {
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
        $message = $data['choices'][0]['message'] ?? [];

        // Case 1: images array (OpenRouter standard format)
        if (!empty($message['images'])) {
            $image = $message['images'][0];
            
            // Format theo docs: { image_url: { url: "data:image/..." } }
            if (is_array($image) && isset($image['image_url']['url'])) {
                return $image['image_url']['url'];
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
                        return $part['image_url']['url'] ?? null;
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
        try {
            $response = Http::timeout(30)->get($url);
            
            if ($response->successful()) {
                return base64_encode($response->body());
            }
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

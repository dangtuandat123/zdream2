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
     * Fallback models nếu API không khả dụng
     */
    protected function getFallbackModels(): array
    {
        return [
            ['id' => 'google/gemini-2.0-flash-exp:free', 'name' => 'Gemini 2.0 Flash (Free)', 'description' => 'Google Gemini 2.0 Flash - Free tier'],
            ['id' => 'google/gemini-2.5-flash-preview-05-20', 'name' => 'Gemini 2.5 Flash Preview', 'description' => 'Google Gemini 2.5 Flash'],
            ['id' => 'black-forest-labs/flux-1.1-pro', 'name' => 'Flux 1.1 Pro', 'description' => 'Black Forest Labs Flux 1.1 Pro'],
            ['id' => 'black-forest-labs/flux-schnell', 'name' => 'Flux Schnell', 'description' => 'Black Forest Labs Flux Schnell'],
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
                
                // Thêm tất cả images vào content
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
                'has_input_image' => !empty($inputImage),
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
     * Response có thể có cấu trúc:
     * - choices[0].message.images[] (array of base64)
     * - choices[0].message.content (base64 string hoặc URL)
     */
    protected function extractImageFromResponse(array $data): ?string
    {
        $message = $data['choices'][0]['message'] ?? [];

        // Case 1: images array (Gemini format)
        if (!empty($message['images'])) {
            $image = $message['images'][0];
            
            // Có thể là object với data property
            if (is_array($image) && isset($image['data'])) {
                return $image['data'];
            }
            
            // Hoặc là base64 string trực tiếp
            return $image;
        }

        // Case 2: content chứa base64 (một số model)
        if (!empty($message['content'])) {
            $content = $message['content'];
            
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

<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\Style;
use App\Services\ImageGeneration\ModelAdapterFactory;
use App\Services\ImageGeneration\ModelAdapterInterface;
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
    protected ModelAdapterFactory $adapterFactory;

    public function __construct(?ModelAdapterFactory $adapterFactory = null)
    {
        // Lấy API key từ database Settings (có cache và decrypt)
        $this->apiKey = Setting::get('openrouter_api_key', config('services_custom.openrouter.api_key', ''));
        $defaultBaseUrl = config('services_custom.openrouter.base_url', 'https://openrouter.ai/api/v1');
        $baseUrl = Setting::get('openrouter_base_url', $defaultBaseUrl);
        if (empty($baseUrl)) {
            $baseUrl = $defaultBaseUrl;
        }
        $this->baseUrl = rtrim($baseUrl, '/');
        
        // [BUG FIX] Auto-append /api/v1 nếu thiếu
        if (!str_ends_with($this->baseUrl, '/api/v1')) {
            // Remove trailing parts if partial path exists
            $this->baseUrl = preg_replace('#/api(/v\d+)?$#', '', $this->baseUrl);
            $this->baseUrl .= '/api/v1';
        }
        
        if ($this->baseUrl === '' || $this->baseUrl === '/api/v1') {
            $this->baseUrl = 'https://openrouter.ai/api/v1';
        }
        $this->timeout = config('services_custom.openrouter.timeout', 120);
        
        // Initialize adapter factory
        $this->adapterFactory = $adapterFactory ?? new ModelAdapterFactory();
    }

    /**
     * Tạo HTTP client với headers chuẩn
     * Thêm retry/backoff để xử lý 429/5xx errors
     */
    protected function client(): PendingRequest
    {
        return Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
            'HTTP-Referer' => config('app.url'),
            'X-Title' => config('app.name'),
        ])->acceptJson()
          ->timeout($this->timeout)
          ->connectTimeout(10)
          ->retry(2, 500);
    }

    /**
     * Tạo HTTP client cho POST requests với retry logic mạnh hơn
     * Retry khi gặp 429 (rate limit), 500, 502, 503, 504 errors
     */
    protected function clientForPost(): PendingRequest
    {
        return Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
            'HTTP-Referer' => config('app.url'),
            'X-Title' => config('app.name'),
        ])->acceptJson()
          ->timeout($this->timeout)
          ->connectTimeout(15)
          ->retry(3, function (int $attempt, \Exception $exception) {
              // Exponential backoff: 1s, 2s, 4s
              return min(1000 * pow(2, $attempt - 1), 4000);
          }, function (\Exception $exception, PendingRequest $request) {
              // Chỉ retry các lỗi có thể recover
              if ($exception instanceof \Illuminate\Http\Client\ConnectionException) {
                  return true;
              }
              if ($exception instanceof \Illuminate\Http\Client\RequestException) {
                  $status = $exception->response->status();
                  // Retry 429 (rate limit), 5xx errors
                  return in_array($status, [429, 500, 502, 503, 504]);
              }
              return false;
          });
    }

    /**
     * Kiểm tra số dư tài khoản OpenRouter
     * 
     * @return array Thông tin API key và credits
     */
    public function checkBalance(): array
    {
        try {
            // Sử dụng endpoint /key để lấy thông tin API key (baseUrl đã có /api/v1)
            $response = $this->client()->get($this->baseUrl . '/key');
            
            if (!$response->successful()) {
                Log::error('OpenRouter credits check failed', [
                    'status' => $response->status(),
                    'body' => substr($response->body(), 0, 500),
                ]);
                return ['error' => 'Failed to check credits', 'status' => $response->status()];
            }
            
            $data = $response->json()['data'] ?? [];
            
            return [
                'success' => true,
                'label' => $data['label'] ?? 'Unknown',
                'limit' => $data['limit'] ?? null,
                'limit_remaining' => $data['limit_remaining'] ?? null,
                'is_free_tier' => $data['is_free_tier'] ?? false,
                'usage' => [
                    'total' => $data['usage'] ?? 0,
                    'daily' => $data['usage_daily'] ?? 0,
                    'weekly' => $data['usage_weekly'] ?? 0,
                    'monthly' => $data['usage_monthly'] ?? 0,
                ],
                'byok_usage' => [
                    'total' => $data['byok_usage'] ?? 0,
                    'daily' => $data['byok_usage_daily'] ?? 0,
                ],
            ];
            
        } catch (\Exception $e) {
            Log::error('OpenRouter credits check exception', ['error' => $e->getMessage()]);
            return ['error' => $e->getMessage(), 'success' => false];
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
                    
                    // CRITICAL FIX: modalities nằm trong architecture object
                    $architecture = $model['architecture'] ?? [];
                    $outputModalities = $model['output_modalities'] ?? ($architecture['output_modalities'] ?? []);
                    $inputModalities = $model['input_modalities'] ?? ($architecture['input_modalities'] ?? []);
                    if (!is_array($outputModalities)) {
                        $outputModalities = [];
                    }
                    if (!is_array($inputModalities)) {
                        $inputModalities = [];
                    }
                    
                    // Check 1: output_modalities chứa 'image'
                    $isImageModel = in_array('image', $outputModalities, true);
                    
                    // Check 2: Strict fallback for known models if modalities missing
                    // Remove loose substring matching to prevent false positives
                    if (!$isImageModel && empty($outputModalities)) {
                        $knownImageModels = config('services_custom.openrouter.image_models_fallback', []);
                        
                        if (in_array($modelId, $knownImageModels, true)) {
                            $isImageModel = true;
                            // Backfill modalities for consistency
                            $outputModalities = ['image', 'text'];
                            $inputModalities = ['text', 'image'];
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
                            'input_modalities' => $inputModalities,
                            'supports_image_config' => str_contains($modelId, 'gemini'),
                            // Estimated cost và capabilities
                            'estimated_cost_per_image' => $this->calculateEstimatedCost($pricing),
                            'supports_text_input' => in_array('text', $inputModalities, true),
                            'supports_image_input' => in_array('image', $inputModalities, true),
                            'source' => 'api',
                        ];
                    }
                }
                
                if (empty($models)) {
                    Log::warning('OpenRouter models fetch returned no image-capable models, using fallback', [
                        'models_total' => count($data['data'] ?? []),
                    ]);
                    return $this->getFallbackModels();
                }
                
                // Sort by estimated cost (lowest first)
                usort($models, function($a, $b) {
                    $costA = $a['estimated_cost_per_image'] ?? 999;
                    $costB = $b['estimated_cost_per_image'] ?? 999;
                    return $costA <=> $costB;
                });
                
                Log::info('OpenRouter image models fetched', [
                    'from_api' => count($models),
                    'fallback_used' => false,
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
        $fallbacks = [
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

        // Normalize fallback models để match format của API models
        return array_map(function ($model) {
            return [
                'id' => $model['id'],
                'name' => $model['name'],
                'description' => $model['description'] ?? '',
                'pricing' => [],
                'prompt_price' => null,
                'completion_price' => null,
                'context_length' => 0,
                'output_modalities' => ['image'],
                'input_modalities' => ['text', 'image'], // Assume both
                'supports_image_config' => $model['supports_image_config'] ?? false,
                'estimated_cost_per_image' => null,
                'supports_text_input' => true,
                'supports_image_input' => true,
                'pricing_unknown' => true,
                'source' => 'fallback',
            ];
        }, $fallbacks);
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

            // DEBUG: Log cho việc troubleshooting option/prompt issues
            Log::debug('OpenRouter generateImage input', [
                'style_id' => $style->id,
                'selectedOptionIds' => $selectedOptionIds,
                'userCustomInput' => $userCustomInput ? mb_substr($userCustomInput, 0, 100) : null,
                'finalPrompt_length' => mb_strlen($finalPrompt),
                'finalPrompt_preview' => mb_substr($finalPrompt, 0, 200),
            ]);

            if (empty($this->apiKey)) {
                Log::warning('OpenRouter API key missing');
                return [
                    'success' => false,
                    'error' => 'Thiếu OpenRouter API key',
                    'final_prompt' => $finalPrompt,
                ];
            }
            
            // Get appropriate adapter for this model
            $adapter = $this->adapterFactory->getAdapter($style->openrouter_model_id);
            
            Log::debug('Using model adapter', [
                'model' => $style->openrouter_model_id,
                'adapter_type' => $adapter->getModelType(),
                'supports_image_config' => $adapter->supportsImageConfig(),
            ]);
            
            // Build base payload
            $payload = $style->buildOpenRouterPayload($finalPrompt);
            
            // Prepare adapter options
            $imageSlots = $style->image_slots ?? [];
            $slotDescriptions = collect($imageSlots)->keyBy('key')->map(fn($s) => $s['description'] ?? '')->toArray();
            
            $adapterOptions = [
                'aspectRatio' => $aspectRatio,
                'imageSize' => $imageSize,
                'inputImages' => $inputImages,
                'slotDescriptions' => $slotDescriptions,
            ];
            
            // Let adapter prepare the payload (model-specific handling)
            $payload = $adapter->preparePayload($payload, $adapterOptions);
            
            // Add system images from Style config (background, overlay, etc)
            // This is common to all adapters
            $systemImages = $style->system_images ?? [];
            if (!empty($systemImages)) {
                $sysDescText = '';
                foreach ($systemImages as $sysImg) {
                    $desc = $sysImg['description'] ?? '';
                    $label = $sysImg['label'] ?? 'System Image';
                    if ($desc) {
                        $sysDescText .= "\n[{$label}: {$desc}]";
                    }
                }
                
                // Ensure content is array
                if (is_string($payload['messages'][0]['content'])) {
                    $payload['messages'][0]['content'] = [
                        ['type' => 'text', 'text' => $payload['messages'][0]['content'] . $sysDescText],
                    ];
                } else {
                    $payload['messages'][0]['content'][0]['text'] .= $sysDescText;
                }
                
                // Add system images as image_url parts
                foreach ($systemImages as $sysImg) {
                    $url = $sysImg['url'] ?? '';
                    if ($url) {
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

            // DEBUG: Log payload (sanitized)
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
            // Sử dụng @ để suppress warning nếu safe_mode enabled hoặc không có quyền
            @set_time_limit(180); // 3 phút
            
            // Gọi API với retry logic cho POST requests
            $response = $this->clientForPost()->post($this->baseUrl . '/chat/completions', $payload);

            if (!$response->successful()) {
                // IMG-06 FIX: Truncate body để tránh log quá lớn
                $body = $response->body();
                $truncatedBody = strlen($body) > 2000 ? substr($body, 0, 2000) . '...[TRUNCATED]' : $body;
                
                Log::error('OpenRouter API error', [
                    'status' => $response->status(),
                    'body' => $truncatedBody,
                ]);
                
                return [
                    'success' => false,
                    'error' => 'API error: ' . $response->status() . ' - ' . $truncatedBody,
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

            // Parse response using adapter (model-specific parsing)
            $imageBase64 = $adapter->parseResponse($data);
            
            if (empty($imageBase64)) {
                // Extract text response from model as error message
                $textResponse = $adapter->extractTextResponse($data);
                
                Log::error('Failed to extract image from response', [
                    'model' => $style->openrouter_model_id,
                    'text_response' => $textResponse ? substr($textResponse, 0, 500) : null,
                    'response_preview' => substr(json_encode($data), 0, 2000),
                ]);
                
                // Tạo error message descriptive hơn
                $errorMessage = 'Model không trả về ảnh.';
                if ($textResponse) {
                    // Truncate và clean text response
                    $cleanText = strip_tags($textResponse);
                    $cleanText = preg_replace('/\s+/', ' ', $cleanText);
                    $shortText = mb_substr($cleanText, 0, 200);
                    if (mb_strlen($cleanText) > 200) {
                        $shortText .= '...';
                    }
                    $errorMessage = "Model phản hồi: \"{$shortText}\"";
                }
                
                return [
                    'success' => false,
                    'error' => $errorMessage,
                    'model_response' => $textResponse,
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
     * 
     * FIX: Changed to ?array to handle null response from json()
     */
    protected function extractImageFromResponse(?array $data): ?string
    {
        // Handle null/empty data
        if (empty($data)) {
            return null;
        }
        
        $choices = $data['choices'] ?? [];
        $message = $choices[0]['message'] ?? [];

        // Case 1: images array (OpenRouter standard format)
        if (!empty($message['images'])) {
            $image = $message['images'][0];
            
            // Format theo docs: { image_url: { url: "data:image/..." } }
            if (is_array($image) && isset($image['image_url']['url'])) {
                return $this->normalizeImageValue($image['image_url']['url']);
            }
            
            // Một số SDK trả về imageUrl thay vì image_url
            if (is_array($image) && isset($image['imageUrl']['url'])) {
                return $this->normalizeImageValue($image['imageUrl']['url']);
            }

            // Format: { url: "data:image/..." }
            if (is_array($image) && isset($image['url'])) {
                return $this->normalizeImageValue($image['url']);
            }
            
            // Format: { data: "base64..." }
            if (is_array($image) && isset($image['data'])) {
                return $this->normalizeImageValue($image['data']);
            }
            
            // Format: base64 string direct
            if (is_string($image)) {
                return $this->normalizeImageValue($image);
            }
        }

        // Case 2: content chứa base64 (backup)
        if (!empty($message['content'])) {
            $content = $message['content'];
            
            // Nếu là array (multimodal content)
            if (is_array($content)) {
                foreach ($content as $part) {
                    if (isset($part['type']) && $part['type'] === 'image_url') {
                        $url = $part['image_url']['url'] ?? ($part['imageUrl']['url'] ?? null);
                        return $url ? $this->normalizeImageValue($url) : null;
                    }
                }
            }
            
            // Nếu là string, normalize nó
            if (is_string($content)) {
                return $this->normalizeImageValue($content);
            }
        }

        return null;
    }

    /**
     * Extract text response from OpenRouter response
     * Dùng khi model trả text thay vì image (vd: từ chối, giải thích)
     */
    protected function extractTextFromResponse(?array $data): ?string
    {
        if (empty($data)) {
            return null;
        }
        
        $choices = $data['choices'] ?? [];
        $message = $choices[0]['message'] ?? [];
        
        // Check content field
        if (!empty($message['content'])) {
            $content = $message['content'];
            
            // Nếu là string, return trực tiếp
            if (is_string($content)) {
                return $content;
            }
            
            // Nếu là array (multimodal), tìm text parts
            if (is_array($content)) {
                $textParts = [];
                foreach ($content as $part) {
                    if (isset($part['type']) && $part['type'] === 'text' && !empty($part['text'])) {
                        $textParts[] = $part['text'];
                    }
                }
                return !empty($textParts) ? implode("\n", $textParts) : null;
            }
        }
        
        return null;
    }

    /**
     * Normalize image value: convert URL to base64 if needed
     */
    protected function normalizeImageValue(string $value): ?string
    {
        // Already base64 data URL
        if (str_starts_with($value, 'data:image/')) {
            return $value;
        }
        
        // HTTP(S) URL -> download and convert
        if (filter_var($value, FILTER_VALIDATE_URL)) {
            return $this->downloadImageAsBase64($value);
        }
        
        // Raw base64 string (no prefix)
        if ($this->isBase64Image($value)) {
            return $value;
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
     * Có SSRF protection: block private/local IP addresses
     * MEDIUM-01 FIX: Đọc từ MinIO storage trực tiếp nếu URL match MinIO endpoint
     */
    protected function downloadImageAsBase64(string $url): ?string
    {
        if (str_starts_with($url, 'data:image/')) {
            return $url;
        }

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }

        // MEDIUM-01 FIX: Check nếu đây là MinIO URL, đọc trực tiếp qua Storage
        $minioPath = $this->extractMinioPath($url);
        if ($minioPath !== null) {
            return $this->readMinioImageAsBase64($minioPath);
        }

        $scheme = parse_url($url, PHP_URL_SCHEME);
        if (!in_array($scheme, ['http', 'https'], true)) {
            return null;
        }

        // SSRF Protection: Block private/local addresses
        $host = parse_url($url, PHP_URL_HOST);
        if ($host && $this->isPrivateOrLocalHost($host)) {
            Log::warning('Blocked SSRF attempt to private/local address', ['url' => $url, 'host' => $host]);
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
     * MEDIUM-01 FIX: Extract MinIO path từ URL nếu match với configured endpoint
     */
    protected function extractMinioPath(string $url): ?string
    {
        $minioUrl = config('filesystems.disks.minio.url');
        $minioEndpoint = config('filesystems.disks.minio.endpoint');
        $bucket = config('filesystems.disks.minio.bucket');
        
        // Check if URL matches MinIO URL or endpoint
        foreach ([$minioUrl, $minioEndpoint] as $endpoint) {
            if (empty($endpoint)) continue;
            
            // Normalize endpoint
            $endpoint = rtrim($endpoint, '/');
            
            if (str_starts_with($url, $endpoint)) {
                // Extract path after endpoint
                $pathPart = substr($url, strlen($endpoint));
                $pathPart = ltrim($pathPart, '/');
                
                // Remove bucket prefix if present
                if (!empty($bucket) && str_starts_with($pathPart, $bucket . '/')) {
                    $pathPart = substr($pathPart, strlen($bucket) + 1);
                }
                
                return $pathPart;
            }
        }
        
        return null;
    }

    /**
     * MEDIUM-01 FIX: Đọc file từ MinIO storage và convert sang base64
     */
    protected function readMinioImageAsBase64(string $path): ?string
    {
        try {
            if (!\Storage::disk('minio')->exists($path)) {
                Log::warning('MinIO file not found', ['path' => $path]);
                return null;
            }

            $content = \Storage::disk('minio')->get($path);
            if (strlen($content) > $this->maxImageBytes) {
                Log::warning('MinIO image too large', ['path' => $path, 'bytes' => strlen($content)]);
                return null;
            }

            // Detect mime type
            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->buffer($content);
            
            if (!str_starts_with($mime, 'image/')) {
                Log::warning('MinIO file is not an image', ['path' => $path, 'mime' => $mime]);
                return null;
            }

            return 'data:' . $mime . ';base64,' . base64_encode($content);
        } catch (\Exception $e) {
            Log::warning('Failed to read MinIO image', ['path' => $path, 'error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Check if host is private or local (SSRF protection)
     * [FIX API-03] Enhanced to handle IP literals directly
     */
    protected function isPrivateOrLocalHost(string $host): bool
    {
        // Block localhost
        if (in_array(strtolower($host), ['localhost', '127.0.0.1', '::1', '0.0.0.0'], true)) {
            return true;
        }
        
        // [FIX API-03] Check if host is already an IP literal
        if (filter_var($host, FILTER_VALIDATE_IP)) {
            // It's an IP address, validate directly
            return filter_var(
                $host,
                FILTER_VALIDATE_IP,
                FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
            ) === false;
        }
        
        // Resolve hostname to IP and check if private
        $ip = gethostbyname($host);
        if ($ip === $host) {
            // Could not resolve, allow (might be external domain)
            return false;
        }
        
        // Check for private IP ranges
        return filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        ) === false;
    }

    /**
     * Lấy danh sách models có sẵn
     * 
     * @deprecated Use fetchImageModels() instead
     */
    public function getAvailableModels(): array
    {
        // DEPRECATED: Use fetchImageModels() to get real API data
        return config('services_custom.openrouter.models', []);
    }

    /**
     * Lấy danh sách aspect ratios hỗ trợ
     */
    public function getAspectRatios(): array
    {
        return config('services_custom.openrouter.aspect_ratios', []);
    }

    /**
     * Calculate estimated cost per image based on pricing structure
     * 
     * IMAGE GENERATION PRICING (OpenRouter 2026):
     * - Tính theo PER-IMAGE, KHÔNG phải theo tokens
     * - Field chính: pricing['image'] = cost per output image
     * - Ví dụ: FLUX ~$0.04/image, Gemini ~$0.134/image
     * 
     * @param array $pricing Pricing structure từ API
     * @return float|null Estimated cost in USD per image (null if unknown)
     */
    protected function calculateEstimatedCost(array $pricing): ?float
    {
        if (empty($pricing)) {
            return null;
        }

        // Image generation models use per-image pricing
        // Primary field: 'image' = cost per output image
        if (isset($pricing['image'])) {
            return round((float) $pricing['image'], 6);
        }

        // Fallback: Some models might use 'request' pricing
        if (isset($pricing['request'])) {
            return round((float) $pricing['request'], 6);
        }

        // If no image/request pricing, model might be free or pricing unknown
        return null;
    }
}

<?php

namespace App\Services;

use App\Models\Style;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * OpenRouterService
 * 
 * Xử lý việc gọi OpenRouter API để tạo ảnh AI.
 * Hỗ trợ các model: Gemini, Flux, etc.
 */
class OpenRouterService
{
    protected string $apiKey;
    protected string $baseUrl;
    protected int $timeout;

    public function __construct()
    {
        $this->apiKey = config('services_custom.openrouter.api_key');
        $this->baseUrl = config('services_custom.openrouter.base_url');
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
     * Tạo ảnh từ Style và options
     * 
     * @param Style $style Style đã chọn
     * @param array $selectedOptionIds Danh sách ID của options đã chọn
     * @param string|null $userCustomInput Nội dung user tự gõ
     * @return array ['success' => bool, 'image_base64' => string|null, 'openrouter_id' => string|null, 'error' => string|null]
     */
    public function generateImage(
        Style $style, 
        array $selectedOptionIds = [], 
        ?string $userCustomInput = null
    ): array {
        try {
            // Build final prompt
            $finalPrompt = $style->buildFinalPrompt($selectedOptionIds, $userCustomInput);
            
            // Build OpenRouter payload
            $payload = $style->buildOpenRouterPayload($finalPrompt);

            Log::info('OpenRouter request', [
                'model' => $style->openrouter_model_id,
                'prompt_length' => strlen($finalPrompt),
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

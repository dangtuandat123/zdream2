<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\Style;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * BflService
 *
 * Xử lý gọi Black Forest Labs (BFL) FLUX API để tạo ảnh.
 * API key lấy từ Settings (có cache + decrypt).
 */
class BflService
{
    protected string $apiKey;
    protected string $baseUrl;
    protected int $timeout;
    protected int $pollTimeout;
    protected int $maxImageBytes = 10485760; // default 10MB, override via config
    protected bool $verifySsl = true;
    protected int $maxPostAttempts = 3;

    public function __construct()
    {
        $fallbackApiKey = (string) config('services_custom.bfl.api_key', '');
        $fallbackBaseUrl = (string) config('services_custom.bfl.base_url', 'https://api.bfl.ai');

        try {
            $this->apiKey = (string) (Setting::get('bfl_api_key', $fallbackApiKey) ?? '');
            $baseUrl = (string) (Setting::get('bfl_base_url', $fallbackBaseUrl) ?? $fallbackBaseUrl);
        } catch (\Throwable $e) {
            Log::warning('BflService: cannot load DB settings, using config fallback', [
                'error' => $e->getMessage(),
            ]);
            $this->apiKey = $fallbackApiKey;
            $baseUrl = $fallbackBaseUrl;
        }

        $baseUrl = trim($baseUrl);
        $this->baseUrl = rtrim($baseUrl, '/');
        if ($this->baseUrl === '') {
            $this->baseUrl = 'https://api.bfl.ai';
        }
        // Normalize: nếu admin nhập kèm /v1 thì loại bỏ để tránh /v1/v1
        if (str_ends_with($this->baseUrl, '/v1')) {
            $this->baseUrl = substr($this->baseUrl, 0, -3);
        }

        $this->timeout = (int) config('services_custom.bfl.timeout', 120);
        $this->pollTimeout = (int) config('services_custom.bfl.poll_timeout', 120);
        $this->maxImageBytes = (int) config('services_custom.bfl.max_image_bytes', 26214400);
        $this->verifySsl = (bool) config('services_custom.bfl.verify_ssl', true);
        $this->maxPostAttempts = (int) config('services_custom.bfl.max_post_attempts', 3);
    }

    /**
     * HTTP client chuẩn cho BFL
     */
    protected function client(): PendingRequest
    {
        return Http::withHeaders([
            'x-key' => $this->apiKey,
            'accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->withOptions(['verify' => $this->verifySsl])
            ->timeout($this->timeout)
            ->connectTimeout(10)
            ->retry(2, 500, function (\Exception $exception) {
                if ($exception instanceof \Illuminate\Http\Client\ConnectionException) {
                    return true;
                }
                if ($exception instanceof \Illuminate\Http\Client\RequestException) {
                    $status = $exception->response->status();
                    return in_array($status, [429, 500, 502, 503, 504]);
                }
                return false;
            });
    }

    /**
     * HTTP client cho POST với retry/backoff khi 429/5xx
     */
    protected function clientForPost(): PendingRequest
    {
        return Http::withHeaders([
            'x-key' => $this->apiKey,
            'accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->withOptions(['verify' => $this->verifySsl])
            ->timeout($this->timeout)
            ->connectTimeout(15)
            ->retry($this->maxPostAttempts, function (int $attempt) {
                return min(1000 * pow(2, $attempt - 1), 4000);
            }, function (\Exception $exception) {
                if ($exception instanceof \Illuminate\Http\Client\ConnectionException) {
                    return true;
                }
                if ($exception instanceof \Illuminate\Http\Client\RequestException) {
                    $status = $exception->response->status();
                    return in_array($status, [429, 500, 502, 503, 504]);
                }
                return false;
            });
    }

    /**
     * Translate prompt to English using OpenRouter
     * Model and system prompt configurable via Settings
     */
    protected function translateToEnglish(string $prompt): string
    {
        Log::info('BflService: translateToEnglish called', ['prompt' => $prompt]);

        // Skip if already looks like English (basic check)
        if (preg_match('/^[a-zA-Z0-9\s\.,!?\-\'\"]+$/', $prompt)) {
            Log::info('BflService: Prompt looks like English, skipping translation');
            return $prompt;
        }

        $openRouterKey = Setting::get('openrouter_api_key', config('services_custom.openrouter.api_key', ''));
        if (empty($openRouterKey)) {
            Log::warning('BflService: OpenRouter API key not set, skipping translation');
            return $prompt;
        }

        Log::info('BflService: Will translate prompt');

        // Get model and system prompt from settings
        $model = Setting::get('translation_model', 'google/gemma-2-9b-it:free');
        // Default system prompt (Generic principles, no hardcoded examples)
        $defaultSystemPrompt = 'You are a professional translator for AI image generation.
Rules:
1. Translate accurately to English.
2. **Prioritize User Intent:** Strictly preserve specific details (colors, numbers, types) requested by the user.
3. **Enhance Quality:** For generic requests, assume high-quality, realistic, and detailed attributes.
4. **Clean Surfaces:** Keep displayable surfaces (screens, signs, paper) clean, blank, or abstract unless content is specified. Avoid random text/gibberish.
5. **Output:** Return ONLY the final English prompt.';
        $systemPrompt = Setting::get('translation_system_prompt', $defaultSystemPrompt);

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $openRouterKey,
                'Content-Type' => 'application/json',
            ])->withOptions(['verify' => $this->verifySsl])
                ->timeout(15)
                ->post('https://openrouter.ai/api/v1/chat/completions', [
                    'model' => $model,
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => $systemPrompt
                        ],
                        [
                            'role' => 'user',
                            'content' => $prompt
                        ]
                    ],
                    'max_tokens' => 150,
                    'temperature' => 0.3,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $translated = $data['choices'][0]['message']['content'] ?? $prompt;
                Log::info('BflService: Translation result', ['translated' => $translated]);
                return trim($translated);
            } else {
                Log::error('BflService: Translation failed', ['error' => $response->body()]);
                return $prompt;
            }
        } catch (\Exception $e) {
            Log::error('BflService: Translation exception', ['message' => $e->getMessage()]);
            return $prompt;
        }
    }

    /**
     * Magic Prompt Enhancer: Expands simple prompts into professional AI prompts
     */
    public function magicEnhancePrompt(string $prompt): string
    {
        Log::info('BflService: magicEnhancePrompt called', ['prompt' => $prompt]);

        $openRouterKey = Setting::get('openrouter_api_key', config('services_custom.openrouter.api_key', ''));
        if (empty($openRouterKey)) {
            return $prompt; // Fallback if no key
        }

        $model = Setting::get('translation_model', 'google/gemma-2-9b-it:free');

        $magicSystemPrompt = 'You are a Creative Director for AI Art.
Rules:
1.  **Analyze** the user\'s input (e.g., "cat", "city sunset").
2.  **Enhance** it into a highly detailed, professional text-to-image prompt (English).
3.  **Add Structure:** Subject + Action/Context + Lighting + Art Style + Quality Tags.
4.  **Example:** Input: "A soldier" -> Output: "Close-up portrait of a battle-hardened futuristic soldier, detailed cyberpunk armor, glowing blue neon accents, raining dark city street background, cinematic lighting, 8k resolution, photorealistic, masterpiece".
5.  **Constraint:** Keep it under 75 words. Return ONLY the enhanced prompt.';

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $openRouterKey,
                'Content-Type' => 'application/json',
            ])->withOptions(['verify' => $this->verifySsl])
                ->timeout(20)
                ->post('https://openrouter.ai/api/v1/chat/completions', [
                    'model' => $model,
                    'messages' => [
                        ['role' => 'system', 'content' => $magicSystemPrompt],
                        ['role' => 'user', 'content' => $prompt]
                    ]
                ]);

            if ($response->successful()) {
                $data = $response->json();
                return trim($data['choices'][0]['message']['content'] ?? $prompt);
            }
        } catch (\Exception $e) {
            Log::error('BflService: Magic Enhance exception', ['message' => $e->getMessage()]);
        }

        return $prompt; // Fail graceful
    }

    /**
     * Kiểm tra credits BFL
     */
    public function checkCredits(): array
    {
        if (empty($this->apiKey)) {
            return ['success' => false, 'error' => 'Thiếu BFL API key'];
        }

        try {
            $response = $this->client()->get($this->baseUrl . '/v1/credits');
            if (!$response->successful()) {
                return [
                    'success' => false,
                    'error' => 'Failed to check credits: ' . $response->status(),
                ];
            }

            $data = $response->json();
            return [
                'success' => true,
                'credits' => $data['credits'] ?? null,
            ];
        } catch (\Exception $e) {
            Log::error('BFL credits check exception', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function getAvailableModels(): array
    {
        return config('services_custom.bfl.models', []);
    }

    public function getAspectRatios(): array
    {
        return config('services_custom.bfl.aspect_ratios', []);
    }

    protected function getModelConfig(string $modelId): ?array
    {
        $modelId = $this->normalizeModelId($modelId);
        foreach ($this->getAvailableModels() as $model) {
            if (($model['id'] ?? '') === $modelId) {
                return $model;
            }
        }
        return null;
    }

    public function getModelCapabilities(string $modelId): array
    {
        $model = $this->getModelConfig($modelId) ?? [];
        return array_merge([
            'generation_mode' => 't2i',
            'supports_text_input' => true,
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
            'supports_mask' => false,
            'supports_blob_path' => false,
            'max_input_images' => 0,
            'uses_image_prompt' => false,
            'output_formats' => ['jpeg', 'png'],
            'safety_tolerance' => ['min' => 0, 'max' => 6, 'default' => 2],
            'steps' => null,
            'guidance' => null,
            'image_prompt_strength' => null,
            'prompt_upsampling_default' => false,
            'min_dimension' => (int) config('services_custom.bfl.min_dimension', 256),
            'max_dimension' => (int) config('services_custom.bfl.max_dimension', 1408),
            'dimension_multiple' => (int) config('services_custom.bfl.dimension_multiple', 32),
        ], $model);
    }

    protected function normalizeModelId(string $modelId): string
    {
        $modelId = trim($modelId);
        if ($modelId === '') {
            return $modelId;
        }

        $base = explode(':', $modelId, 2)[0];

        $map = [
            'black-forest-labs/flux.2-max' => 'flux-2-max',
            'black-forest-labs/flux.2-pro' => 'flux-2-pro',
            'black-forest-labs/flux.2-flex' => 'flux-2-flex',
            'black-forest-labs/flux.2-klein-4b' => 'flux-2-klein-4b',
            'black-forest-labs/flux.2-klein-9b' => 'flux-2-klein-9b',
            'black-forest-labs/flux-pro-1.0-fill' => 'flux-pro-1.0-fill',
            'black-forest-labs/flux-pro-1.0-fill-finetuned' => 'flux-pro-1.0-fill-finetuned',
            'black-forest-labs/flux-pro-1.0-expand' => 'flux-pro-1.0-expand',
            'black-forest-labs/flux-kontext-pro' => 'flux-kontext-pro',
            'black-forest-labs/flux-kontext-max' => 'flux-kontext-max',
            'black-forest-labs/flux-1.1-pro' => 'flux-pro-1.1',
            'black-forest-labs/flux-1.1-pro-ultra' => 'flux-pro-1.1-ultra',
            'black-forest-labs/flux-pro' => 'flux-pro',
            'black-forest-labs/flux-dev' => 'flux-dev',
            'flux-1.1-pro' => 'flux-pro-1.1',
            'flux-1.1-pro-ultra' => 'flux-pro-1.1-ultra',
            'flux-pro-1.0-fill' => 'flux-pro-1.0-fill',
            'flux-pro-1.0-fill-finetuned' => 'flux-pro-1.0-fill-finetuned',
            'flux-pro-1.0-expand' => 'flux-pro-1.0-expand',
            'flux.2-max' => 'flux-2-max',
            'flux.2-pro' => 'flux-2-pro',
            'flux.2-flex' => 'flux-2-flex',
            'flux.2-klein-4b' => 'flux-2-klein-4b',
            'flux.2-klein-9b' => 'flux-2-klein-9b',
        ];

        if (isset($map[$base])) {
            return $map[$base];
        }

        if (str_starts_with($base, 'black-forest-labs/')) {
            $base = substr($base, strlen('black-forest-labs/'));
        }

        return $base;
    }

    public function supportsImageInput(string $modelId): bool
    {
        $cap = $this->getModelCapabilities($modelId);
        return (bool) ($cap['supports_image_input'] ?? false);
    }

    public function supportsAspectRatio(string $modelId): bool
    {
        $cap = $this->getModelCapabilities($modelId);
        return (bool) ($cap['supports_aspect_ratio'] ?? false);
    }

    public function supportsWidthHeight(string $modelId): bool
    {
        $cap = $this->getModelCapabilities($modelId);
        return (bool) ($cap['supports_width_height'] ?? false);
    }

    public function maxInputImages(string $modelId): int
    {
        $cap = $this->getModelCapabilities($modelId);
        return (int) ($cap['max_input_images'] ?? 0);
    }

    public function usesImagePrompt(string $modelId): bool
    {
        $cap = $this->getModelCapabilities($modelId);
        return (bool) ($cap['uses_image_prompt'] ?? false);
    }

    public function supportsSteps(string $modelId): bool
    {
        $cap = $this->getModelCapabilities($modelId);
        return (bool) ($cap['supports_steps'] ?? false);
    }

    public function supportsGuidance(string $modelId): bool
    {
        $cap = $this->getModelCapabilities($modelId);
        return (bool) ($cap['supports_guidance'] ?? false);
    }

    public function supportsPromptUpsampling(string $modelId): bool
    {
        $cap = $this->getModelCapabilities($modelId);
        return (bool) ($cap['supports_prompt_upsampling'] ?? false);
    }

    public function supportsSeed(string $modelId): bool
    {
        $cap = $this->getModelCapabilities($modelId);
        return (bool) ($cap['supports_seed'] ?? false);
    }

    public function supportsOutputFormat(string $modelId): bool
    {
        $cap = $this->getModelCapabilities($modelId);
        return (bool) ($cap['supports_output_format'] ?? false);
    }

    public function supportsSafetyTolerance(string $modelId): bool
    {
        $cap = $this->getModelCapabilities($modelId);
        return (bool) ($cap['supports_safety_tolerance'] ?? false);
    }

    public function supportsRaw(string $modelId): bool
    {
        $cap = $this->getModelCapabilities($modelId);
        return (bool) ($cap['supports_raw'] ?? false);
    }

    public function supportsImagePromptStrength(string $modelId): bool
    {
        $cap = $this->getModelCapabilities($modelId);
        return (bool) ($cap['supports_image_prompt_strength'] ?? false);
    }

    public function supportsMask(string $modelId): bool
    {
        $cap = $this->getModelCapabilities($modelId);
        return (bool) ($cap['supports_mask'] ?? false);
    }

    public function supportsBlobPath(string $modelId): bool
    {
        $cap = $this->getModelCapabilities($modelId);
        return (bool) ($cap['supports_blob_path'] ?? false);
    }

    protected function isFillModel(string $modelId): bool
    {
        $cap = $this->getModelCapabilities($modelId);
        return (($cap['generation_mode'] ?? '') === 'fill');
    }

    protected function isExpandModel(string $modelId): bool
    {
        $cap = $this->getModelCapabilities($modelId);
        return (($cap['generation_mode'] ?? '') === 'expand');
    }

    public function getOutputFormats(string $modelId): array
    {
        $cap = $this->getModelCapabilities($modelId);
        return $cap['output_formats'] ?? ['jpeg', 'png'];
    }

    public function getSafetyToleranceRange(string $modelId): array
    {
        $cap = $this->getModelCapabilities($modelId);
        return $cap['safety_tolerance'] ?? ['min' => 0, 'max' => 6, 'default' => 2];
    }

    public function getStepsRange(string $modelId): ?array
    {
        $cap = $this->getModelCapabilities($modelId);
        return $cap['steps'] ?? null;
    }

    public function getGuidanceRange(string $modelId): ?array
    {
        $cap = $this->getModelCapabilities($modelId);
        return $cap['guidance'] ?? null;
    }

    public function getImagePromptStrengthRange(string $modelId): ?array
    {
        $cap = $this->getModelCapabilities($modelId);
        return $cap['image_prompt_strength'] ?? null;
    }

    protected function formatApiError(\Illuminate\Http\Client\Response $response): string
    {
        $status = $response->status();
        $body = $response->body();
        $truncatedBody = strlen($body) > 1000 ? substr($body, 0, 1000) . '...[TRUNCATED]' : $body;

        return match ($status) {
            400 => 'Yêu cầu không hợp lệ (400). ' . $truncatedBody,
            402 => 'BFL đã hết credits (402). Vui lòng nạp thêm credits.',
            403 => 'BFL API key không có quyền (403).',
            422 => 'Dữ liệu gửi lên không hợp lệ (422). ' . $truncatedBody,
            429 => 'BFL đang quá tải hoặc đạt giới hạn tác vụ (429). Vui lòng thử lại sau.',
            500, 503 => 'BFL đang gặp sự cố (' . $status . '). Vui lòng thử lại sau.',
            default => 'BFL API error: ' . $status . ' - ' . $truncatedBody,
        };
    }

    /**
     * Tạo ảnh từ Style và options
     *
     * @return array ['success' => bool, 'image_base64' => string|null, 'bfl_task_id' => string|null, 'error' => string|null]
     */
    public function generateImage(
        Style $style,
        array $selectedOptionIds = [],
        ?string $userCustomInput = null,
        ?string $aspectRatio = null,
        ?string $imageSize = null,
        array $inputImages = [],
        array $generationOverrides = []
    ): array {
        $finalPrompt = $style->buildFinalPrompt($selectedOptionIds, $userCustomInput);
        $rawModelId = $style->bfl_model_id ?? $style->openrouter_model_id ?? '';

        // Reject non-BFL model IDs that still contain external provider prefixes
        if (str_contains($rawModelId, '/') && !str_starts_with($rawModelId, 'black-forest-labs/')) {
            return [
                'success' => false,
                'error' => 'Model không hợp lệ cho BFL. Vui lòng chọn model FLUX.',
                'final_prompt' => $finalPrompt,
            ];
        }

        $modelId = $this->normalizeModelId($rawModelId);

        Log::debug('BFL generateImage input', [
            'style_id' => $style->id,
            'model_id' => $modelId,
            'finalPrompt_length' => mb_strlen($finalPrompt),
        ]);

        if (empty($this->apiKey)) {
            return [
                'success' => false,
                'error' => 'Thiếu BFL API key',
                'final_prompt' => $finalPrompt,
            ];
        }

        if (empty($modelId)) {
            return [
                'success' => false,
                'error' => 'Thiếu model BFL',
                'final_prompt' => $finalPrompt,
            ];
        }

        // Collect input images (user + system) with meta (label/description)
        $inputItems = $this->collectInputImagesWithMeta($style, $inputImages);
        $blobPath = $this->extractBlobPathFromItems($inputItems);
        if ($blobPath !== null && !$this->supportsBlobPath($modelId)) {
            Log::warning('Blob path provided but model does not support it, ignoring', [
                'model_id' => $modelId,
            ]);
            $blobPath = null;
        }
        $nonBlobItems = array_values(array_filter($inputItems, fn($item) => empty($item['is_blob'])));
        $normalizedImages = array_values(array_map(fn($item) => $item['value'], $nonBlobItems));
        $maxImages = $this->maxInputImages($modelId);

        if ($maxImages === 0 && !empty($normalizedImages)) {
            return [
                'success' => false,
                'error' => 'Model không hỗ trợ ảnh tham chiếu',
                'final_prompt' => $finalPrompt,
            ];
        }

        if ($maxImages > 0 && count($normalizedImages) > $maxImages) {
            Log::warning('BFL input images exceed limit, truncating', [
                'model_id' => $modelId,
                'count' => count($normalizedImages),
                'max' => $maxImages,
            ]);
            $nonBlobItems = array_slice($nonBlobItems, 0, $maxImages);
            $normalizedImages = array_values(array_map(fn($item) => $item['value'], $nonBlobItems));
        }

        // Append image descriptions to prompt AFTER truncation
        $finalPrompt = $this->appendImageDescriptionsFromMeta($finalPrompt, $nonBlobItems);

        if ($this->isFillModel($modelId)) {
            $fillInputs = $this->extractFillInputs($inputImages, $inputItems);
            if (empty($fillInputs['image'])) {
                return [
                    'success' => false,
                    'error' => 'Thiếu ảnh gốc để inpaint/outpaint.',
                    'final_prompt' => $finalPrompt,
                ];
            }
            $payload = $this->buildFillPayload(
                $finalPrompt,
                $modelId,
                $fillInputs['image'],
                $fillInputs['mask'] ?? null,
                $generationOverrides,
                $style->config_payload ?? []
            );
        } elseif ($this->isExpandModel($modelId)) {
            // Expand mode: extract image and expand directions
            $expandImage = $normalizedImages[0] ?? null;
            if (empty($expandImage)) {
                return [
                    'success' => false,
                    'error' => 'Thiếu ảnh gốc để expand/outpaint.',
                    'final_prompt' => $finalPrompt,
                ];
            }
            $expandDirections = [
                'top' => (int) ($generationOverrides['expand_top'] ?? 0),
                'bottom' => (int) ($generationOverrides['expand_bottom'] ?? 0),
                'left' => (int) ($generationOverrides['expand_left'] ?? 0),
                'right' => (int) ($generationOverrides['expand_right'] ?? 0),
            ];
            // Validate at least one direction has pixels
            if (array_sum($expandDirections) <= 0) {
                return [
                    'success' => false,
                    'error' => 'Cần chỉ định ít nhất một hướng expand (top, bottom, left, right).',
                    'final_prompt' => $finalPrompt,
                ];
            }
            $payload = $this->buildExpandPayload(
                $modelId,
                $expandImage,
                $expandDirections,
                $finalPrompt,
                $generationOverrides,
                $style->config_payload ?? []
            );
        } else {
            $payload = $this->buildPayload($style, $finalPrompt, $modelId, $aspectRatio, $imageSize, $normalizedImages, $generationOverrides, $blobPath);
        }

        try {
            $response = $this->clientForPost()->post($this->baseUrl . '/v1/' . $modelId, $payload);
            if (!$response->successful()) {
                return [
                    'success' => false,
                    'error' => $this->formatApiError($response),
                    'final_prompt' => $finalPrompt,
                ];
            }

            $data = $response->json();
            $taskId = $data['id'] ?? null;
            $pollingUrl = $data['polling_url'] ?? null;

            if (empty($taskId)) {
                return [
                    'success' => false,
                    'error' => 'Không nhận được task id từ BFL',
                    'final_prompt' => $finalPrompt,
                ];
            }

            $pollResult = $this->pollResult($taskId, $pollingUrl);
            if (!$pollResult['success']) {
                return [
                    'success' => false,
                    'error' => $pollResult['error'] ?? 'BFL polling error',
                    'final_prompt' => $finalPrompt,
                ];
            }

            return [
                'success' => true,
                'image_base64' => $pollResult['image_base64'] ?? null,
                'bfl_task_id' => $taskId,
                'final_prompt' => $finalPrompt,
            ];
        } catch (\Exception $e) {
            Log::error('BFL exception', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'final_prompt' => $finalPrompt,
            ];
        }
    }

    /**
     * Build payload theo model BFL
     */
    protected function buildPayload(
        Style $style,
        string $prompt,
        string $modelId,
        ?string $aspectRatio,
        ?string $imageSize,
        array $inputImages,
        array $generationOverrides = [],
        ?string $blobPath = null
    ): array {
        $payload = [
            'prompt' => $prompt,
        ];

        $config = $style->config_payload ?? [];
        $overrides = is_array($generationOverrides) ? $generationOverrides : [];
        $ratio = $overrides['aspect_ratio'] ?? $aspectRatio ?? ($config['aspect_ratio'] ?? null);
        $constraints = $this->getDimensionConstraints($modelId);

        $overrideWidth = $overrides['width'] ?? null;
        $overrideHeight = $overrides['height'] ?? null;
        $hasCustomDimensions = $this->supportsWidthHeight($modelId)
            && $overrideWidth !== null
            && $overrideHeight !== null;

        if ($this->supportsAspectRatio($modelId) && $ratio && !$hasCustomDimensions) {
            $payload['aspect_ratio'] = $ratio;
        } else {
            if ($this->supportsWidthHeight($modelId)) {
                if ($hasCustomDimensions) {
                    $minDim = (int) ($constraints['min'] ?? 256);
                    $maxDim = (int) ($constraints['max'] ?? 1408);
                    $width = (int) $overrideWidth;
                    $height = (int) $overrideHeight;

                    $width = max($minDim, min($width, $maxDim));
                    $height = max($minDim, min($height, $maxDim));

                    $multiple = (int) ($constraints['multiple'] ?? 1);
                    $multiple = $multiple > 0 ? $multiple : 1;
                    $width = $this->roundToMultiple($width, $multiple);
                    $height = $this->roundToMultiple($height, $multiple);

                    $payload['width'] = $width;
                    $payload['height'] = $height;
                } else {
                    $dimensions = $this->resolveDimensions($ratio, $imageSize, array_merge($config, $overrides), $constraints);
                    if ($dimensions) {
                        $payload['width'] = $dimensions['width'];
                        $payload['height'] = $dimensions['height'];
                    }
                }
            }
        }

        $allowedKeys = [
            'seed',
            'guidance',
            'steps',
            'safety_tolerance',
            'output_format',
            'prompt_upsampling',
            'raw',
            'image_prompt_strength',
        ];

        $supported = [
            'seed' => $this->supportsSeed($modelId),
            'guidance' => $this->supportsGuidance($modelId),
            'steps' => $this->supportsSteps($modelId),
            'safety_tolerance' => $this->supportsSafetyTolerance($modelId),
            'output_format' => $this->supportsOutputFormat($modelId),
            'prompt_upsampling' => $this->supportsPromptUpsampling($modelId),
            'raw' => $this->supportsRaw($modelId),
            'image_prompt_strength' => $this->supportsImagePromptStrength($modelId),
        ];

        foreach ($allowedKeys as $key) {
            if (!($supported[$key] ?? false)) {
                continue;
            }

            $value = null;
            if (array_key_exists($key, $overrides)) {
                $value = $overrides[$key];
            } elseif (array_key_exists($key, $config)) {
                $value = $config[$key];
            }

            if ($value === null) {
                continue;
            }

            if ($key === 'image_prompt_strength' && (empty($inputImages) || !$this->usesImagePrompt($modelId))) {
                continue;
            }

            $payload[$key] = $value;
        }

        if (!empty($inputImages) || $blobPath !== null) {
            if ($blobPath !== null && $this->supportsBlobPath($modelId)) {
                $payload['input_image_blob_path'] = $blobPath;
            }

            if ($this->usesImagePrompt($modelId)) {
                if (!empty($inputImages)) {
                    $payload['image_prompt'] = $inputImages[0];
                }
            } else {
                foreach ($inputImages as $index => $img) {
                    $fieldIndex = $index + 1;
                    $field = $fieldIndex === 1 ? 'input_image' : 'input_image_' . $fieldIndex;
                    $payload[$field] = $img;
                }
            }
        }

        return $payload;
    }

    /**
     * Build payload for FLUX Fill (inpaint/outpaint)
     */
    protected function buildFillPayload(
        string $prompt,
        string $modelId,
        string $image,
        ?string $mask,
        array $generationOverrides = [],
        array $config = []
    ): array {
        $payload = [
            'image' => $image,
            'prompt' => $prompt,
        ];

        if (!empty($mask)) {
            $payload['mask'] = $mask;
        }

        $allowedKeys = [
            'seed',
            'guidance',
            'steps',
            'safety_tolerance',
            'output_format',
            'prompt_upsampling',
        ];

        $supported = [
            'seed' => $this->supportsSeed($modelId),
            'guidance' => $this->supportsGuidance($modelId),
            'steps' => $this->supportsSteps($modelId),
            'safety_tolerance' => $this->supportsSafetyTolerance($modelId),
            'output_format' => $this->supportsOutputFormat($modelId),
            'prompt_upsampling' => $this->supportsPromptUpsampling($modelId),
        ];

        foreach ($allowedKeys as $key) {
            if (($supported[$key] ?? false) === false) {
                continue;
            }

            $value = null;
            if (array_key_exists($key, $generationOverrides)) {
                $value = $generationOverrides[$key];
            } elseif (array_key_exists($key, $config)) {
                $value = $config[$key];
            }

            if ($value === null) {
                continue;
            }

            $payload[$key] = $value;
        }

        return $payload;
    }

    /**
     * Build payload for FLUX Expand (outpainting)
     *
     * @param string $image Base64-encoded image
     * @param array $expandDirections Keys: top, bottom, left, right (pixels to expand)
     * @param string|null $prompt Optional context prompt
     * @param array $generationOverrides
     * @param array $config
     * @return array
     */
    protected function buildExpandPayload(
        string $modelId,
        string $image,
        array $expandDirections,
        ?string $prompt = null,
        array $generationOverrides = [],
        array $config = []
    ): array {
        $payload = [
            'image' => $image,
            'expand_top' => (int) ($expandDirections['top'] ?? 0),
            'expand_bottom' => (int) ($expandDirections['bottom'] ?? 0),
            'expand_left' => (int) ($expandDirections['left'] ?? 0),
            'expand_right' => (int) ($expandDirections['right'] ?? 0),
        ];

        if (!empty($prompt)) {
            $payload['prompt'] = $prompt;
        }

        $allowedKeys = [
            'seed',
            'guidance',
            'steps',
            'safety_tolerance',
            'output_format',
            'prompt_upsampling',
        ];

        $supported = [
            'seed' => $this->supportsSeed($modelId),
            'guidance' => $this->supportsGuidance($modelId),
            'steps' => $this->supportsSteps($modelId),
            'safety_tolerance' => $this->supportsSafetyTolerance($modelId),
            'output_format' => $this->supportsOutputFormat($modelId),
            'prompt_upsampling' => $this->supportsPromptUpsampling($modelId),
        ];

        foreach ($allowedKeys as $key) {
            if (($supported[$key] ?? false) === false) {
                continue;
            }

            $value = null;
            if (array_key_exists($key, $generationOverrides)) {
                $value = $generationOverrides[$key];
            } elseif (array_key_exists($key, $config)) {
                $value = $config[$key];
            }

            if ($value === null) {
                continue;
            }

            $payload[$key] = $value;
        }

        return $payload;
    }

    /**
     * Polling kết quả theo polling_url (ưu tiên) hoặc get_result
     */
    protected function pollResult(string $taskId, ?string $pollingUrl): array
    {
        $started = time();
        $pollInterval = 0.5;
        $maxInterval = 5.0;
        $attempts = 0;
        $pollUrl = $pollingUrl ?: ($this->baseUrl . '/v1/get_result');
        $maxExecution = (int) ini_get('max_execution_time');
        $pollTimeout = $this->pollTimeout;

        if ($maxExecution > 0) {
            // Keep a small buffer to avoid PHP fatal timeout
            $pollTimeout = min($pollTimeout, max(5, $maxExecution - 5));
        }

        while ((time() - $started) < $pollTimeout) {
            $attempts++;
            usleep((int) ($pollInterval * 1000000));

            try {
                if ($pollingUrl) {
                    if (str_contains($pollUrl, 'id=')) {
                        $response = $this->client()->get($pollUrl);
                    } else {
                        $response = $this->client()->get($pollUrl, ['id' => $taskId]);
                    }
                } else {
                    $response = $this->client()->get($pollUrl, ['id' => $taskId]);
                }

                if (!$response->successful()) {
                    if ($response->status() === 429) {
                        $pollInterval = min($maxInterval, $pollInterval * 1.5);
                        usleep(500000);
                    }
                    continue;
                }

                $data = $response->json();
                $status = $data['status'] ?? '';

                if ($status === 'Ready' || strtolower((string) $status) === 'completed') {
                    $imageBase64 = $this->extractImageFromResult($data);
                    if (!$imageBase64) {
                        return [
                            'success' => false,
                            'error' => 'BFL trả về kết quả nhưng không có ảnh',
                        ];
                    }
                    return [
                        'success' => true,
                        'image_base64' => $imageBase64,
                    ];
                }

                if (in_array($status, ['Error', 'Failed', 'Request Moderated', 'Content Moderated', 'Task not found'], true)) {
                    $message = match ($status) {
                        'Request Moderated' => 'Yêu cầu bị từ chối do nội dung không phù hợp (moderation).',
                        'Content Moderated' => 'Kết quả bị chặn do nội dung không phù hợp (moderation).',
                        'Task not found' => 'Không tìm thấy task (có thể đã hết hạn).',
                        'Failed' => 'Tác vụ thất bại khi xử lý.',
                        default => 'Lỗi khi xử lý ảnh.',
                    };
                    return [
                        'success' => false,
                        'error' => $message,
                    ];
                }

                // Gradual backoff to reduce API pressure
                if ($attempts % 10 === 0) {
                    $pollInterval = min($maxInterval, $pollInterval * 1.5);
                }
            } catch (\Exception $e) {
                Log::warning('BFL polling error', ['error' => $e->getMessage()]);
            }
        }

        return [
            'success' => false,
            'error' => 'Timeout khi chờ BFL trả kết quả',
        ];
    }

    /**
     * Extract image từ result (sample URL hoặc base64)
     */
    protected function extractImageFromResult(array $data): ?string
    {
        $result = $data['result'] ?? null;
        if (!is_array($result)) {
            $result = [];
        }

        $sample = $result['sample'] ?? null;
        $sampleUrl = $this->extractSampleUrl($sample);

        if (empty($sampleUrl) && isset($result['samples']) && is_array($result['samples'])) {
            $sampleUrl = $this->extractSampleUrl($result['samples'][0] ?? null);
        }

        if (empty($sampleUrl) && isset($result['image'])) {
            $sampleUrl = $this->extractSampleUrl($result['image']);
        }

        if (empty($sampleUrl) && isset($result['url'])) {
            $sampleUrl = $this->extractSampleUrl($result['url']);
        }

        if (empty($sampleUrl) && isset($result['image_url'])) {
            $sampleUrl = $this->extractSampleUrl($result['image_url']);
        }

        if (empty($sampleUrl) && isset($result['output'])) {
            $sampleUrl = $this->extractSampleUrl($result['output']);
        }

        if (empty($sampleUrl) && isset($result['outputs']) && is_array($result['outputs'])) {
            $sampleUrl = $this->extractSampleUrl($result['outputs'][0] ?? null);
        }

        if (empty($sampleUrl) && isset($data['preview']) && is_array($data['preview'])) {
            $sampleUrl = $this->extractSampleUrl($data['preview']['sample'] ?? ($data['preview']['url'] ?? null));
        }

        if (is_string($sampleUrl) && $sampleUrl !== '') {
            if (str_starts_with($sampleUrl, 'data:image/')) {
                return $sampleUrl;
            }
            if ($this->isBase64Image($sampleUrl)) {
                return 'data:image/png;base64,' . $sampleUrl;
            }
            if (filter_var($sampleUrl, FILTER_VALIDATE_URL)) {
                return $this->downloadImageAsBase64($sampleUrl);
            }
        }

        // Debug: log result keys when Ready but no image found
        Log::warning('BFL result missing sample image', [
            'result_keys' => array_keys($result),
            'result_preview' => substr(json_encode($result), 0, 800),
            'status' => $data['status'] ?? null,
            'has_preview' => isset($data['preview']),
        ]);

        return null;
    }

    /**
     * Extract sample URL from various shapes (string | array)
     */
    protected function extractSampleUrl($sample): ?string
    {
        if (is_string($sample)) {
            return $sample;
        }

        if (is_array($sample)) {
            // Common shapes: { url: "" } or { image_url: "" } or { image: "" }
            if (!empty($sample['url'])) {
                return $sample['url'];
            }
            if (!empty($sample['image_url'])) {
                return $sample['image_url'];
            }
            if (!empty($sample['image'])) {
                return $sample['image'];
            }

            // Array of strings or objects
            if (isset($sample[0])) {
                return $this->extractSampleUrl($sample[0]);
            }
        }

        return null;
    }

    /**
     * Append descriptions for input/system images to prompt
     */
    protected function appendImageDescriptionsFromMeta(string $prompt, array $items): string
    {
        $parts = [];

        foreach ($items as $item) {
            if (!empty($item['is_blob'])) {
                continue;
            }
            $desc = (string) ($item['description'] ?? '');
            $label = (string) ($item['label'] ?? '');
            $desc = trim($desc);
            $label = trim($label);
            if ($desc !== '') {
                $parts[] = $label !== '' ? "{$label}: {$desc}" : $desc;
            }
        }

        if (!empty($parts)) {
            $prompt .= "\n" . implode("\n", array_map(fn($p) => "[{$p}]", $parts));
        }

        return $prompt;
    }

    /**
     * Collect user input images + system images with meta
     * @return array<int, array{value: string, key: string, label: string, description: string, source: string}>
     */
    protected function collectInputImagesWithMeta(Style $style, array $inputImages): array
    {
        $items = [];
        $imageSlots = $style->image_slots ?? [];
        $slotMeta = collect($imageSlots)->keyBy('key')->toArray();
        $slotKeys = collect($imageSlots)->pluck('key')->all();

        if (isset($inputImages['blob_path'])) {
            $blobValue = $this->normalizeInputImage((string) $inputImages['blob_path']);
            if ($blobValue !== null && $this->isBlobPath($blobValue)) {
                $items[] = [
                    'value' => $blobValue,
                    'key' => 'blob_path',
                    'label' => 'Blob path',
                    'description' => '',
                    'source' => 'blob',
                    'is_blob' => true,
                ];
            }
        }

        foreach ($slotKeys as $key) {
            if (!isset($inputImages[$key])) {
                continue;
            }
            $normalized = $this->normalizeInputImage($inputImages[$key]);
            if ($normalized) {
                $meta = $slotMeta[$key] ?? [];
                $items[] = [
                    'value' => $normalized,
                    'key' => (string) $key,
                    'label' => (string) ($meta['label'] ?? $key),
                    'description' => (string) ($meta['description'] ?? ''),
                    'source' => 'user',
                    'is_blob' => $this->isBlobPath($normalized),
                ];
            }
        }

        // Add any remaining inputs not in slots
        foreach ($inputImages as $key => $value) {
            if (in_array($key, $slotKeys, true)) {
                continue;
            }
            $normalized = $this->normalizeInputImage($value);
            if ($normalized) {
                $items[] = [
                    'value' => $normalized,
                    'key' => (string) $key,
                    'label' => (string) $key,
                    'description' => '',
                    'source' => 'user',
                    'is_blob' => $this->isBlobPath($normalized),
                ];
            }
        }

        foreach ($style->system_images ?? [] as $index => $sysImg) {
            $blob = (string) ($sysImg['blob_path'] ?? '');
            $url = (string) ($sysImg['url'] ?? '');
            $value = $blob !== '' ? $blob : $url;
            if ($value === '') {
                continue;
            }
            $normalized = $this->normalizeInputImage($value);
            if ($normalized) {
                $items[] = [
                    'value' => $normalized,
                    'key' => (string) ($sysImg['key'] ?? ('system_' . $index)),
                    'label' => (string) ($sysImg['label'] ?? 'System Image'),
                    'description' => (string) ($sysImg['description'] ?? ''),
                    'source' => $blob !== '' ? 'blob' : 'system',
                    'is_blob' => $this->isBlobPath($normalized),
                ];
            }
        }

        return $items;
    }

    /**
     * Collect user input images + system images
     */
    protected function collectInputImages(Style $style, array $inputImages): array
    {
        $images = [];

        // Preserve order based on image_slots config
        $slotKeys = collect($style->image_slots ?? [])->pluck('key')->all();
        foreach ($slotKeys as $key) {
            if (isset($inputImages[$key])) {
                $normalized = $this->normalizeInputImage($inputImages[$key]);
                if ($normalized) {
                    $images[] = $normalized;
                }
            }
        }

        // Add any remaining inputs not in slots
        foreach ($inputImages as $key => $value) {
            if (!in_array($key, $slotKeys, true)) {
                $normalized = $this->normalizeInputImage($value);
                if ($normalized) {
                    $images[] = $normalized;
                }
            }
        }

        // Append system images after user inputs
        foreach ($style->system_images ?? [] as $sysImg) {
            $url = $sysImg['url'] ?? '';
            if ($url) {
                $normalized = $this->normalizeInputImage($url);
                if ($normalized) {
                    $images[] = $normalized;
                }
            }
        }

        return $images;
    }

    /**
     * Extract image + mask for fill models
     * @return array{image: string|null, mask: string|null}
     */
    protected function extractFillInputs(array $inputImages, array $items): array
    {
        $image = null;
        $mask = null;

        $priorityImageKeys = ['image', 'input_image', 'ref_1'];
        $priorityMaskKeys = ['mask', 'mask_image', 'ref_mask'];

        foreach ($priorityImageKeys as $key) {
            if (!isset($inputImages[$key])) {
                continue;
            }
            $normalized = $this->normalizeInputImage((string) $inputImages[$key]);
            if ($normalized && !$this->isBlobPath($normalized)) {
                $image = $normalized;
                break;
            }
        }

        foreach ($priorityMaskKeys as $key) {
            if (!isset($inputImages[$key])) {
                continue;
            }
            $normalized = $this->normalizeInputImage((string) $inputImages[$key]);
            if ($normalized && !$this->isBlobPath($normalized)) {
                $mask = $normalized;
                break;
            }
        }

        if ($image === null && !empty($items)) {
            foreach ($items as $item) {
                if (!empty($item['is_blob'])) {
                    continue;
                }
                $image = $item['value'] ?? null;
                if ($image) {
                    break;
                }
            }
        }

        if ($mask === null && count($items) > 1) {
            foreach ($items as $item) {
                if (!empty($item['is_blob'])) {
                    continue;
                }
                if ($item['value'] !== $image) {
                    $mask = $item['value'];
                    break;
                }
            }
        }

        return [
            'image' => $image,
            'mask' => $mask,
        ];
    }

    /**
     * Convert input image (data URL/base64/URL) to raw base64
     */
    protected function normalizeInputImage(string $value): ?string
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        $blobPath = $this->normalizeBlobPath($value);
        if ($blobPath !== null) {
            return 'blob:' . $blobPath;
        }

        if (str_starts_with($value, 'data:image/')) {
            return $this->stripDataUrlPrefix($value);
        }

        if (filter_var($value, FILTER_VALIDATE_URL)) {
            $dataUrl = $this->downloadImageAsBase64($value);
            return $dataUrl ? $this->stripDataUrlPrefix($dataUrl) : null;
        }

        if ($this->isBase64Image($value)) {
            return $value;
        }

        return null;
    }

    protected function isBlobPath(string $value): bool
    {
        return str_starts_with($value, 'blob:');
    }

    protected function normalizeBlobPath(string $value): ?string
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }
        if (str_starts_with($value, 'blob:')) {
            return substr($value, 5);
        }
        if (str_starts_with($value, 'blob://')) {
            return substr($value, 7);
        }
        if (str_starts_with($value, 'bfl://')) {
            return substr($value, 6);
        }
        return null;
    }

    protected function extractBlobPathFromItems(array $items): ?string
    {
        foreach ($items as $item) {
            $value = $item['value'] ?? null;
            if (is_string($value) && $this->isBlobPath($value)) {
                return substr($value, 5);
            }
        }
        return null;
    }

    protected function stripDataUrlPrefix(string $dataUrl): string
    {
        if (str_contains($dataUrl, ',')) {
            return explode(',', $dataUrl, 2)[1] ?? $dataUrl;
        }
        return $dataUrl;
    }

    /**
     * Resolve width/height theo aspect ratio + imageSize
     */
    protected function resolveDimensions(?string $aspectRatio, ?string $imageSize, array $config, array $constraints = []): ?array
    {
        if (!empty($config['width']) && !empty($config['height'])) {
            $minDim = (int) ($constraints['min'] ?? config('services_custom.bfl.min_dimension', 256));
            $maxDim = (int) ($constraints['max'] ?? config('services_custom.bfl.max_dimension', 1408));
            $multiple = (int) ($constraints['multiple'] ?? config('services_custom.bfl.dimension_multiple', 32));
            $multiple = $multiple > 0 ? $multiple : 1;

            $width = (int) $config['width'];
            $height = (int) $config['height'];

            $width = max($minDim, min($width, $maxDim));
            $height = max($minDim, min($height, $maxDim));
            $width = $this->roundToMultiple($width, $multiple);
            $height = $this->roundToMultiple($height, $multiple);

            return [
                'width' => $width,
                'height' => $height,
            ];
        }

        $ratio = $aspectRatio ?: '1:1';
        $ratioMap = config('services_custom.bfl.ratio_dimensions', []);
        $dimensions = $ratioMap[$ratio] ?? ($ratioMap['1:1'] ?? null);

        if (!$dimensions) {
            return null;
        }

        $width = (int) ($dimensions['width'] ?? 1024);
        $height = (int) ($dimensions['height'] ?? 1024);

        $scale = $this->resolveSizeScale($imageSize);
        if ($scale !== 1.0) {
            $width = (int) round($width * $scale);
            $height = (int) round($height * $scale);
        }

        $maxDim = (int) ($constraints['max'] ?? config('services_custom.bfl.max_dimension', 1408));
        $minDim = (int) ($constraints['min'] ?? config('services_custom.bfl.min_dimension', 256));

        $multiple = (int) ($constraints['multiple'] ?? config('services_custom.bfl.dimension_multiple', 32));
        $multiple = $multiple > 0 ? $multiple : 1;
        $width = $this->roundToMultiple($width, $multiple);
        $height = $this->roundToMultiple($height, $multiple);

        $width = max($minDim, min($width, $maxDim));
        $height = max($minDim, min($height, $maxDim));

        return [
            'width' => $width,
            'height' => $height,
        ];
    }

    protected function resolveSizeScale(?string $imageSize): float
    {
        return match (strtoupper((string) $imageSize)) {
            '2K' => 1.25,
            '4K' => 1.5,
            default => 1.0,
        };
    }

    protected function getDimensionConstraints(string $modelId): array
    {
        $cap = $this->getModelCapabilities($modelId);

        return [
            'min' => (int) ($cap['min_dimension'] ?? config('services_custom.bfl.min_dimension', 256)),
            'max' => (int) ($cap['max_dimension'] ?? config('services_custom.bfl.max_dimension', 1408)),
            'multiple' => (int) ($cap['dimension_multiple'] ?? config('services_custom.bfl.dimension_multiple', 32)),
        ];
    }

    protected function roundToMultiple(int $value, int $multiple): int
    {
        if ($multiple <= 0) {
            return $value;
        }
        return (int) (round($value / $multiple) * $multiple);
    }

    /**
     * Download ảnh từ URL và convert sang base64 data URL
     */
    protected function downloadImageAsBase64(string $url): ?string
    {
        if (str_starts_with($url, 'data:image/')) {
            return $url;
        }

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }

        $minioPath = $this->extractMinioPath($url);
        if ($minioPath !== null) {
            return $this->readMinioImageAsBase64($minioPath);
        }

        $scheme = parse_url($url, PHP_URL_SCHEME);
        if (!in_array($scheme, ['http', 'https'], true)) {
            return null;
        }

        $host = parse_url($url, PHP_URL_HOST);
        if ($host && $this->isPrivateOrLocalHost($host)) {
            Log::warning('Blocked SSRF attempt to private/local address', ['url' => $url, 'host' => $host]);
            return null;
        }

        try {
            $response = Http::withOptions(['verify' => $this->verifySsl])
                ->timeout(30)
                ->get($url);
            if (!$response->successful()) {
                Log::warning('Failed to download image (non-200)', [
                    'url' => $url,
                    'status' => $response->status(),
                ]);
                return null;
            }

            $contentType = $response->header('Content-Type') ?? '';
            $mime = strtolower(trim(explode(';', $contentType)[0]));

            if (!str_starts_with($mime, 'image/')) {
                Log::warning('Downloaded content is not image', [
                    'url' => $url,
                    'content_type' => $contentType,
                ]);
                return null;
            }

            $body = $response->body();
            if (strlen($body) > $this->maxImageBytes) {
                Log::warning('Downloaded image exceeds max bytes', [
                    'url' => $url,
                    'bytes' => strlen($body),
                    'max_bytes' => $this->maxImageBytes,
                ]);
                return null;
            }

            return 'data:' . $mime . ';base64,' . base64_encode($body);
        } catch (\Exception $e) {
            Log::warning('Failed to download image', ['url' => $url, 'error' => $e->getMessage()]);
        }

        return null;
    }

    protected function isBase64Image(string $data): bool
    {
        if (str_starts_with($data, 'data:image/')) {
            return true;
        }

        $decoded = base64_decode($data, true);
        return $decoded !== false && strlen($decoded) > 100;
    }

    protected function extractMinioPath(string $url): ?string
    {
        $minioUrl = config('filesystems.disks.minio.url');
        $minioEndpoint = config('filesystems.disks.minio.endpoint');
        $bucket = config('filesystems.disks.minio.bucket');

        foreach ([$minioUrl, $minioEndpoint] as $endpoint) {
            if (empty($endpoint)) {
                continue;
            }

            $endpoint = rtrim($endpoint, '/');
            if (str_starts_with($url, $endpoint)) {
                $pathPart = substr($url, strlen($endpoint));
                $pathPart = ltrim($pathPart, '/');

                if (!empty($bucket) && str_starts_with($pathPart, $bucket . '/')) {
                    $pathPart = substr($pathPart, strlen($bucket) + 1);
                }

                return $pathPart;
            }
        }

        return null;
    }

    protected function readMinioImageAsBase64(string $path): ?string
    {
        try {
            if (!Storage::disk('minio')->exists($path)) {
                return null;
            }

            $content = Storage::disk('minio')->get($path);
            if (strlen($content) > $this->maxImageBytes) {
                return null;
            }

            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->buffer($content);
            if (!str_starts_with($mime, 'image/')) {
                return null;
            }

            return 'data:' . $mime . ';base64,' . base64_encode($content);
        } catch (\Exception $e) {
            Log::warning('Failed to read MinIO image', ['path' => $path, 'error' => $e->getMessage()]);
            return null;
        }
    }

    protected function isPrivateOrLocalHost(string $host): bool
    {
        if (in_array(strtolower($host), ['localhost', '127.0.0.1', '::1', '0.0.0.0'], true)) {
            return true;
        }

        if (filter_var($host, FILTER_VALIDATE_IP)) {
            return filter_var(
                $host,
                FILTER_VALIDATE_IP,
                FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
            ) === false;
        }

        $ip = gethostbyname($host);
        if ($ip === $host) {
            return false;
        }

        return filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        ) === false;
    }
    // =============================================
    // DIRECT EDIT METHODS (Bypass Style System)
    // =============================================

    /**
     * Edit image with mask (Inpaint/Background Change)
     * Model configurable via Settings: edit_studio.model_replace
     */
    public function editWithMask(string $imageBase64, string $maskBase64, string $prompt, array $options = []): array
    {
        // Get model from settings, fallback to default
        $modelId = Setting::get('edit_studio.model_replace', 'flux-pro-1.0-fill');

        // Auto-translate Vietnamese to English for better AI understanding
        $translatedPrompt = $this->translateToEnglish($prompt);

        // Add prefix to guide AI - Force structural change
        $promptPrefix = Setting::get(
            'edit_studio.prompt_prefix_replace',
            'COMPLETELY REPLACE the masked area with this object (ignore original shape):'
        );
        $finalPrompt = trim($promptPrefix . ' ' . $translatedPrompt);

        $payload = [
            'image' => $imageBase64,
            'mask' => $maskBase64,
            'prompt' => $finalPrompt,
            'steps' => 50, // Increase steps for complex structural changes
            'guidance' => 60, // Strong guidance to force prompt adherence
            'prompt_upsampling' => false, // STRICTLY DISABLE: Prevents AI from "guessing" context (e.g. seeing a desk and forcing a laptop)
            'safety_tolerance' => 2,
            'output_format' => 'jpeg',
        ];

        // Add optional params
        if (isset($options['guidance']))
            $payload['guidance'] = $options['guidance'];
        if (isset($options['steps']))
            $payload['steps'] = $options['steps'];
        if (isset($options['safety_tolerance']))
            $payload['safety_tolerance'] = $options['safety_tolerance'];
        if (isset($options['output_format']))
            $payload['output_format'] = $options['output_format'];

        return $this->executeDirectGeneration($modelId, $payload);
    }

    /**
     * Edit background (Fill API with inverted mask)
     * Model configurable via Settings: edit_studio.model_background
     */
    public function editBackground(string $imageBase64, string $maskBase64, string $prompt, array $options = []): array
    {
        // Get model from settings (background uses same fill model by default)
        $modelId = Setting::get('edit_studio.model_background', 'flux-pro-1.0-fill');

        // Auto-translate Vietnamese to English for better AI understanding
        $translatedPrompt = $this->translateToEnglish($prompt);

        // Get prompt prefix from settings (Focus on subject preservation + realistic lighting match)
        $promptPrefix = Setting::get(
            'edit_studio.prompt_prefix_background',
            'Keep the main subject exactly as is. Change the background to a realistic environment that matches the subject lighting:'
        );
        $finalPrompt = trim($promptPrefix . ' ' . $translatedPrompt);

        $payload = [
            'image' => $imageBase64,
            'mask' => $maskBase64,
            'prompt' => $finalPrompt,
            'steps' => 28, // Faster generation
            'guidance' => 50, // Increased for better adherence to prompt description
            'prompt_upsampling' => true, // Helps AI understand prompt better
            'safety_tolerance' => 2,
            'output_format' => 'jpeg',
        ];

        // Add optional params
        if (isset($options['guidance']))
            $payload['guidance'] = $options['guidance'];
        if (isset($options['steps']))
            $payload['steps'] = $options['steps'];
        if (isset($options['safety_tolerance']))
            $payload['safety_tolerance'] = $options['safety_tolerance'];
        if (isset($options['output_format']))
            $payload['output_format'] = $options['output_format'];

        return $this->executeDirectGeneration($modelId, $payload);
    }

    /**
     * Edit text in image
     * Model configurable via Settings: edit_studio.model_text
     */
    public function editText(string $imageBase64, string $prompt, array $options = []): array
    {
        // Get model from settings, fallback to default
        $modelId = Setting::get('edit_studio.model_text', 'flux-kontext-pro');

        // Get prompt prefix from settings
        $promptPrefix = Setting::get('edit_studio.prompt_prefix_text', '');

        // NO TRANSLATION for text edit
        Log::info('EditText: Original prompt', ['prompt' => $prompt]);

        // Smart Case Handling: Automatically expand quoted text to cover case variations
        // Supports both straight quotes " and curly quotes “ ”
        $smartPrompt = preg_replace_callback('/["“”]([^"“”]+)["“”]/u', function ($matches) {
            $text = $matches[1];
            // Skip if text is too long or contains spaces (likely a sentence, not a keyword)
            if (strlen($text) > 20 || str_word_count($text) > 3) {
                return '"' . $text . '"';
            }

            $variants = array_unique([
                $text,                  // Original
                strtoupper($text),      // UPPERCASE
                strtolower($text),      // lowercase
                ucfirst(strtolower($text)) // Capitalized
            ]);

            // If variations exist, join them with OR
            if (count($variants) > 1) {
                $expanded = '(' . implode(' OR ', array_map(fn($v) => '"' . $v . '"', $variants)) . ')';
                Log::info('EditText: Expanded text', ['original' => $text, 'expanded' => $expanded]);
                return $expanded;
            }
            return '"' . $text . '"';
        }, $prompt);

        Log::info('EditText: Smart prompt', ['smartPrompt' => $smartPrompt]);

        $finalPrompt = trim($promptPrefix . ' ' . $smartPrompt);

        $payload = [
            'prompt' => $finalPrompt,
            'input_image' => $imageBase64,
        ];

        // Add optional params
        if (isset($options['guidance']))
            $payload['guidance'] = $options['guidance'];
        if (isset($options['steps']))
            $payload['steps'] = $options['steps'];
        if (isset($options['safety_tolerance']))
            $payload['safety_tolerance'] = $options['safety_tolerance'];
        if (isset($options['output_format']))
            $payload['output_format'] = $options['output_format'];

        return $this->executeDirectGeneration($modelId, $payload);
    }

    /**
     * Expand image (Outpainting)
     * Model configurable via Settings: edit_studio.model_expand
     */
    public function expandImage(string $imageBase64, array $expandDirections, string $prompt = null, array $options = []): array
    {
        // Get model from settings, fallback to default
        $modelId = Setting::get('edit_studio.model_expand', 'flux-pro-1.0-expand');

        // API uses 'top', 'bottom', 'left', 'right' (not 'expand_*')
        $payload = [
            'image' => $imageBase64,
            'top' => (int) ($expandDirections['top'] ?? 0),
            'bottom' => (int) ($expandDirections['bottom'] ?? 0),
            'left' => (int) ($expandDirections['left'] ?? 0),
            'right' => (int) ($expandDirections['right'] ?? 0),
            'steps' => 28, // Faster generation
            'guidance' => 60, // Strong guidance for seamless style matching
            'safety_tolerance' => 2, // API default
            'output_format' => 'jpeg',
        ];

        if (!empty($prompt)) {
            // Auto-translate Vietnamese to English
            $translatedPrompt = $this->translateToEnglish($prompt);
            // Get prompt prefix from settings (Focus on seamless continuation)
            $promptPrefix = Setting::get(
                'edit_studio.prompt_prefix_expand',
                'Seamlessly expand the image to match the original style, lighting, and details:'
            );
            $payload['prompt'] = trim($promptPrefix . ' ' . $translatedPrompt);
        }

        // Add optional params
        if (isset($options['guidance']))
            $payload['guidance'] = $options['guidance'];
        if (isset($options['steps']))
            $payload['steps'] = $options['steps'];
        if (isset($options['safety_tolerance']))
            $payload['safety_tolerance'] = $options['safety_tolerance'];
        if (isset($options['output_format']))
            $payload['output_format'] = $options['output_format'];

        return $this->executeDirectGeneration($modelId, $payload);
    }

    /**
     * Helper to execute API call and poll result
     */
    protected function executeDirectGeneration(string $modelId, array $payload): array
    {
        try {
            $response = $this->clientForPost()->post($this->baseUrl . '/v1/' . $modelId, $payload);

            if (!$response->successful()) {
                return [
                    'success' => false,
                    'error' => $this->formatApiError($response),
                ];
            }

            $data = $response->json();
            $taskId = $data['id'] ?? null;
            $pollingUrl = $data['polling_url'] ?? null;

            if (empty($taskId)) {
                return [
                    'success' => false,
                    'error' => 'Không nhận được task id từ BFL',
                ];
            }

            $pollResult = $this->pollResult($taskId, $pollingUrl);

            if ($pollResult['success']) {
                return [
                    'success' => true,
                    'image_base64' => $pollResult['image_base64'],
                    'bfl_task_id' => $taskId,
                ];
            }

            return [
                'success' => false,
                'error' => $pollResult['error'] ?? 'Lỗi không xác định khi polling',
            ];

        } catch (\Exception $e) {
            Log::error('BFL direct execution error', ['model' => $modelId, 'error' => $e->getMessage()]);
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}

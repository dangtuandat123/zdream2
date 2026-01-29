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

    public function __construct()
    {
        $this->apiKey = (string) (Setting::get('bfl_api_key', config('services_custom.bfl.api_key', '')) ?? '');
        $baseUrl = Setting::get('bfl_base_url', config('services_custom.bfl.base_url', 'https://api.bfl.ai'));
        $baseUrl = trim((string) $baseUrl);
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
          ->retry(2, 500);
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
          ->retry(3, function (int $attempt) {
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
            'max_input_images' => 0,
            'uses_image_prompt' => false,
            'output_formats' => ['jpeg', 'png'],
            'safety_tolerance' => ['min' => 0, 'max' => 6, 'default' => 2],
            'steps' => null,
            'guidance' => null,
            'image_prompt_strength' => null,
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
            'black-forest-labs/flux-kontext-pro' => 'flux-kontext-pro',
            'black-forest-labs/flux-kontext-max' => 'flux-kontext-max',
            'black-forest-labs/flux-1.1-pro' => 'flux-pro-1.1',
            'black-forest-labs/flux-1.1-pro-ultra' => 'flux-pro-1.1-ultra',
            'black-forest-labs/flux-pro' => 'flux-pro',
            'black-forest-labs/flux-dev' => 'flux-dev',
            'flux-1.1-pro' => 'flux-pro-1.1',
            'flux-1.1-pro-ultra' => 'flux-pro-1.1-ultra',
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
            500, 503 => 'BFL đang gặp sự cố ('.$status.'). Vui lòng thử lại sau.',
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

        // Append image descriptions to prompt
        $finalPrompt = $this->appendImageDescriptions($style, $finalPrompt, $inputImages);

        // Collect input images (user + system)
        $normalizedImages = $this->collectInputImages($style, $inputImages);
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
            $normalizedImages = array_slice($normalizedImages, 0, $maxImages);
        }

        $payload = $this->buildPayload($style, $finalPrompt, $modelId, $aspectRatio, $imageSize, $normalizedImages, $generationOverrides);

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
        array $generationOverrides = []
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

        if (!empty($inputImages)) {
            if ($this->usesImagePrompt($modelId)) {
                $payload['image_prompt'] = $inputImages[0];
            } else {
                foreach ($inputImages as $index => $img) {
                    $field = $index === 0 ? 'input_image' : 'input_image_' . ($index + 1);
                    $payload[$field] = $img;
                }
            }
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
    protected function appendImageDescriptions(Style $style, string $prompt, array $inputImages): string
    {
        $parts = [];

        $imageSlots = $style->image_slots ?? [];
        $slotMeta = collect($imageSlots)->keyBy('key')->toArray();

        foreach ($inputImages as $key => $value) {
            $meta = $slotMeta[$key] ?? [];
            $desc = $meta['description'] ?? '';
            $label = $meta['label'] ?? $key;
            if (!empty($desc)) {
                $parts[] = "{$label}: {$desc}";
            }
        }

        foreach ($style->system_images ?? [] as $sysImg) {
            $desc = $sysImg['description'] ?? '';
            $label = $sysImg['label'] ?? 'System Image';
            if (!empty($desc)) {
                $parts[] = "{$label}: {$desc}";
            }
        }

        if (!empty($parts)) {
            $prompt .= "\n" . implode("\n", array_map(fn ($p) => "[{$p}]", $parts));
        }

        return $prompt;
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
     * Convert input image (data URL/base64/URL) to raw base64
     */
    protected function normalizeInputImage(string $value): ?string
    {
        $value = trim($value);
        if ($value === '') {
            return null;
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
}

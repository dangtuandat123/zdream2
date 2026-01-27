<?php

namespace App\Livewire;

use App\Exceptions\InsufficientCreditsException;
use App\Jobs\GenerateImageJob;
use App\Models\GeneratedImage;
use App\Models\Style;
use App\Services\BflService;
use App\Services\StorageService;
use App\Services\WalletService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Livewire Component: ImageGenerator
 * 
 * Component chính để tạo ảnh AI.
 * Hỗ trợ cả sync và async mode (queue job).
 * 
 * Production-grade với full validation và error handling
 */
class ImageGenerator extends Component
{
    use WithFileUploads;
    
    // Style đang sử dụng
    public Style $style;
    
    // Selected option IDs (grouped by group_name)
    public array $selectedOptions = [];
    
    // User custom input (nếu được phép)
    public string $customInput = '';
    
    // Aspect Ratio đã chọn
    public string $selectedAspectRatio = '1:1';
    
    // Image Size đã chọn (chỉ Gemini)
    public string $selectedImageSize = '1K';
    
    // Các aspect ratios hỗ trợ (load từ BflService)
    public array $aspectRatios = [];
    
    // Các image sizes (chỉ Gemini models)
    public array $imageSizes = [
        '1K' => '1K (Chuẩn)',
        '2K' => '2K (Cao)',
        '4K' => '4K (Rất cao)',
    ];
    
    // Model có hỗ trợ image_size (legacy Gemini) không
    public bool $supportsImageConfig = false;
    
    // Model có hỗ trợ aspect_ratio trực tiếp không
    public bool $supportsAspectRatio = true;
    
    // Model có hỗ trợ ảnh tham chiếu (input_image) không
    public bool $supportsImageInput = false;

    // Model capabilities nâng cao
    public bool $supportsWidthHeight = false;
    public bool $supportsSeed = false;
    public bool $supportsSteps = false;
    public bool $supportsGuidance = false;
    public bool $supportsPromptUpsampling = false;
    public bool $supportsOutputFormat = false;
    public bool $supportsSafetyTolerance = false;
    public bool $supportsRaw = false;
    public bool $supportsImagePromptStrength = false;

    public int $maxInputImages = 0;
    public array $outputFormats = [];
    public array $stepsRange = [];
    public array $guidanceRange = [];
    public array $safetyToleranceRange = [];
    public array $imagePromptStrengthRange = [];
    public array $ratioDimensions = [];

    // Advanced settings (user override)
    public ?int $seed = null;
    public ?int $steps = null;
    public ?float $guidance = null;
    public ?bool $promptUpsampling = null;
    public ?int $safetyTolerance = null;
    public ?string $outputFormat = null;
    public ?bool $raw = null;
    public ?float $imagePromptStrength = null;
    public ?int $customWidth = null;
    public ?int $customHeight = null;
    public string $sizeMode = 'ratio';

    public int $dimensionMin = 256;
    public int $dimensionMax = 1408;
    public int $dimensionMultiple = 32;
    
    // State
    public bool $isGenerating = false;
    public ?string $generatedImageUrl = null;
    public ?string $errorMessage = null;
    
    // Uploaded images for img2img (key => file)
    public array $uploadedImages = [];
    public array $uploadedImagePreviews = [];
    
    // Last generated image
    public ?int $lastImageId = null;
    
    // Async mode: Use queue job for generation
    public bool $useAsyncMode = true;
    
    // Polling interval (ms) for async mode
    public int $pollingInterval = 2000;
    
    // Timeout cho async processing (phút) - sau đó sẽ refund
    private const PROCESSING_TIMEOUT_MINUTES = 5;

    /**
     * Mount component với Style
     */
    public function mount(Style $style): void
    {
        $this->style = $style;
        $this->style->loadMissing('options');
        
        // HIGH-02 FIX: Chỉ dùng async mode khi QUEUE_CONNECTION != 'sync'
        $this->useAsyncMode = config('queue.default') !== 'sync';
        
        // Load aspect ratios từ service (đồng bộ với config)
        $bflService = app(BflService::class);
        $modelId = $style->bfl_model_id ?? $style->openrouter_model_id ?? '';
        $this->aspectRatios = $bflService->getAspectRatios();
        $this->ratioDimensions = config('services_custom.bfl.ratio_dimensions', []);
        $this->dimensionMin = (int) config('services_custom.bfl.min_dimension', 256);
        $this->dimensionMax = (int) config('services_custom.bfl.max_dimension', 1408);
        $this->dimensionMultiple = (int) config('services_custom.bfl.dimension_multiple', 32);

        // BFL không dùng image_size như OpenRouter/Gemini
        $this->supportsImageConfig = false;
        $this->applyModelCapabilities($modelId);
        
        // Set default aspect ratio từ style config (với fallback)
        $this->selectedAspectRatio = $style->aspect_ratio ?? '1:1';

        // Load advanced defaults từ config_payload (nếu có)
        $this->applyDefaultAdvancedSettings();
        
        // Pre-select default options
        $this->preselectDefaultOptions();
    }

    /**
     * Pre-select default options khi mount hoặc reset
     */
    protected function preselectDefaultOptions(): void
    {
        $this->selectedOptions = [];
        foreach ($this->style->options as $option) {
            if ($option->is_default) {
                $this->selectedOptions[$option->group_name] = $option->id;
            }
        }
    }

    /**
     * Toggle chọn option (dạng single select per group)
     * NOTE: $optionId can be null for "Mặc định" (no effect) option
     */
    public function selectOption(string $groupName, ?int $optionId): void
    {
        // Handle "Mặc định" (null) case - remove from selection
        if ($optionId === null) {
            unset($this->selectedOptions[$groupName]);
            return;
        }

        // Validate optionId belongs to style
        $validIds = $this->style->options->pluck('id')->all();
        if (!in_array($optionId, $validIds, true)) {
            return;
        }

        // Nếu đã chọn rồi thì bỏ chọn (toggle off)
        if (isset($this->selectedOptions[$groupName]) && $this->selectedOptions[$groupName] === $optionId) {
            unset($this->selectedOptions[$groupName]);
        } else {
            $this->selectedOptions[$groupName] = $optionId;
        }
    }

    /**
     * Xử lý khi upload ảnh xong (với slot key)
     */
    public function updatedUploadedImages($value, $key): void
    {
        // Validate slot key exists
        $slotKeys = $this->getImageSlotKeys();
        if (!in_array($key, $slotKeys, true)) {
            unset($this->uploadedImages[$key]);
            return;
        }

        $this->validate([
            "uploadedImages.{$key}" => 'image|max:10240', // Max 10MB
        ]);

        if (isset($this->uploadedImages[$key]) && $this->uploadedImages[$key]) {
            try {
                $this->uploadedImagePreviews[$key] = $this->uploadedImages[$key]->temporaryUrl();
            } catch (\Exception $e) {
                Log::warning('Failed to get temporary URL', ['key' => $key, 'error' => $e->getMessage()]);
            }
        }
    }

    /**
     * Xóa ảnh đã upload theo key
     */
    public function removeUploadedImage(string $key): void
    {
        unset($this->uploadedImages[$key]);
        unset($this->uploadedImagePreviews[$key]);
    }

    /**
     * Convert all uploaded images to base64 array
     */
    protected function getUploadedImagesBase64(): array
    {
        $result = [];
        
        foreach ($this->uploadedImages as $key => $image) {
            if ($image && method_exists($image, 'getRealPath')) {
                try {
                    $realPath = $image->getRealPath();
                    if ($realPath && file_exists($realPath)) {
                        $contents = file_get_contents($realPath);
                        $mimeType = $image->getMimeType() ?? 'image/jpeg';
                        $result[$key] = "data:{$mimeType};base64," . base64_encode($contents);
                    }
                } catch (\Exception $e) {
                    Log::warning('Failed to encode image to base64', ['key' => $key, 'error' => $e->getMessage()]);
                }
            }
        }
        
        return $result;
    }

    /**
     * Lấy danh sách key của image slots
     */
    protected function getImageSlotKeys(): array
    {
        $imageSlots = $this->style->image_slots ?? [];

        return collect($imageSlots)
            ->pluck('key')
            ->filter(fn($key) => !empty($key))
            ->values()
            ->all();
    }

    /**
     * Apply model capabilities từ config/services_custom.php
     */
    protected function applyModelCapabilities(string $modelId): void
    {
        $bflService = app(BflService::class);
        $cap = $bflService->getModelCapabilities($modelId);

        $this->supportsAspectRatio = (bool) ($cap['supports_aspect_ratio'] ?? false);
        $this->supportsImageInput = (bool) ($cap['supports_image_input'] ?? false);
        $this->supportsWidthHeight = (bool) ($cap['supports_width_height'] ?? false);
        $this->supportsSeed = (bool) ($cap['supports_seed'] ?? false);
        $this->supportsSteps = (bool) ($cap['supports_steps'] ?? false);
        $this->supportsGuidance = (bool) ($cap['supports_guidance'] ?? false);
        $this->supportsPromptUpsampling = (bool) ($cap['supports_prompt_upsampling'] ?? false);
        $this->supportsOutputFormat = (bool) ($cap['supports_output_format'] ?? false);
        $this->supportsSafetyTolerance = (bool) ($cap['supports_safety_tolerance'] ?? false);
        $this->supportsRaw = (bool) ($cap['supports_raw'] ?? false);
        $this->supportsImagePromptStrength = (bool) ($cap['supports_image_prompt_strength'] ?? false);

        $this->maxInputImages = (int) ($cap['max_input_images'] ?? 0);
        $this->outputFormats = $cap['output_formats'] ?? ['jpeg', 'png'];
        $this->stepsRange = $cap['steps'] ?? [];
        $this->guidanceRange = $cap['guidance'] ?? [];
        $this->safetyToleranceRange = $cap['safety_tolerance'] ?? ['min' => 0, 'max' => 6, 'default' => 2];
        $this->imagePromptStrengthRange = $cap['image_prompt_strength'] ?? [];
    }

    /**
     * Load advanced defaults từ config_payload
     */
    protected function applyDefaultAdvancedSettings(): void
    {
        $config = $this->style->config_payload ?? [];

        $this->seed = $this->supportsSeed && array_key_exists('seed', $config)
            ? (int) $config['seed']
            : null;

        $this->steps = $this->supportsSteps
            ? (array_key_exists('steps', $config) ? (int) $config['steps'] : ($this->stepsRange['default'] ?? null))
            : null;

        $this->guidance = $this->supportsGuidance
            ? (array_key_exists('guidance', $config) ? (float) $config['guidance'] : ($this->guidanceRange['default'] ?? null))
            : null;

        $this->promptUpsampling = $this->supportsPromptUpsampling
            ? (array_key_exists('prompt_upsampling', $config) ? (bool) $config['prompt_upsampling'] : false)
            : null;

        $this->safetyTolerance = $this->supportsSafetyTolerance
            ? (array_key_exists('safety_tolerance', $config) ? (int) $config['safety_tolerance'] : ($this->safetyToleranceRange['default'] ?? null))
            : null;

        $this->outputFormat = $this->supportsOutputFormat
            ? ($config['output_format'] ?? ($this->outputFormats[0] ?? null))
            : null;

        $this->raw = $this->supportsRaw
            ? (array_key_exists('raw', $config) ? (bool) $config['raw'] : false)
            : null;

        $this->imagePromptStrength = $this->supportsImagePromptStrength
            ? (array_key_exists('image_prompt_strength', $config)
                ? (float) $config['image_prompt_strength']
                : ($this->imagePromptStrengthRange['default'] ?? null))
            : null;

        $this->customWidth = $this->supportsWidthHeight && array_key_exists('width', $config)
            ? (int) $config['width']
            : null;

        $this->customHeight = $this->supportsWidthHeight && array_key_exists('height', $config)
            ? (int) $config['height']
            : null;

        if (!$this->supportsWidthHeight) {
            $this->sizeMode = 'ratio';
        } else {
            $this->sizeMode = ($this->customWidth !== null && $this->customHeight !== null) ? 'custom' : 'ratio';
        }
    }

    /**
     * Build overrides payload từ UI advanced settings
     */
    protected function buildGenerationOverrides(): array
    {
        $overrides = [];

        if ($this->supportsSeed && $this->seed !== null) {
            $overrides['seed'] = (int) $this->seed;
        }
        if ($this->supportsSteps && $this->steps !== null) {
            $overrides['steps'] = (int) $this->steps;
        }
        if ($this->supportsGuidance && $this->guidance !== null) {
            $overrides['guidance'] = (float) $this->guidance;
        }
        if ($this->supportsPromptUpsampling && $this->promptUpsampling !== null) {
            $overrides['prompt_upsampling'] = (bool) $this->promptUpsampling;
        }
        if ($this->supportsSafetyTolerance && $this->safetyTolerance !== null) {
            $overrides['safety_tolerance'] = (int) $this->safetyTolerance;
        }
        if ($this->supportsOutputFormat && $this->outputFormat) {
            $overrides['output_format'] = $this->outputFormat;
        }
        if ($this->supportsRaw && $this->raw !== null) {
            $overrides['raw'] = (bool) $this->raw;
        }
        if ($this->supportsImagePromptStrength && $this->imagePromptStrength !== null) {
            $overrides['image_prompt_strength'] = (float) $this->imagePromptStrength;
        }
        if ($this->supportsWidthHeight && $this->sizeMode === 'custom' && $this->customWidth !== null && $this->customHeight !== null) {
            $overrides['width'] = (int) $this->customWidth;
            $overrides['height'] = (int) $this->customHeight;
        }

        return $overrides;
    }

    /**
     * Build generation params for persistence
     */
    protected function buildGenerationParams(array $overrides): array
    {
        $params = [
            'model_id' => $this->style->bfl_model_id ?? $this->style->openrouter_model_id,
            'aspect_ratio' => $this->selectedAspectRatio,
        ];

        if ($this->supportsImageConfig) {
            $params['image_size'] = $this->selectedImageSize;
        }

        $params = array_merge($params, $overrides);

        return array_filter($params, fn ($value) => !($value === null || $value === ''));
    }

    /**
     * Randomize seed để user có thể thử nhiều lần
     */
    public function randomizeSeed(): void
    {
        $this->seed = random_int(1, 2147483647);
    }

    /**
     * Clear seed (auto/random)
     */
    public function clearSeed(): void
    {
        $this->seed = null;
    }

    /**
     * Generate image - MAIN FLOW
     */
    public function generate(): void
    {
        // Prevent double-click
        if ($this->isGenerating) {
            return;
        }

        $this->resetState();

        $user = Auth::user();
        
        if (!$user) {
            $this->errorMessage = 'Vui lòng đăng nhập để tạo ảnh.';
            return;
        }

        // Kiểm tra đủ credits
        if (!$user->hasEnoughCredits($this->style->price)) {
            $this->errorMessage = "Bạn không đủ credits. Cần: {$this->style->price}, Hiện có: {$user->credits}";
            return;
        }

        // [BUG FIX] Kiểm tra style còn active không (có thể bị disable khi tab đang mở)
        $this->style->refresh(); // Refresh từ DB
        if (!$this->style->is_active) {
            $this->errorMessage = 'Style này đã bị tắt. Vui lòng chọn style khác.';
            return;
        }

        // [BUG FIX P3-04] Kiểm tra model có hỗ trợ image generation không
        // Sử dụng cache để tránh gọi API mỗi request
        try {
            $imageCapableModels = cache()->remember('image_capable_model_ids', 3600, function () {
                $modelManager = app(\App\Services\ModelManager::class);
                return collect($modelManager->fetchModels())->pluck('id')->toArray();
            });
            
            /* 
            // DISABLED: Allow custom models manually entered by admin
            $modelId = $this->style->bfl_model_id ?? $this->style->openrouter_model_id;
            if (!empty($imageCapableModels) && !in_array($modelId, $imageCapableModels)) {
                $this->errorMessage = 'Model AI của style này không còn hỗ trợ. Vui lòng liên hệ admin.';
                Log::error('Style uses non-image-capable model', [
                    'style_id' => $this->style->id,
                    'model_id' => $modelId,
                ]);
                return;
            }
            */
        } catch (\Exception $e) {
            // [BUG FIX P3-05] Fallback: nếu API fail, cho phép generate nhưng log warning
            Log::warning('ModelManager validation skipped due to API error', [
                'error' => $e->getMessage(),
                'model_id' => $this->style->bfl_model_id ?? $this->style->openrouter_model_id,
            ]);
        }

        // Validate all inputs
        if (!$this->validateGenerationInputs()) {
            return;
        }

        // Validate required images
        if (!$this->validateRequiredImages()) {
            return;
        }

        $this->isGenerating = true;
        $generatedImage = null;
        $creditsDeducted = false;
        $walletService = app(WalletService::class);

        try {
            $generationOverrides = $this->buildGenerationOverrides();
            $generationParams = $this->buildGenerationParams($generationOverrides);

            // Lấy danh sách option IDs đã chọn
            $selectedOptionIds = array_values($this->selectedOptions);

            // Tạo record GeneratedImage trước (trạng thái processing)
            $generatedImage = GeneratedImage::create([
                'user_id' => $user->id,
                'style_id' => $this->style->id,
                'final_prompt' => '', // Sẽ cập nhật sau
                'selected_options' => $selectedOptionIds,
                'user_custom_input' => $this->customInput ?: null,
                'generation_params' => $generationParams,
                'status' => GeneratedImage::STATUS_PROCESSING,
                'credits_used' => $this->style->price,
            ]);

            // Trừ credits
            $walletService->deductCredits(
                $user,
                $this->style->price,
                "Tạo ảnh Style: {$this->style->name}",
                'generation',
                (string) $generatedImage->id
            );
            $creditsDeducted = true;

            // Lấy base64 images trước khi dispatch (vì UploadedFile không serialize được)
            $inputImagesBase64 = $this->getUploadedImagesBase64();

            // ASYNC MODE: Dispatch job và bắt đầu polling
            if ($this->useAsyncMode) {
                GenerateImageJob::dispatch(
                    $generatedImage,
                    $selectedOptionIds,
                    $this->customInput ?: null,
                    $this->selectedAspectRatio,
                    $this->selectedImageSize,
                    $inputImagesBase64,
                    $generationOverrides
                );

                $this->lastImageId = $generatedImage->id;
                
                Log::info('Image generation job dispatched', [
                    'image_id' => $generatedImage->id,
                    'user_id' => $user->id,
                ]);

                // Không set isGenerating = false, để UI biết đang chờ
                // Component sẽ dùng polling hoặc Livewire event để cập nhật
                return;
            }

            // SYNC MODE: Gọi trực tiếp (legacy, for testing)
            $bflService = app(BflService::class);
            
            $result = $bflService->generateImage(
                $this->style,
                $selectedOptionIds,
                $this->customInput ?: null,
                $this->selectedAspectRatio,
                $this->selectedImageSize,
                $inputImagesBase64,
                $generationOverrides
            );

            if (!$result['success']) {
                $error = $result['error'] ?? 'BFL error';

                // Hoàn tiền nếu API thất bại
                $refunded = $this->handleRefund($walletService, $user, $creditsDeducted, $error, $generatedImage);

                $generatedImage->markAsFailed($error);

                $this->errorMessage = $refunded
                    ? 'Có lỗi khi tạo ảnh. Credits đã được hoàn lại.'
                    : 'Có lỗi khi tạo ảnh. Vui lòng liên hệ hỗ trợ để được hoàn tiền.';
                return;
            }

            // Cập nhật final prompt
            $generatedImage->update(['final_prompt' => $result['final_prompt'] ?? '']);

            // Lưu ảnh vào MinIO
            $storageService = app(StorageService::class);
            $storageResult = $storageService->saveBase64Image(
                $result['image_base64'],
                $user->id
            );

            if (!$storageResult['success']) {
                $storageError = $storageResult['error'] ?? 'Unknown storage error';

                $refunded = $this->handleRefund($walletService, $user, $creditsDeducted, 'Storage error: ' . $storageError, $generatedImage);

                $generatedImage->markAsFailed('Storage error: ' . $storageError);

                $this->errorMessage = $refunded
                    ? 'Có lỗi khi lưu ảnh. Credits đã được hoàn lại.'
                    : 'Có lỗi khi lưu ảnh. Vui lòng liên hệ hỗ trợ để được hoàn tiền.';
                return;
            }
            // Đánh dấu hoàn thành
            $generatedImage->markAsCompleted(
                $storageResult['path'],
                $result['bfl_task_id'] ?? null
            );

            // FIX: Dùng presigned URL từ accessor (giống History) thay vì public URL
            $generatedImage->refresh(); // Refresh để lấy storage_path mới
            $this->generatedImageUrl = $generatedImage->image_url;
            $this->lastImageId = $generatedImage->id;

            // Dispatch event để các component khác (history) có thể refresh
            $this->dispatch('imageGenerated');

            Log::info('Image generated successfully (sync)', [
                'user_id' => $user->id,
                'style_id' => $this->style->id,
                'image_id' => $generatedImage->id,
            ]);

        } catch (InsufficientCreditsException $e) {
            if ($generatedImage) {
                $generatedImage->markAsFailed('Không đủ credits');
            }
            $this->errorMessage = 'Bạn không đủ credits để tạo ảnh.';
            
        } catch (\Throwable $e) {
            $refunded = $this->handleRefund(
                $walletService,
                $user,
                $creditsDeducted,
                'Lỗi hệ thống: ' . $e->getMessage(),
                $generatedImage
            );

            if ($generatedImage) {
                $generatedImage->markAsFailed('Lỗi hệ thống: ' . $e->getMessage());
            }

            Log::error('Image generation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $user->id ?? null,
                'style_id' => $this->style->id ?? null,
                'generated_image_id' => $generatedImage?->id,
            ]);

            $refundMsg = $refunded
                ? ' Credits đã được hoàn lại.'
                : ' Vui lòng liên hệ hỗ trợ để được hoàn tiền.';

            $this->errorMessage = config('app.debug')
                ? 'Có lỗi xảy ra: ' . $e->getMessage() . $refundMsg
                : 'Có lỗi xảy ra trong quá trình tạo ảnh.' . $refundMsg;
                
        } finally {
            // Chỉ tắt isGenerating nếu SYNC mode
            if (!$this->useAsyncMode) {
                $this->isGenerating = false;
            }
        }
    }

    /**
     * Poll image status (called by frontend via wire:poll)
     * Release session lock để tránh blocking các requests khác
     */
    public function pollImageStatus(): void
    {
        // Release session lock ngay lập tức để không block các requests khác
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }

        if (!$this->lastImageId) {
            return;
        }

        $image = GeneratedImage::find($this->lastImageId);
        
        if (!$image) {
            $this->isGenerating = false;
            $this->errorMessage = 'Không tìm thấy ảnh.';
            $this->lastImageId = null;
            return;
        }

        // IMG-02 FIX: Security - Check ownership để tránh user xem ảnh của user khác
        $currentUserId = Auth::id();
        if ($image->user_id !== $currentUserId) {
            Log::warning('Unauthorized image access attempt', [
                'image_id' => $image->id,
                'image_owner' => $image->user_id,
                'requester' => $currentUserId,
            ]);
            $this->isGenerating = false;
            $this->errorMessage = 'Không có quyền truy cập ảnh này.';
            $this->lastImageId = null;
            return;
        }

        // Check status
        if ($image->status === GeneratedImage::STATUS_COMPLETED) {
            $this->isGenerating = false;
            $this->generatedImageUrl = $image->image_url;
            
        } elseif ($image->status === GeneratedImage::STATUS_FAILED) {
            $this->isGenerating = false;
            // [FIX IMG-05] Hiển thị lỗi cụ thể thay vì chung chung
            $this->errorMessage = $image->error_message 
                ? 'Lỗi: ' . $image->error_message . '. Credits đã được hoàn lại.'
                : 'Tạo ảnh thất bại. Credits đã được hoàn lại.';
            
        } elseif ($image->status === GeneratedImage::STATUS_PROCESSING) {
            // HIGH-02 FIX: Watchdog - kiểm tra timeout
            $processingMinutes = now()->diffInMinutes($image->created_at);
            
            if ($processingMinutes >= self::PROCESSING_TIMEOUT_MINUTES) {
                Log::warning('Watchdog: Job timeout detected', [
                    'image_id' => $image->id,
                    'processing_minutes' => $processingMinutes,
                ]);
                
                // Mark as failed và refund credits
                $image->markAsFailed('Timeout: Job không hoàn thành sau ' . self::PROCESSING_TIMEOUT_MINUTES . ' phút');
                
                // Refund credits - [BUG FIX P4-02] Check nếu đã refund trước đó
                $user = $image->user;
                if ($user && $image->credits_used > 0) {
                    // Check if already refunded
                    $alreadyRefunded = \App\Models\WalletTransaction::where('source', 'refund')
                        ->where('reference_id', (string) $image->id)
                        ->exists();
                    
                    if (!$alreadyRefunded) {
                        try {
                            $walletService = app(WalletService::class);
                            $walletService->refundCredits(
                                $user,
                                $image->credits_used,
                                'Watchdog timeout refund',
                                (string) $image->id
                            );
                        } catch (\Throwable $e) {
                            Log::error('Watchdog: Failed to refund credits', [
                                'image_id' => $image->id,
                                'error' => $e->getMessage(),
                            ]);
                        }
                    } else {
                        Log::info('Watchdog: Refund already processed, skipping', [
                            'image_id' => $image->id,
                        ]);
                    }
                }
                
                $this->isGenerating = false;
                $this->errorMessage = 'Xử lý quá lâu. Credits đã được hoàn lại. Vui lòng thử lại.';
            }
            // Else: still processing, keep polling
        }
    }

    /**
     * Validate tất cả inputs trước khi generate
     */
    protected function validateGenerationInputs(): bool
    {
        // 1. Validate customInput length (O5: tránh vượt context/cost)
        if (mb_strlen($this->customInput) > 500) {
            $this->errorMessage = 'Mô tả bổ sung không được vượt quá 500 ký tự.';
            return false;
        }

        // 2. Sanitize customInput - remove HTML tags only (keep special chars for AI prompt)
        $this->customInput = strip_tags($this->customInput);

        // 3. Validate aspect ratio
        $ratioKeys = array_keys($this->aspectRatios);
        if (!empty($ratioKeys) && !in_array($this->selectedAspectRatio, $ratioKeys, true)) {
            $this->errorMessage = 'Tỉ lệ khung hình không hợp lệ. Vui lòng chọn lại.';
            return false;
        }

        // 4. Validate image size (Gemini only)
        if ($this->supportsImageConfig) {
            if (!array_key_exists($this->selectedImageSize, $this->imageSizes)) {
                $this->errorMessage = 'Chất lượng ảnh không hợp lệ. Vui lòng chọn lại.';
                return false;
            }
        } else {
            // Không gửi image_size đối với model không hỗ trợ
            $this->selectedImageSize = '1K';
        }

        // 4b. Validate advanced settings (theo capability)
        if (!$this->supportsSeed) {
            $this->seed = null;
        } elseif ($this->seed !== null && $this->seed < 0) {
            $this->errorMessage = 'Seed không hợp lệ. Vui lòng nhập số nguyên >= 0.';
            return false;
        }

        if (!$this->supportsSteps) {
            $this->steps = null;
        } elseif ($this->steps !== null) {
            $min = (int) ($this->stepsRange['min'] ?? 1);
            $max = (int) ($this->stepsRange['max'] ?? 100);
            if ($this->steps < $min || $this->steps > $max) {
                $this->errorMessage = "Steps phải trong khoảng {$min}–{$max}.";
                return false;
            }
        }

        if (!$this->supportsGuidance) {
            $this->guidance = null;
        } elseif ($this->guidance !== null) {
            $min = (float) ($this->guidanceRange['min'] ?? 1.0);
            $max = (float) ($this->guidanceRange['max'] ?? 10.0);
            if ($this->guidance < $min || $this->guidance > $max) {
                $this->errorMessage = "Guidance phải trong khoảng {$min}–{$max}.";
                return false;
            }
        }

        if (!$this->supportsPromptUpsampling) {
            $this->promptUpsampling = null;
        }

        if (!$this->supportsSafetyTolerance) {
            $this->safetyTolerance = null;
        } elseif ($this->safetyTolerance !== null) {
            $min = (int) ($this->safetyToleranceRange['min'] ?? 0);
            $max = (int) ($this->safetyToleranceRange['max'] ?? 6);
            if ($this->safetyTolerance < $min || $this->safetyTolerance > $max) {
                $this->errorMessage = "Safety tolerance phải trong khoảng {$min}–{$max}.";
                return false;
            }
        }

        if (!$this->supportsOutputFormat) {
            $this->outputFormat = null;
        } elseif ($this->outputFormat !== null) {
            if (!in_array($this->outputFormat, $this->outputFormats, true)) {
                $this->errorMessage = 'Định dạng ảnh không hợp lệ.';
                return false;
            }
        }

        if (!$this->supportsRaw) {
            $this->raw = null;
        }

        if (!$this->supportsImagePromptStrength) {
            $this->imagePromptStrength = null;
        } elseif ($this->imagePromptStrength !== null) {
            $min = (float) ($this->imagePromptStrengthRange['min'] ?? 0);
            $max = (float) ($this->imagePromptStrengthRange['max'] ?? 1);
            if ($this->imagePromptStrength < $min || $this->imagePromptStrength > $max) {
                $this->errorMessage = "Image prompt strength phải trong khoảng {$min}–{$max}.";
                return false;
            }
        }

        

        if (!$this->supportsWidthHeight) {
            $this->customWidth = null;
            $this->customHeight = null;
            $this->sizeMode = 'ratio';
        } elseif ($this->sizeMode === 'custom') {
            if ($this->customWidth !== null || $this->customHeight !== null) {
                if ($this->customWidth === null || $this->customHeight === null) {
                    $this->errorMessage = 'Vui lòng nhập đủ các thông số Width và Height.';
                    return false;
                }

                $min = $this->dimensionMin;
                $max = $this->dimensionMax;
                $multiple = max(1, $this->dimensionMultiple);

                if ($this->customWidth < $min || $this->customWidth > $max) {
                    $this->errorMessage = "Width phải trong khoảng {$min}–{$max}.";
                    return false;
                }
                if ($this->customHeight < $min || $this->customHeight > $max) {
                    $this->errorMessage = "Height phải trong khoảng {$min}–{$max}.";
                    return false;
                }
                if (($this->customWidth % $multiple) !== 0 || ($this->customHeight % $multiple) !== 0) {
                    $this->errorMessage = "Width/Height phải là bội số của {$multiple}.";
                    return false;
                }
            }
        } else {
            // Ratio mode: ignore custom size
            $this->customWidth = null;
            $this->customHeight = null;
        }

        // 5. Validate selected options thuộc style hiện tại
        $validOptionIds = $this->style->options->pluck('id')->all();
        foreach ($this->selectedOptions as $optionId) {
            if (!in_array($optionId, $validOptionIds, true)) {
                $this->errorMessage = 'Tùy chọn không hợp lệ. Vui lòng tải lại trang.';
                return false;
            }
        }

        // 6. Validate uploaded image keys
        $slotKeys = $this->getImageSlotKeys();
        $uploadedKeys = array_keys($this->uploadedImages);
        if (!empty($uploadedKeys)) {
            $unknownKeys = array_diff($uploadedKeys, $slotKeys);
            if (!empty($unknownKeys)) {
                $this->errorMessage = 'Ảnh tải lên không hợp lệ. Vui lòng thử lại.';
                return false;
            }

            // Re-validate files server-side
            $rules = [];
            foreach ($uploadedKeys as $key) {
                $rules["uploadedImages.{$key}"] = 'image|max:10240';
            }
            try {
                $this->validate($rules);
            } catch (\Illuminate\Validation\ValidationException $e) {
                $this->errorMessage = 'Ảnh tải lên không hợp lệ. Vui lòng chọn file ảnh (JPEG, PNG, GIF, WebP).';
                return false;
            }
        }

        // 7. Validate total payload size (max 25MB for all images combined)
        // BFL/Providers often have payload limits (e.g. 10MB-20MB)
        $totalSize = 0;
        foreach ($this->uploadedImages as $image) {
            if ($image && method_exists($image, 'getSize')) {
                $totalSize += $image->getSize();
            }
        }
        
        // 25MB limit (conservative)
        if ($totalSize > 25 * 1024 * 1024) {
            $this->errorMessage = 'Tổng dung lượng ảnh tải lên quá lớn (Max 25MB). Vui lòng giảm dung lượng hoặc số lượng ảnh.';
            return false;
        }

        return true;
    }

    /**
     * Validate required images from slots
     */
    protected function validateRequiredImages(): bool
    {
        $imageSlots = $this->style->image_slots ?? [];

        // Nếu model không hỗ trợ ảnh tham chiếu nhưng style bắt buộc ảnh hoặc có system images
        if (!$this->supportsImageInput) {
            $hasRequiredSlot = collect($imageSlots)->contains(fn ($slot) => !empty($slot['required']));
            $hasUploaded = !empty($this->uploadedImages);
            $hasSystemImages = !empty($this->style->system_images);
            if ($hasRequiredSlot || $hasUploaded || $hasSystemImages) {
                $this->errorMessage = 'Style này yêu cầu ảnh tham chiếu nhưng model hiện tại không hỗ trợ.';
                return false;
            }
        } else {
            $uploadedCount = count(array_filter($this->uploadedImages));
            $systemCount = count($this->style->system_images ?? []);
            $totalImages = $uploadedCount + $systemCount;
            if ($this->maxInputImages > 0 && $totalImages > $this->maxInputImages) {
                $this->errorMessage = "Model hiện tại chỉ hỗ trợ tối đa {$this->maxInputImages} ảnh tham chiếu. Vui lòng giảm số lượng ảnh.";
                return false;
            }
        }
        
        foreach ($imageSlots as $slot) {
            $slotKey = $slot['key'] ?? '';
            $isRequired = $slot['required'] ?? false;
            $slotLabel = $slot['label'] ?? 'Ảnh';
            
            if ($isRequired && empty($this->uploadedImages[$slotKey])) {
                $this->errorMessage = "Vui lòng upload ảnh: {$slotLabel}";
                return false;
            }
        }

        return true;
    }

    /**
     * Handle refund safely
     */
    protected function handleRefund(
        WalletService $walletService,
        $user,
        bool $creditsDeducted,
        string $reason,
        ?GeneratedImage $generatedImage = null
    ): bool {
        if (!$creditsDeducted) {
            return false;
        }

        try {
            $walletService->refundCredits(
                $user,
                $this->style->price,
                $reason,
                $generatedImage?->id ? (string) $generatedImage->id : null
            );
            return true;
        } catch (\Throwable $e) {
            Log::error('Refund credits failed', [
                'error' => $e->getMessage(),
                'user_id' => $user->id ?? null,
                'generated_image_id' => $generatedImage?->id,
                'amount' => $this->style->price,
            ]);
            return false;
        }
    }

    /**
     * Reset internal state trước khi tạo ảnh mới
     */
    protected function resetState(): void
    {
        $this->generatedImageUrl = null;
        $this->errorMessage = null;
        $this->lastImageId = null;
    }

    /**
     * Close modal but keep form inputs (để user có thể chỉnh sửa và tạo lại)
     */
    public function closeModal(): void
    {
        $this->resetState();
    }

    /**
     * Reset form để tạo ảnh mới (public - từ nút "Tạo lại")
     */
    public function resetForm(): void
    {
        $this->resetState();
        $this->customInput = '';
        $this->uploadedImages = [];
        $this->uploadedImagePreviews = [];
        $this->selectedAspectRatio = $this->style->aspect_ratio ?? '1:1';
        $this->selectedImageSize = '1K';
        $this->applyDefaultAdvancedSettings();
        
        // Re-select defaults
        $this->preselectDefaultOptions();
    }

    /**
     * Render component
     */
    public function render()
    {
        // Query user's images với style này - reactive, update khi component re-render
        $userStyleImages = Auth::check()
            ? GeneratedImage::where('user_id', Auth::id())
                ->where('style_id', $this->style->id)
                ->completed()
                ->latest()
                ->limit(6)
                ->get()
            : collect();

        return view('livewire.image-generator', [
            'optionGroups' => $this->style->options->groupBy('group_name'),
            'user' => Auth::user(),
            'userStyleImages' => $userStyleImages,
        ]);
    }
}

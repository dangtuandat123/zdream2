<?php

namespace App\Livewire;

use App\Jobs\EditImageJob;
use App\Models\GeneratedImage;
use App\Models\Setting;
use App\Services\BflService;
use App\Services\WalletService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Livewire Component: ImageEditStudio
 * 
 * Cho phép user edit ảnh bằng cách:
 * - Vẽ vùng mask (brush/rectangle)
 * - Chọn edit mode (replace/text/background/expand)
 * - Mô tả thay đổi
 * - Gọi BFL API (Fill/Kontext/Expand) via Queue
 */
class ImageEditStudio extends Component
{
    use WithFileUploads;

    // =============================================
    // COMPUTED PROPERTIES
    // =============================================

    public function getPlaceholderTextProperty(): string
    {
        return match ($this->editMode) {
            'replace' => 'VD: Thay bằng một chiếc xe thể thao màu đỏ',
            'text' => 'VD: Giữ nguyên font chữ, màu sắc, kích thước',
            'background' => 'VD: Thay nền bằng bãi biển lúc hoàng hôn',
            'expand' => 'VD: Tiếp tục cảnh biển với bầu trời xanh',
            default => 'Mô tả những gì bạn muốn thay đổi',
        };
    }

    // =============================================
    // DIRECT EDIT METHODS (Bypass Style System)
    // =============================================

    // =============================================
    // PUBLIC PROPERTIES (Synced với Blade)
    // =============================================

    /** @var \Livewire\Features\SupportFileUploads\TemporaryUploadedFile|null */
    public $uploadedImage;

    /** Source image as base64 data URI */
    public string $sourceImage = '';

    /** Mask data từ canvas (base64 PNG) */
    public string $maskData = '';

    /** Prompt mô tả thay đổi */
    public string $editPrompt = '';

    /** Edit mode: replace, text, background, expand */
    public string $editMode = 'replace';

    /** Expand directions (pixels) */
    public array $expandDirections = [
        'top' => 0,
        'bottom' => 0,
        'left' => 0,
        'right' => 0,
    ];

    /** Trạng thái đang xử lý (job đang chạy) */
    public bool $isProcessing = false;

    /** ID của GeneratedImage đang xử lý (để polling) */
    public ?int $processingImageId = null;

    /** Kết quả sau edit (base64 hoặc URL) */
    public string $resultImage = '';

    /** Error message */
    public string $errorMessage = '';

    /** Success message */
    public string $successMessage = '';

    /** Original image dimensions */
    public int $imageWidth = 0;
    public int $imageHeight = 0;

    /** Pricing per mode (loaded from settings) */
    public array $modePrices = [
        'replace' => 5,
        'text' => 4,
        'background' => 5,
        'expand' => 5,
    ];

    /** User's current balance */
    public float $userCredits = 0;

    /** Last generated image ID (for continue editing) */
    public ?int $lastGeneratedImageId = null;

    /** Text replacements for text mode [['from' => 'old', 'to' => 'new'], ...] */
    public array $textReplacements = [
        ['from' => '', 'to' => ''],
    ];

    // =============================================
    // PROTECTED PROPERTIES
    // =============================================

    protected BflService $bflService;

    // =============================================
    // VALIDATION RULES
    // =============================================

    protected function rules(): array
    {
        // editPrompt is optional for text mode (uses textReplacements) and expand mode
        $promptRule = in_array($this->editMode, ['text', 'expand'])
            ? 'nullable|string|max:1000'
            : 'required|string|min:3|max:1000';

        return [
            'uploadedImage' => 'nullable|image|max:10240', // 10MB max
            'editPrompt' => $promptRule,
            'editMode' => 'required|in:replace,text,background,expand',
            'expandDirections.top' => 'integer|min:0|max:1024',
            'expandDirections.bottom' => 'integer|min:0|max:1024',
            'expandDirections.left' => 'integer|min:0|max:1024',
            'expandDirections.right' => 'integer|min:0|max:1024',
        ];
    }

    protected function messages(): array
    {
        return [
            'uploadedImage.image' => 'File phải là ảnh hợp lệ.',
            'uploadedImage.max' => 'Ảnh không được vượt quá 10MB.',
            'editPrompt.required' => 'Vui lòng mô tả thay đổi bạn muốn.',
            'editPrompt.min' => 'Mô tả phải có ít nhất 3 ký tự.',
        ];
    }

    // =============================================
    // LIFECYCLE
    // =============================================

    public function boot(BflService $bflService): void
    {
        $this->bflService = $bflService;
    }

    public function mount(): void
    {
        // Load prices from settings
        $this->modePrices = [
            'replace' => (float) Setting::get('edit_studio.credit_cost_replace', 5),
            'text' => (float) Setting::get('edit_studio.credit_cost_text', 4),
            'background' => (float) Setting::get('edit_studio.credit_cost_background', 5),
            'expand' => (float) Setting::get('edit_studio.credit_cost_expand', 5),
        ];

        // Load user credits
        $user = Auth::user();
        $this->userCredits = $user ? (float) $user->credits : 0;
    }

    /**
     * Get current mode's price
     */
    public function getCurrentPriceProperty(): float
    {
        return $this->modePrices[$this->editMode] ?? 5;
    }

    /**
     * Check if user has enough credits for current mode
     */
    public function getHasEnoughCreditsProperty(): bool
    {
        return $this->userCredits >= $this->currentPrice;
    }

    // =============================================
    // FILE UPLOAD HANDLERS
    // =============================================

    /**
     * Xử lý khi user upload ảnh mới
     */
    public function updatedUploadedImage(): void
    {
        $this->validateOnly('uploadedImage');

        if ($this->uploadedImage) {
            try {
                // Read image and convert to base64
                $imageContent = file_get_contents($this->uploadedImage->getRealPath());
                $mimeType = $this->uploadedImage->getMimeType();
                $this->sourceImage = 'data:' . $mimeType . ';base64,' . base64_encode($imageContent);

                // Get dimensions
                $imageInfo = getimagesize($this->uploadedImage->getRealPath());
                if ($imageInfo) {
                    $this->imageWidth = $imageInfo[0];
                    $this->imageHeight = $imageInfo[1];
                }

                // Clear previous results
                $this->resultImage = '';
                $this->maskData = '';
                $this->errorMessage = '';
                $this->successMessage = '';

                // Dispatch event to init canvas
                $this->dispatch('image-loaded', [
                    'src' => $this->sourceImage,
                    'width' => $this->imageWidth,
                    'height' => $this->imageHeight,
                ]);

            } catch (\Exception $e) {
                Log::error('ImageEditStudio: Failed to process uploaded image', [
                    'error' => $e->getMessage(),
                ]);
                $this->errorMessage = 'Không thể xử lý ảnh. Vui lòng thử lại.';
            }
        }
    }

    // =============================================
    // MASK HANDLERS (Called from JavaScript)
    // =============================================

    /**
     * Nhận mask data từ JavaScript canvas
     */
    public function setMaskData(string $maskBase64): void
    {
        $this->maskData = $maskBase64;
    }

    /**
     * Clear mask
     */
    public function clearMask(): void
    {
        $this->maskData = '';
        $this->dispatch('clear-canvas-mask');
    }

    // =============================================
    // EDIT MODE HANDLERS
    // =============================================

    /**
     * Thay đổi edit mode
     */
    public function setEditMode(string $mode): void
    {
        if (in_array($mode, ['replace', 'text', 'background', 'expand'])) {
            $this->editMode = $mode;
        }
    }

    /**
     * Magic Prompt Enhancer
     */
    public function magicEnhance(): void
    {
        if (empty(trim($this->editPrompt))) {
            $this->dispatch(
                'notify',
                type: 'error',
                message: 'Vui lòng nhập từ khóa trước khi dùng Đũa thần!'
            );
            return;
        }

        $this->isProcessing = true; // Show loading spinner

        try {
            // Call service to enhance prompt
            $enhanced = $this->bflService->magicEnhancePrompt($this->editPrompt);

            // Update prompt with enhanced version
            $this->editPrompt = $enhanced;

            $this->dispatch(
                'notify',
                type: 'success',
                message: 'Đã phù phép prompt thành công! ✨'
            );
        } catch (\Exception $e) {
            $this->dispatch(
                'notify',
                type: 'error',
                message: 'Có lỗi xảy ra: ' . $e->getMessage()
            );
        }

        $this->isProcessing = false;
    }

    /**
     * Set expand directions preset
     */
    public function setExpandPreset(string $preset): void
    {
        $this->expandDirections = match ($preset) {
            'vertical' => ['top' => 256, 'bottom' => 256, 'left' => 0, 'right' => 0],
            'horizontal' => ['top' => 0, 'bottom' => 0, 'left' => 256, 'right' => 256],
            'all' => ['top' => 128, 'bottom' => 128, 'left' => 128, 'right' => 128],
            'reset' => ['top' => 0, 'bottom' => 0, 'left' => 0, 'right' => 0],
            default => $this->expandDirections,
        };
    }

    /**
     * Add a new text replacement pair
     */
    public function addTextReplacement(): void
    {
        $this->textReplacements[] = ['from' => '', 'to' => ''];
    }

    /**
     * Remove a text replacement pair
     */
    public function removeTextReplacement(int $index): void
    {
        if (count($this->textReplacements) > 1) {
            unset($this->textReplacements[$index]);
            $this->textReplacements = array_values($this->textReplacements);
        }
    }

    /**
     * Build prompt from text replacements
     */
    protected function buildTextPrompt(): string
    {
        $parts = [];
        foreach ($this->textReplacements as $pair) {
            $from = trim($pair['from'] ?? '');
            $to = trim($pair['to'] ?? '');
            if (!empty($from) && !empty($to)) {
                // Use API recommended format: Replace 'X' with 'Y'
                $parts[] = "Replace '{$from}' with '{$to}'";
            }
        }

        if (empty($parts)) {
            return $this->editPrompt; // fallback to manual prompt
        }

        $prompt = implode('. ', $parts);

        // Append additional instructions if provided, otherwise use default
        if (!empty($this->editPrompt)) {
            $prompt .= '. ' . $this->editPrompt;
        } else {
            // Default: preserve original text style
            $prompt .= '. Keep the same font, color, size, style and effects as the original text';
        }

        return $prompt;
    }

    // =============================================
    // MAIN EDIT FLOW
    // =============================================

    /**
     * Process edit - Main entry point (Dispatch to Queue)
     */
    public function processEdit(): void
    {
        // Validate
        $this->validate();

        // Check source image
        if (empty($this->sourceImage)) {
            $this->errorMessage = 'Vui lòng upload ảnh trước.';
            return;
        }

        // Mode-specific validation
        if ($this->editMode === 'expand') {
            $totalExpand = array_sum($this->expandDirections);
            if ($totalExpand <= 0) {
                $this->errorMessage = 'Vui lòng chọn ít nhất một hướng expand.';
                return;
            }
        } elseif ($this->editMode !== 'text' && empty($this->maskData)) {
            $this->errorMessage = 'Vui lòng vẽ vùng muốn chỉnh sửa.';
            return;
        }

        // Check user authentication
        $user = Auth::user();
        if (!$user) {
            $this->errorMessage = 'Vui lòng đăng nhập để sử dụng tính năng này.';
            return;
        }

        // Get credit cost for current mode
        $creditCost = $this->modePrices[$this->editMode] ?? 5;

        // Check if user has enough credits
        if (!$user->hasEnoughCredits($creditCost)) {
            $this->errorMessage = "Bạn không đủ Xu. Cần: {$creditCost} Xu, Hiện có: {$user->credits} Xu";
            return;
        }

        $this->errorMessage = '';
        $this->resultImage = '';

        try {
            // Get mode label
            $modeLabels = [
                'replace' => 'Replace Object',
                'text' => 'Text Edit',
                'background' => 'Background Change',
                'expand' => 'Expand Image',
            ];
            $modeLabel = $modeLabels[$this->editMode] ?? 'Edit';

            // Deduct credits BEFORE dispatching job
            $walletService = app(WalletService::class);
            $walletService->deductCredits(
                $user,
                $creditCost,
                "Edit Studio: {$modeLabel}",
                'edit_studio'
            );

            // Update local credits display
            $user->refresh();
            $this->userCredits = (float) $user->credits;

            // Build final prompt based on mode
            $finalPrompt = $this->editMode === 'text'
                ? $this->buildTextPrompt()
                : $this->editPrompt;

            // Create GeneratedImage record (processing status)
            $generatedImage = GeneratedImage::create([
                'user_id' => $user->id,
                'style_id' => null, // Edit Studio không dùng style
                'final_prompt' => $finalPrompt,
                'user_custom_input' => $this->editPrompt,
                'generation_params' => [
                    'mode' => $this->editMode,
                    'mode_label' => $modeLabel,
                    'source' => 'edit_studio',
                    'expand_directions' => $this->expandDirections,
                    'text_replacements' => $this->editMode === 'text' ? $this->textReplacements : null,
                ],
                'status' => GeneratedImage::STATUS_PROCESSING,
                'credits_used' => $creditCost,
            ]);

            // Dispatch job
            EditImageJob::dispatch(
                $generatedImage,
                $this->editMode,
                $this->sourceImage,
                $this->maskData,
                $finalPrompt, // Use built prompt
                $this->expandDirections
            );

            // Set processing state for polling
            $this->isProcessing = true;
            $this->processingImageId = $generatedImage->id;

            Log::info('ImageEditStudio: Job dispatched', [
                'image_id' => $generatedImage->id,
                'user_id' => $user->id,
                'mode' => $this->editMode,
            ]);

        } catch (\Exception $e) {
            Log::error('ImageEditStudio: Failed to dispatch job', [
                'mode' => $this->editMode,
                'error' => $e->getMessage(),
            ]);
            $this->errorMessage = 'Có lỗi xảy ra: ' . $e->getMessage();
        }
    }

    /**
     * Poll for job status (called by wire:poll)
     */
    public function pollStatus(): void
    {
        if (!$this->processingImageId) {
            $this->isProcessing = false;
            return;
        }

        $image = GeneratedImage::find($this->processingImageId);
        if (!$image) {
            $this->isProcessing = false;
            $this->processingImageId = null;
            $this->errorMessage = 'Không tìm thấy task xử lý.';
            return;
        }

        if ($image->status === GeneratedImage::STATUS_COMPLETED) {
            // Success!
            $this->isProcessing = false;
            $this->processingImageId = null;
            $this->resultImage = $image->image_url;
            $this->lastGeneratedImageId = $image->id;
            $this->successMessage = "Chỉnh sửa thành công! Đã trừ {$image->credits_used} Xu.";

            // Auto-replace source with result for continuous editing
            $this->sourceImage = $image->image_url;
            $this->maskData = ''; // Clear mask
            $this->dispatch('image-loaded', src: $image->image_url);
            $this->dispatch('clear-canvas-mask');

            Log::info('ImageEditStudio: Edit completed', [
                'image_id' => $image->id,
            ]);
        } elseif ($image->status === GeneratedImage::STATUS_FAILED) {
            // Failed - credits already refunded by job
            $this->isProcessing = false;
            $this->processingImageId = null;
            $this->errorMessage = $image->error_message ?? 'Có lỗi xảy ra khi xử lý.';

            // Refresh user credits (may have been refunded)
            $user = Auth::user();
            if ($user) {
                $user->refresh();
                $this->userCredits = (float) $user->credits;
            }
        }
        // If still processing, do nothing - poll will call again
    }


    /**
     * Execute Object Replace (Fill API)
     */
    protected function executeReplace(): array
    {
        $imageBase64 = $this->extractBase64FromDataUri($this->sourceImage);
        $maskBase64 = $this->extractBase64FromDataUri($this->maskData);

        return $this->bflService->editWithMask(
            $imageBase64,
            $maskBase64,
            $this->editPrompt
        );
    }

    /**
     * Execute Text Edit (Kontext API)
     */
    protected function executeTextEdit(): array
    {
        $imageBase64 = $this->extractBase64FromDataUri($this->sourceImage);

        return $this->bflService->editText(
            $imageBase64,
            $this->editPrompt
        );
    }

    /**
     * Execute Background Change (Fill API với inverted mask)
     */
    protected function executeBackgroundChange(): array
    {
        $imageBase64 = $this->extractBase64FromDataUri($this->sourceImage);
        $maskBase64 = $this->extractBase64FromDataUri($this->maskData);

        // Invert mask: subject stays, background changes
        $invertedMask = $this->invertMask($maskBase64);

        return $this->bflService->editBackground(
            $imageBase64,
            $invertedMask,
            $this->editPrompt
        );
    }

    /**
     * Execute Expand (Expand API)
     */
    protected function executeExpand(): array
    {
        $imageBase64 = $this->extractBase64FromDataUri($this->sourceImage);

        return $this->bflService->expandImage(
            $imageBase64,
            $this->expandDirections,
            $this->editPrompt
        );
    }

    // =============================================
    // UTILITY METHODS
    // =============================================

    /**
     * Extract base64 string from data URI
     */
    protected function extractBase64FromDataUri(string $dataUri): string
    {
        if (str_contains($dataUri, ';base64,')) {
            return explode(';base64,', $dataUri)[1];
        }
        return $dataUri;
    }

    /**
     * Invert a mask (black ↔ white)
     */
    protected function invertMask(string $maskBase64): string
    {
        try {
            $imageData = base64_decode($maskBase64);
            $image = imagecreatefromstring($imageData);

            if (!$image) {
                return $maskBase64;
            }

            // Invert colors
            imagefilter($image, IMG_FILTER_NEGATE);

            // Convert back to base64
            ob_start();
            imagepng($image);
            $invertedData = ob_get_clean();
            imagedestroy($image);

            return base64_encode($invertedData);
        } catch (\Exception $e) {
            Log::warning('ImageEditStudio: Failed to invert mask', ['error' => $e->getMessage()]);
            return $maskBase64;
        }
    }

    /**
     * Reset editor state
     */
    public function resetEditor(): void
    {
        $this->sourceImage = '';
        $this->maskData = '';
        $this->editPrompt = '';
        $this->editMode = 'replace';
        $this->expandDirections = ['top' => 0, 'bottom' => 0, 'left' => 0, 'right' => 0];
        $this->resultImage = '';
        $this->errorMessage = '';
        $this->successMessage = '';
        $this->uploadedImage = null;
        $this->imageWidth = 0;
        $this->imageHeight = 0;

        $this->dispatch('reset-canvas');
    }

    /**
     * Download result image
     */
    public function downloadResult(): void
    {
        if (empty($this->resultImage)) {
            return;
        }

        $this->dispatch('download-image', [
            'src' => $this->resultImage,
            'filename' => 'edited-image-' . time() . '.png',
        ]);
    }

    /**
     * Save result image to MinIO and create history record
     */
    protected function saveResultToHistory($user, string $modeLabel, float $creditCost): void
    {
        try {
            // Extract base64 content from data URI or use URL directly
            $imageData = $this->resultImage;

            if (str_starts_with($imageData, 'data:image')) {
                // Base64 data URI
                $parts = explode(',', $imageData);
                $base64 = $parts[1] ?? '';
                $imageContent = base64_decode($base64);
            } elseif (str_starts_with($imageData, 'http')) {
                // URL - download content
                $imageContent = @file_get_contents($imageData);
                if ($imageContent === false) {
                    Log::warning('ImageEditStudio: Failed to download result image for storage');
                    return;
                }
            } else {
                Log::warning('ImageEditStudio: Unknown image format');
                return;
            }

            // Generate storage path
            $filename = 'edit-' . time() . '-' . uniqid() . '.png';
            $storagePath = "generated/{$user->id}/{$filename}";

            // Save to MinIO
            Storage::disk('minio')->put($storagePath, $imageContent, 'public');

            // Create GeneratedImage record
            $generatedImage = GeneratedImage::create([
                'user_id' => $user->id,
                'style_id' => null, // Edit Studio không dùng style
                'final_prompt' => $this->editPrompt,
                'user_custom_input' => $this->editPrompt,
                'generation_params' => [
                    'mode' => $this->editMode,
                    'mode_label' => $modeLabel,
                    'source' => 'edit_studio',
                ],
                'storage_path' => $storagePath,
                'status' => GeneratedImage::STATUS_COMPLETED,
                'credits_used' => $creditCost,
            ]);

            $this->lastGeneratedImageId = $generatedImage->id;

            // Update resultImage to use MinIO URL for continue editing
            $this->resultImage = $generatedImage->image_url;

            Log::info('ImageEditStudio: Saved result to history', [
                'image_id' => $generatedImage->id,
                'user_id' => $user->id,
                'storage_path' => $storagePath,
            ]);

        } catch (\Exception $e) {
            Log::error('ImageEditStudio: Failed to save result to history', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            // Don't throw - this is a non-critical error
        }
    }

    /**
     * Continue editing with result image as new source
     */
    public function continueEditing(): void
    {
        if (empty($this->resultImage)) {
            return;
        }

        // Set result as new source
        $this->sourceImage = $this->resultImage;
        $this->resultImage = '';
        $this->maskData = '';
        $this->editPrompt = '';
        $this->successMessage = '';
        $this->errorMessage = '';

        // Dispatch event to reload canvas with new image
        $this->dispatch('image-loaded', ['src' => $this->sourceImage]);
    }

    // =============================================
    // RENDER
    // =============================================

    public function render()
    {
        return view('livewire.image-edit-studio');
    }
}

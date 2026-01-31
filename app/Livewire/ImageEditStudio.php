<?php

namespace App\Livewire;

use App\Models\Setting;
use App\Services\BflService;
use App\Services\WalletService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Livewire Component: ImageEditStudio
 * 
 * Cho phép user edit ảnh bằng cách:
 * - Vẽ vùng mask (brush/rectangle)
 * - Chọn edit mode (replace/text/background/expand)
 * - Mô tả thay đổi
 * - Gọi BFL API (Fill/Kontext/Expand)
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
            'text' => 'Ví dụ: Change "OLD TEXT" to "NEW TEXT"',
            'expand' => 'Ví dụ: Continue the beach scene with blue sky',
            default => 'Ví dụ: Replace with a red sports car',
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

    /** Trạng thái đang xử lý */
    public bool $isProcessing = false;

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

    // =============================================
    // PROTECTED PROPERTIES
    // =============================================

    protected BflService $bflService;

    // =============================================
    // VALIDATION RULES
    // =============================================

    protected function rules(): array
    {
        return [
            'uploadedImage' => 'nullable|image|max:10240', // 10MB max
            'editPrompt' => 'required|string|min:3|max:1000',
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

    // =============================================
    // MAIN EDIT FLOW
    // =============================================

    /**
     * Process edit - Main entry point
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

        $this->isProcessing = true;
        $this->errorMessage = '';
        $this->resultImage = '';

        try {
            $result = match ($this->editMode) {
                'replace' => $this->executeReplace(),
                'text' => $this->executeTextEdit(),
                'background' => $this->executeBackgroundChange(),
                'expand' => $this->executeExpand(),
                default => throw new \Exception('Invalid edit mode'),
            };

            if ($result['success']) {
                $this->resultImage = $result['image_url'] ?? $result['image_base64'] ?? '';

                // Deduct credits after successful edit
                try {
                    $walletService = app(WalletService::class);
                    $modeLabels = [
                        'replace' => 'Replace Object',
                        'text' => 'Text Edit',
                        'background' => 'Background Change',
                        'expand' => 'Expand Image',
                    ];
                    $modeLabel = $modeLabels[$this->editMode] ?? 'Edit';

                    $walletService->deductCredits(
                        $user,
                        $creditCost,
                        "Edit Studio: {$modeLabel}",
                        'edit_studio'
                    );

                    // Update local credits display
                    $user->refresh();
                    $this->userCredits = (float) $user->credits;

                    $this->successMessage = "Chỉnh sửa thành công! Đã trừ {$creditCost} Xu.";
                } catch (\Exception $e) {
                    Log::error('ImageEditStudio: Failed to deduct credits', [
                        'user_id' => $user->id,
                        'amount' => $creditCost,
                        'error' => $e->getMessage(),
                    ]);
                    // Still show success since edit worked, just log the credit error
                    $this->successMessage = 'Chỉnh sửa thành công!';
                }
            } else {
                $this->errorMessage = $result['error'] ?? 'Có lỗi xảy ra khi xử lý.';
            }

        } catch (\Exception $e) {
            Log::error('ImageEditStudio: Edit failed', [
                'mode' => $this->editMode,
                'error' => $e->getMessage(),
            ]);
            $this->errorMessage = 'Có lỗi xảy ra: ' . $e->getMessage();
        } finally {
            $this->isProcessing = false;
        }
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

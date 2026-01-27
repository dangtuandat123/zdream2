<?php

namespace App\Livewire;

use App\Exceptions\InsufficientCreditsException;
use App\Jobs\GenerateImageJob;
use App\Models\GeneratedImage;
use App\Models\Style;
use App\Services\OpenRouterService;
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
    
    // Các aspect ratios hỗ trợ (load từ OpenRouterService)
    public array $aspectRatios = [];
    
    // Các image sizes (chỉ Gemini models)
    public array $imageSizes = [
        '1K' => '1K (Chuẩn)',
        '2K' => '2K (Cao)',
        '4K' => '4K (Rất cao)',
    ];
    
    // Model có hỗ trợ image_config không (Gemini)
    public bool $supportsImageConfig = false;
    
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
        $openRouterService = app(OpenRouterService::class);
        $this->aspectRatios = $openRouterService->getAspectRatios();
        
        // Detect xem model có hỗ trợ image_config không (Gemini)
        $this->supportsImageConfig = str_contains(strtolower($style->openrouter_model_id), 'gemini');
        
        // Set default aspect ratio từ style config (với fallback)
        $this->selectedAspectRatio = $style->aspect_ratio ?? '1:1';
        
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
            if (!empty($imageCapableModels) && !in_array($this->style->openrouter_model_id, $imageCapableModels)) {
                $this->errorMessage = 'Model AI của style này không còn hỗ trợ. Vui lòng liên hệ admin.';
                Log::error('Style uses non-image-capable model', [
                    'style_id' => $this->style->id,
                    'model_id' => $this->style->openrouter_model_id,
                ]);
                return;
            }
            */
        } catch (\Exception $e) {
            // [BUG FIX P3-05] Fallback: nếu API fail, cho phép generate nhưng log warning
            Log::warning('ModelManager validation skipped due to API error', [
                'error' => $e->getMessage(),
                'model_id' => $this->style->openrouter_model_id,
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
            // Lấy danh sách option IDs đã chọn
            $selectedOptionIds = array_values($this->selectedOptions);

            // Tạo record GeneratedImage trước (trạng thái processing)
            $generatedImage = GeneratedImage::create([
                'user_id' => $user->id,
                'style_id' => $this->style->id,
                'final_prompt' => '', // Sẽ cập nhật sau
                'selected_options' => $selectedOptionIds,
                'user_custom_input' => $this->customInput ?: null,
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
                    $inputImagesBase64
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
            $openRouterService = app(OpenRouterService::class);
            
            $result = $openRouterService->generateImage(
                $this->style,
                $selectedOptionIds,
                $this->customInput ?: null,
                $this->selectedAspectRatio,
                $this->selectedImageSize,
                $inputImagesBase64
            );

            if (!$result['success']) {
                $error = $result['error'] ?? 'OpenRouter error';

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
                $result['openrouter_id'] ?? null
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
        // OpenRouter/Providers often have payload limits (e.g. 10MB-20MB)
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
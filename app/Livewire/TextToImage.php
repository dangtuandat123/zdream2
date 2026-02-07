<?php

namespace App\Livewire;

use App\Jobs\GenerateImageJob;
use App\Models\GeneratedImage;
use App\Models\Setting;
use App\Models\Style;
use App\Services\BflService;
use App\Services\StorageService;
use App\Services\WalletService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * Livewire Component: TextToImage
 * 
 * Giao diện đơn giản để tạo ảnh từ prompt text.
 * Không cần chọn Style - sử dụng system style mặc định.
 */
class TextToImage extends Component
{
    // User prompt input
    public string $prompt = '';

    // Selected aspect ratio
    public string $aspectRatio = '1:1';

    // Selected model
    public string $modelId = 'flux-pro-1.1-ultra';

    // Available options
    public array $aspectRatios = [];
    public array $availableModels = [];

    // State
    public bool $isGenerating = false;
    public ?string $generatedImageUrl = null;
    public ?string $errorMessage = null;
    public ?int $lastImageId = null;

    // Async mode
    public bool $useAsyncMode = true;
    public int $pollingInterval = 2000;

    // History data
    public int $perPage = 12;
    public bool $loadingMore = false;

    // Credit cost
    public float $creditCost = 5.0;

    #[Computed]
    public function history()
    {
        if (!Auth::check())
            return collect();

        return GeneratedImage::where('user_id', Auth::id())
            ->whereHas('style', function ($q) {
                $q->where('is_system', true)->where('slug', Style::SYSTEM_T2I_SLUG);
            })
            ->latest()
            ->paginate($this->perPage);
    }

    public function mount(?string $initialPrompt = null): void
    {
        $this->useAsyncMode = config('queue.default') !== 'sync';

        // Load options from BflService
        $bflService = app(BflService::class);
        $this->aspectRatios = $bflService->getAspectRatios();

        // Filter models to only show text-to-image capable ones
        $allModels = $bflService->getAvailableModels();
        $this->availableModels = array_filter($allModels, function ($model) {
            $mode = $model['generation_mode'] ?? 't2i';
            return $mode === 't2i';
        });

        // Set default model from settings or first available
        $defaultModel = Setting::get('default_t2i_model', 'flux-pro-1.1-ultra');
        $this->modelId = $defaultModel;

        // Credit cost from settings
        $this->creditCost = (float) Setting::get('t2i_credit_cost', 5.0);

        // Set initial prompt if provided
        if (!empty($initialPrompt)) {
            $this->prompt = $initialPrompt;
        }
    }

    /**
     * Generate image from prompt
     */
    public function generate(): void
    {
        if ($this->isGenerating) {
            return;
        }

        $this->resetState();

        $user = Auth::user();
        if (!$user) {
            $this->errorMessage = 'Vui lòng đăng nhập để tạo ảnh.';
            return;
        }

        // Validate prompt
        $prompt = trim($this->prompt);
        if (empty($prompt)) {
            $this->errorMessage = 'Vui lòng nhập mô tả hình ảnh.';
            return;
        }

        if (mb_strlen($prompt) > 2000) {
            $this->errorMessage = 'Mô tả quá dài (tối đa 2000 ký tự).';
            return;
        }

        // Check credits
        if ($this->creditCost > 0 && !$user->hasEnoughCredits($this->creditCost)) {
            $this->errorMessage = "Bạn không đủ credits. Cần: {$this->creditCost}, Hiện có: {$user->credits}";
            return;
        }

        $this->isGenerating = true;
        $walletService = app(WalletService::class);
        $creditsDeducted = false;
        $generatedImage = null;

        try {
            // Get or create system style for text-to-image
            $systemStyle = Style::where('slug', Style::SYSTEM_T2I_SLUG)->first();

            if (!$systemStyle) {
                // Create on-the-fly if not exists (fallback)
                $systemStyle = Style::create([
                    'name' => 'Text to Image',
                    'slug' => Style::SYSTEM_T2I_SLUG,
                    'description' => 'Tạo ảnh AI từ mô tả văn bản.',
                    'price' => $this->creditCost,
                    'bfl_model_id' => $this->modelId,
                    'base_prompt' => '',
                    'config_payload' => [
                        'aspect_ratio' => $this->aspectRatio,
                        'output_format' => 'jpeg',
                    ],
                    'is_active' => true,
                    'is_system' => true,
                    'allow_user_custom_prompt' => true,
                ]);
            }

            // Update system style with current user selections
            $systemStyle->bfl_model_id = $this->modelId;
            $systemStyle->config_payload = array_merge(
                $systemStyle->config_payload ?? [],
                ['aspect_ratio' => $this->aspectRatio]
            );

            $generationParams = [
                'model_id' => $this->modelId,
                'aspect_ratio' => $this->aspectRatio,
            ];

            // Create GeneratedImage record
            $generatedImage = GeneratedImage::create([
                'user_id' => $user->id,
                'style_id' => $systemStyle->id,
                'final_prompt' => $prompt,
                'selected_options' => [],
                'user_custom_input' => $prompt,
                'generation_params' => $generationParams,
                'status' => GeneratedImage::STATUS_PROCESSING,
                'credits_used' => $this->creditCost,
            ]);

            // Deduct credits
            if ($this->creditCost > 0) {
                $walletService->deductCredits(
                    $user,
                    $this->creditCost,
                    "Tạo ảnh Text-to-Image",
                    'generation',
                    (string) $generatedImage->id
                );
                $creditsDeducted = true;
            }

            // Async mode: dispatch job
            if ($this->useAsyncMode) {
                GenerateImageJob::dispatch(
                    $generatedImage,
                    [], // no options
                    $prompt,
                    $this->aspectRatio,
                    '1K',
                    [], // no input images
                    ['aspect_ratio' => $this->aspectRatio]
                );

                $this->lastImageId = $generatedImage->id;
                return;
            }

            // Sync mode: generate directly
            $bflService = app(BflService::class);
            $result = $bflService->generateImage(
                $systemStyle,
                [],
                $prompt,
                $this->aspectRatio,
                null,
                [],
                ['aspect_ratio' => $this->aspectRatio]
            );

            if (!$result['success']) {
                $this->handleRefund($walletService, $user, $creditsDeducted, $result['error'] ?? 'Unknown error', $generatedImage);
                $generatedImage->markAsFailed($result['error'] ?? 'BFL error');
                $this->errorMessage = 'Có lỗi khi tạo ảnh. Credits đã được hoàn lại.';
                return;
            }

            // Save image
            $storageService = app(StorageService::class);
            $storageResult = $storageService->saveBase64Image($result['image_base64'], $user->id);

            if (!$storageResult['success']) {
                $this->handleRefund($walletService, $user, $creditsDeducted, 'Storage error', $generatedImage);
                $generatedImage->markAsFailed('Storage error');
                $this->errorMessage = 'Có lỗi khi lưu ảnh. Credits đã được hoàn lại.';
                return;
            }

            $generatedImage->markAsCompleted($storageResult['path'], $result['bfl_task_id'] ?? null);
            $generatedImage->refresh();

            $this->generatedImageUrl = $generatedImage->image_url;
            $this->lastImageId = $generatedImage->id;
            $this->dispatch('imageGenerated');

        } catch (\Throwable $e) {
            $this->handleRefund($walletService, $user, $creditsDeducted, $e->getMessage(), $generatedImage);

            if ($generatedImage) {
                $generatedImage->markAsFailed($e->getMessage());
            }

            Log::error('TextToImage generation failed', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
            ]);

            $this->errorMessage = config('app.debug')
                ? 'Lỗi: ' . $e->getMessage()
                : 'Có lỗi xảy ra. Credits đã được hoàn lại.';

            if (!$this->lastImageId) {
                $this->isGenerating = false;
            }
        } finally {
            if (!$this->useAsyncMode) {
                $this->isGenerating = false;
            }
        }
    }

    /**
     * Poll image status for async mode
     */
    public function pollImageStatus(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }

        if (!$this->lastImageId) {
            if ($this->isGenerating) {
                $this->isGenerating = false;
                $this->errorMessage = 'Không tìm thấy yêu cầu. Vui lòng thử lại.';
            }
            return;
        }

        $image = GeneratedImage::find($this->lastImageId);
        if (!$image) {
            $this->isGenerating = false;
            $this->errorMessage = 'Không tìm thấy ảnh.';
            $this->lastImageId = null;
            return;
        }

        // Security check
        if ($image->user_id !== Auth::id()) {
            $this->isGenerating = false;
            $this->errorMessage = 'Không có quyền truy cập.';
            $this->lastImageId = null;
            return;
        }

        if ($image->status === GeneratedImage::STATUS_COMPLETED) {
            $this->isGenerating = false;
            $this->generatedImageUrl = $image->image_url;
            $this->dispatch('imageGenerated');
        } elseif ($image->status === GeneratedImage::STATUS_FAILED) {
            $this->isGenerating = false;
            $this->errorMessage = 'Tạo ảnh thất bại. Credits đã được hoàn lại.';
            $this->lastImageId = null;
        }
    }

    protected function resetState(): void
    {
        $this->errorMessage = null;
        $this->generatedImageUrl = null;
        $this->lastImageId = null;
    }

    protected function handleRefund(
        WalletService $walletService,
        $user,
        bool $creditsDeducted,
        string $reason,
        ?GeneratedImage $generatedImage = null
    ): bool {
        if (!$creditsDeducted || $this->creditCost <= 0) {
            return false;
        }

        try {
            $walletService->addCredits(
                $user,
                $this->creditCost,
                "Hoàn tiền: {$reason}",
                'refund',
                $generatedImage ? (string) $generatedImage->id : null
            );
            return true;
        } catch (\Exception $e) {
            Log::error('Refund failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function resetForm(): void
    {
        $this->prompt = '';
        $this->resetState();
        $this->isGenerating = false;
    }

    public function loadMore(): void
    {
        $this->perPage += 12;
    }

    public function render()
    {
        return view('livewire.text-to-image', [
            'history' => $this->history
        ]);
    }
}

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
use Illuminate\Support\Str;
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
    public array $generatingImageIds = []; // Track multiple images in batch

    // Async mode
    public bool $useAsyncMode = true;
    public int $pollingInterval = 2000;

    // History data
    public int $perPage = 20; // Load 20 items initially
    public bool $loadingMore = false;

    // Filters
    public string $filterDate = 'all';
    public string $filterModel = 'all';
    public string $filterRatio = 'all';

    // Credit cost
    public float $creditCost = 5.0;

    public array $referenceImages = [];
    public int $batchSize = 1; // 1-4 items

    // For retry functionality
    public ?string $lastPrompt = null;
    public ?array $lastSettings = null;

    // Estimated generation time (seconds)
    public int $estimatedTime = 20;

    #[Computed]
    public function history()
    {
        if (!Auth::check())
            return collect();

        $query = GeneratedImage::where('user_id', Auth::id())
            ->where('status', GeneratedImage::STATUS_COMPLETED)
            ->whereNotNull('storage_path')
            ->whereHas('style', function ($q) {
                $q->where('is_system', true)->where('slug', Style::SYSTEM_T2I_SLUG);
            });

        // Date filter
        if ($this->filterDate !== 'all') {
            $date = match ($this->filterDate) {
                'week' => now()->subWeek(),
                'month' => now()->subMonth(),
                '3months' => now()->subMonths(3),
                default => null,
            };
            if ($date) {
                $query->where('created_at', '>=', $date);
            }
        }

        // Model filter
        if ($this->filterModel !== 'all') {
            $query->whereJsonContains('generation_params->model_id', $this->filterModel);
        }

        // Ratio filter
        if ($this->filterRatio !== 'all') {
            $query->whereJsonContains('generation_params->aspect_ratio', $this->filterRatio);
        }

        return $query->latest()->paginate($this->perPage);
    }

    public function updatedFilterDate(): void
    {
        $this->perPage = 12;
    }

    public function updatedFilterModel(): void
    {
        $this->perPage = 12;
    }

    public function updatedFilterRatio(): void
    {
        $this->perPage = 12;
    }

    public function resetFilters(): void
    {
        $this->filterDate = 'all';
        $this->filterModel = 'all';
        $this->filterRatio = 'all';
        $this->perPage = 12;
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
        $validIds = array_column($this->availableModels, 'id');
        $this->modelId = in_array($defaultModel, $validIds) ? $defaultModel : ($validIds[0] ?? $defaultModel);

        // Credit cost from settings
        $this->creditCost = (float) Setting::get('t2i_credit_cost', 5.0);

        // Set initial prompt if provided
        if (!empty($initialPrompt)) {
            $this->prompt = $initialPrompt;
        }

        // Handle query parameters for seamless handoff (e.g. from Homepage)
        if (request()->has('model')) {
            $m = request('model');
            // Check if model exists in available models
            foreach ($this->availableModels as $model) {
                if (($model['id'] ?? '') === $m) {
                    $this->modelId = $m;
                    break;
                }
            }
        }

        if (request()->has('ratio')) {
            $r = request('ratio');
            // Validate ratio format (e.g. 16:9) or from available list
            if (array_key_exists($r, $this->aspectRatios) || $r === 'auto' || preg_match('/^\d+:\d+$/', $r)) {
                $this->aspectRatio = $r;
            }
        }

        if (request()->has('batch')) {
            $b = (int) request('batch');
            if ($b >= 1 && $b <= 4) {
                $this->batchSize = $b;
            }
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

        // Check credits for TOTAL batch
        $totalCost = $this->creditCost * $this->batchSize;
        if ($totalCost > 0 && !$user->hasEnoughCredits($totalCost)) {
            $this->errorMessage = "Bạn không đủ credits. Cần: {$totalCost}, Hiện có: {$user->credits}";
            return;
        }

        $this->isGenerating = true;

        // Save for retry functionality
        $this->lastPrompt = $prompt;
        $this->lastSettings = [
            'aspectRatio' => $this->aspectRatio,
            'modelId' => $this->modelId,
        ];

        $walletService = app(WalletService::class);

        // Loop for batch size
        $batchId = Str::uuid()->toString();
        for ($i = 0; $i < $this->batchSize; $i++) {
            $generatedImage = null;
            $creditsDeducted = false;

            try {
                // Get or create system style
                $systemStyle = Style::where('slug', Style::SYSTEM_T2I_SLUG)->first();

                if (!$systemStyle) {
                    $systemStyle = Style::create([
                        'name' => 'Text to Image',
                        'slug' => Style::SYSTEM_T2I_SLUG,
                        'description' => 'Tạo ảnh AI từ mô tả văn bản.',
                        'price' => $this->creditCost,
                        'bfl_model_id' => $this->modelId,
                        'is_active' => true,
                        'is_system' => true,
                        'allow_user_custom_prompt' => true,
                    ]);
                } else {
                    // Always sync model to user selection (Bug 3 fix)
                    if ($systemStyle->bfl_model_id !== $this->modelId) {
                        $systemStyle->bfl_model_id = $this->modelId;
                        $systemStyle->save();
                    }
                }

                // Resolve auto ratio — store user choice + effective value for API
                $effectiveRatio = $this->aspectRatio === 'auto' ? null : $this->aspectRatio;

                $generationParams = [
                    'model_id' => $this->modelId,
                    'aspect_ratio' => $this->aspectRatio,
                    'aspect_ratio_user' => $this->aspectRatio,
                    'aspect_ratio_effective' => $effectiveRatio,
                    'batch_id' => $batchId,
                    'batch_index' => $i,
                ];

                // Create GeneratedImage record
                $generatedImage = GeneratedImage::create([
                    'user_id' => $user->id,
                    'style_id' => $systemStyle->id,
                    'final_prompt' => $prompt,
                    'model_id' => $this->modelId,
                    'aspect_ratio' => $this->aspectRatio,
                    'status' => GeneratedImage::STATUS_PENDING, // Fix: Init as PENDING
                    'credits_used' => $this->creditCost,
                    'generation_params' => $generationParams,
                ]);

                $this->generatingImageIds[] = $generatedImage->id;

                // Deduct credits (per image)
                if ($this->creditCost > 0) {
                    $walletService->deductCredits(
                        $user,
                        $this->creditCost,
                        "Tạo ảnh Text-to-Image (Batch " . ($i + 1) . ")",
                        'generation',
                        (string) $generatedImage->id
                    );
                    $creditsDeducted = true;
                }

                // Prepare inputs
                $inputImages = [];
                if (!empty($this->referenceImages)) {
                    foreach ($this->referenceImages as $idx => $img) {
                        $url = $img['url'] ?? $img;
                        if (is_string($url) && !empty($url)) {
                            $inputImages['image_' . $idx] = $url;
                        }
                    }
                }

                // Dispatch Job
                if ($this->useAsyncMode) {
                    GenerateImageJob::dispatch(
                        $generatedImage,
                        [],
                        $prompt,
                        $effectiveRatio ?? $this->aspectRatio,
                        '1K',
                        $inputImages,
                        ['aspect_ratio' => $effectiveRatio ?? $this->aspectRatio],
                        $this->modelId  // P0#2: freeze model at dispatch time
                    );
                } else {
                    // Sync fallback
                    $bflService = app(BflService::class);
                    $result = $bflService->generateImage(
                        $systemStyle,
                        [],
                        $prompt,
                        $effectiveRatio ?? $this->aspectRatio,
                        null,
                        $inputImages,
                        ['aspect_ratio' => $effectiveRatio ?? $this->aspectRatio]
                    );

                    if ($result['success']) {
                        $storageService = app(StorageService::class);
                        $sRes = $storageService->saveBase64Image($result['image_base64'], $user->id);
                        if ($sRes['success']) {
                            $generatedImage->markAsCompleted($sRes['path'], $result['bfl_task_id'] ?? null);
                        } else {
                            $generatedImage->markAsFailed('Storage error');
                        }
                    } else {
                        $generatedImage->markAsFailed($result['error'] ?? 'Error');
                        if ($creditsDeducted) {
                            $walletService->addCredits($user, $this->creditCost, 'refund', 'refund', (string) $generatedImage->id);
                        }
                    }
                }

            } catch (\Throwable $e) {
                Log::error('Batch item error: ' . $e->getMessage());
                if ($generatedImage) {
                    $generatedImage->markAsFailed($e->getMessage());
                }
                if ($creditsDeducted && isset($walletService)) {
                    $walletService->addCredits($user, $this->creditCost, 'refund', 'refund', $generatedImage ? (string) $generatedImage->id : null);
                }
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

        if (empty($this->generatingImageIds)) {
            if ($this->isGenerating) {
                $this->isGenerating = false;
            }
            return;
        }

        // Timeout check (7 minutes)
        $firstPending = GeneratedImage::whereIn('id', $this->generatingImageIds)
            ->whereIn('status', [GeneratedImage::STATUS_PENDING, GeneratedImage::STATUS_PROCESSING])
            ->oldest()
            ->first();

        if ($firstPending && $firstPending->created_at->diffInMinutes(now()) > 7) {
            GeneratedImage::whereIn('id', $this->generatingImageIds)
                ->whereIn('status', [GeneratedImage::STATUS_PENDING, GeneratedImage::STATUS_PROCESSING])
                ->update(['status' => GeneratedImage::STATUS_FAILED, 'error_message' => 'Timeout: Generation took too long']);

            $this->isGenerating = false;
            $this->generatingImageIds = [];
            $this->errorMessage = 'Quá trình tạo ảnh mất quá nhiều thời gian. Vui lòng thử lại.';
            $this->dispatch('imageGenerationFailed');
            return;
        }

        // Count pending
        $pendingCount = GeneratedImage::whereIn('id', $this->generatingImageIds)
            ->where(function ($q) {
                $q->where('status', GeneratedImage::STATUS_PENDING)
                    ->orWhere('status', GeneratedImage::STATUS_PROCESSING);
            })
            ->count();

        // Fix 1: Skip re-render while still pending to prevent gallery flash
        if ($pendingCount > 0) {
            $this->skipRender();
            return;
        }

        // All done — allow full re-render
        $this->isGenerating = false;
        $successCount = GeneratedImage::whereIn('id', $this->generatingImageIds)
            ->where('status', GeneratedImage::STATUS_COMPLETED)->count();
        $failedCount = count($this->generatingImageIds) - $successCount;
        $lastId = end($this->generatingImageIds);
        if ($lastId) {
            $img = GeneratedImage::find($lastId);
            $this->generatedImageUrl = $img ? $img->image_url : null;
        }
        $this->generatingImageIds = [];
        if ($successCount > 0) {
            $this->dispatch('imageGenerated', successCount: $successCount, failedCount: $failedCount);
        } else {
            $this->dispatch('imageGenerationFailed');
        }
    }

    protected function resetState(): void
    {
        $this->errorMessage = null;
        $this->generatedImageUrl = null;
        $this->generatingImageIds = [];
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
        $this->referenceImages = [];
        $this->resetState();
        $this->isGenerating = false;
    }

    public function loadMore(): void
    {
        if ($this->loadingMore)
            return;
        $this->loadingMore = true;
        $this->perPage += 8;
        $this->loadingMore = false;
        $this->dispatch('historyUpdated');
    }

    /**
     * Retry last generation with same settings
     */
    public function retry(): void
    {
        if ($this->lastPrompt) {
            $this->prompt = $this->lastPrompt;
            if ($this->lastSettings) {
                $this->aspectRatio = $this->lastSettings['aspectRatio'] ?? $this->aspectRatio;
                $this->modelId = $this->lastSettings['modelId'] ?? $this->modelId;
            }
            $this->errorMessage = null;
            $this->generate();
        }
    }

    /**
     * Cancel ongoing generation (for async mode)
     */
    public function cancelGeneration(): void
    {
        if ($this->isGenerating && !empty($this->generatingImageIds)) {
            $walletService = app(WalletService::class);
            foreach ($this->generatingImageIds as $imageId) {
                $image = GeneratedImage::find($imageId);
                if ($image && $image->user_id === Auth::id() && $image->status === GeneratedImage::STATUS_PROCESSING) {
                    $image->markAsFailed('Đã hủy bởi user');
                    $walletService->addCredits(
                        Auth::user(),
                        $this->creditCost,
                        'Hoàn tiền: Hủy tạo ảnh',
                        'refund',
                        (string) $image->id
                    );
                }
            }
        }

        $this->isGenerating = false;
        $this->generatingImageIds = [];
        $this->errorMessage = null;
    }

    /**
     * Set reference images from frontend
     */
    public function setReferenceImages(array $images): void
    {
        $modelConfig = collect(config('services_custom.bfl.models'))
            ->firstWhere('id', $this->modelId);
        $max = ($modelConfig['supports_image_input'] ?? false)
            ? ($modelConfig['max_input_images'] ?? 1) : 0;
        $this->referenceImages = array_slice($images, 0, max($max, 0));
    }

    public function copyPrompt(int $id): void
    {
        $image = GeneratedImage::find($id);
        if ($image && $image->user_id === Auth::id()) {
            $this->prompt = $image->final_prompt;
        }
    }

    public function reusePrompt(int $id): void
    {
        $image = GeneratedImage::find($id);
        if ($image && $image->user_id === Auth::id()) {
            $this->prompt = $image->final_prompt;
            $this->modelId = $image->generation_params['model_id'] ?? $this->modelId;
            $this->aspectRatio = $image->generation_params['aspect_ratio'] ?? $this->aspectRatio;
        }
    }

    public function deleteImage(int $id): void
    {
        $image = GeneratedImage::find($id);
        if ($image && $image->user_id === Auth::id()) {
            $image->delete();
        }
    }



    public function render()
    {
        $history = $this->history;
        return view('livewire.text-to-image', [
            'history' => $history,
        ]);
    }
}

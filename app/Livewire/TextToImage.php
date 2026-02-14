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
 * Giao dien don gian de tao anh tu prompt text.
 * Khong can chon Style - su dung system style mac dinh.
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
    public int $lastPolledCompletedCount = 0;

    // History data
    public int $perPage = 6; // Load a small newest batch first; older history loads progressively
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
        try {
            if (!Auth::check()) {
                return collect();
            }

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

            return $query
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->paginate($this->perPage);
        } catch (\Throwable $e) {
            report($e);

            return collect();
        }
    }

    public function updatedFilterDate(): void
    {
        $this->perPage = 6;
    }

    public function updatedFilterModel(): void
    {
        $this->perPage = 6;
    }

    public function updatedFilterRatio(): void
    {
        $this->perPage = 6;
    }

    public function resetFilters(): void
    {
        $this->filterDate = 'all';
        $this->filterModel = 'all';
        $this->filterRatio = 'all';
        $this->perPage = 6;
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
            $this->errorMessage = 'Vui long dang nhap de tao anh.';
            return;
        }

        // Validate prompt
        $prompt = trim($this->prompt);
        if (empty($prompt)) {
            $this->errorMessage = 'Vui long nhap mo ta hinh anh.';
            return;
        }

        if (mb_strlen($prompt) > 2000) {
            $this->errorMessage = 'Mo ta qua dai (toi da 2000 ky tu).';
            return;
        }

        // Check credits for TOTAL batch
        $totalCost = $this->creditCost * $this->batchSize;
        if ($totalCost > 0 && !$user->hasEnoughCredits($totalCost)) {
            $this->errorMessage = "Ban khong du credits. Can: {$totalCost}, Hien co: {$user->credits}";
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
                        'description' => 'Tao anh AI tu mo ta van ban.',
                        'price' => $this->creditCost,
                        'bfl_model_id' => $this->modelId,
                        'is_active' => true,
                        'is_system' => true,
                        'allow_user_custom_prompt' => true,
                    ]);
                }

                // Resolve auto ratio - store user choice + effective value for API
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
                        "Tao anh Text-to-Image (Batch " . ($i + 1) . ")",
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
                    $syncStyle = clone $systemStyle;
                    $syncStyle->bfl_model_id = $this->modelId;
                    $result = $bflService->generateImage(
                        $syncStyle,
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
                            try {
                                $walletService->refundCredits(
                                    $user,
                                    $this->creditCost,
                                    'Generation failed',
                                    (string) $generatedImage->id
                                );
                            } catch (\Throwable $refundError) {
                                Log::error('Sync refund failed', [
                                    'image_id' => $generatedImage->id,
                                    'error' => $refundError->getMessage(),
                                ]);
                            }
                        }
                    }
                }

            } catch (\Throwable $e) {
                Log::error('Batch item error: ' . $e->getMessage());
                if ($generatedImage) {
                    $generatedImage->markAsFailed($e->getMessage());
                }
                if ($creditsDeducted && isset($walletService)) {
                    try {
                        $walletService->refundCredits(
                            $user,
                            $this->creditCost,
                            'Generation exception',
                            $generatedImage ? (string) $generatedImage->id : null
                        );
                    } catch (\Throwable $refundError) {
                        Log::error('Exception refund failed', [
                            'image_id' => $generatedImage?->id,
                            'error' => $refundError->getMessage(),
                        ]);
                    }
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

        // Timeout check (configurable)
        $timeoutMinutes = (int) config('services_custom.bfl.processing_timeout_minutes', 7);
        $firstPending = GeneratedImage::whereIn('id', $this->generatingImageIds)
            ->whereIn('status', [GeneratedImage::STATUS_PENDING, GeneratedImage::STATUS_PROCESSING])
            ->oldest()
            ->first();

        if ($firstPending && $firstPending->created_at->diffInMinutes(now()) > $timeoutMinutes) {
            $pendingImages = GeneratedImage::whereIn('id', $this->generatingImageIds)
                ->whereIn('status', [GeneratedImage::STATUS_PENDING, GeneratedImage::STATUS_PROCESSING])
                ->get();

            $walletService = app(WalletService::class);
            $authUser = Auth::user();

            foreach ($pendingImages as $image) {
                $image->markAsFailed('Timeout: Generation took too long');

                if (!$authUser || $image->user_id !== $authUser->id || (float) $image->credits_used <= 0) {
                    continue;
                }

                try {
                    $walletService->refundCredits(
                        $authUser,
                        (float) $image->credits_used,
                        'Generation timeout',
                        (string) $image->id
                    );
                } catch (\Throwable $e) {
                    Log::error('Failed to refund timeout generation', [
                        'image_id' => $image->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            $this->isGenerating = false;
            $this->generatingImageIds = [];
            $this->lastPolledCompletedCount = 0;
            $this->errorMessage = 'Qua trinh tao anh mat qua nhieu thoi gian. Vui long thu lai.';
            $this->dispatch('imageGenerationFailed');
            return;
        }

        $completedCount = GeneratedImage::whereIn('id', $this->generatingImageIds)
            ->where('status', GeneratedImage::STATUS_COMPLETED)
            ->count();

        // Count pending
        $pendingCount = GeneratedImage::whereIn('id', $this->generatingImageIds)
            ->where(function ($q) {
                $q->where('status', GeneratedImage::STATUS_PENDING)
                    ->orWhere('status', GeneratedImage::STATUS_PROCESSING);
            })
            ->count();

        // Render only when there is newly completed output while batch is still pending.
        if ($pendingCount > 0) {
            if ($completedCount > $this->lastPolledCompletedCount) {
                $this->lastPolledCompletedCount = $completedCount;
                return;
            }

            $this->skipRender();
            return;
        }

        // All done - allow full re-render
        $this->isGenerating = false;
        $successCount = $completedCount;
        $failedCount = count($this->generatingImageIds) - $successCount;
        $lastId = end($this->generatingImageIds);
        if ($lastId) {
            $img = GeneratedImage::find($lastId);
            $this->generatedImageUrl = $img ? $img->image_url : null;
        }
        $this->generatingImageIds = [];
        $this->lastPolledCompletedCount = 0;
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
        $this->lastPolledCompletedCount = 0;
    }

    public function resetForm(): void
    {
        $this->prompt = '';
        $this->referenceImages = [];
        $this->resetState();
        $this->isGenerating = false;
    }

    public function loadMore(int $count = 1): void
    {
        if ($this->loadingMore) {
            return;
        }

        $history = $this->history;
        if (!$history instanceof \Illuminate\Pagination\LengthAwarePaginator) {
            return;
        }

        $total = (int) $history->total();
        if ($total <= 0 || !$history->hasMorePages()) {
            return;
        }

        // Clamp client-requested batch size to protect from over-fetch.
        $count = max(1, min($count, 12));

        $this->loadingMore = true;
        $this->perPage = min($this->perPage + $count, $total);
        $this->loadingMore = false;

        $hasMoreAfterUpdate = $this->perPage < $total;

        $this->dispatch('historyUpdated', hasMore: $hasMoreAfterUpdate);
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
            $authUser = Auth::user();
            foreach ($this->generatingImageIds as $imageId) {
                $image = GeneratedImage::find($imageId);
                if (!$image || $image->user_id !== Auth::id()) {
                    continue;
                }

                if (!in_array($image->status, [GeneratedImage::STATUS_PENDING, GeneratedImage::STATUS_PROCESSING], true)) {
                    continue;
                }

                $image->markAsFailed('Da huy boi user');

                if ($authUser && (float) $image->credits_used > 0) {
                    try {
                        $walletService->refundCredits(
                            $authUser,
                            (float) $image->credits_used,
                            'Huy tao anh',
                            (string) $image->id
                        );
                    } catch (\Throwable $e) {
                        Log::error('Cancel refund failed', [
                            'image_id' => $image->id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            }
        }

        $this->isGenerating = false;
        $this->generatingImageIds = [];
        $this->lastPolledCompletedCount = 0;
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


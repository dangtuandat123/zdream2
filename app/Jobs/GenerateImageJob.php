<?php

namespace App\Jobs;

use App\Models\GeneratedImage;
use App\Models\User;
use App\Services\OpenRouterService;
use App\Services\StorageService;
use App\Services\WalletService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * GenerateImageJob
 * 
 * Queue job để tạo ảnh AI trong background.
 * Xử lý: gọi OpenRouter API → lưu ảnh → update status
 * Tự động retry 2 lần với exponential backoff.
 */
class GenerateImageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Số lần retry tối đa
     */
    public int $tries = 2;

    /**
     * Backoff time giữa các lần retry (seconds)
     */
    public array $backoff = [30, 60];

    /**
     * Timeout cho job (3 phút)
     */
    public int $timeout = 180;

    /**
     * Constructor
     */
    public function __construct(
        public GeneratedImage $generatedImage,
        public array $selectedOptionIds,
        public ?string $customInput,
        public string $aspectRatio,
        public string $imageSize,
        public array $inputImagesBase64 = []
    ) {}

    /**
     * Execute the job.
     */
    public function handle(
        OpenRouterService $openRouterService,
        StorageService $storageService,
        WalletService $walletService
    ): void {
        $generatedImage = $this->generatedImage;
        $user = $generatedImage->user;
        $style = $generatedImage->style;

        if (!$user || !$style) {
            Log::error('GenerateImageJob: Missing user or style', [
                'image_id' => $generatedImage->id,
            ]);
            $generatedImage->markAsFailed('Missing user or style data');
            return;
        }

        try {
            Log::info('GenerateImageJob started', [
                'image_id' => $generatedImage->id,
                'user_id' => $user->id,
                'style_id' => $style->id,
            ]);

            // Gọi OpenRouter API
            $result = $openRouterService->generateImage(
                $style,
                $this->selectedOptionIds,
                $this->customInput,
                $this->aspectRatio,
                $this->imageSize,
                $this->inputImagesBase64
            );

            if (!$result['success']) {
                $this->handleFailure($generatedImage, $user, $walletService, $result['error'] ?? 'OpenRouter error');
                return;
            }

            // Cập nhật final prompt
            $generatedImage->update(['final_prompt' => $result['final_prompt'] ?? '']);

            // Lưu ảnh vào MinIO
            $storageResult = $storageService->saveBase64Image(
                $result['image_base64'],
                $user->id
            );

            if (!$storageResult['success']) {
                $this->handleFailure($generatedImage, $user, $walletService, 'Storage error: ' . ($storageResult['error'] ?? 'Unknown'));
                return;
            }

            // Đánh dấu hoàn thành
            $generatedImage->markAsCompleted(
                $storageResult['path'],
                $result['openrouter_id'] ?? null
            );

            Log::info('GenerateImageJob completed', [
                'image_id' => $generatedImage->id,
                'storage_path' => $storageResult['path'],
            ]);

        } catch (\Throwable $e) {
            Log::error('GenerateImageJob exception', [
                'image_id' => $generatedImage->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->handleFailure($generatedImage, $user, $walletService, 'System error: ' . $e->getMessage());
        }
    }

    /**
     * Handle job failure: mark as failed và refund credits
     */
    protected function handleFailure(
        GeneratedImage $generatedImage,
        User $user,
        WalletService $walletService,
        string $errorMessage
    ): void {
        $generatedImage->markAsFailed($errorMessage);

        // Refund credits
        try {
            $walletService->refundCredits(
                $user,
                $generatedImage->credits_used,
                $errorMessage,
                (string) $generatedImage->id
            );

            Log::info('Credits refunded for failed generation', [
                'image_id' => $generatedImage->id,
                'user_id' => $user->id,
                'amount' => $generatedImage->credits_used,
            ]);

        } catch (\Throwable $e) {
            Log::error('Failed to refund credits', [
                'image_id' => $generatedImage->id,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle a job failure (called by Laravel queue worker)
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('GenerateImageJob failed permanently', [
            'image_id' => $this->generatedImage->id,
            'error' => $exception->getMessage(),
        ]);

        // Mark as failed nếu chưa
        if ($this->generatedImage->status === GeneratedImage::STATUS_PROCESSING) {
            $this->generatedImage->markAsFailed('Job failed: ' . $exception->getMessage());
        }
    }
}

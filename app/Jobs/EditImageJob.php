<?php

namespace App\Jobs;

use App\Models\GeneratedImage;
use App\Models\User;
use App\Services\BflService;
use App\Services\StorageService;
use App\Services\WalletService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * EditImageJob
 * 
 * Queue job để xử lý edit ảnh AI trong background.
 * Xử lý: gọi BFL API → lưu ảnh → update status
 * Tự động retry 2 lần với exponential backoff.
 */
class EditImageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Số lần retry tối đa
     */
    public int $tries = 2;

    /**
     * Timeout cho job (7 phút)
     */
    public int $timeout = 420;

    /**
     * Backoff times between retries (seconds)
     */
    public array $backoff = [30, 60];

    /**
     * Constructor
     */
    public function __construct(
        public GeneratedImage $generatedImage,
        public string $editMode,
        public string $sourceImagePath,
        public string $maskDataBase64,
        public string $editPrompt,
        public array $expandDirections = [],
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(
        BflService $bflService,
        StorageService $storageService,
        WalletService $walletService
    ): void {
        $generatedImage = $this->generatedImage;
        $user = $generatedImage->user;

        if (!$user) {
            Log::error('EditImageJob: Missing user', [
                'image_id' => $generatedImage->id,
            ]);
            $generatedImage->markAsFailed('Missing user data');
            return;
        }

        try {
            Log::info('EditImageJob started', [
                'image_id' => $generatedImage->id,
                'user_id' => $user->id,
                'mode' => $this->editMode,
            ]);

            // Resolve source image from MinIO path or base64
            $sourceImageBase64 = $this->resolveSourceImage();
            if (!$sourceImageBase64) {
                $this->handleFailure($generatedImage, $user, $walletService, 'Cannot load source image');
                return;
            }

            // Execute edit based on mode
            $result = match ($this->editMode) {
                'replace' => $bflService->editWithMask(
                    $sourceImageBase64,
                    $this->maskDataBase64,
                    $this->editPrompt
                ),
                'text' => $bflService->editText(
                    $sourceImageBase64,
                    $this->editPrompt
                ),
                'background' => $bflService->editBackground(
                    $sourceImageBase64,
                    $this->maskDataBase64,
                    $this->editPrompt
                ),
                'expand' => $bflService->expandImage(
                    $sourceImageBase64,
                    $this->expandDirections,
                    $this->editPrompt
                ),
                default => ['success' => false, 'error' => 'Invalid edit mode'],
            };

            if (!$result['success']) {
                $this->handleFailure($generatedImage, $user, $walletService, $result['error'] ?? 'BFL error');
                return;
            }

            // Get image data
            $imageBase64 = $result['image_base64'] ?? null;
            if (!$imageBase64 && isset($result['image_url'])) {
                // Download from URL
                $imageContent = @file_get_contents($result['image_url']);
                if ($imageContent) {
                    $imageBase64 = 'data:image/png;base64,' . base64_encode($imageContent);
                }
            }

            if (!$imageBase64) {
                $this->handleFailure($generatedImage, $user, $walletService, 'No image in result');
                return;
            }

            // Save to MinIO
            $storageResult = $storageService->saveBase64Image($imageBase64, $user->id);

            if (!$storageResult['success']) {
                $this->handleFailure($generatedImage, $user, $walletService, 'Storage error: ' . ($storageResult['error'] ?? 'Unknown'));
                return;
            }

            // Check status before completing (watchdog might have marked failed)
            $generatedImage->refresh();
            if ($generatedImage->status !== GeneratedImage::STATUS_PROCESSING) {
                Log::warning('EditImageJob: Status changed during processing', [
                    'image_id' => $generatedImage->id,
                    'current_status' => $generatedImage->status,
                ]);
                $storageService->deleteImage($storageResult['path']);
                return;
            }

            // Mark completed
            $generatedImage->markAsCompleted($storageResult['path']);

            Log::info('EditImageJob completed', [
                'image_id' => $generatedImage->id,
                'storage_path' => $storageResult['path'],
            ]);

        } catch (\Throwable $e) {
            Log::error('EditImageJob exception', [
                'image_id' => $generatedImage->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->handleFailure($generatedImage, $user, $walletService, 'System error: ' . $e->getMessage());
        } finally {
            $this->cleanupTempFiles();
        }
    }

    /**
     * Resolve source image from path or base64
     */
    protected function resolveSourceImage(): ?string
    {
        // If it's already base64
        if (str_starts_with($this->sourceImagePath, 'data:image')) {
            return $this->sourceImagePath;
        }

        // If it's a URL, download it
        if (str_starts_with($this->sourceImagePath, 'http')) {
            $content = @file_get_contents($this->sourceImagePath);
            if ($content) {
                $finfo = new \finfo(FILEINFO_MIME_TYPE);
                $mime = $finfo->buffer($content) ?: 'image/jpeg';
                return 'data:' . $mime . ';base64,' . base64_encode($content);
            }
            return null;
        }

        // Try to load from MinIO
        try {
            $content = \Illuminate\Support\Facades\Storage::disk('minio')->get($this->sourceImagePath);
            if ($content) {
                $finfo = new \finfo(FILEINFO_MIME_TYPE);
                $mime = $finfo->buffer($content) ?: 'image/jpeg';
                return 'data:' . $mime . ';base64,' . base64_encode($content);
            }
        } catch (\Exception $e) {
            Log::warning('EditImageJob: Failed to load source from MinIO', [
                'path' => $this->sourceImagePath,
                'error' => $e->getMessage(),
            ]);
        }

        return null;
    }

    /**
     * Cleanup temporary files
     */
    protected function cleanupTempFiles(): void
    {
        // Clean up any temp files if needed
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
            if ($generatedImage->credits_used > 0) {
                $walletService->refundCredits(
                    $user,
                    $generatedImage->credits_used,
                    $errorMessage,
                    (string) $generatedImage->id
                );

                Log::info('Credits refunded for failed edit', [
                    'image_id' => $generatedImage->id,
                    'user_id' => $user->id,
                    'amount' => $generatedImage->credits_used,
                ]);
            }
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
        Log::error('EditImageJob failed permanently', [
            'image_id' => $this->generatedImage->id,
            'error' => $exception->getMessage(),
        ]);

        // Refund credits if job fails permanently
        if ($this->generatedImage->status === GeneratedImage::STATUS_PROCESSING) {
            $this->refundCreditsDirectly($this->generatedImage->user, $this->generatedImage);
        }
    }

    /**
     * Refund credits directly (dùng khi không có DI container)
     */
    protected function refundCreditsDirectly(User $user, GeneratedImage $generatedImage): void
    {
        if ($generatedImage->credits_used <= 0) {
            return;
        }

        try {
            $user->increment('credits', $generatedImage->credits_used);

            \App\Models\WalletTransaction::create([
                'user_id' => $user->id,
                'type' => 'refund',
                'amount' => $generatedImage->credits_used,
                'balance_after' => $user->fresh()->credits,
                'description' => 'Hoàn xu do edit thất bại: ' . ($generatedImage->error_message ?? 'Unknown error'),
                'reference_type' => 'generated_image',
                'reference_id' => (string) $generatedImage->id,
            ]);

            $generatedImage->markAsFailed('Job failed permanently: ' . ($generatedImage->error_message ?? 'Unknown'));

            Log::info('Credits refunded directly', [
                'user_id' => $user->id,
                'image_id' => $generatedImage->id,
                'amount' => $generatedImage->credits_used,
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to refund credits directly', [
                'user_id' => $user->id,
                'image_id' => $generatedImage->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}

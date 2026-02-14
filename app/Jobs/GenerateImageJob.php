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
use Illuminate\Support\Facades\Storage;

/**
 * GenerateImageJob
 * 
 * Queue job để tạo ảnh AI trong background.
 * Xử lý: gọi BFL API → lưu ảnh → update status
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
     * Timeout cho job (7 phút)
     */
    public int $timeout = 420;

    /**
     * Constructor
     */
    public function __construct(
        public GeneratedImage $generatedImage,
        public array $selectedOptionIds,
        public ?string $customInput,
        public string $aspectRatio,
        public string $imageSize,
        public array $inputImagesBase64 = [],
        public array $generationOverrides = [],
        public ?string $modelOverride = null
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
        $style = $generatedImage->style;

        // Fix: Mark as processing when job starts
        $generatedImage->update(['status' => GeneratedImage::STATUS_PROCESSING]);

        if (!$user || !$style) {
            Log::error('GenerateImageJob: Missing user or style', [
                'image_id' => $generatedImage->id,
            ]);
            $generatedImage->markAsFailed('Missing user or style data');

            // HIGH-01 FIX: Refund credits nếu có user và credits_used > 0
            if ($user && $generatedImage->credits_used > 0) {
                $this->refundCreditsDirectly($user, $generatedImage);
            }
            return;
        }

        // [FIX INT-02] Check style còn active không trước khi gọi API
        $style->refresh();
        if (!$style->is_active) {
            Log::warning('GenerateImageJob: Style disabled during processing', [
                'image_id' => $generatedImage->id,
                'style_id' => $style->id,
            ]);
            $generatedImage->markAsFailed('Style đã bị tắt trong khi xử lý');
            $this->refundCreditsDirectly($user, $generatedImage);
            return;
        }

        // Fix 4: Freeze model AFTER refresh so override is not lost
        // Priority: explicit override → generation_params → style default
        $frozenModel = $this->modelOverride
            ?? ($generatedImage->generation_params['model_id'] ?? null)
            ?? $style->bfl_model_id;
        $style->bfl_model_id = $frozenModel;

        try {
            Log::info('GenerateImageJob started', [
                'image_id' => $generatedImage->id,
                'user_id' => $user->id,
                'style_id' => $style->id,
            ]);

            $resolvedInputImages = $this->resolveInputImages();

            // Gọi BFL API
            $result = $bflService->generateImage(
                $style,
                $this->selectedOptionIds,
                $this->customInput,
                $this->aspectRatio,
                $this->imageSize,
                $resolvedInputImages,
                $this->generationOverrides
            );

            if (!$result['success']) {
                $this->handleFailure($generatedImage, $user, $walletService, $result['error'] ?? 'BFL error');
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

            // INT-01 FIX: Refresh và check status trước khi complete
            // Nếu watchdog đã mark failed + refund, không ghi đè status
            $generatedImage->refresh();
            if ($generatedImage->status !== GeneratedImage::STATUS_PROCESSING) {
                Log::warning('GenerateImageJob: Status changed during processing, skipping complete', [
                    'image_id' => $generatedImage->id,
                    'current_status' => $generatedImage->status,
                ]);
                // Xóa ảnh đã upload vì đã refund
                $storageService->deleteImage($storageResult['path']);
                return;
            }

            // Đánh dấu hoàn thành
            $generatedImage->markAsCompleted(
                $storageResult['path'],
                $result['bfl_task_id'] ?? null
            );

            // Fix 5: Save actual output dimensions for proper gallery rendering
            try {
                $imageData = $result['image_base64'] ?? null;
                if ($imageData) {
                    // Remove data URI prefix if present
                    if (str_contains($imageData, ',')) {
                        $imageData = substr($imageData, strpos($imageData, ',') + 1);
                    }
                    $decoded = base64_decode($imageData);
                    if ($decoded) {
                        $size = getimagesizefromstring($decoded);
                        if ($size) {
                            $params = $generatedImage->generation_params ?? [];
                            $params['output_width'] = $size[0];
                            $params['output_height'] = $size[1];
                            $generatedImage->update(['generation_params' => $params]);
                        }
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('GenerateImageJob: Failed to detect output dimensions', [
                    'image_id' => $generatedImage->id,
                    'error' => $e->getMessage(),
                ]);
            }

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
        } finally {
            $this->cleanupTempImages();
        }
    }

    /**
     * Convert temp file paths to base64 data URLs for BFL
     */
    protected function resolveInputImages(): array
    {
        $result = [];
        foreach ($this->inputImagesBase64 as $key => $value) {
            if (!is_string($value)) {
                continue;
            }

            if (str_starts_with($value, 'data:image/')) {
                $result[$key] = $value;
                continue;
            }

            if (str_starts_with($value, 'minio:')) {
                $minioPath = substr($value, 6);
                if (Storage::disk('minio')->exists($minioPath)) {
                    $content = Storage::disk('minio')->get($minioPath);
                    $finfo = new \finfo(FILEINFO_MIME_TYPE);
                    $mime = $finfo->buffer($content) ?: 'image/jpeg';
                    $result[$key] = 'data:' . $mime . ';base64,' . base64_encode($content);
                    continue;
                }
            }

            if (Storage::disk('local')->exists($value)) {
                $content = Storage::disk('local')->get($value);
                $finfo = new \finfo(FILEINFO_MIME_TYPE);
                $mime = $finfo->buffer($content) ?: 'image/jpeg';
                $result[$key] = 'data:' . $mime . ';base64,' . base64_encode($content);
                continue;
            }

            // Fallback: assume raw base64 or URL is still usable
            $result[$key] = $value;
        }

        return $result;
    }

    /**
     * Cleanup temp images after job finishes
     */
    protected function cleanupTempImages(): void
    {
        foreach ($this->inputImagesBase64 as $value) {
            if (!is_string($value)) {
                continue;
            }

            if (str_starts_with($value, 'minio:')) {
                $minioPath = substr($value, 6);
                if (Storage::disk('minio')->exists($minioPath)) {
                    Storage::disk('minio')->delete($minioPath);
                }
                continue;
            }

            if (Storage::disk('local')->exists($value)) {
                Storage::disk('local')->delete($value);
            }
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
            if ($generatedImage->credits_used > 0) {
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
     * HIGH-01 FIX: Đảm bảo refund credits khi job fail permanently
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

            // HIGH-01 FIX: Refund credits khi job fail permanently
            $user = $this->generatedImage->user;
            if ($user && $this->generatedImage->credits_used > 0) {
                $this->refundCreditsDirectly($user, $this->generatedImage);
            }
        }
    }

    /**
     * Refund credits trực tiếp (dùng khi không có DI container)
     * HIGH-01 FIX: Helper method để refund trong failed() và edge cases
     */
    protected function refundCreditsDirectly(User $user, GeneratedImage $generatedImage): void
    {
        try {
            $walletService = app(WalletService::class);
            $walletService->refundCredits(
                $user,
                $generatedImage->credits_used,
                'Job failed permanently',
                (string) $generatedImage->id
            );

            Log::info('Credits refunded in failed() handler', [
                'image_id' => $generatedImage->id,
                'user_id' => $user->id,
                'amount' => $generatedImage->credits_used,
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to refund credits in failed() handler', [
                'image_id' => $generatedImage->id,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}

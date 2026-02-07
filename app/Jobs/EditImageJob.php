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
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

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
            $sourceImageDataUri = $this->resolveSourceImage();
            if (!$sourceImageDataUri) {
                $this->handleFailure($generatedImage, $user, $walletService, 'Cannot load source image');
                return;
            }

            // Extract raw base64 from data URI (BFL API doesn't accept data URI prefix)
            $sourceImageBase64 = $this->extractBase64FromDataUri($sourceImageDataUri);
            $maskBase64 = $this->extractBase64FromDataUri($this->maskDataBase64);

            // Execute edit based on mode
            $result = match ($this->editMode) {
                'replace' => $bflService->editWithMask(
                    $sourceImageBase64,
                    $maskBase64,
                    $this->editPrompt
                ),
                'text' => $bflService->editText(
                    $sourceImageBase64,
                    $this->editPrompt
                ),
                'background' => $bflService->editBackground(
                    $sourceImageBase64,
                    $maskBase64,
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
                $imageContent = $this->downloadImageFromUrl((string) $result['image_url']);
                if (!empty($imageContent)) {
                    $finfo = new \finfo(FILEINFO_MIME_TYPE);
                    $mime = $finfo->buffer($imageContent) ?: 'image/png';
                    $imageBase64 = 'data:' . $mime . ';base64,' . base64_encode($imageContent);
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

        // If it's a URL, download it with timeout and SSRF guard
        if (str_starts_with($this->sourceImagePath, 'http')) {
            $content = $this->downloadImageFromUrl($this->sourceImagePath);
            if (!empty($content)) {
                $finfo = new \finfo(FILEINFO_MIME_TYPE);
                $mime = $finfo->buffer($content) ?: 'image/jpeg';
                return 'data:' . $mime . ';base64,' . base64_encode($content);
            }
            return null;
        }

        // Try to load from MinIO
        try {
            $content = Storage::disk('minio')->get($this->sourceImagePath);
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
     * Extract raw base64 string from data URI
     */
    protected function extractBase64FromDataUri(string $dataUri): string
    {
        if (empty($dataUri)) {
            return '';
        }

        if (str_contains($dataUri, ';base64,')) {
            return explode(';base64,', $dataUri)[1];
        }

        return $dataUri;
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
            $this->generatedImage->markAsFailed('Job failed: ' . $exception->getMessage());
            $user = $this->generatedImage->user;
            if ($user && $this->generatedImage->credits_used > 0) {
                $this->refundCreditsDirectly($user, $this->generatedImage);
            }
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
            $walletService = app(WalletService::class);
            $walletService->refundCredits(
                $user,
                $generatedImage->credits_used,
                'Job failed permanently',
                (string) $generatedImage->id
            );

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

    protected function downloadImageFromUrl(string $url): ?string
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }

        $scheme = parse_url($url, PHP_URL_SCHEME);
        if (!in_array($scheme, ['http', 'https'], true)) {
            return null;
        }

        $host = parse_url($url, PHP_URL_HOST);
        if (!empty($host) && $this->isPrivateOrLocalHost($host)) {
            Log::warning('EditImageJob: blocked private/local URL', ['url' => $url, 'host' => $host]);
            return null;
        }

        try {
            $response = Http::withOptions([
                'verify' => (bool) config('services_custom.bfl.verify_ssl', true),
            ])->timeout(30)->connectTimeout(10)->get($url);

            if (!$response->successful()) {
                Log::warning('EditImageJob: failed to download image', [
                    'url' => $url,
                    'status' => $response->status(),
                ]);
                return null;
            }

            $body = $response->body();
            if ($body === '') {
                return null;
            }

            $maxBytes = (int) config('services_custom.bfl.max_image_bytes', 26214400);
            if (strlen($body) > $maxBytes) {
                Log::warning('EditImageJob: image too large', [
                    'url' => $url,
                    'bytes' => strlen($body),
                    'max_bytes' => $maxBytes,
                ]);
                return null;
            }

            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $detectedMime = $finfo->buffer($body) ?: '';
            if (!str_starts_with($detectedMime, 'image/')) {
                Log::warning('EditImageJob: downloaded content is not image', [
                    'url' => $url,
                    'mime' => $detectedMime,
                ]);
                return null;
            }

            return $body;
        } catch (\Throwable $e) {
            Log::warning('EditImageJob: download image exception', [
                'url' => $url,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    protected function isPrivateOrLocalHost(string $host): bool
    {
        if (in_array(strtolower($host), ['localhost', '127.0.0.1', '::1', '0.0.0.0'], true)) {
            return true;
        }

        if (filter_var($host, FILTER_VALIDATE_IP)) {
            return filter_var(
                $host,
                FILTER_VALIDATE_IP,
                FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
            ) === false;
        }

        $ip = gethostbyname($host);
        if ($ip === $host) {
            return false;
        }

        return filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        ) === false;
    }
}

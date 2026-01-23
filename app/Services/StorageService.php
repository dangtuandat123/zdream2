<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

/**
 * StorageService
 * 
 * Xử lý việc lưu trữ ảnh vào MinIO (S3-compatible).
 * Chuyển đổi Base64 → File → Upload → Public URL.
 */
class StorageService
{
    protected string $disk;
    protected string $basePath;

    public function __construct()
    {
        $this->disk = 'minio';
        $this->basePath = 'generated-images';
    }

    /**
     * Lưu ảnh Base64 vào storage
     * 
     * @param string $base64Image Base64 encoded image (có thể có prefix data:image/...)
     * @param int $userId User ID để tạo subfolder
     * @param string|null $filename Tên file (optional, sẽ tự generate nếu không có)
     * @return array ['success' => bool, 'path' => string|null, 'url' => string|null, 'error' => string|null]
     */
    public function saveBase64Image(string $base64Image, int $userId, ?string $filename = null): array
    {
        try {
            // Remove data:image/xxx;base64, prefix nếu có
            $imageData = $this->cleanBase64($base64Image);
            
            // Decode base64
            $decodedImage = base64_decode($imageData);
            
            if ($decodedImage === false) {
                return [
                    'success' => false,
                    'error' => 'Invalid base64 data',
                ];
            }

            // Detect image type
            $extension = $this->detectImageExtension($decodedImage);
            
            // Generate filename nếu chưa có
            if (empty($filename)) {
                $filename = $this->generateFilename($extension);
            }

            // Build full path: generated-images/user-{id}/2026/01/filename.webp
            $path = $this->buildPath($userId, $filename);

            // Upload to storage
            $saved = Storage::disk($this->disk)->put($path, $decodedImage, 'public');

            if (!$saved) {
                return [
                    'success' => false,
                    'error' => 'Failed to save file to storage',
                ];
            }

            // Get public URL
            $url = Storage::disk($this->disk)->url($path);

            Log::info('Image saved to storage', [
                'path' => $path,
                'size' => strlen($decodedImage),
                'user_id' => $userId,
            ]);

            return [
                'success' => true,
                'path' => $path,
                'url' => $url,
            ];

        } catch (\Exception $e) {
            Log::error('StorageService error', [
                'message' => $e->getMessage(),
                'user_id' => $userId,
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Xóa ảnh từ storage
     */
    public function deleteImage(string $path): bool
    {
        try {
            return Storage::disk($this->disk)->delete($path);
        } catch (\Exception $e) {
            Log::warning('Failed to delete image', ['path' => $path, 'error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Lấy URL của ảnh từ path
     */
    public function getUrl(string $path): string
    {
        return Storage::disk($this->disk)->url($path);
    }

    /**
     * Kiểm tra file có tồn tại không
     */
    public function exists(string $path): bool
    {
        return Storage::disk($this->disk)->exists($path);
    }

    /**
     * Làm sạch base64 string (bỏ prefix)
     */
    protected function cleanBase64(string $base64): string
    {
        // Remove "data:image/png;base64," or similar prefixes
        if (str_contains($base64, ',')) {
            $parts = explode(',', $base64, 2);
            return $parts[1] ?? $base64;
        }
        
        return $base64;
    }

    /**
     * Detect image extension từ binary data
     */
    protected function detectImageExtension(string $binaryData): string
    {
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->buffer($binaryData);

        return match ($mimeType) {
            'image/png' => 'png',
            'image/jpeg', 'image/jpg' => 'jpg',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
            default => 'png',
        };
    }

    /**
     * Generate unique filename
     */
    protected function generateFilename(string $extension): string
    {
        $timestamp = now()->format('Ymd_His');
        $random = Str::random(8);
        return "ezshot_{$timestamp}_{$random}.{$extension}";
    }

    /**
     * Build full storage path
     * Format: generated-images/user-{id}/2026/01/filename.ext
     */
    protected function buildPath(int $userId, string $filename): string
    {
        $year = now()->format('Y');
        $month = now()->format('m');
        
        return "{$this->basePath}/user-{$userId}/{$year}/{$month}/{$filename}";
    }
}

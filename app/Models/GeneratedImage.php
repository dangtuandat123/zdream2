<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

/**
 * Model: GeneratedImage
 * Stores generated images created by users.
 *
 * @property int $id
 * @property int $user_id
 * @property int|null $style_id
 * @property string $final_prompt
 * @property array|null $selected_options
 * @property string|null $user_custom_input
 * @property array|null $generation_params
 * @property string|null $storage_path
 * @property string|null $bfl_task_id
 * @property string $status
 * @property string|null $error_message
 * @property float $credits_used
 */
class GeneratedImage extends Model
{
    use HasFactory, SoftDeletes;

    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';

    protected $fillable = [
        'user_id',
        'style_id',
        'final_prompt',
        'selected_options',
        'user_custom_input',
        'generation_params',
        'storage_path',
        'bfl_task_id',
        'status',
        'error_message',
        'credits_used',
    ];

    protected $casts = [
        'selected_options' => 'array',
        'generation_params' => 'array',
        'credits_used' => 'decimal:2',
    ];

    protected $attributes = [
        'status' => self::STATUS_PENDING,
        'credits_used' => 0,
    ];

    // =========================================
    // RELATIONSHIPS
    // =========================================

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function style(): BelongsTo
    {
        return $this->belongsTo(Style::class);
    }

    // =========================================
    // SCOPES
    // =========================================

    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopePending($query)
    {
        return $query->whereIn('status', [self::STATUS_PENDING, self::STATUS_PROCESSING]);
    }

    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    public function scopeLatest($query)
    {
        return $query->orderByDesc('created_at');
    }

    // =========================================
    // ACCESSORS
    // =========================================

    /**
     * Check whether a signed BFL URL has expired via `se` query param.
     */
    protected function isExpiredBflSignedUrl(string $url): bool
    {
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        if ($host === '' || !str_contains($host, 'bfl.ai')) {
            return false;
        }

        $query = (string) parse_url($url, PHP_URL_QUERY);
        if ($query === '') {
            return false;
        }

        parse_str($query, $params);
        $expiryRaw = $params['se'] ?? null;
        if (empty($expiryRaw)) {
            return false;
        }

        try {
            $expiry = Carbon::parse(urldecode((string) $expiryRaw));
            return now()->greaterThanOrEqualTo($expiry);
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Resolve image URL from storage_path.
     * - For full HTTP URL: return as-is, except expired signed BFL URLs.
     * - For internal path: generate cached temporary URL (preferred), then public URL fallback.
     */
    public function getImageUrlAttribute(): ?string
    {
        if (empty($this->storage_path)) {
            return null;
        }

        if (str_starts_with($this->storage_path, 'http')) {
            if ($this->isExpiredBflSignedUrl($this->storage_path)) {
                return asset('images/placeholder.svg');
            }

            return $this->storage_path;
        }

        try {
            $storagePath = $this->storage_path;
            $cacheKey = 'minio:temp_url:' . md5($storagePath);
            $cacheTtl = now()->addDays(6); // Refresh before 7-day max expiry.

            return Cache::remember($cacheKey, $cacheTtl, function () use ($storagePath) {
                $temporaryUrl = $this->buildMinioTemporaryUrl($storagePath, now()->addDays(7));

                if (!empty($temporaryUrl)) {
                    return $temporaryUrl;
                }

                return $this->buildMinioPublicUrl($storagePath) ?? asset('images/placeholder.svg');
            });
        } catch (\Exception $e) {
            Cache::forget('minio:temp_url:' . md5($this->storage_path));
            return $this->buildMinioPublicUrl($this->storage_path) ?? asset('images/placeholder.svg');
        }
    }

    /**
     * Build temporary URL from MinIO disk if driver supports it.
     */
    protected function buildMinioTemporaryUrl(string $path, Carbon $expiresAt): ?string
    {
        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('minio');

        if (!$disk instanceof FilesystemAdapter) {
            return null;
        }

        try {
            return $disk->temporaryUrl($path, $expiresAt);
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Build public URL from MinIO disk if driver supports it.
     */
    protected function buildMinioPublicUrl(string $path): ?string
    {
        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('minio');

        if ($disk instanceof FilesystemAdapter) {
            return $disk->url($path);
        }

        return null;
    }

    /**
     * Remaining days before image expiry.
     */
    public function getDaysUntilExpiryAttribute(): int
    {
        $expiryDays = (int) Setting::get('image_expiry_days', 30);
        $expiryDate = $this->created_at->addDays($expiryDays);
        $daysLeft = now()->diffInDays($expiryDate, false);

        return max(0, (int) $daysLeft);
    }

    /**
     * Whether image is expiring soon (within 7 days).
     */
    public function getIsExpiringAttribute(): bool
    {
        return $this->days_until_expiry <= 7 && $this->days_until_expiry > 0;
    }

    public function getIsCompletedAttribute(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function getIsProcessingAttribute(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_PROCESSING], true);
    }

    // =========================================
    // HELPERS
    // =========================================

    public function markAsProcessing(): void
    {
        $this->update(['status' => self::STATUS_PROCESSING]);
    }

    public function markAsCompleted(string $storagePath, ?string $bflTaskId = null): void
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'storage_path' => $storagePath,
            'bfl_task_id' => $bflTaskId,
        ]);
    }

    public function markAsFailed(string $errorMessage): void
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'error_message' => $errorMessage,
        ]);
    }
}

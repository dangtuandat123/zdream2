<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

/**
 * Model: GeneratedImage
 * 
 * Lưu lịch sử ảnh đã tạo bởi user.
 * 
 * @property int $id
 * @property int $user_id
 * @property int|null $style_id
 * @property string $final_prompt
 * @property array|null $selected_options
 * @property string|null $user_custom_input
 * @property string|null $storage_path
 * @property string|null $openrouter_id
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
        'storage_path',
        'openrouter_id',
        'status',
        'error_message',
        'credits_used',
    ];

    protected $casts = [
        'selected_options' => 'array',
        'credits_used' => 'decimal:2',
    ];

    /**
     * Default attribute values
     */
    protected $attributes = [
        'status' => self::STATUS_PENDING,
        'credits_used' => 0,
    ];

    // =========================================
    // RELATIONSHIPS
    // =========================================

    /**
     * Lấy user đã tạo ảnh này
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Lấy style đã sử dụng
     */
    public function style(): BelongsTo
    {
        return $this->belongsTo(Style::class);
    }

    // =========================================
    // SCOPES
    // =========================================

    /**
     * Scope: Lấy ảnh theo status
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope: Ảnh đã hoàn thành
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Scope: Ảnh đang xử lý
     */
    public function scopePending($query)
    {
        return $query->whereIn('status', [self::STATUS_PENDING, self::STATUS_PROCESSING]);
    }

    /**
     * Scope: Ảnh thất bại
     */
    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    /**
     * Scope: Sắp xếp mới nhất
     */
    public function scopeLatest($query)
    {
        return $query->orderByDesc('created_at');
    }

    // =========================================
    // ACCESSORS
    // =========================================

    /**
     * Lấy URL ảnh từ storage_path
     * Sử dụng temporaryUrl (pre-signed) để bypass bucket policy restrictions
     */
    public function getImageUrlAttribute(): ?string
    {
        if (empty($this->storage_path)) {
            return null;
        }

        // Nếu là URL đầy đủ (từ MinIO)
        if (str_starts_with($this->storage_path, 'http')) {
            return $this->storage_path;
        }

        // Sử dụng temporaryUrl (pre-signed URL) với expiry 24h
        // Điều này bypass bucket policy và cho phép public access
        try {
            return Storage::disk('minio')->temporaryUrl(
                $this->storage_path,
                now()->addHours(24)
            );
        } catch (\Exception $e) {
            // Fallback to regular URL nếu temporaryUrl không khả dụng
            return Storage::disk('minio')->url($this->storage_path);
        }
    }

    /**
     * Kiểm tra ảnh có hoàn thành không
     */
    public function getIsCompletedAttribute(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Kiểm tra ảnh có đang xử lý không
     */
    public function getIsProcessingAttribute(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_PROCESSING]);
    }

    // =========================================
    // HELPER METHODS
    // =========================================

    /**
     * Đánh dấu đang xử lý
     */
    public function markAsProcessing(): void
    {
        $this->update(['status' => self::STATUS_PROCESSING]);
    }

    /**
     * Đánh dấu hoàn thành
     */
    public function markAsCompleted(string $storagePath, ?string $openrouterId = null): void
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'storage_path' => $storagePath,
            'openrouter_id' => $openrouterId,
        ]);
    }

    /**
     * Đánh dấu thất bại
     */
    public function markAsFailed(string $errorMessage): void
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'error_message' => $errorMessage,
        ]);
    }
}

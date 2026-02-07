<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model: StyleOption
 * 
 * Đại diện cho một tùy chọn bổ sung của Style.
 * User có thể chọn nhiều options để customize prompt.
 * 
 * @property int $id
 * @property int $style_id
 * @property string $label
 * @property string $group_name
 * @property string $prompt_fragment
 * @property string|null $icon
 * @property int $sort_order
 * @property bool $is_default
 */
class StyleOption extends Model
{
    use HasFactory;

    protected $fillable = [
        'style_id',
        'label',
        'group_name',
        'prompt_fragment',
        'icon',
        'thumbnail',
        'sort_order',
        'is_default',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_default' => 'boolean',
    ];

    /**
     * Default attribute values
     */
    protected $attributes = [
        'sort_order' => 0,
        'is_default' => false,
    ];

    // =========================================
    // ACCESSORS
    // =========================================

    /**
     * Lấy URL của thumbnail (local storage)
     * Dùng relative URL để không phụ thuộc APP_URL
     */
    public function getThumbnailUrlAttribute(): ?string
    {
        if (empty($this->thumbnail)) {
            return null;
        }

        $value = (string) $this->thumbnail;
        if (filter_var($value, FILTER_VALIDATE_URL)) {
            $host = strtolower((string) parse_url($value, PHP_URL_HOST));
            $placeholderHosts = [
                'cdn.yourservice.com',
                'cdn.example.com',
                'yourservice.com',
                'example.com',
            ];

            if (in_array($host, $placeholderHosts, true)) {
                return '/images/placeholder.svg';
            }

            return $value;
        }

        if (str_starts_with($value, '/storage/')) {
            return $value;
        }

        return '/storage/' . ltrim($value, '/');
    }

    // =========================================
    // RELATIONSHIPS
    // =========================================

    /**
     * Lấy Style mà option này thuộc về
     */
    public function style(): BelongsTo
    {
        return $this->belongsTo(Style::class);
    }

    // =========================================
    // SCOPES
    // =========================================

    /**
     * Scope: Lấy theo group
     */
    public function scopeInGroup($query, string $groupName)
    {
        return $query->where('group_name', $groupName);
    }

    /**
     * Scope: Sắp xếp theo thứ tự
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    /**
     * Scope: Chỉ lấy options mặc định
     */
    public function scopeDefaults($query)
    {
        return $query->where('is_default', true);
    }
}

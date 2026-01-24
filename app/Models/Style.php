<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * Model: Style
 * 
 * Đại diện cho một "công thức" tạo ảnh AI.
 * Mỗi Style chứa cấu hình OpenRouter và các options bổ sung.
 * 
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $thumbnail_url
 * @property string|null $description
 * @property float $price
 * @property string $openrouter_model_id
 * @property string $base_prompt
 * @property array|null $config_payload
 * @property bool $is_active
 * @property bool $allow_user_custom_prompt
 * @property int $sort_order
 */
class Style extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'thumbnail_url',
        'description',
        'price',
        'openrouter_model_id',
        'base_prompt',
        'config_payload',
        'is_active',
        'allow_user_custom_prompt',
        'image_slots',
        'system_images',
        'sort_order',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'config_payload' => 'array',
        'image_slots' => 'array',
        'system_images' => 'array',
        'is_active' => 'boolean',
        'allow_user_custom_prompt' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Defaults - đảm bảo không bị undefined khi create
     */
    protected $attributes = [
        'is_active' => true,
        'allow_user_custom_prompt' => false,
        'sort_order' => 0,
    ];

    // =========================================
    // BOOT (Auto-generate slug)
    // =========================================

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($style) {
            if (empty($style->slug)) {
                $baseSlug = Str::slug($style->name);
                $slug = $baseSlug;
                $counter = 1;
                
                // Đảm bảo slug unique
                while (static::where('slug', $slug)->exists()) {
                    $slug = $baseSlug . '-' . $counter;
                    $counter++;
                }
                
                $style->slug = $slug;
            }
        });

        static::updating(function ($style) {
            if ($style->isDirty('name') && !$style->isDirty('slug')) {
                $style->slug = Str::slug($style->name);
            }
        });
    }

    // =========================================
    // RELATIONSHIPS
    // =========================================

    /**
     * Lấy tất cả options của style này
     */
    public function options(): HasMany
    {
        return $this->hasMany(StyleOption::class)->orderBy('group_name')->orderBy('sort_order');
    }

    /**
     * Lấy tất cả ảnh đã tạo với style này
     */
    public function generatedImages(): HasMany
    {
        return $this->hasMany(GeneratedImage::class);
    }

    // =========================================
    // SCOPES
    // =========================================

    /**
     * Scope: Chỉ lấy style đang active
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Sắp xếp theo sort_order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    // =========================================
    // ACCESSORS
    // =========================================

    /**
     * Lấy URL thumbnail (với fallback)
     */
    public function getThumbnailAttribute(): string
    {
        return $this->thumbnail_url 
            ?? 'https://via.placeholder.com/400x600/1e1e32/ffffff?text=' . urlencode($this->name);
    }

    /**
     * Lấy danh sách options theo nhóm
     */
    public function getOptionsGroupedAttribute(): array
    {
        return $this->options->groupBy('group_name')->toArray();
    }

    /**
     * Lấy aspect ratio từ config_payload
     */
    public function getAspectRatioAttribute(): string
    {
        return $this->config_payload['aspect_ratio'] ?? '1:1';
    }

    // =========================================
    // HELPER METHODS
    // =========================================

    /**
     * Build prompt cuối cùng từ base + selected options + user input
     * 
     * @param array $selectedOptionIds Danh sách ID của StyleOption đã chọn
     * @param string|null $userCustomInput Nội dung user tự gõ
     * @return string Final prompt để gửi đến OpenRouter
     */
    public function buildFinalPrompt(array $selectedOptionIds = [], ?string $userCustomInput = null): string
    {
        $prompt = $this->base_prompt;

        // Nối các prompt fragments từ options đã chọn
        if (!empty($selectedOptionIds)) {
            $options = $this->options()->whereIn('id', $selectedOptionIds)->get();
            foreach ($options as $option) {
                $prompt .= $option->prompt_fragment;
            }
        }

        // Nối user custom input (nếu được phép và có nội dung)
        if ($this->allow_user_custom_prompt && !empty($userCustomInput)) {
            $prompt .= ', ' . trim($userCustomInput);
        }

        return $prompt;
    }

    /**
     * Build OpenRouter payload
     * 
     * Lưu ý: image_config chỉ hỗ trợ cho Gemini models
     * FLUX và các model khác không cần/hỗ trợ image_config
     */
    public function buildOpenRouterPayload(string $finalPrompt): array
    {
        $payload = [
            'model' => $this->openrouter_model_id,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $finalPrompt,
                ],
            ],
            'modalities' => ['image', 'text'], // Bắt buộc cho image generation
        ];

        // Thêm image_config CHỈ cho Gemini models (theo OpenRouter docs)
        $isGeminiModel = str_contains(strtolower($this->openrouter_model_id), 'gemini');
        
        if ($isGeminiModel && !empty($this->config_payload)) {
            $payload['image_config'] = $this->config_payload;
        }

        return $payload;
    }
}

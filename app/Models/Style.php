<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * Model: Style
 * 
 * Đại diện cho một "công thức" tạo ảnh AI.
 * Mỗi Style chứa cấu hình BFL (FLUX) và các options bổ sung.
 * 
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $thumbnail_url
 * @property string|null $description
 * @property float $price
 * @property string $bfl_model_id
 * @property string $base_prompt
 * @property array|null $config_payload
 * @property bool $is_active
 * @property int|null $tag_id
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
        'bfl_model_id',
        'base_prompt',
        'config_payload',
        'is_active',
        'tag_id',
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
        'tag_id' => 'integer',
        'allow_user_custom_prompt' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Defaults - đảm bảo không bị undefined khi create
     */
    protected $attributes = [
        'is_active' => true,
        'tag_id' => null,
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

        // MEDIUM-02 FIX: Removed auto slug update on 'updating' event
        // Lý do: Tự đổi slug khi name thay đổi có thể gây:
        // 1. Unique constraint violation nếu slug mới đã tồn tại
        // 2. URL cũ bị broken (bookmark, SEO, shared links)
        // Admin phải tự cập nhật slug nếu muốn đổi
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

    /**
     * Lấy tag của style này
     */
    public function tag(): BelongsTo
    {
        return $this->belongsTo(Tag::class);
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
     * LOW-01 FIX: Safe null check để tránh warning khi config_payload là null
     */
    public function getAspectRatioAttribute(): string
    {
        return $this->config_payload['aspect_ratio'] ?? '1:1';
    }

    // =========================================
    // HELPER METHODS
    // =========================================

    /**
     * Max prompt length to avoid API rejection và memory issues
     */
    const MAX_PROMPT_LENGTH = 4000;
    
    /**
     * Standard separator giữa các prompt fragments
     */
    const PROMPT_SEPARATOR = ', ';

    /**
     * Build prompt cuối cùng từ base + selected options + user input
     * 
     * IMPROVED: Tự động normalize separator, giới hạn length, sanitize
     * 
     * @param array $selectedOptionIds Danh sách ID của StyleOption đã chọn
     * @param string|null $userCustomInput Nội dung user tự gõ
     * @return string Final prompt để gửi đến BFL
     */
    public function buildFinalPrompt(array $selectedOptionIds = [], ?string $userCustomInput = null): string
    {
        $prompt = trim($this->base_prompt);
        $fragments = [];

        // Collect all option fragments
        if (!empty($selectedOptionIds)) {
            $options = $this->options()
                ->whereIn('id', $selectedOptionIds)
                ->orderBy('sort_order')
                ->get();
                
            foreach ($options as $option) {
                $fragment = $this->normalizePromptFragment($option->prompt_fragment);
                if (!empty($fragment)) {
                    $fragments[] = $fragment;
                }
            }
        }

        // Add user custom input (nếu được phép và có nội dung)
        if ($this->allow_user_custom_prompt && !empty($userCustomInput)) {
            $fragment = $this->normalizePromptFragment($userCustomInput);
            if (!empty($fragment)) {
                $fragments[] = $fragment;
            }
        }

        // Join all fragments with separator
        if (!empty($fragments)) {
            // Đảm bảo base_prompt không kết thúc bằng separator
            $prompt = rtrim($prompt, ', ;');
            $prompt .= self::PROMPT_SEPARATOR . implode(self::PROMPT_SEPARATOR, $fragments);
        }

        // Enforce max length để tránh API rejection
        if (mb_strlen($prompt) > self::MAX_PROMPT_LENGTH) {
            $prompt = mb_substr($prompt, 0, self::MAX_PROMPT_LENGTH - 3) . '...';
        }

        return $prompt;
    }

    /**
     * Normalize một prompt fragment
     * - Trim whitespace
     * - Loại bỏ leading separators (sẽ được thêm lại khi join)
     * - Sanitize basic
     */
    protected function normalizePromptFragment(string $fragment): string
    {
        // Trim whitespace
        $fragment = trim($fragment);
        
        if (empty($fragment)) {
            return '';
        }
        
        // Loại bỏ leading separators (comma, semicolon, space)
        $fragment = ltrim($fragment, ', ;');
        $fragment = trim($fragment);
        
        return $fragment;
    }


    /**
     * Legacy: Build OpenRouter payload (không dùng trong luồng BFL)
     *
     * Lưu ý: image_config chỉ hỗ trợ cho Gemini models
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

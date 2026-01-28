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
        $config = is_array($this->config_payload) ? $this->config_payload : [];
        $modelId = strtolower((string) ($this->bfl_model_id ?? ''));

        $fragmentsBySlot = $this->collectPromptFragmentsBySlot($selectedOptionIds, $userCustomInput);
        $template = trim((string) ($config['prompt_template'] ?? ''));
        $strategy = trim((string) ($config['prompt_strategy'] ?? ''));

        if ($template !== '') {
            $prompt = $this->applyPromptTemplate($template, $prompt, $fragmentsBySlot);
        } else {
            $orderedFragments = $this->buildOrderedPromptFragments($modelId, $strategy, $prompt, $fragmentsBySlot);
            $prompt = implode(self::PROMPT_SEPARATOR, $orderedFragments);
        }

        $prefix = trim((string) ($config['prompt_prefix'] ?? ''));
        $suffix = trim((string) ($config['prompt_suffix'] ?? ''));

        if ($prefix !== '') {
            $prompt = $prefix . self::PROMPT_SEPARATOR . ltrim($prompt, ', ;');
        }
        if ($suffix !== '') {
            $prompt = rtrim($prompt, ', ;') . self::PROMPT_SEPARATOR . $suffix;
        }

        $prompt = $this->cleanupPrompt($prompt);

        // Enforce max length để tránh API rejection
        if (mb_strlen($prompt) > self::MAX_PROMPT_LENGTH) {
            $prompt = mb_substr($prompt, 0, self::MAX_PROMPT_LENGTH - 3) . '...';
        }

        return $prompt;
    }

    /**
     * Collect prompt fragments and group by semantic slots (subject/action/style/context/etc.)
     */
    protected function collectPromptFragmentsBySlot(array $selectedOptionIds = [], ?string $userCustomInput = null): array
    {
        $slots = $this->getPromptSlots();
        $fragmentsBySlot = array_fill_keys($slots, []);
        $config = is_array($this->config_payload) ? $this->config_payload : [];
        $defaults = $config['prompt_defaults'] ?? [];

        if (is_array($defaults)) {
            foreach ($slots as $slot) {
                $defaultValue = $defaults[$slot] ?? '';
                if ($defaultValue !== null && $defaultValue !== '') {
                    $fragment = $this->normalizePromptFragment((string) $defaultValue);
                    if ($fragment !== '') {
                        $fragmentsBySlot[$slot][] = $fragment;
                    }
                }
            }
        }

        if (!empty($selectedOptionIds)) {
            $options = $this->options()
                ->whereIn('id', $selectedOptionIds)
                ->orderBy('sort_order')
                ->get();

            foreach ($options as $option) {
                $fragment = $this->normalizePromptFragment($option->prompt_fragment);
                if ($fragment === '') {
                    continue;
                }
                $slot = $this->classifyGroupName((string) $option->group_name);
                $fragmentsBySlot[$slot][] = $fragment;
            }
        }

        if ($this->allow_user_custom_prompt && !empty($userCustomInput)) {
            $fragment = $this->normalizePromptFragment($userCustomInput);
            if ($fragment !== '') {
                $fragmentsBySlot['custom'][] = $fragment;
            }
        }

        return $fragmentsBySlot;
    }

    /**
     * Prompt slots that we support.
     */
    protected function getPromptSlots(): array
    {
        return [
            'subject',
            'action',
            'style',
            'context',
            'mood',
            'lighting',
            'color',
            'details',
            'technical',
            'custom',
            'misc',
        ];
    }

    /**
     * Map group_name -> semantic slot.
     */
    protected function classifyGroupName(string $groupName): string
    {
        $normalized = strtolower(Str::ascii($groupName));
        $normalized = preg_replace('/\s+/', ' ', trim($normalized));

        $match = function (array $keywords) use ($normalized): bool {
            foreach ($keywords as $keyword) {
                if (str_contains($normalized, $keyword)) {
                    return true;
                }
            }
            return false;
        };

        if ($match(['chu the', 'nhan vat', 'doi tuong', 'subject', 'character', 'person', 'animal'])) {
            return 'subject';
        }
        if ($match(['hanh dong', 'action', 'pose', 'tu the', 'cu chi', 'gesture'])) {
            return 'action';
        }
        if ($match(['phong cach', 'style', 'art', 'aesthetic', 'vibe', 'trend'])) {
            return 'style';
        }
        if ($match(['boi canh', 'background', 'scene', 'setting', 'dia diem', 'moi truong', 'khung canh'])) {
            return 'context';
        }
        if ($match(['cam xuc', 'mood', 'tone', 'khong khi', 'atmosphere'])) {
            return 'mood';
        }
        if ($match(['anh sang', 'lighting', 'light'])) {
            return 'lighting';
        }
        if ($match(['mau', 'color', 'palette', 'hex'])) {
            return 'color';
        }
        if ($match(['chi tiet', 'detail', 'texture', 'chat lieu', 'material', 'pattern'])) {
            return 'details';
        }
        if ($match(['ky thuat', 'technical', 'camera', 'lens', 'photography', 'render', 'do net', 'resolution', 'quality'])) {
            return 'technical';
        }

        return 'misc';
    }

    /**
     * Build ordered fragments based on model strategy.
     */
    protected function buildOrderedPromptFragments(string $modelId, string $strategy, string $basePrompt, array $fragmentsBySlot): array
    {
        $isKlein = str_contains($modelId, 'klein');
        $isNarrative = $strategy === 'narrative' || $isKlein;

        $slotText = [];
        foreach ($fragmentsBySlot as $slot => $fragments) {
            if (!empty($fragments)) {
                $slotText[$slot] = implode(self::PROMPT_SEPARATOR, $fragments);
            }
        }

        $order = $isNarrative
            ? ['context', 'subject', 'action', 'mood', 'style', 'lighting', 'color', 'details', 'technical', 'custom', 'misc']
            : ['subject', 'action', 'style', 'context', 'mood', 'lighting', 'color', 'details', 'technical', 'custom', 'misc'];

        $parts = [];
        if (!empty($basePrompt)) {
            $parts[] = $basePrompt;
        }

        foreach ($order as $slot) {
            if (!empty($slotText[$slot])) {
                $parts[] = $slotText[$slot];
            }
        }

        return $parts;
    }

    /**
     * Apply prompt template with placeholders like {{subject}}, {{style}}, {{context}}, {{custom}}, {{base}}
     */
    protected function applyPromptTemplate(string $template, string $basePrompt, array $fragmentsBySlot): string
    {
        $slotText = [];
        foreach ($fragmentsBySlot as $slot => $fragments) {
            $slotText[$slot] = !empty($fragments)
                ? implode(self::PROMPT_SEPARATOR, $fragments)
                : '';
        }

        $replacements = array_merge([
            '{{base}}' => $basePrompt,
        ], collect($slotText)->mapWithKeys(fn ($value, $slot) => ['{{' . $slot . '}}' => $value])->toArray());

        $prompt = strtr($template, $replacements);

        return $this->cleanupPrompt($prompt);
    }

    /**
     * Cleanup prompt: remove double separators and trim.
     */
    protected function cleanupPrompt(string $prompt): string
    {
        $prompt = preg_replace('/\s+,/', ',', $prompt);
        $prompt = preg_replace('/,+/', ',', $prompt);
        $prompt = preg_replace('/\s{2,}/', ' ', $prompt);
        $prompt = trim($prompt);
        $prompt = trim($prompt, ',;');

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

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model: Tag
 * 
 * Tag cho styles (HOT, MỚI, SALE, etc.)
 * 
 * @property int $id
 * @property string $name
 * @property string $color_from
 * @property string $color_to
 * @property string $icon
 * @property int $sort_order
 * @property bool $is_active
 */
class Tag extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'color_from',
        'color_to',
        'icon',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected $attributes = [
        'color_from' => 'orange-500',
        'color_to' => 'red-500',
        'icon' => 'fa-fire',
        'sort_order' => 0,
        'is_active' => true,
    ];

    // =========================================
    // RELATIONSHIPS
    // =========================================

    /**
     * Lấy tất cả styles có tag này
     */
    public function styles(): HasMany
    {
        return $this->hasMany(Style::class);
    }

    // =========================================
    // SCOPES
    // =========================================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    // =========================================
    // ACCESSORS
    // =========================================

    /**
     * Lấy gradient class cho Tailwind
     */
    public function getGradientClassAttribute(): string
    {
        return "from-{$this->color_from} to-{$this->color_to}";
    }

    /**
     * Lấy full icon class
     */
    public function getIconClassAttribute(): string
    {
        return "fa-solid {$this->icon}";
    }
}

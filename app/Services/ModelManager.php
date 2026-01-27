<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * ModelManager Service
 * 
 * Quản lý AI models với cache và helper methods.
 * Tách logic cache khỏi BflService để dễ test và reuse.
 */
class ModelManager
{
    protected BflService $bflService;
    protected int $cacheTimeout = 3600; // 1 hour

    public function __construct(BflService $bflService)
    {
        $this->bflService = $bflService;
    }

    /**
     * Fetch models với cache management
     * 
     * @param bool $forceRefresh Bỏ qua cache và fetch mới
     * @return array Danh sách models
     */
    public function fetchModels(bool $forceRefresh = false): array
    {
        $cacheKey = 'bfl_models';

        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, $this->cacheTimeout, function () use ($forceRefresh) {
            $models = $this->bflService->getAvailableModels();
            return array_map(function (array $model) {
                $model['supports_text_input'] = $model['supports_text_input'] ?? true;
                $model['supports_image_input'] = $model['supports_image_input'] ?? false;
                $model['supports_aspect_ratio'] = $model['supports_aspect_ratio'] ?? false;
                $model['supports_width_height'] = $model['supports_width_height'] ?? false;
                $model['supports_seed'] = $model['supports_seed'] ?? false;
                $model['supports_steps'] = $model['supports_steps'] ?? false;
                $model['supports_guidance'] = $model['supports_guidance'] ?? false;
                $model['supports_prompt_upsampling'] = $model['supports_prompt_upsampling'] ?? false;
                $model['supports_output_format'] = $model['supports_output_format'] ?? false;
                $model['supports_safety_tolerance'] = $model['supports_safety_tolerance'] ?? false;
                $model['supports_raw'] = $model['supports_raw'] ?? false;
                $model['supports_image_prompt_strength'] = $model['supports_image_prompt_strength'] ?? false;
                $model['output_formats'] = $model['output_formats'] ?? ['jpeg', 'png'];
                $model['safety_tolerance'] = $model['safety_tolerance'] ?? ['min' => 0, 'max' => 6, 'default' => 2];
                $model['steps'] = $model['steps'] ?? null;
                $model['guidance'] = $model['guidance'] ?? null;
                $model['image_prompt_strength'] = $model['image_prompt_strength'] ?? null;
                $model['estimated_cost_per_image'] = $model['estimated_cost_per_image'] ?? null;
                return $model;
            }, $models);
        });
    }

    /**
     * Lấy model theo ID
     */
    public function getById(string $modelId): ?array
    {
        $models = $this->fetchModels();
        
        foreach ($models as $model) {
            if ($model['id'] === $modelId) {
                return $model;
            }
        }
        
        return null;
    }

    /**
     * Filter models theo price range
     * 
     * @param float $minPrice Minimum cost per image (USD)
     * @param float $maxPrice Maximum cost per image (USD)
     * @return array Filtered models
     */
    public function getByPriceRange(float $minPrice, float $maxPrice): array
    {
        $models = $this->fetchModels();
        
        return array_filter($models, function ($model) use ($minPrice, $maxPrice) {
            $cost = $model['estimated_cost_per_image'] ?? 999;
            return $cost >= $minPrice && $cost <= $maxPrice;
        });
    }

    /**
     * Lấy các models phổ biến (hardcoded list)
     * 
     * @return array Popular models
     */
    public function getPopularModels(): array
    {
        $models = $this->fetchModels();
        
        // Danh sách ID models phổ biến
        $popularIds = [
            'flux-2-pro',
            'flux-2-max',
            'flux-2-flex',
            'flux-kontext-pro',
            'flux-pro-1.1',
            'flux-dev',
        ];
        
        return array_filter($models, function ($model) use ($popularIds) {
            return in_array($model['id'], $popularIds);
        });
    }

    /**
     * Group models by provider (Google, OpenAI, Black Forest Labs, etc.)
     * 
     * @param array|null $models Danh sách models (null = fetch all)
     * @return array Grouped models: ['Google' => [...], 'OpenAI' => [...]]
     */
    public function groupByProvider(?array $models = null): array
    {
        if ($models === null) {
            $models = $this->fetchModels();
        }

        $grouped = [];

        foreach ($models as $model) {
            $provider = $this->detectProvider($model['id']);
            
            if (!isset($grouped[$provider])) {
                $grouped[$provider] = [];
            }
            
            $grouped[$provider][] = $model;
        }

        // Sort providers alphabetically
        ksort($grouped);

        return $grouped;
    }

    /**
     * Detect provider từ model ID
     */
    protected function detectProvider(string $modelId): string
    {
        $modelLower = strtolower($modelId);

        if (str_contains($modelLower, 'flux')) {
            return 'Black Forest Labs';
        }

        return 'Other';
    }

    /**
     * Format cost cho display
     * 
     * @param float $cost Cost in USD
     * @return string Formatted string: "$0.0012" or "Free"
     */
    public function formatCost(float $cost): string
    {
        if ($cost <= 0) {
            return 'Free';
        }

        if ($cost < 0.0001) {
            return '< $0.0001';
        }

        return '$' . number_format($cost, 4);
    }

    /**
     * Clear cache
     */
    public function clearCache(): void
    {
        Cache::forget('bfl_models');
        Log::info('ModelManager cache cleared');
    }

    /**
     * Get cache info
     */
    public function getCacheInfo(): array
    {
        $cacheKey = 'bfl_models';
        $hasCachedData = Cache::has($cacheKey);

        return [
            'has_cache' => $hasCachedData,
            'cache_timeout' => $this->cacheTimeout,
            'cache_key' => $cacheKey,
        ];
    }
}

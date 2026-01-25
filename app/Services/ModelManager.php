<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * ModelManager Service
 * 
 * Quản lý AI models với cache và helper methods.
 * Tách logic cache khỏi OpenRouterService để dễ test và reuse.
 */
class ModelManager
{
    protected OpenRouterService $openRouterService;
    protected int $cacheTimeout = 3600; // 1 hour

    public function __construct(OpenRouterService $openRouterService)
    {
        $this->openRouterService = $openRouterService;
    }

    /**
     * Fetch models với cache management
     * 
     * @param bool $forceRefresh Bỏ qua cache và fetch mới
     * @return array Danh sách models
     */
    public function fetchModels(bool $forceRefresh = false): array
    {
        $cacheKey = 'openrouter_models_enhanced';

        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, $this->cacheTimeout, function () use ($forceRefresh) {
            return $this->openRouterService->fetchImageModels($forceRefresh);
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
            'google/gemini-2.5-flash-image-preview',
            'google/gemini-3-pro-image-preview',
            'black-forest-labs/flux.2-pro',
            'black-forest-labs/flux.2-flex',
            'black-forest-labs/flux-1.1-pro',
            'black-forest-labs/flux-schnell',
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

        if (str_contains($modelLower, 'google/') || str_contains($modelLower, 'gemini')) {
            return 'Google';
        }

        if (str_contains($modelLower, 'openai/') || str_contains($modelLower, 'gpt') || str_contains($modelLower, 'dall-e')) {
            return 'OpenAI';
        }

        if (str_contains($modelLower, 'black-forest-labs/') || str_contains($modelLower, 'flux')) {
            return 'Black Forest Labs';
        }

        if (str_contains($modelLower, 'stability') || str_contains($modelLower, 'stable-diffusion') || str_contains($modelLower, 'sdxl')) {
            return 'Stability AI';
        }

        if (str_contains($modelLower, 'ideogram')) {
            return 'Ideogram';
        }

        if (str_contains($modelLower, 'recraft')) {
            return 'Recraft';
        }

        if (str_contains($modelLower, 'riverflow') || str_contains($modelLower, 'sourceful')) {
            return 'Sourceful';
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
        Cache::forget('openrouter_models_enhanced');
        Log::info('ModelManager cache cleared');
    }

    /**
     * Get cache info
     */
    public function getCacheInfo(): array
    {
        $cacheKey = 'openrouter_models_enhanced';
        $hasCachedData = Cache::has($cacheKey);

        return [
            'has_cache' => $hasCachedData,
            'cache_timeout' => $this->cacheTimeout,
            'cache_key' => $cacheKey,
        ];
    }
}

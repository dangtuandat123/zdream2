<?php

namespace App\Services\ImageGeneration;

use Illuminate\Support\Facades\Log;

/**
 * Factory for creating and selecting the appropriate model adapter
 * 
 * Adapters are checked in order of specificity:
 * 1. GeminiAdapter (Gemini models)
 * 2. FluxAdapter (FLUX models)  
 * 3. GptImageAdapter (GPT image models)
 * 4. GenericAdapter (fallback)
 */
class ModelAdapterFactory
{
    /**
     * Registered adapters in priority order
     * @var ModelAdapterInterface[]
     */
    protected array $adapters = [];

    public function __construct()
    {
        // Register adapters in order of specificity
        $this->adapters = [
            new GeminiAdapter(),
            new FluxAdapter(),
            new GptImageAdapter(),
            new GenericAdapter(), // Always last as fallback
        ];
    }

    /**
     * Get the appropriate adapter for the given model ID
     */
    public function getAdapter(string $modelId): ModelAdapterInterface
    {
        foreach ($this->adapters as $adapter) {
            // Skip generic adapter until we've tried all others
            if ($adapter instanceof GenericAdapter) {
                continue;
            }
            
            if ($adapter->supports($modelId)) {
                Log::debug('ModelAdapterFactory: Selected adapter', [
                    'model' => $modelId,
                    'adapter' => $adapter->getModelType(),
                ]);
                return $adapter;
            }
        }
        
        // Fallback to generic
        $generic = new GenericAdapter();
        Log::debug('ModelAdapterFactory: Using generic adapter', [
            'model' => $modelId,
        ]);
        
        return $generic;
    }

    /**
     * Register a custom adapter (will be checked first)
     */
    public function registerAdapter(ModelAdapterInterface $adapter): void
    {
        // Insert before GenericAdapter
        array_splice($this->adapters, -1, 0, [$adapter]);
    }

    /**
     * Get all registered adapters
     * @return ModelAdapterInterface[]
     */
    public function getAdapters(): array
    {
        return $this->adapters;
    }

    /**
     * Check if a model ID has a specific (non-generic) adapter
     */
    public function hasSpecificAdapter(string $modelId): bool
    {
        foreach ($this->adapters as $adapter) {
            if ($adapter instanceof GenericAdapter) {
                continue;
            }
            
            if ($adapter->supports($modelId)) {
                return true;
            }
        }
        
        return false;
    }
}

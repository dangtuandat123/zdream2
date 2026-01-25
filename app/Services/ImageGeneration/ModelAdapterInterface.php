<?php

namespace App\Services\ImageGeneration;

/**
 * Interface for model-specific image generation adapters
 * 
 * Each adapter handles:
 * - Payload preparation (model-specific params)
 * - Response parsing (different response formats)
 * - Default options (aspect ratio, size, etc.)
 */
interface ModelAdapterInterface
{
    /**
     * Check if this adapter supports the given model ID
     */
    public function supports(string $modelId): bool;

    /**
     * Prepare the API payload with model-specific parameters
     * 
     * @param array $basePayload The base payload from Style::buildOpenRouterPayload()
     * @param array $options Options like aspectRatio, imageSize, inputImages
     * @return array Modified payload ready for API call
     */
    public function preparePayload(array $basePayload, array $options = []): array;

    /**
     * Parse the API response to extract base64 image
     * 
     * @param array|null $response The API response data
     * @return string|null Base64 image data URL or null if not found
     */
    public function parseResponse(?array $response): ?string;

    /**
     * Extract text response when model returns text instead of image
     * 
     * @param array|null $response The API response data
     * @return string|null Text content or null
     */
    public function extractTextResponse(?array $response): ?string;

    /**
     * Get default options for this model type
     * 
     * @return array Default options like aspectRatio, imageSize
     */
    public function getDefaultOptions(): array;

    /**
     * Check if this model type supports image_config
     */
    public function supportsImageConfig(): bool;

    /**
     * Get model type identifier for logging/debugging
     */
    public function getModelType(): string;
}

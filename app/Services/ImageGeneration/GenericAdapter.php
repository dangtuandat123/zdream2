<?php

namespace App\Services\ImageGeneration;

/**
 * Generic fallback adapter for unknown image generation models
 * 
 * Features:
 * - Basic modalities only
 * - Multi-path response parsing
 * - Aspect ratio via prompt text
 */
class GenericAdapter extends BaseAdapter
{
    public function supports(string $modelId): bool
    {
        // Generic adapter supports everything as fallback
        return true;
    }

    public function preparePayload(array $basePayload, array $options = []): array
    {
        $payload = $basePayload;
        
        // Ensure modalities
        $payload['modalities'] = ['image', 'text'];
        
        // Generic: append aspect ratio to prompt
        if (!empty($options['aspectRatio'])) {
            $aspectText = ", {$options['aspectRatio']} aspect ratio";
            
            if (is_string($payload['messages'][0]['content'])) {
                $payload['messages'][0]['content'] .= $aspectText;
            } else if (isset($payload['messages'][0]['content'][0]['text'])) {
                $payload['messages'][0]['content'][0]['text'] .= $aspectText;
            }
        }
        
        // Add input images if provided
        if (!empty($options['inputImages'])) {
            $payload = $this->addInputImagesToPayload(
                $payload, 
                $options['inputImages'],
                $options['slotDescriptions'] ?? []
            );
        }
        
        return $payload;
    }

    public function parseResponse(?array $response): ?string
    {
        if (empty($response)) {
            return null;
        }

        $choices = $response['choices'] ?? [];
        $message = $choices[0]['message'] ?? [];

        // Try all known paths for image extraction
        
        // Path 1: message.images[]
        if (!empty($message['images'])) {
            $image = $message['images'][0];
            
            // Try various formats
            $paths = [
                ['image_url', 'url'],
                ['imageUrl', 'url'],
                ['url'],
                ['data'],
            ];
            
            foreach ($paths as $path) {
                $value = $this->getNestedValue($image, $path);
                if ($value) {
                    $normalized = $this->normalizeImageValue($value);
                    if ($normalized) {
                        return $normalized;
                    }
                }
            }
            
            // Direct string
            if (is_string($image)) {
                return $this->normalizeImageValue($image);
            }
        }

        // Path 2: message.content (array with image parts)
        if (!empty($message['content']) && is_array($message['content'])) {
            foreach ($message['content'] as $part) {
                if (isset($part['type']) && in_array($part['type'], ['image_url', 'image'])) {
                    $url = $part['image_url']['url'] 
                        ?? ($part['imageUrl']['url'] 
                        ?? ($part['image']['url'] 
                        ?? ($part['url'] ?? null)));
                    
                    if ($url) {
                        return $this->normalizeImageValue($url);
                    }
                }
            }
        }

        // Path 3: message.content as base64 string
        if (!empty($message['content']) && is_string($message['content'])) {
            $normalized = $this->normalizeImageValue($message['content']);
            if ($normalized && str_starts_with($normalized, 'data:image/')) {
                return $normalized;
            }
        }

        return null;
    }

    /**
     * Get nested value from array by path
     */
    protected function getNestedValue($array, array $path)
    {
        $current = $array;
        foreach ($path as $key) {
            if (!is_array($current) || !isset($current[$key])) {
                return null;
            }
            $current = $current[$key];
        }
        return $current;
    }

    public function getDefaultOptions(): array
    {
        return [
            'aspectRatio' => '1:1',
        ];
    }

    public function supportsImageConfig(): bool
    {
        return false;
    }

    public function getModelType(): string
    {
        return 'generic';
    }
}

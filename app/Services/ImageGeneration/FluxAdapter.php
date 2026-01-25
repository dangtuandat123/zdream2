<?php

namespace App\Services\ImageGeneration;

/**
 * Adapter for FLUX image generation models (Black Forest Labs)
 * 
 * Features:
 * - No image_config support
 * - Aspect ratio appended to prompt text
 */
class FluxAdapter extends BaseAdapter
{
    public function supports(string $modelId): bool
    {
        $modelLower = strtolower($modelId);
        return str_contains($modelLower, 'flux');
    }

    public function preparePayload(array $basePayload, array $options = []): array
    {
        $payload = $basePayload;
        
        // Ensure modalities
        $payload['modalities'] = ['image', 'text'];
        
        // FLUX doesn't support image_config, append to prompt
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

        // Same format as Gemini
        if (!empty($message['images'])) {
            $image = $message['images'][0];
            
            if (is_array($image) && isset($image['image_url']['url'])) {
                return $this->normalizeImageValue($image['image_url']['url']);
            }
            
            if (is_array($image) && isset($image['url'])) {
                return $this->normalizeImageValue($image['url']);
            }
            
            if (is_string($image)) {
                return $this->normalizeImageValue($image);
            }
        }

        // Check content
        if (!empty($message['content']) && is_string($message['content'])) {
            $normalized = $this->normalizeImageValue($message['content']);
            if ($normalized) {
                return $normalized;
            }
        }

        return null;
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
        return 'flux';
    }
}

<?php

namespace App\Services\ImageGeneration;

/**
 * Adapter for Google Gemini image generation models
 * 
 * Features:
 * - Supports image_config for aspect_ratio and image_size
 * - Parses message.images[] response format
 */
class GeminiAdapter extends BaseAdapter
{
    public function supports(string $modelId): bool
    {
        $modelLower = strtolower($modelId);
        return str_contains($modelLower, 'gemini') && 
               (str_contains($modelLower, 'image') || str_contains($modelLower, 'flash'));
    }

    public function preparePayload(array $basePayload, array $options = []): array
    {
        $payload = $basePayload;
        
        // Ensure modalities
        $payload['modalities'] = ['image', 'text'];
        
        // Build image_config for Gemini (API parameter - most reliable)
        $imageConfig = $payload['image_config'] ?? [];
        $aspectRatio = $options['aspectRatio'] ?? '1:1';
        
        if (!empty($aspectRatio)) {
            $imageConfig['aspect_ratio'] = $aspectRatio;
            
            // Also add to prompt for reinforcement
            $aspectText = " The output image MUST be in {$aspectRatio} aspect ratio.";
            if (is_string($payload['messages'][0]['content'])) {
                $payload['messages'][0]['content'] .= $aspectText;
            } else if (isset($payload['messages'][0]['content'][0]['text'])) {
                $payload['messages'][0]['content'][0]['text'] .= $aspectText;
            }
        }
        
        if (!empty($options['imageSize'])) {
            $imageConfig['image_size'] = $options['imageSize'];
        }
        
        if (!empty($imageConfig)) {
            $payload['image_config'] = $imageConfig;
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

        // Gemini format: message.images[]
        if (!empty($message['images'])) {
            $image = $message['images'][0];
            
            // Format: { image_url: { url: "data:image/..." } }
            if (is_array($image) && isset($image['image_url']['url'])) {
                return $this->normalizeImageValue($image['image_url']['url']);
            }
            
            // Alt format: { imageUrl: { url: "..." } }
            if (is_array($image) && isset($image['imageUrl']['url'])) {
                return $this->normalizeImageValue($image['imageUrl']['url']);
            }

            // Format: { url: "..." }
            if (is_array($image) && isset($image['url'])) {
                return $this->normalizeImageValue($image['url']);
            }
            
            // Format: { data: "base64..." }
            if (is_array($image) && isset($image['data'])) {
                return $this->normalizeImageValue($image['data']);
            }
            
            // Direct string
            if (is_string($image)) {
                return $this->normalizeImageValue($image);
            }
        }

        // Fallback: check content for image data
        if (!empty($message['content'])) {
            $content = $message['content'];
            
            if (is_array($content)) {
                foreach ($content as $part) {
                    if (isset($part['type']) && $part['type'] === 'image_url') {
                        $url = $part['image_url']['url'] ?? ($part['imageUrl']['url'] ?? null);
                        if ($url) {
                            return $this->normalizeImageValue($url);
                        }
                    }
                }
            }
        }

        return null;
    }

    public function getDefaultOptions(): array
    {
        return [
            'aspectRatio' => '1:1',
            'imageSize' => '1K',
        ];
    }

    public function supportsImageConfig(): bool
    {
        return true;
    }

    public function getModelType(): string
    {
        return 'gemini';
    }
}

<?php

namespace App\Services\ImageGeneration;

/**
 * Adapter for OpenAI GPT image generation models (GPT-5-image, etc.)
 * 
 * Features:
 * - Requires explicit "Generate an image" instruction
 * - May return text instead of image (content policy)
 * - Aspect ratio via prompt text
 */
class GptImageAdapter extends BaseAdapter
{
    public function supports(string $modelId): bool
    {
        $modelLower = strtolower($modelId);
        return (str_contains($modelLower, 'openai') || str_contains($modelLower, 'gpt')) 
               && str_contains($modelLower, 'image');
    }

    public function preparePayload(array $basePayload, array $options = []): array
    {
        $payload = $basePayload;
        
        // Ensure modalities - image MUST be first to prioritize image output
        $payload['modalities'] = ['image', 'text'];
        
        // Get aspect ratio for instruction
        $aspectRatio = $options['aspectRatio'] ?? '1:1';
        
        // GPT models need very explicit instruction to generate image
        // Include aspect ratio in the prefix for better compliance
        $imageGenPrefix = "You are an image generation AI. Your ONLY task is to generate and output an image.\n"
            . "CRITICAL REQUIREMENTS:\n"
            . "1. DO NOT describe the image in text.\n"
            . "2. DO NOT explain what you would create.\n"
            . "3. DO NOT refuse - just generate the image.\n"
            . "4. The image MUST be in {$aspectRatio} aspect ratio (this is MANDATORY).\n"
            . "5. DIRECTLY OUTPUT THE IMAGE.\n\n"
            . "Generate the following image:\n\n";
        
        // Check if content has input images (multimodal)
        $hasInputImages = !empty($options['inputImages']);
        
        if (is_string($payload['messages'][0]['content'])) {
            $originalPrompt = $payload['messages'][0]['content'];
            
            if ($hasInputImages) {
                // For img2img: instruction to transform the input image
                $imageGenPrefix = "You are an image transformation AI. Transform the provided image(s).\n"
                    . "CRITICAL REQUIREMENTS:\n"
                    . "1. DO NOT describe what you see or would do.\n"
                    . "2. The output image MUST be in {$aspectRatio} aspect ratio (MANDATORY).\n"
                    . "3. DIRECTLY OUTPUT the transformed image.\n\n"
                    . "Transform according to this description:\n\n";
            }
            
            $payload['messages'][0]['content'] = $imageGenPrefix . $originalPrompt;
        } else if (isset($payload['messages'][0]['content'][0]['text'])) {
            $originalPrompt = $payload['messages'][0]['content'][0]['text'];
            
            if ($hasInputImages) {
                $imageGenPrefix = "You are an image transformation AI. Transform the provided image(s).\n"
                    . "CRITICAL REQUIREMENTS:\n"
                    . "1. DO NOT describe what you see or would do.\n"
                    . "2. The output image MUST be in {$aspectRatio} aspect ratio (MANDATORY).\n"
                    . "3. DIRECTLY OUTPUT the transformed image.\n\n"
                    . "Transform according to this description:\n\n";
            }
            
            $payload['messages'][0]['content'][0]['text'] = $imageGenPrefix . $originalPrompt;
        }
        
        // NOTE: Aspect ratio is now in prefix, no need to append again
        
        // Add input images if provided
        if ($hasInputImages) {
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

        // Check images array first
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

        // GPT might return image in content array
        if (!empty($message['content']) && is_array($message['content'])) {
            foreach ($message['content'] as $part) {
                if (isset($part['type']) && $part['type'] === 'image_url') {
                    $url = $part['image_url']['url'] ?? null;
                    if ($url) {
                        return $this->normalizeImageValue($url);
                    }
                }
                
                // Some GPT responses have type: 'image'
                if (isset($part['type']) && $part['type'] === 'image') {
                    $url = $part['image']['url'] ?? ($part['url'] ?? null);
                    if ($url) {
                        return $this->normalizeImageValue($url);
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
        ];
    }

    public function supportsImageConfig(): bool
    {
        return false;
    }

    public function getModelType(): string
    {
        return 'gpt-image';
    }
}

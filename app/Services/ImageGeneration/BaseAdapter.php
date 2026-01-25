<?php

namespace App\Services\ImageGeneration;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Base adapter with shared parsing logic
 */
abstract class BaseAdapter implements ModelAdapterInterface
{
    protected int $maxImageBytes = 20 * 1024 * 1024; // 20MB

    /**
     * Extract text response from OpenRouter response
     */
    public function extractTextResponse(?array $response): ?string
    {
        if (empty($response)) {
            return null;
        }
        
        $choices = $response['choices'] ?? [];
        $message = $choices[0]['message'] ?? [];
        
        if (!empty($message['content'])) {
            $content = $message['content'];
            
            if (is_string($content)) {
                return $content;
            }
            
            if (is_array($content)) {
                $textParts = [];
                foreach ($content as $part) {
                    if (isset($part['type']) && $part['type'] === 'text' && !empty($part['text'])) {
                        $textParts[] = $part['text'];
                    }
                }
                return !empty($textParts) ? implode("\n", $textParts) : null;
            }
        }
        
        return null;
    }

    /**
     * Normalize image value: handle base64, URLs, etc.
     */
    protected function normalizeImageValue(string $value): ?string
    {
        // Already base64 data URL
        if (str_starts_with($value, 'data:image/')) {
            return $value;
        }
        
        // HTTP(S) URL -> download and convert
        if (filter_var($value, FILTER_VALIDATE_URL)) {
            return $this->downloadImageAsBase64($value);
        }
        
        // Raw base64 string (no prefix) - add default prefix
        if ($this->isBase64Image($value)) {
            return 'data:image/png;base64,' . $value;
        }
        
        return null;
    }

    /**
     * Check if string is valid base64 image
     */
    protected function isBase64Image(string $value): bool
    {
        if (strlen($value) < 100) {
            return false;
        }
        
        $decoded = base64_decode($value, true);
        if ($decoded === false) {
            return false;
        }
        
        // Check for common image signatures
        $signatures = [
            "\xFF\xD8\xFF" => true,          // JPEG
            "\x89PNG\r\n\x1a\n" => true,     // PNG
            "GIF87a" => true,                 // GIF87a
            "GIF89a" => true,                 // GIF89a
            "RIFF" => true,                   // WEBP (partial)
        ];
        
        foreach ($signatures as $sig => $valid) {
            if (str_starts_with($decoded, $sig)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Download image from URL and convert to base64
     * Includes SSRF protection: blocks private/local IP addresses
     */
    protected function downloadImageAsBase64(string $url): ?string
    {
        // Already base64 data URL
        if (str_starts_with($url, 'data:image/')) {
            return $url;
        }

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }

        // Only allow http/https
        $scheme = parse_url($url, PHP_URL_SCHEME);
        if (!in_array($scheme, ['http', 'https'], true)) {
            return null;
        }

        // SSRF Protection: Block private/local addresses
        $host = parse_url($url, PHP_URL_HOST);
        if ($host && $this->isPrivateOrLocalHost($host)) {
            Log::warning('BaseAdapter: Blocked SSRF attempt', ['url' => $url, 'host' => $host]);
            return null;
        }

        try {
            $response = Http::timeout(30)->get($url);
            
            if (!$response->successful()) {
                return null;
            }
            
            $contentType = $response->header('Content-Type') ?? 'image/png';
            $mime = strtolower(trim(explode(';', $contentType)[0]));
            
            if (!str_starts_with($mime, 'image/')) {
                return null;
            }
            
            $body = $response->body();
            if (strlen($body) > $this->maxImageBytes) {
                return null;
            }
            
            return 'data:' . $mime . ';base64,' . base64_encode($body);
        } catch (\Exception $e) {
            Log::warning('BaseAdapter: Failed to download image', [
                'url' => $url,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Check if host is private or local (SSRF protection)
     */
    protected function isPrivateOrLocalHost(string $host): bool
    {
        // Localhost checks
        if (in_array(strtolower($host), ['localhost', '127.0.0.1', '::1', '0.0.0.0'], true)) {
            return true;
        }

        // Check for local domain patterns
        if (str_ends_with(strtolower($host), '.local') || str_ends_with(strtolower($host), '.localhost')) {
            return true;
        }

        // Resolve IP and check private ranges
        $ip = gethostbyname($host);
        if ($ip === $host) {
            return false; // Could not resolve
        }

        // Check private IP ranges
        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false;
    }

    /**
     * Add input images to payload content
     */
    protected function addInputImagesToPayload(array $payload, array $inputImages, array $slotDescriptions = []): array
    {
        if (empty($inputImages)) {
            return $payload;
        }

        // Build text with image descriptions
        $imageDescText = '';
        foreach ($inputImages as $key => $imageBase64) {
            $desc = $slotDescriptions[$key] ?? '';
            if ($desc) {
                $imageDescText .= "\n[Image: {$desc}]";
            }
        }

        // Get current content
        $currentContent = $payload['messages'][0]['content'] ?? '';
        
        // Build content parts
        $contentParts = [];
        
        if (is_string($currentContent)) {
            $contentParts[] = [
                'type' => 'text',
                'text' => $currentContent . $imageDescText,
            ];
        } else if (is_array($currentContent)) {
            $contentParts = $currentContent;
            // Append image descriptions to first text part
            if (isset($contentParts[0]['text'])) {
                $contentParts[0]['text'] .= $imageDescText;
            }
        }

        // Add input images
        foreach ($inputImages as $key => $imageBase64) {
            $contentParts[] = [
                'type' => 'image_url',
                'image_url' => [
                    'url' => $imageBase64,
                ],
            ];
        }

        $payload['messages'][0]['content'] = $contentParts;
        
        return $payload;
    }
}

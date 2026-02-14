<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;

/**
 * Setting Model
 * 
 * Quản lý cấu hình hệ thống lưu trong database.
 * Hỗ trợ cache và mã hóa cho các giá trị nhạy cảm (API keys).
 */
class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'label',
        'description',
        'is_encrypted',
    ];

    protected $casts = [
        'is_encrypted' => 'boolean',
    ];

    /**
     * Get setting value by key
     * 
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $cacheKey = "setting_{$key}";

        try {
            return Cache::remember($cacheKey, 3600, function () use ($key, $default) {
                $setting = self::where('key', $key)->first();

                if (!$setting) {
                    return $default;
                }

                $value = $setting->value;

                // Decrypt if encrypted
                if ($setting->is_encrypted && $value) {
                    try {
                        $value = Crypt::decryptString($value);
                    } catch (\Exception $e) {
                        return $default;
                    }
                }

                // Cast based on type
                return match ($setting->type) {
                    'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
                    'integer' => (int) $value,
                    'json' => json_decode($value, true),
                    default => $value,
                };
            });
        } catch (\Throwable $e) {
            report($e);

            return $default;
        }
    }

    /**
     * Set setting value by key
     * 
     * @param string $key
     * @param mixed $value
     * @param array $options
     * @return Setting
     */
    public static function set(string $key, mixed $value, array $options = []): Setting
    {
        // Clear cache
        Cache::forget("setting_{$key}");
        
        $setting = self::firstOrNew(['key' => $key]);
        
        // Handle JSON values
        if (is_array($value)) {
            $value = json_encode($value);
            $setting->type = 'json';
        }
        
        // Encrypt if needed
        $isEncrypted = $options['is_encrypted'] ?? $setting->is_encrypted ?? false;
        if ($isEncrypted && $value) {
            $value = Crypt::encryptString($value);
        }
        
        $setting->value = $value;
        $setting->is_encrypted = $isEncrypted;
        
        if (isset($options['type'])) {
            $setting->type = $options['type'];
        }
        if (isset($options['group'])) {
            $setting->group = $options['group'];
        }
        if (isset($options['label'])) {
            $setting->label = $options['label'];
        }
        if (isset($options['description'])) {
            $setting->description = $options['description'];
        }
        
        $setting->save();
        
        return $setting;
    }

    /**
     * Get all settings by group
     */
    public static function getByGroup(string $group): array
    {
        $settings = self::where('group', $group)->get();
        $result = [];
        
        foreach ($settings as $setting) {
            $result[$setting->key] = self::get($setting->key);
        }
        
        return $result;
    }

    /**
     * Clear all settings cache
     */
    public static function clearCache(): void
    {
        $keys = self::pluck('key');
        foreach ($keys as $key) {
            Cache::forget("setting_{$key}");
        }
    }
}

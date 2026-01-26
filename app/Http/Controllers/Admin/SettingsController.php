<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

/**
 * SettingsController (Admin)
 * 
 * Quản lý cấu hình hệ thống
 */
class SettingsController extends Controller
{
    /**
     * Hiển thị trang settings
     */
    public function index(): View
    {
        $apiSettings = Setting::where('group', 'api')->get();
        $generalSettings = Setting::where('group', 'general')->get();
        
        return view('admin.settings.index', compact('apiSettings', 'generalSettings'));
    }

    /**
     * Cập nhật settings
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'openrouter_api_key' => 'nullable|string|max:500',
            'openrouter_base_url' => 'nullable|url|max:500',
            'site_name' => 'nullable|string|max:255',
            'default_credits' => 'nullable|integer|min:0',
            'image_expiry_days' => 'nullable|integer|min:1|max:365',
        ]);

        $refreshModels = false;

        // Update API Key (encrypted)
        if (filled($validated['openrouter_api_key'] ?? null)) {
            Setting::set('openrouter_api_key', $validated['openrouter_api_key'], [
                'is_encrypted' => true,
                'group' => 'api',
                'label' => 'OpenRouter API Key',
            ]);
            $refreshModels = true;
        }

        // Update Base URL
        if (array_key_exists('openrouter_base_url', $validated)) {
            $baseUrl = trim((string) ($validated['openrouter_base_url'] ?? ''));
            if ($baseUrl === '') {
                Setting::where('key', 'openrouter_base_url')->delete();
                Cache::forget('setting_openrouter_base_url');
            } else {
                Setting::set('openrouter_base_url', $baseUrl, [
                    'group' => 'api',
                    'label' => 'OpenRouter Base URL',
                ]);
            }
            $refreshModels = true;
        }

        // Update General Settings
        if (isset($validated['site_name'])) {
            Setting::set('site_name', $validated['site_name'], [
                'group' => 'general',
                'label' => 'Tên website',
            ]);
        }

        if (isset($validated['default_credits'])) {
            Setting::set('default_credits', $validated['default_credits'], [
                'type' => 'integer',
                'group' => 'general',
                'label' => 'Xu mặc định',
            ]);
        }

        // Update Image Expiry Days
        if (isset($validated['image_expiry_days'])) {
            Setting::set('image_expiry_days', $validated['image_expiry_days'], [
                'type' => 'integer',
                'group' => 'general',
                'label' => 'Số ngày lưu ảnh',
            ]);
        }

        if ($refreshModels) {
            Cache::forget('openrouter_image_models');
            Cache::forget('openrouter_models_enhanced');
        }

        return redirect()
            ->route('admin.settings.index')
            ->with('success', 'Cài đặt đã được cập nhật!');
    }

    /**
     * Force refresh OpenRouter model list
     */
    public function refreshModels(): RedirectResponse
    {
        Cache::forget('openrouter_image_models');
        Cache::forget('openrouter_models_enhanced');
        // [FIX loi.md #6] Clear image_capable_model_ids cache
        Cache::forget('image_capable_model_ids');

        try {
            $modelManager = app(\App\Services\ModelManager::class);
            $models = $modelManager->fetchModels(true);
            $count = is_array($models) ? count($models) : 0;

            return redirect()
                ->route('admin.settings.index')
                ->with('success', "Models refreshed ({$count}).");
        } catch (\Throwable $e) {
            logger()->error('Failed to refresh OpenRouter models', [
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->route('admin.settings.index')
                ->with('error', 'Failed to refresh models. Please try again.');
        }
    }
}

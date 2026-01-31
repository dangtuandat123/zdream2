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

        // Edit Studio settings with defaults
        $editStudioSettings = [
            'model_replace' => Setting::get('edit_studio.model_replace', 'flux-pro-1.0-fill'),
            'model_text' => Setting::get('edit_studio.model_text', 'flux-kontext-pro'),
            'model_background' => Setting::get('edit_studio.model_background', 'flux-pro-1.0-fill'),
            'model_expand' => Setting::get('edit_studio.model_expand', 'flux-pro-1.0-expand'),
            'prompt_prefix_replace' => Setting::get('edit_studio.prompt_prefix_replace', ''),
            'prompt_prefix_text' => Setting::get('edit_studio.prompt_prefix_text', ''),
            'prompt_prefix_background' => Setting::get('edit_studio.prompt_prefix_background', 'Keep the main subject exactly as is. Change the background to:'),
            'prompt_prefix_expand' => Setting::get('edit_studio.prompt_prefix_expand', ''),
        ];

        // Available models for edit modes
        $editModels = [
            'fill' => [
                'flux-pro-1.0-fill' => 'FLUX Pro 1.0 Fill (Recommended)',
                'flux-dev-fill' => 'FLUX Dev Fill (Cheaper)',
            ],
            'text' => [
                'flux-kontext-pro' => 'FLUX Kontext Pro (Recommended)',
                'flux-kontext-max' => 'FLUX Kontext Max (Higher Quality)',
            ],
            'expand' => [
                'flux-pro-1.0-expand' => 'FLUX Pro 1.0 Expand (Only Option)',
            ],
        ];

        return view('admin.settings.index', compact('apiSettings', 'generalSettings', 'editStudioSettings', 'editModels'));
    }

    /**
     * Cập nhật settings
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'bfl_api_key' => 'nullable|string|max:500',
            'bfl_base_url' => 'nullable|url|max:500',
            'site_name' => 'nullable|string|max:255',
            'default_credits' => 'nullable|integer|min:0',
            'credit_exchange_rate' => 'nullable|integer|min:1',
            'image_expiry_days' => 'nullable|integer|min:1|max:365',
            // Edit Studio settings
            'edit_studio_model_replace' => 'nullable|string|max:100',
            'edit_studio_model_text' => 'nullable|string|max:100',
            'edit_studio_model_background' => 'nullable|string|max:100',
            'edit_studio_model_expand' => 'nullable|string|max:100',
            'edit_studio_prompt_prefix_replace' => 'nullable|string|max:500',
            'edit_studio_prompt_prefix_text' => 'nullable|string|max:500',
            'edit_studio_prompt_prefix_background' => 'nullable|string|max:500',
            'edit_studio_prompt_prefix_expand' => 'nullable|string|max:500',
        ]);

        $refreshModels = false;

        // Update API Key (encrypted)
        if (filled($validated['bfl_api_key'] ?? null)) {
            Setting::set('bfl_api_key', $validated['bfl_api_key'], [
                'is_encrypted' => true,
                'group' => 'api',
                'label' => 'BFL API Key',
            ]);
            $refreshModels = true;
        }

        // Update Base URL
        if (array_key_exists('bfl_base_url', $validated)) {
            $baseUrl = trim((string) ($validated['bfl_base_url'] ?? ''));
            if ($baseUrl === '') {
                Setting::where('key', 'bfl_base_url')->delete();
                Cache::forget('setting_bfl_base_url');
            } else {
                Setting::set('bfl_base_url', $baseUrl, [
                    'group' => 'api',
                    'label' => 'BFL Base URL',
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

        if (isset($validated['credit_exchange_rate'])) {
            Setting::set('credit_exchange_rate', $validated['credit_exchange_rate'], [
                'type' => 'integer',
                'group' => 'general',
                'label' => 'Tỉ lệ VND/Xu',
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

        // =============================================
        // Edit Studio Settings
        // =============================================
        $editStudioFields = [
            'edit_studio_model_replace' => ['key' => 'edit_studio.model_replace', 'label' => 'Model Replace'],
            'edit_studio_model_text' => ['key' => 'edit_studio.model_text', 'label' => 'Model Text'],
            'edit_studio_model_background' => ['key' => 'edit_studio.model_background', 'label' => 'Model Background'],
            'edit_studio_model_expand' => ['key' => 'edit_studio.model_expand', 'label' => 'Model Expand'],
            'edit_studio_prompt_prefix_replace' => ['key' => 'edit_studio.prompt_prefix_replace', 'label' => 'Prompt Prefix Replace'],
            'edit_studio_prompt_prefix_text' => ['key' => 'edit_studio.prompt_prefix_text', 'label' => 'Prompt Prefix Text'],
            'edit_studio_prompt_prefix_background' => ['key' => 'edit_studio.prompt_prefix_background', 'label' => 'Prompt Prefix Background'],
            'edit_studio_prompt_prefix_expand' => ['key' => 'edit_studio.prompt_prefix_expand', 'label' => 'Prompt Prefix Expand'],
        ];

        foreach ($editStudioFields as $field => $config) {
            if (array_key_exists($field, $validated)) {
                $value = trim((string) ($validated[$field] ?? ''));
                if ($value !== '') {
                    Setting::set($config['key'], $value, [
                        'group' => 'edit_studio',
                        'label' => $config['label'],
                    ]);
                } else {
                    // Clear if empty
                    Setting::where('key', $config['key'])->delete();
                    Cache::forget('setting_' . $config['key']);
                }
            }
        }

        if ($refreshModels) {
            Cache::forget('bfl_models');
            Cache::forget('image_capable_model_ids');
        }

        return redirect()
            ->route('admin.settings.index')
            ->with('success', 'Cài đặt đã được cập nhật!');
    }

    /**
     * Force refresh BFL model list
     */
    public function refreshModels(): RedirectResponse
    {
        Cache::forget('bfl_models');
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
            logger()->error('Failed to refresh BFL models', [
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->route('admin.settings.index')
                ->with('error', 'Failed to refresh models. Please try again.');
        }
    }
}

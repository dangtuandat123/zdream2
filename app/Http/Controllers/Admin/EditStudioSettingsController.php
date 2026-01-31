<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

/**
 * EditStudioSettingsController (Admin)
 * 
 * Quản lý cấu hình cho chức năng Edit Studio
 */
class EditStudioSettingsController extends Controller
{
    /**
     * Hiển thị trang Edit Studio settings
     */
    public function index(): View
    {
        // Edit Studio settings with defaults
        $settings = [
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
        $models = [
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

        return view('admin.edit-studio.index', compact('settings', 'models'));
    }

    /**
     * Cập nhật Edit Studio settings
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'model_replace' => 'nullable|string|max:100',
            'model_text' => 'nullable|string|max:100',
            'model_background' => 'nullable|string|max:100',
            'model_expand' => 'nullable|string|max:100',
            'prompt_prefix_replace' => 'nullable|string|max:500',
            'prompt_prefix_text' => 'nullable|string|max:500',
            'prompt_prefix_background' => 'nullable|string|max:500',
            'prompt_prefix_expand' => 'nullable|string|max:500',
        ]);

        $fields = [
            'model_replace' => ['key' => 'edit_studio.model_replace', 'label' => 'Model Replace'],
            'model_text' => ['key' => 'edit_studio.model_text', 'label' => 'Model Text'],
            'model_background' => ['key' => 'edit_studio.model_background', 'label' => 'Model Background'],
            'model_expand' => ['key' => 'edit_studio.model_expand', 'label' => 'Model Expand'],
            'prompt_prefix_replace' => ['key' => 'edit_studio.prompt_prefix_replace', 'label' => 'Prompt Prefix Replace'],
            'prompt_prefix_text' => ['key' => 'edit_studio.prompt_prefix_text', 'label' => 'Prompt Prefix Text'],
            'prompt_prefix_background' => ['key' => 'edit_studio.prompt_prefix_background', 'label' => 'Prompt Prefix Background'],
            'prompt_prefix_expand' => ['key' => 'edit_studio.prompt_prefix_expand', 'label' => 'Prompt Prefix Expand'],
        ];

        foreach ($fields as $field => $config) {
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

        return redirect()
            ->route('admin.edit-studio.index')
            ->with('success', 'Cài đặt Edit Studio đã được cập nhật!');
    }
}

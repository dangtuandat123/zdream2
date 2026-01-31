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
            // Model settings
            'model_replace' => Setting::get('edit_studio.model_replace', 'flux-pro-1.0-fill'),
            'model_text' => Setting::get('edit_studio.model_text', 'flux-kontext-pro'),
            'model_background' => Setting::get('edit_studio.model_background', 'flux-pro-1.0-fill'),
            'model_expand' => Setting::get('edit_studio.model_expand', 'flux-pro-1.0-expand'),
            // Prompt prefix settings
            'prompt_prefix_replace' => Setting::get('edit_studio.prompt_prefix_replace', ''),
            'prompt_prefix_text' => Setting::get('edit_studio.prompt_prefix_text', ''),
            'prompt_prefix_background' => Setting::get('edit_studio.prompt_prefix_background', 'Keep the main subject exactly as is. Change the background to:'),
            'prompt_prefix_expand' => Setting::get('edit_studio.prompt_prefix_expand', ''),
            // Credit cost settings (based on BFL pricing)
            'credit_cost_replace' => (float) Setting::get('edit_studio.credit_cost_replace', 5),
            'credit_cost_text' => (float) Setting::get('edit_studio.credit_cost_text', 4),
            'credit_cost_background' => (float) Setting::get('edit_studio.credit_cost_background', 5),
            'credit_cost_expand' => (float) Setting::get('edit_studio.credit_cost_expand', 5),
        ];


        // Available models for edit modes with pricing
        // FLUX.1 models: FIXED price per image (regardless of resolution)
        // FLUX.2 models: Megapixel-based pricing (varies by output resolution)
        $models = [
            'fill' => [
                'flux-pro-1.0-fill' => 'FLUX.1 Fill Pro — $0.05/image (5 credits, cố định)',
                'flux-pro-1.0-fill-finetuned' => 'FLUX.1 Fill Pro Finetuned — $0.05/image (5 credits, cố định)',
            ],
            'text' => [
                'flux-kontext-pro' => 'FLUX.1 Kontext Pro — $0.04/image (4 credits, cố định)',
                'flux-kontext-max' => 'FLUX.1 Kontext Max — $0.08/image (8 credits, cố định)',
            ],
            'expand' => [
                'flux-pro-1.0-expand' => 'FLUX.1 Expand — $0.05/image (5 credits, cố định)',
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
            'credit_cost_replace' => 'nullable|numeric|min:0|max:1000',
            'credit_cost_text' => 'nullable|numeric|min:0|max:1000',
            'credit_cost_background' => 'nullable|numeric|min:0|max:1000',
            'credit_cost_expand' => 'nullable|numeric|min:0|max:1000',
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
            'credit_cost_replace' => ['key' => 'edit_studio.credit_cost_replace', 'label' => 'Credit Cost Replace'],
            'credit_cost_text' => ['key' => 'edit_studio.credit_cost_text', 'label' => 'Credit Cost Text'],
            'credit_cost_background' => ['key' => 'edit_studio.credit_cost_background', 'label' => 'Credit Cost Background'],
            'credit_cost_expand' => ['key' => 'edit_studio.credit_cost_expand', 'label' => 'Credit Cost Expand'],
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

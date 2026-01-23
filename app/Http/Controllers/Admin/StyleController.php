<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Style;
use App\Models\StyleOption;
use App\Services\OpenRouterService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Str;

/**
 * StyleController (Admin)
 * 
 * CRUD quản lý Styles và Options
 */
class StyleController extends Controller
{
    protected OpenRouterService $openRouterService;

    public function __construct(OpenRouterService $openRouterService)
    {
        $this->openRouterService = $openRouterService;
    }

    /**
     * Danh sách Styles
     */
    public function index(): View
    {
        $styles = Style::query()
            ->withCount('options')
            ->withCount('generatedImages')
            ->ordered()
            ->paginate(15);

        return view('admin.styles.index', compact('styles'));
    }

    /**
     * Form tạo Style mới
     */
    public function create(): View
    {
        $models = $this->openRouterService->getAvailableModels();
        $aspectRatios = $this->openRouterService->getAspectRatios();

        return view('admin.styles.create', compact('models', 'aspectRatios'));
    }

    /**
     * Lưu Style mới
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'thumbnail_url' => 'nullable|url|max:500',
            'price' => 'required|numeric|min:0',
            'openrouter_model_id' => 'required|string|max:255',
            'base_prompt' => 'required|string',
            'aspect_ratio' => 'nullable|string',
            'allow_user_custom_prompt' => 'boolean',
            'is_active' => 'boolean',
            
            // Options (Dynamic array)
            'options' => 'nullable|array',
            'options.*.label' => 'required_with:options|string|max:255',
            'options.*.group_name' => 'required_with:options|string|max:100',
            'options.*.prompt_fragment' => 'required_with:options|string|max:500',
        ]);

        // Build config_payload
        $configPayload = null;
        if (!empty($validated['aspect_ratio'])) {
            $configPayload = ['aspect_ratio' => $validated['aspect_ratio']];
        }

        // Tạo Style
        $style = Style::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'description' => $validated['description'] ?? null,
            'thumbnail_url' => $validated['thumbnail_url'] ?? null,
            'price' => $validated['price'],
            'openrouter_model_id' => $validated['openrouter_model_id'],
            'base_prompt' => $validated['base_prompt'],
            'config_payload' => $configPayload,
            'allow_user_custom_prompt' => $validated['allow_user_custom_prompt'] ?? false,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        // Tạo Options
        if (!empty($validated['options'])) {
            foreach ($validated['options'] as $index => $optionData) {
                $style->options()->create([
                    'label' => $optionData['label'],
                    'group_name' => $optionData['group_name'],
                    'prompt_fragment' => $optionData['prompt_fragment'],
                    'sort_order' => $index,
                ]);
            }
        }

        return redirect()
            ->route('admin.styles.index')
            ->with('success', 'Style đã được tạo thành công!');
    }

    /**
     * Form chỉnh sửa Style
     */
    public function edit(Style $style): View
    {
        $style->load('options');
        $models = $this->openRouterService->getAvailableModels();
        $aspectRatios = $this->openRouterService->getAspectRatios();

        return view('admin.styles.edit', compact('style', 'models', 'aspectRatios'));
    }

    /**
     * Cập nhật Style
     */
    public function update(Request $request, Style $style): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'thumbnail_url' => 'nullable|url|max:500',
            'price' => 'required|numeric|min:0',
            'openrouter_model_id' => 'required|string|max:255',
            'base_prompt' => 'required|string',
            'aspect_ratio' => 'nullable|string',
            'allow_user_custom_prompt' => 'boolean',
            'is_active' => 'boolean',
            
            'options' => 'nullable|array',
            'options.*.id' => 'nullable|integer',
            'options.*.label' => 'required_with:options|string|max:255',
            'options.*.group_name' => 'required_with:options|string|max:100',
            'options.*.prompt_fragment' => 'required_with:options|string|max:500',
        ]);

        // Build config_payload
        $configPayload = null;
        if (!empty($validated['aspect_ratio'])) {
            $configPayload = ['aspect_ratio' => $validated['aspect_ratio']];
        }

        // Update Style
        $style->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'thumbnail_url' => $validated['thumbnail_url'] ?? null,
            'price' => $validated['price'],
            'openrouter_model_id' => $validated['openrouter_model_id'],
            'base_prompt' => $validated['base_prompt'],
            'config_payload' => $configPayload,
            'allow_user_custom_prompt' => $validated['allow_user_custom_prompt'] ?? false,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        // Sync Options
        $existingOptionIds = [];
        if (!empty($validated['options'])) {
            foreach ($validated['options'] as $index => $optionData) {
                if (!empty($optionData['id'])) {
                    // Update existing option
                    $option = StyleOption::find($optionData['id']);
                    if ($option && $option->style_id === $style->id) {
                        $option->update([
                            'label' => $optionData['label'],
                            'group_name' => $optionData['group_name'],
                            'prompt_fragment' => $optionData['prompt_fragment'],
                            'sort_order' => $index,
                        ]);
                        $existingOptionIds[] = $option->id;
                    }
                } else {
                    // Create new option
                    $newOption = $style->options()->create([
                        'label' => $optionData['label'],
                        'group_name' => $optionData['group_name'],
                        'prompt_fragment' => $optionData['prompt_fragment'],
                        'sort_order' => $index,
                    ]);
                    $existingOptionIds[] = $newOption->id;
                }
            }
        }

        // Delete removed options
        $style->options()->whereNotIn('id', $existingOptionIds)->delete();

        return redirect()
            ->route('admin.styles.index')
            ->with('success', 'Style đã được cập nhật!');
    }

    /**
     * Xóa Style
     */
    public function destroy(Style $style): RedirectResponse
    {
        $style->delete();

        return redirect()
            ->route('admin.styles.index')
            ->with('success', 'Style đã được xóa!');
    }
}

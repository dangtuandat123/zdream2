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
        $models = $this->openRouterService->fetchImageModels();
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
            'slug' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'thumbnail_url' => 'nullable|url|max:500',
            'price' => 'required|numeric|min:0',
            'sort_order' => 'nullable|integer|min:0',
            'openrouter_model_id' => 'required|string|max:255',
            'base_prompt' => 'required|string',
            'aspect_ratio' => 'nullable|string',
            'allow_user_custom_prompt' => 'boolean',
            'is_active' => 'boolean',
            
            // Image Slots (Dynamic array)
            'image_slots' => 'nullable|array',
            'image_slots.*.key' => ['required_with:image_slots', 'string', 'max:100', 'regex:/^[a-zA-Z0-9_-]+$/'],
            'image_slots.*.label' => 'required_with:image_slots|string|max:255',
            'image_slots.*.description' => 'nullable|string|max:500',
            'image_slots.*.required' => 'nullable',
            
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

        // Process image_slots
        $imageSlots = null;
        if (!empty($validated['image_slots'])) {
            $imageSlots = collect($validated['image_slots'])->map(function ($slot) {
                return [
                    'key' => $slot['key'],
                    'label' => $slot['label'],
                    'description' => $slot['description'] ?? '',
                    'required' => isset($slot['required']) && $slot['required'],
                ];
            })->values()->toArray();
        }

        // Process system_images (upload to MinIO)
        $systemImages = null;
        if ($request->hasFile('system_images_files')) {
            $storageService = app(\App\Services\StorageService::class);
            $systemImages = [];
            
            $labels = $request->input('system_images_labels', []);
            $descriptions = $request->input('system_images_descriptions', []);
            
            foreach ($request->file('system_images_files') as $index => $file) {
                if ($file && $file->isValid()) {
                    // Upload to MinIO
                    $path = $file->store('system-images', 'minio');
                    $url = \Illuminate\Support\Facades\Storage::disk('minio')->url($path);
                    
                    $systemImages[] = [
                        'key' => 'sys_' . time() . '_' . $index,
                        'label' => $labels[$index] ?? 'System Image ' . ($index + 1),
                        'description' => $descriptions[$index] ?? '',
                        'path' => $path,
                        'url' => $url,
                    ];
                }
            }
        }

        // Tạo Style
        $style = Style::create([
            'name' => $validated['name'],
            'slug' => $validated['slug'] ?? null,
            'description' => $validated['description'] ?? null,
            'thumbnail_url' => $validated['thumbnail_url'] ?? null,
            'price' => $validated['price'],
            'sort_order' => $validated['sort_order'] ?? 0,
            'openrouter_model_id' => $validated['openrouter_model_id'],
            'base_prompt' => $validated['base_prompt'],
            'config_payload' => $configPayload,
            'image_slots' => $imageSlots,
            'system_images' => $systemImages,
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
        $models = $this->openRouterService->fetchImageModels();
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
            'slug' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'thumbnail_url' => 'nullable|url|max:500',
            'price' => 'required|numeric|min:0',
            'sort_order' => 'nullable|integer|min:0',
            'openrouter_model_id' => 'required|string|max:255',
            'base_prompt' => 'required|string',
            'aspect_ratio' => 'nullable|string',
            'allow_user_custom_prompt' => 'boolean',
            'is_active' => 'boolean',
            
            'image_slots' => 'nullable|array',
            'image_slots.*.key' => 'required_with:image_slots|string|max:100',
            'image_slots.*.label' => 'required_with:image_slots|string|max:255',
            'image_slots.*.description' => 'nullable|string|max:500',
            'image_slots.*.required' => 'nullable',
            
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

        // Process image_slots
        $imageSlots = null;
        if (!empty($validated['image_slots'])) {
            $imageSlots = collect($validated['image_slots'])->map(function ($slot) {
                return [
                    'key' => $slot['key'],
                    'label' => $slot['label'],
                    'description' => $slot['description'] ?? '',
                    'required' => isset($slot['required']) && $slot['required'],
                ];
            })->values()->toArray();
        }

        // Process system_images (merge existing + new uploads)
        $systemImages = [];
        
        // Keep existing images that were not removed
        if ($request->has('existing_system_images')) {
            foreach ($request->input('existing_system_images', []) as $existing) {
                $systemImages[] = [
                    'key' => $existing['key'],
                    'label' => $existing['label'] ?? '',
                    'description' => $existing['description'] ?? '',
                    'path' => $existing['path'] ?? '',
                    'url' => $existing['url'] ?? '',
                ];
            }
        }
        
        // Upload new images to MinIO
        if ($request->hasFile('system_images_files')) {
            $labels = $request->input('system_images_labels', []);
            $descriptions = $request->input('system_images_descriptions', []);
            
            foreach ($request->file('system_images_files') as $index => $file) {
                if ($file && $file->isValid()) {
                    $path = $file->store('system-images', 'minio');
                    $url = \Illuminate\Support\Facades\Storage::disk('minio')->url($path);
                    
                    $systemImages[] = [
                        'key' => 'sys_' . time() . '_' . $index,
                        'label' => $labels[$index] ?? 'System Image',
                        'description' => $descriptions[$index] ?? '',
                        'path' => $path,
                        'url' => $url,
                    ];
                }
            }
        }

        // Update Style
        $style->update([
            'name' => $validated['name'],
            'slug' => $validated['slug'] ?? $style->slug,
            'description' => $validated['description'] ?? null,
            'thumbnail_url' => $validated['thumbnail_url'] ?? null,
            'price' => $validated['price'],
            'sort_order' => $validated['sort_order'] ?? $style->sort_order,
            'openrouter_model_id' => $validated['openrouter_model_id'],
            'base_prompt' => $validated['base_prompt'],
            'config_payload' => $configPayload,
            'image_slots' => $imageSlots,
            'system_images' => !empty($systemImages) ? $systemImages : null,
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

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Style;
use App\Models\StyleOption;
use App\Services\ModelManager;
use App\Services\OpenRouterService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * StyleController (Admin)
 * 
 * CRUD quản lý Styles và Options
 * Production-grade với full error handling
 */
class StyleController extends Controller
{
    protected OpenRouterService $openRouterService;
    protected ModelManager $modelManager;

    public function __construct(
        OpenRouterService $openRouterService,
        ModelManager $modelManager
    ) {
        $this->openRouterService = $openRouterService;
        $this->modelManager = $modelManager;
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
        $models = $this->modelManager->fetchModels();
        $groupedModels = $this->modelManager->groupByProvider($models);
        $aspectRatios = $this->openRouterService->getAspectRatios();

        return view('admin.styles.create', [
            'models' => $models,
            'groupedModels' => $groupedModels,
            'aspectRatios' => $aspectRatios,
        ]);
    }

    /**
     * Lưu Style mới
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:styles,slug',
            'description' => 'nullable|string|max:1000',
            'thumbnail_url' => 'nullable|url|max:500',
            'price' => 'required|numeric|min:0',
            'sort_order' => 'nullable|integer|min:0',
            'openrouter_model_id' => [
                'required', 
                'string', 
                'max:255',
                function ($attribute, $value, $fail) {
                    // Fetch models (cached)
                    $models = $this->modelManager->fetchModels();
                    $validIds = array_column($models, 'id');
                    
                    if (!in_array($value, $validIds)) {
                        // Allow if it's a known fallback or user explicitly wants to force it
                        // But warn if it's completely unknown
                        // For now, we enforce it must be in the list of image models
                        $fail("Model ID '{$value}' không hợp lệ hoặc không hỗ trợ tạo ảnh (không tìm thấy trong danh sách image models).");
                    }
                },
            ],
            'base_prompt' => 'required|string|max:10000', // Limit prompt length
            // HIGH-05 FIX: Validate aspect_ratio against supported list
            'aspect_ratio' => ['nullable', 'string', 'max:20', Rule::in(array_keys($this->openRouterService->getAspectRatios()))],
            'allow_user_custom_prompt' => 'nullable',
            'is_active' => 'nullable',
            
            // Image Slots (Dynamic array)
            'image_slots' => 'nullable|array|max:10', // Max 10 slots
            'image_slots.*.key' => ['required_with:image_slots', 'string', 'max:100', 'regex:/^[a-zA-Z0-9_-]+$/'],
            'image_slots.*.label' => 'required_with:image_slots|string|max:255',
            'image_slots.*.description' => 'nullable|string|max:500',
            'image_slots.*.required' => 'nullable',
            
            // Options (Dynamic array)
            'options' => 'nullable|array|max:50', // Max 50 options
            'options.*.label' => 'required_with:options|string|max:255',
            // C2 FIX: Reject quotes in group_name to prevent HTML injection in wire:click
            'options.*.group_name' => ['required_with:options', 'string', 'max:100', 'regex:/^[^\'\"]+$/'],
            'options.*.prompt_fragment' => 'required_with:options|string|max:500',
            
            // System images files validation
            'system_images_files' => 'nullable|array|max:5', // Max 5 system images
            'system_images_files.*' => 'image|max:10240', // 10MB max per image
            'system_images_labels' => 'nullable|array',
            'system_images_labels.*' => 'nullable|string|max:255',
            'system_images_descriptions' => 'nullable|array',
            'system_images_descriptions.*' => 'nullable|string|max:500',
        ]);

        // Use database transaction for data integrity
        try {
            return DB::transaction(function () use ($request, $validated) {
                // Build config_payload
                $configPayload = null;
                if (!empty($validated['aspect_ratio'])) {
                    $configPayload = ['aspect_ratio' => $validated['aspect_ratio']];
                }

                // Process image_slots
                $imageSlots = $this->processImageSlots($validated['image_slots'] ?? []);

                // Process system_images (upload to MinIO)
                $systemImages = $this->uploadSystemImages($request);

                // Tạo Style
                $style = Style::create([
                    'name' => $validated['name'],
                    'slug' => !empty($validated['slug']) ? $validated['slug'] : null,
                    'description' => $validated['description'] ?? null,
                    'thumbnail_url' => $validated['thumbnail_url'] ?? null,
                    'price' => $validated['price'],
                    'sort_order' => $validated['sort_order'] ?? 0,
                    'openrouter_model_id' => $validated['openrouter_model_id'],
                    'base_prompt' => $validated['base_prompt'],
                    'config_payload' => $configPayload,
                    'image_slots' => $imageSlots,
                    'system_images' => $systemImages,
                    'allow_user_custom_prompt' => $request->boolean('allow_user_custom_prompt'),
                    'is_active' => $request->boolean('is_active'),
                ]);

                // Tạo Options
                $this->createOptions($style, $validated['options'] ?? []);

                Log::info('Style created', ['id' => $style->id, 'name' => $style->name]);

                return redirect()
                    ->route('admin.styles.index')
                    ->with('success', 'Style đã được tạo thành công!');
            });
        } catch (\Exception $e) {
            Log::error('Failed to create style', [
                'error' => $e->getMessage(),
                'name' => $validated['name'] ?? 'unknown',
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Có lỗi xảy ra khi tạo Style: ' . $e->getMessage());
        }
    }

    /**
     * Form chỉnh sửa Style
     */
    public function edit(Style $style): View
    {
        $style->load('options');
        $models = $this->modelManager->fetchModels();
        $groupedModels = $this->modelManager->groupByProvider($models);
        $aspectRatios = $this->openRouterService->getAspectRatios();

        return view('admin.styles.edit', [
            'style' => $style,
            'models' => $models,
            'groupedModels' => $groupedModels,
            'aspectRatios' => $aspectRatios,
        ]);
    }

    /**
     * Cập nhật Style
     */
    public function update(Request $request, Style $style): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:styles,slug,' . $style->id,
            'description' => 'nullable|string|max:1000',
            'thumbnail_url' => 'nullable|url|max:500',
            'price' => 'required|numeric|min:0',
            'sort_order' => 'nullable|integer|min:0',
            'openrouter_model_id' => 'required|string|max:255',
            'base_prompt' => 'required|string|max:10000',
            // HIGH-05 FIX: Validate aspect_ratio against supported list
            'aspect_ratio' => ['nullable', 'string', 'max:20', Rule::in(array_keys($this->openRouterService->getAspectRatios()))],
            'allow_user_custom_prompt' => 'nullable',
            'is_active' => 'nullable',
            
            'image_slots' => 'nullable|array|max:10',
            'image_slots.*.key' => ['required_with:image_slots', 'string', 'max:100', 'regex:/^[a-zA-Z0-9_-]+$/'],
            'image_slots.*.label' => 'required_with:image_slots|string|max:255',
            'image_slots.*.description' => 'nullable|string|max:500',
            'image_slots.*.required' => 'nullable',
            
            'options' => 'nullable|array|max:50',
            'options.*.id' => 'nullable|integer',
            'options.*.label' => 'required_with:options|string|max:255',
            // C2 FIX: Reject quotes in group_name to prevent HTML injection in wire:click
            'options.*.group_name' => ['required_with:options', 'string', 'max:100', 'regex:/^[^\'\"]+$/'],
            'options.*.prompt_fragment' => 'required_with:options|string|max:500',
            
            'existing_system_images' => 'nullable|array',
            'removed_system_images' => 'nullable|array',
            'system_images_files' => 'nullable|array|max:5',
            'system_images_files.*' => 'image|max:10240',
            'system_images_labels' => 'nullable|array',
            'system_images_descriptions' => 'nullable|array',
        ]);

        try {
            return DB::transaction(function () use ($request, $validated, $style) {
                // Build config_payload - preserve existing, update aspect_ratio
                $configPayload = $style->config_payload ?? [];
                
                // INT-01 FIX: Cho phép clear aspect_ratio khi empty
                if (!empty($validated['aspect_ratio'])) {
                    $configPayload['aspect_ratio'] = $validated['aspect_ratio'];
                } else {
                    // Remove aspect_ratio nếu admin chọn "mặc định"
                    unset($configPayload['aspect_ratio']);
                }

                // Process image_slots
                $imageSlots = $this->processImageSlots($validated['image_slots'] ?? []);

                // Handle system images: remove, keep existing, add new
                $systemImages = $this->handleSystemImagesUpdate($request, $style);

                // Update Style
                $style->update([
                    'name' => $validated['name'],
                    'slug' => !empty($validated['slug']) ? $validated['slug'] : $style->slug,
                    'description' => $validated['description'] ?? null,
                    'thumbnail_url' => $validated['thumbnail_url'] ?? null,
                    'price' => $validated['price'],
                    'sort_order' => $validated['sort_order'] ?? $style->sort_order,
                    'openrouter_model_id' => $validated['openrouter_model_id'],
                    'base_prompt' => $validated['base_prompt'],
                    'config_payload' => !empty($configPayload) ? $configPayload : null,
                    'image_slots' => $imageSlots,
                    'system_images' => !empty($systemImages) ? $systemImages : null,
                    'allow_user_custom_prompt' => $request->boolean('allow_user_custom_prompt'),
                    'is_active' => $request->boolean('is_active'),
                ]);

                // C1 FIX: Chỉ sync options khi request có options field
                // Tránh xóa sạch options khi edit form không có options section
                if ($request->has('options')) {
                    $this->syncOptions($style, $validated['options'] ?? []);
                }

                Log::info('Style updated', ['id' => $style->id, 'name' => $style->name]);

                return redirect()
                    ->route('admin.styles.index')
                    ->with('success', 'Style đã được cập nhật!');
            });
        } catch (\Exception $e) {
            Log::error('Failed to update style', [
                'id' => $style->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Có lỗi xảy ra khi cập nhật Style: ' . $e->getMessage());
        }
    }

    /**
     * Xóa Style
     */
    public function destroy(Style $style): RedirectResponse
    {
        try {
            // Cleanup system_images files từ MinIO
            $this->deleteSystemImageFiles($style->system_images ?? []);

            $styleName = $style->name;
            $style->delete();

            Log::info('Style deleted', ['name' => $styleName]);

            return redirect()
                ->route('admin.styles.index')
                ->with('success', 'Style đã được xóa!');
        } catch (\Exception $e) {
            Log::error('Failed to delete style', [
                'id' => $style->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->route('admin.styles.index')
                ->with('error', 'Có lỗi xảy ra khi xóa Style: ' . $e->getMessage());
        }
    }

    // =========================================
    // PRIVATE HELPER METHODS
    // =========================================

    /**
     * Process image slots array
     */
    private function processImageSlots(array $slots): ?array
    {
        if (empty($slots)) {
            return null;
        }

        return collect($slots)->map(function ($slot) {
            return [
                'key' => $slot['key'] ?? Str::slug($slot['label'] ?? 'slot') . '_' . Str::random(6),
                'label' => $slot['label'] ?? '',
                'description' => $slot['description'] ?? '',
                'required' => isset($slot['required']) && ($slot['required'] === true || $slot['required'] === '1' || $slot['required'] === 'on'),
            ];
        })->values()->toArray();
    }

    /**
     * Create options for a style
     */
    private function createOptions(Style $style, array $options): void
    {
        foreach ($options as $index => $optionData) {
            $style->options()->create([
                'label' => $optionData['label'],
                'group_name' => $optionData['group_name'],
                'prompt_fragment' => $optionData['prompt_fragment'],
                'sort_order' => $index,
            ]);
        }
    }

    /**
     * Sync options (create new, update existing, delete removed)
     */
    private function syncOptions(Style $style, array $options): void
    {
        $existingOptionIds = [];

        foreach ($options as $index => $optionData) {
            if (!empty($optionData['id'])) {
                // Update existing option - verify it belongs to this style
                $option = StyleOption::where('id', $optionData['id'])
                    ->where('style_id', $style->id)
                    ->first();
                    
                if ($option) {
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

        // Delete removed options
        $style->options()->whereNotIn('id', $existingOptionIds)->delete();
    }

    /**
     * Upload system images to MinIO
     */
    private function uploadSystemImages(Request $request): ?array
    {
        if (!$request->hasFile('system_images_files')) {
            return null;
        }

        $systemImages = [];
        $labels = $request->input('system_images_labels', []);
        $descriptions = $request->input('system_images_descriptions', []);

        foreach ($request->file('system_images_files') as $index => $file) {
            if ($file && $file->isValid()) {
                try {
                    $path = $file->store('system-images', 'minio');
                    $url = Storage::disk('minio')->url($path);

                    // Use unique key to avoid collisions
                    $uniqueKey = 'sys_' . Str::random(12) . '_' . $index;

                    $systemImages[] = [
                        'key' => $uniqueKey,
                        'label' => $labels[$index] ?? 'System Image ' . ($index + 1),
                        'description' => $descriptions[$index] ?? '',
                        'path' => $path,
                        'url' => $url,
                    ];
                } catch (\Exception $e) {
                    Log::warning('Failed to upload system image', [
                        'index' => $index,
                        'error' => $e->getMessage(),
                    ]);
                    // Continue with other uploads
                }
            }
        }

        return !empty($systemImages) ? $systemImages : null;
    }

    /**
     * Handle system images update (remove old, keep existing, add new)
     */
    private function handleSystemImagesUpdate(Request $request, Style $style): array
    {
        $systemImages = [];

        // 1. Get list of removed image keys
        $removedKeys = $request->input('removed_system_images', []);
        if (!is_array($removedKeys)) {
            $removedKeys = [];
        }

        // 2. Delete files for removed images
        $existingImages = $style->system_images ?? [];
        foreach ($existingImages as $img) {
            $key = $img['key'] ?? '';
            if (in_array($key, $removedKeys) && !empty($img['path'])) {
                try {
                    Storage::disk('minio')->delete($img['path']);
                    Log::debug('Deleted system image', ['key' => $key, 'path' => $img['path']]);
                } catch (\Exception $e) {
                    Log::warning('Failed to delete system image file', [
                        'key' => $key,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        // 3. Keep existing images that were not removed
        if ($request->has('existing_system_images')) {
            foreach ($request->input('existing_system_images', []) as $existing) {
                if (is_array($existing) && !empty($existing['key'])) {
                    $systemImages[] = [
                        'key' => $existing['key'],
                        'label' => $existing['label'] ?? '',
                        'description' => $existing['description'] ?? '',
                        'path' => $existing['path'] ?? '',
                        'url' => $existing['url'] ?? '',
                    ];
                }
            }
        } else {
            // If no existing_system_images in request, keep all that weren't removed
            foreach ($existingImages as $img) {
                $key = $img['key'] ?? '';
                if (!in_array($key, $removedKeys)) {
                    $systemImages[] = $img;
                }
            }
        }

        // 4. Upload and add new images
        $newImages = $this->uploadSystemImages($request);
        if (!empty($newImages)) {
            $systemImages = array_merge($systemImages, $newImages);
        }

        return $systemImages;
    }

    /**
     * Delete system image files from MinIO
     */
    private function deleteSystemImageFiles(array $systemImages): void
    {
        foreach ($systemImages as $img) {
            if (!empty($img['path'])) {
                try {
                    Storage::disk('minio')->delete($img['path']);
                } catch (\Exception $e) {
                    Log::warning('Failed to delete system image on style destroy', [
                        'path' => $img['path'],
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }
    }
}

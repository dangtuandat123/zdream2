<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Style;
use App\Models\StyleOption;
use App\Models\Tag;
use App\Services\ModelManager;
use App\Services\BflService;
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
    protected BflService $bflService;
    protected ModelManager $modelManager;

    public function __construct(
        BflService $bflService,
        ModelManager $modelManager
    ) {
        $this->bflService = $bflService;
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
    $aspectRatios = $this->bflService->getAspectRatios();
    $tags = Tag::active()->ordered()->get();

    return view('admin.styles.create', [
        'models' => $models,
        'groupedModels' => $groupedModels,
        'aspectRatios' => $aspectRatios,
        'tags' => $tags,
    ]);
}

    /**
     * Form import styles
     */
    public function importForm(): View
    {
        return view('admin.styles.import');
    }

    /**
     * Import styles from JSON
     */
    public function importStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'import_file' => 'nullable|file|mimes:json,txt',
            'import_text' => 'nullable|string',
            'dry_run' => 'nullable',
        ]);

        $content = '';
        if ($request->hasFile('import_file')) {
            $content = (string) file_get_contents($request->file('import_file')->getRealPath());
        } elseif (!empty($validated['import_text'])) {
            $content = (string) $validated['import_text'];
        }

        $content = trim($content);
        if ($content === '') {
            return redirect()->back()->with('error', 'Vui lòng chọn file JSON hoặc dán nội dung JSON để import.');
        }

        $payload = json_decode($content, true);
        if (!is_array($payload)) {
            return redirect()->back()->with('error', 'JSON không hợp lệ. Vui lòng kiểm tra lại định dạng.');
        }

        $stylesData = $payload['styles'] ?? $payload;
        if (!is_array($stylesData)) {
            return redirect()->back()->with('error', 'Không tìm thấy danh sách styles trong JSON.');
        }

        $dryRun = $request->boolean('dry_run');
        $created = 0;
        $skipped = 0;
        $errors = [];

        foreach ($stylesData as $index => $styleData) {
            if (!is_array($styleData)) {
                $skipped++;
                $errors[] = "Style #{$index}: Dữ liệu không hợp lệ.";
                continue;
            }

            $name = trim((string) ($styleData['name'] ?? ''));
            $basePrompt = trim((string) ($styleData['base_prompt'] ?? ''));
            $modelId = trim((string) ($styleData['bfl_model_id'] ?? $styleData['model_id'] ?? ''));

            if ($name === '' || $basePrompt === '' || $modelId === '') {
                $skipped++;
                $errors[] = "Style #{$index}: Thiếu name/base_prompt/bfl_model_id.";
                continue;
            }

            $slugInput = trim((string) ($styleData['slug'] ?? ''));
            $slug = $slugInput !== '' ? $slugInput : Str::slug($name);
            $slug = $this->generateUniqueSlug($slug);

            $price = is_numeric($styleData['price'] ?? null) ? (float) $styleData['price'] : 2;
            $sortOrder = (int) ($styleData['sort_order'] ?? 0);

            $configPayload = is_array($styleData['config_payload'] ?? null) ? $styleData['config_payload'] : [];
            if (!empty($styleData['aspect_ratio']) && empty($configPayload['aspect_ratio'])) {
                $configPayload['aspect_ratio'] = $styleData['aspect_ratio'];
            }
            if (!empty($styleData['prompt_defaults']) && empty($configPayload['prompt_defaults'])) {
                $configPayload['prompt_defaults'] = $styleData['prompt_defaults'];
            }
            $configPayload = $this->mergeAdvancedConfig($configPayload, $configPayload);
            $configPayload = !empty($configPayload) ? $configPayload : null;

            $imageSlots = $this->processImageSlots($styleData['image_slots'] ?? []);
            $systemImages = $this->normalizeSystemImages($styleData['system_images'] ?? []);

            $tagId = null;
            if (!empty($styleData['tag_id'])) {
                $tagCandidate = (int) $styleData['tag_id'];
                $tagId = Tag::where('id', $tagCandidate)->exists() ? $tagCandidate : null;
            } elseif (!empty($styleData['tag'])) {
                $tagName = trim((string) $styleData['tag']);
                if ($tagName !== '') {
                    $tag = Tag::firstOrCreate(['name' => $tagName]);
                    $tagId = $tag->id;
                }
            }

            if ($dryRun) {
                $created++;
                continue;
            }

            try {
                DB::transaction(function () use (
                    $styleData,
                    $name,
                    $slug,
                    $basePrompt,
                    $modelId,
                    $price,
                    $sortOrder,
                    $configPayload,
                    $imageSlots,
                    $systemImages,
                    $tagId,
                    &$created
                ) {
                    $style = Style::create([
                        'name' => $name,
                        'slug' => $slug,
                        'description' => $styleData['description'] ?? null,
                        'thumbnail_url' => $styleData['thumbnail_url'] ?? null,
                        'price' => $price,
                        'sort_order' => $sortOrder,
                        'bfl_model_id' => $modelId,
                        'base_prompt' => $basePrompt,
                        'config_payload' => $configPayload,
                        'image_slots' => $imageSlots,
                        'system_images' => $systemImages,
                        'allow_user_custom_prompt' => !empty($styleData['allow_user_custom_prompt']),
                        'is_active' => array_key_exists('is_active', $styleData) ? (bool) $styleData['is_active'] : true,
                        'tag_id' => $tagId,
                    ]);

                    $this->importOptions($style, $styleData['options'] ?? []);

                    $created++;
                });
            } catch (\Throwable $e) {
                $skipped++;
                $errors[] = "Style #{$index}: " . $e->getMessage();
            }
        }

        $summary = "Import xong: {$created} style" . ($dryRun ? ' (dry-run)' : '') . ". Bỏ qua: {$skipped}.";
        if (!empty($errors)) {
            Log::warning('Style import errors', ['errors' => $errors]);
            return redirect()->back()->with('error', $summary . ' Có lỗi ở một số style, xem log để biết chi tiết.');
        }

        return redirect()
            ->route('admin.styles.index')
            ->with('success', $summary);
    }
    /**
     * Lưu Style mới
     */
    public function store(Request $request): RedirectResponse
    {
        $modelId = (string) $request->input('bfl_model_id', '');
        $cap = $this->bflService->getModelCapabilities($modelId);
        $minDim = (int) ($cap['min_dimension'] ?? config('services_custom.bfl.min_dimension', 256));
        $maxDim = (int) ($cap['max_dimension'] ?? config('services_custom.bfl.max_dimension', 1408));
        $multiple = (int) ($cap['dimension_multiple'] ?? config('services_custom.bfl.dimension_multiple', 32));
        $multiple = $multiple > 0 ? $multiple : 1;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:styles,slug',
            'description' => 'nullable|string|max:1000',
            'thumbnail_url' => 'nullable|url|max:500',
            'price' => 'required|numeric|min:0',
            'sort_order' => 'nullable|integer|min:0',
            'bfl_model_id' => [
                'required', 
                'string', 
                'max:255',
                // REMOVED: Strict validation against fetched models to allow manual input
            ],
            'base_prompt' => 'required|string|max:10000', // Limit prompt length
            // HIGH-05 FIX: Validate aspect_ratio against supported list
            'aspect_ratio' => ['nullable', 'string', 'max:20', Rule::in(array_keys($this->bflService->getAspectRatios()))],
            'config_payload' => 'nullable|array',
            'config_payload.width' => ['nullable', 'integer', "min:{$minDim}", "max:{$maxDim}", 'required_with:config_payload.height', function ($attribute, $value, $fail) use ($multiple) {
                if ($value !== null && $value % $multiple !== 0) {
                    $fail("Width phải là bội số của {$multiple}.");
                }
            }],
            'config_payload.height' => ['nullable', 'integer', "min:{$minDim}", "max:{$maxDim}", 'required_with:config_payload.width', function ($attribute, $value, $fail) use ($multiple) {
                if ($value !== null && $value % $multiple !== 0) {
                    $fail("Height phải là bội số của {$multiple}.");
                }
            }],
            'config_payload.seed' => 'nullable|integer|min:0',
            'config_payload.steps' => 'nullable|integer|min:1|max:50',
            'config_payload.guidance' => 'nullable|numeric|min:1.5|max:10',
            'config_payload.prompt_upsampling' => 'nullable|boolean',
            'config_payload.safety_tolerance' => 'nullable|integer|min:0|max:6',
            'config_payload.output_format' => ['nullable', 'string', Rule::in(['jpeg', 'png'])],
            'config_payload.raw' => 'nullable|boolean',
            'config_payload.image_prompt_strength' => 'nullable|numeric|min:0|max:1',
            'config_payload.prompt_template' => 'nullable|string|max:2000',
            'config_payload.prompt_prefix' => 'nullable|string|max:500',
            'config_payload.prompt_suffix' => 'nullable|string|max:500',
            'config_payload.prompt_strategy' => ['nullable', 'string', Rule::in(['standard', 'narrative'])],
            'config_payload.prompt_defaults' => 'nullable|array',
            'config_payload.prompt_defaults.subject' => 'nullable|string|max:500',
            'config_payload.prompt_defaults.action' => 'nullable|string|max:500',
            'config_payload.prompt_defaults.style' => 'nullable|string|max:500',
            'config_payload.prompt_defaults.context' => 'nullable|string|max:500',
            'config_payload.prompt_defaults.mood' => 'nullable|string|max:500',
            'config_payload.prompt_defaults.lighting' => 'nullable|string|max:500',
            'config_payload.prompt_defaults.color' => 'nullable|string|max:500',
            'config_payload.prompt_defaults.details' => 'nullable|string|max:500',
            'config_payload.prompt_defaults.technical' => 'nullable|string|max:500',
            'config_payload.prompt_defaults.custom' => 'nullable|string|max:500',
            'config_payload.prompt_defaults.misc' => 'nullable|string|max:500',
            'allow_user_custom_prompt' => 'nullable',
            'is_active' => 'nullable',
            'is_featured' => 'nullable',
            'is_new' => 'nullable',
            
            // Image Slots (Dynamic array)
            'image_slots' => 'nullable|array|max:10', // Max 10 slots
            // [FIX loi.md M8] Add distinct to prevent duplicate keys
            'image_slots.*.key' => ['required_with:image_slots', 'string', 'max:100', 'regex:/^[a-zA-Z0-9_-]+$/', 'distinct'],
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
                $configPayload = [];
                if (!empty($validated['aspect_ratio'])) {
                    $configPayload['aspect_ratio'] = $validated['aspect_ratio'];
                }
                $configPayload = $this->mergeAdvancedConfig($configPayload, $validated['config_payload'] ?? []);
                $configPayload = !empty($configPayload) ? $configPayload : null;

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
                    'bfl_model_id' => $validated['bfl_model_id'],
                    'base_prompt' => $validated['base_prompt'],
                    'config_payload' => $configPayload,
                    'image_slots' => $imageSlots,
                    'system_images' => $systemImages,
                    'allow_user_custom_prompt' => $request->boolean('allow_user_custom_prompt'),
                    'is_active' => $request->boolean('is_active'),
                    'tag_id' => $request->input('tag_id') ?: null,
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
    $aspectRatios = $this->bflService->getAspectRatios();
    $tags = Tag::active()->ordered()->get();

    return view('admin.styles.edit', [
        'style' => $style,
        'models' => $models,
        'groupedModels' => $groupedModels,
        'aspectRatios' => $aspectRatios,
        'tags' => $tags,
    ]);
}
    /**
     * Cập nhật Style
     */
    public function update(Request $request, Style $style): RedirectResponse
    {
        $modelId = (string) $request->input('bfl_model_id', $style->bfl_model_id);
        $cap = $this->bflService->getModelCapabilities($modelId);
        $minDim = (int) ($cap['min_dimension'] ?? config('services_custom.bfl.min_dimension', 256));
        $maxDim = (int) ($cap['max_dimension'] ?? config('services_custom.bfl.max_dimension', 1408));
        $multiple = (int) ($cap['dimension_multiple'] ?? config('services_custom.bfl.dimension_multiple', 32));
        $multiple = $multiple > 0 ? $multiple : 1;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:styles,slug,' . $style->id,
            'description' => 'nullable|string|max:1000',
            'thumbnail_url' => 'nullable|url|max:500',
            'price' => 'required|numeric|min:0',
            'sort_order' => 'nullable|integer|min:0',
            // [FIX IMG-01] Validate model image-capable same as store()
            'bfl_model_id' => [
                'required', 
                'string', 
                'max:255',
                // REMOVED: Strict validation against fetched models to allow manual input
            ],
            'base_prompt' => 'required|string|max:10000',
            // HIGH-05 FIX: Validate aspect_ratio against supported list
            'aspect_ratio' => ['nullable', 'string', 'max:20', Rule::in(array_keys($this->bflService->getAspectRatios()))],
            'config_payload' => 'nullable|array',
            'config_payload.width' => ['nullable', 'integer', "min:{$minDim}", "max:{$maxDim}", 'required_with:config_payload.height', function ($attribute, $value, $fail) use ($multiple) {
                if ($value !== null && $value % $multiple !== 0) {
                    $fail("Width phải là bội số của {$multiple}.");
                }
            }],
            'config_payload.height' => ['nullable', 'integer', "min:{$minDim}", "max:{$maxDim}", 'required_with:config_payload.width', function ($attribute, $value, $fail) use ($multiple) {
                if ($value !== null && $value % $multiple !== 0) {
                    $fail("Height phải là bội số của {$multiple}.");
                }
            }],
            'config_payload.seed' => 'nullable|integer|min:0',
            'config_payload.steps' => 'nullable|integer|min:1|max:50',
            'config_payload.guidance' => 'nullable|numeric|min:1.5|max:10',
            'config_payload.prompt_upsampling' => 'nullable|boolean',
            'config_payload.safety_tolerance' => 'nullable|integer|min:0|max:6',
            'config_payload.output_format' => ['nullable', 'string', Rule::in(['jpeg', 'png'])],
            'config_payload.raw' => 'nullable|boolean',
            'config_payload.image_prompt_strength' => 'nullable|numeric|min:0|max:1',
            'config_payload.prompt_template' => 'nullable|string|max:2000',
            'config_payload.prompt_prefix' => 'nullable|string|max:500',
            'config_payload.prompt_suffix' => 'nullable|string|max:500',
            'config_payload.prompt_strategy' => ['nullable', 'string', Rule::in(['standard', 'narrative'])],
            'config_payload.prompt_defaults' => 'nullable|array',
            'config_payload.prompt_defaults.subject' => 'nullable|string|max:500',
            'config_payload.prompt_defaults.action' => 'nullable|string|max:500',
            'config_payload.prompt_defaults.style' => 'nullable|string|max:500',
            'config_payload.prompt_defaults.context' => 'nullable|string|max:500',
            'config_payload.prompt_defaults.mood' => 'nullable|string|max:500',
            'config_payload.prompt_defaults.lighting' => 'nullable|string|max:500',
            'config_payload.prompt_defaults.color' => 'nullable|string|max:500',
            'config_payload.prompt_defaults.details' => 'nullable|string|max:500',
            'config_payload.prompt_defaults.technical' => 'nullable|string|max:500',
            'config_payload.prompt_defaults.custom' => 'nullable|string|max:500',
            'config_payload.prompt_defaults.misc' => 'nullable|string|max:500',
            'allow_user_custom_prompt' => 'nullable',
            'is_active' => 'nullable',
            'is_featured' => 'nullable',
            'is_new' => 'nullable',
            
            'image_slots' => 'nullable|array|max:10',
            // [FIX loi.md M8] Add distinct to prevent duplicate keys in update
            'image_slots.*.key' => ['required_with:image_slots', 'string', 'max:100', 'regex:/^[a-zA-Z0-9_-]+$/', 'distinct'],
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
                $configPayload = $this->mergeAdvancedConfig($configPayload, $validated['config_payload'] ?? []);
                $configPayload = !empty($configPayload) ? $configPayload : null;

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
                    'bfl_model_id' => $validated['bfl_model_id'],
                    'base_prompt' => $validated['base_prompt'],
                    'config_payload' => !empty($configPayload) ? $configPayload : null,
                    'image_slots' => $imageSlots,
                    'system_images' => !empty($systemImages) ? $systemImages : null,
                    'allow_user_custom_prompt' => $request->boolean('allow_user_custom_prompt'),
                    'is_active' => $request->boolean('is_active'),
                    'tag_id' => $request->input('tag_id') ?: null,
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

    /**
     * Merge advanced config_payload fields (sanitize + cast + allow clear)
     */
    private function mergeAdvancedConfig(array $base, array $input): array
    {
        if (empty($input)) {
            return $base;
        }

        if (array_key_exists('prompt_defaults', $input)) {
            $defaults = $input['prompt_defaults'];
            if (!is_array($defaults)) {
                unset($base['prompt_defaults']);
            } else {
                $cleanDefaults = [];
                foreach ($defaults as $slot => $value) {
                    $value = trim((string) $value);
                    if ($value !== '') {
                        $cleanDefaults[$slot] = $value;
                    }
                }
                if (empty($cleanDefaults)) {
                    unset($base['prompt_defaults']);
                } else {
                    $base['prompt_defaults'] = $cleanDefaults;
                }
            }
        }

        $map = [
            'seed' => 'int',
            'steps' => 'int',
            'guidance' => 'float',
            'prompt_upsampling' => 'bool',
            'safety_tolerance' => 'int',
            'output_format' => 'string',
            'raw' => 'bool',
            'image_prompt_strength' => 'float',
            'width' => 'int',
            'height' => 'int',
            'prompt_template' => 'string',
            'prompt_prefix' => 'string',
            'prompt_suffix' => 'string',
            'prompt_strategy' => 'string',
        ];

        foreach ($map as $key => $type) {
            if (!array_key_exists($key, $input)) {
                continue;
            }

            $value = $input[$key];
            if ($value === '' || $value === null) {
                unset($base[$key]);
                continue;
            }

            switch ($type) {
                case 'int':
                    $base[$key] = (int) $value;
                    break;
                case 'float':
                    $base[$key] = (float) $value;
                    break;
                case 'bool':
                    $base[$key] = (bool) $value;
                    break;
                case 'string':
                default:
                    $base[$key] = (string) $value;
                    break;
            }
        }

        return $base;
    }

    /**
     * Generate unique slug for imported styles
     */
    private function generateUniqueSlug(string $slugBase): string
    {
        $slugBase = Str::slug($slugBase);
        if ($slugBase === '') {
            $slugBase = 'style';
        }

        $slug = $slugBase;
        $counter = 1;
        while (Style::where('slug', $slug)->exists()) {
            $slug = $slugBase . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Import options with full fields
     */
    private function importOptions(Style $style, array $options): void
    {
        if (empty($options)) {
            return;
        }

        foreach ($options as $index => $optionData) {
            if (!is_array($optionData)) {
                continue;
            }

            $label = trim((string) ($optionData['label'] ?? ''));
            $groupName = trim((string) ($optionData['group_name'] ?? ''));
            $fragment = trim((string) ($optionData['prompt_fragment'] ?? ''));

            if ($label === '' || $groupName === '' || $fragment === '') {
                continue;
            }

            $style->options()->create([
                'label' => $label,
                'group_name' => $groupName,
                'prompt_fragment' => $fragment,
                'icon' => $optionData['icon'] ?? null,
                'thumbnail' => $optionData['thumbnail'] ?? null,
                'sort_order' => isset($optionData['sort_order']) ? (int) $optionData['sort_order'] : $index,
                'is_default' => !empty($optionData['is_default']),
            ]);
        }
    }

    /**
     * Normalize system_images import payload
     */
    private function normalizeSystemImages(array $systemImages): ?array
    {
        if (empty($systemImages)) {
            return null;
        }

        $result = [];
        foreach ($systemImages as $index => $img) {
            if (!is_array($img)) {
                continue;
            }

            $url = trim((string) ($img['url'] ?? $img['image_url'] ?? ''));
            if ($url === '') {
                continue;
            }

            $key = $img['key'] ?? ('sys_' . Str::random(10) . '_' . $index);

            $result[] = [
                'key' => $key,
                'label' => $img['label'] ?? ('System Image ' . ($index + 1)),
                'description' => $img['description'] ?? '',
                'path' => $img['path'] ?? '',
                'url' => $url,
            ];
        }

        return !empty($result) ? $result : null;
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Style;
use App\Models\StyleOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Admin Controller: Quản lý Style Options
 * 
 * CRUD cho các options (prompt fragments) của mỗi Style.
 * Routes: admin/styles/{style}/options
 */
class StyleOptionController extends Controller
{
    /**
     * Hiển thị danh sách options của một style
     */
    public function index(Style $style)
    {
        $options = $style->options()
            ->orderBy('group_name')
            ->orderBy('sort_order')
            ->get()
            ->groupBy('group_name');

        return view('admin.styles.options.index', compact('style', 'options'));
    }

    /**
     * Form tạo option mới
     */
    public function create(Style $style)
    {
        // Lấy danh sách group names hiện có để suggest
        $existingGroups = $style->options()
            ->distinct()
            ->pluck('group_name')
            ->toArray();

        return view('admin.styles.options.create', compact('style', 'existingGroups'));
    }

    /**
     * Lưu option mới
     */
    public function store(Request $request, Style $style)
    {
        $validated = $request->validate([
            'label' => 'required|string|max:255',
            'group_name' => 'required|string|max:100',
            'prompt_fragment' => 'required|string|max:500',
            'icon' => 'nullable|string|max:100',
            'thumbnail' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:1024',
            'sort_order' => 'nullable|integer|min:0',
            'is_default' => 'boolean',
        ]);

        $validated['sort_order'] = $validated['sort_order'] ?? 0;
        $validated['is_default'] = $request->boolean('is_default');

        // Upload thumbnail to local storage (public disk)
        if ($request->hasFile('thumbnail')) {
            $path = $request->file('thumbnail')->store('option-thumbnails', 'public');
            $validated['thumbnail'] = $path;
        }

        // L6: Nếu option mới là default, bỏ is_default của các option khác trong cùng group
        if ($validated['is_default']) {
            $style->options()
                ->where('group_name', $validated['group_name'])
                ->update(['is_default' => false]);
        }

        $style->options()->create($validated);

        return redirect()
            ->route('admin.styles.options.index', $style)
            ->with('success', 'Đã thêm option "' . $validated['label'] . '" thành công!');
    }

    /**
     * Form sửa option
     */
    public function edit(Style $style, StyleOption $option)
    {
        // Đảm bảo option thuộc về style
        if ($option->style_id !== $style->id) {
            abort(404);
        }

        $existingGroups = $style->options()
            ->distinct()
            ->pluck('group_name')
            ->toArray();

        return view('admin.styles.options.edit', compact('style', 'option', 'existingGroups'));
    }

    /**
     * Cập nhật option
     */
    public function update(Request $request, Style $style, StyleOption $option)
    {
        if ($option->style_id !== $style->id) {
            abort(404);
        }

        $validated = $request->validate([
            'label' => 'required|string|max:255',
            'group_name' => 'required|string|max:100',
            'prompt_fragment' => 'required|string|max:500',
            'icon' => 'nullable|string|max:100',
            'thumbnail' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:1024',
            'remove_thumbnail' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
            'is_default' => 'boolean',
        ]);

        $validated['is_default'] = $request->boolean('is_default');

        // Handle thumbnail upload/removal
        if ($request->boolean('remove_thumbnail') && $option->thumbnail) {
            Storage::disk('public')->delete($option->thumbnail);
            $validated['thumbnail'] = null;
        } elseif ($request->hasFile('thumbnail')) {
            // Delete old thumbnail if exists
            if ($option->thumbnail) {
                Storage::disk('public')->delete($option->thumbnail);
            }
            $path = $request->file('thumbnail')->store('option-thumbnails', 'public');
            $validated['thumbnail'] = $path;
        } else {
            unset($validated['thumbnail']);
        }
        unset($validated['remove_thumbnail']);

        // L6: Nếu option này được set default, bỏ is_default của các option khác trong cùng group
        if ($validated['is_default']) {
            $style->options()
                ->where('group_name', $validated['group_name'])
                ->where('id', '!=', $option->id)
                ->update(['is_default' => false]);
        }

        $option->update($validated);

        return redirect()
            ->route('admin.styles.options.index', $style)
            ->with('success', 'Đã cập nhật option "' . $validated['label'] . '"!');
    }

    /**
     * Xóa option
     */
    public function destroy(Style $style, StyleOption $option)
    {
        if ($option->style_id !== $style->id) {
            abort(404);
        }

        $label = $option->label;
        $option->delete();

        return redirect()
            ->route('admin.styles.options.index', $style)
            ->with('success', 'Đã xóa option "' . $label . '"!');
    }
}

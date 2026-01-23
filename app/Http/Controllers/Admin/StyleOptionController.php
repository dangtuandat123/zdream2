<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Style;
use App\Models\StyleOption;
use Illuminate\Http\Request;

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
            'sort_order' => 'nullable|integer|min:0',
            'is_default' => 'boolean',
        ]);

        $validated['sort_order'] = $validated['sort_order'] ?? 0;
        $validated['is_default'] = $request->boolean('is_default');

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
            'sort_order' => 'nullable|integer|min:0',
            'is_default' => 'boolean',
        ]);

        $validated['is_default'] = $request->boolean('is_default');

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

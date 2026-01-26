<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * TagController (Admin)
 * 
 * CRUD quản lý Tags cho Styles
 */
class TagController extends Controller
{
    /**
     * Danh sách Tags
     */
    public function index(): View
    {
        $tags = Tag::ordered()->withCount('styles')->get();

        return view('admin.tags.index', compact('tags'));
    }

    /**
     * Form tạo Tag mới
     */
    public function create(): View
    {
        return view('admin.tags.create');
    }

    /**
     * Lưu Tag mới
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:tags,name',
            'color_from' => 'required|string|max:30',
            'color_to' => 'required|string|max:30',
            'icon' => 'required|string|max:50',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable',
        ]);

        Tag::create([
            'name' => $validated['name'],
            'color_from' => $validated['color_from'],
            'color_to' => $validated['color_to'],
            'icon' => $validated['icon'],
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()
            ->route('admin.tags.index')
            ->with('success', 'Tag đã được tạo thành công!');
    }

    /**
     * Form chỉnh sửa Tag
     */
    public function edit(Tag $tag): View
    {
        return view('admin.tags.edit', compact('tag'));
    }

    /**
     * Cập nhật Tag
     */
    public function update(Request $request, Tag $tag): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:tags,name,' . $tag->id,
            'color_from' => 'required|string|max:30',
            'color_to' => 'required|string|max:30',
            'icon' => 'required|string|max:50',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable',
        ]);

        $tag->update([
            'name' => $validated['name'],
            'color_from' => $validated['color_from'],
            'color_to' => $validated['color_to'],
            'icon' => $validated['icon'],
            'sort_order' => $validated['sort_order'] ?? $tag->sort_order,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()
            ->route('admin.tags.index')
            ->with('success', 'Tag đã được cập nhật!');
    }

    /**
     * Xóa Tag
     */
    public function destroy(Tag $tag): RedirectResponse
    {
        // Xóa tag - styles sẽ bị set tag_id = null (onDelete: nullOnDelete)
        $tag->delete();

        return redirect()
            ->route('admin.tags.index')
            ->with('success', 'Tag đã được xóa!');
    }
}

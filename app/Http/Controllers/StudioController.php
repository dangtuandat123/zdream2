<?php

namespace App\Http\Controllers;

use App\Models\Style;
use Illuminate\View\View;

/**
 * StudioController
 * 
 * Trang Studio - nơi user tạo ảnh AI
 */
class StudioController extends Controller
{
    /**
     * Hiển thị trang Studio cho một Style
     */
    public function show(Style $style): View
    {
        // Kiểm tra style có active không
        if (!$style->is_active) {
            abort(404);
        }

        // Load options grouped by group_name
        $style->load(['options' => function ($query) {
            $query->orderBy('group_name')->orderBy('sort_order');
        }]);

        // Group options theo nhóm
        $optionGroups = $style->options->groupBy('group_name');

        return view('studio.show', compact('style', 'optionGroups'));
    }
}

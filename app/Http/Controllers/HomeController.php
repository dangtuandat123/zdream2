<?php

namespace App\Http\Controllers;

use App\Models\Style;
use Illuminate\View\View;

/**
 * HomeController
 * 
 * Trang chủ - Gallery hiển thị các Styles có sẵn
 */
class HomeController extends Controller
{
    /**
     * Hiển thị trang chủ với danh sách Styles
     * Mặc định sắp xếp theo lượt tạo nhiều nhất
     */
    public function index(): View
    {
        $styles = Style::query()
            ->active()
            ->with('tag') // Eager load tag relationship
            ->withCount('generatedImages')
            ->orderByDesc('generated_images_count')
            ->take(50) // Limit để tránh slow query
            ->get();

        return view('home', compact('styles'));
    }
}

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
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();

        $styles = Style::query()
            ->active()
            ->with('tag')
            ->withCount('generatedImages')
            ->withCount(['generatedImages as month_generated_count' => function ($query) use ($startOfMonth, $endOfMonth) {
                $query->whereBetween('created_at', [$startOfMonth, $endOfMonth]);
            }])
            ->orderByDesc('month_generated_count')
            ->orderByDesc('generated_images_count')
            ->take(8)
            ->get();

        return view('home', compact('styles'));
    }
}

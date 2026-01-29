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
        $featuredStyles = Style::query()
            ->active()
            ->featured()
            ->with('tag')
            ->withCount('generatedImages')
            ->orderByDesc('generated_images_count')
            ->take(8)
            ->get();

        if ($featuredStyles->count() < 8) {
            $missing = 8 - $featuredStyles->count();
            $fallback = Style::query()
                ->active()
                ->whereNotIn('id', $featuredStyles->pluck('id'))
                ->with('tag')
                ->withCount('generatedImages')
                ->orderByDesc('generated_images_count')
                ->take($missing)
                ->get();

            $featuredStyles = $featuredStyles->concat($fallback);
        }

        return view('home', [
            'styles' => $featuredStyles,
        ]);
    }
}

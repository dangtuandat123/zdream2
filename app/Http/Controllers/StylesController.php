<?php

namespace App\Http\Controllers;

use App\Models\Style;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * StylesController
 * 
 * Public gallery page for browsing all styles with search, filter, and pagination.
 */
class StylesController extends Controller
{
    /**
     * Display styles gallery with filtering and pagination.
     */
    public function index(Request $request): View
    {
        // [FIX UX-03] Khởi tạo biến filter để tránh undefined
        $search = $request->input('search', '');
        $priceFilter = $request->input('price');
        $sort = $request->input('sort', 'popular');

        // [FIX UX-02] Không gọi ordered() ở đây, để sort options quyết định
        $query = Style::query()
            ->active()
            ->with('tag')
            ->withCount('generatedImages');

        // Search by name
        if (!empty($search)) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        // Filter by price range
        if ($priceFilter) {
            match ($priceFilter) {
                'free' => $query->where('price', 0),
                'low' => $query->where('price', '>', 0)->where('price', '<=', 5),
                'mid' => $query->where('price', '>', 5)->where('price', '<=', 15),
                'high' => $query->where('price', '>', 15),
                default => null,
            };
        }

        // [FIX UX-02] Sort options - sử dụng reorder() để clear existing orders
        match ($sort) {
            'newest' => $query->reorder()->latest(),
            'price_asc' => $query->reorder()->orderBy('price', 'asc')->orderBy('name', 'asc'),
            'price_desc' => $query->reorder()->orderBy('price', 'desc')->orderBy('name', 'asc'),
            'popular' => $query->reorder()->orderByDesc('generated_images_count'),
            default => $query->ordered(), // Chỉ gọi ordered() ở default
        };

        $styles = $query->paginate(16)->withQueryString();

        // Get price ranges for filter UI
        $priceRanges = [
            'free' => 'Miễn phí',
            'low' => '1-5 Xu',
            'mid' => '6-15 Xu',
            'high' => '> 15 Xu',
        ];

        $sortOptions = [
            'popular' => 'Phổ biến',
            'newest' => 'Mới nhất',
            'price_asc' => 'Giá thấp → cao',
            'price_desc' => 'Giá cao → thấp',
        ];

        return view('styles.index', [
            'styles' => $styles,
            'priceRanges' => $priceRanges,
            'sortOptions' => $sortOptions,
            'currentSearch' => $search,
            'currentPrice' => $priceFilter,
            'currentSort' => $sort,
        ]);
    }
}

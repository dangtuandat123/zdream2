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
        $query = Style::query()
            ->active()
            ->with('tag') // Eager load tag
            ->ordered()
            ->withCount('generatedImages');

        // Search by name
        if ($search = $request->input('search')) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        // Filter by price range
        if ($priceFilter = $request->input('price')) {
            match ($priceFilter) {
                'free' => $query->where('price', 0),
                'low' => $query->where('price', '>', 0)->where('price', '<=', 5),
                'mid' => $query->where('price', '>', 5)->where('price', '<=', 15),
                'high' => $query->where('price', '>', 15),
                default => null,
            };
        }

        // Sort options
        $sort = $request->input('sort', 'popular');
        match ($sort) {
            'newest' => $query->latest(),
            'price_asc' => $query->orderBy('price', 'asc'),
            'price_desc' => $query->orderBy('price', 'desc'),
            'popular' => $query->orderByDesc('generated_images_count'),
            default => $query->ordered(),
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

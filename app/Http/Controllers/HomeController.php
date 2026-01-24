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
     */
    public function index(): View
    {
        $styles = Style::query()
            ->active()
            ->ordered()
            ->take(50) // Limit để tránh slow query
            ->get();

        return view('home', compact('styles'));
    }
}

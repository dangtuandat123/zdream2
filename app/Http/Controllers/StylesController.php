<?php

namespace App\Http\Controllers;

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
    public function index(): View
    {
        return view('styles.index');
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * HistoryController
 * 
 * Hiển thị lịch sử ảnh đã tạo của user
 */
class HistoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Hiển thị trang lịch sử ảnh
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $images = $user->generatedImages()
            ->with('style')
            ->latest()
            ->paginate(12);

        return view('history.index', [
            'images' => $images,
            'user' => $user,
        ]);
    }
}

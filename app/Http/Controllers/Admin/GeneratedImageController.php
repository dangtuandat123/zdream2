<?php

namespace App\Http\Controllers\Admin;

use App\Models\GeneratedImage;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Admin Generated Image Controller
 * 
 * Quản lý và moderate ảnh đã tạo
 */
class GeneratedImageController extends Controller
{
    /**
     * Display a listing of all generated images.
     */
    public function index(Request $request)
    {
        $query = GeneratedImage::with(['user', 'style'])->latest();

        // Filter by status
        if ($status = $request->get('status')) {
            $query->status($status);
        }

        // Filter by style
        if ($styleId = $request->get('style_id')) {
            $query->where('style_id', $styleId);
        }

        // Search by user
        if ($search = $request->get('search')) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Date filter
        if ($from = $request->get('from')) {
            $query->whereDate('created_at', '>=', $from);
        }
        if ($to = $request->get('to')) {
            $query->whereDate('created_at', '<=', $to);
        }

        $images = $query->paginate(24)->withQueryString();

        // Stats
        $stats = [
            'total' => GeneratedImage::count(),
            'completed' => GeneratedImage::completed()->count(),
            'failed' => GeneratedImage::failed()->count(),
            'today' => GeneratedImage::whereDate('created_at', today())->count(),
        ];

        // Styles for filter dropdown
        $styles = \App\Models\Style::orderBy('name')->get(['id', 'name']);

        return view('admin.images.index', compact('images', 'stats', 'styles'));
    }

    /**
     * Show image details.
     */
    public function show(GeneratedImage $image)
    {
        $image->load(['user', 'style']);
        
        return view('admin.images.show', compact('image'));
    }

    /**
     * Delete an image.
     */
    public function destroy(GeneratedImage $image)
    {
        try {
            // Delete from storage if exists
            if ($image->storage_path && !str_starts_with($image->storage_path, 'http')) {
                Storage::disk('minio')->delete($image->storage_path);
            }

            $userName = $image->user->name ?? 'Unknown';
            $image->delete();

            Log::info('Admin deleted generated image', [
                'admin_id' => auth()->id(),
                'image_id' => $image->id,
                'user_name' => $userName,
            ]);

            return redirect()
                ->route('admin.images.index')
                ->with('success', 'Đã xóa ảnh thành công.');

        } catch (\Exception $e) {
            Log::error('Failed to delete image', [
                'image_id' => $image->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Lỗi khi xóa ảnh: ' . $e->getMessage());
        }
    }
}

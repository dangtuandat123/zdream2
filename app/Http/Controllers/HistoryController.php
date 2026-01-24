<?php

namespace App\Http\Controllers;

use App\Models\GeneratedImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

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
        
        $query = $user->generatedImages()->with('style');

        // Filter by status
        if ($status = $request->get('status')) {
            if (in_array($status, ['completed', 'processing', 'failed', 'pending'])) {
                $query->where('status', $status);
            }
        }

        // Filter by style
        if ($styleId = $request->get('style_id')) {
            $query->where('style_id', (int)$styleId);
        }

        $images = $query->latest()->paginate(12)->withQueryString();

        // Get styles for filter dropdown
        $styles = \App\Models\Style::select('id', 'name')->orderBy('name')->get();

        return view('history.index', [
            'images' => $images,
            'user' => $user,
            'styles' => $styles,
        ]);
    }

    /**
     * Xóa ảnh của user
     */
    public function destroy(GeneratedImage $image)
    {
        $user = Auth::user();

        // Authorization: Chỉ cho phép xóa ảnh của chính mình
        if ($image->user_id !== $user->id) {
            abort(403, 'Bạn không có quyền xóa ảnh này.');
        }

        try {
            // Xóa file từ MinIO storage nếu có
            if ($image->storage_path) {
                // Nếu là full URL, extract path
                $path = $image->storage_path;
                if (!str_starts_with($path, 'http')) {
                    Storage::disk('minio')->delete($path);
                } else {
                    // Extract relative path from full URL
                    $parsedUrl = parse_url($path);
                    $relativePath = ltrim($parsedUrl['path'] ?? '', '/');
                    // Remove bucket name if present
                    $bucket = config('filesystems.disks.minio.bucket');
                    if (str_starts_with($relativePath, $bucket . '/')) {
                        $relativePath = substr($relativePath, strlen($bucket) + 1);
                    }
                    if (!empty($relativePath)) {
                        Storage::disk('minio')->delete($relativePath);
                    }
                }
            }

            $image->delete();

            Log::info('User deleted image', [
                'user_id' => $user->id,
                'image_id' => $image->id,
            ]);

            return redirect()->route('history.index')
                ->with('success', 'Đã xóa ảnh thành công!');

        } catch (\Exception $e) {
            Log::error('Failed to delete user image', [
                'user_id' => $user->id,
                'image_id' => $image->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('history.index')
                ->with('error', 'Không thể xóa ảnh. Vui lòng thử lại.');
        }
    }
}

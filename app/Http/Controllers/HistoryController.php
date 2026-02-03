<?php

namespace App\Http\Controllers;

use App\Models\GeneratedImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
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
        return view('history.index');
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

    /**
     * Download ảnh (proxy để bypass cross-origin)
     */
    public function download(GeneratedImage $image)
    {
        $user = Auth::user();

        // Authorization: Chỉ cho phép download ảnh của chính mình
        if ($image->user_id !== $user->id) {
            abort(403, 'Bạn không có quyền tải ảnh này.');
        }

        // Kiểm tra ảnh hoàn thành và có storage_path
        if ($image->status !== GeneratedImage::STATUS_COMPLETED || empty($image->storage_path)) {
            abort(404, 'Ảnh không tồn tại hoặc chưa hoàn thành.');
        }

        try {
            $path = $image->storage_path;

            // Nếu là full URL, cần fetch nội dung
            if (str_starts_with($path, 'http')) {
                // LOW-03 FIX: Dùng Http::get() thay vì file_get_contents
                $response = Http::timeout(30)->get($path);
                if (!$response->successful()) {
                    abort(404, 'Không thể tải ảnh từ URL.');
                }
                $content = $response->body();
                $filename = basename(parse_url($path, PHP_URL_PATH));
            } else {
                // Lấy từ MinIO storage
                if (!Storage::disk('minio')->exists($path)) {
                    abort(404, 'File không tồn tại trên storage.');
                }
                $content = Storage::disk('minio')->get($path);
                $filename = basename($path);
            }

            // Xác định mime type
            $extension = pathinfo($filename, PATHINFO_EXTENSION);
            $mimeTypes = [
                'png' => 'image/png',
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'webp' => 'image/webp',
                'gif' => 'image/gif',
            ];
            $mimeType = $mimeTypes[strtolower($extension)] ?? 'application/octet-stream';

            return response($content)
                ->header('Content-Type', $mimeType)
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
                ->header('Content-Length', strlen($content));

        } catch (\Exception $e) {
            Log::error('Failed to download image', [
                'user_id' => $user->id,
                'image_id' => $image->id,
                'error' => $e->getMessage(),
            ]);

            abort(500, 'Không thể tải ảnh. Vui lòng thử lại.');
        }
    }
}

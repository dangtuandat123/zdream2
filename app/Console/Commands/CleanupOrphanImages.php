<?php

namespace App\Console\Commands;

use App\Models\GeneratedImage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * CleanupOrphanImages
 * 
 * Xóa các ảnh orphan (failed status > 7 ngày, soft deleted > 30 ngày)
 * Dọn dẹp files trong storage không còn reference.
 */
class CleanupOrphanImages extends Command
{
    protected $signature = 'images:cleanup 
                            {--dry-run : Chỉ hiển thị, không xóa thật}
                            {--failed-days=7 : Số ngày giữ ảnh failed}
                            {--deleted-days=30 : Số ngày giữ soft deleted}';

    protected $description = 'Cleanup orphan và expired images';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $failedDays = (int) $this->option('failed-days');
        $deletedDays = (int) $this->option('deleted-days');

        $this->info($dryRun ? '🔍 DRY RUN MODE' : '🗑️ CLEANUP MODE');

        // 1. Xóa permanently các soft deleted > X ngày
        $this->cleanupSoftDeleted($deletedDays, $dryRun);

        // 2. Xóa các failed images > X ngày
        $this->cleanupFailedImages($failedDays, $dryRun);

        // 3. Xóa storage files không có reference
        $this->cleanupOrphanFiles($dryRun);

        $this->info('✅ Cleanup completed!');
        
        return Command::SUCCESS;
    }

    protected function cleanupSoftDeleted(int $days, bool $dryRun): void
    {
        $cutoff = now()->subDays($days);
        
        $query = GeneratedImage::onlyTrashed()
            ->where('deleted_at', '<', $cutoff);

        $count = $query->count();
        $this->info("📦 Soft deleted > {$days} days: {$count} records");

        if (!$dryRun && $count > 0) {
            // Delete storage files first
            $query->get()->each(function ($image) {
                if ($image->storage_path) {
                    Storage::disk('minio')->delete($image->storage_path);
                }
            });

            // Force delete from DB
            $query->forceDelete();
            
            Log::info('Cleanup: Force deleted old soft-deleted images', ['count' => $count]);
        }
    }

    protected function cleanupFailedImages(int $days, bool $dryRun): void
    {
        $cutoff = now()->subDays($days);

        $query = GeneratedImage::where('status', GeneratedImage::STATUS_FAILED)
            ->where('created_at', '<', $cutoff);

        $count = $query->count();
        $this->info("❌ Failed images > {$days} days: {$count} records");

        if (!$dryRun && $count > 0) {
            // Soft delete (không xóa thật, để có thể phục hồi)
            $query->delete();
            
            Log::info('Cleanup: Soft deleted old failed images', ['count' => $count]);
        }
    }

    protected function cleanupOrphanFiles(bool $dryRun): void
    {
        $disk = Storage::disk('minio');
        $basePath = 'generated-images';

        try {
            $files = $disk->allFiles($basePath);
        } catch (\Exception $e) {
            $this->warn("⚠️ Cannot list storage files: " . $e->getMessage());
            return;
        }

        $orphanCount = 0;

        foreach ($files as $file) {
            $exists = GeneratedImage::withTrashed()
                ->where('storage_path', $file)
                ->exists();

            if (!$exists) {
                $orphanCount++;
                if (!$dryRun) {
                    $disk->delete($file);
                }
            }
        }

        $this->info("🗂️ Orphan storage files: {$orphanCount} files");

        if (!$dryRun && $orphanCount > 0) {
            Log::info('Cleanup: Deleted orphan storage files', ['count' => $orphanCount]);
        }
    }
}

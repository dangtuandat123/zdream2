<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Cleanup orphan/expired images hàng ngày lúc 3 AM
        $schedule->command('images:cleanup')
            ->dailyAt('03:00')
            ->withoutOverlapping()
            ->runInBackground();
        
        // [FIX loi.md H1] Watchdog for stalled processing jobs
        // Runs every 5 minutes to fail/refund processing jobs older than 10 minutes
        $schedule->call(function () {
            $this->cleanupStalledProcessingJobs();
        })->name('watchdog:processing-jobs')
          ->everyFiveMinutes()
          ->withoutOverlapping()
          ->runInBackground();
    }
    
    /**
     * [FIX loi.md H1] Cleanup stalled processing jobs
     * Marks jobs stuck in processing > 10 minutes as failed and refunds credits
     */
    protected function cleanupStalledProcessingJobs(): void
    {
        $cutoffMinutes = (int) config('services_custom.bfl.processing_timeout_minutes', 10);
        $cutoff = now()->subMinutes($cutoffMinutes);
        
        $stalledImages = \App\Models\GeneratedImage::where('status', \App\Models\GeneratedImage::STATUS_PROCESSING)
            ->where('created_at', '<', $cutoff)
            ->get();
        
        foreach ($stalledImages as $image) {
            \Illuminate\Support\Facades\Log::warning('Watchdog: Stalled processing job found', [
                'image_id' => $image->id,
                'created_at' => $image->created_at,
                'minutes_old' => now()->diffInMinutes($image->created_at),
            ]);
            
            $image->markAsFailed('Timeout: Job không hoàn thành sau ' . $cutoffMinutes . ' phút (watchdog)');
            
            // Refund credits if applicable
            $user = $image->user;
            if ($user && $image->credits_used > 0) {
                $alreadyRefunded = \App\Models\WalletTransaction::where('source', 'refund')
                    ->where('reference_id', (string) $image->id)
                    ->exists();
                
                if (!$alreadyRefunded) {
                    try {
                        app(\App\Services\WalletService::class)->addCredits(
                            $user,
                            $image->credits_used,
                            'Hoàn tiền tự động do lỗi xử lý (watchdog)',
                            'refund',
                            (string) $image->id
                        );
                        
                        \Illuminate\Support\Facades\Log::info('Watchdog: Refunded credits', [
                            'image_id' => $image->id,
                            'user_id' => $user->id,
                            'credits' => $image->credits_used,
                        ]);
                    } catch (\Exception $e) {
                        \Illuminate\Support\Facades\Log::error('Watchdog: Refund failed', [
                            'image_id' => $image->id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            }
        }
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}

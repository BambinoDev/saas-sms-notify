<?php

namespace App\Jobs;

use App\Models\SmsLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CleanupOldSmsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private int $daysToKeep = 90
    ) {}

    public function handle(): void
    {
        Log::info('=== DÉBUT CLEANUP SMS LOGS ===', [
            'days_to_keep' => $this->daysToKeep
        ]);

        try {
            $cutoffDate = now()->subDays($this->daysToKeep);

            $deleted = SmsLog::where('created_at', '<', $cutoffDate)->delete();

            Log::info('=== CLEANUP SMS LOGS TERMINÉ ===', [
                'deleted_count' => $deleted,
                'cutoff_date' => $cutoffDate->toDateString()
            ]);

        } catch (\Exception $e) {
            Log::error('=== ERREUR CLEANUP SMS LOGS ===', [
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }
}
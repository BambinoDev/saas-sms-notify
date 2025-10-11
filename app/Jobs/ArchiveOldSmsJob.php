<?php

namespace App\Jobs;

use App\Models\SmsQueue;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ArchiveOldSmsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        Log::info('=== DÉBUT ARCHIVAGE SMS ANCIENS ===');

        try {
            // Archiver SMS passés (scheduled_at < aujourd'hui)
            $archived = SmsQueue::where('scheduled_at', '<', now()->startOfDay())
                ->where('status', '!=', 'delivered')
                ->update([
                    'status' => 'failed',
                    'error_message' => 'SMS non envoyé - date dépassée',
                ]);

            Log::info('=== ARCHIVAGE TERMINÉ ===', [
                'archived_count' => $archived
            ]);

        } catch (\Exception $e) {
            Log::error('=== ERREUR ARCHIVAGE ===', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}
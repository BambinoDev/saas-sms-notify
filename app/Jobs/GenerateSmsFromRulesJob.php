<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Services\SmsGenerationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateSmsFromRulesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Nombre de tentatives
     */
    public $tries = 3;

    /**
     * Timeout en secondes
     */
    public $timeout = 300; // 5 minutes

    /**
     * Execute the job
     */
    public function handle(SmsGenerationService $service): void
    {
        Log::info('GenerateSmsFromRulesJob started');

        try {
            $result = $service->generateForAllDueRules();

            Log::info('GenerateSmsFromRulesJob completed', [
                'rules_processed' => $result['total_rules'],
                'sms_generated' => $result['total_generated'],
                'duplicates' => $result['total_duplicates'],
                'errors' => $result['total_errors'],
            ]);

        } catch (\Exception $e) {
            Log::error('GenerateSmsFromRulesJob failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e; // Re-throw pour retry
        }
    }

    /**
     * Handle job failure
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('GenerateSmsFromRulesJob failed permanently', [
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}
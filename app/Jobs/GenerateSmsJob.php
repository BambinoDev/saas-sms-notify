<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Models\SmsRule;
use App\Services\SmsGenerationService;
use Carbon\Carbon;

class GenerateSmsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public $tries = 3;

    /**
     * The number of seconds the job can run before timing out.
     */
    public $timeout = 300; // 5 minutes

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(SmsGenerationService $smsService): void
    {
        Log::info('Starting SMS generation job');

        $startTime = now();
        $totalGenerated = 0;
        $totalSkipped = 0;
        $totalErrors = 0;
        $rulesProcessed = 0;

        try {
            // Get all active rules
            $rules = SmsRule::where('active', true)
                ->orderBy('priority', 'asc')
                ->get();

            Log::info("Active rules found", [
                'count' => $rules->count(),
            ]);

            if ($rules->isEmpty()) {
                Log::warning('No active rules found');
                return;
            }

            // Process each rule
            foreach ($rules as $rule) {
                try {
                    $stats = $smsService->generateForRule($rule);

                    $totalGenerated += $stats['generated'];
                    $totalSkipped += $stats['skipped'];
                    $totalErrors += $stats['errors'];
                    $rulesProcessed++;

                    Log::info("Rule processed", [
                        'rule_id' => $rule->id,
                        'rule_name' => $rule->name,
                        'generated' => $stats['generated'],
                        'skipped' => $stats['skipped'],
                        'errors' => $stats['errors'],
                    ]);

                } catch (\Exception $e) {
                    Log::error("Failed to process rule", [
                        'rule_id' => $rule->id,
                        'rule_name' => $rule->name,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            $duration = now()->diffInSeconds($startTime);

            Log::info('SMS generation completed', [
                'duration_seconds' => $duration,
                'rules_processed' => $rulesProcessed,
                'total_generated' => $totalGenerated,
                'total_skipped' => $totalSkipped,
                'total_errors' => $totalErrors,
            ]);

        } catch (\Exception $e) {
            Log::error('SMS generation job failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e; // Re-throw to trigger retry
        }
    }

    /**
     * Handle job failure
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('SMS generation job failed after all retries', [
            'error' => $exception->getMessage(),
        ]);

        // TODO: Send admin notification
    }
}

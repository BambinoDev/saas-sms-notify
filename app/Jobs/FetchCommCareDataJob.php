<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Services\CommCareService;
use App\Models\CaseModel;
use Carbon\Carbon;

class FetchCommCareDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public $tries = 3;

    /**
     * The number of seconds the job can run before timing out.
     */
    public $timeout = 600; // 10 minutes

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
    public function handle(CommCareService $commCareService): void
    {
        Log::info('Starting CommCare sync job');

        $startTime = now();
        $totalCases = 0;
        $newCases = 0;
        $updatedCases = 0;
        $errors = 0;

        try {
            // Get last sync date for incremental sync
            $lastSync = CaseModel::max('server_modified_on');
            $lastSyncDate = $lastSync ? Carbon::parse($lastSync)->toIso8601String() : null;

            Log::info('Last sync date', ['date' => $lastSyncDate]);

            $offset = 0;
            $limit = config('commcare.batch_size', 100);
            $hasMore = true;

            while ($hasMore) {
                // Fetch batch of cases
                $response = $commCareService->fetchCases($lastSyncDate, $limit, $offset);

                if (!$response || !isset($response['objects'])) {
                    Log::error('Invalid CommCare response');
                    break;
                }

                $cases = $response['objects'];
                $totalCount = $response['meta']['total_count'] ?? 0;

                Log::info("Processing batch", [
                    'offset' => $offset,
                    'count' => count($cases),
                    'total' => $totalCount,
                ]);

                // Process each case
                foreach ($cases as $caseData) {
                    try {
                        $mappedData = $commCareService->mapCaseFields($caseData);

                        if (!$mappedData['case_id']) {
                            Log::warning('Case missing case_id', ['data' => $caseData]);
                            $errors++;
                            continue;
                        }

                        // Upsert: update if exists, insert if new
                        $case = CaseModel::updateOrCreate(
                            ['case_id' => $mappedData['case_id']],
                            $mappedData
                        );

                        if ($case->wasRecentlyCreated) {
                            $newCases++;
                        } else {
                            $updatedCases++;
                        }

                        $totalCases++;

                    } catch (\Exception $e) {
                        Log::error('Error processing case', [
                            'case_id' => $caseData['case_id'] ?? 'unknown',
                            'error' => $e->getMessage(),
                        ]);
                        $errors++;
                    }
                }

                // Check if there are more cases to fetch
                $offset += $limit;
                $hasMore = ($offset < $totalCount);

                // Safety: stop after 1000 batches (100k cases)
                if ($offset > 100000) {
                    Log::warning('Sync stopped: too many cases');
                    break;
                }
            }

            $duration = now()->diffInSeconds($startTime);

            Log::info('CommCare sync completed', [
                'duration_seconds' => $duration,
                'total_cases' => $totalCases,
                'new_cases' => $newCases,
                'updated_cases' => $updatedCases,
                'errors' => $errors,
            ]);

        } catch (\Exception $e) {
            Log::error('CommCare sync failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e; // Re-throw to trigger retry
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('CommCare sync job failed after all retries', [
            'error' => $exception->getMessage(),
        ]);

        // TODO: Send admin notification
    }
}

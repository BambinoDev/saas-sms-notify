<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Organization;
use App\Models\CaseModel;
use App\Services\CommCareService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SyncCommCareCasesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 1800; // 30 minutes pour gérer les gros volumes
    public int $tries = 1;

    public function __construct(private int $organizationId)
    {
    }

    public function handle(CommCareService $commCareService): void
    {
        $organization = Organization::findOrFail($this->organizationId);

        try {
            Log::info('Starting CommCare sync', [
                'organization_id' => $organization->id,
                'case_type' => $organization->commcare_case_type,
            ]);

            $email = $organization->commcare_email;
            $apiKey = Crypt::decryptString($organization->commcare_api_key);
            $projectSpace = $organization->commcare_project_space;
            $caseType = $organization->commcare_case_type;

            $baseUrl = "https://www.commcarehq.org/a/{$projectSpace}/api/case/v1/";
            $params = [
                'case_type' => $caseType,
                'closed' => 'false', // ✅ Filtrer seulement les cases non fermés
                'limit' => 500, // Réduit pour éviter les timeouts
                'offset' => 0,
            ];

            // Première requête pour obtenir le total_count
            $this->updateProgress($organization, 0, null, 'Connexion à CommCare...');
            
            $url = $baseUrl . '?' . http_build_query($params);
            $response = $commCareService->makeApiCall($email, $apiKey, $url);

            if (!$response['success']) {
                throw new \Exception('Erreur API CommCare : ' . $response['message']);
            }

            $data = $response['data'];
            $totalCases = $data['meta']['total_count'] ?? 0;
            
            Log::info('CommCare total cases found', [
                'organization_id' => $organization->id,
                'total_cases' => $totalCases,
            ]);

            if ($totalCases === 0) {
                $this->updateProgress($organization, 0, 0, 'Aucun case trouvé', false);
                return;
            }

            $totalFetched = 0;
            $totalCreated = 0;
            $totalUpdated = 0;
            $hasMore = true;

            while ($hasMore) {
                $this->updateProgress($organization, $totalFetched, $totalCases, "Récupération des dossiers : {$totalFetched}/{$totalCases}");

                $url = $baseUrl . '?' . http_build_query($params);
                $response = $commCareService->makeApiCall($email, $apiKey, $url);

                if (!$response['success']) {
                    throw new \Exception('Erreur API CommCare : ' . $response['message']);
                }

                $data = $response['data'];
                $cases = $data['objects'] ?? [];

                foreach ($cases as $caseData) {
                    $result = $this->processCase($organization, $caseData);
                    if ($result === 'created') {
                        $totalCreated++;
                    } elseif ($result === 'updated') {
                        $totalUpdated++;
                    }
                    $totalFetched++;

                    if ($totalFetched % 10 === 0) {
                        $this->updateProgress($organization, $totalFetched, $totalCases, "Traitement : {$totalFetched}/{$totalCases} dossiers");
                    }
                }

                $hasMore = !empty($data['meta']['next']);
                $params['offset'] += $params['limit'];

                // Arrêter si on a traité tous les cases ou si pas de next
                if ($totalFetched >= $totalCases || !$hasMore) {
                    Log::info('Sync completed: all cases processed', [
                        'organization_id' => $organization->id,
                        'total_fetched' => $totalFetched,
                        'total_expected' => $totalCases,
                    ]);
                    break;
                }
            }

            $this->updateProgress($organization, $totalFetched, $totalCases, "Synchronisation terminée : {$totalCreated} créés, {$totalUpdated} mis à jour", false);

            $organization->update([
                'last_commcare_sync_at' => now(),
            ]);

            Log::info('CommCare sync completed', [
                'organization_id' => $organization->id,
                'total_fetched' => $totalFetched,
                'created' => $totalCreated,
                'updated' => $totalUpdated,
            ]);
        } catch (\Exception $e) {
            Log::error('CommCare sync failed', [
                'organization_id' => $organization->id,
                'error' => $e->getMessage(),
            ]);

            $this->updateProgress($organization, 0, 0, "Erreur : {$e->getMessage()}", false);
            throw $e;
        }
    }

    private function processCase(Organization $organization, array $caseData): string
    {
        $properties = $caseData['properties'] ?? [];

        $mappedData = [
            'organization_id' => $organization->id,
            'case_id' => $caseData['case_id'] ?? null,
        ];

        foreach ($organization->case_properties_mapping as $property) {
            $value = $properties[$property] ?? null;
            switch ($property) {
                case 'case_name':
                case 'name':
                    $mappedData['case_name'] = $value;
                    break;
                case $organization->phone_number_field:
                    $mappedData['contact_phone_number'] = $value;
                    break;
                case 'next_visit_date':
                case 'next_appointment':
                    $mappedData['next_visit_date'] = $value ? Carbon::parse($value) : null;
                    break;
                case 'structure_sanitaire':
                case 'facility':
                    $mappedData['structure_sanitaire'] = $value;
                    break;
                case 'district_sanitaire':
                case 'district':
                    $mappedData['district_sanitaire'] = $value;
                    break;
                case 'region_sanitaire':
                case 'region':
                    $mappedData['region_sanitaire'] = $value;
                    break;
            }
        }

        // API v1 utilise server_date_modified au lieu de server_modified_on
        if (isset($caseData['server_date_modified'])) {
            $mappedData['server_modified_on'] = Carbon::parse($caseData['server_date_modified']);
        } elseif (isset($caseData['server_modified_on'])) {
            $mappedData['server_modified_on'] = Carbon::parse($caseData['server_modified_on']);
        }

        $case = CaseModel::updateOrCreate(
            [
                'organization_id' => $organization->id,
                'case_id' => $mappedData['case_id'],
            ],
            $mappedData
        );

        return $case->wasRecentlyCreated ? 'created' : 'updated';
    }

    private function updateProgress(Organization $organization, int $current, ?int $total, string $message, bool $isSyncing = true): void
    {
        $progress = $total > 0 ? round(($current / $total) * 100, 1) : 0;
        Cache::put("sync_status_{$organization->id}", [
            'is_syncing' => $isSyncing,
            'progress' => $progress,
            'total' => $total ?? $current,
            'current' => $current,
            'message' => $message,
            'updated_at' => now()->toIso8601String(),
        ], 3600);
    }
}



<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\TenantCommCareConfig;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CommCareSyncService
{
    private ?TenantCommCareConfig $config = null;

    public function setConfig(TenantCommCareConfig $config): self
    {
        $this->config = $config;
        return $this;
    }

    public function testConnection(): bool
    {
        try {
            $response = Http::withBasicAuth($this->config->commcare_username, $this->config->commcare_api_key)
                ->get("https://www.commcarehq.org/a/{$this->config->commcare_domain}/api/v0.5/case/");

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('CommCare connection test failed', [
                'error' => $e->getMessage(),
                'domain' => $this->config->commcare_domain
            ]);
            return false;
        }
    }

    public function syncCases(int $limit = 1000): array
    {
        if (!$this->config) {
            throw new \Exception('CommCare config not set. Call setConfig() first.');
        }

        try {
            $url = "https://www.commcarehq.org/a/{$this->config->commcare_domain}/api/v0.5/case/";
            $params = [
                'limit' => $limit,
                'format' => 'json'
            ];

            // Ajouter le case_type si spécifié
            if ($this->config->default_case_type) {
                $params['case_type'] = $this->config->default_case_type;
            }

            $response = Http::withBasicAuth($this->config->commcare_username, $this->config->commcare_api_key)
                ->get($url, $params);

            if (!$response->successful()) {
                return [
                    'success' => false,
                    'total' => 0,
                    'synced' => 0,
                    'errors' => ['HTTP Error: ' . $response->status()]
                ];
            }

            $data = $response->json();
            $cases = $data['objects'] ?? [];

            // Structure de base - à implémenter complètement plus tard
            return [
                'success' => true,
                'total' => count($cases),
                'synced' => 0, // Sera implémenté avec la logique de sync
                'errors' => []
            ];

        } catch (\Exception $e) {
            Log::error('CommCare sync failed', [
                'error' => $e->getMessage(),
                'domain' => $this->config->commcare_domain
            ]);

            return [
                'success' => false,
                'total' => 0,
                'synced' => 0,
                'errors' => [$e->getMessage()]
            ];
        }
    }

    public function getCaseTypes(): array
    {
        if (!$this->config) {
            throw new \Exception('CommCare config not set. Call setConfig() first.');
        }

        try {
            $url = "https://www.commcarehq.org/a/{$this->config->commcare_domain}/api/v0.5/case_type/";
            
            $response = Http::withBasicAuth($this->config->commcare_username, $this->config->commcare_api_key)
                ->get($url);

            if (!$response->successful()) {
                return [];
            }

            $data = $response->json();
            return $data['objects'] ?? [];

        } catch (\Exception $e) {
            Log::error('CommCare get case types failed', [
                'error' => $e->getMessage(),
                'domain' => $this->config->commcare_domain
            ]);
            return [];
        }
    }

    public function getCaseDetails(string $caseId): ?array
    {
        if (!$this->config) {
            throw new \Exception('CommCare config not set. Call setConfig() first.');
        }

        try {
            $url = "https://www.commcarehq.org/a/{$this->config->commcare_domain}/api/v0.5/case/{$caseId}/";
            
            $response = Http::withBasicAuth($this->config->commcare_username, $this->config->commcare_api_key)
                ->get($url);

            if (!$response->successful()) {
                return null;
            }

            return $response->json();

        } catch (\Exception $e) {
            Log::error('CommCare get case details failed', [
                'error' => $e->getMessage(),
                'case_id' => $caseId,
                'domain' => $this->config->commcare_domain
            ]);
            return null;
        }
    }

    public function updateConfigConnectionStatus(): void
    {
        if (!$this->config) {
            return;
        }

        $isValid = $this->testConnection();
        
        $this->config->update([
            'connection_valid' => $isValid,
            'last_connection_test' => now(),
        ]);
    }

    public function getApiInfo(): array
    {
        if (!$this->config) {
            throw new \Exception('CommCare config not set. Call setConfig() first.');
        }

        try {
            $url = "https://www.commcarehq.org/a/{$this->config->commcare_domain}/api/v0.5/";
            
            $response = Http::withBasicAuth($this->config->commcare_username, $this->config->commcare_api_key)
                ->get($url);

            if (!$response->successful()) {
                return [];
            }

            return $response->json();

        } catch (\Exception $e) {
            Log::error('CommCare get API info failed', [
                'error' => $e->getMessage(),
                'domain' => $this->config->commcare_domain
            ]);
            return [];
        }
    }
}

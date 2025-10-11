<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CommCareService
{
    protected $domain;
    protected $username;
    protected $password;
    protected $apiUrl;

    public function __construct()
    {
        $this->domain = config('commcare.domain');
        $this->username = config('commcare.username');
        $this->password = config('commcare.password');
        $this->apiUrl = str_replace('{domain}', $this->domain, config('commcare.api_url'));
    }

    /**
     * Fetch cases from CommCare API
     * 
     * @param string|null $lastSyncDate Date of last sync (ISO format)
     * @param int $limit Number of cases to fetch
     * @param int $offset Pagination offset
     * @return array|null
     */
    public function fetchCases($lastSyncDate = null, $limit = 100, $offset = 0)
    {
        try {
            $url = "{$this->apiUrl}/case/";
            
            $params = [
                'type' => config('commcare.case_type'),
                'limit' => $limit,
                'offset' => $offset,
            ];

            // Incremental sync: only fetch cases modified since last sync
            if ($lastSyncDate) {
                $params['server_date_modified_start'] = $lastSyncDate;
            }

            Log::info('CommCare API Request', [
                'url' => $url,
                'params' => $params,
            ]);

            $response = Http::withBasicAuth($this->username, $this->password)
                ->timeout(config('commcare.timeout'))
                ->get($url, $params);

            if ($response->successful()) {
                $data = $response->json();
                
                Log::info('CommCare API Response', [
                    'total_count' => $data['meta']['total_count'] ?? 0,
                    'limit' => $data['meta']['limit'] ?? 0,
                    'offset' => $data['meta']['offset'] ?? 0,
                ]);

                return $data;
            }

            Log::error('CommCare API Error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;

        } catch (\Exception $e) {
            Log::error('CommCare API Exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return null;
        }
    }

    /**
     * Map CommCare case to database fields
     * 
     * @param array $caseData Raw case data from CommCare
     * @return array Mapped fields for database
     */
    public function mapCaseFields($caseData)
    {
                return [
            'case_id' => $caseData['case_id'] ?? null,
            'case_name' => $caseData['properties']['case_name'] ?? null,
            'contact_phone_number' => $this->formatPhone($caseData['properties']['contact_phone_number'] ?? null),
            'structure_sanitaire' => $caseData['properties']['structure_sanitaire'] ?? null,
            'district_sanitaire' => $caseData['properties']['district_sanitaire'] ?? null,
            'region_sanitaire' => $caseData['properties']['region_sanitaire'] ?? null,
            'next_visit_date' => $this->parseDate($caseData['properties']['next_visit_date'] ?? null),
            'server_modified_on' => $this->parseDate($caseData['server_date_modified'] ?? null),
            'updated_at' => now(),
            'created_at' => now(),
        ];
    }

    /**
     * Format phone number for Côte d'Ivoire
     */
    private function formatPhone($phone)
    {
        if (!$phone) {
            return null;
        }

        // Remove all non-digit characters
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Check if valid Ivorian number (10 digits starting with 01, 05, or 07)
        if (strlen($phone) === 10 && in_array(substr($phone, 0, 2), ['01', '05', '07'])) {
            return '+225 ' . substr($phone, 0, 2) . ' ' . substr($phone, 2, 2) . ' ' . substr($phone, 4, 2) . ' ' . substr($phone, 6, 2) . ' ' . substr($phone, 8, 2);
        }

        // Invalid number
        return null;
    }

    /**
     * Parse date from CommCare format
     */
    private function parseDate($date)
    {
        if (!$date) {
            return null;
        }

        try {
            return Carbon::parse($date);
        } catch (\Exception $e) {
            return null;
        }
    }
}

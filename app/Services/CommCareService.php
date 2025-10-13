<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CommCareService
{
    private string $baseUrl = 'https://www.commcarehq.org';
    private int $timeout = 30;

    /**
     * Test la connexion à CommCare
     * Endpoint utilisé : /a/{project}/api/v0.5/application/
     * Note : Ce endpoint reste en v0.5 car c'est pour les applications, pas les cases
     * 
     * @param string $email Email utilisateur CommCare
     * @param string $apiKey Clé API CommCare
     * @param string $projectSpace Nom du projet (domain)
     * @return array ['success' => bool, 'message' => string, 'data' => array|null]
     */
    public function testConnection(string $email, string $apiKey, string $projectSpace): array
    {
        try {
            Log::info('Testing CommCare connection', [
                'project_space' => $projectSpace,
                'email' => $email,
            ]);

            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Authorization' => "ApiKey {$email}:{$apiKey}",
                ])
                ->get("{$this->baseUrl}/a/{$projectSpace}/api/v0.5/application/");

            if ($response->successful()) {
                $data = $response->json();
                $applicationsCount = count($data['objects'] ?? []);
                
                Log::info('CommCare connection test successful', [
                    'project_space' => $projectSpace,
                    'applications_count' => $applicationsCount,
                ]);

                return [
                    'success' => true,
                    'message' => "Connexion réussie ! {$applicationsCount} application(s) trouvée(s).",
                    'data' => $data,
                ];
            }

            Log::warning('CommCare connection test failed', [
                'status' => $response->status(),
                'project_space' => $projectSpace,
                'response_body' => $response->body(),
            ]);

            return [
                'success' => false,
                'message' => "Échec de connexion (HTTP {$response->status()}). Vérifiez vos identifiants.",
                'data' => null,
            ];

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('CommCare connection timeout', [
                'error' => $e->getMessage(),
                'project_space' => $projectSpace,
            ]);

            return [
                'success' => false,
                'message' => "Délai d'attente dépassé (30s). Vérifiez votre connexion internet.",
                'data' => null,
            ];

        } catch (\Exception $e) {
            Log::error('CommCare connection error', [
                'error' => $e->getMessage(),
                'project_space' => $projectSpace,
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => "Erreur : {$e->getMessage()}",
                'data' => null,
            ];
        }
    }

    /**
     * Récupère les case types disponibles dans le projet
     * Endpoint utilisé : /a/{project}/api/case/v1/?closed=false&limit=1000
     * 
     * LIMITATION : L'API CommCare n'a pas d'endpoint pour lister tous les case types.
     * Cette méthode récupère un échantillon de cases et extrait les types uniques.
     * Certains case types rares peuvent ne pas apparaître dans l'échantillon.
     * 
     * SOLUTION : Dans l'interface, permettre à l'utilisateur de saisir manuellement
     * un case type s'il ne le trouve pas dans la liste suggérée.
     * 
     * @param string $email
     * @param string $apiKey
     * @param string $projectSpace
     * @param int $sampleSize Nombre de cases à récupérer (1000 par défaut)
     * @return array Liste des case types uniques trouvés dans l'échantillon
     */
    public function fetchCaseTypes(string $email, string $apiKey, string $projectSpace, int $sampleSize = 1000): array
    {
        try {
            Log::info('Fetching case types', [
                'project_space' => $projectSpace,
                'sample_size' => $sampleSize,
            ]);

            // Récupérer un échantillon de cases pour extraire les types
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Authorization' => "ApiKey {$email}:{$apiKey}",
                ])
                ->get("{$this->baseUrl}/a/{$projectSpace}/api/case/v1/", [
                    'closed' => 'false',  // Seulement les cases ouverts
                    'limit' => $sampleSize, // Taille de l'échantillon
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $cases = $data['objects'] ?? [];
                
                // Extraire les case_type uniques
                // Dans l'API v1, case_type est dans properties.case_type
                $caseTypes = collect($cases)
                    ->pluck('properties.case_type')
                    ->unique()
                    ->filter()
                    ->sort()
                    ->values()
                    ->toArray();

                Log::info('CommCare case types fetched', [
                    'project_space' => $projectSpace,
                    'case_types' => $caseTypes,
                    'count' => count($caseTypes),
                    'total_cases_fetched' => count($cases),
                    'total_cases_in_project' => $data['meta']['total_count'] ?? 'unknown',
                ]);

                return $caseTypes;
            }

            Log::warning('Failed to fetch case types', [
                'status' => $response->status(),
                'project_space' => $projectSpace,
                'response_body' => $response->body(),
            ]);

            return [];

        } catch (\Exception $e) {
            Log::error('Error fetching case types', [
                'error' => $e->getMessage(),
                'project_space' => $projectSpace,
            ]);

            return [];
        }
    }

    /**
     * Vérifie si un case type existe dans le projet
     * Tente de récupérer au moins 1 case de ce type
     * 
     * @param string $email
     * @param string $apiKey
     * @param string $projectSpace
     * @param string $caseType
     * @return bool True si le case type existe, false sinon
     */
    public function validateCaseType(string $email, string $apiKey, string $projectSpace, string $caseType): bool
    {
        try {
            Log::info('Validating case type', [
                'project_space' => $projectSpace,
                'case_type' => $caseType,
            ]);

            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Authorization' => "ApiKey {$email}:{$apiKey}",
                ])
                ->get("{$this->baseUrl}/a/{$projectSpace}/api/case/v1/", [
                    'closed' => 'false',
                    'case_type' => $caseType,
                    'limit' => 1,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $totalCount = $data['meta']['total_count'] ?? 0;
                $exists = $totalCount > 0;

                Log::info('Case type validation result', [
                    'project_space' => $projectSpace,
                    'case_type' => $caseType,
                    'exists' => $exists,
                    'total_count' => $totalCount,
                ]);

                return $exists;
            }

            Log::warning('Failed to validate case type', [
                'status' => $response->status(),
                'project_space' => $projectSpace,
                'case_type' => $caseType,
            ]);

            return false;

        } catch (\Exception $e) {
            Log::error('Error validating case type', [
                'error' => $e->getMessage(),
                'project_space' => $projectSpace,
                'case_type' => $caseType,
            ]);

            return false;
        }
    }

    /**
     * Récupère le case le plus récent d'un type donné
     * Endpoint utilisé : /a/{project}/api/case/v1/?closed=false&case_type={type}&limit=1
     * 
     * @param string $email
     * @param string $apiKey
     * @param string $projectSpace
     * @param string $caseType
     * @return object|null Objet case ou null si aucun trouvé
     */
    public function fetchLatestCase(string $email, string $apiKey, string $projectSpace, string $caseType): ?object
    {
        try {
            Log::info('Fetching latest case', [
                'project_space' => $projectSpace,
                'case_type' => $caseType,
            ]);

            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Authorization' => "ApiKey {$email}:{$apiKey}",
                ])
                ->get("{$this->baseUrl}/a/{$projectSpace}/api/case/v1/", [
                    'closed' => 'false',        // Seulement les cases ouverts
                    'case_type' => $caseType,   // Filtrer par type
                    'limit' => 1,               // Un seul case suffit
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $cases = $data['objects'] ?? [];

                if (count($cases) > 0) {
                    $case = $cases[0];
                    
                    Log::info('Latest case fetched', [
                        'project_space' => $projectSpace,
                        'case_type' => $caseType,
                        'case_id' => $case['case_id'] ?? 'unknown',
                    ]);

                    return (object) $case;
                }

                Log::warning('No cases found for type', [
                    'project_space' => $projectSpace,
                    'case_type' => $caseType,
                ]);
            } else {
                Log::warning('Failed to fetch latest case', [
                    'status' => $response->status(),
                    'project_space' => $projectSpace,
                    'case_type' => $caseType,
                    'response_body' => $response->body(),
                ]);
            }

            return null;

        } catch (\Exception $e) {
            Log::error('Error fetching latest case', [
                'error' => $e->getMessage(),
                'project_space' => $projectSpace,
                'case_type' => $caseType,
            ]);

            return null;
        }
    }

    /**
     * Extrait toutes les propriétés d'un case
     * Retourne : Case Metadata + Special Case Properties + Custom Properties
     * 
     * Structure d'un case CommCare v1 :
     * - case_id, user_id, date_modified, closed, date_closed (metadata)
     * - properties: { case_name, case_type, owner_id, external_id, date_opened, ... custom fields }
     * 
     * @param object $case Objet case récupéré de l'API
     * @return array Liste des noms de propriétés (triée alphabétiquement)
     */
    public function extractProperties(object $case): array
    {
        $properties = [];

        // Case Metadata (au niveau racine du case)
        $metadata = [
            'case_id',
            'user_id',
            'date_modified',
            'closed',
            'date_closed',
            'domain',
            'opened_by',
            'closed_by',
            'server_date_modified',
            'server_date_opened',
        ];

        // Ajouter les metadata qui existent
        foreach ($metadata as $field) {
            if (isset($case->$field)) {
                $properties[] = $field;
            }
        }

        // Propriétés du case (dans case->properties)
        if (isset($case->properties)) {
            // Convertir l'objet properties en array
            $propertiesData = json_decode(json_encode($case->properties), true);
            if (is_array($propertiesData)) {
                $caseProperties = array_keys($propertiesData);
                $properties = array_merge($properties, $caseProperties);
            }
        }

        // Indices (relations avec d'autres cases)
        if (isset($case->indices) && is_object($case->indices)) {
            $indicesData = json_decode(json_encode($case->indices), true);
            if (is_array($indicesData) && count($indicesData) > 0) {
                // Ajouter "indices" comme propriété disponible
                $properties[] = 'indices';
            }
        }

        // Supprimer les doublons et trier
        $properties = array_unique($properties);
        sort($properties);

        Log::info('Case properties extracted', [
            'case_id' => $case->case_id ?? 'unknown',
            'case_type' => $case->properties->case_type ?? 'unknown',
            'properties_count' => count($properties),
            'properties' => $properties,
        ]);

        return $properties;
    }
}
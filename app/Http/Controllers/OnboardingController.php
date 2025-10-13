<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\CommCareService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;

class OnboardingController extends Controller
{
    public function __construct(
        private readonly CommCareService $commCareService
    ) {}

    // ============================================
    // ÉTAPE 1 : WELCOME
    // ============================================
    
    /**
     * Page d'accueil de l'onboarding
     */
    public function welcome(): Response
    {
        $organization = Auth::user()->organization;

        return Inertia::render('Onboarding/Welcome', [
            'organization' => $organization,
        ]);
    }

    // ============================================
    // ÉTAPE 2 : COMPANY
    // ============================================
    
    /**
     * Affiche le formulaire Company
     */
    public function company(): Response
    {
        $organization = Auth::user()->organization;

        return Inertia::render('Onboarding/Company', [
            'organization' => $organization,
        ]);
    }

    /**
     * Sauvegarde les données Company
     */
    public function storeCompany(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'organization_type' => 'required|string|in:hospital,health_center,school,cooperative,ngo,local_ngo,ministry,other',
            'sector' => 'required|string|in:health,education,agriculture,ngo,government,other',
            'timezone' => 'required|timezone',
            'team_size' => 'required|string|in:1-10,10-50,50-200,200+',
            'project_description' => 'nullable|string|max:500',
        ]);

        $organization = Auth::user()->organization;
        
        $organization->update([
            'organization_type' => $validated['organization_type'],
            'sector' => $validated['sector'],
            'timezone' => $validated['timezone'],
            'team_size' => $validated['team_size'],
            'project_description' => $validated['project_description'] ?? null,
            'onboarding_step' => 3, // Passer à l'étape suivante
        ]);

        Log::info('Onboarding Company completed', [
            'organization_id' => $organization->id,
            'organization_type' => $validated['organization_type'],
        ]);

        return redirect()->route('onboarding.commcare');
    }

    // ============================================
    // ÉTAPE 3 : COMMCARE
    // ============================================
    
    /**
     * Affiche le formulaire CommCare
     */
    public function commcare(): Response
    {
        $organization = Auth::user()->organization;

        return Inertia::render('Onboarding/CommCare', [
            'organization' => $organization,
        ]);
    }

    /**
     * Test de connexion CommCare (AJAX)
     */
    public function testCommcare(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'api_key' => 'required|string',
            'project_space' => 'required|string',
        ]);

        // Appeler le service CommCare
        $result = $this->commCareService->testConnection(
            $validated['email'],
            $validated['api_key'],
            $validated['project_space']
        );

        Log::info('CommCare connection test', [
            'success' => $result['success'],
            'project_space' => $validated['project_space'],
        ]);

        return response()->json($result);
    }

    /**
     * Sauvegarde la configuration CommCare
     */
    public function storeCommcare(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'domain' => 'required|string',
            'api_key' => 'required|string',
            'app_id' => 'nullable|string',
            'project_name' => 'required|string',
        ]);

        $organization = Auth::user()->organization;
        
        // ⚠️ ENCRYPTION de l'API Key
        $organization->update([
            'commcare_email' => $validated['email'],
            'commcare_api_key' => Crypt::encryptString($validated['api_key']),
            'commcare_domain' => $validated['domain'],
            'commcare_project_space' => $validated['domain'],
            'commcare_app_id' => $validated['app_id'] ?? null,
            'commcare_project_name' => $validated['project_name'],
            'onboarding_step' => 4,
        ]);

        Log::info('Onboarding CommCare completed', [
            'organization_id' => $organization->id,
            'project_space' => $validated['domain'],
        ]);

        return redirect()->route('onboarding.mapping');
    }

    // ============================================
    // ÉTAPE 4 : MAPPING
    // ============================================
    
    /**
     * Affiche le formulaire Mapping
     */
    public function mapping(): Response
    {
        $organization = Auth::user()->organization;

        return Inertia::render('Onboarding/Mapping', [
            'organization' => $organization,
        ]);
    }

    /**
     * Récupère les case types disponibles (AJAX)
     */
    public function fetchCaseTypes(Request $request): JsonResponse
    {
        $organization = Auth::user()->organization;

        // Vérifier que la config CommCare existe
        if (!$organization->commcare_api_key) {
            return response()->json([
                'success' => false,
                'message' => 'Configuration CommCare manquante. Veuillez compléter l\'étape précédente.',
            ], 400);
        }

        try {
            // Décrypter l'API Key
            $email = $organization->commcare_email;
            $apiKey = Crypt::decryptString($organization->commcare_api_key);
            $projectSpace = $organization->commcare_project_space;

            // Fetch les case types
            $caseTypes = $this->commCareService->fetchCaseTypes(
                $email,
                $apiKey,
                $projectSpace
            );

            Log::info('Case types fetched', [
                'organization_id' => $organization->id,
                'count' => count($caseTypes),
            ]);

            return response()->json([
                'success' => true,
                'case_types' => $caseTypes,
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching case types', [
                'organization_id' => $organization->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des case types : ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Valide un case type saisi manuellement (AJAX)
     */
    public function validateCaseType(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'case_type' => 'required|string',
        ]);

        $organization = Auth::user()->organization;

        // Vérifier que la config CommCare existe
        if (!$organization->commcare_api_key) {
            return response()->json([
                'success' => false,
                'message' => 'Configuration CommCare manquante.',
            ], 400);
        }

        try {
            // Décrypter l'API Key
            $email = $organization->commcare_email;
            $apiKey = Crypt::decryptString($organization->commcare_api_key);
            $projectSpace = $organization->commcare_project_space;

            // Valider le case type
            $exists = $this->commCareService->validateCaseType(
                $email,
                $apiKey,
                $projectSpace,
                $validated['case_type']
            );

            return response()->json([
                'success' => true,
                'exists' => $exists,
                'message' => $exists 
                    ? "Le case type '{$validated['case_type']}' existe dans votre projet."
                    : "Le case type '{$validated['case_type']}' n'existe pas dans votre projet.",
            ]);

        } catch (\Exception $e) {
            Log::error('Error validating case type', [
                'organization_id' => $organization->id,
                'case_type' => $validated['case_type'],
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la validation : ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Récupère les propriétés d'un case type (AJAX)
     */
    public function fetchCaseProperties(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'case_type' => 'required|string',
        ]);

        $organization = Auth::user()->organization;

        // Vérifier que la config CommCare existe
        if (!$organization->commcare_api_key) {
            return response()->json([
                'success' => false,
                'message' => 'Configuration CommCare manquante. Veuillez compléter l\'étape précédente.',
            ], 400);
        }

        try {
            // Décrypter l'API Key
            $email = $organization->commcare_email;
            $apiKey = Crypt::decryptString($organization->commcare_api_key);
            $projectSpace = $organization->commcare_project_space;

            // Extraire toutes les propriétés possibles en analysant plusieurs cases
            $properties = $this->commCareService->extractAllProperties(
                $email,
                $apiKey,
                $projectSpace,
                $validated['case_type']
            );

            if (empty($properties)) {
                return response()->json([
                    'success' => false,
                    'message' => "Aucun case trouvé pour le type '{$validated['case_type']}'. Vérifiez que ce type existe dans votre projet CommCare.",
                ], 404);
            }

            Log::info('All case properties fetched', [
                'organization_id' => $organization->id,
                'case_type' => $validated['case_type'],
                'properties_count' => count($properties),
            ]);

            return response()->json([
                'success' => true,
                'properties' => $properties,
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching case properties', [
                'organization_id' => $organization->id,
                'case_type' => $validated['case_type'],
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des propriétés : ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Sauvegarde le mapping des champs
     */
    public function storeMapping(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'case_type' => 'required|string',
            'selected_properties' => 'required|array|min:1',
            'selected_properties.*' => 'string',
            'phone_number_field' => 'required|string',
            'eligibility_field' => 'nullable|string',
            'eligibility_condition' => 'nullable|in:equals,not_equals,contains,not_contains',
            'eligibility_value' => 'nullable|string',
        ]);

        $organization = Auth::user()->organization;
        
        $organization->update([
            'commcare_case_type' => $validated['case_type'],
            'case_properties_mapping' => $validated['selected_properties'],
            'phone_number_field' => $validated['phone_number_field'],
            'eligibility_field' => $validated['eligibility_field'] ?? null,
            'eligibility_condition' => $validated['eligibility_condition'] ?? null,
            'eligibility_value' => $validated['eligibility_value'] ?? null,
            'onboarding_step' => 5,
        ]);

        Log::info('Onboarding Mapping completed', [
            'organization_id' => $organization->id,
            'case_type' => $validated['case_type'],
            'properties_count' => count($validated['selected_properties']),
            'phone_field' => $validated['phone_number_field'],
        ]);

        return redirect()->route('onboarding.completion');
    }

    // ============================================
    // ÉTAPE 5 : COMPLETION
    // ============================================
    
    /**
     * Page de complétion de l'onboarding
     */
    public function completion(): Response
    {
        $organization = Auth::user()->organization;

        // Marquer l'onboarding comme terminé
        $organization->update([
            'onboarding_completed' => true,
            'onboarding_completed_at' => now(),
        ]);

        Log::info('Onboarding completed', [
            'organization_id' => $organization->id,
            'organization_name' => $organization->name,
        ]);

        return Inertia::render('Onboarding/Completion', [
            'organization' => $organization,
        ]);
    }
}


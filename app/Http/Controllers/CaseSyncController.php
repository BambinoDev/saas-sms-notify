<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Jobs\SyncCommCareCasesJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\JsonResponse;

class CaseSyncController extends Controller
{
    public function index(): Response
    {
        $organization = Auth::user()->organization;

        if (!$organization->onboarding_completed) {
            return redirect()->route('onboarding.welcome');
        }

        if (!$organization->commcare_api_key) {
            return redirect()->route('dashboard')
                ->with('error', 'Configuration CommCare manquante. Veuillez compléter l\'onboarding.');
        }

        $syncStatus = Cache::get("sync_status_{$organization->id}", [
            'is_syncing' => false,
            'progress' => 0,
            'total' => 0,
            'current' => 0,
            'message' => null,
            'last_sync_at' => $organization->last_commcare_sync_at,
        ]);

        return Inertia::render('Cases/Sync', [
            'organization' => [
                'name' => $organization->name,
                'commcare_case_type' => $organization->commcare_case_type,
                'case_properties_count' => count($organization->case_properties_mapping ?? []),
                'phone_number_field' => $organization->phone_number_field,
                'last_sync_at' => $organization->last_commcare_sync_at,
            ],
            'syncStatus' => $syncStatus,
        ]);
    }

    public function sync(Request $request): JsonResponse
    {
        $organization = Auth::user()->organization;

        $isAlreadySyncing = Cache::get("sync_status_{$organization->id}.is_syncing", false);
        if ($isAlreadySyncing) {
            return response()->json([
                'success' => false,
                'message' => 'Une synchronisation est déjà en cours.',
            ], 409);
        }

        Cache::put("sync_status_{$organization->id}", [
            'is_syncing' => true,
            'progress' => 0,
            'total' => 0,
            'current' => 0,
            'message' => 'Démarrage de la synchronisation...',
            'started_at' => now()->toIso8601String(),
        ], 3600);

        SyncCommCareCasesJob::dispatchSync($organization->id);

        Log::info('CommCare sync started', [
            'organization_id' => $organization->id,
            'case_type' => $organization->commcare_case_type,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Synchronisation lancée avec succès.',
        ]);
    }

    public function status(Request $request): JsonResponse
    {
        $organization = Auth::user()->organization;

        $syncStatus = Cache::get("sync_status_{$organization->id}", [
            'is_syncing' => false,
            'progress' => 0,
            'total' => 0,
            'current' => 0,
            'message' => null,
        ]);

        return response()->json($syncStatus);
    }
}



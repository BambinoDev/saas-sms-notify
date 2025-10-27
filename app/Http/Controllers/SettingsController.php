<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class SettingsController extends Controller
{
    /**
     * Rediriger /settings vers /settings/general
     */
    public function index()
    {
        return redirect()->route('settings.general');
    }

    /**
     * Page Général
     */
    public function general(): Response
    {
        $organization = Auth::user()->organization;

        return Inertia::render('Settings/General', [
            'organization' => $organization,
        ]);
    }

    /**
     * Page Synchronisation
     */
    public function sync(): Response
    {
        $organization = Auth::user()->organization;

        // Stats réelles
        $stats = [
            'total_cases' => \App\Models\CaseModel::where('organization_id', $organization->id)->count(),
            'eligible_cases' => \App\Models\CaseModel::where('organization_id', $organization->id)
                ->whereNotNull('contact_phone_number')
                ->where('contact_phone_number', '!=', '')
                ->count(),
            'pending_sms' => \App\Models\SmsQueue::where('organization_id', $organization->id)
                ->where('status', 'pending')
                ->count(),
        ];

        // Dernière synchronisation
        $lastSync = $organization->last_commcare_sync_at 
            ? $organization->last_commcare_sync_at->format('d/m/Y H:i:s')
            : 'Jamais';

        return Inertia::render('Settings/Sync', [
            'organization' => $organization,
            'stats' => $stats,
            'lastSync' => $lastSync,
        ]);
    }

    /**
     * Page Configuration SMS
     */
    public function sms(): Response
    {
        $organization = Auth::user()->organization;

        // Récupérer la config SMS (depuis table organization ou autre)
        $smsConfig = [
            'environment' => $organization->sms_environment ?? 'sandbox',
            'username' => $organization->sms_username ?? '',
            'api_key' => $organization->sms_api_key ?? '',
            'sender_id' => $organization->sms_sender_id ?? 'S-REMIND',
        ];

        // Stats SMS
        $smsStats = [
            'sent_this_month' => \App\Models\SmsQueue::where('organization_id', $organization->id)
                ->whereIn('status', ['sent', 'delivered'])
                ->whereMonth('sent_at', now()->month)
                ->count(),
            'cost_this_month' => \App\Models\SmsQueue::where('organization_id', $organization->id)
                ->whereIn('status', ['sent', 'delivered'])
                ->whereMonth('sent_at', now()->month)
                ->sum('cost') ?? 0,
        ];

        // Solde (mock pour l'instant)
        $balance = 5240; // TODO: Récupérer depuis Africa's Talking

        return Inertia::render('Settings/Sms', [
            'organization' => $organization,
            'smsConfig' => $smsConfig,
            'balance' => $balance,
            'smsStats' => $smsStats,
        ]);
    }

    /**
     * Mettre à jour la config SMS
     */
    public function smsUpdate(Request $request)
    {
        $validated = $request->validate([
            'environment' => 'required|in:sandbox,production',
            'username' => 'required|string|max:255',
            'api_key' => 'required|string',
            'sender_id' => 'required|string|max:11',
        ]);

        $organization = Auth::user()->organization;
        $organization->update([
            'sms_environment' => $validated['environment'],
            'sms_username' => $validated['username'],
            'sms_api_key' => encrypt($validated['api_key']), // Encrypt for security
            'sms_sender_id' => $validated['sender_id'],
        ]);

        return redirect()->back()->with('success', 'Configuration SMS mise à jour');
    }

    /**
     * Tester la connexion Africa's Talking
     */
    public function smsTest(Request $request)
    {
        $validated = $request->validate([
            'environment' => 'required|in:sandbox,production',
            'username' => 'required|string',
            'api_key' => 'required|string',
        ]);

        try {
            // TODO: Implémenter test réel avec Africa's Talking SDK
            // Pour l'instant, mock response
            
            return response()->json([
                'success' => true,
                'message' => 'Connexion réussie !',
                'balance' => 'FCFA 5,240',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Page Mapping champs
     */
    public function mapping(): Response
    {
        $organization = Auth::user()->organization;

        return Inertia::render('Settings/Mapping', [
            'organization' => $organization,
            'currentMappings' => $organization->case_properties_mapping ?? [],
        ]);
    }

    /**
     * Mettre à jour le mapping
     */
    public function mappingUpdate(Request $request)
    {
        $validated = $request->validate([
            'mappings' => 'required|array',
            'mappings.*.commcare_field' => 'required|string',
            'mappings.*.system_variable' => 'required|string',
            'mappings.*.label' => 'required|string',
        ]);

        $organization = Auth::user()->organization;

        // Transformer en format key => value pour stockage
        $mapping = [];
        foreach ($validated['mappings'] as $item) {
            $mapping[$item['commcare_field']] = $item['label'];
        }

        $organization->update([
            'case_properties_mapping' => $mapping,
        ]);

        \Log::info('Mapping updated', [
            'organization_id' => $organization->id,
            'mappings_count' => count($mapping),
        ]);

        return redirect()->back()->with('success', 'Mappings enregistrés');
    }

    /**
     * Page Paramètres d'envoi
     */
    public function sending(): Response
    {
        $organization = Auth::user()->organization;

        // Préparer les settings pour la page
        $settings = [
            'timezone' => $organization->timezone,
            'default_send_time' => $organization->default_send_time,
            'send_window_start' => $organization->send_window_start,
            'send_window_end' => $organization->send_window_end,
            'default_language' => $organization->default_language,
        ];

        return Inertia::render('Settings/Sending', [
            'organization' => $organization,
            'settings' => $settings,
        ]);
    }

    /**
     * Mettre à jour les paramètres d'envoi
     */
    public function sendingUpdate(Request $request)
    {
        $validated = $request->validate([
            'timezone' => 'required|string|max:100',
            'default_send_time' => 'required|string|max:10',
            'send_window_start' => 'required|string|max:10',
            'send_window_end' => 'required|string|max:10',
            'default_language' => 'required|string|in:fr,en,es,ar',
        ]);

        $organization = Auth::user()->organization;
        
        $organization->update([
            'timezone' => $validated['timezone'],
            'default_send_time' => $validated['default_send_time'],
            'send_window_start' => $validated['send_window_start'],
            'send_window_end' => $validated['send_window_end'],
            'default_language' => $validated['default_language'],
        ]);

        \Log::info('Sending settings updated', [
            'organization_id' => $organization->id,
            'timezone' => $validated['timezone'],
            'default_send_time' => $validated['default_send_time'],
        ]);

        return redirect()->back()->with('success', 'Paramètres d\'envoi enregistrés avec succès');
    }
}

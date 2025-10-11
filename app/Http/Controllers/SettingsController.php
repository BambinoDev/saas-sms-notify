<?php
namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Services\CommCareService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = [
            'template_j_minus_2' => AppSetting::get('sms_template_j_minus_2'),
            'template_jour_j' => AppSetting::get('sms_template_jour_j'),
            'sending_time' => AppSetting::get('sms_sending_time'),
            'last_sync' => AppSetting::getLastSync(),
            'sync_status' => AppSetting::getSyncStatus(),
        ];

        // Statistiques pour l'affichage
        $stats = [
            'total_women' => \App\Models\Woman::count(),
            'eligible_women' => \App\Models\Woman::eligibleForSms()->count(),
            'pending_sms' => \App\Models\SmsQueue::pending()->count(),
        ];

        return Inertia::render('Settings/Index', [
            'settings' => $settings,
            'stats' => $stats,
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'template_j_minus_2' => 'required|string|max:320', // 2 SMS max
            'template_jour_j' => 'required|string|max:320',
            'sending_time' => 'required|date_format:H:i',
        ]);

        try {
            // Avertissement si > 160 caractères
            if (strlen($validated['template_j_minus_2']) > 160) {
                Log::warning('Template J-2 dépasse 160 caractères', [
                    'length' => strlen($validated['template_j_minus_2'])
                ]);
            }
            
            if (strlen($validated['template_jour_j']) > 160) {
                Log::warning('Template Jour-J dépasse 160 caractères', [
                    'length' => strlen($validated['template_jour_j'])
                ]);
            }

            AppSetting::set('sms_template_j_minus_2', $validated['template_j_minus_2']);
            AppSetting::set('sms_template_jour_j', $validated['template_jour_j']);
            AppSetting::set('sms_sending_time', $validated['sending_time']);

            Log::info('Paramètres mis à jour', $validated);

            return back()->with('success', 'Paramètres mis à jour avec succès');

        } catch (\Exception $e) {
            Log::error('Erreur mise à jour paramètres', [
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Erreur lors de la mise à jour');
        }
    }

    public function syncCommCare()
    {
        try {
            \App\Jobs\FetchCommCareDataJob::dispatch();

            Log::info('Sync manuelle lancée en arrière-plan');

            return back()->with('success', 'Synchronisation lancée en arrière-plan. Rafraîchissez la page dans quelques minutes.');

        } catch (\Exception $e) {
            Log::error('Erreur lancement sync', [
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Erreur lors du lancement de la synchronisation');
        }
    }
}
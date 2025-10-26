<?php

namespace App\Http\Controllers;

use App\Models\SmsQueue;
use App\Models\SmsRule;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class SmsController extends Controller
{
    public function index(Request $request)
    {
        // CRITIQUE : Récupérer l'organisation de l'utilisateur connecté
        $organization = auth()->user()->organization;
        
        // Paramètres de filtrage
        $search = $request->input('search', '');
        $status = $request->input('status', 'all');
        $dateFrom = $request->input('date_from', '');
        $dateTo = $request->input('date_to', '');
        $perPage = $request->input('per_page', 25);

        // Query de base avec relation case - FILTRÉ PAR ORGANISATION
        $query = SmsQueue::where('organization_id', $organization->id)->with('case');

        // Recherche (nom ou téléphone)
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('recipient_phone', 'like', "%{$search}%")
                  ->orWhereHas('case', function($caseQuery) use ($search) {
                      $caseQuery->where('case_name', 'ilike', "%{$search}%");
                  });
            });
        }

        // Filtres par statut
        if ($status !== 'all') {
            $query->where('status', $status);
        }

        // Filtres par date
        if ($dateFrom) {
            $query->whereDate('scheduled_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('scheduled_at', '<=', $dateTo);
        }

        // Stats FILTRÉES PAR ORGANISATION
        $stats = [
            'total' => SmsQueue::where('organization_id', $organization->id)->count(),
            'pending' => SmsQueue::where('organization_id', $organization->id)
                ->where('status', 'pending')->count(),
            'sent' => SmsQueue::where('organization_id', $organization->id)
                ->where('status', 'sent')->count(),
            'delivered' => SmsQueue::where('organization_id', $organization->id)
                ->where('status', 'delivered')->count(),
            'failed' => SmsQueue::where('organization_id', $organization->id)
                ->where('status', 'failed')->count(),
        ];

        // Pagination
        $smsQueue = $query->orderBy('scheduled_at', 'desc')
            ->paginate($perPage)
            ->withQueryString()
            ->through(function ($sms) {
                return [
                    'id' => $sms->id,
                    'recipient_name' => $sms->case?->case_name ?? 'N/A',
                    'phone_number' => $sms->recipient_phone,
                    'message' => $sms->message_content,
                    'template_name' => $this->extractTemplateName($sms->message_content),
                    'template_type' => $sms->sms_type ?? 'reminder',
                    'status' => $sms->status,
                    'scheduled_at' => $sms->scheduled_at,
                    'sent_at' => $sms->sent_at,
                    'delivered_at' => $sms->delivered_at,
                    'created_at' => $sms->created_at,
                    'cost' => $sms->cost,
                ];
            });

        return Inertia::render('Sms/Index', [
            'smsQueue' => $smsQueue,
            'stats' => $stats,
            'filters' => [
                'search' => $search,
                'status' => $status,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'per_page' => $perPage,
            ],
        ]);
    }

    /**
     * Extraire le nom du template depuis le message
     */
    private function extractTemplateName($message)
    {
        if (!$message) return 'Custom';
        
        // Si le message commence par "Bonjour", identifier le template
        if (str_starts_with($message, 'Bonjour Mme')) {
            if (str_contains($message, "n'oubliez pas votre RDV aujourd'hui")) {
                return 'Rappel Jour-J';
            } elseif (str_contains($message, 'après-demain')) {
                return 'Rappel J-2';
            }
        }
        return 'Custom';
    }

    /**
     * Get SMS stats (FILTRÉES PAR ORGANISATION)
     */
    private function getStats($organizationId)
    {
        return [
            'total' => SmsQueue::where('organization_id', $organizationId)->count(),
            'pending' => SmsQueue::where('organization_id', $organizationId)
                ->where('status', 'pending')->count(),
            'sent' => SmsQueue::where('organization_id', $organizationId)
                ->where('status', 'sent')->count(),
            'delivered' => SmsQueue::where('organization_id', $organizationId)
                ->where('status', 'delivered')->count(),
            'failed' => SmsQueue::where('organization_id', $organizationId)
                ->where('status', 'failed')->count(),
        ];
    }

    /**
     * Show single SMS details
     */
    public function show($id)
    {
        $organization = auth()->user()->organization;
        
        // CRITIQUE : Vérifier que le SMS appartient à l'organisation de l'utilisateur
        $sms = SmsQueue::where('organization_id', $organization->id)
            ->with('case')
            ->findOrFail($id);

        return Inertia::render('Sms/Show', [
            'sms' => [
                'id' => $sms->id,
                'recipient_name' => $sms->case?->case_name ?? 'N/A',
                'phone_number' => $sms->recipient_phone,
                'message' => $sms->message_content,
                'template_name' => $this->extractTemplateName($sms->message_content),
                'status' => $sms->status,
                'scheduled_at' => $sms->scheduled_at,
                'sent_at' => $sms->sent_at,
                'delivered_at' => $sms->delivered_at,
                'error_message' => $sms->error_message,
                'cost' => $sms->cost,
                'created_at' => $sms->created_at,
                'updated_at' => $sms->updated_at,
                'case' => $sms->case ? [
                    'id' => $sms->case->id,
                    'case_name' => $sms->case->case_name,
                    'phone' => $sms->case->contact_phone_number,
                    'structure' => $sms->case->structure_sanitaire,
                ] : null,
            ],
        ]);
    }

    /**
     * Retry failed SMS
     */
    public function retry($id)
    {
        $organization = auth()->user()->organization;
        
        // CRITIQUE : Vérifier que le SMS appartient à l'organisation de l'utilisateur
        $sms = SmsQueue::where('organization_id', $organization->id)
            ->findOrFail($id);

        if ($sms->status !== 'failed') {
            return back()->with('error', 'Only failed SMS can be retried');
        }

        // Reset status to pending
        $sms->update([
            'status' => 'pending',
            'error_message' => null,
            'scheduled_at' => Carbon::now(),
        ]);

        return back()->with('success', 'SMS queued for retry');
    }

    /**
     * Retry ALL failed SMS (FILTRÉ PAR ORGANISATION)
     */
    public function retryAllFailed()
    {
        $organization = auth()->user()->organization;
        
        $count = SmsQueue::where('organization_id', $organization->id)
            ->where('status', 'failed')
            ->update([
                'status' => 'pending',
                'error_message' => null,
                'scheduled_at' => Carbon::now(),
            ]);

        return back()->with('success', "{$count} SMS remis en file d'attente");
    }

    /**
     * Bulk retry failed SMS (FILTRÉ PAR ORGANISATION)
     */
    public function bulkRetry(Request $request)
    {
        $organization = auth()->user()->organization;
        $ids = $request->input('ids', []);

        $count = SmsQueue::where('organization_id', $organization->id)
            ->whereIn('id', $ids)
            ->where('status', 'failed')
            ->update([
                'status' => 'pending',
                'error_message' => null,
                'scheduled_at' => Carbon::now(),
            ]);

        return redirect()->back()->with('success', "$count SMS queued for retry");
    }

    /**
     * Envoyer immédiatement un SMS pending
     */
    public function sendNow($id)
    {
        $organization = auth()->user()->organization;
        
        // CRITIQUE : Vérifier que le SMS appartient à l'organisation de l'utilisateur
        $sms = SmsQueue::where('organization_id', $organization->id)
            ->findOrFail($id);

        if ($sms->status !== 'pending') {
            return back()->with('error', 'Seuls les SMS pending peuvent être envoyés immédiatement');
        }

        // Mettre à jour scheduled_at pour envoi immédiat
        $sms->update([
            'scheduled_at' => Carbon::now(),
        ]);

        return back()->with('success', 'SMS programmé pour envoi immédiat');
    }

    /**
     * Delete SMS
     */
    public function destroy($id)
    {
        $organization = auth()->user()->organization;
        
        // CRITIQUE : Vérifier que le SMS appartient à l'organisation de l'utilisateur
        $sms = SmsQueue::where('organization_id', $organization->id)
            ->findOrFail($id);
        $sms->delete();

        return redirect()->back()->with('success', 'SMS deleted successfully');
    }

    /**
     * Export CSV (FILTRÉ PAR ORGANISATION)
     */
    public function export(Request $request)
    {
        $organization = auth()->user()->organization;
        
        $status = $request->input('status', 'all');
        $dateFrom = $request->input('date_from', '');
        $dateTo = $request->input('date_to', '');

        $query = SmsQueue::where('organization_id', $organization->id)->with('case');

        if ($status !== 'all') {
            $query->where('status', $status);
        }
        if ($dateFrom) {
            $query->whereDate('scheduled_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('scheduled_at', '<=', $dateTo);
        }

        $smsQueue = $query->orderBy('scheduled_at', 'desc')->get();

        $filename = 'sms_queue_export_' . now()->format('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($smsQueue) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($file, [
                'ID',
                'Destinataire',
                'Téléphone',
                'Message',
                'Statut',
                'Programmé pour',
                'Envoyé le',
                'Coût',
            ], ';');

            foreach ($smsQueue as $sms) {
                fputcsv($file, [
                    $sms->id,
                    $sms->case?->case_name ?? 'N/A',
                    $sms->recipient_phone,
                    $sms->message_content,
                    $sms->status,
                    $sms->scheduled_at,
                    $sms->sent_at,
                    $sms->cost,
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}

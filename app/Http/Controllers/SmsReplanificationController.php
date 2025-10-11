<?php

namespace App\Http\Controllers;

use App\Models\SmsQueue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Carbon\Carbon;

class SmsReplanificationController extends Controller
{
    /**
     * Afficher l'interface de replanification
     */
    public function index(Request $request)
    {
        $status = $request->input('status', 'pending');
        $type = $request->input('type', 'all');
        $search = $request->input('search', '');
        $dateFrom = $request->input('date_from', '');
        $dateTo = $request->input('date_to', '');

        $query = SmsQueue::with('woman:id,case_name,case_id');

        // Filtres
        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if ($type !== 'all') {
            $query->where('sms_type', $type);
        }

        if ($search) {
            $query->whereHas('woman', function($q) use ($search) {
                $q->where('case_name', 'ilike', "%{$search}%")
                  ->orWhere('case_id', 'ilike', "%{$search}%");
            });
        }

        if ($dateFrom) {
            $query->whereDate('scheduled_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('scheduled_at', '<=', $dateTo);
        }

        $sms = $query->orderBy('scheduled_at', 'desc')->paginate(50)->withQueryString();

        // Stats pour les filtres
        $stats = [
            'total' => SmsQueue::count(),
            'pending' => SmsQueue::where('status', 'pending')->count(),
            'failed' => SmsQueue::where('status', 'failed')->count(),
            'sent' => SmsQueue::where('status', 'sent')->count(),
        ];

        // Types SMS disponibles
        $smsTypes = SmsQueue::distinct()->pluck('sms_type')->filter()->values();

        return Inertia::render('Sms/Replanification', [
            'sms' => $sms,
            'stats' => $stats,
            'smsTypes' => $smsTypes,
            'filters' => [
                'status' => $status,
                'type' => $type,
                'search' => $search,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ],
        ]);
    }

    /**
     * Replanifier des SMS sélectionnés
     */
    public function replanify(Request $request)
    {
        $validated = $request->validate([
            'sms_ids' => 'required|array',
            'sms_ids.*' => 'required|integer|exists:sms_queue,id',
            'replanify_type' => 'required|in:offset,absolute_time',
            'offset_hours' => 'nullable|integer',
            'offset_days' => 'nullable|integer',
            'new_datetime' => 'nullable|date',
            'reason' => 'nullable|string|max:500',
        ]);

        try {
            $smsIds = $validated['sms_ids'];
            $reason = $validated['reason'] ?? 'Replanification manuelle';

            // Récupérer les SMS sélectionnés
            $smsToReplanify = SmsQueue::whereIn('id', $smsIds)
                ->whereIn('status', ['pending', 'failed']) // Seulement pending et failed
                ->get();

            if ($smsToReplanify->isEmpty()) {
                return back()->with('error', 'Aucun SMS éligible pour la replanification');
            }

            $replanifiedCount = 0;
            $newScheduleTime = null;

            foreach ($smsToReplanify as $sms) {
                // Calculer la nouvelle heure selon le type
                if ($validated['replanify_type'] === 'offset') {
                    $newScheduleTime = $sms->scheduled_at;
                    
                    if (isset($validated['offset_hours'])) {
                        $newScheduleTime = $newScheduleTime->addHours($validated['offset_hours']);
                    }
                    
                    if (isset($validated['offset_days'])) {
                        $newScheduleTime = $newScheduleTime->addDays($validated['offset_days']);
                    }
                } else {
                    $newScheduleTime = Carbon::parse($validated['new_datetime']);
                }

                // Vérifier que la nouvelle heure est dans le futur
                if ($newScheduleTime->isFuture()) {
                    $sms->update([
                        'scheduled_at' => $newScheduleTime,
                        'status' => 'pending', // Remettre en pending
                        'retry_count' => 0, // Reset retry count
                        'error_message' => null, // Clear error
                    ]);

                    $replanifiedCount++;
                }
            }

            Log::info('Replanification SMS', [
                'count' => $replanifiedCount,
                'total_selected' => count($smsIds),
                'reason' => $reason,
                'user' => auth()->user()->name ?? 'System',
            ]);

            return back()->with('success', "{$replanifiedCount} SMS replanifié(s) avec succès");

        } catch (\Exception $e) {
            Log::error('Erreur replanification SMS', [
                'error' => $e->getMessage(),
                'sms_ids' => $validated['sms_ids'] ?? [],
            ]);

            return back()->with('error', 'Erreur lors de la replanification');
        }
    }

    /**
     * Replanifier par template (ex: tous les jours à 8h)
     */
    public function replanifyByTemplate(Request $request)
    {
        $validated = $request->validate([
            'sms_ids' => 'required|array',
            'sms_ids.*' => 'required|integer|exists:sms_queue,id',
            'template_type' => 'required|in:daily_time,weekly_pattern',
            'daily_time' => 'nullable|date_format:H:i',
            'weekly_pattern' => 'nullable|array',
            'reason' => 'nullable|string|max:500',
        ]);

        try {
            $smsIds = $validated['sms_ids'];
            $reason = $validated['reason'] ?? 'Replanification par template';

            $smsToReplanify = SmsQueue::whereIn('id', $smsIds)
                ->whereIn('status', ['pending', 'failed'])
                ->get();

            $replanifiedCount = 0;

            foreach ($smsToReplanify as $sms) {
                $newScheduleTime = null;

                if ($validated['template_type'] === 'daily_time' && $validated['daily_time']) {
                    // Appliquer l'heure quotidienne
                    $newScheduleTime = $sms->scheduled_at->setTimeFromTimeString($validated['daily_time']);
                }

                if ($newScheduleTime && $newScheduleTime->isFuture()) {
                    $sms->update([
                        'scheduled_at' => $newScheduleTime,
                        'status' => 'pending',
                        'retry_count' => 0,
                        'error_message' => null,
                    ]);

                    $replanifiedCount++;
                }
            }

            Log::info('Replanification SMS par template', [
                'count' => $replanifiedCount,
                'template_type' => $validated['template_type'],
                'reason' => $reason,
            ]);

            return back()->with('success', "{$replanifiedCount} SMS replanifié(s) avec le template");

        } catch (\Exception $e) {
            Log::error('Erreur replanification template', [
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Erreur lors de la replanification par template');
        }
    }

    /**
     * Obtenir les statistiques de replanification
     */
    public function stats()
    {
        $stats = [
            'pending_sms' => SmsQueue::where('status', 'pending')->count(),
            'failed_sms' => SmsQueue::where('status', 'failed')->count(),
            'sms_by_type' => SmsQueue::selectRaw('sms_type, COUNT(*) as count')
                ->whereIn('status', ['pending', 'failed'])
                ->groupBy('sms_type')
                ->get(),
            'sms_by_date' => SmsQueue::selectRaw('DATE(scheduled_at) as date, COUNT(*) as count')
                ->whereIn('status', ['pending', 'failed'])
                ->where('scheduled_at', '>=', now()->subDays(7))
                ->groupBy('date')
                ->orderBy('date')
                ->get(),
        ];

        return response()->json($stats);
    }
}


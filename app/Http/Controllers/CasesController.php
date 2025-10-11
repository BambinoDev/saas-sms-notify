<?php

namespace App\Http\Controllers;

use App\Models\CaseModel;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CasesController extends Controller
{
    public function index(Request $request)
    {
        // Paramètres de filtrage/recherche
        $search = $request->input('search', '');
        $status = $request->input('status', 'all'); // all, active, eligible, ineligible
        $perPage = $request->input('per_page', 25);

        // Query de base
        $query = CaseModel::query();

        // Recherche (nom ou téléphone)
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('case_name', 'ilike', "%{$search}%")
                  ->orWhere('contact_phone_number', 'like', "%{$search}%")
                  ->orWhere('case_id', 'like', "%{$search}%");
            });
        }

        // Filtres par statut
        switch ($status) {
            case 'active':
                $query->where('closed', false);
                break;
            case 'eligible':
                $query->whereNotNull('contact_phone_number')
                      ->where('contact_phone_number', '!=', '');
                break;
            case 'ineligible':
                $query->where(function($q) {
                    $q->whereNull('contact_phone_number')
                      ->orWhere('contact_phone_number', '');
                });
                break;
        }

        // Tri par défaut
        $query->orderBy('updated_at', 'desc');

        // Pagination avec préservation des paramètres
        $cases = $query->paginate($perPage)
            ->withQueryString()
            ->through(function ($case) {
                return [
                    'id' => $case->id,
                    'case_id' => $case->case_id,
                    'case_name' => $case->case_name,
                    'phone' => $case->contact_phone_number,
                    'owner_name' => $case->owner_name,
                    'structure' => $case->structure_sanitaire,
                    'district' => $case->district_sanitaire,
                    'next_visit' => $case->next_visit_date?->format('Y-m-d'),
                    'status' => $case->closed ? 'closed' : 'active',
                    'last_sync' => $case->updated_at->diffForHumans(),
                ];
            });

        // Stats GLOBALES (indépendantes de la pagination)
        $stats = [
            'total' => CaseModel::count(),
            'active' => CaseModel::where('closed', false)->count(),
            'eligible' => CaseModel::whereNotNull('contact_phone_number')
                ->where('contact_phone_number', '!=', '')
                ->count(),
            'with_phone' => CaseModel::whereNotNull('contact_phone_number')
                ->where('contact_phone_number', '!=', '')
                ->count(),
        ];

        return Inertia::render('Cases/Index', [
            'cases' => $cases,
            'stats' => $stats,
            'filters' => [
                'search' => $search,
                'status' => $status,
                'per_page' => $perPage,
            ],
        ]);
    }

    /**
     * Show single case details
     */
    public function show($id)
    {
        $case = CaseModel::with('smsQueue')->findOrFail($id);

        return Inertia::render('Cases/Show', [
            'case' => [
                'id' => $case->id,
                'case_id' => $case->case_id,
                'case_name' => $case->case_name,
                'owner_name' => $case->owner_name,
                'contact_phone_number' => $case->contact_phone_number,
                'date_of_birth' => $case->date_of_birth,
                'anc_number' => $case->anc_number,
                'edd' => $case->edd,
                'next_visit_date' => $case->next_visit_date,
                'structure_sanitaire' => $case->structure_sanitaire,
                'district_sanitaire' => $case->district_sanitaire,
                'sous_prefecture' => $case->sous_prefecture,
                'commune' => $case->commune,
                'closed' => $case->closed,
                'date_closed' => $case->date_closed,
                'created_at' => $case->created_at,
                'updated_at' => $case->updated_at,
                'sms_history' => $case->smsQueue->map(function($sms) {
                    return [
                        'id' => $sms->id,
                        'message' => $sms->message,
                        'status' => $sms->status,
                        'scheduled_at' => $sms->scheduled_at,
                        'sent_at' => $sms->sent_at,
                        'cost' => $sms->cost,
                    ];
                }),
            ],
        ]);
    }

    /**
     * Export cases to CSV
     */
    public function export(Request $request)
    {
        $search = $request->input('search', '');
        $status = $request->input('status', 'all');

        // Query de base
        $query = CaseModel::query();

        // Recherche
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('case_name', 'ilike', "%{$search}%")
                  ->orWhere('contact_phone_number', 'like', "%{$search}%")
                  ->orWhere('case_id', 'like', "%{$search}%");
            });
        }

        // Filtres par statut
        switch ($status) {
            case 'active':
                $query->where('closed', false);
                break;
            case 'eligible':
                $query->whereNotNull('contact_phone_number')
                      ->where('contact_phone_number', '!=', '');
                break;
            case 'ineligible':
                $query->where(function($q) {
                    $q->whereNull('contact_phone_number')
                      ->orWhere('contact_phone_number', '');
                });
                break;
        }

        $cases = $query->get();

        // Créer le CSV
        $filename = 'cases_export_' . now()->format('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function() use ($cases) {
            $file = fopen('php://output', 'w');
            
            // BOM UTF-8 pour Excel
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Header
            fputcsv($file, [
                'ID',
                'Case ID',
                'Nom',
                'Téléphone',
                'Propriétaire',
                'Structure',
                'District',
                'Statut',
                'Date de création',
                'Dernière mise à jour',
            ], ';');

            // Data
            foreach ($cases as $case) {
                fputcsv($file, [
                    $case->id,
                    $case->case_id,
                    $case->case_name,
                    $case->contact_phone_number,
                    $case->owner_name,
                    $case->structure_sanitaire,
                    $case->district_sanitaire,
                    $case->closed ? 'Fermé' : 'Actif',
                    $case->created_at->format('Y-m-d H:i:s'),
                    $case->updated_at->format('Y-m-d H:i:s'),
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}

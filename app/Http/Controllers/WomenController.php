<?php

namespace App\Http\Controllers;

use App\Models\Woman;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WomenController extends Controller
{
    public function index(Request $request)
    {
        $eligibility = $request->input('eligibility', 'all');
        $region = $request->input('region', 'all');
        $district = $request->input('district', 'all');
        $structure = $request->input('structure', 'all');
        $rdv = $request->input('rdv', 'all');
        $search = $request->input('search', '');
        $perPage = $request->input('per_page', 50);

        // Valider perPage
        if (!in_array($perPage, [25, 50, 100])) {
            $perPage = 50;
        }

        $query = Woman::query();

        // Filtre éligibilité
        if ($eligibility === 'eligible') {
            $query->eligibleForSms();
        } elseif ($eligibility === 'not_eligible') {
            $query->where(function($q) {
                $q->where('consent_sms_yes', false)
                  ->orWhere('contact_phone_number_is_verified', false)
                  ->orWhere(function($subq) {
                      $subq->whereNull('contact_phone_number')
                           ->whereNull('husband_phone_number');
                  })
                  ->orWhere('closed', true);
            });
        }

        // Filtre région
        if ($region !== 'all') {
            $query->where('region_sanitaire', $region);
        }

        // Filtre district
        if ($district !== 'all') {
            $query->where('district_sanitaire', $district);
        }

        // Filtre structure
        if ($structure !== 'all') {
            $query->where('structure_sanitaire', $structure);
        }

        // Filtre RDV
        if ($rdv === 'today') {
            $query->hasAppointmentOn(now());
        } elseif ($rdv === 'this_week') {
            $query->whereBetween('next_visit_date', [
                now()->startOfWeek(),
                now()->endOfWeek()
            ]);
        } elseif ($rdv === 'this_month') {
            $query->whereYear('next_visit_date', now()->year)
                  ->whereMonth('next_visit_date', now()->month);
        }

        // Recherche
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('case_name', 'ilike', "%{$search}%")
                  ->orWhere('case_id', 'ilike', "%{$search}%")
                  ->orWhere('contact_phone_number', 'like', "%{$search}%");
            });
        }

        $women = $query->orderBy('next_visit_date', 'asc')
                      ->paginate($perPage)
                      ->withQueryString();

        // Stats
        $stats = [
            'total' => Woman::count(),
            'eligible' => Woman::eligibleForSms()->count(),
            'not_eligible' => Woman::count() - Woman::eligibleForSms()->count(),
            'rdv_today' => Woman::hasAppointmentOn(now())->count(),
            'rdv_this_week' => Woman::whereBetween('next_visit_date', [
                now()->startOfWeek(),
                now()->endOfWeek()
            ])->count(),
        ];

        // Listes pour filtres
        $regions = Woman::select('region_sanitaire')
            ->whereNotNull('region_sanitaire')
            ->distinct()
            ->orderBy('region_sanitaire')
            ->pluck('region_sanitaire');

        $districts = Woman::select('district_sanitaire')
            ->whereNotNull('district_sanitaire')
            ->distinct()
            ->orderBy('district_sanitaire')
            ->pluck('district_sanitaire');

        $structures = Woman::select('structure_sanitaire')
            ->whereNotNull('structure_sanitaire')
            ->distinct()
            ->orderBy('structure_sanitaire')
            ->pluck('structure_sanitaire');

        return Inertia::render('Women/Index', [
            'women' => $women,
            'stats' => $stats,
            'regions' => $regions,
            'districts' => $districts,
            'structures' => $structures,
            'filters' => [
                'eligibility' => $eligibility,
                'region' => $region,
                'district' => $district,
                'structure' => $structure,
                'rdv' => $rdv,
                'search' => $search,
                'per_page' => $perPage,
            ],
        ]);
    }
}
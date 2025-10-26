<?php

namespace App\Http\Controllers;

use App\Models\CaseModel;
use App\Models\SmsQueue;
use App\Models\SmsRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('login');
        }
        
        $organization = $user->organization;

        // Rediriger vers l'onboarding si non complété
        if (!$organization->onboarding_completed) {
            return redirect()->route('onboarding.welcome');
        }

        // Stats filtrées par organization
        $stats = $this->getOrganizationStats($organization);

        // Déterminer l'état du dashboard
        $dashboardState = $this->getDashboardState($organization, $stats);

        // Données détaillées uniquement si actif
        $detailedData = $dashboardState === 'active'
            ? $this->getDetailedData($organization)
            : null;

        return Inertia::render('Dashboard/Index', [
            'organization' => [
                'name' => $organization->name,
                'commcare_case_type' => $organization->commcare_case_type,
                'case_properties_count' => count($organization->case_properties_mapping ?? []),
                'phone_number_field' => $organization->phone_number_field,
                'commcare_project_name' => $organization->commcare_project_name,
            ],
            'dashboardState' => $dashboardState,
            'stats' => $stats,
            'smsActivity' => $detailedData['smsActivity'] ?? [],
            'recentCases' => $detailedData['recentCases'] ?? [],
            'upcomingSms' => $detailedData['upcomingSms'] ?? [],
            'recentActivity' => $detailedData['recentActivity'] ?? [],
            'nextSteps' => $this->getNextSteps($organization, $stats),
        ]);
    }

    private function getOrganizationStats($organization): array
    {
        $totalCases = CaseModel::forOrganization($organization->id)->count();
        $casesLastMonth = CaseModel::forOrganization($organization->id)
            ->whereMonth('created_at', now()->subMonth())
            ->count();
        $casesGrowth = $casesLastMonth > 0
            ? round((($totalCases - $casesLastMonth) / $casesLastMonth) * 100, 1)
            : 0;

        $activeSms = SmsQueue::where('organization_id', $organization->id)
            ->whereIn('status', ['pending', 'sending'])
            ->count();

        $queuedSms = SmsQueue::where('organization_id', $organization->id)
            ->where('status', 'pending')
            ->where('scheduled_at', '<=', now()->addDay())
            ->count();

        $sentLast7Days = SmsQueue::where('organization_id', $organization->id)
            ->where('status', 'sent')
            ->where('sent_at', '>=', now()->subDays(7))
            ->count();

        $failedLast7Days = SmsQueue::where('organization_id', $organization->id)
            ->where('status', 'failed')
            ->where('created_at', '>=', now()->subDays(7))
            ->count();

        $totalLast7Days = $sentLast7Days + $failedLast7Days;
        $successRate = $totalLast7Days > 0
            ? round(($sentLast7Days / $totalLast7Days) * 100, 1)
            : 0;

        $rulesCount = SmsRule::where('organization_id', $organization->id)
            ->where('is_active', true)
            ->count();

        return [
            'totalCases' => [
                'value' => $totalCases,
                'label' => 'Total Cases',
                'growth' => $casesGrowth,
            ],
            'activeSms' => [
                'value' => $activeSms,
                'label' => 'SMS Actifs',
                'sublabel' => 'En cours de traitement',
            ],
            'queuedSms' => [
                'value' => $queuedSms,
                'label' => 'File d\'attente',
                'sublabel' => 'Programmés pour aujourd\'hui',
            ],
            'successRate' => [
                'value' => $successRate . '%',
                'label' => 'Taux de succès',
                'sublabel' => $successRate >= 90 ? 'Excellent' : ($successRate >= 70 ? 'Bon' : 'À améliorer'),
            ],
            'rulesCount' => $rulesCount,
            'casesCount' => $totalCases,
            'smsTotal' => $activeSms + $queuedSms,
        ];
    }

    private function getDashboardState($organization, array $stats): string
    {
        if ($stats['casesCount'] === 0 && $stats['rulesCount'] === 0) {
            return 'empty';
        }
        return 'active';
    }

    private function getDetailedData($organization): array
    {
        $smsActivity = [];
        for ($i = 6; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $sent = SmsQueue::where('organization_id', $organization->id)
                ->where('status', 'sent')
                ->whereYear('sent_at', $month->year)
                ->whereMonth('sent_at', $month->month)
                ->count();

            $delivered = SmsQueue::where('organization_id', $organization->id)
                ->where('status', 'delivered')
                ->whereYear('delivered_at', $month->year)
                ->whereMonth('delivered_at', $month->month)
                ->count();

            $smsActivity[] = [
                'month' => $month->format('M'),
                'sent' => $sent,
                'delivered' => $delivered,
            ];
        }

        $recentCases = CaseModel::forOrganization($organization->id)
            ->latest('updated_at')
            ->take(5)
            ->get()
            ->map(function ($case) {
                return [
                    'id' => $case->id,
                    'name' => $case->case_name,
                    'phone' => $case->contact_phone_number,
                    'type' => 'Patient',
                    'status' => $case->status,
                ];
            });

        $upcomingSms = SmsQueue::where('organization_id', $organization->id)
            ->where('status', 'pending')
            ->where('scheduled_at', '>=', now())
            ->where('scheduled_at', '<=', now()->addDay())
            ->orderBy('scheduled_at', 'asc')
            ->take(5)
            ->get()
            ->map(function ($sms) {
                return [
                    'id' => $sms->id,
                    'recipient' => $sms->case->case_name ?? 'Unknown',
                    'phone' => $sms->phone_number,
                    'template' => $sms->rule_type,
                    'scheduled' => Carbon::parse($sms->scheduled_at)->format('Y-m-d H:i'),
                ];
            });

        $lastSync = CaseModel::forOrganization($organization->id)->max('updated_at');
        $recentActivity = [
            [
                'title' => 'CommCare sync completed',
                'time' => $lastSync ? Carbon::parse($lastSync)->diffForHumans() : 'Never',
            ],
        ];

        return [
            'smsActivity' => $smsActivity,
            'recentCases' => $recentCases,
            'upcomingSms' => $upcomingSms,
            'recentActivity' => $recentActivity,
        ];
    }

    private function getNextSteps($organization, array $stats): array
    {
        $steps = [];

        if ($stats['casesCount'] === 0) {
            $steps[] = [
                'priority' => 1,
                'title' => 'Synchroniser vos données CommCare',
                'description' => "Importez vos cases de type '{$organization->commcare_case_type}' depuis CommCare",
                'action' => 'Lancer la synchronisation',
                'route' => 'cases.sync', // ✅ Corrigé
                'icon' => 'sync',
            ];
        }

        if ($stats['rulesCount'] === 0) {
            $steps[] = [
                'priority' => 2,
                'title' => 'Configurer vos règles d\'envoi',
                'description' => 'Définissez quand et comment envoyer vos SMS automatiquement',
                'action' => 'Créer une règle',
                'route' => 'rules.create', // ✅ Corrigé
                'icon' => 'rules',
            ];
        }

        if ($stats['casesCount'] > 0 && $stats['rulesCount'] > 0 && $stats['smsTotal'] === 0) {
            $steps[] = [
                'priority' => 3,
                'title' => 'Générer vos premiers SMS',
                'description' => 'Lancez la génération de SMS selon vos règles configurées',
                'action' => 'Générer les SMS',
                'route' => 'rules.index', // ✅ Route temporaire vers la liste des règles
                'icon' => 'generate',
            ];
        }

        return $steps;
    }
}
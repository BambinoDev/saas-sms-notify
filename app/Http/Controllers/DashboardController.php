<?php

namespace App\Http\Controllers;

use App\Models\CaseModel;
use App\Models\SmsQueue;
use App\Models\SmsRule;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // Total cases
        $totalCases = CaseModel::count();
        $casesLastMonth = CaseModel::whereMonth('created_at', now()->subMonth())->count();
        $casesGrowth = $casesLastMonth > 0 
            ? round((($totalCases - $casesLastMonth) / $casesLastMonth) * 100, 1)
            : 0;

        // SMS Stats
        $activeSms = SmsQueue::whereIn('status', ['pending', 'sending'])->count();
        $queuedSms = SmsQueue::where('status', 'pending')
            ->where('scheduled_at', '<=', now()->addDay())
            ->count();
        
        // Success Rate (last 7 days)
        $sentLast7Days = SmsQueue::where('status', 'sent')
            ->where('sent_at', '>=', now()->subDays(7))
            ->count();
        $failedLast7Days = SmsQueue::where('status', 'failed')
            ->where('created_at', '>=', now()->subDays(7))
            ->count();
        $totalLast7Days = $sentLast7Days + $failedLast7Days;
        $successRate = $totalLast7Days > 0 
            ? round(($sentLast7Days / $totalLast7Days) * 100, 1)
            : 0;

        // SMS Activity (last 7 months)
        $smsActivity = [];
        for ($i = 6; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $sent = SmsQueue::where('status', 'sent')
                ->whereYear('sent_at', $month->year)
                ->whereMonth('sent_at', $month->month)
                ->count();
            $delivered = SmsQueue::where('status', 'delivered')
                ->whereYear('delivered_at', $month->year)
                ->whereMonth('delivered_at', $month->month)
                ->count();
            
            $smsActivity[] = [
                'month' => $month->format('M'),
                'sent' => $sent,
                'delivered' => $delivered,
            ];
        }

        // Recent Cases (last 5)
        $recentCases = CaseModel::with(['smsQueue' => function($query) {
                $query->latest()->first();
            }])
            ->latest('updated_at')
            ->take(5)
            ->get()
            ->map(function($case) {
                return [
                    'id' => $case->id,
                    'name' => $case->case_name,
                    'phone' => $case->contact_phone_number,
                    'type' => 'Patient',
                    'status' => $case->case_status ?? 'active',
                ];
            });

        // Upcoming SMS (next 24h)
        $upcomingSms = SmsQueue::with('case')
            ->where('status', 'pending')
            ->where('scheduled_at', '>=', now())
            ->where('scheduled_at', '<=', now()->addDay())
            ->orderBy('scheduled_at', 'asc')
            ->take(5)
            ->get()
            ->map(function($sms) {
                return [
                    'id' => $sms->id,
                    'recipient' => $sms->case->case_name ?? 'Unknown',
                    'phone' => $sms->phone_number,
                    'template' => $sms->rule_type,
                    'scheduled' => $sms->scheduled_at->format('Y-m-d H:i'),
                ];
            });

        // Recent Activity
        $recentActivity = [
            [
                'title' => 'CommCare sync completed',
                'time' => CaseModel::max('updated_at') 
                    ? Carbon::parse(CaseModel::max('updated_at'))->diffForHumans()
                    : 'Never',
            ],
            [
                'title' => 'New rule activated: reminder',
                'time' => SmsRule::where('active', true)->max('updated_at')
                    ? Carbon::parse(SmsRule::where('active', true)->max('updated_at'))->diffForHumans()
                    : 'Never',
            ],
            [
                'title' => $failedLast7Days . ' SMS failed to send',
                'time' => 'Today',
            ],
        ];

        return Inertia::render('Dashboard/Index', [
            'stats' => [
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
                    'sublabel' => 'Excellent',
                ],
            ],
            'smsActivity' => $smsActivity,
            'recentCases' => $recentCases,
            'upcomingSms' => $upcomingSms,
            'recentActivity' => $recentActivity,
        ]);
    }
}
<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\User;
use App\Models\Subscription;
use App\Models\CaseModel;
use App\Models\SmsQueue;
use Inertia\Inertia;
use Inertia\Response;

class AdminDashboardController extends Controller
{
    /**
     * Dashboard admin avec stats globales
     */
    public function index(): Response
    {
        $stats = [
            // Organizations
            'total_organizations' => Organization::count(),
            'active_organizations' => Organization::where('status', 'active')->count(),
            'trial_organizations' => Organization::where('status', 'trial')->count(),
            'suspended_organizations' => Organization::where('status', 'suspended')->count(),
            
            // Users
            'total_users' => User::count(),
            'superadmins' => User::where('is_superadmin', true)->count(),
            
            // Subscriptions
            'total_subscriptions' => Subscription::count(),
            'active_subscriptions' => Subscription::where('status', 'active')->count(),
            
            // Global Stats
            'total_cases' => CaseModel::count(),
            'total_sms' => SmsQueue::count(),
            'sms_sent' => SmsQueue::where('status', 'sent')->count(),
            'sms_pending' => SmsQueue::where('status', 'pending')->count(),
            'sms_failed' => SmsQueue::where('status', 'failed')->count(),
            
            // Revenue (si billing activé)
            'monthly_revenue' => Subscription::where('status', 'active')->sum('price'),
        ];
        
        // Dernières organisations créées
        $recent_organizations = Organization::with('subscription')
            ->latest()
            ->take(5)
            ->get();
        
        // Organisations par statut
        $organizations_by_status = Organization::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');
        
        return Inertia::render('Admin/Dashboard', [
            'stats' => $stats,
            'recent_organizations' => $recent_organizations,
            'organizations_by_status' => $organizations_by_status,
        ]);
    }
}


<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class AdminOrganizationsController extends Controller
{
    /**
     * Liste TOUTES les organisations (SUPERADMIN)
     */
    public function index(Request $request): Response
    {
        $query = Organization::with(['subscription', 'users'])
            ->withCount(['cases', 'smsQueue', 'rules']);
        
        // Filtres
        if ($request->status) {
            $query->where('status', $request->status);
        }
        
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('slug', 'like', "%{$request->search}%");
            });
        }
        
        $organizations = $query->latest()->paginate(15);
        
        return Inertia::render('Admin/Organizations/Index', [
            'organizations' => $organizations,
            'filters' => $request->only(['status', 'search']),
        ]);
    }
    
    /**
     * Afficher formulaire création
     */
    public function create(): Response
    {
        return Inertia::render('Admin/Organizations/Create');
    }
    
    /**
     * Créer nouvelle organisation
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'primary_color' => 'required|string|regex:/^#[0-9A-F]{6}$/i',
            'secondary_color' => 'required|string|regex:/^#[0-9A-F]{6}$/i',
            'logo_url' => 'nullable|url',
            'plan' => 'required|in:starter,pro,enterprise',
        ]);
        
        // Générer slug unique
        $slug = Str::slug($validated['name']);
        $counter = 1;
        while (Organization::where('slug', $slug)->exists()) {
            $slug = Str::slug($validated['name']) . '-' . $counter;
            $counter++;
        }
        
        $organization = Organization::create([
            'name' => $validated['name'],
            'slug' => $slug,
            'primary_color' => $validated['primary_color'],
            'secondary_color' => $validated['secondary_color'],
            'logo_url' => $validated['logo_url'] ?? null,
            'status' => 'trial',
            'trial_ends_at' => now()->addDays(14),
        ]);
        
        // Créer subscription selon le plan
        $plans = [
            'starter' => ['sms_limit' => 1000, 'users_limit' => 1, 'structures_limit' => 1, 'price' => 49.00],
            'pro' => ['sms_limit' => 10000, 'users_limit' => 5, 'structures_limit' => 10, 'price' => 149.00],
            'enterprise' => ['sms_limit' => 999999, 'users_limit' => 999, 'structures_limit' => 999, 'price' => 0.00],
        ];
        
        $planConfig = $plans[$validated['plan']];
        
        $organization->subscriptions()->create([
            'plan' => $validated['plan'],
            'status' => 'trial',
            'sms_limit' => $planConfig['sms_limit'],
            'users_limit' => $planConfig['users_limit'],
            'structures_limit' => $planConfig['structures_limit'],
            'price' => $planConfig['price'],
            'trial_ends_at' => now()->addDays(14),
            'current_period_start' => now(),
            'current_period_end' => now()->addMonth(),
        ]);
        
        return redirect()->route('admin.organizations.show', $organization)
            ->with('success', 'Organisation créée avec succès');
    }
    
    /**
     * Afficher détails (N'IMPORTE quelle org)
     */
    public function show(Organization $organization): Response
    {
        $organization->load([
            'subscription',
            'users' => function ($query) {
                $query->withPivot('role')->orderBy('organization_user.role');
            },
        ]);
        
        $organization->loadCount(['cases', 'smsQueue', 'rules']);
        
        // Stats détaillées
        $stats = [
            'total_cases' => $organization->cases()->count(),
            'active_cases' => $organization->cases()->where('closed', false)->count(),
            'total_sms' => $organization->smsQueue()->count(),
            'sms_sent' => $organization->smsQueue()->where('status', 'sent')->count(),
            'sms_pending' => $organization->smsQueue()->where('status', 'pending')->count(),
            'sms_failed' => $organization->smsQueue()->where('status', 'failed')->count(),
            'active_rules' => $organization->rules()->where('active', true)->count(),
        ];
        
        return Inertia::render('Admin/Organizations/Show', [
            'organization' => $organization,
            'stats' => $stats,
        ]);
    }
    
    /**
     * Afficher formulaire édition
     */
    public function edit(Organization $organization): Response
    {
        return Inertia::render('Admin/Organizations/Edit', [
            'organization' => $organization,
        ]);
    }
    
    /**
     * Mettre à jour
     */
    public function update(Request $request, Organization $organization): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'primary_color' => 'required|string|regex:/^#[0-9A-F]{6}$/i',
            'secondary_color' => 'required|string|regex:/^#[0-9A-F]{6}$/i',
            'logo_url' => 'nullable|url',
            'status' => 'required|in:active,trial,suspended,cancelled',
        ]);
        
        $organization->update($validated);
        
        return redirect()->route('admin.organizations.show', $organization)
            ->with('success', 'Organisation mise à jour');
    }
    
    /**
     * Supprimer (soft delete)
     */
    public function destroy(Organization $organization): RedirectResponse
    {
        // Empêcher suppression si données liées
        if ($organization->cases()->count() > 0) {
            return back()->with('error', 'Impossible de supprimer une organisation avec des cases');
        }
        
        $name = $organization->name;
        $organization->delete(); // Soft delete
        
        return redirect()->route('admin.organizations.index')
            ->with('success', "Organisation \"{$name}\" supprimée");
    }
    
    /**
     * Gérer les membres (N'IMPORTE quelle org)
     */
    public function members(Organization $organization): Response
    {
        $members = $organization->users()
            ->withPivot('role', 'created_at')
            ->orderBy('organization_user.role')
            ->get();
        
        return Inertia::render('Admin/Organizations/Members', [
            'organization' => $organization,
            'members' => $members,
        ]);
    }
    
    /**
     * Inviter membre
     */
    public function inviteMember(Request $request, Organization $organization): RedirectResponse
    {
        $validated = $request->validate([
            'email' => 'required|email|exists:users,email',
            'role' => 'required|in:owner,admin,manager,user',
        ]);
        
        $user = User::where('email', $validated['email'])->first();
        
        // Vérifier si déjà membre
        if ($organization->users()->where('users.id', $user->id)->exists()) {
            return back()->with('error', 'Cet utilisateur est déjà membre');
        }
        
        $organization->users()->attach($user->id, [
            'role' => $validated['role']
        ]);
        
        return back()->with('success', 'Membre ajouté avec succès');
    }
    
    /**
     * Changer rôle membre
     */
    public function updateMemberRole(Request $request, Organization $organization, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'role' => 'required|in:owner,admin,manager,user',
        ]);
        
        $organization->users()->updateExistingPivot($user->id, [
            'role' => $validated['role']
        ]);
        
        return back()->with('success', 'Rôle mis à jour');
    }
    
    /**
     * Retirer membre
     */
    public function removeMember(Organization $organization, User $user): RedirectResponse
    {
        $organization->users()->detach($user->id);
        
        return back()->with('success', 'Membre retiré');
    }
}


<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class OrganizationSettingsController extends Controller
{
    /**
     * Settings home (redirect to general)
     */
    public function index(): RedirectResponse
    {
        return redirect()->route('organization.settings.general');
    }
    
    /**
     * General settings (branding, etc)
     */
    public function general(Request $request): Response
    {
        $organization = $request->user()->firstOrganization();
        
        if (!$organization) {
            abort(404, 'Aucune organisation trouvée');
        }
        
        // Vérifier permissions
        if (!$request->user()->canManageOrganization($organization)) {
            abort(403, 'Accès non autorisé');
        }
        
        $organization->load('subscription');
        $organization->loadCount(['cases', 'smsQueue', 'rules']);
        
        // Stats
        $stats = [
            'total_cases' => $organization->cases()->count(),
            'active_cases' => $organization->cases()->where('closed', false)->count(),
            'total_sms' => $organization->smsQueue()->count(),
            'sms_sent' => $organization->smsQueue()->where('status', 'sent')->count(),
            'active_rules' => $organization->rules()->where('active', true)->count(),
        ];
        
        return Inertia::render('OrganizationSettings/General', [
            'organization' => $organization,
            'stats' => $stats,
        ]);
    }
    
    /**
     * Update general settings
     */
    public function updateGeneral(Request $request): RedirectResponse
    {
        $organization = $request->user()->firstOrganization();
        
        if (!$request->user()->canManageOrganization($organization)) {
            abort(403);
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'primary_color' => 'required|string|regex:/^#[0-9A-F]{6}$/i',
            'secondary_color' => 'required|string|regex:/^#[0-9A-F]{6}$/i',
            'logo_url' => 'nullable|url',
        ]);
        
        $organization->update($validated);
        
        return back()->with('success', 'Paramètres mis à jour');
    }
    
    /**
     * Members management
     */
    public function members(Request $request): Response
    {
        $organization = $request->user()->firstOrganization();
        
        if (!$request->user()->canManageOrganization($organization)) {
            abort(403);
        }
        
        $members = $organization->users()
            ->withPivot('role', 'created_at')
            ->orderBy('organization_user.role')
            ->get();
        
        return Inertia::render('OrganizationSettings/Members', [
            'organization' => $organization,
            'members' => $members,
        ]);
    }
    
    /**
     * Inviter un membre
     */
    public function inviteMember(Request $request): RedirectResponse
    {
        $organization = $request->user()->firstOrganization();
        
        if (!$request->user()->canManageOrganization($organization)) {
            abort(403);
        }
        
        $validated = $request->validate([
            'email' => 'required|email|exists:users,email',
            'role' => 'required|in:admin,manager,user',
        ]);
        
        $user = User::where('email', $validated['email'])->first();
        
        // Vérifier si déjà membre
        if ($organization->users()->where('users.id', $user->id)->exists()) {
            return back()->with('error', 'Cet utilisateur est déjà membre');
        }
        
        // Ajouter membre
        $organization->users()->attach($user->id, [
            'role' => $validated['role']
        ]);
        
        return back()->with('success', 'Membre ajouté avec succès');
    }
    
    /**
     * Retirer un membre
     */
    public function removeMember(Request $request, User $user): RedirectResponse
    {
        $organization = $request->user()->firstOrganization();
        
        if (!$request->user()->canManageOrganization($organization)) {
            abort(403);
        }
        
        // Empêcher de se retirer soi-même si owner
        if ($user->id === $request->user()->id && 
            $request->user()->isOwnerOfOrganization($organization)) {
            return back()->with('error', 'Le propriétaire ne peut pas se retirer');
        }
        
        $organization->users()->detach($user->id);
        
        return back()->with('success', 'Membre retiré');
    }
    
    /**
     * Billing & subscription
     */
    public function billing(Request $request): Response
    {
        $organization = $request->user()->firstOrganization();
        
        if (!$request->user()->canManageOrganization($organization)) {
            abort(403);
        }
        
        $organization->load('subscription');
        
        return Inertia::render('OrganizationSettings/Billing', [
            'organization' => $organization,
            'subscription' => $organization->subscription,
        ]);
    }
    
    /**
     * API settings
     */
    public function api(Request $request): Response
    {
        $organization = $request->user()->firstOrganization();
        
        return Inertia::render('OrganizationSettings/Api', [
            'organization' => $organization,
        ]);
    }
}


<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\SmsTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class SmsTemplateController extends Controller
{
    /**
     * Liste des templates
     */
    public function index(): Response
    {
        $organization = Auth::user()->organization;

        $templates = SmsTemplate::forOrganization($organization->id)
            ->withCount('rules')
            ->latest()
            ->get();

        return Inertia::render('Templates/Index', [
            'templates' => $templates,
        ]);
    }

    /**
     * Formulaire de création
     */
    public function create(): Response
    {
        $organization = Auth::user()->organization;

        return Inertia::render('Templates/Create', [
            'availableVariables' => $this->getAvailableVariables($organization),
        ]);
    }

    /**
     * Sauvegarde d'un nouveau template
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'content' => 'required|string|max:1000',
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ]);

        $organization = Auth::user()->organization;

        SmsTemplate::create([
            'organization_id' => $organization->id,
            'name' => $validated['name'],
            'content' => $validated['content'],
            'description' => $validated['description'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return redirect()->route('templates.index')
            ->with('success', 'Template créé avec succès !');
    }

    /**
     * Formulaire d'édition
     */
    public function edit(SmsTemplate $template): Response
    {
        // Vérifier que le template appartient à l'organisation
        $organization = Auth::user()->organization;
        
        if ($template->organization_id !== $organization->id) {
            abort(403);
        }

        return Inertia::render('Templates/Edit', [
            'template' => $template,
            'availableVariables' => $this->getAvailableVariables($organization),
        ]);
    }

    /**
     * Mise à jour d'un template
     */
    public function update(Request $request, SmsTemplate $template): RedirectResponse
    {
        // Vérifier que le template appartient à l'organisation
        $organization = Auth::user()->organization;
        
        if ($template->organization_id !== $organization->id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'content' => 'required|string|max:1000',
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ]);

        $template->update($validated);

        return redirect()->route('templates.index')
            ->with('success', 'Template mis à jour avec succès !');
    }

    /**
     * Suppression d'un template
     */
    public function destroy(SmsTemplate $template): RedirectResponse
    {
        // Vérifier que le template appartient à l'organisation
        $organization = Auth::user()->organization;
        
        if ($template->organization_id !== $organization->id) {
            abort(403);
        }

        // Vérifier qu'il n'est pas utilisé par des règles
        if ($template->rules()->count() > 0) {
            return redirect()->route('templates.index')
                ->with('error', 'Impossible de supprimer ce template car il est utilisé par des règles.');
        }

        $template->delete();

        return redirect()->route('templates.index')
            ->with('success', 'Template supprimé avec succès !');
    }

    /**
     * Récupère les variables disponibles selon la config de l'organisation
     */
    private function getAvailableVariables($organization): array
    {
        $variables = [
            'case_id' => 'ID du case',
            'case_name' => 'Nom du case',
            $organization->phone_number_field => 'Numéro de téléphone',
        ];

        // Ajouter les propriétés mappées
        foreach ($organization->case_properties_mapping ?? [] as $property) {
            if (!isset($variables[$property])) {
                $variables[$property] = ucfirst(str_replace('_', ' ', $property));
            }
        }

        return $variables;
    }
}

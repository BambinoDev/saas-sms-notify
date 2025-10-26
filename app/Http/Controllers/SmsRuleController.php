<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\SmsRule;
use App\Models\SmsTemplate;
use App\Services\SmsGenerationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;

class SmsRuleController extends Controller
{
    /**
     * Liste des règles (améliorer pour inclure stats)
     */
    public function index(): Response
    {
        $organization = Auth::user()->organization;

        $rules = SmsRule::where('organization_id', $organization->id)
            ->with('template')
            ->orderBy('priority', 'asc')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($rule) {
                return [
                    'id' => $rule->id,
                    'name' => $rule->name,
                    'description' => $rule->description,
                    'is_active' => $rule->is_active,
                    'is_paused' => $rule->is_paused,
                    'paused_at' => $rule->paused_at,
                    'pause_reason' => $rule->pause_reason,
                    'template_name' => $rule->template->name ?? 'N/A',
                    'trigger_field' => $rule->trigger_field,
                    'trigger_condition' => $rule->trigger_condition,
                    'trigger_value' => $rule->trigger_value,
                    'trigger_unit' => $rule->trigger_unit,
                    'generation_frequency' => $rule->generation_frequency,
                    'generation_time' => $rule->generation_time,
                    'priority' => $rule->priority,
                    // STATS
                    'total_generated' => $rule->total_generated,
                    'total_sent' => $rule->total_sent,
                    'total_delivered' => $rule->total_delivered,
                    'total_failed' => $rule->total_failed,
                    'last_generated_at' => $rule->last_generated_at,
                    'success_rate' => $rule->success_rate,
                    'next_generation_time' => $rule->next_generation_time,
                ];
            });

        return Inertia::render('Rules/Index', [
            'rules' => $rules,
        ]);
    }

    /**
     * Formulaire de création
     */
    public function create(): Response
    {
        $organization = Auth::user()->organization;

        $templates = SmsTemplate::forOrganization($organization->id)
            ->active()
            ->get(['id', 'name']);

        return Inertia::render('Rules/Create', [
            'templates' => $templates,
            'availableFields' => $this->getAvailableFields($organization),
        ]);
    }

    /**
     * Sauvegarde d'une nouvelle règle
     */
    public function store(Request $request): RedirectResponse
    {
        $organization = Auth::user()->organization;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'sms_template_id' => 'required|exists:sms_templates,id',
            'is_active' => 'boolean',
            'trigger_field' => 'required|string',
            'trigger_condition' => 'required|in:before,after,equals,between',
            'trigger_value' => 'required|string',
            'trigger_unit' => 'required|in:minutes,hours,days,weeks',
            'send_time' => 'nullable|string',
            // NOUVEAUX CHAMPS
            'generation_frequency' => 'required|in:daily,weekly,monthly',
            'generation_time' => 'required|string',
            'generation_day_of_week' => 'nullable|integer|min:0|max:6',
            'generation_day_of_month' => 'nullable|integer|min:1|max:31',
            'priority' => 'nullable|integer|min:0|max:10',
            'daily_limit' => 'nullable|integer|min:1',
        ]);

        $rule = SmsRule::create([
            'organization_id' => $organization->id,
            ...$validated,
            'priority' => $validated['priority'] ?? 5,
        ]);

        Log::info('SMS Rule created', [
            'rule_id' => $rule->id,
            'rule_name' => $rule->name,
            'organization_id' => $organization->id,
        ]);

        return redirect()->route('rules.index')
            ->with('success', "✅ Règle \"{$rule->name}\" créée avec succès");
    }

    /**
     * Formulaire d'édition
     */
    public function edit(SmsRule $rule): Response
    {
        $organization = Auth::user()->organization;
        
        if ($rule->organization_id !== $organization->id) {
            abort(403);
        }

        $templates = SmsTemplate::forOrganization($organization->id)
            ->active()
            ->get(['id', 'name']);

        return Inertia::render('Rules/Edit', [
            'rule' => $rule->load('template'),
            'templates' => $templates,
            'availableFields' => $this->getAvailableFields($organization),
        ]);
    }

    /**
     * Mise à jour d'une règle
     */
    public function update(Request $request, SmsRule $rule): RedirectResponse
    {
        $organization = Auth::user()->organization;
        
        if ($rule->organization_id !== $organization->id) {
            abort(403);
        }

        // Debug temporaire
        Log::info('SMS Rule update attempt', [
            'rule_id' => $rule->id,
            'request_data' => $request->all(),
            'trigger_value_type' => gettype($request->get('trigger_value')),
            'trigger_value_value' => $request->get('trigger_value'),
        ]);

        try {
            $validated = $request->validate([
                'name' => 'sometimes|string|max:255',
                'description' => 'nullable|string|max:500',
                'sms_template_id' => 'sometimes|exists:sms_templates,id',
                'is_active' => 'sometimes|boolean',
                'trigger_field' => 'sometimes|string',
                'trigger_condition' => 'sometimes|in:before,after,equals,between',
                'trigger_value' => 'sometimes|string',
                'trigger_unit' => 'sometimes|in:minutes,hours,days,weeks',
                'send_time' => 'nullable|string',
                'generation_frequency' => 'sometimes|in:daily,weekly,monthly',
                'generation_time' => 'sometimes|string',
                'generation_day_of_week' => 'nullable|integer|min:0|max:6',
                'generation_day_of_month' => 'nullable|integer|min:1|max:31',
                'priority' => 'nullable|integer|min:0|max:10',
                'daily_limit' => 'nullable|integer|min:1',
            ]);

            Log::info('Validation passed', ['validated_data' => $validated]);

            $rule->update($validated);

            Log::info('SMS Rule updated', [
                'rule_id' => $rule->id,
                'rule_name' => $rule->name,
            ]);

            return redirect()->route('rules.index')
                ->with('success', 'Règle mise à jour avec succès !');
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation failed', [
                'errors' => $e->errors(),
                'request_data' => $request->all(),
            ]);
            
            throw $e;
        }
    }

    /**
     * Suppression d'une règle
     */
    public function destroy(SmsRule $rule): RedirectResponse
    {
        $organization = Auth::user()->organization;
        
        if ($rule->organization_id !== $organization->id) {
            abort(403);
        }

        $rule->delete();

        return redirect()->route('rules.index')
            ->with('success', 'Règle supprimée avec succès !');
    }

    /**
     * Toggle active/inactive
     */
    public function toggle(SmsRule $rule): JsonResponse
    {
        $organization = Auth::user()->organization;
        
        if ($rule->organization_id !== $organization->id) {
            abort(403);
        }

        $rule->update(['is_active' => !$rule->is_active]);

        Log::info('SMS Rule toggled', [
            'rule_id' => $rule->id,
            'is_active' => $rule->is_active,
        ]);

        return response()->json([
            'success' => true,
            'is_active' => $rule->is_active,
        ]);
    }

    /**
     * Test d'une règle (preview)
     */
    public function test(Request $request, SmsRule $rule): JsonResponse
    {
        $organization = Auth::user()->organization;
        
        if ($rule->organization_id !== $organization->id) {
            abort(403);
        }

        // Simuler un case pour le test
        $testCase = [
            'case_id' => 'TEST_001',
            'case_name' => 'Test Patient',
            'contact_phone_number' => '+225070000000',
            'next_visit_date' => now()->addDay()->format('Y-m-d'),
        ];

        $template = $rule->template;
        $preview = $template->renderWithData($testCase);

        return response()->json([
            'success' => true,
            'preview' => $preview,
            'test_data' => $testCase,
        ]);
    }

    /**
     * Générer SMS manuellement
     */
    public function generate(SmsRule $rule, SmsGenerationService $service)
    {
        $organization = Auth::user()->organization;

        if ($rule->organization_id !== $organization->id) {
            abort(403, 'Unauthorized');
        }

        if (!$rule->canGenerate()) {
            return back()->with('error', '❌ Cette règle ne peut pas générer de SMS (inactive ou en pause)');
        }

        Log::info('Manual SMS generation started', [
            'rule_id' => $rule->id,
            'rule_name' => $rule->name,
            'user_id' => Auth::id(),
        ]);

        // Générer avec trigger_type = 'manual'
        $result = $service->generateForRule($rule, 'manual', Auth::id());

        if ($result['success']) {
            $message = "✅ Génération terminée :\n";
            $message .= "• {$result['generated']} SMS générés\n";
            
            if ($result['duplicates'] > 0) {
                $message .= "• {$result['duplicates']} doublons ignorés\n";
            }
            
            if ($result['errors'] > 0) {
                $message .= "• ⚠️ {$result['errors']} erreurs";
            }

            return back()->with('success', $message);
        } else {
            return back()->with('error', "❌ Erreur : {$result['error']}");
        }
    }

    /**
     * Mettre en pause
     */
    public function pause(SmsRule $rule, Request $request)
    {
        $organization = Auth::user()->organization;

        if ($rule->organization_id !== $organization->id) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        $rule->pause($validated['reason'] ?? null);

        Log::info('Rule paused', [
            'rule_id' => $rule->id,
            'rule_name' => $rule->name,
            'reason' => $validated['reason'] ?? 'No reason',
            'user_id' => Auth::id(),
        ]);

        return back()->with('success', "⏸️ Règle \"{$rule->name}\" mise en pause");
    }

    /**
     * Reprendre
     */
    public function resume(SmsRule $rule)
    {
        $organization = Auth::user()->organization;

        if ($rule->organization_id !== $organization->id) {
            abort(403, 'Unauthorized');
        }

        $rule->resume();

        Log::info('Rule resumed', [
            'rule_id' => $rule->id,
            'rule_name' => $rule->name,
            'user_id' => Auth::id(),
        ]);

        return back()->with('success', "▶️ Règle \"{$rule->name}\" reprise");
    }

    /**
     * Récupère les champs disponibles pour les conditions
     */
    private function getAvailableFields($organization): array
    {
        $fields = [];

        foreach ($organization->case_properties_mapping ?? [] as $property) {
            $fields[$property] = ucfirst(str_replace('_', ' ', $property));
        }

        return $fields;
    }
}
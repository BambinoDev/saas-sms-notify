<?php

namespace App\Http\Controllers;

use App\Models\SmsRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class SmsRuleController extends Controller
{
    public function index()
    {
        $rules = SmsRule::ordered()->get();

        return Inertia::render('Settings/SmsRules', [
            'rules' => $rules,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'type' => 'required|string|max:50|unique:sms_rules,type',
            'days_before' => 'required|integer|min:0|max:30',
            'sending_time' => 'required|date_format:H:i',
            'template' => 'required|string|max:500',
            'active' => 'boolean',
            'priority' => 'integer|min:0',
            'window_enabled' => 'boolean',
            'window_start' => 'required|date_format:H:i',
            'window_end' => 'required|date_format:H:i|after:window_start',
        ]);

        // Gérer la checkbox window_enabled (checkbox non cochée = pas envoyée)
        $validated['window_enabled'] = $request->has('window_enabled');

        SmsRule::create($validated);

        return back()->with('success', 'Règle SMS créée avec succès');
    }

    public function update(Request $request, $id)
    {
        Log::info('Début update règle', [
            'id' => $id,
            'data_received' => $request->all()
        ]);
        
        try {
            $rule = SmsRule::findOrFail($id);
            
            // Validation moins stricte
            $validated = $request->validate([
                'name' => 'sometimes|string|max:100',
                'days_before' => 'sometimes|integer|min:0|max:30',
                'sending_time' => 'sometimes|string', // Enlever date_format temporairement
                'template' => 'sometimes|string|max:500',
                'active' => 'sometimes|boolean',
                'priority' => 'sometimes|integer|min:0',
                'window_enabled' => 'sometimes|boolean',
                'window_start' => 'sometimes|date_format:H:i',
                'window_end' => 'sometimes|date_format:H:i|after:window_start',
            ]);

            // Gérer la checkbox window_enabled (checkbox non cochée = pas envoyée)
            if ($request->has('window_enabled')) {
                $validated['window_enabled'] = true;
            } else {
                $validated['window_enabled'] = false;
            }

            Log::info('Données validées', ['validated' => $validated]);

            // Si sending_time est présent, valider le format manuellement
            if (isset($validated['sending_time'])) {
                // Accepter HH:mm ou HH:mm:ss
                if (!preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $validated['sending_time'])) {
                    Log::error('Format sending_time invalide', ['time' => $validated['sending_time']]);
                    return back()->with('error', 'Format d\'heure invalide');
                }
                // Normaliser au format HH:mm si HH:mm:ss
                if (strlen($validated['sending_time']) > 5) {
                    $validated['sending_time'] = substr($validated['sending_time'], 0, 5);
                }
            }

            $rule->update($validated);

            Log::info('Update OK', ['rule' => $rule->fresh()]);

            return back()->with('success', 'Règle SMS mise à jour avec succès');
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Erreur validation', [
                'errors' => $e->errors()
            ]);
            return back()->withErrors($e->errors())->with('error', 'Erreur de validation');
            
        } catch (\Exception $e) {
            Log::error('Erreur update règle', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->with('error', 'Erreur lors de la mise à jour');
        }
    }

    public function destroy($id)
    {
        try {
            $rule = SmsRule::findOrFail($id);
            $rule->delete();
            
            Log::info('Règle SMS supprimée', ['rule_id' => $id]);
            
            return back()->with('success', 'Règle SMS supprimée');
        } catch (\Exception $e) {
            Log::error('Erreur suppression règle', [
                'rule_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return back()->with('error', 'Erreur lors de la suppression');
        }
    }

    public function toggle($id)
    {
        try {
            $rule = SmsRule::findOrFail($id);
            $rule->update(['active' => !$rule->active]);
            
            $status = $rule->active ? 'activée' : 'désactivée';
            
            Log::info('Règle SMS toggle', [
                'rule_id' => $id,
                'new_status' => $status
            ]);
            
            return back()->with('success', "Règle {$status}");
        } catch (\Exception $e) {
            Log::error('Erreur toggle règle', [
                'rule_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return back()->with('error', 'Erreur lors du changement de statut');
        }
    }
}
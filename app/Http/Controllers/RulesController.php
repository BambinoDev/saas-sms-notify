<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\SmsRule;
use Illuminate\Support\Facades\Validator;

class RulesController extends Controller
{
    /**
     * Display rules list with filters and pagination
     */
    public function index(Request $request)
    {
        $query = SmsRule::query();

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'ILIKE', "%{$search}%")
                  ->orWhere('type', 'ILIKE', "%{$search}%")
                  ->orWhere('template', 'ILIKE', "%{$search}%");
            });
        }

        // Type filter
        if ($request->filled('type') && $request->type !== 'all') {
            $query->where('type', $request->type);
        }

        // Status filter
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('active', $request->status === 'active');
        }

        // Stats
        $stats = $this->getStats();

        // Pagination
        $perPage = $request->get('per_page', 25);
        $rules = $query->orderBy('priority', 'asc')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->through(function ($rule) {
                return [
                    'id' => $rule->id,
                    'name' => $rule->name,
                    'type' => $rule->type,
                    'template' => $rule->template, // ← Le template stocké comme texte
                    'template_id' => null, // ← Pas de template_id pour l'instant
                    'template_name' => 'Custom', // ← Indique que c'est un template personnalisé
                    'days_before' => $rule->days_before,
                    'sending_time' => $rule->sending_time,
                    'window_start' => '06:00', // Default (colonne pas encore migrée)
                    'window_end' => '12:00',   // Default (colonne pas encore migrée)
                    'status' => $rule->active ? 'active' : 'inactive',
                    'priority' => $rule->priority,
                    'sent_count' => $rule->usage_count,
                    'created_at' => $rule->created_at->format('Y-m-d'),
                ];
            });

        // Charger TOUTES les règles actives pour permettre de copier les templates
        // (les templates sont stockés directement dans sms_rules.template)
        $templates = SmsRule::where('active', true)
            ->orderBy('name', 'asc')
            ->get()
            ->map(function($rule) {
                return [
                    'id' => $rule->id,
                    'name' => $rule->name,
                    'type' => $rule->type,
                    'message' => $rule->template, // Template stocké dans la règle
                ];
            });

        return Inertia::render('Rules/Index', [
            'rules' => $rules,
            'templates' => $templates, // ← Règles actives pour copier les templates
            'stats' => $stats,
            'filters' => $request->only(['search', 'type', 'status']),
        ]);
    }

    /**
     * Get rules stats
     */
    private function getStats()
    {
        return [
            'total' => SmsRule::count(),
            'active' => SmsRule::where('active', true)->count(),
            'inactive' => SmsRule::where('active', false)->count(),
            'total_sent' => 0, // Will implement with real SMS tracking
        ];
    }

    /**
     * Store new rule
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:reminder,confirmation,notification',
            'days_before' => 'required|integer',
            'sending_time' => 'required|string', // ← Accepte tous formats
            'priority' => 'required|integer|min:1',
            'template' => 'required|string',
            'status' => 'required|string|in:active,inactive',
        ]);

        // Normaliser l'heure (enlever les secondes si présentes: "09:00:00" -> "09:00")
        $validated['sending_time'] = substr($validated['sending_time'], 0, 5);

        SmsRule::create([
            'name' => $validated['name'],
            'type' => $validated['type'],
            'days_before' => $validated['days_before'],
            'sending_time' => $validated['sending_time'],
            'priority' => $validated['priority'],
            'template' => $validated['template'],
            'active' => $validated['status'] === 'active',
        ]);

        return redirect()->route('rules.index')->with('success', 'Rule created successfully');
    }

    /**
     * Update existing rule
     */
    public function update(Request $request, $id)
    {
        $rule = SmsRule::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:reminder,confirmation,notification',
            'days_before' => 'required|integer',
            'sending_time' => 'required|string', // ← Accepte tous formats
            'priority' => 'required|integer|min:1',
            'template' => 'required|string',
            'status' => 'required|string|in:active,inactive',
        ]);

        // Normaliser l'heure (enlever les secondes si présentes: "09:00:00" -> "09:00")
        $validated['sending_time'] = substr($validated['sending_time'], 0, 5);

        $rule->update([
            'name' => $validated['name'],
            'type' => $validated['type'],
            'days_before' => $validated['days_before'],
            'sending_time' => $validated['sending_time'],
            'priority' => $validated['priority'],
            'template' => $validated['template'],
            'active' => $validated['status'] === 'active',
        ]);

        return redirect()->route('rules.index')->with('success', 'Rule updated successfully');
    }

    /**
     * Delete rule
     */
    public function destroy($id)
    {
        $rule = SmsRule::findOrFail($id);
        $rule->delete();

        return redirect()->back()->with('success', 'Rule deleted successfully');
    }

    /**
     * Toggle rule status (active/inactive)
     */
    public function toggleStatus($id)
    {
        $rule = SmsRule::findOrFail($id);
        $rule->update(['active' => !$rule->active]);

        $status = $rule->active ? 'activated' : 'deactivated';
        return redirect()->back()->with('success', "Rule {$status} successfully");
    }

    /**
     * Update rules priority order
     */
    public function updatePriority(Request $request)
    {
        $rules = $request->input('rules', []);

        foreach ($rules as $index => $ruleId) {
            SmsRule::where('id', $ruleId)->update(['priority' => $index + 1]);
        }

        return redirect()->back()->with('success', 'Priority order updated successfully');
    }
}

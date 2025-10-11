<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\SmsRule;
use Illuminate\Support\Facades\Validator;

class TemplatesController extends Controller
{
    /**
     * Display templates list
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
        $templates = $query->orderBy('priority', 'asc')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->through(function ($template) {
                return [
                    'id' => $template->id,
                    'name' => $template->name ?? ucfirst($template->type),
                    'type' => $template->type,
                    'language' => 'fr', // Default for now
                    'message' => $template->template,
                    'variables' => $template->template_variables_used,
                    'status' => $template->active ? 'active' : 'inactive',
                    'usage_count' => $template->usage_count,
                    'created_at' => $template->created_at->format('Y-m-d'),
                    'updated_at' => $template->updated_at->format('Y-m-d'),
                ];
            });

        return Inertia::render('Templates/Index', [
            'templates' => $templates,
            'stats' => $stats,
            'filters' => $request->only(['search', 'type', 'status']),
            'available_variables' => SmsRule::getTemplateVariables(),
        ]);
    }

    /**
     * Get templates stats (SIMPLIFIED)
     */
    private function getStats()
    {
        return [
            'total' => SmsRule::count(),
            'active' => SmsRule::where('active', true)->count(),
            'inactive' => SmsRule::where('active', false)->count(),
            'total_usage' => 0, // Will show 0 until rule_id is implemented
        ];
    }

    /**
     * Store new template
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'type' => 'required|string|max:50',
            'template' => 'required|string|max:160',
            'days_before' => 'required|integer',
            'sending_time' => 'required|date_format:H:i',
            'priority' => 'nullable|integer|min:1',
            'active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $maxPriority = SmsRule::max('priority') ?? 0;

        SmsRule::create([
            'name' => $request->name,
            'type' => $request->type,
            'template' => $request->template,
            'days_before' => $request->days_before,
            'sending_time' => $request->sending_time,
            'priority' => $request->priority ?? ($maxPriority + 1),
            'active' => $request->active ?? true,
        ]);

        return redirect()->back()->with('success', 'Template created successfully');
    }

    /**
     * Update template
     */
    public function update(Request $request, $id)
    {
        $template = SmsRule::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:100',
            'type' => 'required|string|max:50',
            'template' => 'required|string|max:160',
            'days_before' => 'nullable|integer',
            'sending_time' => 'nullable|date_format:H:i',
            'priority' => 'nullable|integer|min:1',
            'active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $updateData = [
            'type' => $request->type,
            'template' => $request->template,
            'active' => $request->active ?? $template->active,
        ];

        // Only update if provided
        if ($request->filled('name')) {
            $updateData['name'] = $request->name;
        }
        if ($request->filled('days_before')) {
            $updateData['days_before'] = $request->days_before;
        }
        if ($request->filled('sending_time')) {
            $updateData['sending_time'] = $request->sending_time;
        }
        if ($request->filled('priority')) {
            $updateData['priority'] = $request->priority;
        }

        $template->update($updateData);

        return redirect()->back()->with('success', 'Template updated successfully');
    }

    /**
     * Delete template
     */
    public function destroy($id)
    {
        $template = SmsRule::findOrFail($id);

        // Simplified - just delete for now
        // TODO: Add usage check when rule_id exists in sms_queue
        $template->delete();

        return redirect()->back()->with('success', 'Template deleted successfully');
    }

    /**
     * Duplicate template
     */
    public function duplicate($id)
    {
        $template = SmsRule::findOrFail($id);

        $maxPriority = SmsRule::max('priority') ?? 0;

        $newTemplate = $template->replicate();
        $newTemplate->name = ($template->name ?? $template->type) . ' (Copy)';
        $newTemplate->priority = $maxPriority + 1;
        $newTemplate->save();

        return redirect()->back()->with('success', 'Template duplicated successfully');
    }

    /**
     * Preview template with sample data
     */
    public function preview(Request $request)
    {
        $template = $request->input('template');
        
        // Sample data for preview
        $sampleData = [
            'case_name' => 'Marie KOUASSI',
            'case_id' => 'ABC123',
            'visit_date' => '2025-03-15',
            'visit_time' => '09:00',
            'facility_name' => 'CHU Cocody',
            'district' => 'Abidjan',
            'contact_phone' => '+225 07 12 34 56 78',
        ];

        // Replace variables
        $preview = $template;
        foreach ($sampleData as $key => $value) {
            $preview = str_replace("{{$key}}", $value, $preview);
        }

        return response()->json([
            'preview' => $preview,
            'character_count' => strlen($preview),
        ]);
    }

    /**
     * Toggle template status
     */
    public function toggleStatus($id)
    {
        $template = SmsRule::findOrFail($id);
        $template->update(['active' => !$template->active]);

        return redirect()->back()->with('success', 'Template status updated');
    }
}

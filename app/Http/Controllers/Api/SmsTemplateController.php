<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SmsTemplate;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SmsTemplateController extends Controller
{
    public function index(): JsonResponse
    {
        $templates = SmsTemplate::orderBy('priority')->get();
        return response()->json($templates);
    }

    public function show(string $id): JsonResponse
    {
        $template = SmsTemplate::with(['smsQueue'])->findOrFail($id);
        return response()->json($template);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'case_type' => 'nullable|string',
            'trigger_type' => 'required|in:date_based,sequence_based,status_change,custom_condition',
            'days_before' => 'nullable|integer',
            'sending_time' => 'required|date_format:H:i:s',
            'message_template' => 'required|string',
            'is_active' => 'boolean',
        ]);

        $template = SmsTemplate::create($validated);

        return response()->json($template, 201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $template = SmsTemplate::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string',
            'message_template' => 'sometimes|string',
            'is_active' => 'sometimes|boolean',
            'days_before' => 'sometimes|integer',
            'sending_time' => 'sometimes|date_format:H:i:s',
        ]);

        $template->update($validated);

        return response()->json($template);
    }

    public function destroy(string $id): JsonResponse
    {
        $template = SmsTemplate::findOrFail($id);
        $template->delete();

        return response()->json(['message' => 'Template deleted successfully']);
    }

    public function testRender(Request $request, string $id): JsonResponse
    {
        $template = SmsTemplate::findOrFail($id);

        $validated = $request->validate([
            'case_name' => 'required|string',
            'primary_date' => 'nullable|string',
            'sequence_number' => 'nullable|integer',
            'location_name' => 'nullable|string',
        ]);

        $rendered = $template->renderMessage($validated);

        return response()->json([
            'template' => $template->message_template,
            'rendered' => $rendered,
            'length' => strlen($rendered),
            'parts' => ceil(strlen($rendered) / 160),
        ]);
    }

    public function stats(): JsonResponse
    {
        return response()->json([
            'total' => SmsTemplate::count(),
            'active' => SmsTemplate::active()->count(),
            'by_trigger_type' => SmsTemplate::selectRaw('trigger_type, count(*) as count')
                ->groupBy('trigger_type')
                ->get(),
            'total_sent' => SmsTemplate::sum('total_sent'),
            'total_delivered' => SmsTemplate::sum('total_delivered'),
            'total_failed' => SmsTemplate::sum('total_failed'),
        ]);
    }
}

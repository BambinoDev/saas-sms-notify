<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TenantCase;
use App\Models\TenantPhoneConfig;
use App\Services\PhoneValidationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CaseController extends Controller
{
    public function __construct(
        private PhoneValidationService $phoneService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = TenantCase::query();

        // Filtres
        if ($request->has('case_type')) {
            $query->where('case_type', $request->case_type);
        }

        if ($request->has('eligible_sms')) {
            $query->eligibleForSms();
        }

        if ($request->has('search')) {
            $query->where('case_name', 'like', "%{$request->search}%");
        }

        // Pagination
        $cases = $query->paginate($request->get('per_page', 15));

        return response()->json($cases);
    }

    public function show(string $id): JsonResponse
    {
        $case = TenantCase::with(['smsQueue'])->findOrFail($id);
        return response()->json($case);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'case_id' => 'required|string|unique:cases,case_id',
            'case_type' => 'required|string',
            'case_name' => 'required|string',
            'contact_phone_number' => 'nullable|string',
            'primary_date' => 'nullable|date',
            'sequence_number' => 'nullable|integer',
            'properties' => 'nullable|array',
        ]);

        // Valider et formater le numéro si présent
        if (isset($validated['contact_phone_number'])) {
            $phoneConfig = TenantPhoneConfig::on('pgsql')
                ->where('tenant_id', tenancy()->tenant->id)
                ->first();
            
            if ($phoneConfig) {
                $result = $this->phoneService->setConfig($phoneConfig)->validate($validated['contact_phone_number']);
                
                $validated['formatted_phone_number'] = $result['formatted'];
                $validated['phone_valid'] = $result['valid'];
                $validated['phone_validation_error'] = $result['error'];
            }
        }

        $case = TenantCase::create($validated);

        return response()->json($case, 201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $case = TenantCase::findOrFail($id);

        $validated = $request->validate([
            'case_name' => 'sometimes|string',
            'contact_phone_number' => 'sometimes|string',
            'primary_date' => 'sometimes|date',
            'sequence_number' => 'sometimes|integer',
            'properties' => 'sometimes|array',
        ]);

        // Revalider le numéro si modifié
        if (isset($validated['contact_phone_number'])) {
            $phoneConfig = TenantPhoneConfig::on('pgsql')
                ->where('tenant_id', tenancy()->tenant->id)
                ->first();
            
            if ($phoneConfig) {
                $result = $this->phoneService->setConfig($phoneConfig)->validate($validated['contact_phone_number']);
                
                $validated['formatted_phone_number'] = $result['formatted'];
                $validated['phone_valid'] = $result['valid'];
                $validated['phone_validation_error'] = $result['error'];
            }
        }

        $case->update($validated);

        return response()->json($case);
    }

    public function destroy(string $id): JsonResponse
    {
        $case = TenantCase::findOrFail($id);
        $case->delete();

        return response()->json(['message' => 'Case deleted successfully']);
    }

    public function stats(): JsonResponse
    {
        return response()->json([
            'total' => TenantCase::count(),
            'eligible_for_sms' => TenantCase::eligibleForSms()->count(),
            'open' => TenantCase::open()->count(),
            'closed' => TenantCase::where('is_closed', true)->count(),
            'by_type' => TenantCase::selectRaw('case_type, count(*) as count')
                ->groupBy('case_type')
                ->get(),
        ]);
    }
}

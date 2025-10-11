<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InitializeTenancy
{
    public function handle(Request $request, Closure $next): Response
    {
        // Récupérer tenant_id du header ou du sous-domaine
        $tenantId = $request->header('X-Tenant-ID') 
                    ?? $request->route('tenant_id');

        if (!$tenantId) {
            return response()->json([
                'error' => 'Tenant ID required',
                'message' => 'Please provide X-Tenant-ID header or tenant_id parameter'
            ], 400);
        }

        $tenant = Tenant::find($tenantId);

        if (!$tenant) {
            return response()->json([
                'error' => 'Tenant not found',
                'message' => "Tenant with ID {$tenantId} does not exist"
            ], 404);
        }

        if ($tenant->status !== 'active') {
            return response()->json([
                'error' => 'Tenant suspended',
                'message' => 'This tenant account is not active'
            ], 403);
        }

        // Initialiser le contexte tenant
        tenancy()->initialize($tenant);

        $response = $next($request);

        // Nettoyer le contexte après la requête
        tenancy()->end();

        return $response;
    }
}

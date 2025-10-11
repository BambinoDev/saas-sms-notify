<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateApiToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();
        $validToken = config('app.api_token');

        if (!$token || $token !== $validToken) {
            return response()->json([
                'success' => false,
                'message' => 'Token d\'authentification invalide',
            ], 401);
        }

        return $next($request);
    }
}
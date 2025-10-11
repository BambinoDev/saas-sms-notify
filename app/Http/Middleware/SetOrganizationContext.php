<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SetOrganizationContext
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();
            
            // Récupérer la première organisation de l'utilisateur
            $organization = $user->firstOrganization();
            
            if ($organization) {
                // Définir l'organisation active dans le request
                $request->merge(['current_organization' => $organization]);
                
                // Partager avec les vues
                view()->share('currentOrganization', $organization);
                
                // Définir dans la session
                session(['current_organization_id' => $organization->id]);
            }
        }
        
        return $next($request);
    }
}


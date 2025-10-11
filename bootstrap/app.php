<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \App\Http\Middleware\SetOrganizationContext::class,
        ]);
        
        $middleware->alias([
            'api.token' => \App\Http\Middleware\ValidateApiToken::class,
            'tenant' => \App\Http\Middleware\InitializeTenancy::class,
            'superadmin' => \App\Http\Middleware\EnsureSuperAdmin::class,
        ]);

        // Rediriger les utilisateurs non authentifiés vers la page login
        $middleware->redirectGuestsTo('/login');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();

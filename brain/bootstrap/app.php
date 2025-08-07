<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Exclude webhook endpoints from CSRF protection
        $middleware->validateCsrfTokens(except: [
            '/webhook.php',
            '/webhook/*',
            '/api/webhooks',
            '/test-lead-data',
            '/api-webhook',
            '/webhook-failsafe.php',
            '/webhook-emergency',
            '/test-webhook',
            'api-webhook',
            'webhook*',
            'api/*',
            '/admin/clear-test-leads'
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();

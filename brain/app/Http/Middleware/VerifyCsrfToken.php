<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        // Webhook endpoints - NEVER require CSRF
        'webhook*',
        'webhook.php',
        'webhook-failsafe.php',
        'test-webhook',
        'webhook/*',
        '/webhook*',
        '/webhook.php',
        '/webhook-failsafe.php',
        '/test-webhook',
        '/webhook/*',
        
        // API endpoints
        'api/*',
        '/api/*',
        
        // Admin clear leads endpoint
        'admin/clear-test-leads',
        '/admin/clear-test-leads',
        
        // Any other endpoints that should bypass CSRF
        'webhook/debug',
        'webhook/vici',
        'webhook/leadsquotingfast',
    ];
}




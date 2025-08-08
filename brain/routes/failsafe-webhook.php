<?php

// FAILSAFE WEBHOOK ENDPOINT
// This endpoint ALWAYS returns 200 OK immediately to prevent lead loss
// It queues the lead for processing later

Route::post('/webhook-failsafe.php', function (Request $request) {
    try {
        // Store in queue immediately
        \App\Models\LeadQueue::create([
            'payload' => $request->all(),
            'source' => 'leadsquotingfast',
            'status' => 'pending'
        ]);
        
        // Log for monitoring
        Log::info('Lead queued for processing', [
            'timestamp' => now(),
            'ip' => $request->ip()
        ]);
        
        // Return success immediately (prevents timeout/loss)
        return response()->json([
            'success' => true,
            'message' => 'Lead queued for processing',
            'timestamp' => now()->toIso8601String()
        ], 200);
        
    } catch (\Exception $e) {
        // Even if queueing fails, return 200 to prevent retry storms
        Log::error('Failed to queue lead but returning 200', [
            'error' => $e->getMessage(),
            'payload' => $request->all()
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Failed to queue but acknowledged',
            'error' => $e->getMessage()
        ], 200); // Still return 200!
    }
})->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);




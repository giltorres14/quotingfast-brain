<?php
// MULTI-WEBHOOK ARCHITECTURE EXAMPLES
// ===================================

// 1. LeadsQuotingFast Webhook (existing)
Route::post('/webhook/leadsquotingfast', function (Request $request) {
    $leadData = [
        'source' => 'leadsquotingfast',
        'type' => 'auto_insurance',
        // ... existing logic
    ];
    
    // LQF-specific processing
    $lead = Lead::create($leadData);
    
    // LQF-specific SMS template
    // triggerSMS($lead, 'lqf_template');
    
    return response()->json(['success' => true, 'lead_id' => $lead->id]);
})->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);

// 2. Facebook Ads Webhook
Route::post('/webhook/facebook', function (Request $request) {
    $data = $request->all();
    $contact = $data['contact'] ?? $data;
    
    $leadData = [
        'name' => trim(($contact['first_name'] ?? '') . ' ' . ($contact['last_name'] ?? '')),
        'phone' => $contact['phone'] ?? 'Unknown',
        'email' => $contact['email'] ?? null,
        'source' => 'facebook_ads',
        'type' => 'social_media',
        'campaign_id' => $data['campaign_id'] ?? null, // Facebook-specific
        'ad_set_id' => $data['ad_set_id'] ?? null,     // Facebook-specific
        'received_at' => now(),
        'payload' => json_encode($data),
    ];
    
    $lead = Lead::create($leadData);
    
    // Facebook-specific SMS template
    // triggerSMS($lead, 'facebook_template');
    
    Log::info('Facebook lead received', ['lead_id' => $lead->id, 'campaign' => $data['campaign_id'] ?? 'unknown']);
    
    return response()->json(['success' => true, 'lead_id' => $lead->id, 'source' => 'facebook']);
})->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);

// 3. Google Ads Webhook
Route::post('/webhook/google', function (Request $request) {
    $data = $request->all();
    $contact = $data['contact'] ?? $data;
    
    $leadData = [
        'name' => trim(($contact['first_name'] ?? '') . ' ' . ($contact['last_name'] ?? '')),
        'phone' => $contact['phone'] ?? 'Unknown',
        'email' => $contact['email'] ?? null,
        'source' => 'google_ads',
        'type' => 'search_ads',
        'gclid' => $data['gclid'] ?? null,           // Google-specific
        'keyword' => $data['keyword'] ?? null,       // Google-specific
        'received_at' => now(),
        'payload' => json_encode($data),
    ];
    
    $lead = Lead::create($leadData);
    
    // Google-specific SMS template
    // triggerSMS($lead, 'google_template');
    
    Log::info('Google lead received', ['lead_id' => $lead->id, 'keyword' => $data['keyword'] ?? 'unknown']);
    
    return response()->json(['success' => true, 'lead_id' => $lead->id, 'source' => 'google']);
})->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);

// 4. Organic/SEO Webhook
Route::post('/webhook/organic', function (Request $request) {
    $data = $request->all();
    $contact = $data['contact'] ?? $data;
    
    $leadData = [
        'name' => trim(($contact['first_name'] ?? '') . ' ' . ($contact['last_name'] ?? '')),
        'phone' => $contact['phone'] ?? 'Unknown',
        'email' => $contact['email'] ?? null,
        'source' => 'organic_seo',
        'type' => 'organic',
        'landing_page' => $data['landing_page'] ?? null, // SEO-specific
        'referrer' => $data['referrer'] ?? null,         // SEO-specific
        'received_at' => now(),
        'payload' => json_encode($data),
    ];
    
    $lead = Lead::create($leadData);
    
    // Organic-specific SMS template (maybe more detailed since they're warmer leads)
    // triggerSMS($lead, 'organic_template');
    
    Log::info('Organic lead received', ['lead_id' => $lead->id, 'page' => $data['landing_page'] ?? 'unknown']);
    
    return response()->json(['success' => true, 'lead_id' => $lead->id, 'source' => 'organic']);
})->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);

// 5. Call Center Webhook (for leads that come through phone)
Route::post('/webhook/callcenter', function (Request $request) {
    $data = $request->all();
    $contact = $data['contact'] ?? $data;
    
    $leadData = [
        'name' => trim(($contact['first_name'] ?? '') . ' ' . ($contact['last_name'] ?? '')),
        'phone' => $contact['phone'] ?? 'Unknown',
        'email' => $contact['email'] ?? null,
        'source' => 'call_center',
        'type' => 'inbound_call',
        'call_duration' => $data['call_duration'] ?? null,  // Call-specific
        'agent_id' => $data['agent_id'] ?? null,            // Call-specific
        'call_recording' => $data['recording_url'] ?? null, // Call-specific
        'received_at' => now(),
        'payload' => json_encode($data),
    ];
    
    $lead = Lead::create($leadData);
    
    // Call center leads might not need SMS since they already talked to someone
    // triggerSMS($lead, 'followup_template');
    
    Log::info('Call center lead received', ['lead_id' => $lead->id, 'agent' => $data['agent_id'] ?? 'unknown']);
    
    return response()->json(['success' => true, 'lead_id' => $lead->id, 'source' => 'call_center']);
})->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);

// 6. Generic Webhook (fallback for any source)
Route::post('/webhook/generic', function (Request $request) {
    $data = $request->all();
    $contact = $data['contact'] ?? $data;
    
    $leadData = [
        'name' => trim(($contact['first_name'] ?? '') . ' ' . ($contact['last_name'] ?? '')),
        'phone' => $contact['phone'] ?? 'Unknown',
        'email' => $contact['email'] ?? null,
        'source' => $data['source'] ?? 'unknown',
        'type' => $data['type'] ?? 'generic',
        'received_at' => now(),
        'payload' => json_encode($data),
    ];
    
    $lead = Lead::create($leadData);
    
    // Generic SMS template
    // triggerSMS($lead, 'generic_template');
    
    Log::info('Generic lead received', ['lead_id' => $lead->id, 'source' => $leadData['source']]);
    
    return response()->json(['success' => true, 'lead_id' => $lead->id, 'source' => 'generic']);
})->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);

?>
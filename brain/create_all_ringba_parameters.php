<?php

/**
 * RingBA Bulk Parameter Creation Script
 * Creates all remaining URL parameters automatically
 */

// RingBA API Configuration
$accountId = 'RAf810ac4421a34c9cbfbbf61288a1bec2';
$apiUrl = "https://api.ringba.com/v2/RAf810ac4421a34c9cbfbbf61288a1bec2/queryPathMaps";

// All missing parameters to create (excluding first_name and last_name which are already created)
$parameters = [
    'email', 'phone', 'address1', 'city', 'state', 'country',
    'dob', 'gender', 'marital_status', 'residence_status', 'education_level', 'occupation',
    'currently_insured', 'current_insurance_company', 'policy_expiration_date', 'current_premium', 
    'insurance_duration', 'policy_expires', 'coverage_level', 'deductible_preference', 'coverage_type',
    'credit_score_range', 'credit_score', 'home_ownership', 'home_status', 'years_licensed', 
    'accidents_violations', 'dui_conviction', 'sr22_required', 'license_age', 'active_license', 
    'dui_timeframe', 'dui_sr22', 'num_vehicles', 'vehicle_year', 'vehicle_make', 'vehicle_model', 
    'vehicle_trim', 'vin', 'leased', 'annual_mileage', 'primary_use', 'commute_days', 
    'commute_mileage', 'garage_type', 'alarm', 'lead_source', 'lead_quality_score', 'urgency_level', 
    'best_time_to_call', 'motivation_level', 'motivation_score', 'urgency', 'consent_timestamp', 
    'opt_in_method', 'tcpa_compliant', 'ip_address', 'user_agent', 'referrer_url', 'landing_page',
    'qualified_by_agent', 'qualification_timestamp', 'agent_notes', 'call_duration',
    'shopping_for_rates', 'ready_to_speak', 'allstate_quote'
];

echo "🚀 RingBA Bulk Parameter Creation\n";
echo "📊 Creating " . count($parameters) . " parameters...\n\n";

$successCount = 0;
$failureCount = 0;
$failures = [];

foreach ($parameters as $index => $parameterName) {
    $current = $index + 1;
    $total = count($parameters);
    
    echo "🎯 [{$current}/{$total}] Creating parameter: $parameterName\n";
    
    // Prepare the API request
    $postData = [
        'incomingQueryStringName' => $parameterName,
        'mapToTagName' => $parameterName,
        'mapToTagType' => 'User'
    ];
    
    // Make the API call
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json',
        'Authorization: Token 09f0c9f046f7704cb233f54b8e21375fa6c9511b991e8f10fd3513342948f325456f4480d8314ff1dfed21bb6f8054a028a8086d93170781d26eda7e634c9594d1c131c413563b794c3d71ce50fabdb2135e0ac2e3626674048c16e0e56f8a959d967dec2e2f0f266f71645bf4ba62dd645afcb3'
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        echo "   ❌ CURL Error: $error\n";
        $failureCount++;
        $failures[] = "$parameterName (CURL Error: $error)";
        continue;
    }
    
    if ($httpCode >= 200 && $httpCode < 300) {
        echo "   ✅ SUCCESS! HTTP $httpCode\n";
        $successCount++;
    } else {
        echo "   ❌ FAILED! HTTP $httpCode - Response: $response\n";
        $failureCount++;
        $failures[] = "$parameterName (HTTP $httpCode)";
    }
    
    // Small delay to be nice to the API
    usleep(250000); // 0.25 second delay
    
    // Progress update every 10 parameters
    if ($current % 10 === 0 || $current === $total) {
        echo "\n📊 Progress Update:\n";
        echo "   ✅ Successful: $successCount\n";
        echo "   ❌ Failed: $failureCount\n";
        echo "   📈 Progress: {$current}/{$total} (" . round(($current / $total) * 100, 1) . "%)\n\n";
    }
}

echo "🎉 BULK CREATION COMPLETE!\n\n";
echo "📊 FINAL RESULTS:\n";
echo "   ✅ Successfully created: $successCount parameters\n";
echo "   ❌ Failed to create: $failureCount parameters\n";
echo "   📈 Success rate: " . round(($successCount / count($parameters)) * 100, 1) . "%\n\n";

if (!empty($failures)) {
    echo "❌ FAILED PARAMETERS:\n";
    foreach ($failures as $failure) {
        echo "   - $failure\n";
    }
    echo "\n";
}

// Calculate total including already created ones
$totalCreated = $successCount + 2; // +2 for first_name and last_name already created
echo "📋 TOTAL RINGBA PARAMETERS:\n";
echo "   🎯 Target: 69 parameters\n";
echo "   ✅ Created: $totalCreated parameters\n";
echo "   📋 Remaining: " . (69 - $totalCreated) . " parameters\n\n";

if ($totalCreated >= 69) {
    echo "🎉 ALL RINGBA PARAMETERS CREATED! Your Allstate integration is ready!\n";
} else {
    echo "⚠️  Some parameters still need to be created manually.\n";
}

echo "\n✅ Script completed!\n";

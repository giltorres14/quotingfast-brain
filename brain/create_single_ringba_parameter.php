<?php

/**
 * RingBA Single Parameter Creation Script
 * Creates one URL parameter at a time
 */

// RingBA API Configuration
$accountId = 'RAf810ac4421a34c9cbfbbf61288a1bec2';
$apiUrl = "https://api.ringba.com/v2/RAf810ac4421a34c9cbfbbf61288a1bec2/queryPathMaps";

// All missing parameters to create (one at a time)
$parameters = [
    'first_name', 'last_name', 'email', 'phone', 'address1', 'city', 'state', 'country',
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

// Get parameter name from command line argument
$parameterName = $argv[1] ?? null;

if (!$parameterName) {
    echo "🚀 RingBA Single Parameter Creation\n";
    echo "Usage: php create_single_ringba_parameter.php PARAMETER_NAME\n\n";
    echo "Available parameters:\n";
    foreach ($parameters as $index => $param) {
        echo sprintf("[%2d] %s\n", $index + 1, $param);
    }
    echo "\nExample: php create_single_ringba_parameter.php first_name\n";
    exit(1);
}

// Validate parameter name
if (!in_array($parameterName, $parameters)) {
    echo "❌ ERROR: '$parameterName' is not in the list of required parameters.\n";
    echo "Available parameters: " . implode(', ', $parameters) . "\n";
    exit(1);
}

echo "🎯 Creating RingBA parameter: $parameterName\n";
echo "📡 API URL: $apiUrl\n\n";

// Prepare the API request
$postData = [
    'incomingQueryStringName' => $parameterName,
    'mapToTagName' => $parameterName,
    'mapToTagType' => 'User'
];

echo "📋 Payload: " . json_encode($postData) . "\n\n";

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

echo "🔄 Creating parameter '$parameterName'...\n";

if ($error) {
    echo "❌ CURL Error: $error\n";
    exit(1);
}

echo "📡 HTTP Response Code: $httpCode\n";
echo "📋 Response Body: $response\n\n";

if ($httpCode >= 200 && $httpCode < 300) {
    echo "✅ SUCCESS! Parameter '$parameterName' created successfully!\n";
    
    // Show remaining parameters
    $remaining = array_diff($parameters, [$parameterName]);
    echo "\n📊 Progress: " . (69 - count($remaining)) . "/69 parameters created\n";
    echo "📋 Remaining: " . count($remaining) . " parameters\n";
    
    if (!empty($remaining)) {
        echo "\n🔄 Next parameter to create: " . $remaining[array_keys($remaining)[0]] . "\n";
        echo "💡 Run: php create_single_ringba_parameter.php " . $remaining[array_keys($remaining)[0]] . "\n";
    } else {
        echo "\n🎉 ALL PARAMETERS CREATED! Your RingBA integration is complete!\n";
    }
} else {
    echo "❌ FAILED! Parameter '$parameterName' could not be created.\n";
    
    if ($httpCode == 401) {
        echo "🔐 Authentication issue - check your API token\n";
    } elseif ($httpCode == 409) {
        echo "⚠️  Parameter might already exist\n";
    } elseif ($httpCode == 400) {
        echo "📝 Bad request - check parameter format\n";
    }
}

echo "\n✅ Script completed!\n";

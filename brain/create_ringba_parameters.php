<?php

/**
 * RingBA Parameter Creation Script
 * Automatically creates all missing URL parameters for Allstate integration
 */

// RingBA API Configuration
$accountId = 'RAf810ac4421a34c9cbfbbf61288a1bec2'; // Your RingBA account ID
$apiUrl = "https://api.ringba.com/v2/{$accountId}/queryPathMaps";

// All missing parameters to create
$parameters = [
    // Personal Information
    'first_name', 'last_name', 'email', 'phone', 'address1', 'city', 'state', 'country',
    
    // Demographics
    'dob', 'gender', 'marital_status', 'residence_status', 'education_level', 'occupation',
    
    // Insurance Status
    'currently_insured', 'current_insurance_company', 'policy_expiration_date', 'current_premium', 
    'insurance_duration', 'policy_expires',
    
    // Coverage Requirements
    'coverage_level', 'deductible_preference', 'coverage_type',
    
    // Financial Information
    'credit_score_range', 'credit_score', 'home_ownership', 'home_status',
    
    // Driving Information
    'years_licensed', 'accidents_violations', 'dui_conviction', 'sr22_required', 
    'license_age', 'active_license', 'dui_timeframe', 'dui_sr22',
    
    // Vehicle Information
    'num_vehicles', 'vehicle_year', 'vehicle_make', 'vehicle_model', 'vehicle_trim', 
    'vin', 'leased', 'annual_mileage', 'primary_use', 'commute_days', 'commute_mileage', 
    'garage_type', 'alarm',
    
    // Lead Quality & Timing
    'lead_source', 'lead_quality_score', 'urgency_level', 'best_time_to_call', 
    'motivation_level', 'motivation_score', 'urgency',
    
    // TCPA & Compliance
    'consent_timestamp', 'opt_in_method', 'tcpa_compliant',
    
    // Technical Data
    'ip_address', 'user_agent', 'referrer_url', 'landing_page',
    
    // Agent Qualification Metadata
    'qualified_by_agent', 'qualification_timestamp', 'agent_notes', 'call_duration',
    
    // Additional Qualification Questions
    'shopping_for_rates', 'ready_to_speak', 'allstate_quote'
];

echo "🚀 RingBA Parameter Creation Script\n";
echo "===================================\n\n";

// Check if account ID is set
if ($accountId === 'YOUR_ACCOUNT_ID') {
    echo "❌ ERROR: Please update the \$accountId variable with your actual RingBA account ID\n";
    echo "   Find your account ID in your RingBA dashboard URL or API documentation\n";
    exit(1);
}

echo "📊 Total parameters to create: " . count($parameters) . "\n";
echo "🎯 Target API: $apiUrl\n\n";

// Ask for confirmation
echo "This will create " . count($parameters) . " new URL parameters in your RingBA account.\n";
echo "Are you sure you want to proceed? (y/N): ";
$handle = fopen("php://stdin", "r");
$confirmation = trim(fgets($handle));
fclose($handle);

if (strtolower($confirmation) !== 'y') {
    echo "❌ Operation cancelled.\n";
    exit(0);
}

echo "\n🔄 Creating parameters...\n\n";

$successCount = 0;
$errorCount = 0;
$errors = [];

foreach ($parameters as $index => $parameter) {
    $progress = $index + 1;
    echo "[$progress/" . count($parameters) . "] Creating: $parameter... ";
    
    // Prepare the API request
    $postData = [
        'incomingQueryStringName' => $parameter,
        'mapToTagName' => $parameter,
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
        'Authorization: Bearer 09f0c9f046f7704cb233f54b8e21375fa6c9511b991e8f10fd3513342948f325456f4480d8314ff1dfed21bb6f8054a028a8086d93170781d26eda7e634c9594d1c131c413563b794c3d71ce50fabdb2135e0ac2e3626674048c16e0e56f8a959d967dec2e2f0f266f71645bf4ba62dd645afcb3'
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        echo "❌ CURL Error: $error\n";
        $errorCount++;
        $errors[] = "$parameter: CURL Error - $error";
    } elseif ($httpCode >= 200 && $httpCode < 300) {
        echo "✅ SUCCESS\n";
        $successCount++;
    } else {
        echo "❌ HTTP $httpCode\n";
        $errorCount++;
        $errors[] = "$parameter: HTTP $httpCode - " . substr($response, 0, 100);
        
        // Show response for debugging
        if ($response) {
            echo "   Response: " . substr($response, 0, 200) . "\n";
        }
    }
    
    // Small delay to avoid rate limiting
    usleep(100000); // 0.1 second delay
}

echo "\n📈 RESULTS SUMMARY\n";
echo "==================\n";
echo "✅ Successfully created: $successCount parameters\n";
echo "❌ Failed to create: $errorCount parameters\n";
echo "📊 Total processed: " . count($parameters) . " parameters\n\n";

if (!empty($errors)) {
    echo "❌ ERRORS ENCOUNTERED:\n";
    foreach ($errors as $error) {
        echo "   • $error\n";
    }
    echo "\n";
}

if ($successCount > 0) {
    echo "🎉 SUCCESS! $successCount parameters have been created in your RingBA account.\n";
    echo "   You can now use these parameters in your RingBA campaigns and tracking.\n\n";
}

if ($errorCount > 0) {
    echo "⚠️  Some parameters failed to create. Please check:\n";
    echo "   1. Your RingBA account ID is correct\n";
    echo "   2. You have proper API authentication (add API token if required)\n";
    echo "   3. Your RingBA account has permission to create parameters\n";
    echo "   4. The parameters don't already exist (some APIs reject duplicates)\n\n";
}

echo "📋 Next steps:\n";
echo "   1. Login to your RingBA dashboard\n";
echo "   2. Go to Settings > URL Parameters\n";
echo "   3. Verify the new parameters are listed\n";
echo "   4. Test with a sample campaign\n\n";

echo "🔗 For support, refer to RingBA API documentation:\n";
echo "   https://developers.ringba.com/\n\n";

echo "✅ Script completed!\n";

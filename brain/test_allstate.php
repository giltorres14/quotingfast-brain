<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "🚀 Testing Allstate API Connection with Corrected Test Credentials\n";
echo "=================================================================\n";

// Get the Tambara Farrell lead
$lead = App\Models\Lead::first();
if (!$lead) {
    echo "No lead found!\n";
    exit;
}

echo "Testing with Lead: {$lead->first_name} {$lead->last_name}\n";
echo "Lead Type: {$lead->type}\n";
echo "External ID: {$lead->external_lead_id}\n";
echo "Has drivers: " . (json_decode($lead->drivers, true) ? 'Yes' : 'No') . "\n";
echo "Has vehicles: " . (json_decode($lead->vehicles, true) ? 'Yes' : 'No') . "\n";
echo "Has current policy: " . ($lead->current_policy ? 'Yes' : 'No') . "\n\n";

// Initialize the enhanced service
$allstateService = new App\Services\AllstateCallTransferService();

// Test the API connection with corrected credentials
echo "--- TESTING ALLSTATE API CONNECTION ---\n";
try {
    $result1 = $allstateService->transferCall($lead, 'auto-insurance', []);
    echo "Success: " . ($result1['success'] ? 'YES' : 'NO') . "\n";
    
    if (!$result1['success']) {
        echo "Error: " . $result1['error'] . "\n";
        if (isset($result1['response_body'])) {
            echo "Response Body: " . $result1['response_body'] . "\n";
        }
    } else {
        echo "✅ Allstate API Connection Working!\n";
        if (isset($result1['allstate_response'])) {
            echo "Allstate Response: " . json_encode($result1['allstate_response'], JSON_PRETTY_PRINT) . "\n";
        }
    }
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}

echo "\n--- TEST 2: With Agent Qualification Data (Top 13 Questions) ---\n";

// Simulate agent qualification data (Top 13 Questions answered)
$qualificationData = [
    // Insurance Status
    'currently_insured' => true,
    'current_company' => 'GEICO',
    'policy_expires' => '2024-08-15',
    'current_premium' => 185.50,
    'shopping_for_rates' => true,
    
    // Coverage Needs
    'coverage_type' => 'auto',
    'desired_coverage_level' => 'FULL',
    'deductible_preference' => 500,
    'coverage_level' => 'COMPREHENSIVE',
    
    // Financial
    'credit_score' => 720,
    'home_ownership' => 'own',
    'education_level' => 'BACHELORS',
    'occupation' => 'MARKETING_MANAGER',
    
    // Driving
    'years_licensed' => 18,
    'accidents_violations' => false,
    'dui_conviction' => false,
    'sr22_required' => false,
    
    // Personal
    'date_of_birth' => '1985-03-15',
    'gender' => 'Female',
    'marital_status' => 'married',
    
    // Lead Quality
    'lead_quality_score' => 9,
    'motivation_level' => 8,
    'urgency' => '30_days',
    'best_time_to_call' => 'evenings',
    'agent_notes' => 'Very motivated, current policy expires soon, looking for savings'
];

try {
    $result2 = $allstateService->transferCall($lead, 'auto-insurance', $qualificationData);
    echo "Enhanced Transfer Result:\n";
    echo "Success: " . ($result2['success'] ? 'YES' : 'NO') . "\n";
    if (isset($result2['allstate_response'])) {
        echo "Allstate Response Code: " . ($result2['allstate_response']['status_code'] ?? 'N/A') . "\n";
    }
    if (isset($result2['transfer_data'])) {
        echo "Qualified by agent: " . ($result2['transfer_data']['qualified_by_agent'] ? 'YES' : 'NO') . "\n";
        echo "Lead quality score: " . ($result2['transfer_data']['lead_quality_score'] ?? 'N/A') . "\n";
        echo "Agent notes: " . ($result2['transfer_data']['agent_notes'] ?? 'None') . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n✅ Test completed!\n";
<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "🔥 Testing Allstate API Connection (as configured before)\n";
echo "========================================================\n";

// Test the /ping endpoint first (as we did before)
echo "1. Testing Allstate /ping endpoint...\n";

$testUrl = 'https://int.allstateleadmarketplace.com/v2/ping';
$testAuth = 'Basic cXVvdGluZy1mYXN0Og==';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $testUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([]));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: ' . $testAuth
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    echo "❌ CURL Error: $error\n";
} else {
    echo "✅ HTTP Code: $httpCode\n";
    echo "Response: $response\n";
}

echo "\n2. Testing AllstateCallTransferService with Tambara Farrell...\n";

// Get the lead
$lead = App\Models\Lead::first();
if (!$lead) {
    echo "❌ No lead found\n";
    exit;
}

echo "Lead: {$lead->first_name} {$lead->last_name}\n";

// Test the service
$service = new App\Services\AllstateCallTransferService();

// Check what environment it's configured for
echo "Service Environment: " . (app()->environment('production') ? 'PRODUCTION' : 'TESTING') . "\n";

try {
    $result = $service->transferCall($lead, 'auto-insurance', []);
    echo "Transfer Result:\n";
    print_r($result);
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

echo "\n✅ API Test completed!\n";
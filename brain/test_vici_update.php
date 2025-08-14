<?php

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Lead;
use Illuminate\Support\Facades\Log;

echo "🔍 Testing Vici Lead ID Update\n";
echo "================================\n\n";

// First, let's check if we have any leads with external_lead_id
$leadsWithExternalId = Lead::whereNotNull('external_lead_id')
    ->where('external_lead_id', '!=', '')
    ->limit(5)
    ->get();

echo "Sample leads with external_lead_id:\n";
foreach ($leadsWithExternalId as $lead) {
    echo "- Lead ID: {$lead->id} | External ID: {$lead->external_lead_id} | Phone: {$lead->phone}\n";
}

// Check leads without external_lead_id
$leadsWithoutExternalId = Lead::whereNull('external_lead_id')
    ->orWhere('external_lead_id', '')
    ->count();

echo "\nLeads without external_lead_id: {$leadsWithoutExternalId}\n";

// Test generating a 13-digit ID
echo "\n📝 Testing 13-digit ID generation:\n";
$testId = Lead::generateExternalLeadId();
echo "Generated ID: {$testId}\n";
echo "Length: " . strlen($testId) . " digits\n";

// Test the Vici connection through SSH
echo "\n🔌 Testing Vici SSH connection:\n";
echo "Note: Direct MySQL connection won't work from local environment.\n";
echo "We need to use SSH tunnel or run from deployed environment.\n";

// Check if we're in local or production
$env = config('app.env');
echo "\nCurrent environment: {$env}\n";

if ($env === 'local') {
    echo "\n⚠️  Local environment detected.\n";
    echo "To test Vici updates, you need to:\n";
    echo "1. Deploy to Render and run: php artisan vici:update-brain-ids --test\n";
    echo "2. Or use SSH tunnel: ssh -L 3306:localhost:3306 root@37.27.138.222\n";
    echo "3. Or use the Vici proxy endpoint we created\n";
} else {
    echo "\n✅ Production environment - Vici connection should work.\n";
    echo "Run: php artisan vici:update-brain-ids --test\n";
}

// Test with a specific phone number
echo "\n📱 Looking for a test lead to update:\n";
$testLead = Lead::where('source', 'LQF_BULK')
    ->whereNotNull('phone')
    ->first();

if ($testLead) {
    echo "Found test lead:\n";
    echo "- Name: {$testLead->name}\n";
    echo "- Phone: {$testLead->phone}\n";
    echo "- External ID: {$testLead->external_lead_id}\n";
    echo "- Source: {$testLead->source}\n";
    
    echo "\nTo test update for this specific lead:\n";
    echo "php artisan vici:update-brain-ids --phone={$testLead->phone}\n";
} else {
    echo "No test lead found.\n";
}

echo "\n✅ Test script complete.\n";

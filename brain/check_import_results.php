<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Lead;
use App\Models\Vendor;
use App\Models\Buyer;
use App\Models\Campaign;

// Check recent leads
$recentLeads = Lead::where('source', 'SURAJ_BULK')
    ->orderBy('created_at', 'desc')
    ->limit(3)
    ->get();

echo "=== RECENT IMPORTED LEADS ===\n";
foreach ($recentLeads as $lead) {
    echo "Lead: {$lead->name} | Phone: {$lead->phone} | Campaign: {$lead->campaign_id}\n";
    
    // Check if payload exists
    if ($lead->payload) {
        $payload = json_decode($lead->payload, true);
        if (isset($payload['vendor_name'])) {
            echo "  Vendor: {$payload['vendor_name']} (ID: " . ($payload['vendor_id'] ?? 'N/A') . ")\n";
        }
        if (isset($payload['buyer_name'])) {
            echo "  Buyer: {$payload['buyer_name']} (ID: " . ($payload['buyer_id'] ?? 'N/A') . ")\n";
        }
    }
}

// Check vendors created
echo "\n=== VENDORS (from Suraj) ===\n";
$vendors = Vendor::where('notes', 'LIKE', '%Suraj%')->get();
foreach ($vendors as $vendor) {
    echo "- {$vendor->name} | Leads: {$vendor->total_leads}\n";
}

// Check buyers created
echo "\n=== BUYERS (from Suraj) ===\n";
$buyers = Buyer::where('notes', 'LIKE', '%Suraj%')->get();
foreach ($buyers as $buyer) {
    echo "- {$buyer->name} | Leads: {$buyer->total_leads}\n";
}

// Check campaigns with buyers
echo "\n=== CAMPAIGNS WITH BUYERS ===\n";
$campaigns = Campaign::with('buyers')->where('description', 'LIKE', '%Suraj%')->limit(5)->get();
foreach ($campaigns as $campaign) {
    echo "Campaign #{$campaign->campaign_id}: ";
    $buyerNames = $campaign->buyers->pluck('name')->toArray();
    echo count($buyerNames) > 0 ? implode(', ', $buyerNames) : 'No buyers';
    echo "\n";
}

echo "\n=== SUMMARY ===\n";
echo "Total Suraj Leads: " . Lead::where('source', 'SURAJ_BULK')->count() . "\n";
echo "Unique Vendors: " . $vendors->count() . "\n";
echo "Unique Buyers: " . $buyers->count() . "\n";
echo "Campaigns Created: " . Campaign::where('description', 'LIKE', '%Suraj%')->count() . "\n";

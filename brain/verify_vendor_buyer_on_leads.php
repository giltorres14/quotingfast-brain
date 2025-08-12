<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Lead;

// Check leads with vendor/buyer data
$leadsWithVendor = Lead::where('source', 'SURAJ_BULK')
    ->whereNotNull('vendor_name')
    ->count();

$leadsWithBuyer = Lead::where('source', 'SURAJ_BULK')
    ->whereNotNull('buyer_name')
    ->count();

echo "=== VENDOR/BUYER DATA ON LEADS ===\n";
echo "Leads with vendor_name: $leadsWithVendor / 1426\n";
echo "Leads with buyer_name: $leadsWithBuyer / 1426\n\n";

// Show sample leads
echo "=== SAMPLE LEADS WITH VENDOR/BUYER ===\n";
$samples = Lead::where('source', 'SURAJ_BULK')
    ->whereNotNull('vendor_name')
    ->limit(5)
    ->get(['name', 'phone', 'vendor_name', 'buyer_name', 'campaign_id']);

foreach ($samples as $lead) {
    echo "Lead: {$lead->name}\n";
    echo "  Vendor: {$lead->vendor_name}\n";
    echo "  Buyer: {$lead->buyer_name}\n";
    echo "  Campaign: {$lead->campaign_id}\n\n";
}

// Check vendors created
echo "=== VENDORS CREATED ===\n";
$vendors = \DB::table('vendors')->select('name', 'total_leads')->get();
foreach ($vendors as $vendor) {
    echo "- {$vendor->name}: {$vendor->total_leads} leads\n";
}

// Check buyers created  
echo "\n=== BUYERS CREATED ===\n";
$buyers = \DB::table('buyers')->select('name', 'total_leads')->get();
foreach ($buyers as $buyer) {
    echo "- {$buyer->name}: {$buyer->total_leads} leads\n";
}

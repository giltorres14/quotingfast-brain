<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Lead;

echo "=== SAMPLE LEAD PHONE NUMBERS TO LOOK UP ===\n\n";

// Get leads with vendor/buyer info
$leadsWithInfo = Lead::where('source', 'SURAJ_BULK')
    ->whereNotNull('vendor_name')
    ->limit(3)
    ->get(['name', 'phone', 'vendor_name', 'buyer_name']);

foreach ($leadsWithInfo as $lead) {
    echo "📞 Phone: {$lead->phone}\n";
    echo "   Name: {$lead->name}\n";
    echo "   Vendor: {$lead->vendor_name}\n";
    echo "   Buyer: {$lead->buyer_name}\n\n";
}

// Get leads without vendor/buyer info
echo "--- Leads without vendor/buyer ---\n";
$leadsWithoutInfo = Lead::where('source', 'SURAJ_BULK')
    ->whereNull('vendor_name')
    ->limit(2)
    ->get(['name', 'phone']);

foreach ($leadsWithoutInfo as $lead) {
    echo "📞 Phone: {$lead->phone}\n";
    echo "   Name: {$lead->name}\n\n";
}

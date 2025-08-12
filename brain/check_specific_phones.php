<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Lead;
use App\Models\Vendor;
use App\Models\Buyer;

$phones = ['6828886054', '2563373296'];

foreach ($phones as $phone) {
    $lead = Lead::where('phone', $phone)->first();
    if ($lead) {
        echo "✅ Phone $phone found: {$lead->name}\n";
    } else {
        echo "❌ Phone $phone NOT imported\n";
    }
}

echo "\n=== VENDORS CREATED ===\n";
$vendors = \DB::table('vendors')->get();
foreach ($vendors as $vendor) {
    echo "- {$vendor->name} (Leads: {$vendor->total_leads})\n";
}

echo "\n=== BUYERS CREATED ===\n";
$buyers = \DB::table('buyers')->get();
foreach ($buyers as $buyer) {
    echo "- {$buyer->name} (Leads: {$buyer->total_leads})\n";
}

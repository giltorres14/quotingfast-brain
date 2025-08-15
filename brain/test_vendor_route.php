<?php
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Vendor;
use App\Models\Lead;

try {
    echo "Testing vendor route logic...\n\n";
    
    // Test vendor query
    $vendors = Vendor::orderBy('active', 'desc')
        ->orderBy('total_leads', 'desc')
        ->get();
    
    echo "✅ Vendors query successful\n";
    echo "Found " . $vendors->count() . " vendors\n\n";
    
    // Test stats
    $stats = [
        'total_vendors' => Vendor::count(),
        'active_vendors' => Vendor::where('active', true)->count(),
        'total_leads' => Lead::whereNotNull('vendor_name')->count(),
        'total_spent' => Lead::sum('cost') ?? 0
    ];
    
    echo "✅ Stats calculation successful\n";
    echo "Stats: " . json_encode($stats, JSON_PRETTY_PRINT) . "\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

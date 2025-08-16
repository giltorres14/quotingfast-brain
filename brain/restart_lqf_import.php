<?php
/**
 * Restart LQF import - delete corrupted leads and start fresh
 */

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "\n🔄 RESTART LQF IMPORT\n";
echo "======================\n\n";

// Get current stats
$total = DB::table('leads')->where('source', 'LQF_BULK')->count();
$corrupted = DB::select("
    SELECT COUNT(*) as count 
    FROM leads 
    WHERE source = 'LQF_BULK' 
    AND payload::text LIKE '%{\"{%'
")[0]->count;

echo "Current status:\n";
echo "- Total LQF_BULK leads: " . number_format($total) . "\n";
echo "- Corrupted leads: " . number_format($corrupted) . " (" . round($corrupted/$total*100, 1) . "%)\n";
echo "- Good leads: " . number_format($total - $corrupted) . " (" . round(($total-$corrupted)/$total*100, 1) . "%)\n\n";

echo "Since " . round($corrupted/$total*100, 1) . "% are corrupted, it's better to restart.\n\n";

echo "⚠️  This will DELETE all " . number_format($total) . " LQF_BULK leads and restart the import.\n";
echo "Type 'DELETE' to confirm: ";

$handle = fopen("php://stdin", "r");
$confirmation = trim(fgets($handle));
fclose($handle);

if ($confirmation !== 'DELETE') {
    echo "\n❌ Cancelled. No changes made.\n";
    exit(0);
}

echo "\nDeleting LQF_BULK leads...\n";

// Delete in batches to avoid timeout
$deleted = 0;
while (true) {
    $count = DB::table('leads')
        ->where('source', 'LQF_BULK')
        ->limit(1000)
        ->delete();
    
    if ($count === 0) {
        break;
    }
    
    $deleted += $count;
    echo "  Deleted: " . number_format($deleted) . " leads...\n";
}

echo "\n✅ Deleted " . number_format($deleted) . " LQF_BULK leads\n\n";

// Now run the original import
$csvFile = '/Users/giltorres/Downloads/1755044818-webleads_export_2025-05-01_-_2025-08-12.csv';

if (!file_exists($csvFile)) {
    echo "❌ CSV file not found: $csvFile\n";
    exit(1);
}

echo "Starting fresh import with the WORKING import command...\n";
echo "This will take several hours but the data will be correct.\n\n";

echo "Run this command:\n";
echo "php artisan lqf:bulk-import $csvFile\n\n";

echo "The import will:\n";
echo "- Import ~149,548 leads\n";
echo "- Process at ~45-60 leads per minute\n";
echo "- Take approximately 40-50 hours\n";
echo "- Store all data correctly\n\n";

echo "You can run it in the background with:\n";
echo "nohup php artisan lqf:bulk-import $csvFile > lqf_import.log 2>&1 &\n";



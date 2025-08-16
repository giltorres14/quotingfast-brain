<?php
/**
 * Clear all Vici data that came from the API user method
 * This prevents duplicates when switching to the new export script method
 */

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\ViciCallMetrics;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

echo "\n========================================\n";
echo "   VICI API DATA CLEANUP SCRIPT\n";
echo "========================================\n\n";

// Get current counts
$viciMetricsCount = ViciCallMetrics::count();
$orphanCallsCount = DB::table('orphan_call_logs')->count();

echo "Current data from API user method:\n";
echo "- ViciCallMetrics: " . number_format($viciMetricsCount) . " records\n";
echo "- OrphanCallLogs: " . number_format($orphanCallsCount) . " records\n";
echo "- Total: " . number_format($viciMetricsCount + $orphanCallsCount) . " records\n\n";

if ($viciMetricsCount == 0 && $orphanCallsCount == 0) {
    echo "✅ No data to clear. Database is already clean.\n\n";
    exit(0);
}

// Show sample of data that will be deleted
echo "Sample of ViciCallMetrics that will be deleted:\n";
$samples = ViciCallMetrics::limit(5)->get(['id', 'lead_id', 'phone_number', 'status', 'created_at']);
foreach ($samples as $sample) {
    echo "  - ID: {$sample->id}, Lead: {$sample->lead_id}, Phone: {$sample->phone_number}, Status: {$sample->status}, Created: {$sample->created_at}\n";
}
echo "\n";

// Backup data before deletion
echo "Creating backup before deletion...\n";

// Backup ViciCallMetrics
$backupFile = 'storage/backups/vici_api_data_backup_' . date('Y-m-d_H-i-s') . '.json';
$backupDir = dirname($backupFile);
if (!file_exists($backupDir)) {
    mkdir($backupDir, 0755, true);
}

$backupData = [
    'backup_date' => Carbon::now()->toIso8601String(),
    'source' => 'API User Method',
    'vici_call_metrics' => ViciCallMetrics::all()->toArray(),
    'orphan_call_logs' => DB::table('orphan_call_logs')->get()->toArray(),
    'counts' => [
        'vici_call_metrics' => $viciMetricsCount,
        'orphan_call_logs' => $orphanCallsCount
    ]
];

file_put_contents($backupFile, json_encode($backupData, JSON_PRETTY_PRINT));
echo "✅ Backup saved to: {$backupFile}\n";
echo "   Backup size: " . number_format(filesize($backupFile) / 1024, 2) . " KB\n\n";

// Confirm deletion
echo "⚠️  WARNING: This will delete ALL Vici data from the API user method.\n";
echo "   This data has been backed up and can be restored if needed.\n";
echo "   The new export script will provide much more detailed data.\n\n";
echo "Type 'DELETE' to confirm deletion: ";

$handle = fopen("php://stdin", "r");
$confirmation = trim(fgets($handle));
fclose($handle);

if ($confirmation !== 'DELETE') {
    echo "\n❌ Deletion cancelled. No data was removed.\n";
    echo "   Backup file remains at: {$backupFile}\n\n";
    exit(0);
}

echo "\nDeleting data...\n";

try {
    // Start transaction
    DB::beginTransaction();
    
    // Delete ViciCallMetrics
    $deletedMetrics = ViciCallMetrics::count();
    ViciCallMetrics::truncate();
    echo "✅ Deleted {$deletedMetrics} ViciCallMetrics records\n";
    
    // Delete OrphanCallLogs
    $deletedOrphans = DB::table('orphan_call_logs')->count();
    DB::table('orphan_call_logs')->truncate();
    echo "✅ Deleted {$deletedOrphans} OrphanCallLogs records\n";
    
    // Commit transaction
    DB::commit();
    
    echo "\n========================================\n";
    echo "✅ CLEANUP COMPLETE!\n";
    echo "========================================\n\n";
    echo "Summary:\n";
    echo "- Deleted: " . number_format($deletedMetrics + $deletedOrphans) . " total records\n";
    echo "- Backup saved: {$backupFile}\n";
    echo "- Database ready for new export script data\n\n";
    echo "Next steps:\n";
    echo "1. Wait for Vici support to whitelist Render IPs\n";
    echo "2. The export script will run automatically every 5 minutes\n";
    echo "3. Check logs at: storage/logs/vici_export.log\n\n";
    
} catch (\Exception $e) {
    DB::rollBack();
    echo "\n❌ Error during deletion: " . $e->getMessage() . "\n";
    echo "   Transaction rolled back. No data was deleted.\n";
    echo "   Backup file remains at: {$backupFile}\n\n";
    exit(1);
}

echo "To restore this data later, run:\n";
echo "php artisan tinker --execute=\"\\\$data = json_decode(file_get_contents('{$backupFile}'), true); foreach(\\\$data['vici_call_metrics'] as \\\$record) { \\App\\Models\\ViciCallMetrics::create(\\\$record); }\"\n\n";



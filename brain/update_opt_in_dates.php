<?php

// Connect to PostgreSQL
$pdo = new PDO(
    'pgsql:host=dpg-d277kvk9c44c7388opg0-a.ohio-postgres.render.com;port=5432;dbname=brain_production',
    'brain_user',
    'KoK8TYX26PShPKl8LISdhHOQsCrnzcCQ'
);

echo "=================================\n";
echo "UPDATING OPT-IN DATES FOR LEADS\n";
echo "=================================\n\n";

// First, check if opt_in_date column exists
$checkColumn = $pdo->query("
    SELECT column_name 
    FROM information_schema.columns 
    WHERE table_name = 'leads' 
    AND column_name = 'opt_in_date'
");

if (!$checkColumn->fetch()) {
    echo "Adding opt_in_date column to leads table...\n";
    $pdo->exec("ALTER TABLE leads ADD COLUMN IF NOT EXISTS opt_in_date TIMESTAMP NULL");
    echo "✅ Column added\n\n";
} else {
    echo "✅ opt_in_date column already exists\n\n";
}

// Get all leads with payload
$stmt = $pdo->query("
    SELECT id, payload, source, created_at 
    FROM leads 
    WHERE payload IS NOT NULL 
    AND opt_in_date IS NULL
    LIMIT 1000
");

$leads = $stmt->fetchAll(PDO::FETCH_ASSOC);
$totalLeads = count($leads);
$updated = 0;
$failed = 0;

echo "Found {$totalLeads} leads to process\n\n";

foreach ($leads as $lead) {
    $optInDate = null;
    
    // Decode payload - handle double-encoded JSON
    $payload = $lead['payload'];
    
    // First decode
    if (is_string($payload)) {
        $payload = json_decode($payload, true);
    }
    
    // Check if it's still a string (double-encoded)
    if (is_string($payload)) {
        $payload = json_decode($payload, true);
    }
    
    if (!$payload) {
        continue;
    }
    
    // For LQF leads - check originally_created
    if (isset($payload['originally_created'])) {
        try {
            // Parse the date - format: "2025-08-13T02:15:07Z"
            $date = new DateTime($payload['originally_created']);
            $optInDate = $date->format('Y-m-d H:i:s');
        } catch (Exception $e) {
            // Try as-is
            $optInDate = $payload['originally_created'];
        }
    }
    // For Suraj leads - check timestamp (Column B)
    elseif (isset($payload['timestamp'])) {
        try {
            // Parse the date - format might be "2025-06-09T16:19:03.573Z"
            $date = new DateTime($payload['timestamp']);
            $optInDate = $date->format('Y-m-d H:i:s');
        } catch (Exception $e) {
            echo "Failed to parse timestamp for lead {$lead['id']}: {$payload['timestamp']}\n";
        }
    }
    
    if ($optInDate) {
        try {
            $updateStmt = $pdo->prepare("UPDATE leads SET opt_in_date = ? WHERE id = ?");
            $updateStmt->execute([$optInDate, $lead['id']]);
            $updated++;
            
            if ($updated % 100 == 0) {
                echo "Progress: {$updated}/{$totalLeads} updated\n";
            }
        } catch (Exception $e) {
            $failed++;
            echo "Failed to update lead {$lead['id']}: " . $e->getMessage() . "\n";
        }
    }
}

echo "\n=================================\n";
echo "UPDATE COMPLETE\n";
echo "=================================\n";
echo "✅ Updated: {$updated} leads\n";
echo "❌ Failed: {$failed} leads\n";
echo "⏭️  Skipped: " . ($totalLeads - $updated - $failed) . " leads (no opt-in date found)\n";

// Show some examples
echo "\n📊 Sample of updated leads:\n";
$samples = $pdo->query("
    SELECT id, name, source, opt_in_date, created_at 
    FROM leads 
    WHERE opt_in_date IS NOT NULL 
    ORDER BY id DESC 
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

foreach ($samples as $sample) {
    $optIn = new DateTime($sample['opt_in_date']);
    $created = new DateTime($sample['created_at']);
    $daysDiff = $optIn->diff($created)->days;
    
    echo sprintf(
        "  Lead #%d (%s): Opt-in: %s | Created: %s | Diff: %d days\n",
        $sample['id'],
        $sample['source'],
        $optIn->format('m/d/Y'),
        $created->format('m/d/Y'),
        $daysDiff
    );
}

// Check for leads older than 90 days
echo "\n⚠️  Checking for leads older than 90 days from opt-in:\n";
$oldLeads = $pdo->query("
    SELECT COUNT(*) as count 
    FROM leads 
    WHERE opt_in_date IS NOT NULL 
    AND opt_in_date < NOW() - INTERVAL '90 days'
")->fetch(PDO::FETCH_ASSOC);

echo "Found {$oldLeads['count']} leads older than 90 days from opt-in date\n";

if ($oldLeads['count'] > 0) {
    echo "\nThese leads should be archived according to TCPA compliance rules.\n";
}

#!/usr/bin/env php
<?php
/**
 * ViciDial Dashboard CLI - Direct Data Access
 * No more UI issues, just raw data
 */

require_once __DIR__ . '/vendor/autoload.php';

// Load environment
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Database connection
$host = 'dpg-d277kvk9c44c7388opg0-a.ohio-postgres.render.com';
$port = '5432';
$database = 'brain_production';
$username = 'brain_user';
$password = 'KoK8TYX26PShPKl8LISdhHOQsCrnzcCQ';

try {
    $pdo = new PDO(
        "pgsql:host=$host;port=$port;dbname=$database",
        $username,
        $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    echo "\n";
    echo "╔══════════════════════════════════════════════════════════════╗\n";
    echo "║           VICIDIAL DASHBOARD - COMMAND LINE VERSION          ║\n";
    echo "╚══════════════════════════════════════════════════════════════╝\n";
    echo "\n";
    echo "📅 " . date('l, F j, Y - g:i A') . " EST\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
    
    // Overall Stats
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM leads");
    $total = $stmt->fetchColumn();
    
    $stmt = $pdo->query("SELECT COUNT(*) as today FROM leads WHERE DATE(created_at) = CURRENT_DATE");
    $today = $stmt->fetchColumn();
    
    $stmt = $pdo->query("SELECT COUNT(*) as conversions FROM leads WHERE vici_status IN ('SALE', 'TRANSFERRED')");
    $conversions = $stmt->fetchColumn();
    
    echo "📊 OVERALL STATISTICS\n";
    echo "├─ Total Leads: " . number_format($total) . "\n";
    echo "├─ Today's Leads: " . number_format($today) . "\n";
    echo "├─ Total Conversions: " . number_format($conversions) . "\n";
    echo "└─ Overall Conversion Rate: " . ($total > 0 ? round(($conversions / $total) * 100, 2) : 0) . "%\n\n";
    
    // A/B Test Results
    echo "🔬 A/B TEST RESULTS\n";
    echo "┌─────────────────────────────────────────────────────────────┐\n";
    
    // Test A
    $stmt = $pdo->query("
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN vici_status IN ('SALE', 'TRANSFERRED') THEN 1 ELSE 0 END) as conv
        FROM leads 
        WHERE vici_list_id::text LIKE '10%' OR vici_list_id::text LIKE '11%'
    ");
    $testA = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "│ TEST A (Full Persistence - 48 Calls)                       │\n";
    echo "│ Lists: 101-111                                             │\n";
    echo "│ • Total Leads: " . str_pad(number_format($testA['total'] ?? 0), 10) . "                                  │\n";
    echo "│ • Conversions: " . str_pad(number_format($testA['conv'] ?? 0), 10) . "                                  │\n";
    echo "│ • Conv Rate: " . str_pad(($testA['total'] > 0 ? round(($testA['conv'] / $testA['total']) * 100, 2) : 0) . "%", 10) . "                                    │\n";
    echo "├─────────────────────────────────────────────────────────────┤\n";
    
    // Test B
    $stmt = $pdo->query("
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN vici_status IN ('SALE', 'TRANSFERRED') THEN 1 ELSE 0 END) as conv
        FROM leads 
        WHERE vici_list_id::text LIKE '15%'
    ");
    $testB = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "│ TEST B (Smart Timing - 12-18 Calls)                        │\n";
    echo "│ Lists: 150-153                                             │\n";
    echo "│ • Total Leads: " . str_pad(number_format($testB['total'] ?? 0), 10) . "                                  │\n";
    echo "│ • Conversions: " . str_pad(number_format($testB['conv'] ?? 0), 10) . "                                  │\n";
    echo "│ • Conv Rate: " . str_pad(($testB['total'] > 0 ? round(($testB['conv'] / $testB['total']) * 100, 2) : 0) . "%", 10) . "                                    │\n";
    echo "└─────────────────────────────────────────────────────────────┘\n\n";
    
    // Top Dispositions
    echo "📞 TOP DISPOSITIONS (LAST 7 DAYS)\n";
    $stmt = $pdo->query("
        SELECT 
            vici_status,
            COUNT(*) as count
        FROM leads
        WHERE vici_status IS NOT NULL
            AND created_at >= CURRENT_DATE - INTERVAL '7 days'
        GROUP BY vici_status
        ORDER BY count DESC
        LIMIT 5
    ");
    
    $dispositions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($dispositions as $i => $disp) {
        $prefix = ($i == count($dispositions) - 1) ? "└─" : "├─";
        echo $prefix . " " . str_pad($disp['vici_status'] ?? 'Unknown', 15) . ": " . number_format($disp['count']) . "\n";
    }
    
    echo "\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "💡 TIP: Run this script anytime to get real-time ViciDial stats\n";
    echo "   No more dealing with broken UI pages!\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
    
} catch (Exception $e) {
    echo "❌ Database Error: " . $e->getMessage() . "\n\n";
    echo "Trying alternative connection...\n";
}







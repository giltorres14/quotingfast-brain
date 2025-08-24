#!/usr/bin/env php
<?php
/**
 * ViciDial Stats - Working Dashboard
 * Uses actual database structure
 */

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
    echo "═══════════════════════════════════════════════════════════════\n";
    echo "                    VICIDIAL STATS DASHBOARD                   \n";
    echo "═══════════════════════════════════════════════════════════════\n";
    echo date('l, F j, Y - g:i A') . " EST\n";
    echo "───────────────────────────────────────────────────────────────\n\n";
    
    // Get total leads
    $stmt = $pdo->query("SELECT COUNT(*) FROM leads");
    $totalLeads = $stmt->fetchColumn();
    
    // Get today's leads
    $stmt = $pdo->query("SELECT COUNT(*) FROM leads WHERE DATE(created_at) = CURRENT_DATE");
    $todayLeads = $stmt->fetchColumn();
    
    // Get leads with vici_list_id
    $stmt = $pdo->query("SELECT COUNT(*) FROM leads WHERE vici_list_id IS NOT NULL");
    $viciLeads = $stmt->fetchColumn();
    
    echo "📊 LEAD STATISTICS\n";
    echo "  Total Leads: " . number_format($totalLeads) . "\n";
    echo "  Today's Leads: " . number_format($todayLeads) . "\n";
    echo "  Leads in ViciDial: " . number_format($viciLeads) . "\n\n";
    
    // Check vici_call_logs
    $stmt = $pdo->query("SELECT COUNT(*) FROM vici_call_logs");
    $callLogs = $stmt->fetchColumn();
    
    echo "📞 CALL ACTIVITY\n";
    echo "  Total Call Logs: " . number_format($callLogs) . "\n";
    
    // Get recent call activity
    $stmt = $pdo->query("
        SELECT COUNT(*) 
        FROM vici_call_logs 
        WHERE created_at >= CURRENT_DATE - INTERVAL '7 days'
    ");
    $recentCalls = $stmt->fetchColumn();
    echo "  Calls (Last 7 Days): " . number_format($recentCalls) . "\n\n";
    
    // List breakdown
    echo "📋 LEADS BY LIST\n";
    $stmt = $pdo->query("
        SELECT 
            vici_list_id,
            COUNT(*) as count
        FROM leads
        WHERE vici_list_id IS NOT NULL
        GROUP BY vici_list_id
        ORDER BY vici_list_id
        LIMIT 10
    ");
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "  List " . str_pad($row['vici_list_id'], 4) . ": " . 
             str_pad(number_format($row['count']), 8, ' ', STR_PAD_LEFT) . " leads\n";
    }
    
    echo "\n";
    
    // Check vici_call_metrics
    $stmt = $pdo->query("
        SELECT column_name 
        FROM information_schema.columns 
        WHERE table_name = 'vici_call_metrics' 
        ORDER BY ordinal_position
    ");
    $metricsColumns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (count($metricsColumns) > 0) {
        echo "📈 CALL METRICS TABLE\n";
        echo "  Available columns: " . implode(', ', array_slice($metricsColumns, 0, 5)) . "...\n";
        
        $stmt = $pdo->query("SELECT COUNT(*) FROM vici_call_metrics");
        $metricsCount = $stmt->fetchColumn();
        echo "  Total records: " . number_format($metricsCount) . "\n\n";
    }
    
    // Status breakdown
    echo "📊 LEAD STATUS BREAKDOWN\n";
    $stmt = $pdo->query("
        SELECT 
            COALESCE(status, 'No Status') as status,
            COUNT(*) as count
        FROM leads
        GROUP BY status
        ORDER BY count DESC
        LIMIT 5
    ");
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "  " . str_pad($row['status'], 15) . ": " . 
             str_pad(number_format($row['count']), 8, ' ', STR_PAD_LEFT) . "\n";
    }
    
    echo "\n───────────────────────────────────────────────────────────────\n";
    echo "✅ Stats generated successfully!\n";
    echo "This is your working dashboard - no more UI issues!\n\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}







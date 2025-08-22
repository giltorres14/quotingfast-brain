#!/usr/bin/env php
<?php
/**
 * Direct ViciDial Database Access Script
 * Bypasses all the broken UI issues and gives you direct data
 */

require_once __DIR__ . '/vendor/autoload.php';

// Load environment
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Database connection
$host = $_ENV['DB_HOST'] ?? 'dpg-d277kvk9c44c7388opg0-a.ohio-postgres.render.com';
$port = $_ENV['DB_PORT'] ?? '5432';
$database = $_ENV['DB_DATABASE'] ?? 'brain_production';
$username = $_ENV['DB_USERNAME'] ?? 'brain_user';
$password = $_ENV['DB_PASSWORD'] ?? 'KoK8TYX26PShPKl8LISdhHOQsCrnzcCQ';

try {
    $pdo = new PDO(
        "pgsql:host=$host;port=$port;dbname=$database",
        $username,
        $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    echo "\n=== VICIDIAL DIRECT DATA ACCESS ===\n";
    echo "Time: " . date('Y-m-d H:i:s') . " EST\n\n";
    
    // Get lead counts by list
    echo "📊 LEAD COUNTS BY LIST:\n";
    echo str_repeat("-", 50) . "\n";
    
    $stmt = $pdo->query("
        SELECT 
            COALESCE(vici_list_id, 'Unknown') as list_id,
            COUNT(*) as total_leads,
            SUM(CASE WHEN vici_status IN ('SALE', 'TRANSFERRED') THEN 1 ELSE 0 END) as conversions,
            ROUND(100.0 * SUM(CASE WHEN vici_status IN ('SALE', 'TRANSFERRED') THEN 1 ELSE 0 END) / NULLIF(COUNT(*), 0), 2) as conversion_rate
        FROM leads
        WHERE vici_list_id IS NOT NULL
        GROUP BY vici_list_id
        ORDER BY vici_list_id
    ");
    
    printf("%-15s %-15s %-15s %-15s\n", "List ID", "Total Leads", "Conversions", "Conv Rate %");
    echo str_repeat("-", 50) . "\n";
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        printf("%-15s %-15s %-15s %-15s\n", 
            $row['list_id'], 
            $row['total_leads'], 
            $row['conversions'], 
            $row['conversion_rate'] . '%'
        );
    }
    
    // Get disposition breakdown
    echo "\n📞 DISPOSITION BREAKDOWN:\n";
    echo str_repeat("-", 50) . "\n";
    
    $stmt = $pdo->query("
        SELECT 
            COALESCE(vici_status, 'Unknown') as disposition,
            COUNT(*) as count,
            ROUND(100.0 * COUNT(*) / SUM(COUNT(*)) OVER(), 2) as percentage
        FROM leads
        WHERE vici_status IS NOT NULL
        GROUP BY vici_status
        ORDER BY count DESC
        LIMIT 10
    ");
    
    printf("%-20s %-10s %-10s\n", "Disposition", "Count", "Percentage");
    echo str_repeat("-", 50) . "\n";
    
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        printf("%-20s %-10s %-10s\n", 
            $row['disposition'], 
            $row['count'], 
            $row['percentage'] . '%'
        );
    }
    
    // Get today's activity
    echo "\n📈 TODAY'S ACTIVITY:\n";
    echo str_repeat("-", 50) . "\n";
    
    $stmt = $pdo->query("
        SELECT 
            COUNT(*) as total_calls,
            SUM(CASE WHEN vici_status IN ('SALE', 'TRANSFERRED') THEN 1 ELSE 0 END) as conversions,
            SUM(CASE WHEN vici_status = 'AM' THEN 1 ELSE 0 END) as answering_machines,
            SUM(CASE WHEN vici_status = 'NA' THEN 1 ELSE 0 END) as no_answers
        FROM leads
        WHERE DATE(vici_last_call_time) = CURRENT_DATE
    ");
    
    $today = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "Total Calls Today: " . ($today['total_calls'] ?? 0) . "\n";
    echo "Conversions: " . ($today['conversions'] ?? 0) . "\n";
    echo "Answering Machines: " . ($today['answering_machines'] ?? 0) . "\n";
    echo "No Answers: " . ($today['no_answers'] ?? 0) . "\n";
    
    // A/B Test Performance
    echo "\n🔬 A/B TEST PERFORMANCE:\n";
    echo str_repeat("-", 50) . "\n";
    
    echo "TEST A (Lists 101-111) - 48 Call Persistence:\n";
    $stmt = $pdo->query("
        SELECT 
            COUNT(DISTINCT phone_number) as unique_leads,
            SUM(CASE WHEN vici_status IN ('SALE', 'TRANSFERRED') THEN 1 ELSE 0 END) as conversions,
            ROUND(AVG(CAST(vici_call_count AS NUMERIC)), 2) as avg_calls_per_lead
        FROM leads
        WHERE vici_list_id IN ('101', '102', '103', '104', '105', '106', '107', '108', '109', '110', '111')
    ");
    $testA = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "  Unique Leads: " . ($testA['unique_leads'] ?? 0) . "\n";
    echo "  Conversions: " . ($testA['conversions'] ?? 0) . "\n";
    echo "  Avg Calls/Lead: " . ($testA['avg_calls_per_lead'] ?? 0) . "\n";
    
    echo "\nTEST B (Lists 150-153) - 12-18 Call Optimized:\n";
    $stmt = $pdo->query("
        SELECT 
            COUNT(DISTINCT phone_number) as unique_leads,
            SUM(CASE WHEN vici_status IN ('SALE', 'TRANSFERRED') THEN 1 ELSE 0 END) as conversions,
            ROUND(AVG(CAST(vici_call_count AS NUMERIC)), 2) as avg_calls_per_lead
        FROM leads
        WHERE vici_list_id IN ('150', '151', '152', '153')
    ");
    $testB = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "  Unique Leads: " . ($testB['unique_leads'] ?? 0) . "\n";
    echo "  Conversions: " . ($testB['conversions'] ?? 0) . "\n";
    echo "  Avg Calls/Lead: " . ($testB['avg_calls_per_lead'] ?? 0) . "\n";
    
    echo "\n✅ Direct database access successful!\n";
    echo "This bypasses all the broken UI issues.\n\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}


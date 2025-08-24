#!/usr/bin/env php
<?php

try {
    $pdo = new PDO(
        'pgsql:host=dpg-d277kvk9c44c7388opg0-a.ohio-postgres.render.com;port=5432;dbname=brain_production',
        'brain_user',
        'KoK8TYX26PShPKl8LISdhHOQsCrnzcCQ'
    );
    
    echo "\n📊 LEAD STATISTICS:\n";
    echo "=" . str_repeat("=", 50) . "\n\n";
    
    // Overall stats
    $stmt = $pdo->query("
        SELECT 
            COUNT(*) as total_leads,
            COUNT(CASE WHEN source = 'leadsquotingfast' THEN 1 END) as lqf_leads,
            COUNT(CASE WHEN source = 'LQF Bulk' THEN 1 END) as lqf_bulk_leads,
            COUNT(CASE WHEN created_at > NOW() - INTERVAL '1 hour' THEN 1 END) as last_hour,
            COUNT(CASE WHEN created_at > NOW() - INTERVAL '24 hours' THEN 1 END) as last_24h,
            COUNT(CASE WHEN created_at > NOW() - INTERVAL '7 days' THEN 1 END) as last_7days
        FROM leads
    ");
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "Total Leads: " . number_format($stats['total_leads']) . "\n";
    echo "LQF Webhook Leads: " . number_format($stats['lqf_leads']) . "\n";
    echo "LQF Bulk Import: " . number_format($stats['lqf_bulk_leads']) . "\n";
    echo "Last Hour: " . number_format($stats['last_hour']) . "\n";
    echo "Last 24 Hours: " . number_format($stats['last_24h']) . "\n";
    echo "Last 7 Days: " . number_format($stats['last_7days']) . "\n\n";
    
    // Check recent leads by source
    echo "📈 RECENT LEADS BY SOURCE (Last 24 hours):\n";
    echo "-" . str_repeat("-", 50) . "\n";
    
    $stmt = $pdo->query("
        SELECT 
            source,
            COUNT(*) as count,
            MAX(created_at) as latest
        FROM leads
        WHERE created_at > NOW() - INTERVAL '24 hours'
        GROUP BY source
        ORDER BY count DESC
    ");
    
    $sources = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (empty($sources)) {
        echo "No leads in the last 24 hours\n";
    } else {
        foreach ($sources as $source) {
            echo sprintf("%-30s: %5d leads (latest: %s)\n", 
                $source['source'] ?: 'Unknown',
                $source['count'],
                $source['latest']
            );
        }
    }
    
    // Check the most recent LQF leads
    echo "\n📝 LAST 5 LQF WEBHOOK LEADS:\n";
    echo "-" . str_repeat("-", 50) . "\n";
    
    $stmt = $pdo->query("
        SELECT 
            id,
            external_lead_id,
            name,
            phone,
            created_at,
            type
        FROM leads
        WHERE source = 'leadsquotingfast'
        ORDER BY created_at DESC
        LIMIT 5
    ");
    
    $recent = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (empty($recent)) {
        echo "No LQF webhook leads found\n";
    } else {
        foreach ($recent as $lead) {
            echo sprintf("ID: %s | External: %s | %s | %s | Type: %s | %s\n",
                $lead['id'],
                $lead['external_lead_id'],
                $lead['name'],
                $lead['phone'],
                $lead['type'] ?: 'unknown',
                $lead['created_at']
            );
        }
    }
    
    // Check webhook activity in meta field
    echo "\n🔍 CHECKING FOR WEBHOOK DEBUG MARKERS:\n";
    echo "-" . str_repeat("-", 50) . "\n";
    
    $stmt = $pdo->query("
        SELECT 
            COUNT(*) as debug_count
        FROM leads
        WHERE meta LIKE '%DEBUG_WEBHOOK%'
    ");
    $debug = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Leads with webhook debug markers: " . $debug['debug_count'] . "\n";
    
    // Check for home vs auto leads
    echo "\n🏠🚗 LEAD TYPES (Last 7 days):\n";
    echo "-" . str_repeat("-", 50) . "\n";
    
    $stmt = $pdo->query("
        SELECT 
            type,
            COUNT(*) as count
        FROM leads
        WHERE created_at > NOW() - INTERVAL '7 days'
        GROUP BY type
        ORDER BY count DESC
    ");
    
    $types = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($types as $type) {
        echo sprintf("%-20s: %5d leads\n", 
            $type['type'] ?: 'Not specified',
            $type['count']
        );
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n";







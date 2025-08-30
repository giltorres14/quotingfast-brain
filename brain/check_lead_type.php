#!/usr/bin/env php
<?php

try {
    $pdo = new PDO(
        'pgsql:host=dpg-d277kvk9c44c7388opg0-a.ohio-postgres.render.com;port=5432;dbname=brain_production',
        'brain_user',
        'KoK8TYX26PShPKl8LISdhHOQsCrnzcCQ'
    );
    
    // Check lead 491471
    $stmt = $pdo->prepare("SELECT id, external_lead_id, name, type, source FROM leads WHERE id = 491471 OR external_lead_id = '1755813073001'");
    $stmt->execute();
    $lead = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($lead) {
        echo "Lead found:\n";
        echo "ID: " . $lead['id'] . "\n";
        echo "External ID: " . $lead['external_lead_id'] . "\n";
        echo "Name: " . $lead['name'] . "\n";
        echo "Type: '" . $lead['type'] . "' (length: " . strlen($lead['type']) . ")\n";
        echo "Source: " . $lead['source'] . "\n";
        
        // Check if type is null or empty
        if ($lead['type'] === null) {
            echo "Type is NULL\n";
        } elseif ($lead['type'] === '') {
            echo "Type is empty string\n";
        }
    } else {
        echo "Lead not found\n";
    }
    
    // Check how many leads have proper types
    $stmt = $pdo->query("SELECT type, COUNT(*) as count FROM leads WHERE source = 'leadsquotingfast' GROUP BY type ORDER BY count DESC LIMIT 10");
    $types = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "\n\nLead types from LQF webhook:\n";
    foreach ($types as $type) {
        $typeDisplay = $type['type'] ?: 'NULL/Empty';
        echo "Type: '$typeDisplay' - Count: " . $type['count'] . "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}










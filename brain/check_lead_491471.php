#!/usr/bin/env php
<?php

try {
    $pdo = new PDO(
        'pgsql:host=dpg-d277kvk9c44c7388opg0-a.ohio-postgres.render.com;port=5432;dbname=brain_production',
        'brain_user',
        'KoK8TYX26PShPKl8LISdhHOQsCrnzcCQ'
    );
    
    echo "\n📊 CHECKING LEAD 491471:\n";
    echo "=" . str_repeat("=", 50) . "\n\n";
    
    $stmt = $pdo->prepare("SELECT id, external_lead_id, name, type, source, drivers, vehicles FROM leads WHERE id = 491471 OR external_lead_id = '491471'");
    $stmt->execute();
    $lead = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($lead) {
        echo "ID: " . $lead['id'] . "\n";
        echo "External ID: " . $lead['external_lead_id'] . "\n";
        echo "Name: " . $lead['name'] . "\n";
        echo "Type: " . ($lead['type'] ?: 'NULL/EMPTY') . "\n";
        echo "Source: " . $lead['source'] . "\n";
        
        echo "\nDrivers data: ";
        if ($lead['drivers']) {
            $drivers = json_decode($lead['drivers'], true);
            if ($drivers) {
                echo count($drivers) . " driver(s) found\n";
                print_r($drivers);
            } else {
                echo "Invalid JSON or empty\n";
            }
        } else {
            echo "NULL\n";
        }
        
        echo "\nVehicles data: ";
        if ($lead['vehicles']) {
            $vehicles = json_decode($lead['vehicles'], true);
            if ($vehicles) {
                echo count($vehicles) . " vehicle(s) found\n";
                print_r($vehicles);
            } else {
                echo "Invalid JSON or empty\n";
            }
        } else {
            echo "NULL\n";
        }
    } else {
        echo "Lead not found\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n";









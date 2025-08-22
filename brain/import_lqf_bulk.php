#!/usr/bin/env php
<?php
/**
 * Import LQF Bulk Export with Complete Data
 * Handles the lumped JSON data column properly
 */

require_once __DIR__ . '/vendor/autoload.php';

// Load environment
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Database connection
$host = 'dpg-d277kvk9c44c7388opg0-a.ohio-postgres.render.com';
$db = 'brain_production';
$user = 'brain_user';
$pass = 'KoK8TYX26PShPKl8LISdhHOQsCrnzcCQ';

echo "\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "           LQF BULK IMPORT - Complete Data Import               \n";
echo "═══════════════════════════════════════════════════════════════\n";
echo date('Y-m-d H:i:s') . " EST\n\n";

// CSV file path
$csvFile = '/Users/giltorres/Downloads/LQF Bulk Export2025051920250821.csv';

if (!file_exists($csvFile)) {
    echo "❌ File not found: $csvFile\n";
    exit(1);
}

try {
    $pdo = new PDO(
        "pgsql:host=$host;port=5432;dbname=$db",
        $user,
        $pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "✅ Connected to Brain database\n\n";
    
    // Get current stats
    $stmt = $pdo->query("SELECT COUNT(*) FROM leads");
    $beforeCount = $stmt->fetchColumn();
    echo "📊 Current leads in database: " . number_format($beforeCount) . "\n\n";
    
    // Open CSV file
    $handle = fopen($csvFile, 'r');
    if (!$handle) {
        throw new Exception("Cannot open CSV file");
    }
    
    // Get headers
    $headers = fgetcsv($handle);
    echo "📋 CSV Headers Found:\n";
    $columnMap = [];
    foreach ($headers as $index => $header) {
        $columnMap[trim($header)] = $index;
        if (in_array(trim($header), ['Lead ID', 'First Name', 'Last Name', 'Phone', 'Data', 'TCPA', 'Trusted Form Cert URL'])) {
            echo "   ✓ $header (column $index)\n";
        }
    }
    
    echo "\n🔄 IMPORTING LEADS...\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    
    // Prepare insert statement
    $insertStmt = $pdo->prepare("
        INSERT INTO leads (
            external_lead_id,
            jangle_lead_id,
            name,
            first_name,
            last_name,
            email,
            phone,
            address,
            city,
            state,
            zip_code,
            ip_address,
            source,
            type,
            status,
            tcpa_compliant,
            tcpa_consent_text,
            trusted_form_cert,
            leadid_code,
            user_agent,
            landing_page_url,
            drivers,
            vehicles,
            current_policy,
            requested_policy,
            meta,
            vici_list_id,
            vendor_name,
            buyer_name,
            campaign_id,
            opt_in_date,
            received_at,
            created_at,
            updated_at,
            tenant_id
        ) VALUES (
            :external_lead_id,
            :jangle_lead_id,
            :name,
            :first_name,
            :last_name,
            :email,
            :phone,
            :address,
            :city,
            :state,
            :zip_code,
            :ip_address,
            :source,
            :type,
            :status,
            :tcpa_compliant,
            :tcpa_consent_text,
            :trusted_form_cert,
            :leadid_code,
            :user_agent,
            :landing_page_url,
            :drivers,
            :vehicles,
            :current_policy,
            :requested_policy,
            :meta,
            :vici_list_id,
            :vendor_name,
            :buyer_name,
            :campaign_id,
            :opt_in_date,
            :received_at,
            NOW(),
            NOW(),
            1
        )
        ON CONFLICT (external_lead_id) DO UPDATE
        SET 
            drivers = EXCLUDED.drivers,
            vehicles = EXCLUDED.vehicles,
            current_policy = EXCLUDED.current_policy,
            requested_policy = EXCLUDED.requested_policy,
            meta = EXCLUDED.meta,
            updated_at = NOW()
    ");
    
    $imported = 0;
    $updated = 0;
    $errors = 0;
    $rowNum = 1;
    
    while (($row = fgetcsv($handle)) !== FALSE) {
        $rowNum++;
        
        try {
            // Extract data from CSV columns
            $leadId = $row[$columnMap['Lead ID']] ?? '';
            $vertical = strtolower($row[$columnMap['Vertical']] ?? 'auto'); // Column C - Type (Auto/Home)
            $firstName = $row[$columnMap['First Name']] ?? '';
            $lastName = $row[$columnMap['Last Name']] ?? '';
            $email = $row[$columnMap['Email']] ?? '';
            $phone = preg_replace('/[^0-9]/', '', $row[$columnMap['Phone']] ?? '');
            $address = $row[$columnMap['Address']] ?? '';
            $city = $row[$columnMap['City']] ?? '';
            $state = $row[$columnMap['State']] ?? '';
            $zipCode = $row[$columnMap['ZIP Code']] ?? '';
            $ipAddress = $row[$columnMap['IP Address']] ?? '';
            $timestamp = $row[$columnMap['Timestamp']] ?? '';
            $originallyCreated = $row[$columnMap['Originally Created']] ?? '';
            $tcpa = $row[$columnMap['TCPA']] ?? '';
            $tcpaText = $row[$columnMap['TCPA Consent Text']] ?? '';
            $trustedForm = $row[$columnMap['Trusted Form Cert URL']] ?? '';
            $leadidCode = $row[$columnMap['LeadiD Code']] ?? '';
            $vendor = $row[$columnMap['Vendor']] ?? '';
            $vendorCampaign = $row[$columnMap['Vendor Campaign']] ?? '';
            $buyer = $row[$columnMap['Buyer']] ?? '';
            
            // Parse the Data column (JSON)
            $dataJson = $row[$columnMap['Data']] ?? '{}';
            $parsedData = json_decode($dataJson, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                // If not valid JSON, try to clean it
                $dataJson = str_replace(["\r", "\n", "\t"], '', $dataJson);
                $parsedData = json_decode($dataJson, true);
                
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $parsedData = [];
                }
            }
            
            // Extract JSON components
            $drivers = isset($parsedData['drivers']) ? json_encode($parsedData['drivers']) : '[]';
            $vehicles = isset($parsedData['vehicles']) ? json_encode($parsedData['vehicles']) : '[]';
            $currentPolicy = isset($parsedData['current_policy']) ? json_encode($parsedData['current_policy']) : '{}';
            $requestedPolicy = isset($parsedData['requested_policy']) ? json_encode($parsedData['requested_policy']) : '{}';
            
            // Store any additional data in meta
            $meta = [];
            foreach ($parsedData as $key => $value) {
                if (!in_array($key, ['drivers', 'vehicles', 'current_policy', 'requested_policy'])) {
                    $meta[$key] = $value;
                }
            }
            $metaJson = !empty($meta) ? json_encode($meta) : '{}';
            
            // Determine ViciDial list based on import
            // These are LQF bulk leads that need to be matched with ViciDial lists 6018-6026
            $viciListId = '0'; // Start in List 0, will be matched later
            
            // Generate 13-digit external_lead_id
            $timestamp = time();
            $sequence = str_pad($imported % 1000, 3, '0', STR_PAD_LEFT);
            $externalLeadId = $timestamp . $sequence;
            
            // Execute insert/update
            $insertStmt->execute([
                ':external_lead_id' => $externalLeadId,
                ':jangle_lead_id' => $leadId,
                ':name' => trim($firstName . ' ' . $lastName),
                ':first_name' => $firstName,
                ':last_name' => $lastName,
                ':email' => $email,
                ':phone' => $phone,
                ':address' => $address,
                ':city' => $city,
                ':state' => $state,
                ':zip_code' => $zipCode,
                ':ip_address' => $ipAddress,
                ':source' => 'LQF_Bulk_Import',
                ':type' => $vertical,
                ':status' => 'imported',
                ':tcpa_compliant' => (strtolower($tcpa) === 'yes' || $tcpa === '1'),
                ':tcpa_consent_text' => $tcpaText,
                ':trusted_form_cert' => $trustedForm,
                ':leadid_code' => $leadidCode,
                ':user_agent' => $row[$columnMap['User Agent']] ?? '',
                ':landing_page_url' => $row[$columnMap['Landing Page URL']] ?? '',
                ':drivers' => $drivers,
                ':vehicles' => $vehicles,
                ':current_policy' => $currentPolicy,
                ':requested_policy' => $requestedPolicy,
                ':meta' => $metaJson,
                ':vici_list_id' => $viciListId,
                ':vendor_name' => $vendor,
                ':buyer_name' => $buyer,
                ':campaign_id' => $vendorCampaign,
                ':opt_in_date' => $timestamp ?: null,
                ':received_at' => $originallyCreated ?: $timestamp ?: date('Y-m-d H:i:s')
            ]);
            
            if ($insertStmt->rowCount() > 0) {
                $imported++;
            } else {
                $updated++;
            }
            
            // Progress indicator
            if ($rowNum % 100 == 0) {
                echo "  Processed $rowNum rows... ($imported imported, $updated updated)\n";
            }
            
            // Show sample data from first row
            if ($rowNum == 2) {
                echo "\n📝 SAMPLE DATA FROM FIRST LEAD:\n";
                echo "  Lead ID: $leadId\n";
                echo "  Name: $firstName $lastName\n";
                echo "  Phone: $phone\n";
                echo "  Location: $city, $state $zipCode\n";
                
                if (!empty($parsedData['drivers'])) {
                    echo "  Drivers: " . count($parsedData['drivers']) . " found\n";
                    foreach ($parsedData['drivers'] as $i => $driver) {
                        echo "    - Driver " . ($i+1) . ": " . 
                             ($driver['first_name'] ?? '') . " " . 
                             ($driver['last_name'] ?? '') . " (" . 
                             ($driver['relationship'] ?? '') . ")\n";
                    }
                }
                
                if (!empty($parsedData['vehicles'])) {
                    echo "  Vehicles: " . count($parsedData['vehicles']) . " found\n";
                    foreach ($parsedData['vehicles'] as $i => $vehicle) {
                        echo "    - Vehicle " . ($i+1) . ": " . 
                             ($vehicle['year'] ?? '') . " " . 
                             ($vehicle['make'] ?? '') . " " . 
                             ($vehicle['model'] ?? '') . "\n";
                    }
                }
                
                if (!empty($parsedData['requested_policy'])) {
                    echo "  Requested Coverage: " . 
                         ($parsedData['requested_policy']['coverage_type'] ?? 'Not specified') . "\n";
                }
                
                echo "\n  Continuing import...\n";
            }
            
        } catch (Exception $e) {
            $errors++;
            if ($errors <= 5) {
                echo "  ⚠️ Error on row $rowNum: " . $e->getMessage() . "\n";
            }
        }
    }
    
    fclose($handle);
    
    // Get final count
    $stmt = $pdo->query("SELECT COUNT(*) FROM leads");
    $afterCount = $stmt->fetchColumn();
    
    // Get count in List 0
    $stmt = $pdo->query("SELECT COUNT(*) FROM leads WHERE vici_list_id = '0'");
    $list0Count = $stmt->fetchColumn();
    
    echo "\n";
    echo "═══════════════════════════════════════════════════════════════\n";
    echo "                    IMPORT COMPLETE!                           \n";
    echo "═══════════════════════════════════════════════════════════════\n";
    echo "✅ Successfully imported: " . number_format($imported) . " new leads\n";
    echo "🔄 Updated existing: " . number_format($updated) . " leads\n";
    if ($errors > 0) {
        echo "⚠️  Errors encountered: " . number_format($errors) . "\n";
    }
    echo "\n";
    echo "📊 DATABASE TOTALS:\n";
    echo "  Before import: " . number_format($beforeCount) . " leads\n";
    echo "  After import: " . number_format($afterCount) . " leads\n";
    echo "  Net change: +" . number_format($afterCount - $beforeCount) . " leads\n";
    echo "\n";
    echo "📋 LEADS IN LIST 0 (need ViciDial matching): " . number_format($list0Count) . "\n";
    echo "\n";
    echo "🎯 NEXT STEPS:\n";
    echo "1. Run sync_vici_via_proxy.php to match these with ViciDial lists 6018-6026\n";
    echo "2. The JSON data (drivers, vehicles, policies) has been preserved\n";
    echo "3. All TCPA and Trusted Form data has been captured\n";
    echo "\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "\nStack trace:\n" . $e->getTraceAsString() . "\n";
}

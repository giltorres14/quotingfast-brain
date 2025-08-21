#!/usr/bin/env php
<?php
/**
 * Test Import - First Lead Only
 * Verify mapping before full import
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
echo "           TEST IMPORT - FIRST LEAD ONLY                       \n";
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
    
    // Open CSV file
    $handle = fopen($csvFile, 'r');
    if (!$handle) {
        throw new Exception("Cannot open CSV file");
    }
    
    // Get headers
    $headers = fgetcsv($handle, 0, ',', '"', '\\');
    $columnMap = [];
    foreach ($headers as $index => $header) {
        $columnMap[trim($header)] = $index;
    }
    
    // Get first data row
    $firstRow = fgetcsv($handle, 0, ',', '"', '\\');
    fclose($handle);
    
    // Extract data from CSV columns
    $leadId = $firstRow[$columnMap['Lead ID']] ?? '';
    $timestamp = $firstRow[$columnMap['Timestamp']] ?? '';
    $originallyCreated = $firstRow[$columnMap['Originally Created']] ?? '';
    $firstName = $firstRow[$columnMap['First Name']] ?? '';
    $lastName = $firstRow[$columnMap['Last Name']] ?? '';
    $email = $firstRow[$columnMap['Email']] ?? '';
    $phone = preg_replace('/[^0-9]/', '', $firstRow[$columnMap['Phone']] ?? '');
    $address = $firstRow[$columnMap['Address']] ?? '';
    $city = $firstRow[$columnMap['City']] ?? '';
    $state = $firstRow[$columnMap['State']] ?? '';
    $zipCode = $firstRow[$columnMap['ZIP Code']] ?? '';
    $ipAddress = $firstRow[$columnMap['IP Address']] ?? '';
    $vendor = $firstRow[$columnMap['Vendor']] ?? '';
    $vendorCampaign = $firstRow[$columnMap['Vendor Campaign']] ?? '';
    $buyer = $firstRow[$columnMap['Buyer']] ?? '';
    $buyerCampaign = $firstRow[$columnMap['Buyer Campaign']] ?? '';
    $tcpa = $firstRow[$columnMap['TCPA']] ?? '';
    $tcpaText = $firstRow[$columnMap['TCPA Consent Text']] ?? '';
    $trustedForm = $firstRow[$columnMap['Trusted Form Cert URL']] ?? '';
    $leadidCode = $firstRow[$columnMap['LeadiD Code']] ?? '';
    $userAgent = $firstRow[$columnMap['User Agent']] ?? '';
    $landingPageUrl = $firstRow[$columnMap['Landing Page URL']] ?? '';
    
    // Parse the Data column (JSON)
    $dataJson = $firstRow[$columnMap['Data']] ?? '{}';
    $parsedData = json_decode($dataJson, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        $parsedData = [];
    }
    
    // Generate 13-digit external_lead_id
    $unixTimestamp = time();
    $sequence = '001';
    $externalLeadId = $unixTimestamp . $sequence;
    
    echo "📋 LEAD DATA TO BE IMPORTED:\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "IDENTIFICATION:\n";
    echo "  Jangle Lead ID: $leadId\n";
    echo "  External Lead ID: $externalLeadId (generated 13-digit)\n";
    echo "\n";
    
    echo "TIMESTAMPS:\n";
    echo "  Opt-In Date: $timestamp\n";
    echo "  Originally Created: $originallyCreated\n";
    echo "  Received At: " . ($originallyCreated ?: $timestamp) . "\n";
    echo "\n";
    
    echo "CONTACT INFO:\n";
    echo "  Name: $firstName $lastName\n";
    echo "  Email: $email\n";
    echo "  Phone: $phone (cleaned)\n";
    echo "  Address: $address\n";
    echo "  Location: $city, $state $zipCode\n";
    echo "  IP: $ipAddress\n";
    echo "\n";
    
    echo "VENDOR/BUYER:\n";
    echo "  Vendor: $vendor\n";
    echo "  Vendor Campaign: $vendorCampaign\n";
    echo "  Buyer: $buyer\n";
    echo "  Buyer Campaign: $buyerCampaign\n";
    echo "\n";
    
    echo "COMPLIANCE:\n";
    echo "  TCPA: $tcpa → " . (strtolower($tcpa) === 'yes' ? 'true' : 'false') . "\n";
    echo "  TCPA Text: " . substr($tcpaText, 0, 80) . "...\n";
    echo "  Trusted Form: " . substr($trustedForm, 0, 60) . "...\n";
    echo "  LeadiD: $leadidCode\n";
    echo "\n";
    
    echo "TRACKING:\n";
    echo "  User Agent: " . substr($userAgent, 0, 60) . "...\n";
    echo "  Landing Page: " . substr($landingPageUrl, 0, 60) . "...\n";
    echo "\n";
    
    echo "JSON DATA PARSED:\n";
    if (!empty($parsedData['drivers'])) {
        echo "  ✅ Drivers: " . count($parsedData['drivers']) . " found\n";
        foreach ($parsedData['drivers'] as $i => $driver) {
            echo "     - " . ($driver['first_name'] ?? '') . " " . 
                 ($driver['last_name'] ?? '') . " (" . 
                 ($driver['relationship'] ?? '') . ")\n";
        }
    }
    
    if (!empty($parsedData['vehicles'])) {
        echo "  ✅ Vehicles: " . count($parsedData['vehicles']) . " found\n";
        foreach ($parsedData['vehicles'] as $i => $vehicle) {
            echo "     - " . ($vehicle['year'] ?? '') . " " . 
                 ($vehicle['make'] ?? '') . " " . 
                 ($vehicle['model'] ?? '') . "\n";
        }
    }
    
    if (!empty($parsedData['requested_policy'])) {
        echo "  ✅ Requested Policy: " . 
             ($parsedData['requested_policy']['coverage_type'] ?? 'Not specified') . "\n";
    }
    
    echo "\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "🔄 INSERTING TEST LEAD INTO DATABASE...\n";
    
    // Prepare data for insertion
    $drivers = isset($parsedData['drivers']) ? json_encode($parsedData['drivers']) : '[]';
    $vehicles = isset($parsedData['vehicles']) ? json_encode($parsedData['vehicles']) : '[]';
    $currentPolicy = isset($parsedData['current_policy']) ? json_encode($parsedData['current_policy']) : '{}';
    $requestedPolicy = isset($parsedData['requested_policy']) ? json_encode($parsedData['requested_policy']) : '{}';
    
    // Store additional data in meta
    $meta = [
        'buyer_campaign' => $buyerCampaign,
        'source_file' => 'LQF Bulk Export2025051920250821.csv',
        'import_date' => date('Y-m-d H:i:s'),
        'test_import' => true
    ];
    
    // Add any other fields from parsed data
    foreach ($parsedData as $key => $value) {
        if (!in_array($key, ['drivers', 'vehicles', 'current_policy', 'requested_policy'])) {
            $meta[$key] = $value;
        }
    }
    $metaJson = json_encode($meta);
    
    // Insert the lead
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
        RETURNING id, external_lead_id
    ");
    
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
        ':source' => 'LQF_Bulk_Test',
        ':status' => 'test_import',
        ':tcpa_compliant' => (strtolower($tcpa) === 'yes'),
        ':tcpa_consent_text' => $tcpaText,
        ':trusted_form_cert' => $trustedForm,
        ':leadid_code' => $leadidCode,
        ':user_agent' => $userAgent,
        ':landing_page_url' => $landingPageUrl,
        ':drivers' => $drivers,
        ':vehicles' => $vehicles,
        ':current_policy' => $currentPolicy,
        ':requested_policy' => $requestedPolicy,
        ':meta' => $metaJson,
        ':vici_list_id' => '0',
        ':vendor_name' => $vendor,
        ':buyer_name' => $buyer,
        ':campaign_id' => $vendorCampaign,
        ':opt_in_date' => $timestamp ?: null,
        ':received_at' => $originallyCreated ?: $timestamp ?: date('Y-m-d H:i:s')
    ]);
    
    $result = $insertStmt->fetch(PDO::FETCH_ASSOC);
    
    echo "\n";
    echo "✅ TEST LEAD IMPORTED SUCCESSFULLY!\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "Database ID: " . $result['id'] . "\n";
    echo "External Lead ID: " . $result['external_lead_id'] . "\n";
    echo "Jangle Lead ID: $leadId\n";
    echo "\n";
    echo "📋 VIEW THIS LEAD:\n";
    echo "Web UI: https://quotingfast-brain-ohio.onrender.com/leads\n";
    echo "Direct: https://quotingfast-brain-ohio.onrender.com/leads/" . $result['id'] . "\n";
    echo "\n";
    
    // Query back the lead to verify
    $verifyStmt = $pdo->prepare("
        SELECT 
            id,
            external_lead_id,
            jangle_lead_id,
            first_name,
            last_name,
            phone,
            opt_in_date,
            vendor_name,
            campaign_id,
            tcpa_compliant,
            vici_list_id,
            jsonb_array_length(drivers::jsonb) as driver_count,
            jsonb_array_length(vehicles::jsonb) as vehicle_count
        FROM leads 
        WHERE external_lead_id = :external_lead_id
    ");
    $verifyStmt->execute([':external_lead_id' => $externalLeadId]);
    $verifyResult = $verifyStmt->fetch(PDO::FETCH_ASSOC);
    
    echo "📊 VERIFICATION FROM DATABASE:\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "ID: " . $verifyResult['id'] . "\n";
    echo "External Lead ID: " . $verifyResult['external_lead_id'] . "\n";
    echo "Jangle Lead ID: " . $verifyResult['jangle_lead_id'] . "\n";
    echo "Name: " . $verifyResult['first_name'] . " " . $verifyResult['last_name'] . "\n";
    echo "Phone: " . $verifyResult['phone'] . "\n";
    echo "Opt-In Date: " . $verifyResult['opt_in_date'] . "\n";
    echo "Vendor: " . $verifyResult['vendor_name'] . "\n";
    echo "Campaign ID: " . $verifyResult['campaign_id'] . "\n";
    echo "TCPA Compliant: " . ($verifyResult['tcpa_compliant'] ? 'Yes' : 'No') . "\n";
    echo "ViciDial List: " . $verifyResult['vici_list_id'] . "\n";
    echo "Drivers: " . $verifyResult['driver_count'] . "\n";
    echo "Vehicles: " . $verifyResult['vehicle_count'] . "\n";
    echo "\n";
    echo "✅ Test import complete! Review the data above.\n";
    echo "If everything looks correct, run the full import.\n";
    echo "\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "\nStack trace:\n" . $e->getTraceAsString() . "\n";
}

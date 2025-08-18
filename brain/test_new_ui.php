<?php
// test_new_ui.php
// Test all new UI pages to ensure they work

echo "=== TESTING NEW UI PAGES ===\n\n";

$baseUrl = 'https://quotingfast-brain-ohio.onrender.com';

// List of all new pages to test
$pages = [
    // LEADS Section
    ['url' => '/leads', 'name' => 'Leads Dashboard', 'section' => 'LEADS'],
    ['url' => '/leads/queue', 'name' => 'Lead Queue', 'section' => 'LEADS'],
    ['url' => '/leads/search', 'name' => 'Lead Search', 'section' => 'LEADS'],
    ['url' => '/leads/import', 'name' => 'Lead Import', 'section' => 'LEADS'],
    ['url' => '/leads/reports', 'name' => 'Lead Reports', 'section' => 'LEADS'],
    
    // VICI Section
    ['url' => '/vici', 'name' => 'Vici Dashboard', 'section' => 'VICI'],
    ['url' => '/vici/reports', 'name' => 'Vici Reports', 'section' => 'VICI'],
    ['url' => '/vici/lead-flow', 'name' => 'Lead Flow Monitor', 'section' => 'VICI'],
    ['url' => '/vici/sync-status', 'name' => 'Sync Status', 'section' => 'VICI'],
    ['url' => '/vici/settings', 'name' => 'Vici Settings', 'section' => 'VICI'],
    
    // SMS Section
    ['url' => '/sms', 'name' => 'SMS Dashboard', 'section' => 'SMS'],
    ['url' => '/sms/campaigns', 'name' => 'SMS Campaigns', 'section' => 'SMS'],
    ['url' => '/sms/templates', 'name' => 'SMS Templates', 'section' => 'SMS'],
    ['url' => '/sms/analytics', 'name' => 'SMS Analytics', 'section' => 'SMS'],
    
    // BUYER PORTAL Section
    ['url' => '/buyers', 'name' => 'Buyer Dashboard', 'section' => 'BUYERS'],
    ['url' => '/buyers/directory', 'name' => 'Buyer Directory', 'section' => 'BUYERS'],
    ['url' => '/buyers/transfers', 'name' => 'Transfers', 'section' => 'BUYERS'],
    ['url' => '/buyers/revenue', 'name' => 'Revenue', 'section' => 'BUYERS'],
    
    // ADMIN Section
    ['url' => '/admin', 'name' => 'Admin Dashboard', 'section' => 'ADMIN'],
    
    // Existing pages that should still work
    ['url' => '/admin/simple-dashboard', 'name' => 'Simple Dashboard', 'section' => 'EXISTING'],
    ['url' => '/admin/lead-queue-monitor', 'name' => 'Lead Queue Monitor', 'section' => 'EXISTING'],
    ['url' => '/admin/vici-comprehensive-reports', 'name' => 'Comprehensive Reports', 'section' => 'EXISTING'],
    ['url' => '/admin/vici-lead-flow', 'name' => 'Lead Flow', 'section' => 'EXISTING'],
    ['url' => '/campaigns/directory', 'name' => 'Campaign Directory', 'section' => 'EXISTING'],
];

$currentSection = '';
$passed = 0;
$failed = 0;
$redirected = 0;

foreach ($pages as $page) {
    // Print section header
    if ($currentSection !== $page['section']) {
        $currentSection = $page['section'];
        echo "\n📂 {$currentSection} SECTION\n";
        echo str_repeat("-", 40) . "\n";
    }
    
    $fullUrl = $baseUrl . $page['url'];
    
    // Test the page
    $ch = curl_init($fullUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false); // Don't follow redirects
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_NOBODY, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    // Check status
    if ($httpCode == 200) {
        echo "✅ {$page['name']}: OK (200)\n";
        $passed++;
    } elseif ($httpCode == 302 || $httpCode == 301) {
        // Check where it redirects to
        preg_match('/Location: (.+)/', $response, $matches);
        $redirectTo = isset($matches[1]) ? trim($matches[1]) : 'unknown';
        echo "↪️ {$page['name']}: Redirect ({$httpCode}) → {$redirectTo}\n";
        $redirected++;
    } elseif ($httpCode == 404) {
        echo "❌ {$page['name']}: Not Found (404)\n";
        $failed++;
    } elseif ($httpCode == 500) {
        echo "❌ {$page['name']}: Server Error (500)\n";
        $failed++;
    } else {
        echo "⚠️ {$page['name']}: HTTP {$httpCode}\n";
        $failed++;
    }
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "📊 TEST SUMMARY\n";
echo str_repeat("=", 50) . "\n\n";

echo "✅ Passed: {$passed}\n";
echo "↪️ Redirected: {$redirected}\n";
echo "❌ Failed: {$failed}\n";
echo "📊 Total: " . count($pages) . "\n\n";

if ($failed > 0) {
    echo "⚠️ Some pages failed. These may need view files created.\n";
} else {
    echo "🎉 All pages are working!\n";
}

echo "\n📝 NOTES:\n";
echo "- Redirected pages are OK (they redirect to existing pages)\n";
echo "- Failed pages need view files created\n";
echo "- The new UI structure is accessible at {$baseUrl}/leads\n";


// test_new_ui.php
// Test all new UI pages to ensure they work

echo "=== TESTING NEW UI PAGES ===\n\n";

$baseUrl = 'https://quotingfast-brain-ohio.onrender.com';

// List of all new pages to test
$pages = [
    // LEADS Section
    ['url' => '/leads', 'name' => 'Leads Dashboard', 'section' => 'LEADS'],
    ['url' => '/leads/queue', 'name' => 'Lead Queue', 'section' => 'LEADS'],
    ['url' => '/leads/search', 'name' => 'Lead Search', 'section' => 'LEADS'],
    ['url' => '/leads/import', 'name' => 'Lead Import', 'section' => 'LEADS'],
    ['url' => '/leads/reports', 'name' => 'Lead Reports', 'section' => 'LEADS'],
    
    // VICI Section
    ['url' => '/vici', 'name' => 'Vici Dashboard', 'section' => 'VICI'],
    ['url' => '/vici/reports', 'name' => 'Vici Reports', 'section' => 'VICI'],
    ['url' => '/vici/lead-flow', 'name' => 'Lead Flow Monitor', 'section' => 'VICI'],
    ['url' => '/vici/sync-status', 'name' => 'Sync Status', 'section' => 'VICI'],
    ['url' => '/vici/settings', 'name' => 'Vici Settings', 'section' => 'VICI'],
    
    // SMS Section
    ['url' => '/sms', 'name' => 'SMS Dashboard', 'section' => 'SMS'],
    ['url' => '/sms/campaigns', 'name' => 'SMS Campaigns', 'section' => 'SMS'],
    ['url' => '/sms/templates', 'name' => 'SMS Templates', 'section' => 'SMS'],
    ['url' => '/sms/analytics', 'name' => 'SMS Analytics', 'section' => 'SMS'],
    
    // BUYER PORTAL Section
    ['url' => '/buyers', 'name' => 'Buyer Dashboard', 'section' => 'BUYERS'],
    ['url' => '/buyers/directory', 'name' => 'Buyer Directory', 'section' => 'BUYERS'],
    ['url' => '/buyers/transfers', 'name' => 'Transfers', 'section' => 'BUYERS'],
    ['url' => '/buyers/revenue', 'name' => 'Revenue', 'section' => 'BUYERS'],
    
    // ADMIN Section
    ['url' => '/admin', 'name' => 'Admin Dashboard', 'section' => 'ADMIN'],
    
    // Existing pages that should still work
    ['url' => '/admin/simple-dashboard', 'name' => 'Simple Dashboard', 'section' => 'EXISTING'],
    ['url' => '/admin/lead-queue-monitor', 'name' => 'Lead Queue Monitor', 'section' => 'EXISTING'],
    ['url' => '/admin/vici-comprehensive-reports', 'name' => 'Comprehensive Reports', 'section' => 'EXISTING'],
    ['url' => '/admin/vici-lead-flow', 'name' => 'Lead Flow', 'section' => 'EXISTING'],
    ['url' => '/campaigns/directory', 'name' => 'Campaign Directory', 'section' => 'EXISTING'],
];

$currentSection = '';
$passed = 0;
$failed = 0;
$redirected = 0;

foreach ($pages as $page) {
    // Print section header
    if ($currentSection !== $page['section']) {
        $currentSection = $page['section'];
        echo "\n📂 {$currentSection} SECTION\n";
        echo str_repeat("-", 40) . "\n";
    }
    
    $fullUrl = $baseUrl . $page['url'];
    
    // Test the page
    $ch = curl_init($fullUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false); // Don't follow redirects
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_NOBODY, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    // Check status
    if ($httpCode == 200) {
        echo "✅ {$page['name']}: OK (200)\n";
        $passed++;
    } elseif ($httpCode == 302 || $httpCode == 301) {
        // Check where it redirects to
        preg_match('/Location: (.+)/', $response, $matches);
        $redirectTo = isset($matches[1]) ? trim($matches[1]) : 'unknown';
        echo "↪️ {$page['name']}: Redirect ({$httpCode}) → {$redirectTo}\n";
        $redirected++;
    } elseif ($httpCode == 404) {
        echo "❌ {$page['name']}: Not Found (404)\n";
        $failed++;
    } elseif ($httpCode == 500) {
        echo "❌ {$page['name']}: Server Error (500)\n";
        $failed++;
    } else {
        echo "⚠️ {$page['name']}: HTTP {$httpCode}\n";
        $failed++;
    }
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "📊 TEST SUMMARY\n";
echo str_repeat("=", 50) . "\n\n";

echo "✅ Passed: {$passed}\n";
echo "↪️ Redirected: {$redirected}\n";
echo "❌ Failed: {$failed}\n";
echo "📊 Total: " . count($pages) . "\n\n";

if ($failed > 0) {
    echo "⚠️ Some pages failed. These may need view files created.\n";
} else {
    echo "🎉 All pages are working!\n";
}

echo "\n📝 NOTES:\n";
echo "- Redirected pages are OK (they redirect to existing pages)\n";
echo "- Failed pages need view files created\n";
echo "- The new UI structure is accessible at {$baseUrl}/leads\n";



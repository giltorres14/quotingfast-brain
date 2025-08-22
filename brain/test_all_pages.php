#!/usr/bin/env php
<?php
/**
 * COMPREHENSIVE PAGE TESTING SCRIPT
 * Tests all pages and buttons for errors
 */

echo "\n🔍 COMPREHENSIVE PAGE & BUTTON TEST\n";
echo "=====================================\n";
echo "Waiting 90 seconds for deployment...\n";
sleep(90);

$baseUrl = "https://quotingfast-brain-ohio.onrender.com";

// Test configuration
$tests = [
    // Main pages
    ['name' => 'Homepage', 'url' => '/', 'method' => 'GET'],
    ['name' => 'Admin Dashboard', 'url' => '/admin', 'method' => 'GET'],
    ['name' => 'Leads List', 'url' => '/leads', 'method' => 'GET'],
    ['name' => 'All Leads', 'url' => '/admin/all-leads', 'method' => 'GET'],
    
    // Vici pages
    ['name' => 'Vici Dashboard', 'url' => '/vici', 'method' => 'GET'],
    ['name' => 'Command Center', 'url' => '/vici/command-center', 'method' => 'GET'],
    ['name' => 'Lead Flow', 'url' => '/vici/lead-flow', 'method' => 'GET'],
    ['name' => 'Sync Status', 'url' => '/vici/sync-status', 'method' => 'GET'],
    ['name' => 'Comprehensive Reports', 'url' => '/admin/vici-comprehensive-reports', 'method' => 'GET'],
    
    // Lead specific pages (test multiple leads)
    ['name' => 'Lead 491801 View', 'url' => '/agent/lead/491801?mode=view', 'method' => 'GET'],
    ['name' => 'Lead 491801 Edit', 'url' => '/agent/lead/491801?mode=edit', 'method' => 'GET'],
    ['name' => 'Lead 491801 Payload', 'url' => '/api/lead/491801/payload', 'method' => 'GET'],
    
    ['name' => 'Lead 491471 View', 'url' => '/agent/lead/491471?mode=view', 'method' => 'GET'],
    ['name' => 'Lead 491471 Edit', 'url' => '/agent/lead/491471?mode=edit', 'method' => 'GET'],
    ['name' => 'Lead 491471 Payload', 'url' => '/api/lead/491471/payload', 'method' => 'GET'],
    
    // Button redirects
    ['name' => 'View Button (redirect)', 'url' => '/leads/491801', 'method' => 'GET'],
    ['name' => 'Edit Button (redirect)', 'url' => '/leads/491801/edit', 'method' => 'GET'],
    
    // API endpoints
    ['name' => 'API Directory', 'url' => '/api-directory', 'method' => 'GET'],
    ['name' => 'Webhook Status', 'url' => '/api-webhook', 'method' => 'GET'],
];

$results = [];
$errors = [];
$successes = [];

echo "\n📊 Testing " . count($tests) . " pages/endpoints...\n";
echo str_repeat("-", 60) . "\n";

foreach ($tests as $test) {
    $url = $baseUrl . $test['url'];
    
    // Initialize cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $test['method']);
    
    // Execute request
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    // Analyze response
    $status = 'UNKNOWN';
    $details = '';
    
    if ($error) {
        $status = 'ERROR';
        $details = $error;
        $errors[] = $test['name'];
    } elseif ($httpCode >= 200 && $httpCode < 300) {
        // Check for Blade errors in response
        if (strpos($response, 'unexpected token') !== false) {
            $status = 'BLADE ERROR';
            $details = 'Blade compilation error still present';
            $errors[] = $test['name'] . ' (Blade error)';
        } elseif (strpos($response, 'System Error') !== false) {
            $status = 'APP ERROR';
            $details = 'Application error page shown';
            $errors[] = $test['name'] . ' (App error)';
        } else {
            $status = 'OK';
            $successes[] = $test['name'];
        }
    } elseif ($httpCode == 302) {
        $status = 'REDIRECT';
        $successes[] = $test['name'];
    } elseif ($httpCode == 404) {
        $status = '404';
        $details = 'Page not found';
        $errors[] = $test['name'] . ' (404)';
    } elseif ($httpCode == 500) {
        $status = '500';
        $details = 'Server error';
        $errors[] = $test['name'] . ' (500)';
    } else {
        $status = $httpCode;
        $errors[] = $test['name'] . " ($httpCode)";
    }
    
    // Display result
    $icon = $status == 'OK' || $status == 'REDIRECT' ? '✅' : '❌';
    printf("%-30s %s %-10s %s\n", 
        $test['name'], 
        $icon, 
        "[$status]",
        $details
    );
    
    $results[] = [
        'name' => $test['name'],
        'url' => $test['url'],
        'status' => $status,
        'code' => $httpCode,
        'details' => $details
    ];
}

echo str_repeat("-", 60) . "\n";

// Summary
echo "\n📈 SUMMARY\n";
echo "==========\n";
echo "✅ Working: " . count($successes) . "/" . count($tests) . "\n";
echo "❌ Errors: " . count($errors) . "/" . count($tests) . "\n";

if (!empty($errors)) {
    echo "\n❌ FAILED PAGES:\n";
    foreach ($errors as $error) {
        echo "  - $error\n";
    }
}

if (!empty($successes)) {
    echo "\n✅ WORKING PAGES:\n";
    foreach (array_slice($successes, 0, 10) as $success) {
        echo "  - $success\n";
    }
    if (count($successes) > 10) {
        echo "  ... and " . (count($successes) - 10) . " more\n";
    }
}

// Check critical pages
$criticalPages = ['Lead 491801 View', 'Lead 491801 Edit', 'Lead 491471 View'];
$criticalWorking = true;
foreach ($results as $result) {
    if (in_array($result['name'], $criticalPages)) {
        if ($result['status'] != 'OK') {
            $criticalWorking = false;
            break;
        }
    }
}

echo "\n🎯 CRITICAL STATUS:\n";
if ($criticalWorking) {
    echo "✅ Lead view/edit pages are WORKING!\n";
} else {
    echo "❌ Lead view/edit pages still have errors\n";
    echo "   The Blade compilation error persists\n";
    echo "   May need manual server cache clearing\n";
}

echo "\n💡 RECOMMENDATIONS:\n";
if (!$criticalWorking) {
    echo "1. SSH into server and manually clear cache:\n";
    echo "   php artisan view:clear\n";
    echo "   php artisan cache:clear\n";
    echo "   rm -rf storage/framework/views/*\n";
    echo "2. Or trigger another deployment with CACHE_BUST increment\n";
    echo "3. Or restore from working git commit\n";
} else {
    echo "✅ All critical pages working!\n";
    echo "   Continue with pending tasks\n";
}

echo "\n";


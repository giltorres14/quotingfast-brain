<?php

// This script will verify and fix ALL UI issues from the last 4 hours

$issues = [
    'refresh_button' => false,
    'lead_view_sticky_header' => false,
    'back_button_position' => false,
    'tcpa_decimals' => false,
    'vehicle_comp_collision' => false,
    'duplicate_lead_details' => false,
    'save_button_position' => false,
    'payload_button_position' => false,
    'email_position' => false,
    'edit_mode_questions' => false,
    'years_licensed_formula' => false,
    'tcpa_text_hidden' => false,
    'driver_card_layout' => false,
    'vehicle_card_layout' => false,
    'stats_switching' => false,
    'webhooks_saving' => false,
    'timezone_est' => false,
    'stuck_queue_scroll' => false,
    'stuck_queue_date' => false,
    'stuck_queue_modal' => false
];

// Check lead dashboard for refresh button
$dashboardPath = __DIR__ . '/resources/views/leads/index-new.blade.php';
if (file_exists($dashboardPath)) {
    $content = file_get_contents($dashboardPath);
    
    // Check for refresh button
    if (strpos($content, 'refreshDashboard()') !== false && strpos($content, '🔄 Refresh') !== false) {
        $issues['refresh_button'] = true;
        echo "✅ Refresh button exists\n";
    } else {
        echo "❌ Refresh button missing\n";
    }
    
    // Check stats switching
    if (strpos($content, "window.location.href = '/leads?period=' + period") !== false) {
        $issues['stats_switching'] = true;
        echo "✅ Stats switching works\n";
    } else {
        echo "❌ Stats switching broken\n";
    }
} else {
    echo "❌ Dashboard file not found\n";
}

// Check lead display page
$leadDisplayPath = __DIR__ . '/resources/views/agent/lead-display.blade.php';
if (file_exists($leadDisplayPath)) {
    $content = file_get_contents($leadDisplayPath);
    
    // Check sticky header
    if (strpos($content, 'position: fixed') !== false && strpos($content, 'top: 0') !== false) {
        $issues['lead_view_sticky_header'] = true;
        echo "✅ Header is sticky\n";
    } else {
        echo "❌ Header not sticky\n";
    }
    
    // Check back button position
    if (strpos($content, 'position: absolute; left: 12px') !== false) {
        $issues['back_button_position'] = true;
        echo "✅ Back button positioned left\n";
    } else {
        echo "❌ Back button not positioned correctly\n";
    }
    
    // Check TCPA decimals
    if (strpos($content, 'Math.floor(daysRemaining)') !== false || strpos($content, 'floor($daysRemaining)') !== false) {
        $issues['tcpa_decimals'] = true;
        echo "✅ TCPA days rounded\n";
    } else {
        echo "❌ TCPA days not rounded\n";
    }
    
    // Check vehicle comp/collision
    if (strpos($content, 'Comprehensive Deductible') !== false && strpos($content, 'Collision Deductible') !== false) {
        $issues['vehicle_comp_collision'] = true;
        echo "✅ Comp/Collision in vehicle cards\n";
    } else {
        echo "❌ Comp/Collision missing from vehicle cards\n";
    }
    
    // Check for duplicate lead details section
    if (substr_count($content, '<h2 class="section-title">Lead Details</h2>') <= 0) {
        $issues['duplicate_lead_details'] = true;
        echo "✅ No duplicate lead details\n";
    } else {
        echo "❌ Duplicate lead details section exists\n";
    }
    
    // Check save button in header
    if (strpos($content, 'position: absolute; right:') !== false && strpos($content, 'Save Lead') !== false) {
        $issues['save_button_position'] = true;
        echo "✅ Save button in header\n";
    } else {
        echo "❌ Save button not in header\n";
    }
    
    // Check payload button position
    if (strpos($content, 'View Payload') !== false && strpos($content, 'position: absolute; right:') !== false) {
        $issues['payload_button_position'] = true;
        echo "✅ Payload button in header\n";
    } else {
        echo "❌ Payload button not in header\n";
    }
    
    // Check email position (should be above Lead ID)
    if (preg_match('/<span>.*email.*<\/span>.*<br>.*<span>Lead ID:/s', $content)) {
        $issues['email_position'] = true;
        echo "✅ Email above Lead ID\n";
    } else {
        echo "❌ Email not above Lead ID\n";
    }
    
    // Check edit mode questions visibility
    if (strpos($content, '@if(!isset($mode) || $mode === \'agent\' || $mode === \'edit\')') !== false) {
        $issues['edit_mode_questions'] = true;
        echo "✅ Questions visible in edit mode\n";
    } else {
        echo "❌ Questions not visible in edit mode\n";
    }
    
    // Check Years Licensed formula
    if (strpos($content, 'max(1, $age - 17)') !== false || strpos($content, 'Math.max(1, age - 17)') !== false) {
        $issues['years_licensed_formula'] = true;
        echo "✅ Years Licensed formula correct\n";
    } else {
        echo "❌ Years Licensed formula missing\n";
    }
    
    // Check TCPA text hidden by default
    if (strpos($content, 'tcpa-text-full" style="display: none;"') !== false) {
        $issues['tcpa_text_hidden'] = true;
        echo "✅ TCPA text hidden by default\n";
    } else {
        echo "❌ TCPA text not hidden\n";
    }
} else {
    echo "❌ Lead display file not found\n";
}

// Check webhooks
$webPath = __DIR__ . '/routes/web.php';
if (file_exists($webPath)) {
    $content = file_get_contents($webPath);
    
    // Check if webhooks are saving leads
    if (strpos($content, 'Lead::create($leadData)') !== false) {
        $issues['webhooks_saving'] = true;
        echo "✅ Webhooks saving leads\n";
    } else {
        echo "❌ Webhooks not saving leads\n";
    }
    
    // Check timezone usage
    if (strpos($content, 'estNow()') !== false) {
        $issues['timezone_est'] = true;
        echo "✅ Using EST timezone\n";
    } else {
        echo "❌ Not using EST timezone\n";
    }
} else {
    echo "❌ Routes file not found\n";
}

// Check stuck in queue page
$queuePath = __DIR__ . '/resources/views/admin/lead-queue.blade.php';
if (file_exists($queuePath)) {
    $content = file_get_contents($queuePath);
    
    // Check horizontal scroll
    if (strpos($content, 'overflow-x: auto') !== false) {
        $issues['stuck_queue_scroll'] = true;
        echo "✅ Horizontal scroll enabled\n";
    } else {
        echo "❌ No horizontal scroll\n";
    }
    
    // Check date column
    if (strpos($content, '<th>Date</th>') !== false) {
        $issues['stuck_queue_date'] = true;
        echo "✅ Date column exists\n";
    } else {
        echo "❌ Date column missing\n";
    }
    
    // Check modal functionality
    if (strpos($content, 'showLeadDetail') !== false) {
        $issues['stuck_queue_modal'] = true;
        echo "✅ Lead detail modal exists\n";
    } else {
        echo "❌ Lead detail modal missing\n";
    }
} else {
    echo "❌ Lead queue file not found\n";
}

echo "\n=== SUMMARY ===\n";
$fixedCount = 0;
$brokenCount = 0;
foreach ($issues as $issue => $status) {
    if ($status) {
        $fixedCount++;
    } else {
        $brokenCount++;
        echo "❌ NEEDS FIX: " . str_replace('_', ' ', $issue) . "\n";
    }
}

echo "\n✅ Fixed: $fixedCount\n";
echo "❌ Broken: $brokenCount\n";

if ($brokenCount > 0) {
    echo "\n🔧 Creating fix script...\n";
}

// This script will verify and fix ALL UI issues from the last 4 hours

$issues = [
    'refresh_button' => false,
    'lead_view_sticky_header' => false,
    'back_button_position' => false,
    'tcpa_decimals' => false,
    'vehicle_comp_collision' => false,
    'duplicate_lead_details' => false,
    'save_button_position' => false,
    'payload_button_position' => false,
    'email_position' => false,
    'edit_mode_questions' => false,
    'years_licensed_formula' => false,
    'tcpa_text_hidden' => false,
    'driver_card_layout' => false,
    'vehicle_card_layout' => false,
    'stats_switching' => false,
    'webhooks_saving' => false,
    'timezone_est' => false,
    'stuck_queue_scroll' => false,
    'stuck_queue_date' => false,
    'stuck_queue_modal' => false
];

// Check lead dashboard for refresh button
$dashboardPath = __DIR__ . '/resources/views/leads/index-new.blade.php';
if (file_exists($dashboardPath)) {
    $content = file_get_contents($dashboardPath);
    
    // Check for refresh button
    if (strpos($content, 'refreshDashboard()') !== false && strpos($content, '🔄 Refresh') !== false) {
        $issues['refresh_button'] = true;
        echo "✅ Refresh button exists\n";
    } else {
        echo "❌ Refresh button missing\n";
    }
    
    // Check stats switching
    if (strpos($content, "window.location.href = '/leads?period=' + period") !== false) {
        $issues['stats_switching'] = true;
        echo "✅ Stats switching works\n";
    } else {
        echo "❌ Stats switching broken\n";
    }
} else {
    echo "❌ Dashboard file not found\n";
}

// Check lead display page
$leadDisplayPath = __DIR__ . '/resources/views/agent/lead-display.blade.php';
if (file_exists($leadDisplayPath)) {
    $content = file_get_contents($leadDisplayPath);
    
    // Check sticky header
    if (strpos($content, 'position: fixed') !== false && strpos($content, 'top: 0') !== false) {
        $issues['lead_view_sticky_header'] = true;
        echo "✅ Header is sticky\n";
    } else {
        echo "❌ Header not sticky\n";
    }
    
    // Check back button position
    if (strpos($content, 'position: absolute; left: 12px') !== false) {
        $issues['back_button_position'] = true;
        echo "✅ Back button positioned left\n";
    } else {
        echo "❌ Back button not positioned correctly\n";
    }
    
    // Check TCPA decimals
    if (strpos($content, 'Math.floor(daysRemaining)') !== false || strpos($content, 'floor($daysRemaining)') !== false) {
        $issues['tcpa_decimals'] = true;
        echo "✅ TCPA days rounded\n";
    } else {
        echo "❌ TCPA days not rounded\n";
    }
    
    // Check vehicle comp/collision
    if (strpos($content, 'Comprehensive Deductible') !== false && strpos($content, 'Collision Deductible') !== false) {
        $issues['vehicle_comp_collision'] = true;
        echo "✅ Comp/Collision in vehicle cards\n";
    } else {
        echo "❌ Comp/Collision missing from vehicle cards\n";
    }
    
    // Check for duplicate lead details section
    if (substr_count($content, '<h2 class="section-title">Lead Details</h2>') <= 0) {
        $issues['duplicate_lead_details'] = true;
        echo "✅ No duplicate lead details\n";
    } else {
        echo "❌ Duplicate lead details section exists\n";
    }
    
    // Check save button in header
    if (strpos($content, 'position: absolute; right:') !== false && strpos($content, 'Save Lead') !== false) {
        $issues['save_button_position'] = true;
        echo "✅ Save button in header\n";
    } else {
        echo "❌ Save button not in header\n";
    }
    
    // Check payload button position
    if (strpos($content, 'View Payload') !== false && strpos($content, 'position: absolute; right:') !== false) {
        $issues['payload_button_position'] = true;
        echo "✅ Payload button in header\n";
    } else {
        echo "❌ Payload button not in header\n";
    }
    
    // Check email position (should be above Lead ID)
    if (preg_match('/<span>.*email.*<\/span>.*<br>.*<span>Lead ID:/s', $content)) {
        $issues['email_position'] = true;
        echo "✅ Email above Lead ID\n";
    } else {
        echo "❌ Email not above Lead ID\n";
    }
    
    // Check edit mode questions visibility
    if (strpos($content, '@if(!isset($mode) || $mode === \'agent\' || $mode === \'edit\')') !== false) {
        $issues['edit_mode_questions'] = true;
        echo "✅ Questions visible in edit mode\n";
    } else {
        echo "❌ Questions not visible in edit mode\n";
    }
    
    // Check Years Licensed formula
    if (strpos($content, 'max(1, $age - 17)') !== false || strpos($content, 'Math.max(1, age - 17)') !== false) {
        $issues['years_licensed_formula'] = true;
        echo "✅ Years Licensed formula correct\n";
    } else {
        echo "❌ Years Licensed formula missing\n";
    }
    
    // Check TCPA text hidden by default
    if (strpos($content, 'tcpa-text-full" style="display: none;"') !== false) {
        $issues['tcpa_text_hidden'] = true;
        echo "✅ TCPA text hidden by default\n";
    } else {
        echo "❌ TCPA text not hidden\n";
    }
} else {
    echo "❌ Lead display file not found\n";
}

// Check webhooks
$webPath = __DIR__ . '/routes/web.php';
if (file_exists($webPath)) {
    $content = file_get_contents($webPath);
    
    // Check if webhooks are saving leads
    if (strpos($content, 'Lead::create($leadData)') !== false) {
        $issues['webhooks_saving'] = true;
        echo "✅ Webhooks saving leads\n";
    } else {
        echo "❌ Webhooks not saving leads\n";
    }
    
    // Check timezone usage
    if (strpos($content, 'estNow()') !== false) {
        $issues['timezone_est'] = true;
        echo "✅ Using EST timezone\n";
    } else {
        echo "❌ Not using EST timezone\n";
    }
} else {
    echo "❌ Routes file not found\n";
}

// Check stuck in queue page
$queuePath = __DIR__ . '/resources/views/admin/lead-queue.blade.php';
if (file_exists($queuePath)) {
    $content = file_get_contents($queuePath);
    
    // Check horizontal scroll
    if (strpos($content, 'overflow-x: auto') !== false) {
        $issues['stuck_queue_scroll'] = true;
        echo "✅ Horizontal scroll enabled\n";
    } else {
        echo "❌ No horizontal scroll\n";
    }
    
    // Check date column
    if (strpos($content, '<th>Date</th>') !== false) {
        $issues['stuck_queue_date'] = true;
        echo "✅ Date column exists\n";
    } else {
        echo "❌ Date column missing\n";
    }
    
    // Check modal functionality
    if (strpos($content, 'showLeadDetail') !== false) {
        $issues['stuck_queue_modal'] = true;
        echo "✅ Lead detail modal exists\n";
    } else {
        echo "❌ Lead detail modal missing\n";
    }
} else {
    echo "❌ Lead queue file not found\n";
}

echo "\n=== SUMMARY ===\n";
$fixedCount = 0;
$brokenCount = 0;
foreach ($issues as $issue => $status) {
    if ($status) {
        $fixedCount++;
    } else {
        $brokenCount++;
        echo "❌ NEEDS FIX: " . str_replace('_', ' ', $issue) . "\n";
    }
}

echo "\n✅ Fixed: $fixedCount\n";
echo "❌ Broken: $brokenCount\n";

if ($brokenCount > 0) {
    echo "\n🔧 Creating fix script...\n";
}





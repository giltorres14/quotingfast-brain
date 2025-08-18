<?php

// Update the Lead Flow page to include reset times for all lists

$filePath = __DIR__ . '/resources/views/vici/lead-flow.blade.php';
$content = file_get_contents($filePath);

// List configurations with default reset times
$listConfigs = [
    '102' => [
        'name' => 'First Contact',
        'days' => 1,
        'resets' => 1,
        'reset_times' => '9:00 AM',
        'description' => 'First call attempt - single reset'
    ],
    '103' => [
        'name' => 'VM Follow-up', 
        'days' => 1,
        'resets' => 1,
        'reset_times' => '2:00 PM',
        'description' => 'Voicemail message - afternoon reset'
    ],
    '104' => [
        'name' => 'Intensive',
        'days' => 3,
        'resets' => 4,
        'reset_times' => '9:00 AM, 11:30 AM, 2:00 PM, 4:30 PM',
        'description' => 'Aggressive calling - 4 resets per day for 3 days = 12 calls'
    ],
    '105' => [
        'name' => 'Standard Follow-up',
        'days' => 5,
        'resets' => 2,
        'reset_times' => '10:00 AM, 3:00 PM',
        'description' => 'Regular follow-up - 2 resets per day for 5 days = 10 calls'
    ],
    '106' => [
        'name' => 'Reduced Follow-up',
        'days' => 7,
        'resets' => 1,
        'reset_times' => '11:00 AM',
        'description' => 'Daily single reset for 7 days = 7 calls'
    ],
    '107' => [
        'name' => 'Weekly Touch',
        'days' => 14,
        'resets' => 0.5,
        'reset_times' => 'Mon/Wed/Fri 10:00 AM',
        'description' => 'Every other day reset = 7 calls over 14 days'
    ],
    '108' => [
        'name' => 'Final Attempts',
        'days' => 14,
        'resets' => 0.25,
        'reset_times' => 'Tue/Thu 2:00 PM',
        'description' => 'Twice per week reset = 3-4 calls over 14 days'
    ]
];

// For each list from 102-108, update the structure
foreach ($listConfigs as $listId => $config) {
    // Find and replace the Calls/Day label with List Resets/Day
    $pattern = '/<div class="stat-label">Calls\/Day<\/div>\s*<div class="stat-value editable" contenteditable="false" data-field="calls_per_day" data-list="' . $listId . '">[^<]*<\/div>/';
    $replacement = '<div class="stat-label">List Resets/Day</div>
                    <div class="stat-value editable" contenteditable="false" data-field="calls_per_day" data-list="' . $listId . '">' . $config['resets'] . '</div>';
    
    $content = preg_replace($pattern, $replacement, $content);
    
    // Add reset times section after the stats div for this list
    $statsEndPattern = '/<\/div>\s*<div style="margin-top: 15px; padding: 10px; background: #f9fafb; border-radius: 8px;">\s*<strong>Description:<\/strong>/';
    
    // Look for the description section for this specific list
    $searchPattern = '/data-list="' . $listId . '".*?<div style="margin-top: 15px; padding: 10px; background: #f9fafb; border-radius: 8px;">/s';
    
    if (preg_match($searchPattern, $content, $matches)) {
        $insertPoint = strpos($content, $matches[0]) + strlen($matches[0]) - strlen('<div style="margin-top: 15px; padding: 10px; background: #f9fafb; border-radius: 8px;">');
        
        $resetTimesHtml = '
            <div style="margin-top: 15px; padding: 10px; background: #e5f3ff; border-radius: 8px;">
                <strong>Reset Times:</strong> 
                <span class="editable" contenteditable="false" data-field="reset_times" data-list="' . $listId . '" style="font-family: monospace;">' . $config['reset_times'] . '</span>
            </div>
            
            <div style="margin-top: 15px; padding: 10px; background: #f9fafb; border-radius: 8px;">';
        
        $content = substr($content, 0, $insertPoint) . $resetTimesHtml . substr($content, $insertPoint + strlen('<div style="margin-top: 15px; padding: 10px; background: #f9fafb; border-radius: 8px;">'));
    }
}

// Update the JavaScript to handle reset times
$jsUpdate = "
// Recalculate call ranges based on days and resets per day
function recalculateRanges() {
    let cumulativeCalls = 0;
    const lists = [101, 102, 103, 104, 105, 106, 107, 108];
    
    lists.forEach(listId => {
        const days = parseFloat(document.querySelector(\`[data-field=\"days\"][data-list=\"\${listId}\"]\`)?.textContent) || 0;
        const resetsPerDay = parseFloat(document.querySelector(\`[data-field=\"calls_per_day\"][data-list=\"\${listId}\"]\`)?.textContent) || 0;
        const totalCalls = days * resetsPerDay;
        
        // Update total calls display
        const totalCallsEl = document.getElementById(\`total-calls-\${listId}\`);
        if (totalCallsEl) {
            totalCallsEl.textContent = totalCalls > 0 ? totalCalls.toFixed(1).replace('.0', '') : '0';
        }
        
        // Update call range
        const rangeEl = document.getElementById(\`call-range-\${listId}\`);
        if (rangeEl) {
            if (listId === 101) {
                rangeEl.textContent = '0';
            } else {
                const minCalls = cumulativeCalls + 1;
                const maxCalls = cumulativeCalls + totalCalls;
                
                if (totalCalls === 0) {
                    rangeEl.textContent = '-';
                } else if (totalCalls === 1) {
                    rangeEl.textContent = minCalls.toString();
                } else {
                    rangeEl.textContent = \`\${minCalls}-\${Math.floor(maxCalls)}\`;
                }
                
                cumulativeCalls += totalCalls;
            }
        }
    });
    
    // Update archive list range
    const archiveRangeEl = document.getElementById('call-range-110');
    if (archiveRangeEl) {
        archiveRangeEl.textContent = \`\${Math.floor(cumulativeCalls) + 1}+\`;
    }
    
    // Update summary
    updateSummary();
}

// Update summary with reset schedule info
function updateSummary() {
    // Calculate total resets across all lists
    let totalDailyResets = 0;
    [102, 103, 104, 105, 106, 107, 108].forEach(listId => {
        const leads = parseInt(document.getElementById(\`lead-count-\${listId}\`)?.textContent.replace(/,/g, '')) || 0;
        const resetsPerDay = parseFloat(document.querySelector(\`[data-field=\"calls_per_day\"][data-list=\"\${listId}\"]\`)?.textContent) || 0;
        totalDailyResets += leads * resetsPerDay;
    });
    
    const dailyVolumeEl = document.getElementById('dailyVolume');
    if (dailyVolumeEl) {
        dailyVolumeEl.textContent = Math.floor(totalDailyResets).toLocaleString();
    }
}";

// Replace the existing recalculateRanges function
$content = preg_replace('/\/\/ Recalculate call ranges based on days and calls per day.*?\/\/ Update archive list range.*?\}/s', $jsUpdate, $content);

// Save the updated file
file_put_contents($filePath, $content);

echo "✅ Lead Flow page updated with reset times configuration!\n";
echo "\nList Reset Schedule:\n";
echo "-" . str_repeat("-", 70) . "\n";

foreach ($listConfigs as $listId => $config) {
    echo sprintf("List %s - %s:\n", $listId, $config['name']);
    echo sprintf("  • Days: %d | Resets/Day: %s | Total Calls: %d\n", 
        $config['days'], 
        $config['resets'],
        $config['days'] * $config['resets']
    );
    echo sprintf("  • Reset Times: %s\n", $config['reset_times']);
    echo sprintf("  • %s\n\n", $config['description']);
}

echo "List 110 - Archive: No resets (end of campaign)\n";

// Update the Lead Flow page to include reset times for all lists

$filePath = __DIR__ . '/resources/views/vici/lead-flow.blade.php';
$content = file_get_contents($filePath);

// List configurations with default reset times
$listConfigs = [
    '102' => [
        'name' => 'First Contact',
        'days' => 1,
        'resets' => 1,
        'reset_times' => '9:00 AM',
        'description' => 'First call attempt - single reset'
    ],
    '103' => [
        'name' => 'VM Follow-up', 
        'days' => 1,
        'resets' => 1,
        'reset_times' => '2:00 PM',
        'description' => 'Voicemail message - afternoon reset'
    ],
    '104' => [
        'name' => 'Intensive',
        'days' => 3,
        'resets' => 4,
        'reset_times' => '9:00 AM, 11:30 AM, 2:00 PM, 4:30 PM',
        'description' => 'Aggressive calling - 4 resets per day for 3 days = 12 calls'
    ],
    '105' => [
        'name' => 'Standard Follow-up',
        'days' => 5,
        'resets' => 2,
        'reset_times' => '10:00 AM, 3:00 PM',
        'description' => 'Regular follow-up - 2 resets per day for 5 days = 10 calls'
    ],
    '106' => [
        'name' => 'Reduced Follow-up',
        'days' => 7,
        'resets' => 1,
        'reset_times' => '11:00 AM',
        'description' => 'Daily single reset for 7 days = 7 calls'
    ],
    '107' => [
        'name' => 'Weekly Touch',
        'days' => 14,
        'resets' => 0.5,
        'reset_times' => 'Mon/Wed/Fri 10:00 AM',
        'description' => 'Every other day reset = 7 calls over 14 days'
    ],
    '108' => [
        'name' => 'Final Attempts',
        'days' => 14,
        'resets' => 0.25,
        'reset_times' => 'Tue/Thu 2:00 PM',
        'description' => 'Twice per week reset = 3-4 calls over 14 days'
    ]
];

// For each list from 102-108, update the structure
foreach ($listConfigs as $listId => $config) {
    // Find and replace the Calls/Day label with List Resets/Day
    $pattern = '/<div class="stat-label">Calls\/Day<\/div>\s*<div class="stat-value editable" contenteditable="false" data-field="calls_per_day" data-list="' . $listId . '">[^<]*<\/div>/';
    $replacement = '<div class="stat-label">List Resets/Day</div>
                    <div class="stat-value editable" contenteditable="false" data-field="calls_per_day" data-list="' . $listId . '">' . $config['resets'] . '</div>';
    
    $content = preg_replace($pattern, $replacement, $content);
    
    // Add reset times section after the stats div for this list
    $statsEndPattern = '/<\/div>\s*<div style="margin-top: 15px; padding: 10px; background: #f9fafb; border-radius: 8px;">\s*<strong>Description:<\/strong>/';
    
    // Look for the description section for this specific list
    $searchPattern = '/data-list="' . $listId . '".*?<div style="margin-top: 15px; padding: 10px; background: #f9fafb; border-radius: 8px;">/s';
    
    if (preg_match($searchPattern, $content, $matches)) {
        $insertPoint = strpos($content, $matches[0]) + strlen($matches[0]) - strlen('<div style="margin-top: 15px; padding: 10px; background: #f9fafb; border-radius: 8px;">');
        
        $resetTimesHtml = '
            <div style="margin-top: 15px; padding: 10px; background: #e5f3ff; border-radius: 8px;">
                <strong>Reset Times:</strong> 
                <span class="editable" contenteditable="false" data-field="reset_times" data-list="' . $listId . '" style="font-family: monospace;">' . $config['reset_times'] . '</span>
            </div>
            
            <div style="margin-top: 15px; padding: 10px; background: #f9fafb; border-radius: 8px;">';
        
        $content = substr($content, 0, $insertPoint) . $resetTimesHtml . substr($content, $insertPoint + strlen('<div style="margin-top: 15px; padding: 10px; background: #f9fafb; border-radius: 8px;">'));
    }
}

// Update the JavaScript to handle reset times
$jsUpdate = "
// Recalculate call ranges based on days and resets per day
function recalculateRanges() {
    let cumulativeCalls = 0;
    const lists = [101, 102, 103, 104, 105, 106, 107, 108];
    
    lists.forEach(listId => {
        const days = parseFloat(document.querySelector(\`[data-field=\"days\"][data-list=\"\${listId}\"]\`)?.textContent) || 0;
        const resetsPerDay = parseFloat(document.querySelector(\`[data-field=\"calls_per_day\"][data-list=\"\${listId}\"]\`)?.textContent) || 0;
        const totalCalls = days * resetsPerDay;
        
        // Update total calls display
        const totalCallsEl = document.getElementById(\`total-calls-\${listId}\`);
        if (totalCallsEl) {
            totalCallsEl.textContent = totalCalls > 0 ? totalCalls.toFixed(1).replace('.0', '') : '0';
        }
        
        // Update call range
        const rangeEl = document.getElementById(\`call-range-\${listId}\`);
        if (rangeEl) {
            if (listId === 101) {
                rangeEl.textContent = '0';
            } else {
                const minCalls = cumulativeCalls + 1;
                const maxCalls = cumulativeCalls + totalCalls;
                
                if (totalCalls === 0) {
                    rangeEl.textContent = '-';
                } else if (totalCalls === 1) {
                    rangeEl.textContent = minCalls.toString();
                } else {
                    rangeEl.textContent = \`\${minCalls}-\${Math.floor(maxCalls)}\`;
                }
                
                cumulativeCalls += totalCalls;
            }
        }
    });
    
    // Update archive list range
    const archiveRangeEl = document.getElementById('call-range-110');
    if (archiveRangeEl) {
        archiveRangeEl.textContent = \`\${Math.floor(cumulativeCalls) + 1}+\`;
    }
    
    // Update summary
    updateSummary();
}

// Update summary with reset schedule info
function updateSummary() {
    // Calculate total resets across all lists
    let totalDailyResets = 0;
    [102, 103, 104, 105, 106, 107, 108].forEach(listId => {
        const leads = parseInt(document.getElementById(\`lead-count-\${listId}\`)?.textContent.replace(/,/g, '')) || 0;
        const resetsPerDay = parseFloat(document.querySelector(\`[data-field=\"calls_per_day\"][data-list=\"\${listId}\"]\`)?.textContent) || 0;
        totalDailyResets += leads * resetsPerDay;
    });
    
    const dailyVolumeEl = document.getElementById('dailyVolume');
    if (dailyVolumeEl) {
        dailyVolumeEl.textContent = Math.floor(totalDailyResets).toLocaleString();
    }
}";

// Replace the existing recalculateRanges function
$content = preg_replace('/\/\/ Recalculate call ranges based on days and calls per day.*?\/\/ Update archive list range.*?\}/s', $jsUpdate, $content);

// Save the updated file
file_put_contents($filePath, $content);

echo "✅ Lead Flow page updated with reset times configuration!\n";
echo "\nList Reset Schedule:\n";
echo "-" . str_repeat("-", 70) . "\n";

foreach ($listConfigs as $listId => $config) {
    echo sprintf("List %s - %s:\n", $listId, $config['name']);
    echo sprintf("  • Days: %d | Resets/Day: %s | Total Calls: %d\n", 
        $config['days'], 
        $config['resets'],
        $config['days'] * $config['resets']
    );
    echo sprintf("  • Reset Times: %s\n", $config['reset_times']);
    echo sprintf("  • %s\n\n", $config['description']);
}

echo "List 110 - Archive: No resets (end of campaign)\n";

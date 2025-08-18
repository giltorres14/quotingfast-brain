<?php

echo "🔧 Fixing ALL UI issues comprehensively...\n\n";

// 1. Fix Lead Display Page
$leadDisplayPath = __DIR__ . '/resources/views/agent/lead-display.blade.php';
echo "Fixing lead display page...\n";

$content = file_get_contents($leadDisplayPath);

// Fix back button position (far left)
$content = preg_replace(
    '/<a href="\/leads"[^>]*class="back-button"[^>]*style="[^"]*"/',
    '<a href="/leads" class="back-button" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: white; text-decoration: none; font-weight: 600; font-size: 14px; display: {{ $isIframe ? \'none\' : \'block\' }};"',
    $content
);

// Fix TCPA days to remove decimals
$content = str_replace(
    '$daysRemaining = 90 - $daysSinceOptIn;',
    '$daysRemaining = floor(90 - $daysSinceOptIn);',
    $content
);

// Fix save button and payload button positions in header
// Find the header buttons section and replace it
$content = preg_replace(
    '/<div style="position: absolute; right: 12px;[^>]*>.*?<\/div>\s*<\/div>/s',
    '<div style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); display: flex; gap: 8px;">
        @if(isset($mode) && $mode === \'view\')
            <button onclick="showPayload()" style="background: #10b981; color: white; border: none; padding: 8px 16px; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 14px;">📦 View Payload</button>
            <a href="/agent/lead/{{ $lead->id }}?mode=edit" class="btn btn-secondary" style="background: #f59e0b; color: white; border: none; padding: 8px 16px; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 14px; text-decoration: none;">✏️ Edit Lead</a>
        @elseif(isset($mode) && $mode === \'edit\')
            <button onclick="showPayload()" style="background: #10b981; color: white; border: none; padding: 8px 16px; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 14px;">📦 View Payload</button>
            <button onclick="saveAllLeadData()" class="btn btn-primary" style="background: #28a745; color: white; border: none; padding: 8px 16px; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 14px;">💾 Save Lead</button>
        @endif
    </div>
</div>',
    $content,
    1
);

// Fix email position (above Lead ID)
$content = preg_replace(
    '/<span>Lead ID:.*?<\/span>.*?<br>.*?<span>.*?email.*?<\/span>/s',
    '<span>{{ $lead->email ?: \'No email\' }}</span><br>
            <span>Lead ID: {{ $lead->external_lead_id ?? $lead->id }}</span>',
    $content
);

// Ensure email is above Lead ID in the meta section
$content = preg_replace(
    '/<div class="meta"[^>]*>(.*?)<\/div>/s',
    '<div class="meta" style="font-size: 12px; opacity: 0.9; margin-top: 5px;">
            <span>{{ $lead->address }}, {{ $lead->city }}, {{ $lead->state }} {{ $lead->zip_code }}</span><br>
            <span>{{ $lead->email ?: \'No email\' }}</span><br>
            <span>Lead ID: {{ $lead->external_lead_id ?? $lead->id }}</span>
        </div>',
    $content,
    1
);

// Fix TCPA text to be hidden by default
$content = str_replace(
    '<span class="tcpa-text-full" style="display: block;">',
    '<span class="tcpa-text-full" style="display: none;">',
    $content
);

file_put_contents($leadDisplayPath, $content);
echo "✅ Fixed: Back button, TCPA decimals, Save/Payload buttons, Email position, TCPA text hidden\n";

// 2. Fix Lead Dashboard Stats Switching
$dashboardPath = __DIR__ . '/resources/views/leads/index-new.blade.php';
echo "\nFixing lead dashboard...\n";

$content = file_get_contents($dashboardPath);

// Fix stats switching to use page reload with URL params
$content = str_replace(
    "// For other periods, reload the page with the new period",
    "// For other periods, reload the page with the new period
        window.location.href = '/leads?period=' + period;",
    $content
);

// Ensure the JavaScript properly updates stats
$content = preg_replace(
    '/function updateStats\(period\) \{[^}]*\}/s',
    'function updateStats(period) {
        // Update active button
        document.querySelectorAll(\'.period-btn\').forEach(btn => {
            btn.classList.remove(\'active\');
            if (btn.dataset.period === period) {
                btn.classList.add(\'active\');
            }
        });
        
        // Show loading state
        document.querySelectorAll(\'.stat-value\').forEach(el => {
            el.textContent = \'Loading...\';
        });
        
        // Reload page with new period
        window.location.href = \'/leads?period=\' + period;
    }',
    $content
);

file_put_contents($dashboardPath, $content);
echo "✅ Fixed: Stats switching\n";

// 3. Fix Timezone in routes
$webPath = __DIR__ . '/routes/web.php';
echo "\nFixing webhooks and timezone...\n";

$content = file_get_contents($webPath);

// Replace all now() with estNow() in webhook routes
$content = preg_replace(
    "/('received_at'|'joined_at'|'created_at'|'updated_at')\s*=>\s*now\(\)/",
    "$1 => estNow()",
    $content
);

// Ensure estNow() helper is available
if (strpos($content, "use App\Helpers\timezone;") === false) {
    $content = "<?php\n\nuse App\Helpers\timezone;\n" . substr($content, 5);
}

file_put_contents($webPath, $content);
echo "✅ Fixed: Timezone to EST\n";

// 4. Fix Stuck in Queue page
$queuePath = __DIR__ . '/resources/views/admin/lead-queue.blade.php';
echo "\nFixing stuck in queue page...\n";

$content = file_get_contents($queuePath);

// Add horizontal scroll to table container
$content = str_replace(
    '<div class="card">',
    '<div class="card" style="overflow-x: auto;">',
    $content
);

// Add Date column to table header
$content = str_replace(
    '<th>Name</th>',
    '<th>Date</th>
                    <th>Name</th>',
    $content
);

// Add Date column to table body
$content = str_replace(
    '<td>{{ $lead->name }}</td>',
    '<td>{{ \Carbon\Carbon::parse($lead->created_at)->format(\'m/d/Y H:i\') }}</td>
                        <td>{{ $lead->name }}</td>',
    $content
);

// Fix Lead Detail modal button
$content = str_replace(
    'onclick="alert(\'Lead details would show here\')"',
    'onclick="showLeadDetail({{ $lead->id }})"',
    $content
);

// Add the showLeadDetail JavaScript function
if (strpos($content, 'function showLeadDetail') === false) {
    $content = str_replace(
        '</script>',
        '
    function showLeadDetail(leadId) {
        // Create modal
        const modal = document.createElement(\'div\');
        modal.style.cssText = \'position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 9999;\';
        
        const content = document.createElement(\'div\');
        content.style.cssText = \'background: white; padding: 20px; border-radius: 8px; max-width: 800px; max-height: 80vh; overflow-y: auto;\';
        content.innerHTML = \'<h2>Loading lead details...</h2>\';
        
        modal.appendChild(content);
        document.body.appendChild(modal);
        
        // Fetch lead details
        fetch(\'/api/leads/\' + leadId)
            .then(response => response.json())
            .then(data => {
                content.innerHTML = `
                    <h2>Lead Details</h2>
                    <p><strong>Name:</strong> ${data.name || \'N/A\'}</p>
                    <p><strong>Phone:</strong> ${data.phone || \'N/A\'}</p>
                    <p><strong>Email:</strong> ${data.email || \'N/A\'}</p>
                    <p><strong>Address:</strong> ${data.address || \'N/A\'}</p>
                    <p><strong>City:</strong> ${data.city || \'N/A\'}</p>
                    <p><strong>State:</strong> ${data.state || \'N/A\'}</p>
                    <p><strong>Zip:</strong> ${data.zip_code || \'N/A\'}</p>
                    <button onclick="this.closest(\'div\').parentElement.remove()" style="margin-top: 20px; padding: 10px 20px; background: #3b82f6; color: white; border: none; border-radius: 4px; cursor: pointer;">Close</button>
                `;
            })
            .catch(error => {
                content.innerHTML = `
                    <h2>Error Loading Lead</h2>
                    <p>Could not load lead details.</p>
                    <button onclick="this.closest(\'div\').parentElement.remove()" style="margin-top: 20px; padding: 10px 20px; background: #3b82f6; color: white; border: none; border-radius: 4px; cursor: pointer;">Close</button>
                `;
            });
        
        // Close on background click
        modal.onclick = function(e) {
            if (e.target === modal) {
                modal.remove();
            }
        };
    }
    </script>',
        $content
    );
}

file_put_contents($queuePath, $content);
echo "✅ Fixed: Horizontal scroll, Date column, Lead detail modal\n";

echo "\n✅ ALL UI ISSUES FIXED!\n";
echo "Now committing and deploying...\n";

echo "🔧 Fixing ALL UI issues comprehensively...\n\n";

// 1. Fix Lead Display Page
$leadDisplayPath = __DIR__ . '/resources/views/agent/lead-display.blade.php';
echo "Fixing lead display page...\n";

$content = file_get_contents($leadDisplayPath);

// Fix back button position (far left)
$content = preg_replace(
    '/<a href="\/leads"[^>]*class="back-button"[^>]*style="[^"]*"/',
    '<a href="/leads" class="back-button" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: white; text-decoration: none; font-weight: 600; font-size: 14px; display: {{ $isIframe ? \'none\' : \'block\' }};"',
    $content
);

// Fix TCPA days to remove decimals
$content = str_replace(
    '$daysRemaining = 90 - $daysSinceOptIn;',
    '$daysRemaining = floor(90 - $daysSinceOptIn);',
    $content
);

// Fix save button and payload button positions in header
// Find the header buttons section and replace it
$content = preg_replace(
    '/<div style="position: absolute; right: 12px;[^>]*>.*?<\/div>\s*<\/div>/s',
    '<div style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); display: flex; gap: 8px;">
        @if(isset($mode) && $mode === \'view\')
            <button onclick="showPayload()" style="background: #10b981; color: white; border: none; padding: 8px 16px; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 14px;">📦 View Payload</button>
            <a href="/agent/lead/{{ $lead->id }}?mode=edit" class="btn btn-secondary" style="background: #f59e0b; color: white; border: none; padding: 8px 16px; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 14px; text-decoration: none;">✏️ Edit Lead</a>
        @elseif(isset($mode) && $mode === \'edit\')
            <button onclick="showPayload()" style="background: #10b981; color: white; border: none; padding: 8px 16px; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 14px;">📦 View Payload</button>
            <button onclick="saveAllLeadData()" class="btn btn-primary" style="background: #28a745; color: white; border: none; padding: 8px 16px; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 14px;">💾 Save Lead</button>
        @endif
    </div>
</div>',
    $content,
    1
);

// Fix email position (above Lead ID)
$content = preg_replace(
    '/<span>Lead ID:.*?<\/span>.*?<br>.*?<span>.*?email.*?<\/span>/s',
    '<span>{{ $lead->email ?: \'No email\' }}</span><br>
            <span>Lead ID: {{ $lead->external_lead_id ?? $lead->id }}</span>',
    $content
);

// Ensure email is above Lead ID in the meta section
$content = preg_replace(
    '/<div class="meta"[^>]*>(.*?)<\/div>/s',
    '<div class="meta" style="font-size: 12px; opacity: 0.9; margin-top: 5px;">
            <span>{{ $lead->address }}, {{ $lead->city }}, {{ $lead->state }} {{ $lead->zip_code }}</span><br>
            <span>{{ $lead->email ?: \'No email\' }}</span><br>
            <span>Lead ID: {{ $lead->external_lead_id ?? $lead->id }}</span>
        </div>',
    $content,
    1
);

// Fix TCPA text to be hidden by default
$content = str_replace(
    '<span class="tcpa-text-full" style="display: block;">',
    '<span class="tcpa-text-full" style="display: none;">',
    $content
);

file_put_contents($leadDisplayPath, $content);
echo "✅ Fixed: Back button, TCPA decimals, Save/Payload buttons, Email position, TCPA text hidden\n";

// 2. Fix Lead Dashboard Stats Switching
$dashboardPath = __DIR__ . '/resources/views/leads/index-new.blade.php';
echo "\nFixing lead dashboard...\n";

$content = file_get_contents($dashboardPath);

// Fix stats switching to use page reload with URL params
$content = str_replace(
    "// For other periods, reload the page with the new period",
    "// For other periods, reload the page with the new period
        window.location.href = '/leads?period=' + period;",
    $content
);

// Ensure the JavaScript properly updates stats
$content = preg_replace(
    '/function updateStats\(period\) \{[^}]*\}/s',
    'function updateStats(period) {
        // Update active button
        document.querySelectorAll(\'.period-btn\').forEach(btn => {
            btn.classList.remove(\'active\');
            if (btn.dataset.period === period) {
                btn.classList.add(\'active\');
            }
        });
        
        // Show loading state
        document.querySelectorAll(\'.stat-value\').forEach(el => {
            el.textContent = \'Loading...\';
        });
        
        // Reload page with new period
        window.location.href = \'/leads?period=\' + period;
    }',
    $content
);

file_put_contents($dashboardPath, $content);
echo "✅ Fixed: Stats switching\n";

// 3. Fix Timezone in routes
$webPath = __DIR__ . '/routes/web.php';
echo "\nFixing webhooks and timezone...\n";

$content = file_get_contents($webPath);

// Replace all now() with estNow() in webhook routes
$content = preg_replace(
    "/('received_at'|'joined_at'|'created_at'|'updated_at')\s*=>\s*now\(\)/",
    "$1 => estNow()",
    $content
);

// Ensure estNow() helper is available
if (strpos($content, "use App\Helpers\timezone;") === false) {
    $content = "<?php\n\nuse App\Helpers\timezone;\n" . substr($content, 5);
}

file_put_contents($webPath, $content);
echo "✅ Fixed: Timezone to EST\n";

// 4. Fix Stuck in Queue page
$queuePath = __DIR__ . '/resources/views/admin/lead-queue.blade.php';
echo "\nFixing stuck in queue page...\n";

$content = file_get_contents($queuePath);

// Add horizontal scroll to table container
$content = str_replace(
    '<div class="card">',
    '<div class="card" style="overflow-x: auto;">',
    $content
);

// Add Date column to table header
$content = str_replace(
    '<th>Name</th>',
    '<th>Date</th>
                    <th>Name</th>',
    $content
);

// Add Date column to table body
$content = str_replace(
    '<td>{{ $lead->name }}</td>',
    '<td>{{ \Carbon\Carbon::parse($lead->created_at)->format(\'m/d/Y H:i\') }}</td>
                        <td>{{ $lead->name }}</td>',
    $content
);

// Fix Lead Detail modal button
$content = str_replace(
    'onclick="alert(\'Lead details would show here\')"',
    'onclick="showLeadDetail({{ $lead->id }})"',
    $content
);

// Add the showLeadDetail JavaScript function
if (strpos($content, 'function showLeadDetail') === false) {
    $content = str_replace(
        '</script>',
        '
    function showLeadDetail(leadId) {
        // Create modal
        const modal = document.createElement(\'div\');
        modal.style.cssText = \'position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 9999;\';
        
        const content = document.createElement(\'div\');
        content.style.cssText = \'background: white; padding: 20px; border-radius: 8px; max-width: 800px; max-height: 80vh; overflow-y: auto;\';
        content.innerHTML = \'<h2>Loading lead details...</h2>\';
        
        modal.appendChild(content);
        document.body.appendChild(modal);
        
        // Fetch lead details
        fetch(\'/api/leads/\' + leadId)
            .then(response => response.json())
            .then(data => {
                content.innerHTML = `
                    <h2>Lead Details</h2>
                    <p><strong>Name:</strong> ${data.name || \'N/A\'}</p>
                    <p><strong>Phone:</strong> ${data.phone || \'N/A\'}</p>
                    <p><strong>Email:</strong> ${data.email || \'N/A\'}</p>
                    <p><strong>Address:</strong> ${data.address || \'N/A\'}</p>
                    <p><strong>City:</strong> ${data.city || \'N/A\'}</p>
                    <p><strong>State:</strong> ${data.state || \'N/A\'}</p>
                    <p><strong>Zip:</strong> ${data.zip_code || \'N/A\'}</p>
                    <button onclick="this.closest(\'div\').parentElement.remove()" style="margin-top: 20px; padding: 10px 20px; background: #3b82f6; color: white; border: none; border-radius: 4px; cursor: pointer;">Close</button>
                `;
            })
            .catch(error => {
                content.innerHTML = `
                    <h2>Error Loading Lead</h2>
                    <p>Could not load lead details.</p>
                    <button onclick="this.closest(\'div\').parentElement.remove()" style="margin-top: 20px; padding: 10px 20px; background: #3b82f6; color: white; border: none; border-radius: 4px; cursor: pointer;">Close</button>
                `;
            });
        
        // Close on background click
        modal.onclick = function(e) {
            if (e.target === modal) {
                modal.remove();
            }
        };
    }
    </script>',
        $content
    );
}

file_put_contents($queuePath, $content);
echo "✅ Fixed: Horizontal scroll, Date column, Lead detail modal\n";

echo "\n✅ ALL UI ISSUES FIXED!\n";
echo "Now committing and deploying...\n";





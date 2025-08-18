<?php

// Fix lead dashboard refresh issue - remove auto-refresh and add manual refresh button

$viewFile = 'resources/views/leads/index-new.blade.php';
$content = file_get_contents($viewFile);

echo "Fixing lead dashboard refresh issue...\n\n";

// 1. Remove the auto-refresh interval (lines 620-625)
$content = preg_replace(
    '/\/\/ Auto-refresh stats every 30 seconds.*?}, 30000\);/s',
    '// Auto-refresh disabled - use manual refresh button instead',
    $content
);
echo "✓ Removed auto-refresh interval\n";

// 2. Remove the automatic page reload in fetchStats function
// Replace the setTimeout that reloads the page with just updating the URL without reload
$oldFetchStats = "// Small delay for visual feedback
        setTimeout(() => {
            const startStr = startDate.toISOString().split('T')[0];
            const endStr = endDate.toISOString().split('T')[0];
            
            // Build URL with all necessary parameters
            const url = new URL(window.location);
            
            // Clear old date params
            url.searchParams.delete('date_from');
            url.searchParams.delete('date_to');
            url.searchParams.delete('period');
            
            // Set new params based on period
            if (currentPeriod === 'custom') {
                url.searchParams.set('date_from', startStr);
                url.searchParams.set('date_to', endStr);
            }
            url.searchParams.set('period', currentPeriod);
            
            // Preserve other params (search, filters, etc)
            window.location.href = url.toString();
        }, 100);";

$newFetchStats = "// Update URL without reloading
        const startStr = startDate.toISOString().split('T')[0];
        const endStr = endDate.toISOString().split('T')[0];
        
        // Build URL with all necessary parameters
        const url = new URL(window.location);
        
        // Clear old date params
        url.searchParams.delete('date_from');
        url.searchParams.delete('date_to');
        url.searchParams.delete('period');
        
        // Set new params based on period
        if (currentPeriod === 'custom') {
            url.searchParams.set('date_from', startStr);
            url.searchParams.set('date_to', endStr);
        }
        url.searchParams.set('period', currentPeriod);
        
        // Update URL without reload
        window.history.pushState({}, '', url.toString());
        
        // Fetch data via AJAX instead of page reload
        fetch(url.toString(), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.text())
        .then(html => {
            // Parse the response and update stats
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            
            // Update stat numbers
            const newTotal = doc.getElementById('stat-total');
            const newVici = doc.getElementById('stat-vici');
            const newStuck = doc.getElementById('stat-stuck');
            const newConversion = doc.getElementById('stat-conversion');
            
            if (newTotal) document.getElementById('stat-total').textContent = newTotal.textContent;
            if (newVici) document.getElementById('stat-vici').textContent = newVici.textContent;
            if (newStuck) document.getElementById('stat-stuck').textContent = newStuck.textContent;
            if (newConversion) document.getElementById('stat-conversion').textContent = newConversion.textContent;
            
            // Update lead cards
            const leadGrid = doc.querySelector('.lead-grid');
            if (leadGrid) {
                document.querySelector('.lead-grid').innerHTML = leadGrid.innerHTML;
            }
            
            // Remove loading overlay
            const overlay = document.querySelector('div[style*=\"position:fixed\"]');
            if (overlay) overlay.remove();
        })
        .catch(error => {
            console.error('Error fetching stats:', error);
            // Remove loading overlay
            const overlay = document.querySelector('div[style*=\"position:fixed\"]');
            if (overlay) overlay.remove();
            
            // Show error message
            alert('Error loading stats. Please try again.');
        });";

$content = str_replace($oldFetchStats, $newFetchStats, $content);
echo "✓ Modified fetchStats to use AJAX instead of page reload\n";

// 3. Add manual refresh button to the date range selector
$oldDateButtons = '<div class="date-range-selector">
        <button class="period-btn active" data-period="today" onclick="updateStats(\'today\')">Today</button>
        <button class="period-btn" data-period="yesterday" onclick="updateStats(\'yesterday\')">Yesterday</button>
        <button class="period-btn" data-period="last7" onclick="updateStats(\'last7\')">Last 7 Days</button>
        <button class="period-btn" data-period="last30" onclick="updateStats(\'last30\')">Last 30 Days</button>
        <button class="period-btn" data-period="custom" onclick="showCustomDatePicker()">Custom</button>
    </div>';

$newDateButtons = '<div class="date-range-selector">
        <button class="period-btn active" data-period="today" onclick="updateStats(\'today\')">Today</button>
        <button class="period-btn" data-period="yesterday" onclick="updateStats(\'yesterday\')">Yesterday</button>
        <button class="period-btn" data-period="last7" onclick="updateStats(\'last7\')">Last 7 Days</button>
        <button class="period-btn" data-period="last30" onclick="updateStats(\'last30\')">Last 30 Days</button>
        <button class="period-btn" data-period="custom" onclick="showCustomDatePicker()">Custom</button>
        <button class="refresh-btn" onclick="refreshDashboard()" style="margin-left: auto; background: #10b981; color: white; padding: 0.5rem 1rem; border-radius: 6px; border: none; cursor: pointer; font-weight: 500;">
            🔄 Refresh
        </button>
    </div>';

$content = str_replace($oldDateButtons, $newDateButtons, $content);
echo "✓ Added manual refresh button\n";

// 4. Add refreshDashboard function
$refreshFunction = "
    function refreshDashboard() {
        // Show loading indicator
        const overlay = document.createElement('div');
        overlay.style.cssText = 'position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(255,255,255,0.9);display:flex;align-items:center;justify-content:center;z-index:9999';
        overlay.innerHTML = '<div style=\"text-align:center\"><div style=\"font-size:24px;color:#10b981;margin-bottom:10px\">🔄 Refreshing Dashboard...</div><div style=\"color:#6b7280\">Loading latest data</div></div>';
        document.body.appendChild(overlay);
        
        // Reload the page to get fresh data
        setTimeout(() => {
            window.location.reload();
        }, 500);
    }
    ";

// Add the refresh function before the showPayload function
$content = str_replace(
    '// Show payload in modal
    function showPayload(lead) {',
    $refreshFunction . '
    // Show payload in modal
    function showPayload(lead) {',
    $content
);
echo "✓ Added refreshDashboard function\n";

// 5. Update the date-range-selector CSS to accommodate the refresh button
$oldSelectorCSS = '.date-range-selector {
        display: flex;
        gap: 0.5rem;
        margin-bottom: 1.5rem;
    }';

$newSelectorCSS = '.date-range-selector {
        display: flex;
        gap: 0.5rem;
        margin-bottom: 1.5rem;
        align-items: center;
    }
    
    .refresh-btn:hover {
        background: #059669 !important;
        transform: scale(1.05);
        transition: all 0.2s;
    }';

$content = str_replace($oldSelectorCSS, $newSelectorCSS, $content);
echo "✓ Updated CSS for refresh button\n";

// Write back the modified content
file_put_contents($viewFile, $content);

echo "\n=== DASHBOARD REFRESH FIXED ===\n\n";
echo "Changes made:\n";
echo "1. ✓ Removed auto-refresh that was causing blank page every 30 seconds\n";
echo "2. ✓ Modified stats update to use AJAX instead of page reload\n";
echo "3. ✓ Added manual 'Refresh' button in top navigation\n";
echo "4. ✓ Page will only reload when user clicks refresh button\n";
echo "5. ✓ Stats period changes now update via AJAX without full reload\n";
echo "\nThe dashboard will now be stable and only refresh when requested!\n";


// Fix lead dashboard refresh issue - remove auto-refresh and add manual refresh button

$viewFile = 'resources/views/leads/index-new.blade.php';
$content = file_get_contents($viewFile);

echo "Fixing lead dashboard refresh issue...\n\n";

// 1. Remove the auto-refresh interval (lines 620-625)
$content = preg_replace(
    '/\/\/ Auto-refresh stats every 30 seconds.*?}, 30000\);/s',
    '// Auto-refresh disabled - use manual refresh button instead',
    $content
);
echo "✓ Removed auto-refresh interval\n";

// 2. Remove the automatic page reload in fetchStats function
// Replace the setTimeout that reloads the page with just updating the URL without reload
$oldFetchStats = "// Small delay for visual feedback
        setTimeout(() => {
            const startStr = startDate.toISOString().split('T')[0];
            const endStr = endDate.toISOString().split('T')[0];
            
            // Build URL with all necessary parameters
            const url = new URL(window.location);
            
            // Clear old date params
            url.searchParams.delete('date_from');
            url.searchParams.delete('date_to');
            url.searchParams.delete('period');
            
            // Set new params based on period
            if (currentPeriod === 'custom') {
                url.searchParams.set('date_from', startStr);
                url.searchParams.set('date_to', endStr);
            }
            url.searchParams.set('period', currentPeriod);
            
            // Preserve other params (search, filters, etc)
            window.location.href = url.toString();
        }, 100);";

$newFetchStats = "// Update URL without reloading
        const startStr = startDate.toISOString().split('T')[0];
        const endStr = endDate.toISOString().split('T')[0];
        
        // Build URL with all necessary parameters
        const url = new URL(window.location);
        
        // Clear old date params
        url.searchParams.delete('date_from');
        url.searchParams.delete('date_to');
        url.searchParams.delete('period');
        
        // Set new params based on period
        if (currentPeriod === 'custom') {
            url.searchParams.set('date_from', startStr);
            url.searchParams.set('date_to', endStr);
        }
        url.searchParams.set('period', currentPeriod);
        
        // Update URL without reload
        window.history.pushState({}, '', url.toString());
        
        // Fetch data via AJAX instead of page reload
        fetch(url.toString(), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.text())
        .then(html => {
            // Parse the response and update stats
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            
            // Update stat numbers
            const newTotal = doc.getElementById('stat-total');
            const newVici = doc.getElementById('stat-vici');
            const newStuck = doc.getElementById('stat-stuck');
            const newConversion = doc.getElementById('stat-conversion');
            
            if (newTotal) document.getElementById('stat-total').textContent = newTotal.textContent;
            if (newVici) document.getElementById('stat-vici').textContent = newVici.textContent;
            if (newStuck) document.getElementById('stat-stuck').textContent = newStuck.textContent;
            if (newConversion) document.getElementById('stat-conversion').textContent = newConversion.textContent;
            
            // Update lead cards
            const leadGrid = doc.querySelector('.lead-grid');
            if (leadGrid) {
                document.querySelector('.lead-grid').innerHTML = leadGrid.innerHTML;
            }
            
            // Remove loading overlay
            const overlay = document.querySelector('div[style*=\"position:fixed\"]');
            if (overlay) overlay.remove();
        })
        .catch(error => {
            console.error('Error fetching stats:', error);
            // Remove loading overlay
            const overlay = document.querySelector('div[style*=\"position:fixed\"]');
            if (overlay) overlay.remove();
            
            // Show error message
            alert('Error loading stats. Please try again.');
        });";

$content = str_replace($oldFetchStats, $newFetchStats, $content);
echo "✓ Modified fetchStats to use AJAX instead of page reload\n";

// 3. Add manual refresh button to the date range selector
$oldDateButtons = '<div class="date-range-selector">
        <button class="period-btn active" data-period="today" onclick="updateStats(\'today\')">Today</button>
        <button class="period-btn" data-period="yesterday" onclick="updateStats(\'yesterday\')">Yesterday</button>
        <button class="period-btn" data-period="last7" onclick="updateStats(\'last7\')">Last 7 Days</button>
        <button class="period-btn" data-period="last30" onclick="updateStats(\'last30\')">Last 30 Days</button>
        <button class="period-btn" data-period="custom" onclick="showCustomDatePicker()">Custom</button>
    </div>';

$newDateButtons = '<div class="date-range-selector">
        <button class="period-btn active" data-period="today" onclick="updateStats(\'today\')">Today</button>
        <button class="period-btn" data-period="yesterday" onclick="updateStats(\'yesterday\')">Yesterday</button>
        <button class="period-btn" data-period="last7" onclick="updateStats(\'last7\')">Last 7 Days</button>
        <button class="period-btn" data-period="last30" onclick="updateStats(\'last30\')">Last 30 Days</button>
        <button class="period-btn" data-period="custom" onclick="showCustomDatePicker()">Custom</button>
        <button class="refresh-btn" onclick="refreshDashboard()" style="margin-left: auto; background: #10b981; color: white; padding: 0.5rem 1rem; border-radius: 6px; border: none; cursor: pointer; font-weight: 500;">
            🔄 Refresh
        </button>
    </div>';

$content = str_replace($oldDateButtons, $newDateButtons, $content);
echo "✓ Added manual refresh button\n";

// 4. Add refreshDashboard function
$refreshFunction = "
    function refreshDashboard() {
        // Show loading indicator
        const overlay = document.createElement('div');
        overlay.style.cssText = 'position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(255,255,255,0.9);display:flex;align-items:center;justify-content:center;z-index:9999';
        overlay.innerHTML = '<div style=\"text-align:center\"><div style=\"font-size:24px;color:#10b981;margin-bottom:10px\">🔄 Refreshing Dashboard...</div><div style=\"color:#6b7280\">Loading latest data</div></div>';
        document.body.appendChild(overlay);
        
        // Reload the page to get fresh data
        setTimeout(() => {
            window.location.reload();
        }, 500);
    }
    ";

// Add the refresh function before the showPayload function
$content = str_replace(
    '// Show payload in modal
    function showPayload(lead) {',
    $refreshFunction . '
    // Show payload in modal
    function showPayload(lead) {',
    $content
);
echo "✓ Added refreshDashboard function\n";

// 5. Update the date-range-selector CSS to accommodate the refresh button
$oldSelectorCSS = '.date-range-selector {
        display: flex;
        gap: 0.5rem;
        margin-bottom: 1.5rem;
    }';

$newSelectorCSS = '.date-range-selector {
        display: flex;
        gap: 0.5rem;
        margin-bottom: 1.5rem;
        align-items: center;
    }
    
    .refresh-btn:hover {
        background: #059669 !important;
        transform: scale(1.05);
        transition: all 0.2s;
    }';

$content = str_replace($oldSelectorCSS, $newSelectorCSS, $content);
echo "✓ Updated CSS for refresh button\n";

// Write back the modified content
file_put_contents($viewFile, $content);

echo "\n=== DASHBOARD REFRESH FIXED ===\n\n";
echo "Changes made:\n";
echo "1. ✓ Removed auto-refresh that was causing blank page every 30 seconds\n";
echo "2. ✓ Modified stats update to use AJAX instead of page reload\n";
echo "3. ✓ Added manual 'Refresh' button in top navigation\n";
echo "4. ✓ Page will only reload when user clicks refresh button\n";
echo "5. ✓ Stats period changes now update via AJAX without full reload\n";
echo "\nThe dashboard will now be stable and only refresh when requested!\n";


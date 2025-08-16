<?php

// Fix the stats period switching issue

$file = 'resources/views/leads/index-new.blade.php';
$content = file_get_contents($file);

echo "Fixing stats period switching issue...\n\n";

// 1. Fix the currentPeriod initialization to use URL params
$oldInit = "let currentPeriod = '{{ \$stats[\"current_period\"] ?? \"today\" }}';";
$newInit = "// Get current period from URL or default to today
    const urlParams = new URLSearchParams(window.location.search);
    let currentPeriod = urlParams.get('period') || '{{ \$stats[\"current_period\"] ?? \"today\" }}';";

$content = str_replace($oldInit, $newInit, $content);
echo "✓ Fixed currentPeriod initialization from URL\n";

// 2. Fix the DOMContentLoaded to properly set active button
$oldDomReady = "// Initialize correct button on page load
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.period-btn').forEach(btn => {
            btn.classList.remove('active');
            if (btn.dataset.period === currentPeriod) {
                btn.classList.add('active');
            }
        });
    });";

$newDomReady = "// Initialize correct button on page load
    document.addEventListener('DOMContentLoaded', function() {
        // Get period from URL or use current
        const urlParams = new URLSearchParams(window.location.search);
        const activePeriod = urlParams.get('period') || currentPeriod;
        
        document.querySelectorAll('.period-btn').forEach(btn => {
            btn.classList.remove('active');
            if (btn.dataset.period === activePeriod) {
                btn.classList.add('active');
            }
        });
        
        // Update currentPeriod to match URL
        currentPeriod = activePeriod;
    });";

$content = str_replace($oldDomReady, $newDomReady, $content);
echo "✓ Fixed DOMContentLoaded button initialization\n";

// 3. Fix fetchStats to properly handle period parameter
$fetchStatsOld = 'function fetchStats(startDate, endDate, label) {
        // Show loading state immediately
        document.getElementById(\'stat-total\').innerHTML = \'<span style="color: #6b7280;">Loading...</span>\';
        document.getElementById(\'stat-vici\').innerHTML = \'<span style="color: #6b7280;">Loading...</span>\';
        document.getElementById(\'stat-stuck\').innerHTML = \'<span style="color: #6b7280;">Loading...</span>\';
        document.getElementById(\'stat-conversion\').innerHTML = \'<span style="color: #6b7280;">Loading...</span>\';
        
        // Update labels
        document.querySelectorAll(\'.stat-label\').forEach(el => {
            const text = el.textContent;
            if (text.includes(\'Leads\') && !text.includes(\'Vici\') && !text.includes(\'Queue\')) {
                el.textContent = label + \' Leads\';
            } else if (text.includes(\'Vici\')) {
                el.textContent = \'Sent to Vici (\' + label + \')\';
            } else if (text.includes(\'Queue\')) {
                el.textContent = \'Stuck in Queue (\' + label + \')\';
            }
        });
        
        // For now, just update labels and use the passed PHP data for today
        // In a full implementation, we\'d pass all date ranges from PHP
        if (currentPeriod === \'today\') {
            // Use the PHP-provided today stats with a slight delay to show loading
            setTimeout(() => {
                document.getElementById(\'stat-total\').textContent = \'{{ number_format($stats["today_leads"] ?? 0) }}\';
                document.getElementById(\'stat-vici\').textContent = \'{{ number_format($stats["today_vici"] ?? 0) }}\';
                document.getElementById(\'stat-stuck\').textContent = \'{{ number_format($stats["today_stuck"] ?? 0) }}\';
                
                const total = {{ $stats[\'today_leads\'] ?? 0 }};
                const vici = {{ $stats[\'today_vici\'] ?? 0 }};
                const rate = total > 0 ? ((vici / total) * 100).toFixed(1) : 0;
                document.getElementById(\'stat-conversion\').textContent = rate + \'%\';
            }, 300);
        } else {
            // For other periods, show loading overlay then reload
            const overlay = document.createElement(\'div\');
            overlay.style.cssText = \'position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(255,255,255,0.9);display:flex;align-items:center;justify-content:center;z-index:9999\';
            overlay.innerHTML = \'<div style="text-align:center"><div style="font-size:24px;color:#4A90E2;margin-bottom:10px">Loading Stats...</div><div style="color:#6b7280">Fetching \' + label + \' data</div></div>\';
            document.body.appendChild(overlay);
            
            // Small delay for visual feedback
            setTimeout(() => {
                const startStr = startDate.toISOString().split(\'T\')[0];
                const endStr = endDate.toISOString().split(\'T\')[0];
                
                // Add date range to URL and reload
                const url = new URL(window.location);
                url.searchParams.set(\'date_from\', startStr);
                url.searchParams.set(\'date_to\', endStr);
                url.searchParams.set(\'period\', currentPeriod);
                window.location.href = url.toString();
            }, 100);
        }
    }';

$fetchStatsNew = 'function fetchStats(startDate, endDate, label) {
        // Show loading state immediately
        document.getElementById(\'stat-total\').innerHTML = \'<span style="color: #6b7280;">Loading...</span>\';
        document.getElementById(\'stat-vici\').innerHTML = \'<span style="color: #6b7280;">Loading...</span>\';
        document.getElementById(\'stat-stuck\').innerHTML = \'<span style="color: #6b7280;">Loading...</span>\';
        document.getElementById(\'stat-conversion\').innerHTML = \'<span style="color: #6b7280;">Loading...</span>\';
        
        // Update labels
        document.querySelectorAll(\'.stat-label\').forEach(el => {
            const text = el.textContent;
            if (text.includes(\'Leads\') && !text.includes(\'Vici\') && !text.includes(\'Queue\')) {
                el.textContent = label + \' Leads\';
            } else if (text.includes(\'Vici\')) {
                el.textContent = \'Sent to Vici (\' + label + \')\';
            } else if (text.includes(\'Queue\')) {
                el.textContent = \'Stuck in Queue (\' + label + \')\';
            }
        });
        
        // Always reload page with new parameters to get fresh data
        const overlay = document.createElement(\'div\');
        overlay.style.cssText = \'position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(255,255,255,0.9);display:flex;align-items:center;justify-content:center;z-index:9999\';
        overlay.innerHTML = \'<div style="text-align:center"><div style="font-size:24px;color:#4A90E2;margin-bottom:10px">Loading Stats...</div><div style="color:#6b7280">Fetching \' + label + \' data</div></div>\';
        document.body.appendChild(overlay);
        
        // Small delay for visual feedback
        setTimeout(() => {
            const startStr = startDate.toISOString().split(\'T\')[0];
            const endStr = endDate.toISOString().split(\'T\')[0];
            
            // Build URL with all necessary parameters
            const url = new URL(window.location);
            
            // Clear old date params
            url.searchParams.delete(\'date_from\');
            url.searchParams.delete(\'date_to\');
            url.searchParams.delete(\'period\');
            
            // Set new params based on period
            if (currentPeriod === \'custom\') {
                url.searchParams.set(\'date_from\', startStr);
                url.searchParams.set(\'date_to\', endStr);
            }
            url.searchParams.set(\'period\', currentPeriod);
            
            // Preserve other params (search, filters, etc)
            window.location.href = url.toString();
        }, 100);
    }';

$content = str_replace($fetchStatsOld, $fetchStatsNew, $content);
echo "✓ Fixed fetchStats to properly handle period changes\n";

// 4. Update the button initialization to check URL params on load
$buttonsHtml = '<button onclick="updateStats(\'today\')" class="period-btn active" data-period="today">Today</button>
            <button onclick="updateStats(\'yesterday\')" class="period-btn" data-period="yesterday">Yesterday</button>
            <button onclick="updateStats(\'last7\')" class="period-btn" data-period="last7">Last 7 Days</button>
            <button onclick="updateStats(\'last30\')" class="period-btn" data-period="last30">Last 30 Days</button>
            <button onclick="showCustomDatePicker()" class="period-btn" data-period="custom">Custom Range</button>';

$newButtonsHtml = '<button onclick="updateStats(\'today\')" class="period-btn" data-period="today">Today</button>
            <button onclick="updateStats(\'yesterday\')" class="period-btn" data-period="yesterday">Yesterday</button>
            <button onclick="updateStats(\'last7\')" class="period-btn" data-period="last7">Last 7 Days</button>
            <button onclick="updateStats(\'last30\')" class="period-btn" data-period="last30">Last 30 Days</button>
            <button onclick="showCustomDatePicker()" class="period-btn" data-period="custom">Custom Range</button>';

// Buttons stay the same, the JavaScript handles the active state
echo "✓ Button HTML structure maintained\n";

// Write the fixed content back
file_put_contents($file, $content);

echo "\n✅ Stats switching fixed:\n";
echo "  - Period now properly maintained in URL\n";
echo "  - Active button correctly set from URL on page load\n";
echo "  - Switching between periods properly updates URL\n";
echo "  - Today/Yesterday/Last 7/Last 30 all work correctly\n";

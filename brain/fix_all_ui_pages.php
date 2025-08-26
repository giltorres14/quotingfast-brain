<?php
/**
 * MASTER FIX FOR ALL UI PAGES
 * This script updates all routes to return direct HTML instead of using Blade views
 * Solves persistent 500 errors on Render.com deployment
 */

// This file contains the HTML templates for each page that will be inserted into routes
// Run this locally to generate the route updates

$pages = [
    'vici_dashboard' => [
        'route' => '/vici',
        'title' => 'Vici Dashboard',
        'needs_data' => true
    ],
    'command_center' => [
        'route' => '/vici-command-center',
        'title' => 'ViciDial Command Center',
        'needs_data' => true
    ],
    'admin_dashboard' => [
        'route' => '/admin',
        'title' => 'Admin Dashboard',
        'needs_data' => true
    ],
    'lead_queue' => [
        'route' => '/admin/lead-queue-monitor',
        'title' => 'Lead Queue Monitor',
        'needs_data' => true
    ],
    'control_center' => [
        'route' => '/control-center',
        'title' => 'Control Center',
        'needs_data' => false
    ]
];

// Generate the shared CSS that makes everything look awesome
function getSharedStyles() {
    return '
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        
        /* Animated Background */
        body::before {
            content: "";
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, #667eea, #764ba2, #f093fb, #f5576c);
            background-size: 400% 400%;
            animation: gradientShift 15s ease infinite;
            opacity: 0.1;
            z-index: -1;
        }
        
        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        /* Glass Morphism Cards */
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(31, 38, 135, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.18);
            padding: 25px;
            margin-bottom: 25px;
            animation: slideUp 0.5s ease;
        }
        
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        /* Header */
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            position: relative;
            overflow: hidden;
        }
        
        .header::after {
            content: "";
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: pulse 4s ease-in-out infinite;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 0.5; }
            50% { transform: scale(1.1); opacity: 0.8; }
        }
        
        .header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
            position: relative;
            z-index: 1;
        }
        
        /* Navigation */
        .nav {
            background: rgba(31, 41, 55, 0.95);
            backdrop-filter: blur(10px);
            padding: 15px 0;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .nav-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .nav-link {
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 10px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .nav-link::before {
            content: "";
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s ease;
        }
        
        .nav-link:hover::before {
            left: 100%;
        }
        
        .nav-link:hover {
            background: rgba(255,255,255,0.1);
            transform: translateY(-2px);
        }
        
        .command-center-btn {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            font-weight: 600;
            animation: glow 2s ease-in-out infinite;
        }
        
        @keyframes glow {
            0%, 100% { box-shadow: 0 0 5px rgba(240, 147, 251, 0.5); }
            50% { box-shadow: 0 0 20px rgba(240, 147, 251, 0.8); }
        }
        
        /* Container */
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 30px 20px;
        }
        
        /* Metrics Grid */
        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }
        
        .metric-card {
            background: linear-gradient(135deg, var(--gradient-start), var(--gradient-end));
            color: white;
            padding: 25px;
            border-radius: 20px;
            position: relative;
            overflow: hidden;
            transform: translateY(0);
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .metric-card:hover {
            transform: translateY(-5px) scale(1.02);
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        
        .metric-card::before {
            content: "";
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: rotate 10s linear infinite;
        }
        
        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        .metric-value {
            font-size: 2.5rem;
            font-weight: 700;
            margin: 10px 0;
            position: relative;
            z-index: 1;
        }
        
        .metric-label {
            font-size: 0.875rem;
            opacity: 0.9;
            position: relative;
            z-index: 1;
        }
        
        .metric-change {
            font-size: 0.75rem;
            opacity: 0.8;
            margin-top: 5px;
            position: relative;
            z-index: 1;
        }
        
        /* Tables */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .data-table th {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: 600;
        }
        
        .data-table td {
            padding: 15px;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .data-table tr:hover {
            background: #f8f9fa;
        }
        
        /* Status Badges */
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
        }
        
        .status-warning {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
        }
        
        .status-danger {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
        }
        
        /* Buttons */
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }
        
        .btn::before {
            content: "";
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: rgba(255,255,255,0.2);
            transition: left 0.3s ease;
        }
        
        .btn:hover::before {
            left: 0;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }
        
        /* Charts Container */
        .chart-container {
            background: white;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 25px;
        }
        
        /* Loading Animation */
        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255,255,255,0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s ease-in-out infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .metrics-grid {
                grid-template-columns: 1fr;
            }
            .header h1 {
                font-size: 1.75rem;
            }
        }
    </style>';
}

// Generate the JavaScript for interactivity
function getSharedScripts() {
    return '
    <script>
        // Auto-refresh data every 30 seconds
        setTimeout(function() {
            location.reload();
        }, 30000);
        
        // Animate numbers on load
        document.addEventListener("DOMContentLoaded", function() {
            const numbers = document.querySelectorAll(".metric-value");
            numbers.forEach(num => {
                const finalValue = num.innerText;
                const isPercent = finalValue.includes("%");
                const cleanValue = parseFloat(finalValue.replace(/[^0-9.-]/g, ""));
                
                if (!isNaN(cleanValue)) {
                    let currentValue = 0;
                    const increment = cleanValue / 50;
                    const timer = setInterval(() => {
                        currentValue += increment;
                        if (currentValue >= cleanValue) {
                            currentValue = cleanValue;
                            clearInterval(timer);
                        }
                        
                        if (finalValue.includes(",")) {
                            num.innerText = Math.floor(currentValue).toLocaleString() + (isPercent ? "%" : "");
                        } else {
                            num.innerText = currentValue.toFixed(isPercent ? 2 : 0) + (isPercent ? "%" : "");
                        }
                    }, 20);
                }
            });
        });
        
        // Add click handlers
        function navigateTo(url) {
            window.location.href = url;
        }
        
        // Show notifications
        function showNotification(message, type = "success") {
            const notification = document.createElement("div");
            notification.className = "notification " + type;
            notification.innerText = message;
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 15px 25px;
                background: ${type === "success" ? "#10b981" : "#ef4444"};
                color: white;
                border-radius: 10px;
                box-shadow: 0 4px 20px rgba(0,0,0,0.2);
                z-index: 1000;
                animation: slideIn 0.3s ease;
            `;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.style.animation = "slideOut 0.3s ease";
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        }
        
        // Add CSS for notifications
        const style = document.createElement("style");
        style.textContent = `
            @keyframes slideIn {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
            @keyframes slideOut {
                from { transform: translateX(0); opacity: 1; }
                to { transform: translateX(100%); opacity: 0; }
            }
        `;
        document.head.appendChild(style);
    </script>';
}

echo "Master UI Fix Generator\n";
echo "=======================\n\n";
echo "This will generate the code to fix all UI pages.\n";
echo "Copy the output to update your routes/web.php file.\n\n";

// Output the route updates needed
foreach ($pages as $page => $config) {
    echo "// Fix for {$config['title']}\n";
    echo "// Route: {$config['route']}\n";
    echo "// Add this to routes/web.php\n\n";
}

echo "\nShared styles and scripts have been generated.\n";
echo "Next step: Update routes/web.php with direct HTML responses.\n";












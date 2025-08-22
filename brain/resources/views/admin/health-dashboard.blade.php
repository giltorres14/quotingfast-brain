@extends('layouts.app')

@section('content')
<style>
    .health-dashboard {
        padding: 20px;
        background: #f8f9fa;
        min-height: 100vh;
    }
    
    .health-header {
        background: white;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .status-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 20px;
        margin-bottom: 20px;
    }
    
    .status-card {
        background: white;
        border-radius: 10px;
        padding: 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        position: relative;
        overflow: hidden;
    }
    
    .status-card.healthy {
        border-left: 5px solid #28a745;
    }
    
    .status-card.warning {
        border-left: 5px solid #ffc107;
        animation: pulse-warning 2s infinite;
    }
    
    .status-card.critical {
        border-left: 5px solid #dc3545;
        animation: flash 1s infinite;
    }
    
    @keyframes flash {
        0%, 50%, 100% { 
            background: white; 
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        25%, 75% { 
            background: #ffebee; 
            box-shadow: 0 0 20px rgba(220, 53, 69, 0.5);
        }
    }
    
    @keyframes pulse-warning {
        0%, 100% { 
            background: white; 
        }
        50% { 
            background: #fff8e1; 
        }
    }
    
    .status-icon {
        font-size: 24px;
        margin-right: 10px;
    }
    
    .status-title {
        font-size: 18px;
        font-weight: bold;
        margin-bottom: 10px;
        display: flex;
        align-items: center;
    }
    
    .status-details {
        color: #666;
        font-size: 14px;
        margin-top: 10px;
    }
    
    .critical-alert {
        background: #dc3545;
        color: white;
        padding: 15px;
        border-radius: 10px;
        margin-bottom: 20px;
        animation: flash-alert 1s infinite;
    }
    
    @keyframes flash-alert {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.7; }
    }
    
    .critical-alert h2 {
        margin: 0 0 10px 0;
        display: flex;
        align-items: center;
    }
    
    .refresh-btn {
        background: #007bff;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 5px;
        cursor: pointer;
        font-size: 16px;
    }
    
    .refresh-btn:hover {
        background: #0056b3;
    }
    
    .last-check {
        color: #666;
        font-size: 14px;
    }
    
    .metric-value {
        font-size: 24px;
        font-weight: bold;
        color: #333;
        margin: 5px 0;
    }
    
    .auto-refresh {
        position: fixed;
        bottom: 20px;
        right: 20px;
        background: white;
        padding: 10px;
        border-radius: 5px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.2);
    }
</style>

<div class="health-dashboard">
    <div class="health-header">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h1 style="margin: 0;">🏥 System Health Dashboard</h1>
            <div>
                <span class="last-check" id="last-check">Last check: Never</span>
                <button class="refresh-btn" onclick="refreshHealth()">🔄 Refresh Now</button>
            </div>
        </div>
    </div>
    
    <div id="critical-alerts"></div>
    
    <div class="status-grid" id="status-grid">
        <!-- Status cards will be inserted here -->
    </div>
    
    <div class="auto-refresh">
        <label>
            <input type="checkbox" id="auto-refresh" checked> Auto-refresh (30s)
        </label>
    </div>
</div>

<script>
let autoRefreshInterval;

function refreshHealth() {
    fetch('/api/system-health')
        .then(response => response.json())
        .then(data => {
            updateDashboard(data);
        })
        .catch(error => {
            console.error('Error fetching health status:', error);
        });
}

function updateDashboard(data) {
    // Update last check time
    document.getElementById('last-check').textContent = 'Last check: ' + new Date().toLocaleTimeString();
    
    // Handle critical alerts
    const alertsDiv = document.getElementById('critical-alerts');
    if (data.issues && data.issues.length > 0) {
        alertsDiv.innerHTML = `
            <div class="critical-alert">
                <h2>🚨 CRITICAL ISSUES DETECTED!</h2>
                <ul style="margin: 0; padding-left: 20px;">
                    ${data.issues.map(issue => `<li>${issue}</li>`).join('')}
                </ul>
            </div>
        `;
        
        // Play alert sound (optional)
        playAlertSound();
        
        // Change page title to alert
        document.title = '🚨 CRITICAL - System Health Issues!';
    } else {
        alertsDiv.innerHTML = '';
        document.title = '✅ System Health Dashboard';
    }
    
    // Update status cards
    const grid = document.getElementById('status-grid');
    grid.innerHTML = '';
    
    // Lead Import Card
    addStatusCard(grid, {
        title: '📥 Lead Import (Endpoints)',
        status: data.components?.lead_import?.status || 'unknown',
        metrics: [
            { label: 'Last Lead', value: formatTime(data.components?.lead_import?.last_activity) },
            { label: 'Leads (1hr)', value: data.components?.lead_import?.recent_count || 0 }
        ]
    });
    
    // Vici Push Card
    addStatusCard(grid, {
        title: '📤 Vici Push',
        status: data.components?.vici_push?.status || 'unknown',
        metrics: [
            { label: 'Recent Pushes', value: data.components?.vici_push?.recent_pushes || 0 },
            { label: 'Unpushed', value: data.components?.vici_push?.unpushed_leads || 0 }
        ]
    });
    
    // Call Import Card
    addStatusCard(grid, {
        title: '📞 Call Log Import',
        status: data.components?.call_import?.status || 'unknown',
        metrics: [
            { label: 'Last Import', value: formatTime(data.components?.call_import?.last_import) },
            { label: 'Recent Calls', value: data.components?.call_import?.recent_count || 0 }
        ]
    });
    
    // Vici Connection Card
    addStatusCard(grid, {
        title: '🔌 Vici Database',
        status: data.components?.vici_connection?.status || 'unknown',
        metrics: [
            { label: 'Connection', value: data.components?.vici_connection?.status === 'healthy' ? 'Active' : 'Failed' }
        ]
    });
    
    // Lead Flow Card
    addStatusCard(grid, {
        title: '🔄 Lead Flow',
        status: data.components?.lead_flow?.status || 'unknown',
        metrics: [
            { label: 'Movements (1hr)', value: data.components?.lead_flow?.recent_movements || 0 }
        ]
    });
    
    // Scheduler Card
    addStatusCard(grid, {
        title: '⏰ Cron Scheduler',
        status: data.components?.scheduler?.status || 'unknown',
        metrics: [
            { label: 'Last Run', value: formatTime(data.components?.scheduler?.last_run) }
        ]
    });
}

function addStatusCard(container, config) {
    const card = document.createElement('div');
    card.className = `status-card ${config.status}`;
    
    let icon = '❓';
    if (config.status === 'healthy') icon = '✅';
    else if (config.status === 'warning') icon = '⚠️';
    else if (config.status === 'critical') icon = '🚨';
    
    card.innerHTML = `
        <div class="status-title">
            <span class="status-icon">${icon}</span>
            ${config.title}
        </div>
        <div class="status-details">
            ${config.metrics.map(m => `
                <div>
                    <small>${m.label}:</small>
                    <div class="metric-value">${m.value}</div>
                </div>
            `).join('')}
        </div>
    `;
    
    container.appendChild(card);
}

function formatTime(timestamp) {
    if (!timestamp || timestamp === 'Never') return 'Never';
    const date = new Date(timestamp);
    const now = new Date();
    const diff = Math.floor((now - date) / 1000 / 60); // minutes
    
    if (diff < 1) return 'Just now';
    if (diff < 60) return `${diff}m ago`;
    if (diff < 1440) return `${Math.floor(diff/60)}h ago`;
    return `${Math.floor(diff/1440)}d ago`;
}

function playAlertSound() {
    // Optional: Play an alert sound
    const audio = new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBi');
    audio.volume = 0.3;
    audio.play().catch(() => {}); // Ignore if audio fails
}

// Auto-refresh setup
function setupAutoRefresh() {
    const checkbox = document.getElementById('auto-refresh');
    
    if (checkbox.checked) {
        autoRefreshInterval = setInterval(refreshHealth, 30000); // 30 seconds
    } else {
        clearInterval(autoRefreshInterval);
    }
}

document.getElementById('auto-refresh').addEventListener('change', setupAutoRefresh);

// Initial load
refreshHealth();
setupAutoRefresh();
</script>
@endsection






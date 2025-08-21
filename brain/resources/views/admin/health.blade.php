@extends('layouts.unified-nav')

@section('title', 'System Health Dashboard')

@section('content')
<style>
    .health-dashboard {
        max-width: 1400px;
        margin: 20px auto;
        padding: 0 20px;
    }
    
    .health-header {
        background: white;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    
    .health-title {
        font-size: 28px;
        font-weight: bold;
        margin-bottom: 10px;
    }
    
    .last-check {
        color: #666;
        font-size: 14px;
    }
    
    .refresh-btn {
        float: right;
        background: #667eea;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 5px;
        cursor: pointer;
        font-weight: bold;
    }
    
    .refresh-btn:hover {
        background: #5a67d8;
    }
    
    .health-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
        gap: 20px;
        margin-bottom: 20px;
    }
    
    .health-card {
        background: white;
        border-radius: 10px;
        padding: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        position: relative;
        overflow: hidden;
    }
    
    .health-card.healthy {
        border-left: 5px solid #10b981;
    }
    
    .health-card.warning {
        border-left: 5px solid #f59e0b;
        background: #fffbeb;
    }
    
    .health-card.critical {
        border-left: 5px solid #ef4444;
        background: #fef2f2;
        animation: criticalPulse 2s infinite;
    }
    
    @keyframes criticalPulse {
        0%, 100% { background: #fef2f2; }
        50% { background: #fee2e2; }
    }
    
    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
    }
    
    .card-title {
        font-size: 18px;
        font-weight: bold;
    }
    
    .status-badge {
        padding: 5px 15px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: bold;
        text-transform: uppercase;
    }
    
    .status-badge.healthy {
        background: #d1fae5;
        color: #065f46;
    }
    
    .status-badge.warning {
        background: #fed7aa;
        color: #92400e;
    }
    
    .status-badge.critical {
        background: #fee2e2;
        color: #991b1b;
        animation: flash 1s infinite;
    }
    
    @keyframes flash {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
    }
    
    .card-details {
        font-size: 14px;
        color: #666;
        line-height: 1.6;
    }
    
    .detail-row {
        display: flex;
        justify-content: space-between;
        padding: 5px 0;
        border-bottom: 1px solid #f0f0f0;
    }
    
    .detail-label {
        font-weight: 500;
    }
    
    .detail-value {
        font-weight: bold;
    }
    
    .critical-alert {
        background: #ef4444;
        color: white;
        padding: 15px;
        border-radius: 10px;
        margin-bottom: 20px;
        animation: alertFlash 1s infinite;
    }
    
    @keyframes alertFlash {
        0%, 100% { background: #ef4444; }
        50% { background: #dc2626; }
    }
    
    .critical-alert h3 {
        margin-bottom: 10px;
        font-size: 20px;
    }
    
    .critical-alert ul {
        margin: 0;
        padding-left: 20px;
    }
    
    .critical-alert li {
        margin: 5px 0;
    }
    
    .action-buttons {
        margin-top: 20px;
        display: flex;
        gap: 10px;
    }
    
    .action-btn {
        padding: 10px 20px;
        border-radius: 5px;
        border: none;
        cursor: pointer;
        font-weight: bold;
        text-decoration: none;
        display: inline-block;
    }
    
    .action-btn.primary {
        background: #667eea;
        color: white;
    }
    
    .action-btn.danger {
        background: #ef4444;
        color: white;
    }
</style>

<div class="health-dashboard">
    <div class="health-header">
        <button class="refresh-btn" onclick="location.reload()">🔄 Refresh</button>
        <h1 class="health-title">System Health Dashboard</h1>
        <div class="last-check">Last checked: <span id="last-check">{{ now()->format('Y-m-d H:i:s') }}</span></div>
    </div>
    
    @php
        $healthData = Cache::get('system_health_status', [
            'status' => 'unknown',
            'components' => [],
            'issues' => []
        ]);
    @endphp
    
    @if(!empty($healthData['issues']))
    <div class="critical-alert">
        <h3>🚨 CRITICAL ISSUES DETECTED</h3>
        <ul>
            @foreach($healthData['issues'] as $issue)
                <li>{{ $issue }}</li>
            @endforeach
        </ul>
        <div class="action-buttons">
            <a href="/leads" class="action-btn primary">Check Leads</a>
            <a href="/vici" class="action-btn primary">Check Vici</a>
            <button class="action-btn danger" onclick="runHealthCheck()">Run Full Check</button>
        </div>
    </div>
    @endif
    
    <div class="health-grid">
        <!-- Lead Import Card -->
        <div class="health-card {{ $healthData['components']['lead_import']['status'] ?? 'unknown' }}">
            <div class="card-header">
                <div class="card-title">📥 Lead Import (Endpoints)</div>
                <div class="status-badge {{ $healthData['components']['lead_import']['status'] ?? 'unknown' }}">
                    {{ $healthData['components']['lead_import']['status'] ?? 'UNKNOWN' }}
                </div>
            </div>
            <div class="card-details">
                <div class="detail-row">
                    <span class="detail-label">Last Lead:</span>
                    <span class="detail-value">{{ $healthData['components']['lead_import']['last_activity'] ?? 'N/A' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Leads (1hr):</span>
                    <span class="detail-value">{{ $healthData['components']['lead_import']['recent_count'] ?? 0 }}</span>
                </div>
            </div>
        </div>
        
        <!-- Vici Push Card -->
        <div class="health-card {{ $healthData['components']['vici_push']['status'] ?? 'unknown' }}">
            <div class="card-header">
                <div class="card-title">🚀 Vici Push</div>
                <div class="status-badge {{ $healthData['components']['vici_push']['status'] ?? 'unknown' }}">
                    {{ $healthData['components']['vici_push']['status'] ?? 'UNKNOWN' }}
                </div>
            </div>
            <div class="card-details">
                <div class="detail-row">
                    <span class="detail-label">Recent Pushes:</span>
                    <span class="detail-value">{{ $healthData['components']['vici_push']['recent_pushes'] ?? 0 }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Unpushed Leads:</span>
                    <span class="detail-value">{{ $healthData['components']['vici_push']['unpushed_leads'] ?? 0 }}</span>
                </div>
            </div>
        </div>
        
        <!-- Call Import Card -->
        <div class="health-card {{ $healthData['components']['call_import']['status'] ?? 'unknown' }}">
            <div class="card-header">
                <div class="card-title">📞 Call Log Import</div>
                <div class="status-badge {{ $healthData['components']['call_import']['status'] ?? 'unknown' }}">
                    {{ $healthData['components']['call_import']['status'] ?? 'UNKNOWN' }}
                </div>
            </div>
            <div class="card-details">
                <div class="detail-row">
                    <span class="detail-label">Last Import:</span>
                    <span class="detail-value">{{ $healthData['components']['call_import']['last_import'] ?? 'N/A' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Recent Calls:</span>
                    <span class="detail-value">{{ $healthData['components']['call_import']['recent_count'] ?? 0 }}</span>
                </div>
            </div>
        </div>
        
        <!-- Vici Connection Card -->
        <div class="health-card {{ $healthData['components']['vici_connection']['status'] ?? 'unknown' }}">
            <div class="card-header">
                <div class="card-title">🔌 Vici Database</div>
                <div class="status-badge {{ $healthData['components']['vici_connection']['status'] ?? 'unknown' }}">
                    {{ $healthData['components']['vici_connection']['status'] ?? 'UNKNOWN' }}
                </div>
            </div>
            <div class="card-details">
                <div class="detail-row">
                    <span class="detail-label">Connection:</span>
                    <span class="detail-value">
                        {{ $healthData['components']['vici_connection']['status'] === 'healthy' ? 'Active' : 'Failed' }}
                    </span>
                </div>
            </div>
        </div>
        
        <!-- Lead Flow Card -->
        <div class="health-card {{ $healthData['components']['lead_flow']['status'] ?? 'unknown' }}">
            <div class="card-header">
                <div class="card-title">🔄 Lead Flow Movement</div>
                <div class="status-badge {{ $healthData['components']['lead_flow']['status'] ?? 'unknown' }}">
                    {{ $healthData['components']['lead_flow']['status'] ?? 'UNKNOWN' }}
                </div>
            </div>
            <div class="card-details">
                <div class="detail-row">
                    <span class="detail-label">Movements (1hr):</span>
                    <span class="detail-value">{{ $healthData['components']['lead_flow']['recent_movements'] ?? 0 }}</span>
                </div>
            </div>
        </div>
        
        <!-- Scheduler Card -->
        <div class="health-card {{ $healthData['components']['scheduler']['status'] ?? 'unknown' }}">
            <div class="card-header">
                <div class="card-title">⏰ Cron Scheduler</div>
                <div class="status-badge {{ $healthData['components']['scheduler']['status'] ?? 'unknown' }}">
                    {{ $healthData['components']['scheduler']['status'] ?? 'UNKNOWN' }}
                </div>
            </div>
            <div class="card-details">
                <div class="detail-row">
                    <span class="detail-label">Last Run:</span>
                    <span class="detail-value">{{ $healthData['components']['scheduler']['last_run'] ?? 'Never' }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function runHealthCheck() {
    fetch('/api/health-check', { method: 'POST' })
        .then(() => location.reload());
}

// Auto-refresh every 60 seconds
setTimeout(() => location.reload(), 60000);
</script>
@endsection



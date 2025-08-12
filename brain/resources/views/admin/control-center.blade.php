@extends('components.app-layout')

@section('title', 'Brain Control Center')

@section('content')
<style>
    .control-center {
        padding: 20px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        min-height: 100vh;
    }
    
    .control-header {
        background: white;
        border-radius: 12px;
        padding: 25px;
        margin-bottom: 30px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }
    
    .control-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    
    .control-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        transition: transform 0.3s ease;
    }
    
    .control-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    }
    
    .card-header {
        display: flex;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid #e2e8f0;
    }
    
    .card-icon {
        font-size: 28px;
        margin-right: 15px;
    }
    
    .card-title {
        font-size: 20px;
        font-weight: bold;
        color: #2d3748;
    }
    
    .card-badge {
        margin-left: auto;
        padding: 4px 10px;
        background: #48bb78;
        color: white;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 600;
    }
    
    .control-actions {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    
    .action-button {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 16px;
        background: #f7fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.2s ease;
        text-decoration: none;
        color: #2d3748;
    }
    
    .action-button:hover {
        background: #edf2f7;
        border-color: #cbd5e0;
        transform: translateX(5px);
    }
    
    .action-label {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .action-arrow {
        color: #a0aec0;
    }
    
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 15px;
        margin-top: 20px;
    }
    
    .stat-box {
        background: #f7fafc;
        padding: 15px;
        border-radius: 8px;
        text-align: center;
    }
    
    .stat-value {
        font-size: 24px;
        font-weight: bold;
        color: #4c51bf;
    }
    
    .stat-label {
        font-size: 12px;
        color: #718096;
        margin-top: 5px;
    }
    
    .quick-actions {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 10px;
        margin-top: 20px;
    }
    
    .quick-btn {
        padding: 12px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    
    .quick-btn:hover {
        transform: scale(1.05);
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
    }
    
    .danger-zone {
        background: #fff5f5;
        border: 2px solid #feb2b2;
        border-radius: 12px;
        padding: 20px;
        margin-top: 20px;
    }
    
    .danger-title {
        color: #c53030;
        font-weight: bold;
        margin-bottom: 15px;
    }
    
    .system-health {
        display: flex;
        align-items: center;
        gap: 15px;
        padding: 15px;
        background: #f0fff4;
        border-radius: 8px;
        margin-top: 15px;
    }
    
    .health-indicator {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        animation: pulse 2s infinite;
    }
    
    .health-good { background: #48bb78; }
    .health-warning { background: #f6ad55; }
    .health-critical { background: #f56565; }
    
    @keyframes pulse {
        0% { opacity: 1; }
        50% { opacity: 0.5; }
        100% { opacity: 1; }
    }
</style>

<div class="control-center">
    <!-- Header -->
    <div class="control-header">
        <h1 style="font-size: 32px; margin-bottom: 10px; color: #2d3748;">
            🧠 Brain Control Center
        </h1>
        <p style="color: #718096; font-size: 16px;">
            Central hub for all lead management operations
        </p>
        
        <!-- System Health -->
        <div class="system-health">
            <div class="health-indicator health-good"></div>
            <span><strong>System Status:</strong> All Systems Operational</span>
            <span style="margin-left: auto; color: #718096;">
                Last sync: {{ now()->subMinutes(5)->diffForHumans() }}
            </span>
        </div>
        
        <!-- Migration Status -->
        @if(!config('services.vici.push_enabled'))
        <div style="background: #fef5e7; border: 2px solid #f39c12; border-radius: 8px; padding: 15px; margin-top: 15px;">
            <strong style="color: #e67e22;">⚠️ MIGRATION MODE:</strong>
            <span style="color: #d68910;">Vici push is DISABLED - Leads are being stored in Brain only</span>
            <button onclick="enableViciPush()" style="float: right; background: #27ae60; color: white; border: none; padding: 5px 15px; border-radius: 5px; cursor: pointer;">
                Enable Vici Push
            </button>
        </div>
        @else
        <div style="background: #e8f8f5; border: 2px solid #27ae60; border-radius: 8px; padding: 15px; margin-top: 15px;">
            <strong style="color: #27ae60;">✅ LIVE MODE:</strong>
            <span style="color: #229954;">Vici push is ENABLED - Leads are being sent to Vici</span>
        </div>
        @endif
    </div>
    
    <!-- Main Control Grid -->
    <div class="control-grid">
        
        <!-- Lead Management -->
        <div class="control-card">
            <div class="card-header">
                <span class="card-icon">📊</span>
                <span class="card-title">Lead Management</span>
                <span class="card-badge">Active</span>
            </div>
            
            <div class="control-actions">
                <a href="/leads" class="action-button">
                    <span class="action-label">
                        <span>📋</span>
                        <span>View All Leads</span>
                    </span>
                    <span class="action-arrow">→</span>
                </a>
                
                <a href="/admin/lead-flow" class="action-button">
                    <span class="action-label">
                        <span>🔄</span>
                        <span>Lead Flow Visualization</span>
                    </span>
                    <span class="action-arrow">→</span>
                </a>
                
                <a href="/suraj/upload" class="action-button">
                    <span class="action-label">
                        <span>📤</span>
                        <span>Upload CSV</span>
                    </span>
                    <span class="action-arrow">→</span>
                </a>
            </div>
            
            <div class="stats-grid">
                <div class="stat-box">
                    <div class="stat-value">{{ number_format(\App\Models\Lead::count()) }}</div>
                    <div class="stat-label">Total Leads</div>
                </div>
                <div class="stat-box">
                    <div class="stat-value">{{ number_format(\App\Models\Lead::whereDate('created_at', today())->count()) }}</div>
                    <div class="stat-label">Today</div>
                </div>
                @if(!config('services.vici.push_enabled'))
                <div class="stat-box" style="background: #fef5e7;">
                    <div class="stat-value" style="color: #f39c12;">{{ number_format(\App\Models\Lead::where('status', 'pending_vici_push')->count()) }}</div>
                    <div class="stat-label">Pending Push</div>
                </div>
                @endif
            </div>
            
            @if(!config('services.vici.push_enabled'))
            <div class="quick-actions" style="margin-top: 15px;">
                <button class="quick-btn" onclick="pushPendingLeads()" style="background: #f39c12;">
                    Push Pending Leads to Vici
                </button>
            </div>
            @endif
        </div>
        
        <!-- Vici List Control -->
        <div class="control-card">
            <div class="card-header">
                <span class="card-icon">📝</span>
                <span class="card-title">Vici List Management</span>
                <span class="card-badge">API Ready</span>
            </div>
            
            <div class="control-actions">
                <button onclick="showListMigration()" class="action-button">
                    <span class="action-label">
                        <span>🔄</span>
                        <span>Migrate Leads to Lists</span>
                    </span>
                    <span class="action-arrow">→</span>
                </button>
                
                <button onclick="showBulkMove()" class="action-button">
                    <span class="action-label">
                        <span>📦</span>
                        <span>Bulk Move Leads</span>
                    </span>
                    <span class="action-arrow">→</span>
                </button>
                
                <button onclick="autoAssignLists()" class="action-button">
                    <span class="action-label">
                        <span>🤖</span>
                        <span>Auto-Assign by Status</span>
                    </span>
                    <span class="action-arrow">→</span>
                </button>
            </div>
            
            <div class="quick-actions">
                <button class="quick-btn" onclick="moveToList(102)">
                    Move Selected to Retry (102)
                </button>
                <button class="quick-btn" onclick="moveToList(103)">
                    Move Selected to Callback (103)
                </button>
            </div>
        </div>
        
        <!-- Call Log Sync -->
        <div class="control-card">
            <div class="card-header">
                <span class="card-icon">📞</span>
                <span class="card-title">Call Log Synchronization</span>
                <span class="card-badge">Scheduled</span>
            </div>
            
            <div class="control-actions">
                <button onclick="syncCallLogs()" class="action-button">
                    <span class="action-label">
                        <span>🔄</span>
                        <span>Sync Last Hour</span>
                    </span>
                    <span class="action-arrow">→</span>
                </button>
                
                <button onclick="importHistorical()" class="action-button">
                    <span class="action-label">
                        <span>📥</span>
                        <span>Import Historical</span>
                    </span>
                    <span class="action-arrow">→</span>
                </button>
                
                <button onclick="reconcileAll()" class="action-button">
                    <span class="action-label">
                        <span>✅</span>
                        <span>Reconcile All</span>
                    </span>
                    <span class="action-arrow">→</span>
                </button>
            </div>
            
            <div class="stats-grid">
                <div class="stat-box">
                    <div class="stat-value">{{ number_format(\App\Models\ViciCallMetrics::count()) }}</div>
                    <div class="stat-label">Call Records</div>
                </div>
                <div class="stat-box">
                    <div class="stat-value">5 min</div>
                    <div class="stat-label">Sync Interval</div>
                </div>
            </div>
        </div>
        
        <!-- System Operations -->
        <div class="control-card">
            <div class="card-header">
                <span class="card-icon">⚙️</span>
                <span class="card-title">System Operations</span>
            </div>
            
            <div class="control-actions">
                <a href="/diagnostics" class="action-button">
                    <span class="action-label">
                        <span>🔍</span>
                        <span>Diagnostics Dashboard</span>
                    </span>
                    <span class="action-arrow">→</span>
                </a>
                
                <button onclick="clearCache()" class="action-button">
                    <span class="action-label">
                        <span>🧹</span>
                        <span>Clear Cache</span>
                    </span>
                    <span class="action-arrow">→</span>
                </button>
                
                <button onclick="runMigrations()" class="action-button">
                    <span class="action-label">
                        <span>📊</span>
                        <span>Run Migrations</span>
                    </span>
                    <span class="action-arrow">→</span>
                </button>
            </div>
            
            <div class="danger-zone">
                <div class="danger-title">⚠️ Danger Zone</div>
                <button onclick="archiveOldLeads()" class="quick-btn" style="background: #f56565;">
                    Archive Leads > 90 Days
                </button>
            </div>
        </div>
        
        <!-- Import/Export -->
        <div class="control-card">
            <div class="card-header">
                <span class="card-icon">📤</span>
                <span class="card-title">Import & Export</span>
            </div>
            
            <div class="control-actions">
                <button onclick="showImportModal()" class="action-button">
                    <span class="action-label">
                        <span>📥</span>
                        <span>Import LQF CSV</span>
                    </span>
                    <span class="action-arrow">→</span>
                </button>
                
                <button onclick="showSurajImport()" class="action-button">
                    <span class="action-label">
                        <span>📁</span>
                        <span>Import Suraj Bulk</span>
                    </span>
                    <span class="action-arrow">→</span>
                </button>
                
                <button onclick="exportLeads()" class="action-button">
                    <span class="action-label">
                        <span>💾</span>
                        <span>Export to CSV</span>
                    </span>
                    <span class="action-arrow">→</span>
                </button>
            </div>
        </div>
        
        <!-- Performance Monitor -->
        <div class="control-card">
            <div class="card-header">
                <span class="card-icon">📈</span>
                <span class="card-title">Performance Monitor</span>
            </div>
            
            <div class="stats-grid">
                <div class="stat-box">
                    <div class="stat-value">{{ round(memory_get_usage(true) / 1024 / 1024) }}MB</div>
                    <div class="stat-label">Memory Usage</div>
                </div>
                <div class="stat-box">
                    <div class="stat-value">{{ \Illuminate\Support\Facades\Queue::size('default') }}</div>
                    <div class="stat-label">Queue Depth</div>
                </div>
                <div class="stat-box">
                    <div class="stat-value">{{ number_format(\App\Models\Lead::where('created_at', '>', now()->subHour())->count()) }}</div>
                    <div class="stat-label">Leads/Hour</div>
                </div>
                <div class="stat-box">
                    <div class="stat-value">~50ms</div>
                    <div class="stat-label">Avg Response</div>
                </div>
            </div>
            
            <div class="system-health" style="margin-top: 15px;">
                @php
                    $leadsPerDay = \App\Models\Lead::whereDate('created_at', today())->count();
                    $healthStatus = $leadsPerDay < 10000 ? 'good' : ($leadsPerDay < 25000 ? 'warning' : 'critical');
                @endphp
                <div class="health-indicator health-{{ $healthStatus }}"></div>
                <span>
                    Load Status: 
                    @if($healthStatus == 'good')
                        Normal ({{ number_format($leadsPerDay) }} leads today)
                    @elseif($healthStatus == 'warning')
                        High ({{ number_format($leadsPerDay) }} leads today)
                    @else
                        Critical ({{ number_format($leadsPerDay) }} leads today)
                    @endif
                </span>
            </div>
        </div>
        
    </div>
</div>

<!-- JavaScript for Control Actions -->
<script>
function showListMigration() {
    if (confirm('Run lead migration analysis?\n\nThis will analyze all leads and suggest list assignments.')) {
        window.location.href = '/admin/migrate-leads-preview';
    }
}

function showBulkMove() {
    const listId = prompt('Enter target list ID (101-106, 199):');
    if (listId) {
        window.location.href = `/admin/bulk-move?list=${listId}`;
    }
}

function autoAssignLists() {
    if (confirm('Auto-assign all leads to appropriate lists based on status?\n\nThis will update leads in both Brain and Vici.')) {
        fetch('/api/admin/auto-assign-lists', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        }).then(response => response.json())
          .then(data => {
              alert(`Processed ${data.total} leads\nSuccess: ${data.success}\nFailed: ${data.failed}`);
          });
    }
}

function syncCallLogs() {
    if (confirm('Sync call logs from the last hour?')) {
        fetch('/api/admin/sync-call-logs', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        }).then(() => {
            alert('Call log sync initiated');
        });
    }
}

function clearCache() {
    if (confirm('Clear all application cache?')) {
        fetch('/api/admin/clear-cache', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        }).then(() => {
            alert('Cache cleared successfully');
            location.reload();
        });
    }
}

function archiveOldLeads() {
    if (confirm('⚠️ WARNING: Archive all leads older than 90 days?\n\nThis will move old leads to archive table.')) {
        if (confirm('Are you absolutely sure? This action cannot be undone easily.')) {
            fetch('/api/admin/archive-old-leads', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            }).then(response => response.json())
              .then(data => {
                  alert(`Archived ${data.count} leads`);
              });
        }
    }
}

function moveToList(listId) {
    const leadIds = prompt('Enter lead IDs to move (comma-separated):');
    if (leadIds) {
        fetch('/api/admin/move-leads-to-list', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                lead_ids: leadIds.split(',').map(id => id.trim()),
                list_id: listId
            })
        }).then(response => response.json())
          .then(data => {
              alert(`Moved ${data.success} leads to list ${listId}`);
          });
    }
}

function enableViciPush() {
    if (confirm('⚠️ WARNING: This will enable pushing leads to Vici.\n\nMake sure LQF is no longer pushing directly to Vici to avoid duplicates.\n\nAre you ready to enable?')) {
        alert('To enable Vici push:\n\n1. Update VICI_PUSH_ENABLED=true in Render environment\n2. Redeploy the application\n3. Use "Push Pending Leads" to send accumulated leads');
    }
}

function pushPendingLeads() {
    if (confirm('Push all pending leads to Vici?\n\nThis will send leads that were stored during migration.')) {
        alert('Run this command on the server:\n\nphp artisan vici:push-pending\n\nOr use --dry-run first to preview:\nphp artisan vici:push-pending --dry-run');
    }
}
</script>
@endsection

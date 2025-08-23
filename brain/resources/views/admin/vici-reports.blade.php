<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vici Call Reports - QuotingFast Brain</title>
    <link rel="icon" type="image/png" href="/favicon.png">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
        }
        
        .header {
            background: white;
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .header-left {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .logo {
            height: 60px;
            width: auto;
        }
        
        h1 {
            color: #1a202c;
            font-size: 2rem;
            font-weight: 700;
        }
        
        .subtitle {
            color: #718096;
            font-size: 1rem;
            margin-top: 5px;
        }
        
        .header-actions {
            display: flex;
            gap: 15px;
        }
        
        .btn {
            padding: 12px 24px;
            border-radius: 10px;
            border: none;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .btn-secondary {
            background: #e2e8f0;
            color: #4a5568;
        }
        
        .btn-secondary:hover {
            background: #cbd5e0;
        }
        
        .btn-success {
            background: linear-gradient(135deg, #48bb78, #38a169);
            color: white;
        }
        
        .btn-warning {
            background: linear-gradient(135deg, #f6ad55, #ed8936);
            color: white;
        }
        
        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 15px;
        }
        
        .stat-icon.blue { background: linear-gradient(135deg, #667eea, #764ba2); }
        .stat-icon.green { background: linear-gradient(135deg, #48bb78, #38a169); }
        .stat-icon.orange { background: linear-gradient(135deg, #f6ad55, #ed8936); }
        .stat-icon.red { background: linear-gradient(135deg, #fc8181, #f56565); }
        .stat-icon.purple { background: linear-gradient(135deg, #9f7aea, #805ad5); }
        
        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: #1a202c;
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: #718096;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .stat-change {
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid #e2e8f0;
            font-size: 0.85rem;
        }
        
        .stat-change.positive { color: #48bb78; }
        .stat-change.negative { color: #f56565; }
        
        /* Tabs */
        .tabs-container {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        
        .tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 0;
        }
        
        .tab {
            padding: 15px 25px;
            background: none;
            border: none;
            color: #718096;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            border-bottom: 3px solid transparent;
            margin-bottom: -2px;
        }
        
        .tab:hover {
            color: #4a5568;
        }
        
        .tab.active {
            color: #667eea;
            border-bottom-color: #667eea;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        /* Table Styles */
        .table-container {
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th {
            background: #f7fafc;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #4a5568;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #e2e8f0;
        }
        
        td {
            padding: 15px;
            border-bottom: 1px solid #e2e8f0;
        }
        
        tr:hover {
            background: #f7fafc;
        }
        
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            display: inline-block;
        }
        
        .status-connected { background: #c6f6d5; color: #22543d; }
        .status-no-answer { background: #fed7d7; color: #742a2a; }
        .status-busy { background: #feebc8; color: #7c2d12; }
        .status-transferred { background: #bee3f8; color: #2c5282; }
        
        /* Charts */
        .chart-container {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-top: 20px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        }
        
        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .chart-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #1a202c;
        }
        
        /* Orphan Calls Section */
        .orphan-section {
            background: #fef5e7;
            border: 2px solid #f6ad55;
            border-radius: 15px;
            padding: 20px;
            margin-top: 20px;
        }
        
        .orphan-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .orphan-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #744210;
        }
        
        .orphan-count {
            background: #f6ad55;
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
        }
        
        /* Filters */
        .filters {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        
        .filter-label {
            font-size: 0.85rem;
            color: #718096;
            font-weight: 600;
        }
        
        .filter-input {
            padding: 10px 15px;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }
        
        .filter-input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        /* Loading State */
        .loading {
            text-align: center;
            padding: 40px;
            color: #718096;
        }
        
        .spinner {
            border: 3px solid #e2e8f0;
            border-top: 3px solid #667eea;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }
        
        .empty-icon {
            font-size: 4rem;
            margin-bottom: 20px;
        }
        
        .empty-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #1a202c;
            margin-bottom: 10px;
        }
        
        .empty-text {
            color: #718096;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="header-left">
                <img src="https://quotingfast.com/whitelogo" alt="QuotingFast" class="logo">
                <div>
                    <h1>📞 Vici Call Reports</h1>
                    <div class="subtitle">Monitor call performance and agent metrics</div>
                </div>
            </div>
            <div class="header-actions">
                <button class="btn btn-secondary" onclick="window.location.href='/admin'">
                    ← Back to Dashboard
                </button>
                <button class="btn btn-primary" onclick="syncCallLogs()">
                    🔄 Sync Call Logs
                </button>
                <button class="btn btn-success" onclick="processOrphanCalls()">
                    🔗 Process Orphan Calls
                </button>
            </div>
        </div>
        
        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon blue">📞</div>
                <div class="stat-value">{{ number_format($stats['total_calls'] ?? 0) }}</div>
                <div class="stat-label">Total Calls</div>
                <div class="stat-change positive">↑ {{ number_format($stats['calls_today'] ?? 0) }} today</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon green">✅</div>
                <div class="stat-value">{{ number_format($stats['connected_calls'] ?? 0) }}</div>
                <div class="stat-label">Connected Calls</div>
                <div class="stat-change positive">{{ $stats['connection_rate'] ?? 0 }}% rate</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon orange">⏱️</div>
                <div class="stat-value">{{ gmdate("i:s", $stats['avg_talk_time'] ?? 0) }}</div>
                <div class="stat-label">Avg Talk Time</div>
                <div class="stat-change">{{ number_format($stats['total_talk_hours'] ?? 0) }} total hours</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon red">📵</div>
                <div class="stat-value">{{ number_format($stats['no_answer'] ?? 0) }}</div>
                <div class="stat-label">No Answer</div>
                <div class="stat-change negative">{{ $stats['no_answer_rate'] ?? 0 }}% rate</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon purple">🔀</div>
                <div class="stat-value">{{ number_format($stats['transferred'] ?? 0) }}</div>
                <div class="stat-label">Transferred</div>
                <div class="stat-change positive">{{ $stats['transfer_rate'] ?? 0 }}% success</div>
            </div>
        </div>
        
        <!-- Orphan Calls Alert -->
        @if(($stats['orphan_calls'] ?? 0) > 0)
        <div class="orphan-section">
            <div class="orphan-header">
                <div class="orphan-title">⚠️ Unmatched Orphan Calls</div>
                <div class="orphan-count">{{ number_format($stats['orphan_calls']) }} calls</div>
            </div>
            <p style="color: #744210; margin-bottom: 15px;">
                These calls couldn't be matched to leads in the system. They may belong to leads not yet imported.
            </p>
            <button class="btn btn-warning" onclick="processOrphanCalls()">
                Process Orphan Calls Now
            </button>
        </div>
        @endif
        
        <!-- Tabs -->
        <div class="tabs-container">
            <div class="tabs">
                <button class="tab active" onclick="switchTab('recent')">Recent Calls</button>
                <button class="tab" onclick="switchTab('campaigns')">Campaign Performance</button>
                <button class="tab" onclick="switchTab('agents')">Agent Performance</button>
                <button class="tab" onclick="switchTab('hourly')">Hourly Analysis</button>
                <button class="tab" onclick="switchTab('orphans')">Orphan Calls</button>
            </div>
            
            <!-- Recent Calls Tab -->
            <div id="recent-tab" class="tab-content active">
                <div class="filters">
                    <div class="filter-group">
                        <label class="filter-label">Date Range</label>
                        <input type="date" class="filter-input" id="date-from" value="{{ request()->get('from', date('Y-m-d')) }}">
                    </div>
                    <div class="filter-group">
                        <label class="filter-label">To</label>
                        <input type="date" class="filter-input" id="date-to" value="{{ request()->get('to', date('Y-m-d')) }}">
                    </div>
                    <div class="filter-group">
                        <label class="filter-label">Status</label>
                        <select class="filter-input" id="status-filter">
                            <option value="">All Status</option>
                            <option value="connected">Connected</option>
                            <option value="no-answer">No Answer</option>
                            <option value="busy">Busy</option>
                            <option value="transferred">Transferred</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label class="filter-label">&nbsp;</label>
                        <button class="btn btn-primary" onclick="applyFilters()">Apply Filters</button>
                    </div>
                </div>
                
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Call Time</th>
                                <th>Lead</th>
                                <th>Phone</th>
                                <th>Campaign</th>
                                <th>Agent</th>
                                <th>Status</th>
                                <th>Talk Time</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="recent-calls-tbody">
                            @foreach($recentCalls ?? [] as $call)
                            <tr>
                                <td>{{ $call->first_call_time ? \Carbon\Carbon::parse($call->first_call_time)->format('M j, g:i A') : 'N/A' }}</td>
                                <td>
                                    @if($call->lead)
                                    <a href="/agent/lead/{{ $call->lead->id }}" target="_blank" style="color: #667eea; text-decoration: none; font-weight: 600;">
                                        {{ $call->lead->name }}
                                    </a>
                                    @else
                                    <span style="color: #718096;">Unknown</span>
                                    @endif
                                </td>
                                <td>{{ $call->phone_number ?? 'N/A' }}</td>
                                <td>{{ $call->campaign_id ?? 'N/A' }}</td>
                                <td>{{ $call->agent_id ?? 'N/A' }}</td>
                                <td>
                                    @php
                                        $status = $call->status ?? 'unknown';
                                        $statusClass = 'status-no-answer';
                                        if ($call->connected) $statusClass = 'status-connected';
                                        if ($call->transfer_status) $statusClass = 'status-transferred';
                                    @endphp
                                    <span class="status-badge {{ $statusClass }}">{{ ucfirst($status) }}</span>
                                </td>
                                <td>{{ $call->talk_time ? gmdate("i:s", $call->talk_time) : '-' }}</td>
                                <td>
                                    <button class="btn btn-sm" onclick="viewCallDetails({{ $call->id }})">View</button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    
                    @if(empty($recentCalls) || count($recentCalls) == 0)
                    <div class="empty-state">
                        <div class="empty-icon">📞</div>
                        <div class="empty-title">No Call Data Yet</div>
                        <div class="empty-text">Click "Sync Call Logs" to fetch data from Vici</div>
                    </div>
                    @endif
                </div>
            </div>
            
            <!-- Campaign Performance Tab -->
            <div id="campaigns-tab" class="tab-content">
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Campaign</th>
                                <th>Total Calls</th>
                                <th>Connected</th>
                                <th>Connection Rate</th>
                                <th>Avg Talk Time</th>
                                <th>Transferred</th>
                                <th>Transfer Rate</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($campaignStats ?? [] as $campaign)
                            <tr>
                                <td><strong>{{ $campaign->campaign_id }}</strong></td>
                                <td>{{ number_format($campaign->total_calls) }}</td>
                                <td>{{ number_format($campaign->connected_calls) }}</td>
                                <td>
                                    <span style="color: {{ $campaign->connection_rate > 50 ? '#48bb78' : '#f56565' }};">
                                        {{ $campaign->connection_rate }}%
                                    </span>
                                </td>
                                <td>{{ gmdate("i:s", $campaign->avg_talk_time ?? 0) }}</td>
                                <td>{{ number_format($campaign->transferred ?? 0) }}</td>
                                <td>{{ $campaign->transfer_rate ?? 0 }}%</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Agent Performance Tab -->
            <div id="agents-tab" class="tab-content">
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Agent ID</th>
                                <th>Total Calls</th>
                                <th>Connected</th>
                                <th>Total Talk Time</th>
                                <th>Avg Talk Time</th>
                                <th>Transfers</th>
                                <th>Performance</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($agentStats ?? [] as $agent)
                            <tr>
                                <td><strong>{{ $agent->agent_id }}</strong></td>
                                <td>{{ number_format($agent->total_calls) }}</td>
                                <td>{{ number_format($agent->connected_calls) }}</td>
                                <td>{{ gmdate("H:i:s", $agent->total_talk_time ?? 0) }}</td>
                                <td>{{ gmdate("i:s", $agent->avg_talk_time ?? 0) }}</td>
                                <td>{{ number_format($agent->transfers ?? 0) }}</td>
                                <td>
                                    <div style="display: flex; gap: 10px;">
                                        <span class="status-badge status-connected">
                                            {{ $agent->connection_rate ?? 0 }}% Connect
                                        </span>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Hourly Analysis Tab -->
            <div id="hourly-tab" class="tab-content">
                <div class="chart-container">
                    <div class="chart-header">
                        <div class="chart-title">Call Volume by Hour</div>
                    </div>
                    <canvas id="hourlyChart" width="400" height="200"></canvas>
                </div>
            </div>
            
            <!-- Orphan Calls Tab -->
            <div id="orphans-tab" class="tab-content">
                <div style="margin-bottom: 20px;">
                    <button class="btn btn-warning" onclick="processOrphanCalls()">
                        🔗 Process All Orphan Calls
                    </button>
                    <button class="btn btn-secondary" onclick="processOrphanCallsDryRun()">
                        👁️ Preview Matches (Dry Run)
                    </button>
                </div>
                
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Call Date</th>
                                <th>Phone Number</th>
                                <th>Vendor Lead Code</th>
                                <th>Campaign</th>
                                <th>Agent</th>
                                <th>Status</th>
                                <th>Talk Time</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($orphanCalls ?? [] as $orphan)
                            <tr>
                                <td>{{ $orphan->call_date ? \Carbon\Carbon::parse($orphan->call_date)->format('M j, g:i A') : 'N/A' }}</td>
                                <td>{{ $orphan->phone_number ?? 'N/A' }}</td>
                                <td>{{ $orphan->vendor_lead_code ?? 'N/A' }}</td>
                                <td>{{ $orphan->campaign_id ?? 'N/A' }}</td>
                                <td>{{ $orphan->agent_id ?? 'N/A' }}</td>
                                <td>{{ $orphan->status ?? 'N/A' }}</td>
                                <td>{{ $orphan->talk_time ? gmdate("i:s", $orphan->talk_time) : '-' }}</td>
                                <td>
                                    <button class="btn btn-sm" onclick="tryMatchOrphan({{ $orphan->id }})">Try Match</button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    
                    @if(empty($orphanCalls) || count($orphanCalls) == 0)
                    <div class="empty-state">
                        <div class="empty-icon">✅</div>
                        <div class="empty-title">No Orphan Calls</div>
                        <div class="empty-text">All calls have been successfully matched to leads</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Tab switching
        function switchTab(tabName) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Remove active class from all tab buttons
            document.querySelectorAll('.tab').forEach(btn => {
                btn.classList.remove('active');
            });
            
            // Show selected tab
            document.getElementById(tabName + '-tab').classList.add('active');
            
            // Add active class to clicked button
            event.target.classList.add('active');
        }
        
        // Sync call logs
        function syncCallLogs() {
            if (!confirm('Sync call logs from Vici? This may take a moment.')) return;
            
            fetch('/admin/vici/sync-call-logs', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('✅ Call logs synced successfully!');
                    location.reload();
                } else {
                    alert('❌ Error: ' + data.message);
                }
            })
            .catch(error => {
                alert('❌ Error syncing call logs: ' + error);
            });
        }
        
        // Process orphan calls
        function processOrphanCalls() {
            if (!confirm('Process all orphan calls and attempt to match them to leads?')) return;
            
            fetch('/admin/vici/process-orphans', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(`✅ Processed ${data.matched} orphan calls!\n${data.unmatched} remain unmatched.`);
                    location.reload();
                } else {
                    alert('❌ Error: ' + data.message);
                }
            })
            .catch(error => {
                alert('❌ Error processing orphan calls: ' + error);
            });
        }
        
        // Dry run orphan processing
        function processOrphanCallsDryRun() {
            fetch('/admin/vici/process-orphans?dry_run=1', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(`🔍 Dry Run Results:\n${data.matched} calls could be matched\n${data.unmatched} would remain unmatched`);
                } else {
                    alert('❌ Error: ' + data.message);
                }
            })
            .catch(error => {
                alert('❌ Error running dry run: ' + error);
            });
        }
        
        // View call details
        function viewCallDetails(callId) {
            // Could open a modal with detailed call information
            alert('Call details view coming soon for call ID: ' + callId);
        }
        
        // Try to match single orphan
        function tryMatchOrphan(orphanId) {
            fetch(`/admin/vici/orphan/${orphanId}/match`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('✅ Successfully matched to lead: ' + data.lead_name);
                    location.reload();
                } else {
                    alert('❌ Could not find a matching lead');
                }
            })
            .catch(error => {
                alert('❌ Error: ' + error);
            });
        }
        
        // Apply filters
        function applyFilters() {
            const dateFrom = document.getElementById('date-from').value;
            const dateTo = document.getElementById('date-to').value;
            const status = document.getElementById('status-filter').value;
            
            const params = new URLSearchParams({
                from: dateFrom,
                to: dateTo,
                status: status
            });
            
            window.location.href = `/admin/vici-reports?${params.toString()}`;
        }
    </script>
</body>
</html>

<!-- Removed duplicated second HTML document to avoid Blade compilation issues -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vici Call Reports - QuotingFast Brain</title>
    <link rel="icon" type="image/png" href="/favicon.png">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
        }
        
        .header {
            background: white;
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .header-left {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .logo {
            height: 60px;
            width: auto;
        }
        
        h1 {
            color: #1a202c;
            font-size: 2rem;
            font-weight: 700;
        }
        
        .subtitle {
            color: #718096;
            font-size: 1rem;
            margin-top: 5px;
        }
        
        .header-actions {
            display: flex;
            gap: 15px;
        }
        
        .btn {
            padding: 12px 24px;
            border-radius: 10px;
            border: none;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .btn-secondary {
            background: #e2e8f0;
            color: #4a5568;
        }
        
        .btn-secondary:hover {
            background: #cbd5e0;
        }
        
        .btn-success {
            background: linear-gradient(135deg, #48bb78, #38a169);
            color: white;
        }
        
        .btn-warning {
            background: linear-gradient(135deg, #f6ad55, #ed8936);
            color: white;
        }
        
        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 15px;
        }
        
        .stat-icon.blue { background: linear-gradient(135deg, #667eea, #764ba2); }
        .stat-icon.green { background: linear-gradient(135deg, #48bb78, #38a169); }
        .stat-icon.orange { background: linear-gradient(135deg, #f6ad55, #ed8936); }
        .stat-icon.red { background: linear-gradient(135deg, #fc8181, #f56565); }
        .stat-icon.purple { background: linear-gradient(135deg, #9f7aea, #805ad5); }
        
        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: #1a202c;
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: #718096;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .stat-change {
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid #e2e8f0;
            font-size: 0.85rem;
        }
        
        .stat-change.positive { color: #48bb78; }
        .stat-change.negative { color: #f56565; }
        
        /* Tabs */
        .tabs-container {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        
        .tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 0;
        }
        
        .tab {
            padding: 15px 25px;
            background: none;
            border: none;
            color: #718096;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            border-bottom: 3px solid transparent;
            margin-bottom: -2px;
        }
        
        .tab:hover {
            color: #4a5568;
        }
        
        .tab.active {
            color: #667eea;
            border-bottom-color: #667eea;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        /* Table Styles */
        .table-container {
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th {
            background: #f7fafc;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #4a5568;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #e2e8f0;
        }
        
        td {
            padding: 15px;
            border-bottom: 1px solid #e2e8f0;
        }
        
        tr:hover {
            background: #f7fafc;
        }
        
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            display: inline-block;
        }
        
        .status-connected { background: #c6f6d5; color: #22543d; }
        .status-no-answer { background: #fed7d7; color: #742a2a; }
        .status-busy { background: #feebc8; color: #7c2d12; }
        .status-transferred { background: #bee3f8; color: #2c5282; }
        
        /* Charts */
        .chart-container {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-top: 20px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        }
        
        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .chart-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #1a202c;
        }
        
        /* Orphan Calls Section */
        .orphan-section {
            background: #fef5e7;
            border: 2px solid #f6ad55;
            border-radius: 15px;
            padding: 20px;
            margin-top: 20px;
        }
        
        .orphan-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .orphan-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #744210;
        }
        
        .orphan-count {
            background: #f6ad55;
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
        }
        
        /* Filters */
        .filters {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        
        .filter-label {
            font-size: 0.85rem;
            color: #718096;
            font-weight: 600;
        }
        
        .filter-input {
            padding: 10px 15px;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }
        
        .filter-input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        /* Loading State */
        .loading {
            text-align: center;
            padding: 40px;
            color: #718096;
        }
        
        .spinner {
            border: 3px solid #e2e8f0;
            border-top: 3px solid #667eea;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }
        
        .empty-icon {
            font-size: 4rem;
            margin-bottom: 20px;
        }
        
        .empty-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #1a202c;
            margin-bottom: 10px;
        }
        
        .empty-text {
            color: #718096;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="header-left">
                <img src="https://quotingfast.com/whitelogo" alt="QuotingFast" class="logo">
                <div>
                    <h1>📞 Vici Call Reports</h1>
                    <div class="subtitle">Monitor call performance and agent metrics</div>
                </div>
            </div>
            <div class="header-actions">
                <button class="btn btn-secondary" onclick="window.location.href='/admin'">
                    ← Back to Dashboard
                </button>
                <button class="btn btn-primary" onclick="syncCallLogs()">
                    🔄 Sync Call Logs
                </button>
                <button class="btn btn-success" onclick="processOrphanCalls()">
                    🔗 Process Orphan Calls
                </button>
            </div>
        </div>
        
        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon blue">📞</div>
                <div class="stat-value">{{ number_format($stats['total_calls'] ?? 0) }}</div>
                <div class="stat-label">Total Calls</div>
                <div class="stat-change positive">↑ {{ number_format($stats['calls_today'] ?? 0) }} today</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon green">✅</div>
                <div class="stat-value">{{ number_format($stats['connected_calls'] ?? 0) }}</div>
                <div class="stat-label">Connected Calls</div>
                <div class="stat-change positive">{{ $stats['connection_rate'] ?? 0 }}% rate</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon orange">⏱️</div>
                <div class="stat-value">{{ gmdate("i:s", $stats['avg_talk_time'] ?? 0) }}</div>
                <div class="stat-label">Avg Talk Time</div>
                <div class="stat-change">{{ number_format($stats['total_talk_hours'] ?? 0) }} total hours</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon red">📵</div>
                <div class="stat-value">{{ number_format($stats['no_answer'] ?? 0) }}</div>
                <div class="stat-label">No Answer</div>
                <div class="stat-change negative">{{ $stats['no_answer_rate'] ?? 0 }}% rate</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon purple">🔀</div>
                <div class="stat-value">{{ number_format($stats['transferred'] ?? 0) }}</div>
                <div class="stat-label">Transferred</div>
                <div class="stat-change positive">{{ $stats['transfer_rate'] ?? 0 }}% success</div>
            </div>
        </div>
        
        <!-- Orphan Calls Alert -->
        @if(($stats['orphan_calls'] ?? 0) > 0)
        <div class="orphan-section">
            <div class="orphan-header">
                <div class="orphan-title">⚠️ Unmatched Orphan Calls</div>
                <div class="orphan-count">{{ number_format($stats['orphan_calls']) }} calls</div>
            </div>
            <p style="color: #744210; margin-bottom: 15px;">
                These calls couldn't be matched to leads in the system. They may belong to leads not yet imported.
            </p>
            <button class="btn btn-warning" onclick="processOrphanCalls()">
                Process Orphan Calls Now
            </button>
        </div>
        @endif
        
        <!-- Tabs -->
        <div class="tabs-container">
            <div class="tabs">
                <button class="tab active" onclick="switchTab('recent')">Recent Calls</button>
                <button class="tab" onclick="switchTab('campaigns')">Campaign Performance</button>
                <button class="tab" onclick="switchTab('agents')">Agent Performance</button>
                <button class="tab" onclick="switchTab('hourly')">Hourly Analysis</button>
                <button class="tab" onclick="switchTab('orphans')">Orphan Calls</button>
            </div>
            
            <!-- Recent Calls Tab -->
            <div id="recent-tab" class="tab-content active">
                <div class="filters">
                    <div class="filter-group">
                        <label class="filter-label">Date Range</label>
                        <input type="date" class="filter-input" id="date-from" value="{{ request()->get('from', date('Y-m-d')) }}">
                    </div>
                    <div class="filter-group">
                        <label class="filter-label">To</label>
                        <input type="date" class="filter-input" id="date-to" value="{{ request()->get('to', date('Y-m-d')) }}">
                    </div>
                    <div class="filter-group">
                        <label class="filter-label">Status</label>
                        <select class="filter-input" id="status-filter">
                            <option value="">All Status</option>
                            <option value="connected">Connected</option>
                            <option value="no-answer">No Answer</option>
                            <option value="busy">Busy</option>
                            <option value="transferred">Transferred</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label class="filter-label">&nbsp;</label>
                        <button class="btn btn-primary" onclick="applyFilters()">Apply Filters</button>
                    </div>
                </div>
                
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Call Time</th>
                                <th>Lead</th>
                                <th>Phone</th>
                                <th>Campaign</th>
                                <th>Agent</th>
                                <th>Status</th>
                                <th>Talk Time</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="recent-calls-tbody">
                            @foreach($recentCalls ?? [] as $call)
                            <tr>
                                <td>{{ $call->first_call_time ? \Carbon\Carbon::parse($call->first_call_time)->format('M j, g:i A') : 'N/A' }}</td>
                                <td>
                                    @if($call->lead)
                                    <a href="/agent/lead/{{ $call->lead->id }}" target="_blank" style="color: #667eea; text-decoration: none; font-weight: 600;">
                                        {{ $call->lead->name }}
                                    </a>
                                    @else
                                    <span style="color: #718096;">Unknown</span>
                                    @endif
                                </td>
                                <td>{{ $call->phone_number ?? 'N/A' }}</td>
                                <td>{{ $call->campaign_id ?? 'N/A' }}</td>
                                <td>{{ $call->agent_id ?? 'N/A' }}</td>
                                <td>
                                    @php
                                        $status = $call->status ?? 'unknown';
                                        $statusClass = 'status-no-answer';
                                        if ($call->connected) $statusClass = 'status-connected';
                                        if ($call->transfer_status) $statusClass = 'status-transferred';
                                    @endphp
                                    <span class="status-badge {{ $statusClass }}">{{ ucfirst($status) }}</span>
                                </td>
                                <td>{{ $call->talk_time ? gmdate("i:s", $call->talk_time) : '-' }}</td>
                                <td>
                                    <button class="btn btn-sm" onclick="viewCallDetails({{ $call->id }})">View</button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    
                    @if(empty($recentCalls) || count($recentCalls) == 0)
                    <div class="empty-state">
                        <div class="empty-icon">📞</div>
                        <div class="empty-title">No Call Data Yet</div>
                        <div class="empty-text">Click "Sync Call Logs" to fetch data from Vici</div>
                    </div>
                    @endif
                </div>
            </div>
            
            <!-- Campaign Performance Tab -->
            <div id="campaigns-tab" class="tab-content">
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Campaign</th>
                                <th>Total Calls</th>
                                <th>Connected</th>
                                <th>Connection Rate</th>
                                <th>Avg Talk Time</th>
                                <th>Transferred</th>
                                <th>Transfer Rate</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($campaignStats ?? [] as $campaign)
                            <tr>
                                <td><strong>{{ $campaign->campaign_id }}</strong></td>
                                <td>{{ number_format($campaign->total_calls) }}</td>
                                <td>{{ number_format($campaign->connected_calls) }}</td>
                                <td>
                                    <span style="color: {{ $campaign->connection_rate > 50 ? '#48bb78' : '#f56565' }};">
                                        {{ $campaign->connection_rate }}%
                                    </span>
                                </td>
                                <td>{{ gmdate("i:s", $campaign->avg_talk_time ?? 0) }}</td>
                                <td>{{ number_format($campaign->transferred ?? 0) }}</td>
                                <td>{{ $campaign->transfer_rate ?? 0 }}%</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Agent Performance Tab -->
            <div id="agents-tab" class="tab-content">
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Agent ID</th>
                                <th>Total Calls</th>
                                <th>Connected</th>
                                <th>Total Talk Time</th>
                                <th>Avg Talk Time</th>
                                <th>Transfers</th>
                                <th>Performance</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($agentStats ?? [] as $agent)
                            <tr>
                                <td><strong>{{ $agent->agent_id }}</strong></td>
                                <td>{{ number_format($agent->total_calls) }}</td>
                                <td>{{ number_format($agent->connected_calls) }}</td>
                                <td>{{ gmdate("H:i:s", $agent->total_talk_time ?? 0) }}</td>
                                <td>{{ gmdate("i:s", $agent->avg_talk_time ?? 0) }}</td>
                                <td>{{ number_format($agent->transfers ?? 0) }}</td>
                                <td>
                                    <div style="display: flex; gap: 10px;">
                                        <span class="status-badge status-connected">
                                            {{ $agent->connection_rate ?? 0 }}% Connect
                                        </span>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Hourly Analysis Tab -->
            <div id="hourly-tab" class="tab-content">
                <div class="chart-container">
                    <div class="chart-header">
                        <div class="chart-title">Call Volume by Hour</div>
                    </div>
                    <canvas id="hourlyChart" width="400" height="200"></canvas>
                </div>
            </div>
            
            <!-- Orphan Calls Tab -->
            <div id="orphans-tab" class="tab-content">
                <div style="margin-bottom: 20px;">
                    <button class="btn btn-warning" onclick="processOrphanCalls()">
                        🔗 Process All Orphan Calls
                    </button>
                    <button class="btn btn-secondary" onclick="processOrphanCallsDryRun()">
                        👁️ Preview Matches (Dry Run)
                    </button>
                </div>
                
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Call Date</th>
                                <th>Phone Number</th>
                                <th>Vendor Lead Code</th>
                                <th>Campaign</th>
                                <th>Agent</th>
                                <th>Status</th>
                                <th>Talk Time</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($orphanCalls ?? [] as $orphan)
                            <tr>
                                <td>{{ $orphan->call_date ? \Carbon\Carbon::parse($orphan->call_date)->format('M j, g:i A') : 'N/A' }}</td>
                                <td>{{ $orphan->phone_number ?? 'N/A' }}</td>
                                <td>{{ $orphan->vendor_lead_code ?? 'N/A' }}</td>
                                <td>{{ $orphan->campaign_id ?? 'N/A' }}</td>
                                <td>{{ $orphan->agent_id ?? 'N/A' }}</td>
                                <td>{{ $orphan->status ?? 'N/A' }}</td>
                                <td>{{ $orphan->talk_time ? gmdate("i:s", $orphan->talk_time) : '-' }}</td>
                                <td>
                                    <button class="btn btn-sm" onclick="tryMatchOrphan({{ $orphan->id }})">Try Match</button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    
                    @if(empty($orphanCalls) || count($orphanCalls) == 0)
                    <div class="empty-state">
                        <div class="empty-icon">✅</div>
                        <div class="empty-title">No Orphan Calls</div>
                        <div class="empty-text">All calls have been successfully matched to leads</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Tab switching
        function switchTab(tabName) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Remove active class from all tab buttons
            document.querySelectorAll('.tab').forEach(btn => {
                btn.classList.remove('active');
            });
            
            // Show selected tab
            document.getElementById(tabName + '-tab').classList.add('active');
            
            // Add active class to clicked button
            event.target.classList.add('active');
        }
        
        // Sync call logs
        function syncCallLogs() {
            if (!confirm('Sync call logs from Vici? This may take a moment.')) return;
            
            fetch('/admin/vici/sync-call-logs', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('✅ Call logs synced successfully!');
                    location.reload();
                } else {
                    alert('❌ Error: ' + data.message);
                }
            })
            .catch(error => {
                alert('❌ Error syncing call logs: ' + error);
            });
        }
        
        // Process orphan calls
        function processOrphanCalls() {
            if (!confirm('Process all orphan calls and attempt to match them to leads?')) return;
            
            fetch('/admin/vici/process-orphans', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(`✅ Processed ${data.matched} orphan calls!\n${data.unmatched} remain unmatched.`);
                    location.reload();
                } else {
                    alert('❌ Error: ' + data.message);
                }
            })
            .catch(error => {
                alert('❌ Error processing orphan calls: ' + error);
            });
        }
        
        // Dry run orphan processing
        function processOrphanCallsDryRun() {
            fetch('/admin/vici/process-orphans?dry_run=1', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(`🔍 Dry Run Results:\n${data.matched} calls could be matched\n${data.unmatched} would remain unmatched`);
                } else {
                    alert('❌ Error: ' + data.message);
                }
            })
            .catch(error => {
                alert('❌ Error running dry run: ' + error);
            });
        }
        
        // View call details
        function viewCallDetails(callId) {
            // Could open a modal with detailed call information
            alert('Call details view coming soon for call ID: ' + callId);
        }
        
        // Try to match single orphan
        function tryMatchOrphan(orphanId) {
            fetch(`/admin/vici/orphan/${orphanId}/match`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('✅ Successfully matched to lead: ' + data.lead_name);
                    location.reload();
                } else {
                    alert('❌ Could not find a matching lead');
                }
            })
            .catch(error => {
                alert('❌ Error: ' + error);
            });
        }
        
        // Apply filters
        function applyFilters() {
            const dateFrom = document.getElementById('date-from').value;
            const dateTo = document.getElementById('date-to').value;
            const status = document.getElementById('status-filter').value;
            
            const params = new URLSearchParams({
                from: dateFrom,
                to: dateTo,
                status: status
            });
            
            window.location.href = `/admin/vici-reports?${params.toString()}`;
        }
    </script>
</body>
</html>




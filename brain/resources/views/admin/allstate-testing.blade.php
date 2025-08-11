<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Allstate API Testing Dashboard - Brain</title>
    <link rel="icon" type="image/png" href="/logo.png">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: #333;
        }
        
        .header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 1rem 2rem;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo-section {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .logo {
            height: 50px;
            width: auto;
        }
        
        .title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #2d3748;
        }
        
        .brand-text {
            font-family: 'Orbitron', sans-serif;
            font-size: 1.2rem;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #2563eb;
        }
        
        .nav-links {
            display: flex;
            gap: 1rem;
        }
        
        .nav-link {
            padding: 0.5rem 1rem;
            text-decoration: none;
            color: #4a5568;
            border-radius: 0.5rem;
            transition: all 0.3s ease;
        }
        
        .nav-link:hover {
            background: #edf2f7;
            color: #2d3748;
        }
        
        .container {
            max-width: 1400px;
            margin: 2rem auto;
            padding: 0 2rem;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 1.5rem;
            border-radius: 1rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 0.5rem;
        }
        
        .stat-label {
            color: #718096;
            font-weight: 500;
        }
        
        .success .stat-number { color: #38a169; }
        .error .stat-number { color: #e53e3e; }
        .warning .stat-number { color: #d69e2e; }
        
        .main-content {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 1rem;
            padding: 2rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
        
        .section-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .test-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }
        
        .test-table th,
        .test-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .test-table th {
            background: #f7fafc;
            font-weight: 600;
            color: #4a5568;
        }
        
        .test-table tr:hover {
            background: #f7fafc;
        }
        
        .badge {
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 500;
        }
        
        .badge-success {
            background: #c6f6d5;
            color: #22543d;
        }
        
        .badge-error {
            background: #fed7d7;
            color: #742a2a;
        }
        
        .badge-warning {
            background: #faf089;
            color: #744210;
        }
        
        .btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 0.5rem;
            font-weight: 500;
            text-decoration: none;
            display: inline-block;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background: #4299e1;
            color: white;
        }
        
        .btn-primary:hover {
            background: #3182ce;
        }
        
        .btn-secondary {
            background: #e2e8f0;
            color: #4a5568;
        }
        
        .btn-secondary:hover {
            background: #cbd5e0;
        }
        
        .btn-small {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }
        
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
        }
        
        .modal-content {
            background-color: white;
            margin: 2rem auto;
            padding: 2rem;
            border-radius: 1rem;
            width: 90%;
            max-width: 800px;
            max-height: 80vh;
            overflow-y: auto;
        }
        
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        
        .close:hover {
            color: #000;
        }
        
        .json-viewer {
            background: #f7fafc;
            padding: 1rem;
            border-radius: 0.5rem;
            font-family: 'Monaco', 'Menlo', monospace;
            font-size: 0.875rem;
            white-space: pre-wrap;
            max-height: 300px;
            overflow-y: auto;
            border: 1px solid #e2e8f0;
        }
        
        .data-source-tag {
            padding: 0.125rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.75rem;
            font-weight: 500;
            margin-left: 0.5rem;
        }
        
        .source-payload { background: #bee3f8; color: #2a4365; }
        .source-driver { background: #c6f6d5; color: #22543d; }
        .source-default { background: #faf089; color: #744210; }
        .source-smart { background: #e9d8fd; color: #553c9a; }
        
        .refresh-btn {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            background: #4299e1;
            color: white;
            border: none;
            border-radius: 50%;
            width: 60px;
            height: 60px;
            font-size: 1.5rem;
            cursor: pointer;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
        }
        
        .refresh-btn:hover {
            background: #3182ce;
            transform: rotate(180deg);
        }
        
        /* Bulk Processing Styles */
        .bulk-process-form {
            background: rgba(255, 255, 255, 0.95);
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
        
        .form-row {
            display: flex;
            gap: 1.5rem;
            align-items: end;
            flex-wrap: wrap;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        
        .form-group label {
            font-weight: 600;
            color: #2d3748;
            font-size: 0.875rem;
        }
        
        .form-select {
            padding: 0.75rem;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            background: white;
            font-size: 0.875rem;
            min-width: 150px;
        }
        
        .process-btn {
            background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 2px 10px rgba(72, 187, 120, 0.3);
        }
        
        .process-btn:hover {
            background: linear-gradient(135deg, #38a169 0%, #2f855a 100%);
            transform: translateY(-2px);
            box-shadow: 0 4px 20px rgba(72, 187, 120, 0.4);
        }
        
        .process-btn:disabled {
            background: #a0aec0;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
        
        .process-status {
            margin-top: 1rem;
            padding: 1rem;
            border-radius: 8px;
            font-weight: 500;
        }
        
        .process-status.processing {
            background: #bee3f8;
            color: #2b6cb0;
            border: 1px solid #90cdf4;
        }
        
        .process-status.success {
            background: #c6f6d5;
            color: #2f855a;
            border: 1px solid #9ae6b4;
        }
        
        .process-status.error {
            background: #fed7d7;
            color: #c53030;
            border: 1px solid #feb2b2;
        }
        
        .process-results {
            margin-top: 1rem;
            max-height: 300px;
            overflow-y: auto;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            background: white;
        }
        
        .result-item {
            padding: 0.75rem;
            border-bottom: 1px solid #f7fafc;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .result-item:last-child {
            border-bottom: none;
        }
        
        .result-item.success {
            background: #f0fff4;
        }
        
        .result-item.failed {
            background: #fff5f5;
        }
        
        .result-lead {
            font-weight: 500;
        }
        
        .result-status {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-weight: 600;
        }
        
        .result-status.success {
            background: #c6f6d5;
            color: #2f855a;
        }
        
        .result-status.failed {
            background: #fed7d7;
            color: #c53030;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo-section">
            <img src="https://quotingfast.com/logoqf0704.png" alt="QuotingFast" class="logo" 
                 onerror="this.src='https://quotingfast.com/qfqflogo.png'; this.onerror=null;">
            <div style="display: flex; flex-direction: column; gap: 2px;">
                <span style="font-size: 0.85rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; color: #718096;">QuotingFast</span>
                <span class="brand-text">The Brain</span>
            </div>
            <div class="title">🧪 Allstate API Testing</div>
        </div>
        <div class="nav-links">
            <a href="/admin" class="nav-link">← Admin Dashboard</a>
            <a href="/leads" class="nav-link">Leads</a>
            <a href="#" class="nav-link" onclick="location.reload()" title="Refresh now">🔄 Refresh</a>
            <a href="#" id="autoRefreshBtn" class="nav-link" onclick="toggleAutoRefresh()" title="Toggle auto refresh">⏱️ Auto Refresh: On</a>
        </div>
    </div>

    <div class="container">
        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card success">
                <div class="stat-number">{{ $stats['successful'] }}</div>
                <div class="stat-label">Successful Tests</div>
            </div>
            <div class="stat-card error">
                <div class="stat-number">{{ $stats['failed'] }}</div>
                <div class="stat-label">Failed Tests</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">{{ $stats['success_rate'] }}%</div>
                <div class="stat-label">Success Rate</div>
            </div>
            <div class="stat-card warning">
                <div class="stat-number">{{ $stats['avg_response_time_ms'] }}ms</div>
                <div class="stat-label">Avg Response Time</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">{{ $stats['total_tests'] }}</div>
                <div class="stat-label">Total Tests</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">{{ $stats['last_test'] ? $stats['last_test']->diffForHumans() : 'None' }}</div>
                <div class="stat-label">Last Test</div>
            </div>
        </div>

        <!-- Bulk Processing Section -->
        <div class="main-content" style="margin-bottom: 2rem;">
            <div class="section-title">
                🚀 Bulk Process Existing Leads
                <span style="font-size: 0.875rem; color: #718096; font-weight: normal;">
                    Process leads that were received before testing was enabled
                </span>
            </div>
            
            <div class="bulk-process-form">
                <div class="form-row">
                    <div class="form-group">
                        <label>Date Filter:</label>
                        <select id="dateFilter" class="form-select">
                            <option value="today">Today's Leads</option>
                            <option value="yesterday">Yesterday's Leads</option>
                            <option value="last_7_days">Last 7 Days</option>
                            <option value="last_30_days">Last 30 Days</option>
                            <option value="all">All Leads</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Limit:</label>
                        <select id="limitFilter" class="form-select">
                            <option value="25">25 leads</option>
                            <option value="50" selected>50 leads</option>
                            <option value="100">100 leads</option>
                            <option value="200">200 leads</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <button id="bulkProcessBtn" class="process-btn" onclick="startBulkProcessing()">
                            🧪 Process Leads
                        </button>
                    </div>
                </div>
                
                <div id="bulkProcessStatus" class="process-status" style="display: none;"></div>
                <div id="bulkProcessResults" class="process-results" style="display: none;"></div>
            </div>
        </div>

        <!-- Test Results Table -->
        <div class="main-content">
            <div class="section-title">
                🔬 Recent Test Results
                <span style="font-size: 0.875rem; color: #718096; font-weight: normal;">
                    (Live leads automatically tested, bypassing Vici)
                </span>
            </div>

            <table class="test-table">
                <thead>
                    <tr>
                        <th>Lead</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Response Time</th>
                        <th>Sent At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($testLogs as $log)
                    <tr>
                        <td>
                            <strong>{{ $log->lead_name }}</strong><br>
                            <small style="color: #718096;">{{ $log->lead_phone }}</small>
                            @if($log->lead_email)
                                <br><small style="color: #718096;">{{ $log->lead_email }}</small>
                            @endif
                        </td>
                        <td>
                            <span class="badge badge-{{ $log->lead_type === 'auto' ? 'primary' : 'warning' }}">
                                {{ ucfirst($log->lead_type) }}
                            </span>
                        </td>
                        <td>
                            @if($log->success)
                                <span class="badge badge-success">✅ Success</span>
                            @else
                                <span class="badge badge-error">❌ Failed</span>
                            @endif
                        </td>
                        <td>{{ $log->response_time_ms }}ms</td>
                        <td>{{ $log->sent_at->setTimezone('America/New_York')->format('M j, g:i A') }} ET</td>
                        <td>
                            <button class="btn btn-secondary btn-small" onclick="showDetails({{ $log->id }})">
                                📋 View Details
                            </button>
                            @if(!$log->success)
                                <button class="btn btn-secondary btn-small" onclick="showError({{ $log->id }})">
                                    🚨 View Error
                                </button>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            @if($testLogs->isEmpty())
                <div style="text-align: center; padding: 3rem; color: #718096;">
                    <div style="font-size: 3rem; margin-bottom: 1rem;">🧪</div>
                    <h3>No tests yet</h3>
                    <p>Live leads will automatically appear here when they come in and get tested with Allstate API</p>
                    <p style="margin-top: 1rem;">
                        <strong>Current Mode:</strong> Bypassing Vici, auto-qualifying leads, sending to Allstate Test Environment
                    </p>
                </div>
            @endif
        </div>
    </div>

    <!-- Details Modal -->
    <div id="detailsModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <div id="modalContent">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>

    <!-- Auto-refresh button (floating) -->
    <button class="refresh-btn" onclick="location.reload()" title="Refresh Dashboard">
        🔄
    </button>

    <script>
        // Auto-refresh every 30 seconds unless disabled or a modal is open
        let autoRefresh = true;
        const autoBtn = document.getElementById('autoRefreshBtn');
        function toggleAutoRefresh() {
            autoRefresh = !autoRefresh;
            if (autoBtn) autoBtn.textContent = `⏱️ Auto Refresh: ${autoRefresh ? 'On' : 'Off'}`;
        }
        setInterval(() => {
            const modalOpen = document.getElementById('detailsModal')?.style.display === 'block';
            if (autoRefresh && !modalOpen) {
                location.reload();
            }
        }, 30000);

        function showDetails(logId) {
            fetch(`/admin/allstate-testing/details/${logId}`)
                .then(response => response.json())
                .then(data => {
                    // Pause auto-refresh while viewing details
                    autoRefresh = false; if (autoBtn) autoBtn.textContent = '⏱️ Auto Refresh: Off';
                    document.getElementById('modalContent').innerHTML = `
                        <h2>🔬 Test Details - ${data.lead_name}</h2>
                        
                        <h3>📦 Original Provider Payload (incoming)</h3>
                        <div class="json-viewer">${JSON.stringify(data.allstate_payload?.original_payload || {}, null, 2)}</div>
                        
                        <h3>📋 Auto-Generated Qualification Data</h3>
                        <div class="json-viewer">${JSON.stringify(data.qualification_data, null, 2)}</div>
                        
                        <h3>📍 Data Sources</h3>
                        <div style="margin: .5rem 0 1rem 0; font-size: .875rem; color:#4a5568;">
                            <strong>Legend:</strong>
                            <span class="data-source-tag source-default">default</span> value used when none was supplied;<br>
                            <span class="data-source-tag source-smart">smart_logic</span> value calculated by rules using other fields (best guess).
                        </div>
                        <div style="margin: 1rem 0;">
                            ${Object.entries(data.data_sources).map(([field, source]) => `
                                <div style="margin: 0.5rem 0;">
                                    <strong>${field}:</strong> 
                                    <span class="data-source-tag source-${source.replace('_', '-')}">${source}</span>
                                    ${data.allstate_payload && data.allstate_payload[field] !== undefined ? `
                                        <code style="background:#edf2f7; padding:2px 6px; border-radius:4px; margin-left:.5rem;">${String(data.allstate_payload[field])}</code>
                                    ` : ''}
                                    ${source === 'top12' && data.qualification_data && data.qualification_data[field] !== undefined ? `
                                        <span style="color:#718096; margin-left:.25rem; font-size:.8rem;">(from Top 12)</span>
                                    ` : ''}
                                </div>
                            `).join('')}
                        </div>
                        
                        <h3>🚀 Allstate API Payload</h3>
                        <div class="json-viewer">${JSON.stringify(data.allstate_payload, null, 2)}</div>
                        
                        <h3>📥 Allstate API Response</h3>
                        <div class="json-viewer">${JSON.stringify(data.allstate_response, null, 2)}</div>
                        
                        <div style="margin-top: 2rem; padding-top: 1rem; border-top: 1px solid #e2e8f0;">
                            <strong>Environment:</strong> ${data.test_environment}<br>
                            <strong>Response Time:</strong> ${data.response_time_ms}ms<br>
                            <strong>Status Code:</strong> ${data.response_status}<br>
                            <strong>Endpoint:</strong> ${data.allstate_endpoint}<br>
                            ${data.qualification_data?.agent_notes ? `<strong>Agent Notes:</strong> ${data.qualification_data.agent_notes}` : ''}
                        </div>
                    `;
                    document.getElementById('detailsModal').style.display = 'block';
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to load test details');
                });
        }

        function showError(logId) {
            fetch(`/admin/allstate-testing/details/${logId}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('modalContent').innerHTML = `
                        <h2>🚨 Test Error - ${data.lead_name}</h2>
                        
                        <h3>❌ Error Message</h3>
                        <div style="background: #fed7d7; color: #742a2a; padding: 1rem; border-radius: 0.5rem; margin: 1rem 0;">
                            ${data.error_message || 'Unknown error'}
                        </div>
                        
                        ${data.validation_errors && Object.keys(data.validation_errors).length > 0 ? `
                            <h3>🔍 Validation Errors</h3>
                            <div style="background: #faf089; color: #744210; padding: 1rem; border-radius: 0.5rem; margin: 1rem 0;">
                                ${Object.entries(data.validation_errors).map(([field, error]) => `
                                    <div><strong>${field}:</strong> ${error}</div>
                                `).join('')}
                            </div>
                        ` : ''}
                        
                        <h3>📋 Qualification Data (What was attempted)</h3>
                        <div class="json-viewer">${JSON.stringify(data.qualification_data, null, 2)}</div>
                        
                        <h3>🚀 Payload (What was sent)</h3>
                        <div class="json-viewer">${JSON.stringify(data.allstate_payload, null, 2)}</div>
                    `;
                    document.getElementById('detailsModal').style.display = 'block';
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to load error details');
                });
        }

        function closeModal() {
            document.getElementById('detailsModal').style.display = 'none';
            // Re-enable auto refresh when closing
            autoRefresh = true; if (autoBtn) autoBtn.textContent = '⏱️ Auto Refresh: On';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('detailsModal');
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        }
        
        // Bulk Processing Functionality
        function startBulkProcessing() {
            const dateFilter = document.getElementById('dateFilter').value;
            const limit = document.getElementById('limitFilter').value;
            const button = document.getElementById('bulkProcessBtn');
            const statusDiv = document.getElementById('bulkProcessStatus');
            const resultsDiv = document.getElementById('bulkProcessResults');
            
            // Disable button and show processing status
            button.disabled = true;
            button.textContent = '🔄 Processing...';
            
            statusDiv.style.display = 'block';
            statusDiv.className = 'process-status processing';
            statusDiv.textContent = `Processing ${dateFilter} leads (limit: ${limit})...`;
            
            resultsDiv.style.display = 'none';
            resultsDiv.innerHTML = '';
            
            // Make API call
            fetch('/admin/allstate-testing/bulk-process', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                body: JSON.stringify({
                    date_filter: dateFilter,
                    limit: parseInt(limit)
                })
            })
            .then(response => response.json())
            .then(data => {
                // Re-enable button
                button.disabled = false;
                button.textContent = '🧪 Process Leads';
                
                if (data.success) {
                    // Show success status
                    statusDiv.className = 'process-status success';
                    statusDiv.innerHTML = `
                        <strong>✅ Processing Complete!</strong><br>
                        ${data.message}<br>
                        <small>Successful: ${data.stats.successful} | Failed: ${data.stats.failed}</small>
                    `;
                    
                    // Show detailed results
                    if (data.results && data.results.length > 0) {
                        resultsDiv.style.display = 'block';
                        resultsDiv.innerHTML = data.results.map(result => `
                            <div class="result-item ${result.success ? 'success' : 'failed'}">
                                <div class="result-lead">
                                    ${result.lead_name} (ID: ${result.lead_id})
                                </div>
                                <div class="result-status ${result.success ? 'success' : 'failed'}">
                                    ${result.success ? '✅ Success' : '❌ Failed'}
                                    ${result.response_time_ms ? ` (${result.response_time_ms}ms)` : ''}
                                </div>
                            </div>
                        `).join('');
                    }
                    
                    // Auto-refresh the page after 3 seconds to show new results
                    setTimeout(() => {
                        window.location.reload();
                    }, 3000);
                    
                } else {
                    // Show error status
                    statusDiv.className = 'process-status error';
                    statusDiv.innerHTML = `<strong>❌ Processing Failed</strong><br>${data.message}`;
                }
            })
            .catch(error => {
                console.error('Bulk processing error:', error);
                
                // Re-enable button
                button.disabled = false;
                button.textContent = '🧪 Process Leads';
                
                // Show error status
                statusDiv.className = 'process-status error';
                statusDiv.innerHTML = `<strong>❌ Network Error</strong><br>Failed to process leads. Please try again.`;
            });
        }
    </script>
</body>
</html>
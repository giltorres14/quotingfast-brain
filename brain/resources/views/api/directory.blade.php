<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API & Webhooks Directory - The Brain</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #f5f5f5; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 2rem; text-align: center; }
        .header h1 { font-size: 2.5rem; margin-bottom: 0.5rem; }
        .header p { font-size: 1.1rem; opacity: 0.9; }
        .container { max-width: 1400px; margin: 2rem auto; padding: 0 1rem; }
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 3rem; }
        .stat-card { background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); text-align: center; }
        .stat-number { font-size: 3rem; font-weight: bold; color: #667eea; margin-bottom: 0.5rem; }
        .stat-label { color: #666; font-size: 1.1rem; font-weight: 500; }
        .nav-links { margin-bottom: 2rem; text-align: center; }
        .nav-links a { background: #667eea; color: white; padding: 0.75rem 1.5rem; margin: 0.5rem; text-decoration: none; border-radius: 6px; display: inline-block; transition: all 0.3s; }
        .nav-links a:hover { background: #5a67d8; transform: translateY(-2px); }
        .section { margin-bottom: 3rem; }
        .section-title { font-size: 1.8rem; color: #333; margin-bottom: 1.5rem; padding-left: 1rem; border-left: 4px solid #667eea; }
        .endpoints-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 1.5rem; }
        .endpoint-card { background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1); transition: all 0.3s; }
        .endpoint-card:hover { transform: translateY(-4px); box-shadow: 0 8px 15px rgba(0,0,0,0.15); }
        .endpoint-header { padding: 1.5rem; border-bottom: 1px solid #eee; }
        .endpoint-method { display: inline-block; padding: 0.25rem 0.75rem; border-radius: 4px; font-size: 0.8rem; font-weight: bold; margin-bottom: 0.5rem; }
        .method-get { background: #dcfce7; color: #166534; }
        .method-post { background: #fef3c7; color: #92400e; }
        .endpoint-path { font-family: "Monaco", "Consolas", monospace; font-size: 1.1rem; font-weight: 600; color: #333; margin-bottom: 0.5rem; display: flex; align-items: center; }
        .endpoint-description { color: #666; font-size: 0.95rem; }
        .endpoint-body { padding: 1.5rem; }
        .endpoint-features { list-style: none; }
        .endpoint-features li { padding: 0.25rem 0; color: #555; font-size: 0.9rem; }
        .endpoint-features li:before { content: "✓"; color: #10b981; font-weight: bold; margin-right: 0.5rem; }
        .test-btn { background: #10b981; color: white; padding: 0.5rem 1rem; border: none; border-radius: 6px; text-decoration: none; font-size: 0.9rem; display: inline-block; margin-top: 1rem; transition: all 0.3s; }
        .test-btn:hover { background: #059669; }
        .copy-btn { background: #6b7280; color: white; padding: 0.25rem 0.5rem; border: none; border-radius: 4px; font-size: 0.8rem; margin-left: 0.5rem; cursor: pointer; }
        .copy-btn:hover { background: #4b5563; }
        .status-indicator { display: inline-block; width: 8px; height: 8px; border-radius: 50%; margin-right: 0.5rem; }
        .status-active { background: #10b981; }
        .status-inactive { background: #ef4444; }
        .status-warning { background: #f59e0b; }
        .footer { text-align: center; padding: 2rem; color: #666; }
        .add-endpoint-btn { 
            background: #3b82f6; 
            color: white; 
            padding: 1rem 2rem; 
            border: none; 
            border-radius: 8px; 
            font-size: 1rem; 
            font-weight: 600; 
            cursor: pointer; 
            transition: all 0.3s;
            margin: 1rem;
        }
        .add-endpoint-btn:hover { 
            background: #2563eb; 
            transform: translateY(-2px);
        }
        .management-section {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🧠 API & Webhooks Directory</h1>
        <p>Complete integration hub for The Brain lead management system</p>
    </div>

    <div class="container">
        <!-- Quick Stats -->
        <div class="stats">
            <div class="stat-card">
                <div class="stat-number">{{ $stats['total_leads'] ?? 0 }}</div>
                <div class="stat-label">Total Leads Processed</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">{{ $stats['today_leads'] ?? 0 }}</div>
                <div class="stat-label">Today's Leads</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">{{ $stats['active_sources'] ?? 0 }}</div>
                <div class="stat-label">Active Sources</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">{{ count($webhooks ?? []) }}</div>
                <div class="stat-label">Webhook Endpoints</div>
            </div>
        </div>

        <!-- Navigation Links -->
        <div class="nav-links">
            <a href="/leads">📊 Leads Dashboard</a>
            <a href="/api/webhooks">📈 Webhook Analytics</a>
            <a href="/webhook/status">🔍 System Status</a>
            <a href="/admin">⚙️ Admin Panel</a>
        </div>

        <!-- Endpoint Management -->
        <div class="management-section">
            <h2 style="margin-bottom: 1rem; color: #333;">🔧 Endpoint Management</h2>
            <p style="margin-bottom: 1.5rem; color: #666;">Add new API endpoints or webhooks to expand system capabilities</p>
            <button class="add-endpoint-btn" onclick="addNewEndpoint()">➕ Add New API Endpoint</button>
            <button class="add-endpoint-btn" onclick="addNewWebhook()">🔗 Add New Webhook</button>
        </div>

        <!-- Webhook Endpoints -->
        <div class="section">
            <h2 class="section-title">🔗 Webhook Endpoints</h2>
            <div class="endpoints-grid">
                <!-- LeadsQuotingFast Webhook -->
                <div class="endpoint-card">
                    <div class="endpoint-header">
                        <span class="endpoint-method method-post">POST</span>
                        <div class="endpoint-path">/webhook.php <button class="copy-btn" onclick="copyToClipboard('/webhook.php')">Copy</button></div>
                        <div class="endpoint-description"><span class="status-indicator status-active"></span>LeadsQuotingFast primary webhook</div>
                    </div>
                    <div class="endpoint-body">
                        <ul class="endpoint-features">
                            <li>Receives auto & home insurance leads</li>
                            <li>Automatic lead type detection</li>
                            <li>Vici integration & external lead ID generation</li>
                            <li>TCPA compliance tracking</li>
                        </ul>
                        <a href="/webhook.php" class="test-btn">View Endpoint</a>
                    </div>
                </div>

                <!-- Ringba Webhook -->
                <div class="endpoint-card">
                    <div class="endpoint-header">
                        <span class="endpoint-method method-post">POST</span>
                        <div class="endpoint-path">/webhook/ringba <button class="copy-btn" onclick="copyToClipboard('/webhook/ringba')">Copy</button></div>
                        <div class="endpoint-description"><span class="status-indicator status-active"></span>Ringba call tracking webhook</div>
                    </div>
                    <div class="endpoint-body">
                        <ul class="endpoint-features">
                            <li>Call tracking and routing data</li>
                            <li>Lead enrichment from call events</li>
                            <li>Performance analytics integration</li>
                            <li>Real-time call status updates</li>
                        </ul>
                        <a href="/webhook/ringba" class="test-btn">View Endpoint</a>
                    </div>
                </div>

                <!-- Vici Webhook -->
                <div class="endpoint-card">
                    <div class="endpoint-header">
                        <span class="endpoint-method method-post">POST</span>
                        <div class="endpoint-path">/webhook/vici <button class="copy-btn" onclick="copyToClipboard('/webhook/vici')">Copy</button></div>
                        <div class="endpoint-description"><span class="status-indicator status-active"></span>ViciDial system webhook</div>
                    </div>
                    <div class="endpoint-body">
                        <ul class="endpoint-features">
                            <li>Dialer system integration callbacks</li>
                            <li>Call disposition and outcome tracking</li>
                            <li>Agent performance metrics</li>
                            <li>Lead status synchronization</li>
                        </ul>
                        <a href="/webhook/vici" class="test-btn">View Endpoint</a>
                    </div>
                </div>

                <!-- Allstate Webhook -->
                <div class="endpoint-card">
                    <div class="endpoint-header">
                        <span class="endpoint-method method-post">POST</span>
                        <div class="endpoint-path">/webhook/allstate <button class="copy-btn" onclick="copyToClipboard('/webhook/allstate')">Copy</button></div>
                        <div class="endpoint-description"><span class="status-indicator status-active"></span>Allstate Lead Marketplace</div>
                    </div>
                    <div class="endpoint-body">
                        <ul class="endpoint-features">
                            <li>Transfers leads to Allstate API</li>
                            <li>Supports auto & home insurance verticals</li>
                            <li>Data normalization & validation</li>
                            <li>Real-time transfer status tracking</li>
                        </ul>
                        <a href="/webhook/allstate" class="test-btn">View Endpoint</a>
                    </div>
                </div>

                <!-- Twilio Webhook -->
                <div class="endpoint-card">
                    <div class="endpoint-header">
                        <span class="endpoint-method method-post">POST</span>
                        <div class="endpoint-path">/webhook/twilio <button class="copy-btn" onclick="copyToClipboard('/webhook/twilio')">Copy</button></div>
                        <div class="endpoint-description"><span class="status-indicator status-active"></span>Twilio SMS/Voice webhook</div>
                    </div>
                    <div class="endpoint-body">
                        <ul class="endpoint-features">
                            <li>Handles SMS and voice callbacks</li>
                            <li>Links communications to leads</li>
                            <li>Tracks engagement metrics</li>
                            <li>Automated response workflows</li>
                        </ul>
                        <a href="/webhook/twilio" class="test-btn">View Endpoint</a>
                    </div>
                </div>

                <!-- Ringba Decision Webhook -->
                <div class="endpoint-card">
                    <div class="endpoint-header">
                        <span class="endpoint-method method-post">POST</span>
                        <div class="endpoint-path">/webhook/ringba-decision <button class="copy-btn" onclick="copyToClipboard('/webhook/ringba-decision')">Copy</button></div>
                        <div class="endpoint-description"><span class="status-indicator status-active"></span>Ringba buyer decision webhook</div>
                    </div>
                    <div class="endpoint-body">
                        <ul class="endpoint-features">
                            <li>Receives buyer routing decisions</li>
                            <li>Triggers lead transfers to chosen buyers</li>
                            <li>Supports multiple buyer integrations</li>
                            <li>Decision tracking & analytics</li>
                        </ul>
                        <a href="/webhook/ringba-decision" class="test-btn">View Endpoint</a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- API Endpoints -->
        <div class="section">
            <h2 class="section-title">🔌 API Endpoints</h2>
            <div class="endpoints-grid">
                <!-- Webhooks API -->
                <div class="endpoint-card">
                    <div class="endpoint-header">
                        <span class="endpoint-method method-get">GET</span>
                        <div class="endpoint-path">/api/webhooks <button class="copy-btn" onclick="copyToClipboard('/api/webhooks')">Copy</button></div>
                        <div class="endpoint-description"><span class="status-indicator status-active"></span>Webhook dashboard API</div>
                    </div>
                    <div class="endpoint-body">
                        <ul class="endpoint-features">
                            <li>Returns webhook activity dashboard data</li>
                            <li>Real-time statistics and metrics</li>
                            <li>JSON formatted response</li>
                        </ul>
                        <a href="/api/webhooks" class="test-btn">Test API</a>
                    </div>
                </div>

                <!-- Analytics API -->
                <div class="endpoint-card">
                    <div class="endpoint-header">
                        <span class="endpoint-method method-get">GET</span>
                        <div class="endpoint-path">/api/analytics/quick/{period} <button class="copy-btn" onclick="copyToClipboard('/api/analytics/quick/{period}')">Copy</button></div>
                        <div class="endpoint-description"><span class="status-indicator status-active"></span>Quick analytics API</div>
                    </div>
                    <div class="endpoint-body">
                        <ul class="endpoint-features">
                            <li>Fast analytics for common time periods</li>
                            <li>Supports: today, week, month, quarter</li>
                            <li>Lead volume and conversion metrics</li>
                        </ul>
                        <a href="/api/analytics/quick/today" class="test-btn">Test Today</a>
                    </div>
                </div>

                <!-- Lead Payload API -->
                <div class="endpoint-card">
                    <div class="endpoint-header">
                        <span class="endpoint-method method-get">GET</span>
                        <div class="endpoint-path">/api/lead/{leadId}/payload <button class="copy-btn" onclick="copyToClipboard('/api/lead/{leadId}/payload')">Copy</button></div>
                        <div class="endpoint-description"><span class="status-indicator status-active"></span>Lead payload inspector</div>
                    </div>
                    <div class="endpoint-body">
                        <ul class="endpoint-features">
                            <li>View complete lead data structure</li>
                            <li>JSON formatted lead information</li>
                            <li>Useful for API integration testing</li>
                        </ul>
                        <a href="/api/lead/1/payload" class="test-btn">Test Lead 1</a>
                    </div>
                </div>

                <!-- Webhook Status -->
                <div class="endpoint-card">
                    <div class="endpoint-header">
                        <span class="endpoint-method method-get">GET</span>
                        <div class="endpoint-path">/webhook/status <button class="copy-btn" onclick="copyToClipboard('/webhook/status')">Copy</button></div>
                        <div class="endpoint-description"><span class="status-indicator status-active"></span>Webhook health monitoring</div>
                    </div>
                    <div class="endpoint-body">
                        <ul class="endpoint-features">
                            <li>Real-time webhook status monitoring</li>
                            <li>Error rate and performance metrics</li>
                            <li>Integration health dashboard</li>
                            <li>Uptime and reliability stats</li>
                        </ul>
                        <a href="/webhook/status" class="test-btn">Check Status</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="footer">
        <p>🧠 The Brain - Lead Management System | Last updated: {{ now()->format('M j, Y g:i A') }}</p>
    </div>

    <script>
        function copyToClipboard(text) {
            navigator.clipboard.writeText(window.location.origin + text).then(function() {
                // Show temporary success message
                const btn = event.target;
                const originalText = btn.textContent;
                btn.textContent = 'Copied!';
                btn.style.background = '#10b981';
                setTimeout(() => {
                    btn.textContent = originalText;
                    btn.style.background = '#6b7280';
                }, 2000);
            });
        }

        function addNewEndpoint() {
            alert('🚧 Coming Soon!\n\nEndpoint management interface will allow you to:\n• Add custom API endpoints\n• Configure webhook destinations\n• Set up authentication\n• Monitor endpoint performance');
        }

        function addNewWebhook() {
            alert('🚧 Coming Soon!\n\nWebhook management interface will allow you to:\n• Add new webhook sources\n• Configure data transformations\n• Set up routing rules\n• Test webhook integrations');
        }
    </script>
</body>
</html>
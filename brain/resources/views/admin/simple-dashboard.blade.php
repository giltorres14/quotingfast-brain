<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>The Brain - Admin Dashboard</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f8fafc;
            color: #1a202c;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .header h1 {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }
        
        .header p {
            opacity: 0.9;
            font-size: 1.1rem;
        }
        
        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }
        
        .dashboard-card {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            border: 1px solid #e2e8f0;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .dashboard-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
        
        .card-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }
        
        .card-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #2d3748;
        }
        
        .card-description {
            color: #718096;
            margin-bottom: 1.5rem;
            line-height: 1.5;
        }
        
        .card-button {
            display: inline-block;
            background: #4299e1;
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: background 0.2s;
        }
        
        .card-button:hover {
            background: #3182ce;
        }
        
        .stats-section {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            border: 1px solid #e2e8f0;
            margin-bottom: 2rem;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
        }
        
        .stat-item {
            text-align: center;
        }
        
        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 0.5rem;
        }
        
        .stat-label {
            color: #718096;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        
        .system-status {
            background: #f0fff4;
            border: 1px solid #9ae6b4;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 2rem;
        }
        
        .status-indicator {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #22543d;
            font-weight: 500;
        }
        
        .status-dot {
            width: 8px;
            height: 8px;
            background: #38a169;
            border-radius: 50%;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        
        .endpoints-list {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            border: 1px solid #e2e8f0;
        }
        
        .endpoints-list h3 {
            margin-bottom: 1rem;
            color: #2d3748;
        }
        
        .endpoint-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 0;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .endpoint-item:last-child {
            border-bottom: none;
        }
        
        .endpoint-path {
            font-family: 'Monaco', 'Menlo', monospace;
            background: #f7fafc;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.875rem;
        }
        
        .endpoint-method {
            background: #4299e1;
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 500;
        }
        
        @media (max-width: 768px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🧠 The Brain - Admin Dashboard</h1>
        <p>Complete Lead Management & Analytics System</p>
    </div>
    
    <div class="container">
        <div class="system-status">
            <div class="status-indicator">
                <div class="status-dot"></div>
                System Status: Online & Operational
            </div>
        </div>
        
        <div class="stats-section">
            <h2 style="margin-bottom: 1.5rem; color: #2d3748;">System Overview</h2>
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-value" id="total-leads">Loading...</div>
                    <div class="stat-label">Total Leads</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value" id="webhook-calls">Loading...</div>
                    <div class="stat-label">Webhook Calls</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value" id="active-campaigns">5</div>
                    <div class="stat-label">Active Campaigns</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value" id="system-uptime">99.9%</div>
                    <div class="stat-label">System Uptime</div>
                </div>
            </div>
        </div>
        
        <div class="dashboard-grid">
            <div class="dashboard-card">
                <div class="card-icon">📊</div>
                <h3 class="card-title">Call Analytics Dashboard</h3>
                <p class="card-description">
                    Comprehensive analytics with lead conversion metrics, timing analysis, agent performance, and revenue tracking.
                </p>
                <a href="/analytics" class="card-button">View Analytics</a>
            </div>
            
            <div class="dashboard-card">
                <div class="card-icon">👥</div>
                <h3 class="card-title">Agent Interface</h3>
                <p class="card-description">
                    Lead management interface for agents with Allstate validation, enrichment tools, and comprehensive editing capabilities.
                </p>
                <a href="/agent/lead/BRAIN_TEST_RINGBA" class="card-button">View Agent Interface</a>
            </div>
            
            <div class="dashboard-card">
                <div class="card-icon">🔗</div>
                <h3 class="card-title">Webhook Management</h3>
                <p class="card-description">
                    Monitor and manage webhook integrations with Vici, Ringba, Allstate, and other systems.
                </p>
                <a href="/webhook/status" class="card-button">View Webhooks</a>
            </div>
            
            <div class="dashboard-card">
                <div class="card-icon">🧪</div>
                <h3 class="card-title">System Testing</h3>
                <p class="card-description">
                    Test system components, API endpoints, data normalization, and integration functionality.
                </p>
                <a href="/test" class="card-button">Run Tests</a>
            </div>
            
            <div class="dashboard-card">
                <div class="card-icon">💼</div>
                <h3 class="card-title">Lead Management</h3>
                <p class="card-description">
                    Browse, search, and manage leads with advanced filtering and bulk operations.
                </p>
                <a href="#" class="card-button" onclick="alert('Coming Soon!')">Manage Leads</a>
            </div>
            
            <div class="dashboard-card">
                <div class="card-icon">⚙️</div>
                <h3 class="card-title">System Configuration</h3>
                <p class="card-description">
                    Configure system settings, API keys, webhook URLs, and integration parameters.
                </p>
                <a href="#" class="card-button" onclick="alert('Coming Soon!')">Settings</a>
            </div>
        </div>
        
        <div class="endpoints-list">
            <h3>🔗 Available API Endpoints</h3>
            <div class="endpoint-item">
                <span class="endpoint-path">/analytics</span>
                <span class="endpoint-method">GET</span>
            </div>
            <div class="endpoint-item">
                <span class="endpoint-path">/api/analytics/quick/{period}</span>
                <span class="endpoint-method">GET</span>
            </div>
            <div class="endpoint-item">
                <span class="endpoint-path">/webhook/vici</span>
                <span class="endpoint-method">POST</span>
            </div>
            <div class="endpoint-item">
                <span class="endpoint-path">/webhook/ringba-conversion</span>
                <span class="endpoint-method">POST</span>
            </div>
            <div class="endpoint-item">
                <span class="endpoint-path">/webhook/allstate</span>
                <span class="endpoint-method">POST</span>
            </div>
            <div class="endpoint-item">
                <span class="endpoint-path">/agent/lead/{id}</span>
                <span class="endpoint-method">GET</span>
            </div>
        </div>
    </div>
    
    <script>
        // Load basic stats
        fetch('/api/webhooks')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('webhook-calls').textContent = data.stats?.total_calls || '0';
                }
            })
            .catch(error => {
                document.getElementById('webhook-calls').textContent = 'N/A';
            });
        
        // Simulate lead count (replace with real API call when available)
        document.getElementById('total-leads').textContent = 'N/A';
        
        // Add current timestamp
        const now = new Date();
        document.title = `The Brain Admin - ${now.toLocaleDateString()}`;
    </script>
</body>
</html>
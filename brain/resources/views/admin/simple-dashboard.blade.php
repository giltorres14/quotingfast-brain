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
        
        /* Header Navigation */
        .navbar {
            background: #4f46e5;
            color: white;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 100;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .nav-container {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 2rem;
            height: 70px;
        }
        
        .nav-brand {
            font-size: 1.2rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            color: white;
        }
        
        .brand-logo {
            width: 32px;
            height: 32px;
            background: #4f46e5;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            color: white;
        }
        
        .nav-menu {
            display: flex;
            list-style: none;
            gap: 2rem;
            align-items: center;
        }
        
        .nav-item {
            position: relative;
        }
        
        .nav-link {
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            padding: 0.75rem 1.25rem;
            border-radius: 8px;
            transition: all 0.2s ease;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.95rem;
        }
        
        .nav-link:hover {
            background: rgba(255,255,255,0.1);
            color: white;
        }
        
        .nav-link.active {
            background: rgba(255,255,255,0.15);
            color: white;
            font-weight: 600;
        }
        
        /* Page Header */
        .page-header {
            background: white;
            border-bottom: 1px solid #e2e8f0;
            padding: 2rem 0;
        }
        
        .page-header-content {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 2rem;
        }
        
        .page-title {
            font-size: 2rem;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 0.5rem;
        }
        
        .page-subtitle {
            color: #718096;
            font-size: 1.1rem;
        }
        
        /* Main Container */
        .main-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        /* Feature Grid */
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }
        
        /* Feature Cards */
        .feature-card {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .feature-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 30px rgba(0,0,0,0.15);
        }
        
        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: #4f46e5;
        }
        
        .feature-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            display: block;
        }
        
        .feature-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.75rem;
            color: #2d3748;
        }
        
        .feature-description {
            color: #718096;
            margin-bottom: 1.5rem;
            line-height: 1.6;
            font-size: 1rem;
        }
        
        .feature-stats {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .stat-item {
            text-align: center;
            flex: 1;
        }
        
        .stat-number {
            font-size: 1.5rem;
            font-weight: 700;
            color: #4f46e5;
            display: block;
        }
        
        .stat-label {
            font-size: 0.85rem;
            color: #718096;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }
        
        .btn-primary {
            background: #4f46e5;
            color: white;
        }
        
        .btn-primary:hover {
            background: #4338ca;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(79, 70, 229, 0.4);
        }
        
        .btn-secondary {
            background: #f7fafc;
            color: #4a5568;
            border: 1px solid #e2e8f0;
        }
        
        .btn-secondary:hover {
            background: #edf2f7;
            transform: translateY(-2px);
        }
        
        .btn-success {
            background: #48bb78;
            color: white;
        }
        
        .btn-success:hover {
            background: #38a169;
            transform: translateY(-2px);
        }
        
        .btn-warning {
            background: #ed8936;
            color: white;
        }
        
        .btn-warning:hover {
            background: #dd6b20;
            transform: translateY(-2px);
        }
        
        /* Quick Stats Bar */
        .quick-stats {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            border: 1px solid #e2e8f0;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
        }
        
        .quick-stat {
            text-align: center;
        }
        
        .quick-stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: #4f46e5;
            display: block;
            margin-bottom: 0.5rem;
        }
        
        .quick-stat-label {
            color: #718096;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 0.9rem;
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .nav-container {
                padding: 0 1rem;
            }
            
            .nav-menu {
                gap: 1rem;
            }
            
            .main-container {
                padding: 1rem;
            }
            
            .features-grid {
                grid-template-columns: 1fr;
            }
            
            .page-header-content {
                padding: 0 1rem;
            }
        }
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
    <!-- Navigation Header -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-brand">
                <div class="brand-logo">B</div>
                <span>The Brain</span>
            </div>
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="/admin" class="nav-link active">
                        Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/leads" class="nav-link">
                        Leads
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#messaging" class="nav-link" onclick="alert('SMS/Messaging feature coming soon!')">
                        Messaging
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/analytics" class="nav-link">
                        Analytics
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#campaigns" class="nav-link" onclick="alert('Campaign management feature coming soon!')">
                        Campaigns
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#settings" class="nav-link" onclick="alert('Settings feature coming soon!')">
                        Settings
                    </a>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-content">
            <h1 class="page-title">Dashboard</h1>
            <p class="page-subtitle">Auto Insurance Leads Management System</p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-container">
        <!-- Quick Stats -->
        <div class="quick-stats">
            <div class="stats-grid">
                <div class="quick-stat">
                    <span class="quick-stat-number" id="total-leads">11</span>
                    <span class="quick-stat-label">Total Leads</span>
                </div>
                <div class="quick-stat">
                    <span class="quick-stat-number" id="new-leads">11</span>
                    <span class="quick-stat-label">New Leads</span>
                </div>
                <div class="quick-stat">
                    <span class="quick-stat-number" id="contacted">0</span>
                    <span class="quick-stat-label">Contacted</span>
                </div>
                <div class="quick-stat">
                    <span class="quick-stat-number" id="converted">0</span>
                    <span class="quick-stat-label">Converted</span>
                </div>
            </div>
        </div>

        <!-- Feature Cards -->
        <div class="features-grid">
            <!-- Lead Management -->
            <div class="feature-card">
                <span class="feature-icon">👥</span>
                <h3 class="feature-title">Lead Management</h3>
                <p class="feature-description">
                    Comprehensive lead database with advanced search, filtering, and bulk operations. 
                    Track lead sources, status, and conversion metrics.
                </p>
                <div class="feature-stats">
                    <div class="stat-item">
                        <span class="stat-number" id="leads-today">47</span>
                        <span class="stat-label">Today</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number" id="leads-week">312</span>
                        <span class="stat-label">This Week</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number" id="leads-qualified">89</span>
                        <span class="stat-label">Qualified</span>
                    </div>
                </div>
                                                            <div class="action-buttons">
                    <a href="/leads" class="btn btn-primary">
                        View Leads
                    </a>
                </div>
            </div>

            <!-- SMS Management -->
            <div class="feature-card">
                <span class="feature-icon">💬</span>
                <h3 class="feature-title">SMS Management</h3>
                <p class="feature-description">
                    Send, schedule, and track SMS campaigns. Automated follow-ups, 
                    templates, and compliance management for all messaging.
                </p>
                <div class="feature-stats">
                    <div class="stat-item">
                        <span class="stat-number" id="sms-sent">2,341</span>
                        <span class="stat-label">Sent</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number" id="sms-delivered">94.2%</span>
                        <span class="stat-label">Delivered</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number" id="sms-replies">187</span>
                        <span class="stat-label">Replies</span>
                    </div>
                </div>
                <div class="action-buttons">
                    <a href="#" class="btn btn-primary">
                        📱 SMS Dashboard
                    </a>
                    <a href="#" class="btn btn-secondary">
                        ✉️ New Campaign
                    </a>
                </div>
            </div>

            <!-- Analytics & Reporting -->
            <div class="feature-card">
                <span class="feature-icon">📊</span>
                <h3 class="feature-title">Analytics & Reporting</h3>
                <p class="feature-description">
                    Real-time analytics with conversion tracking, agent performance, 
                    revenue metrics, and comprehensive business intelligence.
                </p>
                <div class="feature-stats">
                    <div class="stat-item">
                        <span class="stat-number" id="reports-generated">156</span>
                        <span class="stat-label">Reports</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number" id="avg-call-time">4.2m</span>
                        <span class="stat-label">Avg Call</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number" id="top-agent">Sarah M.</span>
                        <span class="stat-label">Top Agent</span>
                    </div>
                </div>
                <div class="action-buttons">
                    <a href="/analytics" class="btn btn-primary">
                        📈 View Analytics
                    </a>
                    <a href="#" class="btn btn-secondary">
                        📋 Generate Report
                    </a>
                </div>
            </div>

            <!-- Lead Cost Reporting -->
            <div class="feature-card">
                <span class="feature-icon">💰</span>
                <h3 class="feature-title">Lead Cost Analytics</h3>
                <p class="feature-description">
                    Track lead acquisition costs by source, state, and time period. 
                    Monitor daily spend, ROI metrics, and cost optimization opportunities.
                </p>
                <div class="feature-stats">
                    <div class="stat-item">
                        <span class="stat-number" id="cost-today">$0.00</span>
                        <span class="stat-label">Today's Cost</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number" id="avg-cost-lead">$0.00</span>
                        <span class="stat-label">Avg/Lead</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number" id="top-cost-source">-</span>
                        <span class="stat-label">Top Source</span>
                    </div>
                </div>
                <div class="action-buttons">
                    <a href="#" class="btn btn-primary" onclick="showCostReports()">
                        💰 Cost Reports
                    </a>
                    <a href="#" class="btn btn-secondary" onclick="showCostByState()">
                        📍 By State
                    </a>
                </div>
            </div>

            <!-- Campaign Management -->
            <div class="feature-card">
                <span class="feature-icon">🎯</span>
                <h3 class="feature-title">Campaign Management</h3>
                <p class="feature-description">
                    Create, manage, and optimize marketing campaigns across multiple channels. 
                    Track ROI, conversion rates, and campaign performance.
                </p>
                <div class="feature-stats">
                    <div class="stat-item">
                        <span class="stat-number" id="active-campaigns-detail">8</span>
                        <span class="stat-label">Active</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number" id="campaign-roi">312%</span>
                        <span class="stat-label">Avg ROI</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number" id="best-campaign">Auto-23</span>
                        <span class="stat-label">Top Campaign</span>
                    </div>
                </div>
                <div class="action-buttons">
                    <a href="#" class="btn btn-primary">
                        🎯 View Campaigns
                    </a>
                    <a href="#" class="btn btn-success">
                        🚀 New Campaign
                    </a>
                </div>
            </div>

            <!-- System Monitoring -->
            <div class="feature-card">
                <span class="feature-icon">🔧</span>
                <h3 class="feature-title">System Monitoring</h3>
                <p class="feature-description">
                    Monitor system health, API integrations, webhook status, and performance metrics. 
                    Real-time alerts and diagnostics.
                </p>
                <div class="feature-stats">
                    <div class="stat-item">
                        <span class="stat-number" id="system-uptime">99.9%</span>
                        <span class="stat-label">Uptime</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number" id="api-calls">15.2K</span>
                        <span class="stat-label">API Calls</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number" id="webhooks-active">12</span>
                        <span class="stat-label">Webhooks</span>
                    </div>
                </div>
                <div class="action-buttons">
                    <a href="/webhook/status" class="btn btn-primary">
                        🔍 System Status
                    </a>
                    <a href="/test" class="btn btn-warning">
                        🧪 Run Tests
                    </a>
                </div>
            </div>

            <!-- Settings & Configuration -->
            <div class="feature-card">
                <span class="feature-icon">⚙️</span>
                <h3 class="feature-title">Settings & Configuration</h3>
                <p class="feature-description">
                    Configure system settings, API keys, user permissions, and integration parameters. 
                    Manage security and compliance settings.
                </p>
                <div class="feature-stats">
                    <div class="stat-item">
                        <span class="stat-number" id="integrations">8</span>
                        <span class="stat-label">Integrations</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number" id="users">24</span>
                        <span class="stat-label">Users</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number" id="last-backup">2h ago</span>
                        <span class="stat-label">Last Backup</span>
                    </div>
                </div>
                <div class="action-buttons">
                    <a href="#" class="btn btn-primary">
                        ⚙️ Settings
                    </a>
                    <a href="#" class="btn btn-secondary">
                        👥 Manage Users
                    </a>
                </div>
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
        
        // Load cost analytics data
        async function loadCostAnalytics() {
            try {
                const response = await fetch('/api/reports/cost/today');
                const data = await response.json();
                
                // Update cost stats
                document.getElementById('cost-today').textContent = `$${data.summary.total_cost}`;
                document.getElementById('avg-cost-lead').textContent = `$${data.summary.average_cost_per_lead}`;
                
                // Find top cost source
                if (data.by_source && data.by_source.length > 0) {
                    const topSource = data.by_source.reduce((max, source) => 
                        source.total_cost > max.total_cost ? source : max
                    );
                    document.getElementById('top-cost-source').textContent = topSource.source;
                }
            } catch (error) {
                console.error('Failed to load cost analytics:', error);
            }
        }

        // Show cost reports modal/popup
        function showCostReports() {
            fetch('/api/reports/cost/today')
                .then(response => response.json())
                .then(data => {
                    let reportHtml = `
                        <div style="background: white; padding: 2rem; border-radius: 8px; max-width: 800px; margin: 2rem auto; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                            <h2>📊 Today's Lead Cost Report</h2>
                            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; margin: 1rem 0;">
                                <div style="text-align: center; padding: 1rem; background: #f7fafc; border-radius: 6px;">
                                    <div style="font-size: 1.5rem; font-weight: bold; color: #2d3748;">$${data.summary.total_cost}</div>
                                    <div style="color: #718096;">Total Cost Today</div>
                                </div>
                                <div style="text-align: center; padding: 1rem; background: #f7fafc; border-radius: 6px;">
                                    <div style="font-size: 1.5rem; font-weight: bold; color: #2d3748;">${data.summary.total_leads}</div>
                                    <div style="color: #718096;">Total Leads</div>
                                </div>
                                <div style="text-align: center; padding: 1rem; background: #f7fafc; border-radius: 6px;">
                                    <div style="font-size: 1.5rem; font-weight: bold; color: #2d3748;">$${data.summary.average_cost_per_lead}</div>
                                    <div style="color: #718096;">Avg Cost/Lead</div>
                                </div>
                            </div>
                            
                            <h3>💰 Cost by Source</h3>
                            <div style="margin: 1rem 0;">
                                ${data.by_source.map(source => `
                                    <div style="display: flex; justify-content: space-between; padding: 0.5rem; border-bottom: 1px solid #e2e8f0;">
                                        <span>${source.source}</span>
                                        <span><strong>$${source.total_cost}</strong> (${source.count} leads)</span>
                                    </div>
                                `).join('')}
                            </div>
                            
                            <h3>📍 Cost by State</h3>
                            <div style="margin: 1rem 0;">
                                ${data.by_state.map(state => `
                                    <div style="display: flex; justify-content: space-between; padding: 0.5rem; border-bottom: 1px solid #e2e8f0;">
                                        <span>${state.state}</span>
                                        <span><strong>$${state.total_cost}</strong> (${state.count} leads)</span>
                                    </div>
                                `).join('')}
                            </div>
                            
                            <button onclick="closeCostReport()" style="background: #4299e1; color: white; padding: 0.5rem 1rem; border: none; border-radius: 4px; margin-top: 1rem;">Close</button>
                        </div>
                        <div onclick="closeCostReport()" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;"></div>
                    `;
                    
                    const modal = document.createElement('div');
                    modal.id = 'cost-report-modal';
                    modal.style.cssText = 'position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 1001; overflow-y: auto;';
                    modal.innerHTML = reportHtml;
                    document.body.appendChild(modal);
                })
                .catch(error => {
                    alert('Failed to load cost report. Please try again.');
                    console.error('Cost report error:', error);
                });
        }

        function showCostByState() {
            const state = prompt('Enter state code (e.g., TX, CA, FL):');
            if (state) {
                window.open(`/api/reports/cost/state/${state}`, '_blank');
            }
        }

        function closeCostReport() {
            const modal = document.getElementById('cost-report-modal');
            if (modal) {
                modal.remove();
            }
        }

        // Clean up test leads
        // REMOVED: cleanupTestLeads function per user request
        // This was automatically deleting leads on deployment

        // Load cost analytics on page load
        loadCostAnalytics();
        
        // Add current timestamp
        const now = new Date();
        document.title = `The Brain Admin - ${now.toLocaleDateString()}`;
    </script>
</body>
</html>
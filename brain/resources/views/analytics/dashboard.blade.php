<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Call Analytics Dashboard</title>
    <link rel="icon" type="image/png" href="/favicon.png">
    <link rel="shortcut icon" href="/favicon.ico">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f5f7fa;
            color: #333;
        }
        
        .header {
            background: #2563eb;
            color: white;
            padding: 1rem 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .header h1 {
            font-size: 1.5rem;
            font-weight: 600;
        }
        
        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        
        .controls {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        
        .controls h2 {
            margin-bottom: 1rem;
            color: #374151;
            font-size: 1.1rem;
        }
        
        .control-row {
            display: flex;
            gap: 1rem;
            align-items: end;
            flex-wrap: wrap;
        }
        
        .control-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        
        .control-group label {
            font-weight: 500;
            color: #6b7280;
            font-size: 0.875rem;
        }
        
        .control-group input,
        .control-group select,
        .control-group button {
            padding: 0.5rem 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            font-size: 0.875rem;
        }
        
        .control-group button {
            background: #2563eb;
            color: white;
            border: none;
            cursor: pointer;
            font-weight: 500;
        }
        
        .control-group button:hover {
            background: #1d4ed8;
        }
        
        .quick-periods {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
            margin-top: 1rem;
        }
        
        .quick-period {
            padding: 0.5rem 1rem;
            background: #f3f4f6;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.875rem;
            transition: all 0.2s;
        }
        
        .quick-period:hover,
        .quick-period.active {
            background: #2563eb;
            color: white;
            border-color: #2563eb;
        }
        
        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .metric-card {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .metric-card h3 {
            color: #6b7280;
            font-size: 0.875rem;
            font-weight: 500;
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        
        .metric-value {
            font-size: 2rem;
            font-weight: 700;
            color: #111827;
            margin-bottom: 0.25rem;
        }
        
        .metric-secondary {
            font-size: 0.875rem;
            color: #6b7280;
        }
        
        .loading {
            text-align: center;
            padding: 2rem;
            color: #6b7280;
        }
        
        .error {
            background: #fef2f2;
            color: #dc2626;
            padding: 1rem;
            border-radius: 4px;
            border: 1px solid #fecaca;
            margin-bottom: 1rem;
        }
        
        .tables-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
        }
        
        .table-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .table-card h3 {
            padding: 1rem 1.5rem;
            background: #f9fafb;
            border-bottom: 1px solid #e5e7eb;
            margin: 0;
            font-size: 1rem;
            color: #374151;
        }
        
        .table-card table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .table-card th,
        .table-card td {
            padding: 0.75rem 1.5rem;
            text-align: left;
            border-bottom: 1px solid #f3f4f6;
            font-size: 0.875rem;
        }
        
        .table-card th {
            background: #f9fafb;
            font-weight: 600;
            color: #6b7280;
        }
        
        @media (max-width: 768px) {
            .tables-section {
                grid-template-columns: 1fr;
            }
            
            .control-row {
                flex-direction: column;
                align-items: stretch;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>📊 Call Analytics Dashboard</h1>
    </div>
    
    <div class="container">
        <div class="controls">
            <h2>Date Range & Filters</h2>
            <div class="control-row">
                <div class="control-group">
                    <label for="startDate">Start Date</label>
                    <input type="date" id="startDate" value="">
                </div>
                <div class="control-group">
                    <label for="endDate">End Date</label>
                    <input type="date" id="endDate" value="">
                </div>
                <div class="control-group">
                    <label for="agentFilter">Agent ID</label>
                    <input type="text" id="agentFilter" placeholder="Optional">
                </div>
                <div class="control-group">
                    <label for="campaignFilter">Campaign ID</label>
                    <input type="text" id="campaignFilter" placeholder="Optional">
                </div>
                <div class="control-group">
                    <label for="buyerFilter">Buyer</label>
                    <select id="buyerFilter">
                        <option value="">All Buyers</option>
                        <option value="allstate">Allstate</option>
                        <option value="progressive">Progressive</option>
                        <option value="geico">GEICO</option>
                    </select>
                </div>
                <div class="control-group">
                    <button onclick="loadAnalytics()">Load Analytics</button>
                </div>
            </div>
            
            <div class="quick-periods">
                <div class="quick-period" onclick="loadQuickPeriod('today')">Today</div>
                <div class="quick-period" onclick="loadQuickPeriod('yesterday')">Yesterday</div>
                <div class="quick-period" onclick="loadQuickPeriod('this_week')">This Week</div>
                <div class="quick-period" onclick="loadQuickPeriod('last_week')">Last Week</div>
                <div class="quick-period" onclick="loadQuickPeriod('this_month')">This Month</div>
                <div class="quick-period" onclick="loadQuickPeriod('last_month')">Last Month</div>
                <div class="quick-period" onclick="loadQuickPeriod('last_30_days')">Last 30 Days</div>
            </div>
        </div>
        
        <div id="error-container"></div>
        
        <div id="analytics-container">
            <div class="loading">
                📊 Select a date range to load analytics...
            </div>
        </div>
    </div>
    
    <script>
        // Set default dates (last 7 days)
        const today = new Date();
        const lastWeek = new Date(today.getTime() - 7 * 24 * 60 * 60 * 1000);
        
        document.getElementById('endDate').value = today.toISOString().split('T')[0];
        document.getElementById('startDate').value = lastWeek.toISOString().split('T')[0];
        
        function loadQuickPeriod(period) {
            // Remove active class from all periods
            document.querySelectorAll('.quick-period').forEach(el => el.classList.remove('active'));
            // Add active class to clicked period
            event.target.classList.add('active');
            
            const filters = getFilters();
            
            fetch(`/api/analytics/quick/${period}?${new URLSearchParams(filters)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayAnalytics(data.data, data.period_label);
                        // Update date inputs to match the period
                        const period_data = data.data.period;
                        document.getElementById('startDate').value = period_data.start;
                        document.getElementById('endDate').value = period_data.end;
                    } else {
                        showError(data.error);
                    }
                })
                .catch(error => {
                    showError('Failed to load analytics: ' + error.message);
                });
        }
        
        function loadAnalytics() {
            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate').value;
            
            if (!startDate || !endDate) {
                showError('Please select both start and end dates');
                return;
            }
            
            const filters = getFilters();
            
            document.getElementById('analytics-container').innerHTML = '<div class="loading">📊 Loading analytics...</div>';
            
            fetch(`/api/analytics/${startDate}/${endDate}?${new URLSearchParams(filters)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayAnalytics(data.data, `${startDate} to ${endDate}`);
                    } else {
                        showError(data.error);
                    }
                })
                .catch(error => {
                    showError('Failed to load analytics: ' + error.message);
                });
        }
        
        function getFilters() {
            const filters = {};
            const agentId = document.getElementById('agentFilter').value.trim();
            const campaignId = document.getElementById('campaignFilter').value.trim();
            const buyer = document.getElementById('buyerFilter').value;
            
            if (agentId) filters.agent_id = agentId;
            if (campaignId) filters.campaign_id = campaignId;
            if (buyer) filters.buyer_name = buyer;
            
            return filters;
        }
        
        function displayAnalytics(data, periodLabel) {
            const container = document.getElementById('analytics-container');
            
            container.innerHTML = `
                <div class="metrics-grid">
                    <div class="metric-card">
                        <h3>Total Leads</h3>
                        <div class="metric-value">${data.lead_metrics.total_leads.toLocaleString()}</div>
                        <div class="metric-secondary">${data.period.days} days</div>
                    </div>
                    
                    <div class="metric-card">
                        <h3>Call Rate</h3>
                        <div class="metric-value">${data.lead_metrics.call_rate}%</div>
                        <div class="metric-secondary">${data.lead_metrics.leads_with_calls.toLocaleString()} leads called</div>
                    </div>
                    
                    <div class="metric-card">
                        <h3>Transfer Rate</h3>
                        <div class="metric-value">${data.lead_metrics.transfer_rate}%</div>
                        <div class="metric-secondary">${data.lead_metrics.leads_with_transfers.toLocaleString()} transfers</div>
                    </div>
                    
                    <div class="metric-card">
                        <h3>Conversions</h3>
                        <div class="metric-value">${data.conversion_metrics.successful_conversions.toLocaleString()}</div>
                        <div class="metric-secondary">${data.conversion_metrics.conversion_rate}% rate</div>
                    </div>
                    
                    <div class="metric-card">
                        <h3>Total Revenue</h3>
                        <div class="metric-value">$${data.conversion_metrics.total_revenue.toLocaleString()}</div>
                        <div class="metric-secondary">$${data.conversion_metrics.avg_conversion_value} avg</div>
                    </div>
                    
                    <div class="metric-card">
                        <h3>Avg Time to First Call</h3>
                        <div class="metric-value">${data.timing_metrics.avg_time_to_first_call_minutes}m</div>
                        <div class="metric-secondary">${data.timing_metrics.avg_time_to_first_call}s exact</div>
                    </div>
                    
                    <div class="metric-card">
                        <h3>Total Call Attempts</h3>
                        <div class="metric-value">${data.call_metrics.total_call_attempts.toLocaleString()}</div>
                        <div class="metric-secondary">${data.call_metrics.connection_rate}% connected</div>
                    </div>
                    
                    <div class="metric-card">
                        <h3>Total Talk Time</h3>
                        <div class="metric-value">${Math.round(data.call_metrics.total_talk_time / 60)}m</div>
                        <div class="metric-secondary">${data.call_metrics.avg_talk_time}s avg</div>
                    </div>
                </div>
                
                <div class="tables-section">
                    <div class="table-card">
                        <h3>Agent Performance</h3>
                        <table>
                            <thead>
                                <tr>
                                    <th>Agent ID</th>
                                    <th>Calls</th>
                                    <th>Connection %</th>
                                    <th>Transfers</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${data.agent_performance.slice(0, 10).map(agent => `
                                    <tr>
                                        <td>${agent.agent_id}</td>
                                        <td>${agent.total_calls}</td>
                                        <td>${agent.connection_rate}%</td>
                                        <td>${agent.transfers}</td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="table-card">
                        <h3>Buyer Performance</h3>
                        <table>
                            <thead>
                                <tr>
                                    <th>Buyer</th>
                                    <th>Leads</th>
                                    <th>Conversion %</th>
                                    <th>Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${data.buyer_performance.map(buyer => `
                                    <tr>
                                        <td>${buyer.buyer_name || 'Unknown'}</td>
                                        <td>${buyer.total_leads}</td>
                                        <td>${buyer.conversion_rate}%</td>
                                        <td>$${buyer.revenue.toLocaleString()}</td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    </div>
                </div>
            `;
        }
        
        function showError(message) {
            const container = document.getElementById('error-container');
            container.innerHTML = `<div class="error">❌ ${message}</div>`;
            setTimeout(() => {
                container.innerHTML = '';
            }, 5000);
        }
        
        // Load last 7 days by default
        loadQuickPeriod('last_week');
    </script>
</body>
</html>
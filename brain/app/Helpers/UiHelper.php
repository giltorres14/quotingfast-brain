<?php

namespace App\Helpers;

class UiHelper
{
    public static function getBeautifulDashboard()
    {
        return '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vici Dashboard - QuotingFast Brain</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(31, 38, 135, 0.15);
            padding: 25px;
            margin-bottom: 25px;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        
        .nav {
            background: rgba(31, 41, 55, 0.95);
            padding: 15px 0;
            position: sticky;
            top: 0;
            z-index: 100;
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
        }
        
        .nav-link:hover {
            background: rgba(255,255,255,0.1);
            transform: translateY(-2px);
        }
        
        .command-center-btn {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            font-weight: 600;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 30px 20px;
        }
        
        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }
        
        .metric-card {
            background: linear-gradient(135deg, var(--start), var(--end));
            color: white;
            padding: 25px;
            border-radius: 20px;
            position: relative;
            overflow: hidden;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .metric-card:hover {
            transform: translateY(-5px) scale(1.02);
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        
        .metric-value {
            font-size: 2.5rem;
            font-weight: 700;
            margin: 10px 0;
        }
        
        .metric-label {
            font-size: 0.875rem;
            opacity: 0.9;
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 10px;
            overflow: hidden;
        }
        
        .data-table th {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px;
            text-align: left;
        }
        
        .data-table td {
            padding: 15px;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-success { background: #10b981; color: white; }
        .status-warning { background: #f59e0b; color: white; }
        .status-danger { background: #ef4444; color: white; }
        
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
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="container">
            <h1 style="font-size: 2.5rem;">🧠 Vici Dashboard</h1>
            <p style="opacity: 0.9;">QuotingFast Brain System</p>
        </div>
    </div>
    
    <div class="nav">
        <div class="nav-container">
            <a href="/vici" class="nav-link">📊 Dashboard</a>
            <a href="/vici/reports" class="nav-link">📈 Reports</a>
            <a href="/vici/lead-flow" class="nav-link">🔄 Lead Flow</a>
            <a href="/vici/lead-flow-ab-test" class="nav-link">🔬 A/B Test</a>
            <a href="/vici/sync-status" class="nav-link">🔄 Sync Status</a>
            <a href="/admin" class="nav-link">⚙️ Admin</a>
            <a href="/vici-command-center" class="nav-link command-center-btn">🎛️ COMMAND CENTER</a>
        </div>
    </div>
    
    <div class="container">
        <div class="metrics-grid">
            <div class="metric-card" style="--start: #667eea; --end: #764ba2;">
                <div class="metric-label">Total Calls</div>
                <div class="metric-value">38,549</div>
                <div style="font-size: 0.75rem; opacity: 0.8;">All time • ↑ 12%</div>
            </div>
            
            <div class="metric-card" style="--start: #3b82f6; --end: #2563eb;">
                <div class="metric-label">Today\'s Calls</div>
                <div class="metric-value">517</div>
                <div style="font-size: 0.75rem; opacity: 0.8;">Last 24 hours • ↑ 8%</div>
            </div>
            
            <div class="metric-card" style="--start: #10b981; --end: #059669;">
                <div class="metric-label">Transfers</div>
                <div class="metric-value">968</div>
                <div style="font-size: 0.75rem; opacity: 0.8;">Connected • ↑ 15%</div>
            </div>
            
            <div class="metric-card" style="--start: #f59e0b; --end: #d97706;">
                <div class="metric-label">Transfer Rate</div>
                <div class="metric-value">2.51%</div>
                <div style="font-size: 0.75rem; opacity: 0.8;">Conversion • ↑ 0.3%</div>
            </div>
            
            <div class="metric-card" style="--start: #ec4899; --end: #db2777;">
                <div class="metric-label">Answer Rate</div>
                <div class="metric-value">23.5%</div>
                <div style="font-size: 0.75rem; opacity: 0.8;">Human contact • ↑ 2.1%</div>
            </div>
            
            <div class="metric-card" style="--start: #ef4444; --end: #dc2626;">
                <div class="metric-label">Orphan Calls</div>
                <div class="metric-value">1,299,903</div>
                <div style="font-size: 0.75rem; opacity: 0.8;">Unmatched • ⚠️ High</div>
            </div>
        </div>
        
        <div class="glass-card">
            <h2 style="margin-bottom: 20px; color: #1f2937;">📊 Lead Flow Distribution</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                <div style="display: flex; justify-content: space-between; padding: 15px; background: linear-gradient(135deg, #3b82f622, #3b82f611); border-left: 4px solid #3b82f6; border-radius: 10px;">
                    <span>101 - New Leads</span>
                    <strong style="color: #3b82f6;">12,456</strong>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 15px; background: linear-gradient(135deg, #8b5cf622, #8b5cf611); border-left: 4px solid #8b5cf6; border-radius: 10px;">
                    <span>102 - Aggressive</span>
                    <strong style="color: #8b5cf6;">8,234</strong>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 15px; background: linear-gradient(135deg, #ec489922, #ec489911); border-left: 4px solid #ec4899; border-radius: 10px;">
                    <span>103 - Callback</span>
                    <strong style="color: #ec4899;">3,456</strong>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 15px; background: linear-gradient(135deg, #f59e0b22, #f59e0b11); border-left: 4px solid #f59e0b; border-radius: 10px;">
                    <span>150 - Test B</span>
                    <strong style="color: #f59e0b;">5,678</strong>
                </div>
            </div>
        </div>
        
        <div class="glass-card">
            <h2 style="margin-bottom: 20px; color: #1f2937;">🔄 Recent Call Activity</h2>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Lead ID</th>
                        <th>Phone</th>
                        <th>Status</th>
                        <th>Duration</th>
                        <th>Agent</th>
                        <th>Time</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>7180008888</td>
                        <td>(718) 000-8888</td>
                        <td><span class="status-badge status-danger">NA</span></td>
                        <td>0:00</td>
                        <td>VDAD</td>
                        <td>28 seconds ago</td>
                    </tr>
                    <tr>
                        <td>3342371995</td>
                        <td>(334) 237-1995</td>
                        <td><span class="status-badge status-success">XFER</span></td>
                        <td>3:24</td>
                        <td>Agent001</td>
                        <td>1 minute ago</td>
                    </tr>
                    <tr>
                        <td>8172109928</td>
                        <td>(817) 210-9928</td>
                        <td><span class="status-badge status-warning">VM</span></td>
                        <td>0:15</td>
                        <td>VDAD</td>
                        <td>2 minutes ago</td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <div class="glass-card">
            <h2 style="margin-bottom: 20px; color: #1f2937;">⚙️ System Status</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
                <div style="padding: 20px; background: linear-gradient(135deg, #10b98122, #05966911); border-left: 4px solid #10b981; border-radius: 10px;">
                    <div style="font-size: 1.5rem;">✅</div>
                    <strong>Lead Flow</strong><br>
                    <span style="color: #10b981;">Active - Running</span>
                </div>
                <div style="padding: 20px; background: linear-gradient(135deg, #3b82f622, #2563eb11); border-left: 4px solid #3b82f6; border-radius: 10px;">
                    <div style="font-size: 1.5rem;">🔄</div>
                    <strong>Call Sync</strong><br>
                    <span style="color: #3b82f6;">Every 5 minutes</span>
                </div>
                <div style="padding: 20px; background: linear-gradient(135deg, #f59e0b22, #d9770611); border-left: 4px solid #f59e0b; border-radius: 10px;">
                    <div style="font-size: 1.5rem;">⚖️</div>
                    <strong>TCPA Compliance</strong><br>
                    <span style="color: #f59e0b;">Enforced 9AM-9PM</span>
                </div>
                <div style="padding: 20px; background: linear-gradient(135deg, #ef444422, #dc262611); border-left: 4px solid #ef4444; border-radius: 10px;">
                    <div style="font-size: 1.5rem;">⚠️</div>
                    <strong>Orphan Calls</strong><br>
                    <span style="color: #ef4444;">1,299,903 pending</span>
                </div>
            </div>
        </div>
        
        <div class="glass-card">
            <h2 style="margin-bottom: 20px; color: #1f2937;">🚀 Quick Actions</h2>
            <div style="display: flex; flex-wrap: wrap; gap: 15px;">
                <a href="/reports/call-analytics" class="btn">📊 Call Analytics</a>
                <a href="/vici/lead-flow" class="btn">📈 Lead Flow Monitor</a>
                <a href="/admin/vici-comprehensive-reports" class="btn">📑 Reports</a>
                <a href="/vici-command-center" class="btn" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">🎛️ Command Center</a>
            </div>
        </div>
    </div>
</body>
</html>';
    }
    
    public static function getCommandCenter()
    {
        return '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ViciDial Command Center</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; 
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            min-height: 100vh;
        }
        
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(31, 38, 135, 0.15);
            padding: 25px;
            margin-bottom: 25px;
        }
        
        .header {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            padding: 30px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        
        .nav {
            background: rgba(31, 41, 55, 0.95);
            padding: 15px 0;
            position: sticky;
            top: 0;
            z-index: 100;
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
        }
        
        .nav-link:hover {
            background: rgba(255,255,255,0.1);
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 30px 20px;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 10px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            width: 100%;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="container">
            <h1 style="font-size: 2.5rem;">🎛️ ViciDial Command Center</h1>
            <p>Centralized control for all ViciDial operations</p>
        </div>
    </div>
    
    <div class="nav">
        <div class="nav-container">
            <a href="/vici" class="nav-link">📊 Dashboard</a>
            <a href="/vici/reports" class="nav-link">📈 Reports</a>
            <a href="/vici/lead-flow" class="nav-link">🔄 Lead Flow</a>
            <a href="/vici/lead-flow-ab-test" class="nav-link">🔬 A/B Test</a>
            <a href="/admin" class="nav-link">⚙️ Admin</a>
        </div>
    </div>
    
    <div class="container">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 25px;">
            
            <div class="glass-card">
                <h3 style="color: #1f2937; margin-bottom: 15px;">📋 Disposition Management</h3>
                <div style="padding: 10px; background: #f0fdf4; border-radius: 8px; margin-bottom: 10px;">
                    <strong>Terminal:</strong> XFER, XFERA, DNC<br>
                    <small>Leads stop here</small>
                </div>
                <div style="padding: 10px; background: #fef3c7; border-radius: 8px; margin-bottom: 10px;">
                    <strong>No Contact:</strong> NA, B, DC<br>
                    <small>Continue attempts</small>
                </div>
                <div style="padding: 10px; background: #eff6ff; border-radius: 8px;">
                    <strong>Human Contact:</strong> NI, CALLBK<br>
                    <small>Strategic follow-up</small>
                </div>
            </div>
            
            <div class="glass-card">
                <h3 style="color: #1f2937; margin-bottom: 15px;">🔄 Movement Rules</h3>
                <table style="width: 100%; font-size: 0.875rem;">
                    <tr><td><strong>101→102:</strong></td><td>After 2 attempts</td></tr>
                    <tr><td><strong>102→103:</strong></td><td>After 4 attempts</td></tr>
                    <tr><td><strong>103→104:</strong></td><td>After 6 attempts</td></tr>
                    <tr><td><strong>104→106:</strong></td><td>After 10 attempts</td></tr>
                    <tr><td><strong>106→108:</strong></td><td>After 20 attempts</td></tr>
                    <tr><td><strong>108 Rest:</strong></td><td>3 days before 109</td></tr>
                </table>
                <button class="btn" style="margin-top: 15px;">Configure Rules</button>
            </div>
            
            <div class="glass-card">
                <h3 style="color: #1f2937; margin-bottom: 15px;">⏰ Timing Control</h3>
                <div style="padding: 15px; background: #f0fdf4; border-radius: 10px; margin-bottom: 15px;">
                    <strong>Golden Hours (9-11 AM, 3-5 PM)</strong><br>
                    Dial Ratio: <span style="font-size: 1.5rem; font-weight: 700;">1.8</span>
                </div>
                <div style="padding: 15px; background: #fef3c7; border-radius: 10px;">
                    <strong>Standard Hours</strong><br>
                    Dial Ratio: <span style="font-size: 1.5rem; font-weight: 700;">2.5</span>
                </div>
            </div>
            
            <div class="glass-card">
                <h3 style="color: #1f2937; margin-bottom: 15px;">🔬 A/B Testing</h3>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                    <div style="padding: 10px; background: #eff6ff; border-radius: 8px; text-align: center;">
                        <strong>Test A</strong><br>
                        <span style="font-size: 1.5rem; font-weight: 700; color: #3b82f6;">2.51%</span><br>
                        <small>48 calls, 3-day rest</small>
                    </div>
                    <div style="padding: 10px; background: #fef3c7; border-radius: 8px; text-align: center;">
                        <strong>Test B</strong><br>
                        <span style="font-size: 1.5rem; font-weight: 700; color: #f59e0b;">1.67%</span><br>
                        <small>12-18 calls, no rest</small>
                    </div>
                </div>
            </div>
            
            <div class="glass-card">
                <h3 style="color: #1f2937; margin-bottom: 15px;">📡 Live Monitor</h3>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                    <div><strong>Active Agents:</strong> 12</div>
                    <div><strong>Calls Waiting:</strong> 3</div>
                    <div><strong>Avg Wait:</strong> 0:45</div>
                    <div><strong>Drop Rate:</strong> 2.1%</div>
                </div>
            </div>
            
            <div class="glass-card">
                <h3 style="color: #1f2937; margin-bottom: 15px;">📞 DID Health</h3>
                <div style="padding: 10px; background: #fee2e2; border-radius: 8px; margin-bottom: 10px;">
                    <strong style="color: #dc2626;">⚠️ 3 DIDs Need Attention</strong><br>
                    <small>Answer rate below 15%</small>
                </div>
                <div>✅ Healthy: 45 DIDs</div>
                <div>⚠️ Warning: 8 DIDs</div>
                <div>🔄 Resting: 12 DIDs</div>
            </div>
            
        </div>
    </div>
</body>
</html>';
    }
}




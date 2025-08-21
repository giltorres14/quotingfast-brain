<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vici Dashboard</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f5f5f5; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; }
        .container { max-width: 1400px; margin: 0 auto; padding: 20px; }
        .metrics { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .metric-card { background: white; padding: 20px; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .metric-label { font-size: 0.875rem; color: #6b7280; margin-bottom: 8px; }
        .metric-value { font-size: 2rem; font-weight: 700; color: #1f2937; }
        .metric-sub { font-size: 0.75rem; color: #9ca3af; margin-top: 4px; }
        .card { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .card-title { font-size: 1.25rem; font-weight: 600; color: #1f2937; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #f3f4f6; padding: 12px; text-align: left; font-size: 0.875rem; color: #6b7280; }
        td { padding: 12px; border-bottom: 1px solid #e5e7eb; }
        .status-badge { display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 500; }
        .status-xfer { background: #d1fae5; color: #065f46; }
        .status-na { background: #fee2e2; color: #991b1b; }
        .btn { display: inline-block; padding: 10px 20px; background: #4A90E2; color: white; text-decoration: none; border-radius: 6px; font-weight: 500; }
        .btn:hover { background: #357ABD; }
        .nav { background: #1f2937; padding: 10px 0; margin-bottom: 20px; }
        .nav-container { max-width: 1400px; margin: 0 auto; padding: 0 20px; display: flex; gap: 20px; }
        .nav-link { color: white; text-decoration: none; padding: 8px 16px; border-radius: 4px; }
        .nav-link:hover { background: rgba(255,255,255,0.1); }
        .command-center-btn { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 10px 20px; border-radius: 6px; font-weight: 600; }
    </style>
</head>
<body>
    <div class="header">
        <div class="container">
            <h1>Vici Dashboard</h1>
        </div>
    </div>
    
    <div class="nav">
        <div class="nav-container">
            <a href="/vici" class="nav-link">Dashboard</a>
            <a href="/vici/reports" class="nav-link">Reports</a>
            <a href="/vici/lead-flow" class="nav-link">Lead Flow</a>
            <a href="/vici/lead-flow-ab-test" class="nav-link">🔬 A/B Test</a>
            <a href="/vici/sync-status" class="nav-link">Sync Status</a>
            <a href="/vici/settings" class="nav-link">Settings</a>
            <a href="/vici-command-center" class="nav-link command-center-btn">🎛️ COMMAND CENTER</a>
        </div>
    </div>
    
    <div class="container">
        <!-- Metrics Row -->
        <div class="metrics">
            <div class="metric-card">
                <div class="metric-label">Total Calls</div>
                <div class="metric-value">{{ number_format($totalCalls ?? 0) }}</div>
                <div class="metric-sub">All time</div>
            </div>
            
            <div class="metric-card">
                <div class="metric-label">Today's Calls</div>
                <div class="metric-value">{{ number_format($todayCalls ?? 0) }}</div>
                <div class="metric-sub">Last 24 hours</div>
            </div>
            
            <div class="metric-card">
                <div class="metric-label">Connected</div>
                <div class="metric-value">{{ number_format($connectedCalls ?? 0) }}</div>
                <div class="metric-sub">Transferred calls</div>
            </div>
            
            <div class="metric-card">
                <div class="metric-label">Orphan Calls</div>
                <div class="metric-value">{{ number_format($orphanCalls ?? 0) }}</div>
                <div class="metric-sub">Unmatched</div>
            </div>
        </div>
        
        <!-- Lead Flow Distribution -->
        <div class="card">
            <h2 class="card-title">Lead Flow Distribution</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                @if(isset($listDistribution) && is_array($listDistribution))
                    @foreach($listDistribution as $list)
                        <div style="display: flex; justify-content: space-between; padding: 10px; background: #f9fafb; border-radius: 8px;">
                            <span>{{ $list['list'] ?? 'Unknown' }}</span>
                            <strong>{{ number_format($list['count'] ?? 0) }}</strong>
                        </div>
                    @endforeach
                @else
                    <p>No distribution data available</p>
                @endif
            </div>
            <div style="margin-top: 20px;">
                <a href="/vici/lead-flow" class="btn">View Lead Flow Monitor →</a>
            </div>
        </div>
        
        <!-- Recent Call Activity -->
        <div class="card">
            <h2 class="card-title">Recent Call Activity</h2>
            <table>
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
                    @if(isset($recentCalls) && count($recentCalls) > 0)
                        @foreach($recentCalls as $call)
                            <tr>
                                <td>{{ $call->vici_lead_id ?? 'N/A' }}</td>
                                <td>{{ $call->phone_number ?? 'N/A' }}</td>
                                <td>
                                    <span class="status-badge {{ in_array($call->call_status ?? '', ['XFER', 'XFERA']) ? 'status-xfer' : 'status-na' }}">
                                        {{ $call->call_status ?? 'N/A' }}
                                    </span>
                                </td>
                                <td>{{ gmdate('i:s', $call->talk_time ?? 0) }}</td>
                                <td>{{ $call->agent ?? 'N/A' }}</td>
                                <td>{{ isset($call->created_at) ? $call->created_at->diffForHumans() : 'N/A' }}</td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 20px; color: #6b7280;">
                                No recent calls found
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
        
        <!-- System Status -->
        <div class="card">
            <h2 class="card-title">System Status</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                <div style="padding: 15px; background: #f0fdf4; border-left: 4px solid #10b981; border-radius: 4px;">
                    <strong>Lead Flow</strong><br>
                    <span style="color: #10b981;">Active - Running</span>
                </div>
                <div style="padding: 15px; background: #eff6ff; border-left: 4px solid #3b82f6; border-radius: 4px;">
                    <strong>Call Sync</strong><br>
                    <span style="color: #3b82f6;">Every 5 minutes</span>
                </div>
                <div style="padding: 15px; background: #fef3c7; border-left: 4px solid #f59e0b; border-radius: 4px;">
                    <strong>TCPA Compliance</strong><br>
                    <span style="color: #f59e0b;">Enforced</span>
                </div>
                <div style="padding: 15px; background: #fef2f2; border-left: 4px solid #ef4444; border-radius: 4px;">
                    <strong>Orphan Calls</strong><br>
                    <span style="color: #ef4444;">{{ number_format($orphanCalls ?? 0) }} pending</span>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="card">
            <h2 class="card-title">Quick Actions</h2>
            <div style="display: flex; flex-wrap: wrap; gap: 10px;">
                <a href="/reports/call-analytics" class="btn">📊 View Call Reports</a>
                <a href="/vici/lead-flow" class="btn" style="background: #6b7280;">📈 Monitor Lead Flow</a>
                <a href="/vici/sync-status" class="btn" style="background: #6b7280;">🔄 Check Sync Status</a>
                <a href="/vici/settings" class="btn" style="background: #6b7280;">⚙️ Vici Settings</a>
            </div>
        </div>
    </div>
</body>
</html>




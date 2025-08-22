<!DOCTYPE html>
<html>
<head>
    <title>Vici Sync Management</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        h1 { color: #333; }
        .stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin: 20px 0; }
        .stat-card { background: #f8f9fa; padding: 20px; border-radius: 8px; text-align: center; }
        .stat-value { font-size: 2em; font-weight: bold; color: #4A90E2; }
        .stat-label { color: #666; margin-top: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔄 Vici Call Log Sync Management</h1>
        
        <div class="stats">
            <div class="stat-card">
                <div class="stat-value">{{ number_format($totalCallLogs ?? 0) }}</div>
                <div class="stat-label">Total Call Logs</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">{{ number_format($totalViciMetrics ?? 0) }}</div>
                <div class="stat-label">Vici Metrics</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">0</div>
                <div class="stat-label">Synced Today</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">Ready</div>
                <div class="stat-label">Status</div>
            </div>
        </div>
        
        <p><a href="/vici">← Back to Vici Dashboard</a></p>
    </div>
</body>
</html>






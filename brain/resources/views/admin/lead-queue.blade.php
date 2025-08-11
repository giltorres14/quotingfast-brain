<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lead Queue Monitor - Brain</title>
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
            overflow-x: hidden;
            width: 100%;
            max-width: 100%;
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        h1 {
            color: #333;
            font-size: 32px;
            margin-bottom: 10px;
        }
        
        .subtitle {
            color: #666;
            font-size: 16px;
        }
        
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
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-label {
            color: #666;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 10px;
        }
        
        .stat-value {
            font-size: 36px;
            font-weight: bold;
            color: #333;
        }
        
        .stat-card.pending { border-left: 5px solid #f59e0b; }
        .stat-card.processing { border-left: 5px solid #3b82f6; }
        .stat-card.completed { border-left: 5px solid #10b981; }
        .stat-card.failed { border-left: 5px solid #ef4444; }
        
        .queue-table {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th {
            text-align: left;
            padding: 15px;
            background: #f8f9fa;
            color: #666;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 1px;
        }
        
        td {
            padding: 15px;
            border-top: 1px solid #e5e7eb;
        }
        
        .status-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-pending { background: #fef3c7; color: #92400e; }
        .status-processing { background: #dbeafe; color: #1e40af; }
        .status-completed { background: #d1fae5; color: #065f46; }
        .status-failed { background: #fee2e2; color: #991b1b; }
        
        .action-buttons {
            position: fixed;
            bottom: 30px;
            right: 30px;
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
            display: inline-block;
        }
        
        .btn-primary {
            background: #667eea;
            color: white;
        }
        
        .btn-primary:hover {
            background: #5a67d8;
            transform: translateY(-2px);
        }
        
        .btn-success {
            background: #10b981;
            color: white;
        }
        
        .btn-success:hover {
            background: #059669;
            transform: translateY(-2px);
        }
        
        .empty-state {
            text-align: center;
            padding: 60px;
            color: #999;
        }
        
        .empty-state h3 {
            font-size: 24px;
            margin-bottom: 10px;
        }
        
        .auto-refresh {
            display: inline-block;
            margin-left: 20px;
            padding: 5px 10px;
            background: #e5e7eb;
            border-radius: 5px;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🛡️ Lead Queue Monitor 
                <span class="auto-refresh">Auto-refresh: 30s</span>
            </h1>
            <p class="subtitle">Never lose a lead - All incoming leads are queued and processed automatically</p>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card pending">
                <div class="stat-label">Pending</div>
                <div class="stat-value">{{ $stats['pending'] ?? 0 }}</div>
            </div>
            
            <div class="stat-card processing">
                <div class="stat-label">Processing</div>
                <div class="stat-value">{{ $stats['processing'] ?? 0 }}</div>
            </div>
            
            <div class="stat-card completed">
                <div class="stat-label">Completed (24h)</div>
                <div class="stat-value">{{ $stats['completed'] ?? 0 }}</div>
            </div>
            
            <div class="stat-card failed">
                <div class="stat-label">Failed</div>
                <div class="stat-value">{{ $stats['failed'] ?? 0 }}</div>
            </div>
        </div>
        
        <div class="queue-table">
            <h2 style="margin-bottom: 20px;">Recent Queue Activity</h2>
            
            @if($queueItems->isEmpty())
                <div class="empty-state">
                    <h3>🎉 Queue is Empty</h3>
                    <p>All leads have been processed successfully!</p>
                </div>
            @else
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Lead Name</th>
                            <th>Phone</th>
                            <th>Status</th>
                            <th>Attempts</th>
                            <th>Queued At</th>
                            <th>Processed At</th>
                            <th>Error</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($queueItems as $item)
                        <tr>
                            <td>#{{ $item->id }}</td>
                            <td>
                                {{ $item->payload['contact']['first_name'] ?? '' }}
                                {{ $item->payload['contact']['last_name'] ?? 'Unknown' }}
                            </td>
                            <td>{{ $item->payload['contact']['phone'] ?? 'N/A' }}</td>
                            <td>
                                <span class="status-badge status-{{ $item->status }}">
                                    {{ $item->status }}
                                </span>
                            </td>
                            <td>{{ $item->attempts }}</td>
                            <td>{{ $item->created_at->format('H:i:s') }}</td>
                            <td>{{ $item->processed_at ? $item->processed_at->format('H:i:s') : '-' }}</td>
                            <td>{{ $item->error_message ? substr($item->error_message, 0, 50) . '...' : '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
        
        <div class="action-buttons">
            <a href="/admin/lead-queue/process" class="btn btn-success" 
               onclick="return confirm('Process all pending leads now?')">
                ⚡ Process Queue Now
            </a>
            <a href="/leads" class="btn btn-primary">
                📊 View Leads
            </a>
        </div>
    </div>
    
    <script>
        // Auto-refresh every 30 seconds
        setTimeout(() => {
            window.location.reload();
        }, 30000);
    </script>
</body>
</html>




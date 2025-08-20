<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vici Lead Flow Monitor - QuotingFast Brain</title>
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
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .header h1 {
            color: #2d3748;
            font-size: 2rem;
            margin-bottom: 10px;
        }
        
        .header p {
            color: #718096;
        }
        
        .flow-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .list-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .list-card:hover {
            transform: translateY(-5px);
        }
        
        .list-card.list-101 { border-top: 4px solid #48bb78; }
        .list-card.list-102 { border-top: 4px solid #ed8936; }
        .list-card.list-103 { border-top: 4px solid #9f7aea; }
        .list-card.list-104 { border-top: 4px solid #4299e1; }
        .list-card.list-105 { border-top: 4px solid #f6ad55; }
        .list-card.list-106 { border-top: 4px solid #68d391; }
        .list-card.list-107 { border-top: 4px solid #63b3ed; }
        .list-card.list-108 { border-top: 4px solid #fc8181; }
        .list-card.list-110 { border-top: 4px solid #a0aec0; }
        
        .list-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .list-number {
            font-size: 2rem;
            font-weight: bold;
            color: #2d3748;
        }
        
        .list-icon {
            font-size: 1.5rem;
        }
        
        .list-name {
            color: #4a5568;
            font-size: 0.9rem;
            margin-bottom: 10px;
        }
        
        .list-stats {
            display: grid;
            gap: 8px;
        }
        
        .stat-row {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .stat-label {
            color: #718096;
            font-size: 0.85rem;
        }
        
        .stat-value {
            color: #2d3748;
            font-weight: 600;
        }
        
        .flow-arrow {
            text-align: center;
            color: #cbd5e0;
            font-size: 1.5rem;
            margin: 10px 0;
        }
        
        .movements-table {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .movements-table h2 {
            color: #2d3748;
            margin-bottom: 20px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th {
            background: #f7fafc;
            padding: 12px;
            text-align: left;
            color: #4a5568;
            font-weight: 600;
            border-bottom: 2px solid #e2e8f0;
        }
        
        td {
            padding: 12px;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .badge-success { background: #c6f6d5; color: #22543d; }
        .badge-warning { background: #fed7aa; color: #7c2d12; }
        .badge-info { background: #bee3f8; color: #2c5282; }
        
        .refresh-btn {
            background: #667eea;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
        }
        
        .refresh-btn:hover {
            background: #5a67d8;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #a0aec0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h1>📊 Vici Lead Flow Monitor</h1>
                    <p>Real-time view of lead distribution across lists 101-110</p>
                </div>
                <button class="refresh-btn" onclick="location.reload()">🔄 Refresh</button>
            </div>
        </div>
        
        <div class="flow-container">
            @php
                // Simulate data for now - replace with actual DB query
                $lists = [
                    ['id' => 101, 'name' => 'Immediate', 'icon' => '🆕', 'count' => 0, 'new' => 0, 'xfer' => 0],
                    ['id' => 102, 'name' => 'Aggressive', 'icon' => '🔥', 'count' => 0, 'new' => 0, 'xfer' => 0],
                    ['id' => 103, 'name' => 'Voicemail 1', 'icon' => '📧', 'count' => 0, 'new' => 0, 'xfer' => 0],
                    ['id' => 104, 'name' => 'Phase 1', 'icon' => '📞', 'count' => 0, 'new' => 0, 'xfer' => 0],
                    ['id' => 105, 'name' => 'Voicemail 2', 'icon' => '📧', 'count' => 0, 'new' => 0, 'xfer' => 0],
                    ['id' => 106, 'name' => 'Phase 2', 'icon' => '📞', 'count' => 0, 'new' => 0, 'xfer' => 0],
                    ['id' => 107, 'name' => 'Cool Down', 'icon' => '❄️', 'count' => 0, 'new' => 0, 'xfer' => 0],
                    ['id' => 108, 'name' => 'Phase 3', 'icon' => '📞', 'count' => 0, 'new' => 0, 'xfer' => 0],
                    ['id' => 110, 'name' => 'Archive', 'icon' => '📦', 'count' => 0, 'new' => 0, 'xfer' => 0],
                ];
            @endphp
            
            @foreach($lists as $list)
                <div class="list-card list-{{ $list['id'] }}">
                    <div class="list-header">
                        <div class="list-number">{{ $list['id'] }}</div>
                        <div class="list-icon">{{ $list['icon'] }}</div>
                    </div>
                    <div class="list-name">{{ $list['name'] }}</div>
                    <div class="list-stats">
                        <div class="stat-row">
                            <span class="stat-label">Total Leads:</span>
                            <span class="stat-value">{{ number_format($list['count']) }}</span>
                        </div>
                        <div class="stat-row">
                            <span class="stat-label">New Status:</span>
                            <span class="stat-value">{{ number_format($list['new']) }}</span>
                        </div>
                        <div class="stat-row">
                            <span class="stat-label">Transferred:</span>
                            <span class="stat-value">{{ number_format($list['xfer']) }}</span>
                        </div>
                    </div>
                </div>
                
                @if($list['id'] != 110)
                    <div class="flow-arrow">→</div>
                @endif
            @endforeach
        </div>
        
        <div class="movements-table">
            <h2>📋 Recent Lead Movements (Last 24 Hours)</h2>
            
            @php
                $movements = []; // Replace with actual query
            @endphp
            
            @if(count($movements) > 0)
                <table>
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>Lead ID</th>
                            <th>Brain ID</th>
                            <th>From List</th>
                            <th>To List</th>
                            <th>Reason</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($movements as $move)
                            <tr>
                                <td>{{ $move->move_date }}</td>
                                <td>{{ $move->lead_id }}</td>
                                <td>{{ $move->brain_lead_id }}</td>
                                <td><span class="badge badge-info">{{ $move->from_list_id }}</span></td>
                                <td><span class="badge badge-success">{{ $move->to_list_id }}</span></td>
                                <td>{{ $move->move_reason }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="empty-state">
                    <p>No lead movements in the last 24 hours</p>
                    <p style="margin-top: 10px; font-size: 0.9rem;">Lead flow will begin once leads are added to List 101</p>
                </div>
            @endif
        </div>
    </div>
    
    <script>
        // Auto-refresh every 30 seconds
        setTimeout(() => {
            location.reload();
        }, 30000);
    </script>
</body>
</html>


<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vici Lead Flow Monitor - QuotingFast Brain</title>
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
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .header h1 {
            color: #2d3748;
            font-size: 2rem;
            margin-bottom: 10px;
        }
        
        .header p {
            color: #718096;
        }
        
        .flow-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .list-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .list-card:hover {
            transform: translateY(-5px);
        }
        
        .list-card.list-101 { border-top: 4px solid #48bb78; }
        .list-card.list-102 { border-top: 4px solid #ed8936; }
        .list-card.list-103 { border-top: 4px solid #9f7aea; }
        .list-card.list-104 { border-top: 4px solid #4299e1; }
        .list-card.list-105 { border-top: 4px solid #f6ad55; }
        .list-card.list-106 { border-top: 4px solid #68d391; }
        .list-card.list-107 { border-top: 4px solid #63b3ed; }
        .list-card.list-108 { border-top: 4px solid #fc8181; }
        .list-card.list-110 { border-top: 4px solid #a0aec0; }
        
        .list-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .list-number {
            font-size: 2rem;
            font-weight: bold;
            color: #2d3748;
        }
        
        .list-icon {
            font-size: 1.5rem;
        }
        
        .list-name {
            color: #4a5568;
            font-size: 0.9rem;
            margin-bottom: 10px;
        }
        
        .list-stats {
            display: grid;
            gap: 8px;
        }
        
        .stat-row {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .stat-label {
            color: #718096;
            font-size: 0.85rem;
        }
        
        .stat-value {
            color: #2d3748;
            font-weight: 600;
        }
        
        .flow-arrow {
            text-align: center;
            color: #cbd5e0;
            font-size: 1.5rem;
            margin: 10px 0;
        }
        
        .movements-table {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .movements-table h2 {
            color: #2d3748;
            margin-bottom: 20px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th {
            background: #f7fafc;
            padding: 12px;
            text-align: left;
            color: #4a5568;
            font-weight: 600;
            border-bottom: 2px solid #e2e8f0;
        }
        
        td {
            padding: 12px;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .badge-success { background: #c6f6d5; color: #22543d; }
        .badge-warning { background: #fed7aa; color: #7c2d12; }
        .badge-info { background: #bee3f8; color: #2c5282; }
        
        .refresh-btn {
            background: #667eea;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
        }
        
        .refresh-btn:hover {
            background: #5a67d8;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #a0aec0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h1>📊 Vici Lead Flow Monitor</h1>
                    <p>Real-time view of lead distribution across lists 101-110</p>
                </div>
                <button class="refresh-btn" onclick="location.reload()">🔄 Refresh</button>
            </div>
        </div>
        
        <div class="flow-container">
            @php
                // Simulate data for now - replace with actual DB query
                $lists = [
                    ['id' => 101, 'name' => 'Immediate', 'icon' => '🆕', 'count' => 0, 'new' => 0, 'xfer' => 0],
                    ['id' => 102, 'name' => 'Aggressive', 'icon' => '🔥', 'count' => 0, 'new' => 0, 'xfer' => 0],
                    ['id' => 103, 'name' => 'Voicemail 1', 'icon' => '📧', 'count' => 0, 'new' => 0, 'xfer' => 0],
                    ['id' => 104, 'name' => 'Phase 1', 'icon' => '📞', 'count' => 0, 'new' => 0, 'xfer' => 0],
                    ['id' => 105, 'name' => 'Voicemail 2', 'icon' => '📧', 'count' => 0, 'new' => 0, 'xfer' => 0],
                    ['id' => 106, 'name' => 'Phase 2', 'icon' => '📞', 'count' => 0, 'new' => 0, 'xfer' => 0],
                    ['id' => 107, 'name' => 'Cool Down', 'icon' => '❄️', 'count' => 0, 'new' => 0, 'xfer' => 0],
                    ['id' => 108, 'name' => 'Phase 3', 'icon' => '📞', 'count' => 0, 'new' => 0, 'xfer' => 0],
                    ['id' => 110, 'name' => 'Archive', 'icon' => '📦', 'count' => 0, 'new' => 0, 'xfer' => 0],
                ];
            @endphp
            
            @foreach($lists as $list)
                <div class="list-card list-{{ $list['id'] }}">
                    <div class="list-header">
                        <div class="list-number">{{ $list['id'] }}</div>
                        <div class="list-icon">{{ $list['icon'] }}</div>
                    </div>
                    <div class="list-name">{{ $list['name'] }}</div>
                    <div class="list-stats">
                        <div class="stat-row">
                            <span class="stat-label">Total Leads:</span>
                            <span class="stat-value">{{ number_format($list['count']) }}</span>
                        </div>
                        <div class="stat-row">
                            <span class="stat-label">New Status:</span>
                            <span class="stat-value">{{ number_format($list['new']) }}</span>
                        </div>
                        <div class="stat-row">
                            <span class="stat-label">Transferred:</span>
                            <span class="stat-value">{{ number_format($list['xfer']) }}</span>
                        </div>
                    </div>
                </div>
                
                @if($list['id'] != 110)
                    <div class="flow-arrow">→</div>
                @endif
            @endforeach
        </div>
        
        <div class="movements-table">
            <h2>📋 Recent Lead Movements (Last 24 Hours)</h2>
            
            @php
                $movements = []; // Replace with actual query
            @endphp
            
            @if(count($movements) > 0)
                <table>
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>Lead ID</th>
                            <th>Brain ID</th>
                            <th>From List</th>
                            <th>To List</th>
                            <th>Reason</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($movements as $move)
                            <tr>
                                <td>{{ $move->move_date }}</td>
                                <td>{{ $move->lead_id }}</td>
                                <td>{{ $move->brain_lead_id }}</td>
                                <td><span class="badge badge-info">{{ $move->from_list_id }}</span></td>
                                <td><span class="badge badge-success">{{ $move->to_list_id }}</span></td>
                                <td>{{ $move->move_reason }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="empty-state">
                    <p>No lead movements in the last 24 hours</p>
                    <p style="margin-top: 10px; font-size: 0.9rem;">Lead flow will begin once leads are added to List 101</p>
                </div>
            @endif
        </div>
    </div>
    
    <script>
        // Auto-refresh every 30 seconds
        setTimeout(() => {
            location.reload();
        }, 30000);
    </script>
</body>
</html>









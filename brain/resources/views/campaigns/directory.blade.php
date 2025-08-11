<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Campaign Directory - The Brain</title>
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
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
            padding: 2rem 0;
            margin-bottom: 2rem;
        }
        
        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
            display: flex;
            align-items: center;
            gap: 2rem;
        }
        
        .header-logo img {
            height: 50px;
            width: auto;
            filter: brightness(1.2);
        }
        
        .header-text h1 {
            color: white;
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .header-text p {
            color: rgba(255,255,255,0.9);
            font-size: 1.1rem;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: #667eea;
            margin-bottom: 0.5rem;
        }
        
        .stat-label {
            font-size: 0.9rem;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .search-controls {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
            display: flex;
            gap: 1rem;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .search-input {
            flex: 1;
            min-width: 300px;
            padding: 0.75rem 1rem;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 1rem;
        }
        
        .search-input:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            transition: all 0.2s;
        }
        
        .btn-primary {
            background: #667eea;
            color: white;
        }
        
        .btn-primary:hover {
            background: #5a67d8;
            transform: translateY(-1px);
        }
        
        .btn-secondary {
            background: #e2e8f0;
            color: #4a5568;
        }
        
        .btn-secondary:hover {
            background: #cbd5e0;
        }
        
        .campaigns-table {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .table-header {
            background: #f7fafc;
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #e2e8f0;
            font-weight: 600;
            color: #2d3748;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th {
            background: #f7fafc;
            padding: 1rem 1.5rem;
            text-align: left;
            font-weight: 600;
            color: #4a5568;
            border-bottom: 2px solid #e2e8f0;
            cursor: pointer;
            position: relative;
        }
        
        th:hover {
            background: #edf2f7;
        }
        
        th.sortable::after {
            content: '↕️';
            position: absolute;
            right: 0.5rem;
            opacity: 0.5;
        }
        
        th.sort-asc::after {
            content: '↑';
            opacity: 1;
        }
        
        th.sort-desc::after {
            content: '↓';
            opacity: 1;
        }
        
        td {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #f1f3f4;
        }
        
        tr:hover {
            background: #f8f9fa;
        }
        
        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-active {
            background: #c6f6d5;
            color: #22543d;
        }
        
        .status-auto_detected {
            background: #fed7d7;
            color: #742a2a;
            animation: pulse 2s infinite;
        }
        
        .status-inactive {
            background: #e2e8f0;
            color: #4a5568;
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }
        
        .campaign-id {
            font-family: monospace;
            font-weight: 600;
            color: #667eea;
        }
        
        .auto-created {
            font-style: italic;
            color: #718096;
        }
        
        .needs-attention {
            background: #fff5f5;
        }
        
        .pagination {
            padding: 1.5rem;
            text-align: center;
        }
        
        .pagination a, .pagination span {
            display: inline-block;
            padding: 0.5rem 1rem;
            margin: 0 0.25rem;
            border-radius: 6px;
            text-decoration: none;
            color: #667eea;
        }
        
        .pagination .current {
            background: #667eea;
            color: white;
        }
        
        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #718096;
        }
        
        .empty-state-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
        }
        
        .actions {
            display: flex;
            gap: 0.5rem;
        }
        
        .btn-sm {
            padding: 0.5rem 1rem;
            font-size: 0.8rem;
        }
        
        .btn-edit {
            background: #ed8936;
            color: white;
        }
        
        .btn-edit:hover {
            background: #dd6b20;
        }
        
        .alert {
            padding: 1rem 1.5rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }
        
        .alert-warning {
            background: #fffbeb;
            border: 1px solid #f6e05e;
            color: #744210;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <div class="header-logo">
                <img src="https://quotingfast.com/logoqf0704.png" alt="QuotingFast" onerror="this.style.display='none';">
            </div>
            <div class="header-text">
                <h1>📊 Campaign Directory</h1>
                <p>Manage and track all marketing campaigns with automatic detection</p>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number">{{ $stats['total_campaigns'] }}</div>
                <div class="stat-label">Total Campaigns</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">{{ $stats['active_campaigns'] }}</div>
                <div class="stat-label">Active Campaigns</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">{{ $stats['auto_detected'] }}</div>
                <div class="stat-label">Need Attention</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">{{ $stats['recent_activity'] }}</div>
                <div class="stat-label">Recent Activity</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">{{ $stats['total_leads_from_campaigns'] }}</div>
                <div class="stat-label">Total Campaign Leads</div>
            </div>
        </div>

        <!-- Alert for auto-detected campaigns -->
        @if($stats['auto_detected'] > 0)
        <div class="alert alert-warning">
            🚨 <strong>{{ $stats['auto_detected'] }} campaign(s)</strong> were auto-detected from incoming leads and need campaign names. 
            Click "Edit" to add proper names and they'll automatically update all related leads.
        </div>
        @endif

        <!-- Search and Controls -->
        <div class="search-controls">
            <form method="GET" style="display: flex; gap: 1rem; align-items: center; flex: 1;">
                <input 
                    type="text" 
                    name="search" 
                    value="{{ $search }}" 
                    placeholder="Search by Campaign ID or Name..." 
                    class="search-input"
                >
                <button type="submit" class="btn btn-primary">🔍 Search</button>
                @if($search)
                <a href="/campaign-directory" class="btn btn-secondary">Clear</a>
                @endif
            </form>
            <a href="#" class="btn btn-primary" onclick="alert('Add Campaign feature coming soon!')">➕ Add Campaign</a>
        </div>

        <!-- Campaigns Table -->
        <div class="campaigns-table">
            <div class="table-header">
                Campaign Management Directory
            </div>
            
            @if($campaigns->count() > 0)
            <table>
                <thead>
                    <tr>
                        <th class="sortable {{ $sortBy === 'campaign_id' ? 'sort-' . $sortDir : '' }}" 
                            onclick="sortTable('campaign_id')">
                            Campaign ID
                        </th>
                        <th class="sortable {{ $sortBy === 'name' ? 'sort-' . $sortDir : '' }}" 
                            onclick="sortTable('name')">
                            Campaign Name
                        </th>
                        <th class="sortable {{ $sortBy === 'total_leads' ? 'sort-' . $sortDir : '' }}" 
                            onclick="sortTable('total_leads')">
                            Total Leads
                        </th>
                        <th class="sortable {{ $sortBy === 'last_lead_received_at' ? 'sort-' . $sortDir : '' }}" 
                            onclick="sortTable('last_lead_received_at')">
                            Last Lead Received
                        </th>
                        <th class="sortable {{ $sortBy === 'status' ? 'sort-' . $sortDir : '' }}" 
                            onclick="sortTable('status')">
                            Status
                        </th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($campaigns as $campaign)
                    <tr class="{{ $campaign->is_auto_created ? 'needs-attention' : '' }}">
                        <td>
                            <span class="campaign-id">{{ $campaign->campaign_id }}</span>
                        </td>
                        <td>
                            {{ $campaign->display_name }}
                            @if($campaign->is_auto_created)
                            <br><small class="auto-created">Auto-detected - needs name</small>
                            @endif
                        </td>
                        <td>
                            <strong>{{ number_format($campaign->total_leads) }}</strong>
                        </td>
                        <td>
                            @if($campaign->last_lead_received_at)
                            {{ $campaign->last_lead_received_at->setTimezone('America/New_York')->format('M j, Y g:i A T') }}
                            @else
                            <span style="color: #a0aec0;">Never</span>
                            @endif
                        </td>
                        <td>
                            <span class="status-badge status-{{ $campaign->status }}">
                                {{ ucfirst(str_replace('_', ' ', $campaign->status)) }}
                            </span>
                        </td>
                        <td>
                            <div class="actions">
                                <a href="#" class="btn btn-sm btn-edit" 
                                   onclick="editCampaign({{ $campaign->id }}, '{{ $campaign->campaign_id }}', '{{ addslashes($campaign->name) }}', '{{ addslashes($campaign->description ?? '') }}')">
                                    ✏️ Edit
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            
            <!-- Pagination -->
            <div class="pagination">
                {{ $campaigns->links('pagination::simple-bootstrap-4') }}
            </div>
            @else
            <div class="empty-state">
                <div class="empty-state-icon">📊</div>
                <h3>No campaigns found</h3>
                <p>Campaigns will automatically appear here when leads with campaign IDs are received.</p>
            </div>
            @endif
        </div>
    </div>

    <script>
        function sortTable(column) {
            const currentSort = '{{ $sortBy }}';
            const currentDir = '{{ $sortDir }}';
            const newDir = (currentSort === column && currentDir === 'asc') ? 'desc' : 'asc';
            
            const url = new URL(window.location);
            url.searchParams.set('sort', column);
            url.searchParams.set('dir', newDir);
            
            window.location.href = url.toString();
        }
        
        function editCampaign(id, campaignId, name, description) {
            const newName = prompt(`Edit Campaign Name for ID: ${campaignId}`, name);
            if (newName && newName !== name) {
                const newDescription = prompt('Campaign Description (optional):', description);
                
                // Send update request
                fetch(`/campaign-directory/${id}/update`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    },
                    body: JSON.stringify({
                        name: newName,
                        description: newDescription
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(`✅ Campaign updated! ${data.leads_updated} leads now show "${newName}" instead of "Campaign #${campaignId}"`);
                        window.location.reload();
                    } else {
                        alert('❌ Error updating campaign: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('❌ Error updating campaign: ' + error.message);
                });
            }
        }
    </script>
</body>
</html>
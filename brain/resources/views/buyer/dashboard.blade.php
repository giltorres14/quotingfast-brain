<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dashboard - {{ $buyer->full_name }} | The Brain</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: #f8fafc;
            color: #1a202c;
            line-height: 1.6;
        }
        
        /* Header */
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1.5rem 0;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .header-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo-section {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .logo {
            height: 40px;
            filter: brightness(1.2);
        }
        
        .brand-text {
            font-size: 1.5rem;
            font-weight: 700;
        }
        
        .user-section {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }
        
        .balance-display {
            background: rgba(255, 255, 255, 0.2);
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.1rem;
        }
        
        .logout-btn {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            transition: background 0.2s;
        }
        
        .logout-btn:hover {
            background: rgba(255, 255, 255, 0.3);
        }
        
        /* Navigation */
        .nav-tabs {
            background: white;
            border-bottom: 1px solid #e2e8f0;
            padding: 0 2rem;
        }
        
        .nav-container {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            gap: 2rem;
        }
        
        .nav-tab {
            padding: 1rem 0;
            color: #6b7280;
            text-decoration: none;
            font-weight: 500;
            border-bottom: 2px solid transparent;
            transition: all 0.2s;
        }
        
        .nav-tab:hover {
            color: #667eea;
            text-decoration: none;
        }
        
        .nav-tab.active {
            color: #667eea;
            border-bottom-color: #667eea;
        }
        
        /* Main Content */
        .main-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        .welcome-section {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        
        .welcome-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: #1a202c;
            margin-bottom: 0.5rem;
        }
        
        .welcome-subtitle {
            color: #6b7280;
            font-size: 1.1rem;
        }
        
        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            border-left: 4px solid #667eea;
            transition: transform 0.2s;
        }
        
        .stat-card:hover {
            transform: translateY(-2px);
        }
        
        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .stat-title {
            color: #6b7280;
            font-size: 0.9rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .stat-icon {
            font-size: 1.5rem;
            opacity: 0.7;
        }
        
        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: #1a202c;
            margin-bottom: 0.5rem;
        }
        
        .stat-change {
            font-size: 0.85rem;
            font-weight: 500;
        }
        
        .stat-change.positive {
            color: #10b981;
        }
        
        .stat-change.negative {
            color: #ef4444;
        }
        
        .stat-change.neutral {
            color: #6b7280;
        }
        
        /* Recent Activity */
        .activity-section {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
            margin-bottom: 2rem;
        }
        
        .card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .card-title {
            font-size: 1.2rem;
            font-weight: 700;
            color: #1a202c;
        }
        
        .view-all-link {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.9rem;
        }
        
        .view-all-link:hover {
            text-decoration: underline;
        }
        
        /* Lead List */
        .lead-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
            border-bottom: 1px solid #f1f5f9;
        }
        
        .lead-item:last-child {
            border-bottom: none;
        }
        
        .lead-info {
            flex: 1;
        }
        
        .lead-id {
            font-weight: 600;
            color: #1a202c;
            margin-bottom: 0.25rem;
        }
        
        .lead-details {
            font-size: 0.85rem;
            color: #6b7280;
        }
        
        .lead-meta {
            display: flex;
            align-items: center;
            gap: 1rem;
            text-align: right;
        }
        
        .lead-price {
            font-weight: 600;
            color: #10b981;
        }
        
        .lead-status {
            padding: 0.25rem 0.75rem;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-delivered {
            background: #dcfce7;
            color: #166534;
        }
        
        .status-returned {
            background: #fef3c7;
            color: #92400e;
        }
        
        .status-disputed {
            background: #fee2e2;
            color: #dc2626;
        }
        
        /* Quick Actions */
        .quick-actions {
            display: grid;
            gap: 1rem;
        }
        
        .action-btn {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 1rem;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            text-decoration: none;
            color: #374151;
            font-weight: 500;
            transition: all 0.2s;
        }
        
        .action-btn:hover {
            background: #667eea;
            color: white;
            text-decoration: none;
            transform: translateY(-1px);
        }
        
        .action-icon {
            font-size: 1.2rem;
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: #6b7280;
        }
        
        .empty-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }
        
        .empty-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #374151;
        }
        
        .empty-description {
            margin-bottom: 2rem;
        }
        
        .cta-btn {
            background: #667eea;
            color: white;
            padding: 0.75rem 2rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: background 0.2s;
        }
        
        .cta-btn:hover {
            background: #5a67d8;
            text-decoration: none;
            color: white;
        }
        
        /* Auto-reload Alert */
        .auto-reload-alert {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .auto-reload-alert.warning {
            background: #fef3c7;
            border-color: #fcd34d;
        }
        
        .alert-icon {
            font-size: 1.2rem;
        }
        
        .alert-content {
            flex: 1;
        }
        
        .alert-title {
            font-weight: 600;
            margin-bottom: 0.25rem;
        }
        
        .alert-description {
            font-size: 0.9rem;
            color: #6b7280;
        }
        
        /* Responsive */
        @media (max-width: 1024px) {
            .activity-section {
                grid-template-columns: 1fr;
            }
            
            .stats-grid {
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            }
        }
        
        @media (max-width: 768px) {
            .header-container {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }
            
            .user-section {
                flex-direction: column;
                gap: 1rem;
            }
            
            .nav-container {
                flex-wrap: wrap;
                gap: 1rem;
            }
            
            .main-container {
                padding: 1rem;
            }
            
            .lead-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }
            
            .lead-meta {
                align-self: flex-end;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="header-container">
            <div class="logo-section">
                <img src="https://quotingfast.com/whitelogo" alt="QuotingFast" class="logo" onerror="this.style.display='none';">
                <div class="brand-text">The Brain</div>
            </div>
            
            <div class="user-section">
                <div class="balance-display">
                    💰 Balance: {{ $buyer->formatted_balance }}
                </div>
                
                <div class="user-info">
                    <div class="user-avatar">
                        {{ strtoupper(substr($buyer->first_name, 0, 1)) }}
                    </div>
                    <div>
                        <div style="font-weight: 600;">{{ $buyer->full_name }}</div>
                        <div style="font-size: 0.85rem; opacity: 0.8;">{{ $buyer->company ?? 'Buyer Account' }}</div>
                    </div>
                </div>
                
                <form method="POST" action="/buyer/logout" style="display: inline;">
                    @csrf
                    <button type="submit" class="logout-btn">Sign Out</button>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Navigation -->
    <div class="nav-tabs">
        <div class="nav-container">
            <a href="/buyer/dashboard" class="nav-tab active">📊 Dashboard</a>
            <a href="/buyer/leads" class="nav-tab">👥 My Leads</a>
            <a href="/buyer/billing" class="nav-tab">💳 Billing</a>
            <a href="/buyer/reports" class="nav-tab">📈 Reports</a>
            <a href="/buyer/settings" class="nav-tab">⚙️ Settings</a>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="main-container">
        <!-- Welcome Section -->
        <div class="welcome-section">
            <h1 class="welcome-title">Welcome back, {{ $buyer->first_name }}! 👋</h1>
            <p class="welcome-subtitle">
                Here's what's happening with your leads today. 
                @if($buyer->needsAutoReload())
                    Your balance is running low - consider adding funds.
                @endif
            </p>
        </div>
        
        <!-- Auto-reload Alert -->
        @if($buyer->needsAutoReload())
        <div class="auto-reload-alert warning">
            <div class="alert-icon">⚠️</div>
            <div class="alert-content">
                <div class="alert-title">Low Balance Alert</div>
                <div class="alert-description">
                    Your balance ({{ $buyer->formatted_balance }}) is below your auto-reload threshold 
                    (${{ number_format($buyer->auto_reload_threshold, 2) }}). 
                    Consider adding funds to continue receiving leads.
                </div>
            </div>
        </div>
        @elseif($buyer->auto_reload_enabled)
        <div class="auto-reload-alert">
            <div class="alert-icon">✅</div>
            <div class="alert-content">
                <div class="alert-title">Auto-reload Active</div>
                <div class="alert-description">
                    Your account will automatically reload ${{ number_format($buyer->auto_reload_amount, 2) }} 
                    when your balance drops below ${{ number_format($buyer->auto_reload_threshold, 2) }}.
                </div>
            </div>
        </div>
        @endif
        
        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-title">Total Leads</div>
                    <div class="stat-icon">👥</div>
                </div>
                <div class="stat-value">{{ number_format($stats['total_leads']) }}</div>
                <div class="stat-change neutral">All time</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-title">Delivered</div>
                    <div class="stat-icon">✅</div>
                </div>
                <div class="stat-value">{{ number_format($stats['delivered_leads']) }}</div>
                <div class="stat-change positive">
                    {{ $stats['total_leads'] > 0 ? round(($stats['delivered_leads'] / $stats['total_leads']) * 100, 1) : 0 }}% success rate
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-title">Returned</div>
                    <div class="stat-icon">↩️</div>
                </div>
                <div class="stat-value">{{ number_format($stats['returned_leads']) }}</div>
                <div class="stat-change {{ $stats['returned_leads'] > 0 ? 'negative' : 'positive' }}">
                    {{ $stats['total_leads'] > 0 ? round(($stats['returned_leads'] / $stats['total_leads']) * 100, 1) : 0 }}% return rate
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-title">Total Spent</div>
                    <div class="stat-icon">💰</div>
                </div>
                <div class="stat-value">${{ number_format($stats['total_spent'], 2) }}</div>
                <div class="stat-change neutral">All time investment</div>
            </div>
        </div>
        
        <!-- Activity Section -->
        <div class="activity-section">
            <!-- Recent Leads -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Recent Leads</h2>
                    <a href="/buyer/leads" class="view-all-link">View All →</a>
                </div>
                
                @if($buyer->leads->count() > 0)
                    @foreach($buyer->leads->take(5) as $buyerLead)
                    <div class="lead-item">
                        <div class="lead-info">
                            <div class="lead-id">Lead #{{ $buyerLead->external_lead_id }}</div>
                            <div class="lead-details">
                                {{ ucfirst($buyerLead->vertical) }} Insurance • 
                                {{ $buyerLead->delivered_at->format('M j, Y g:i A') }}
                                @if($buyerLead->lead_data && isset($buyerLead->lead_data['name']))
                                    • {{ $buyerLead->lead_data['name'] }}
                                @endif
                            </div>
                        </div>
                        <div class="lead-meta">
                            <div class="lead-price">{{ $buyerLead->formatted_price }}</div>
                            <span class="lead-status status-{{ $buyerLead->status }}">
                                {{ ucfirst($buyerLead->status) }}
                            </span>
                        </div>
                    </div>
                    @endforeach
                @else
                    <div class="empty-state">
                        <div class="empty-icon">📭</div>
                        <div class="empty-title">No leads yet</div>
                        <div class="empty-description">
                            Your purchased leads will appear here once they're delivered.
                        </div>
                        <a href="/buyer/marketplace" class="cta-btn">
                            🛒 Browse Available Leads
                        </a>
                    </div>
                @endif
            </div>
            
            <!-- Quick Actions -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Quick Actions</h2>
                </div>
                
                <div class="quick-actions">
                    <a href="/buyer/leads" class="action-btn">
                        <span class="action-icon">👥</span>
                        <div>
                            <div>View All Leads</div>
                            <div style="font-size: 0.8rem; color: #6b7280;">Manage your lead portfolio</div>
                        </div>
                    </a>
                    
                    <a href="/buyer/billing" class="action-btn">
                        <span class="action-icon">💳</span>
                        <div>
                            <div>Add Funds</div>
                            <div style="font-size: 0.8rem; color: #6b7280;">Top up your balance</div>
                        </div>
                    </a>
                    
                    <a href="/buyer/reports" class="action-btn">
                        <span class="action-icon">📊</span>
                        <div>
                            <div>View Reports</div>
                            <div style="font-size: 0.8rem; color: #6b7280;">Analyze your performance</div>
                        </div>
                    </a>
                    
                    <a href="/buyer/settings" class="action-btn">
                        <span class="action-icon">⚙️</span>
                        <div>
                            <div>Account Settings</div>
                            <div style="font-size: 0.8rem; color: #6b7280;">Update preferences</div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Auto-refresh dashboard every 30 seconds
        setInterval(() => {
            if (document.visibilityState === 'visible') {
                // Only refresh if user is actively viewing the page
                window.location.reload();
            }
        }, 30000);
        
        // Update last activity timestamp
        fetch('/buyer/activity/ping', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        }).catch(() => {}); // Silent fail
    </script>
</body>
</html>
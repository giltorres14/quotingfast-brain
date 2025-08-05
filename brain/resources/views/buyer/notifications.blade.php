<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Notifications & Alerts - {{ $buyer->full_name }} | The Brain</title>
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
        
        .notification-bell {
            position: relative;
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            padding: 0.75rem;
            border-radius: 50%;
            cursor: pointer;
            font-size: 1.2rem;
            transition: all 0.2s;
        }
        
        .notification-bell:hover {
            background: rgba(255, 255, 255, 0.3);
        }
        
        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #ef4444;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 0.7rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
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
        
        .page-header {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        
        .page-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: #1a202c;
            margin-bottom: 0.5rem;
        }
        
        .page-subtitle {
            color: #6b7280;
            font-size: 1.1rem;
        }
        
        /* Notification Grid */
        .notifications-grid {
            display: grid;
            grid-template-columns: 1fr 350px;
            gap: 2rem;
            margin-bottom: 2rem;
        }
        
        .card {
            background: white;
            border-radius: 12px;
            padding: 2rem;
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
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .card-icon {
            font-size: 1.5rem;
        }
        
        /* Live Status */
        .live-status {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .live-indicator {
            width: 12px;
            height: 12px;
            background: #fbbf24;
            border-radius: 50%;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        
        .live-text {
            flex: 1;
        }
        
        .live-title {
            font-weight: 600;
            margin-bottom: 0.25rem;
        }
        
        .live-subtitle {
            opacity: 0.9;
            font-size: 0.9rem;
        }
        
        .connection-status {
            font-size: 0.8rem;
            opacity: 0.8;
        }
        
        /* Notification Filters */
        .notification-filters {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
        }
        
        .filter-btn {
            background: #f3f4f6;
            border: 1px solid #d1d5db;
            color: #374151;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            cursor: pointer;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.2s;
            text-decoration: none;
        }
        
        .filter-btn:hover {
            background: #e5e7eb;
            text-decoration: none;
        }
        
        .filter-btn.active {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }
        
        /* Notification Items */
        .notification-item {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            transition: all 0.2s;
            cursor: pointer;
            border-left: 4px solid transparent;
        }
        
        .notification-item:hover {
            background: #f8fafc;
            border-left-color: #667eea;
        }
        
        .notification-item.unread {
            background: #f0f4ff;
            border-left-color: #667eea;
        }
        
        .notification-item.read {
            opacity: 0.8;
        }
        
        .notification-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            flex-shrink: 0;
        }
        
        .notification-icon.lead {
            background: #dbeafe;
            color: #2563eb;
        }
        
        .notification-icon.payment {
            background: #dcfce7;
            color: #16a34a;
        }
        
        .notification-icon.system {
            background: #fef3c7;
            color: #d97706;
        }
        
        .notification-icon.alert {
            background: #fee2e2;
            color: #dc2626;
        }
        
        .notification-content {
            flex: 1;
        }
        
        .notification-title {
            font-weight: 600;
            color: #1a202c;
            margin-bottom: 0.25rem;
        }
        
        .notification-message {
            color: #6b7280;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
            line-height: 1.5;
        }
        
        .notification-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.8rem;
            color: #9ca3af;
        }
        
        .notification-time {
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }
        
        .notification-actions {
            display: flex;
            gap: 0.5rem;
        }
        
        .action-btn {
            background: #f3f4f6;
            border: 1px solid #d1d5db;
            color: #374151;
            padding: 0.25rem 0.75rem;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.8rem;
            transition: all 0.2s;
            text-decoration: none;
        }
        
        .action-btn:hover {
            background: #e5e7eb;
            text-decoration: none;
        }
        
        .action-btn.primary {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }
        
        .action-btn.primary:hover {
            background: #5a67d8;
        }
        
        /* Settings Panel */
        .settings-section {
            margin-bottom: 2rem;
        }
        
        .settings-title {
            font-weight: 600;
            color: #1a202c;
            margin-bottom: 1rem;
        }
        
        .setting-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            margin-bottom: 1rem;
        }
        
        .setting-info {
            flex: 1;
        }
        
        .setting-name {
            font-weight: 500;
            color: #1a202c;
            margin-bottom: 0.25rem;
        }
        
        .setting-description {
            font-size: 0.85rem;
            color: #6b7280;
        }
        
        .setting-toggle {
            position: relative;
            width: 48px;
            height: 24px;
            background: #d1d5db;
            border-radius: 12px;
            cursor: pointer;
            transition: background 0.2s;
        }
        
        .setting-toggle.active {
            background: #10b981;
        }
        
        .setting-toggle::after {
            content: '';
            position: absolute;
            top: 2px;
            left: 2px;
            width: 20px;
            height: 20px;
            background: white;
            border-radius: 50%;
            transition: transform 0.2s;
        }
        
        .setting-toggle.active::after {
            transform: translateX(24px);
        }
        
        /* Real-time Updates */
        .realtime-section {
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
            color: white;
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 2rem;
        }
        
        .realtime-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .realtime-icon {
            width: 50px;
            height: 50px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }
        
        .realtime-title {
            font-size: 1.5rem;
            font-weight: 700;
        }
        
        .realtime-features {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .feature-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .feature-icon {
            width: 30px;
            height: 30px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
        }
        
        .feature-text {
            font-size: 0.9rem;
            opacity: 0.95;
        }
        
        .realtime-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            text-align: center;
        }
        
        .stat-item {
            background: rgba(255, 255, 255, 0.1);
            padding: 1rem;
            border-radius: 8px;
        }
        
        .stat-number {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }
        
        .stat-label {
            font-size: 0.8rem;
            opacity: 0.8;
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 3rem 2rem;
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
        
        /* Toast Notifications */
        .toast-container {
            position: fixed;
            top: 2rem;
            right: 2rem;
            z-index: 1000;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        
        .toast {
            background: white;
            border-radius: 8px;
            padding: 1rem 1.5rem;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
            border-left: 4px solid #10b981;
            display: flex;
            align-items: center;
            gap: 1rem;
            min-width: 300px;
            transform: translateX(400px);
            opacity: 0;
            transition: all 0.3s ease;
        }
        
        .toast.show {
            transform: translateX(0);
            opacity: 1;
        }
        
        .toast.success {
            border-left-color: #10b981;
        }
        
        .toast.error {
            border-left-color: #ef4444;
        }
        
        .toast.warning {
            border-left-color: #f59e0b;
        }
        
        .toast.info {
            border-left-color: #3b82f6;
        }
        
        .toast-icon {
            font-size: 1.2rem;
        }
        
        .toast-content {
            flex: 1;
        }
        
        .toast-title {
            font-weight: 600;
            margin-bottom: 0.25rem;
        }
        
        .toast-message {
            font-size: 0.9rem;
            color: #6b7280;
        }
        
        .toast-close {
            background: none;
            border: none;
            color: #9ca3af;
            cursor: pointer;
            font-size: 1.2rem;
            padding: 0.25rem;
        }
        
        .toast-close:hover {
            color: #6b7280;
        }
        
        /* Responsive */
        @media (max-width: 1024px) {
            .notifications-grid {
                grid-template-columns: 1fr;
            }
            
            .realtime-features {
                grid-template-columns: 1fr;
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
            
            .notification-filters {
                justify-content: center;
            }
            
            .realtime-stats {
                grid-template-columns: 1fr;
            }
            
            .toast {
                min-width: 280px;
                margin: 0 1rem;
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
                
                <button class="notification-bell" onclick="toggleNotifications()">
                    🔔
                    <span class="notification-badge" id="notificationBadge">3</span>
                </button>
                
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
                    <button type="submit" class="logout-btn" style="background: rgba(255, 255, 255, 0.2); border: none; color: white; padding: 0.5rem 1rem; border-radius: 8px; cursor: pointer; font-weight: 500;">Sign Out</button>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Navigation -->
    <div class="nav-tabs">
        <div class="nav-container">
            <a href="/buyer/dashboard" class="nav-tab">📊 Dashboard</a>
            <a href="/buyer/leads" class="nav-tab">👥 My Leads</a>
            <a href="/buyer/billing" class="nav-tab">💳 Billing</a>
            <a href="/buyer/documents" class="nav-tab">📄 Documents</a>
            <a href="/buyer/notifications" class="nav-tab active">🔔 Notifications</a>
            <a href="/buyer/reports" class="nav-tab">📈 Reports</a>
            <a href="/buyer/settings" class="nav-tab">⚙️ Settings</a>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="main-container">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-title">Notifications & Alerts 🔔</h1>
            <p class="page-subtitle">
                Stay updated with real-time notifications about leads, payments, and system updates. Customize your alert preferences below.
            </p>
        </div>
        
        <!-- Live Status -->
        <div class="live-status">
            <div class="live-indicator"></div>
            <div class="live-text">
                <div class="live-title">🟢 Real-time Connection Active</div>
                <div class="live-subtitle">Connected to notification server • Last update: {{ now()->format('g:i A') }}</div>
            </div>
            <div class="connection-status" id="connectionStatus">
                WebSocket: Connected
            </div>
        </div>
        
        <!-- Notifications Grid -->
        <div class="notifications-grid">
            <!-- Notifications List -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">
                        <span class="card-icon">📬</span>
                        Recent Notifications
                    </h2>
                    <button class="action-btn" onclick="markAllAsRead()">
                        ✓ Mark All Read
                    </button>
                </div>
                
                <!-- Filters -->
                <div class="notification-filters">
                    <button class="filter-btn active" onclick="filterNotifications('all')">All</button>
                    <button class="filter-btn" onclick="filterNotifications('lead')">👥 Leads</button>
                    <button class="filter-btn" onclick="filterNotifications('payment')">💳 Payments</button>
                    <button class="filter-btn" onclick="filterNotifications('system')">⚙️ System</button>
                    <button class="filter-btn" onclick="filterNotifications('unread')">🔴 Unread</button>
                </div>
                
                <!-- Notification Items -->
                <div id="notificationsList">
                    <div class="notification-item unread" data-type="lead" onclick="markAsRead(this)">
                        <div class="notification-icon lead">👥</div>
                        <div class="notification-content">
                            <div class="notification-title">New Lead Available</div>
                            <div class="notification-message">
                                Auto Insurance lead from Miami, FL - Sarah Johnson, age 28, clean driving record. Price: $45.00
                            </div>
                            <div class="notification-meta">
                                <div class="notification-time">
                                    🕐 2 minutes ago
                                </div>
                                <div class="notification-actions">
                                    <a href="/buyer/leads" class="action-btn primary">View Lead</a>
                                    <button class="action-btn" onclick="dismissNotification(this)">Dismiss</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="notification-item unread" data-type="payment" onclick="markAsRead(this)">
                        <div class="notification-icon payment">💳</div>
                        <div class="notification-content">
                            <div class="notification-title">Payment Processed Successfully</div>
                            <div class="notification-message">
                                Your account has been credited $250.00 via QuickBooks payment. New balance: {{ $buyer->formatted_balance }}
                            </div>
                            <div class="notification-meta">
                                <div class="notification-time">
                                    🕐 15 minutes ago
                                </div>
                                <div class="notification-actions">
                                    <a href="/buyer/billing" class="action-btn primary">View Billing</a>
                                    <button class="action-btn" onclick="dismissNotification(this)">Dismiss</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="notification-item unread" data-type="system" onclick="markAsRead(this)">
                        <div class="notification-icon system">⚙️</div>
                        <div class="notification-content">
                            <div class="notification-title">Low Balance Alert</div>
                            <div class="notification-message">
                                Your account balance is below $50.00. Consider adding funds to continue receiving leads without interruption.
                            </div>
                            <div class="notification-meta">
                                <div class="notification-time">
                                    🕐 1 hour ago
                                </div>
                                <div class="notification-actions">
                                    <a href="/buyer/billing" class="action-btn primary">Add Funds</a>
                                    <button class="action-btn" onclick="dismissNotification(this)">Dismiss</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="notification-item read" data-type="lead" onclick="markAsRead(this)">
                        <div class="notification-icon lead">👥</div>
                        <div class="notification-content">
                            <div class="notification-title">Lead Returned Successfully</div>
                            <div class="notification-message">
                                Lead #LQF789012 has been returned and $35.00 has been credited to your account.
                            </div>
                            <div class="notification-meta">
                                <div class="notification-time">
                                    🕐 3 hours ago
                                </div>
                                <div class="notification-actions">
                                    <a href="/buyer/leads" class="action-btn">View Leads</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="notification-item read" data-type="system" onclick="markAsRead(this)">
                        <div class="notification-icon system">⚙️</div>
                        <div class="notification-content">
                            <div class="notification-title">Contract Signed Successfully</div>
                            <div class="notification-message">
                                Your buyer agreement has been signed and your account is now active. Welcome to QuotingFast!
                            </div>
                            <div class="notification-meta">
                                <div class="notification-time">
                                    🕐 Yesterday
                                </div>
                                <div class="notification-actions">
                                    <a href="/buyer/documents" class="action-btn">View Contract</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Settings Panel -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">
                        <span class="card-icon">⚙️</span>
                        Notification Settings
                    </h2>
                </div>
                
                <!-- Real-time Section -->
                <div class="realtime-section">
                    <div class="realtime-header">
                        <div class="realtime-icon">⚡</div>
                        <div>
                            <div class="realtime-title">Real-time Updates</div>
                            <div style="opacity: 0.9;">Instant notifications via WebSocket</div>
                        </div>
                    </div>
                    
                    <div class="realtime-features">
                        <div class="feature-item">
                            <div class="feature-icon">⚡</div>
                            <div class="feature-text">Instant lead alerts</div>
                        </div>
                        <div class="feature-item">
                            <div class="feature-icon">💳</div>
                            <div class="feature-text">Payment confirmations</div>
                        </div>
                        <div class="feature-item">
                            <div class="feature-icon">📊</div>
                            <div class="feature-text">Balance updates</div>
                        </div>
                        <div class="feature-item">
                            <div class="feature-icon">🔔</div>
                            <div class="feature-text">System alerts</div>
                        </div>
                    </div>
                    
                    <div class="realtime-stats">
                        <div class="stat-item">
                            <div class="stat-number" id="liveLeads">0</div>
                            <div class="stat-label">Live Leads Today</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number" id="liveNotifications">5</div>
                            <div class="stat-label">Total Notifications</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number" id="responseTime">0.2s</div>
                            <div class="stat-label">Response Time</div>
                        </div>
                    </div>
                </div>
                
                <!-- Settings -->
                <div class="settings-section">
                    <div class="settings-title">Email Notifications</div>
                    
                    <div class="setting-item">
                        <div class="setting-info">
                            <div class="setting-name">New Lead Alerts</div>
                            <div class="setting-description">Receive email when new leads are available</div>
                        </div>
                        <div class="setting-toggle active" onclick="toggleSetting(this)"></div>
                    </div>
                    
                    <div class="setting-item">
                        <div class="setting-info">
                            <div class="setting-name">Payment Confirmations</div>
                            <div class="setting-description">Email receipts for payments and refunds</div>
                        </div>
                        <div class="setting-toggle active" onclick="toggleSetting(this)"></div>
                    </div>
                    
                    <div class="setting-item">
                        <div class="setting-info">
                            <div class="setting-name">Low Balance Warnings</div>
                            <div class="setting-description">Alert when account balance is low</div>
                        </div>
                        <div class="setting-toggle active" onclick="toggleSetting(this)"></div>
                    </div>
                    
                    <div class="setting-item">
                        <div class="setting-info">
                            <div class="setting-name">Weekly Summary</div>
                            <div class="setting-description">Weekly report of activity and performance</div>
                        </div>
                        <div class="setting-toggle" onclick="toggleSetting(this)"></div>
                    </div>
                </div>
                
                <div class="settings-section">
                    <div class="settings-title">Push Notifications</div>
                    
                    <div class="setting-item">
                        <div class="setting-info">
                            <div class="setting-name">Browser Notifications</div>
                            <div class="setting-description">Show desktop notifications in browser</div>
                        </div>
                        <div class="setting-toggle active" onclick="toggleSetting(this)"></div>
                    </div>
                    
                    <div class="setting-item">
                        <div class="setting-info">
                            <div class="setting-name">Sound Alerts</div>
                            <div class="setting-description">Play sound for important notifications</div>
                        </div>
                        <div class="setting-toggle" onclick="toggleSetting(this)"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Toast Container -->
    <div class="toast-container" id="toastContainer"></div>

    <script>
        // WebSocket Connection
        let socket = null;
        let reconnectAttempts = 0;
        const maxReconnectAttempts = 5;
        
        function initializeWebSocket() {
            // In a real implementation, you'd connect to your WebSocket server
            // For demo purposes, we'll simulate the connection
            console.log('Initializing WebSocket connection...');
            
            // Simulate connection success
            setTimeout(() => {
                updateConnectionStatus('Connected');
                startRealtimeSimulation();
            }, 1000);
        }
        
        function updateConnectionStatus(status) {
            const statusElement = document.getElementById('connectionStatus');
            statusElement.textContent = `WebSocket: ${status}`;
            
            if (status === 'Connected') {
                statusElement.style.color = '#10b981';
            } else if (status === 'Connecting...') {
                statusElement.style.color = '#f59e0b';
            } else {
                statusElement.style.color = '#ef4444';
            }
        }
        
        function startRealtimeSimulation() {
            // Simulate real-time updates
            setInterval(() => {
                const liveLeads = document.getElementById('liveLeads');
                const currentCount = parseInt(liveLeads.textContent);
                
                // Randomly update live leads count
                if (Math.random() > 0.7) {
                    liveLeads.textContent = currentCount + 1;
                    
                    // Show toast notification for new lead
                    showToast('success', 'New Lead Available!', 'Auto insurance lead from your target area');
                    
                    // Update notification badge
                    updateNotificationBadge();
                }
            }, 10000); // Check every 10 seconds
        }
        
        // Notification Functions
        function filterNotifications(type) {
            const notifications = document.querySelectorAll('.notification-item');
            const filterBtns = document.querySelectorAll('.filter-btn');
            
            // Update active filter button
            filterBtns.forEach(btn => btn.classList.remove('active'));
            event.target.classList.add('active');
            
            // Filter notifications
            notifications.forEach(notification => {
                const notificationType = notification.dataset.type;
                const isRead = notification.classList.contains('read');
                
                if (type === 'all') {
                    notification.style.display = 'flex';
                } else if (type === 'unread') {
                    notification.style.display = isRead ? 'none' : 'flex';
                } else {
                    notification.style.display = notificationType === type ? 'flex' : 'none';
                }
            });
        }
        
        function markAsRead(notification) {
            notification.classList.remove('unread');
            notification.classList.add('read');
            updateNotificationBadge();
        }
        
        function markAllAsRead() {
            const unreadNotifications = document.querySelectorAll('.notification-item.unread');
            unreadNotifications.forEach(notification => {
                notification.classList.remove('unread');
                notification.classList.add('read');
            });
            updateNotificationBadge();
            showToast('success', 'All notifications marked as read', '');
        }
        
        function dismissNotification(btn) {
            const notification = btn.closest('.notification-item');
            notification.style.animation = 'slideOut 0.3s ease-out';
            setTimeout(() => {
                notification.remove();
                updateNotificationBadge();
            }, 300);
        }
        
        function updateNotificationBadge() {
            const unreadCount = document.querySelectorAll('.notification-item.unread').length;
            const badge = document.getElementById('notificationBadge');
            
            if (unreadCount > 0) {
                badge.textContent = unreadCount;
                badge.style.display = 'flex';
            } else {
                badge.style.display = 'none';
            }
        }
        
        function toggleSetting(toggle) {
            toggle.classList.toggle('active');
            
            const settingName = toggle.parentElement.querySelector('.setting-name').textContent;
            const isActive = toggle.classList.contains('active');
            
            showToast('info', 'Setting Updated', `${settingName} ${isActive ? 'enabled' : 'disabled'}`);
        }
        
        function toggleNotifications() {
            // This would typically open a dropdown notification panel
            alert('🔔 Notification Center\n\nQuick access to:\n• Recent notifications\n• Mark as read/unread\n• Notification settings\n• Real-time status');
        }
        
        // Toast Notifications
        function showToast(type, title, message) {
            const toastContainer = document.getElementById('toastContainer');
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            
            const icons = {
                success: '✅',
                error: '❌', 
                warning: '⚠️',
                info: 'ℹ️'
            };
            
            toast.innerHTML = `
                <div class="toast-icon">${icons[type]}</div>
                <div class="toast-content">
                    <div class="toast-title">${title}</div>
                    ${message ? `<div class="toast-message">${message}</div>` : ''}
                </div>
                <button class="toast-close" onclick="closeToast(this)">×</button>
            `;
            
            toastContainer.appendChild(toast);
            
            // Show toast
            setTimeout(() => {
                toast.classList.add('show');
            }, 100);
            
            // Auto-hide after 5 seconds
            setTimeout(() => {
                closeToast(toast.querySelector('.toast-close'));
            }, 5000);
        }
        
        function closeToast(btn) {
            const toast = btn.closest('.toast');
            toast.classList.remove('show');
            setTimeout(() => {
                toast.remove();
            }, 300);
        }
        
        // Request notification permission
        function requestNotificationPermission() {
            if ('Notification' in window) {
                Notification.requestPermission().then(permission => {
                    if (permission === 'granted') {
                        showToast('success', 'Notifications Enabled', 'You will now receive browser notifications');
                    }
                });
            }
        }
        
        // Browser notification
        function showBrowserNotification(title, message, icon = '/favicon.ico') {
            if ('Notification' in window && Notification.permission === 'granted') {
                new Notification(title, {
                    body: message,
                    icon: icon,
                    badge: icon
                });
            }
        }
        
        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            initializeWebSocket();
            updateNotificationBadge();
            requestNotificationPermission();
            
            // Simulate some real-time notifications
            setTimeout(() => {
                showBrowserNotification('QuotingFast - The Brain', 'Welcome! Real-time notifications are now active.');
            }, 3000);
        });
        
        // CSS Animation for slide out
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideOut {
                from { transform: translateX(0); opacity: 1; }
                to { transform: translateX(100%); opacity: 0; }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>
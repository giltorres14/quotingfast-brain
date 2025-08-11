<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Lead Outcomes - {{ $buyer->full_name }} | The Brain</title>
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
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: #f8fafc;
            color: #1a202c;
            line-height: 1.6;
        }
        
        /* Header */
        .header {
            overflow-x: hidden;
            width: 100%;
            max-width: 100%;
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            color: white;
            padding: 1.5rem 0;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .header-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 120px;
            width: 100%;
            box-sizing: border-box;
            overflow-x: hidden;
        }
        
        .logo-section {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .logo {
            height: 100px;
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
        
        /* Navigation */
        .nav-tabs {
            background: white;
            border-bottom: 1px solid #e2e8f0;
            padding: 0 2rem;
        }
        
        .nav-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 120px;
            width: 100%;
            box-sizing: border-box;
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
            color: #3b82f6;
            text-decoration: none;
        }
        
        .nav-tab.active {
            color: #3b82f6;
            border-bottom-color: #3b82f6;
        }
        
        /* Main Content */
        .main-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
            width: 100%;
            box-sizing: border-box;
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
        
        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        
        .stat-icon {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: #1a202c;
            margin-bottom: 0.25rem;
        }
        
        .stat-label {
            color: #6b7280;
            font-size: 0.9rem;
            font-weight: 500;
        }
        
        /* Outcomes Grid */
        .outcomes-grid {
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
        
        /* Filters */
        .filters {
            display: flex;
            gap: 1rem;
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
        }
        
        .filter-btn:hover {
            background: #e5e7eb;
        }
        
        .filter-btn.active {
            background: #3b82f6;
            color: white;
            border-color: #3b82f6;
        }
        
        /* Outcome Items */
        .outcome-item {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            transition: all 0.2s;
            cursor: pointer;
        }
        
        .outcome-item:hover {
            border-color: #3b82f6;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .outcome-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .outcome-lead-info {
            flex: 1;
        }
        
        .outcome-lead-name {
            font-weight: 600;
            color: #1a202c;
            margin-bottom: 0.25rem;
        }
        
        .outcome-lead-details {
            font-size: 0.85rem;
            color: #6b7280;
        }
        
        .outcome-badges {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
        
        .outcome-badge {
            background: #f3f4f6;
            color: #374151;
            padding: 0.25rem 0.75rem;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .outcome-badge.status-new { background: #dbeafe; color: #1e40af; }
        .outcome-badge.status-contacted { background: #fef3c7; color: #92400e; }
        .outcome-badge.status-qualified { background: #dcfce7; color: #166534; }
        .outcome-badge.status-closed-won { background: #dcfce7; color: #166534; }
        .outcome-badge.status-closed-lost { background: #fee2e2; color: #991b1b; }
        .outcome-badge.status-bad-lead { background: #fef2f2; color: #b91c1c; }
        
        .outcome-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
        }
        
        .outcome-detail {
            text-align: center;
        }
        
        .outcome-detail-label {
            font-size: 0.75rem;
            color: #6b7280;
            text-transform: uppercase;
            font-weight: 600;
            margin-bottom: 0.25rem;
        }
        
        .outcome-detail-value {
            font-weight: 600;
            color: #1a202c;
        }
        
        .outcome-actions {
            display: flex;
            gap: 0.5rem;
            justify-content: flex-end;
        }
        
        .btn {
            padding: 0.5rem 1rem;
            border-radius: 6px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
            font-size: 0.85rem;
            text-decoration: none;
        }
        
        .btn-primary {
            background: #3b82f6;
            color: white;
        }
        
        .btn-primary:hover {
            background: #5a67d8;
        }
        
        .btn-secondary {
            background: #f3f4f6;
            color: #374151;
            border: 1px solid #d1d5db;
        }
        
        .btn-secondary:hover {
            background: #e5e7eb;
        }
        
        .btn-success {
            background: #10b981;
            color: white;
        }
        
        .btn-success:hover {
            background: #059669;
        }
        
        .btn-danger {
            background: #ef4444;
            color: white;
        }
        
        .btn-danger:hover {
            background: #dc2626;
        }
        
        /* Quick Report Panel */
        .quick-report {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 2rem;
        }
        
        .quick-report-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .quick-report-icon {
            width: 50px;
            height: 100px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }
        
        .quick-report-title {
            font-size: 1.5rem;
            font-weight: 700;
        }
        
        .quick-report-form {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
        }
        
        .form-label {
            font-weight: 600;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }
        
        .form-input {
            padding: 0.75rem;
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 6px;
            background: rgba(255, 255, 255, 0.1);
            color: white;
            font-size: 0.9rem;
        }
        
        .form-input::placeholder {
            color: rgba(255, 255, 255, 0.7);
        }
        
        .form-input:focus {
            outline: none;
            border-color: rgba(255, 255, 255, 0.5);
            background: rgba(255, 255, 255, 0.15);
        }
        
        .form-select {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: white;
        }
        
        .form-select option {
            background: #059669;
            color: white;
        }
        
        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
        }
        
        .modal.active {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .modal-content {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            max-width: 500px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .modal-title {
            font-size: 1.2rem;
            font-weight: 700;
            color: #1a202c;
        }
        
        .modal-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #6b7280;
        }
        
        .modal-close:hover {
            color: #374151;
        }
        
        /* Responsive */
        @media (max-width: 1024px) {
            .outcomes-grid {
                grid-template-columns: 1fr;
            }
            
            .quick-report-form {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 768px) {
            .header-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 120px;
            width: 100%;
            box-sizing: border-box;
            overflow-x: hidden;
        }
            
            .user-section {
                flex-direction: column;
                gap: 1rem;
            }
            
            .nav-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 120px;
            width: 100%;
            box-sizing: border-box;
        }
            
            .main-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
            width: 100%;
            box-sizing: border-box;
        }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .outcome-details {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .outcome-actions {
                flex-direction: column;
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
                <div class="logo-text" style="display: flex; flex-direction: column; align-items: center; line-height: 1;">
                    <div style="font-family: 'Orbitron', sans-serif; font-size: 0.9rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px;">The</div>
                    <div class="brand-text" style="font-family: 'Orbitron', sans-serif; font-size: 1.4rem; font-weight: 900; text-transform: uppercase; letter-spacing: 1px;">Brain</div>
                </div>
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
            <a href="/buyer/notifications" class="nav-tab">🔔 Notifications</a>
            <a href="/buyer/lead-outcomes" class="nav-tab active">📈 Lead Outcomes</a>
            <a href="/buyer/crm-settings" class="nav-tab">🔗 CRM Integration</a>
            <a href="/buyer/settings" class="nav-tab">⚙️ Settings</a>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="main-container">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-title">Lead Outcomes 📈</h1>
            <p class="page-subtitle">
                Track and report the outcomes of your leads. Help us improve lead quality by sharing what happened with each lead.
            </p>
        </div>
        
        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">🎯</div>
                <div class="stat-number" id="totalLeads">{{ $stats['total_leads'] ?? 0 }}</div>
                <div class="stat-label">Total Leads</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">💰</div>
                <div class="stat-number" id="soldLeads">{{ $stats['sold_leads'] ?? 0 }}</div>
                <div class="stat-label">Leads Sold</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">📊</div>
                <div class="stat-number" id="conversionRate">{{ $stats['conversion_rate'] ?? 0 }}%</div>
                <div class="stat-label">Conversion Rate</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">⭐</div>
                <div class="stat-number" id="avgQuality">{{ $stats['avg_quality'] ?? 0 }}/5</div>
                <div class="stat-label">Avg Quality Rating</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">💵</div>
                <div class="stat-number" id="totalRevenue">${{ number_format($stats['total_revenue'] ?? 0, 0) }}</div>
                <div class="stat-label">Total Revenue</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">⏱️</div>
                <div class="stat-number" id="avgCloseTime">{{ $stats['avg_close_time'] ?? 0 }}d</div>
                <div class="stat-label">Avg Close Time</div>
            </div>
        </div>
        
        <!-- Outcomes Grid -->
        <div class="outcomes-grid">
            <!-- Outcomes List -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">
                        <span class="card-icon">📋</span>
                        Lead Outcomes
                    </h2>
                    <button class="btn btn-primary" onclick="openQuickReportModal()">
                        📝 Quick Report
                    </button>
                </div>
                
                <!-- Filters -->
                <div class="filters">
                    <button class="filter-btn active" onclick="filterOutcomes('all')">All</button>
                    <button class="filter-btn" onclick="filterOutcomes('pending')">⏳ Pending</button>
                    <button class="filter-btn" onclick="filterOutcomes('sold')">💰 Sold</button>
                    <button class="filter-btn" onclick="filterOutcomes('not_sold')">❌ Not Sold</button>
                    <button class="filter-btn" onclick="filterOutcomes('bad_lead')">⚠️ Bad Leads</button>
                </div>
                
                <!-- Outcome Items -->
                <div id="outcomesList">
                    <!-- Sample outcome items - these would be populated from backend -->
                    <div class="outcome-item" data-outcome="pending">
                        <div class="outcome-header">
                            <div class="outcome-lead-info">
                                <div class="outcome-lead-name">Sarah Johnson</div>
                                <div class="outcome-lead-details">Auto Insurance • Miami, FL • Lead #LQF123456</div>
                            </div>
                            <div class="outcome-badges">
                                <span class="outcome-badge status-contacted">📞 Contacted</span>
                                <span class="outcome-badge">⏳ Pending</span>
                            </div>
                        </div>
                        
                        <div class="outcome-details">
                            <div class="outcome-detail">
                                <div class="outcome-detail-label">Received</div>
                                <div class="outcome-detail-value">2 days ago</div>
                            </div>
                            <div class="outcome-detail">
                                <div class="outcome-detail-label">Contact Attempts</div>
                                <div class="outcome-detail-value">3</div>
                            </div>
                            <div class="outcome-detail">
                                <div class="outcome-detail-label">Quality</div>
                                <div class="outcome-detail-value">⭐⭐⭐⭐☆</div>
                            </div>
                            <div class="outcome-detail">
                                <div class="outcome-detail-label">Status</div>
                                <div class="outcome-detail-value">Qualified</div>
                            </div>
                        </div>
                        
                        <div class="outcome-actions">
                            <button class="btn btn-success" onclick="markAsSold('LQF123456')">💰 Mark as Sold</button>
                            <button class="btn btn-danger" onclick="markAsNotSold('LQF123456')">❌ Not Sold</button>
                            <button class="btn btn-secondary" onclick="updateOutcome('LQF123456')">📝 Update</button>
                        </div>
                    </div>
                    
                    <div class="outcome-item" data-outcome="sold">
                        <div class="outcome-header">
                            <div class="outcome-lead-info">
                                <div class="outcome-lead-name">Michael Chen</div>
                                <div class="outcome-lead-details">Home Insurance • Los Angeles, CA • Lead #LQF123457</div>
                            </div>
                            <div class="outcome-badges">
                                <span class="outcome-badge status-closed-won">🎉 Closed Won</span>
                                <span class="outcome-badge">💰 Sold</span>
                            </div>
                        </div>
                        
                        <div class="outcome-details">
                            <div class="outcome-detail">
                                <div class="outcome-detail-label">Sale Amount</div>
                                <div class="outcome-detail-value">$2,400</div>
                            </div>
                            <div class="outcome-detail">
                                <div class="outcome-detail-label">Commission</div>
                                <div class="outcome-detail-value">$480</div>
                            </div>
                            <div class="outcome-detail">
                                <div class="outcome-detail-label">Close Time</div>
                                <div class="outcome-detail-value">5 days</div>
                            </div>
                            <div class="outcome-detail">
                                <div class="outcome-detail-label">Quality</div>
                                <div class="outcome-detail-value">⭐⭐⭐⭐⭐</div>
                            </div>
                        </div>
                        
                        <div class="outcome-actions">
                            <button class="btn btn-secondary" onclick="viewDetails('LQF123457')">👁️ View Details</button>
                            <button class="btn btn-secondary" onclick="updateOutcome('LQF123457')">📝 Edit</button>
                        </div>
                    </div>
                    
                    <div class="outcome-item" data-outcome="bad_lead">
                        <div class="outcome-header">
                            <div class="outcome-lead-info">
                                <div class="outcome-lead-name">Invalid Lead</div>
                                <div class="outcome-lead-details">Auto Insurance • Bad Data • Lead #LQF123458</div>
                            </div>
                            <div class="outcome-badges">
                                <span class="outcome-badge status-bad-lead">⚠️ Bad Lead</span>
                                <span class="outcome-badge">🚫 Invalid</span>
                            </div>
                        </div>
                        
                        <div class="outcome-details">
                            <div class="outcome-detail">
                                <div class="outcome-detail-label">Issue</div>
                                <div class="outcome-detail-value">Wrong Number</div>
                            </div>
                            <div class="outcome-detail">
                                <div class="outcome-detail-label">Quality</div>
                                <div class="outcome-detail-value">⭐☆☆☆☆</div>
                            </div>
                            <div class="outcome-detail">
                                <div class="outcome-detail-label">Refunded</div>
                                <div class="outcome-detail-value">Yes</div>
                            </div>
                            <div class="outcome-detail">
                                <div class="outcome-detail-label">Reported</div>
                                <div class="outcome-detail-value">1 day ago</div>
                            </div>
                        </div>
                        
                        <div class="outcome-actions">
                            <button class="btn btn-secondary" onclick="viewDetails('LQF123458')">👁️ View Details</button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Quick Report Panel -->
            <div class="card">
                <div class="quick-report">
                    <div class="quick-report-header">
                        <div class="quick-report-icon">📝</div>
                        <div>
                            <div class="quick-report-title">Quick Report</div>
                            <div style="opacity: 0.9;">Report lead outcome instantly</div>
                        </div>
                    </div>
                    
                    <form class="quick-report-form" onsubmit="submitQuickReport(event)">
                        <div class="form-group">
                            <label class="form-label">Lead ID</label>
                            <input type="text" class="form-input" id="quickLeadId" placeholder="LQF123456" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Outcome</label>
                            <select class="form-input form-select" id="quickOutcome" required>
                                <option value="">Select outcome...</option>
                                <option value="sold">💰 Sold</option>
                                <option value="not_sold">❌ Not Sold</option>
                                <option value="bad_lead">⚠️ Bad Lead</option>
                                <option value="duplicate">🔄 Duplicate</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Sale Amount (if sold)</label>
                            <input type="number" class="form-input" id="quickSaleAmount" placeholder="2400" step="0.01">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Quality Rating</label>
                            <select class="form-input form-select" id="quickQuality">
                                <option value="">Rate quality...</option>
                                <option value="5">⭐⭐⭐⭐⭐ Excellent</option>
                                <option value="4">⭐⭐⭐⭐☆ Good</option>
                                <option value="3">⭐⭐⭐☆☆ Average</option>
                                <option value="2">⭐⭐☆☆☆ Poor</option>
                                <option value="1">⭐☆☆☆☆ Terrible</option>
                            </select>
                        </div>
                        
                        <div class="form-group" style="grid-column: 1 / -1;">
                            <label class="form-label">Notes</label>
                            <textarea class="form-input" id="quickNotes" placeholder="Additional details about this lead..." rows="3"></textarea>
                        </div>
                        
                        <div style="grid-column: 1 / -1;">
                            <button type="submit" class="btn btn-success" style="width: 100%; padding: 1rem;">
                                📤 Submit Report
                            </button>
                        </div>
                    </form>
                </div>
                
                <!-- Benefits Section -->
                <div style="background: #f0f4ff; border-radius: 8px; padding: 1.5rem; margin-top: 2rem;">
                    <h3 style="color: #1a202c; margin-bottom: 1rem; font-size: 1.1rem;">🎯 Why Report Outcomes?</h3>
                    <ul style="color: #4b5563; font-size: 0.9rem; line-height: 1.6;">
                        <li style="margin-bottom: 0.5rem;">✅ <strong>Better Lead Quality</strong> - Help us improve targeting</li>
                        <li style="margin-bottom: 0.5rem;">✅ <strong>Faster Refunds</strong> - Automatic processing for bad leads</li>
                        <li style="margin-bottom: 0.5rem;">✅ <strong>Performance Insights</strong> - Track your conversion rates</li>
                        <li style="margin-bottom: 0.5rem;">✅ <strong>Priority Support</strong> - Better service for active reporters</li>
                        <li>✅ <strong>Volume Discounts</strong> - Rewards for feedback participation</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Update Outcome Modal -->
    <div class="modal" id="updateModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Update Lead Outcome</h3>
                <button class="modal-close" onclick="closeModal('updateModal')">&times;</button>
            </div>
            
            <form onsubmit="submitOutcomeUpdate(event)">
                <div class="form-group" style="margin-bottom: 1rem;">
                    <label class="form-label">Status</label>
                    <select class="form-input" id="updateStatus" style="width: 100%;">
                        <option value="new">🆕 New</option>
                        <option value="contacted">📞 Contacted</option>
                        <option value="qualified">✅ Qualified</option>
                        <option value="proposal_sent">📄 Proposal Sent</option>
                        <option value="negotiating">🤝 Negotiating</option>
                        <option value="closed_won">🎉 Closed Won</option>
                        <option value="closed_lost">❌ Closed Lost</option>
                        <option value="not_interested">🚫 Not Interested</option>
                        <option value="bad_lead">⚠️ Bad Lead</option>
                    </select>
                </div>
                
                <div class="form-group" style="margin-bottom: 1rem;">
                    <label class="form-label">Sale Amount</label>
                    <input type="number" class="form-input" id="updateSaleAmount" placeholder="0.00" step="0.01" style="width: 100%;">
                </div>
                
                <div class="form-group" style="margin-bottom: 1rem;">
                    <label class="form-label">Quality Rating</label>
                    <select class="form-input" id="updateQuality" style="width: 100%;">
                        <option value="">Select rating...</option>
                        <option value="5">⭐⭐⭐⭐⭐ Excellent</option>
                        <option value="4">⭐⭐⭐⭐☆ Good</option>
                        <option value="3">⭐⭐⭐☆☆ Average</option>
                        <option value="2">⭐⭐☆☆☆ Poor</option>
                        <option value="1">⭐☆☆☆☆ Terrible</option>
                    </select>
                </div>
                
                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label class="form-label">Notes</label>
                    <textarea class="form-input" id="updateNotes" rows="4" style="width: 100%;" placeholder="Update details..."></textarea>
                </div>
                
                <div style="display: flex; gap: 1rem;">
                    <button type="submit" class="btn btn-primary" style="flex: 1;">💾 Save Changes</button>
                    <button type="button" class="btn btn-secondary" onclick="closeModal('updateModal')">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let currentLeadId = null;

        // Filter outcomes
        function filterOutcomes(filter) {
            const items = document.querySelectorAll('.outcome-item');
            const buttons = document.querySelectorAll('.filter-btn');
            
            // Update active button
            buttons.forEach(btn => btn.classList.remove('active'));
            event.target.classList.add('active');
            
            // Filter items
            items.forEach(item => {
                const outcome = item.dataset.outcome;
                if (filter === 'all' || outcome === filter) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        }

        // Quick report submission
        function submitQuickReport(event) {
            event.preventDefault();
            
            const leadId = document.getElementById('quickLeadId').value;
            const outcome = document.getElementById('quickOutcome').value;
            const saleAmount = document.getElementById('quickSaleAmount').value;
            const quality = document.getElementById('quickQuality').value;
            const notes = document.getElementById('quickNotes').value;
            
            const data = {
                lead_id: leadId,
                outcome: outcome,
                sale_amount: saleAmount || null,
                quality_rating: quality || null,
                notes: notes || null
            };
            
            submitOutcomeReport(data);
        }

        // Submit outcome report to API
        async function submitOutcomeReport(data) {
            try {
                const response = await fetch('/api/buyer/lead-outcomes', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();
                
                if (result.success) {
                    alert('✅ Outcome reported successfully!');
                    // Reset form
                    document.getElementById('quickLeadId').value = '';
                    document.getElementById('quickOutcome').value = '';
                    document.getElementById('quickSaleAmount').value = '';
                    document.getElementById('quickQuality').value = '';
                    document.getElementById('quickNotes').value = '';
                    
                    // Refresh the page or update the list
                    location.reload();
                } else {
                    alert('❌ Error: ' + result.message);
                }
            } catch (error) {
                alert('❌ Error submitting report: ' + error.message);
            }
        }

        // Mark lead as sold
        function markAsSold(leadId) {
            const saleAmount = prompt('💰 Enter sale amount (optional):');
            const data = {
                lead_id: leadId,
                outcome: 'sold',
                status: 'closed_won',
                sale_amount: saleAmount || null
            };
            
            submitOutcomeReport(data);
        }

        // Mark lead as not sold
        function markAsNotSold(leadId) {
            const reason = prompt('❌ Why didn\'t this lead convert? (optional):');
            const data = {
                lead_id: leadId,
                outcome: 'not_sold',
                status: 'closed_lost',
                notes: reason || null
            };
            
            submitOutcomeReport(data);
        }

        // Update outcome modal
        function updateOutcome(leadId) {
            currentLeadId = leadId;
            document.getElementById('updateModal').classList.add('active');
        }

        // Submit outcome update
        function submitOutcomeUpdate(event) {
            event.preventDefault();
            
            if (!currentLeadId) return;
            
            const data = {
                lead_id: currentLeadId,
                status: document.getElementById('updateStatus').value,
                sale_amount: document.getElementById('updateSaleAmount').value || null,
                quality_rating: document.getElementById('updateQuality').value || null,
                notes: document.getElementById('updateNotes').value || null
            };
            
            submitOutcomeReport(data);
            closeModal('updateModal');
        }

        // Close modal
        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('active');
            currentLeadId = null;
        }

        // View details
        function viewDetails(leadId) {
            alert(`📋 Viewing details for lead ${leadId}\n\nThis would open a detailed view with:\n• Full lead information\n• Contact history\n• Timeline of events\n• All notes and updates`);
        }

        // Open quick report modal
        function openQuickReportModal() {
            // Focus on the lead ID input
            document.getElementById('quickLeadId').focus();
        }

        // Close modal when clicking outside
        document.addEventListener('click', function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.classList.remove('active');
                currentLeadId = null;
            }
        });

        // Close modal with Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                document.querySelectorAll('.modal.active').forEach(modal => {
                    modal.classList.remove('active');
                });
                currentLeadId = null;
            }
        });
    </script>
</body>
</html>
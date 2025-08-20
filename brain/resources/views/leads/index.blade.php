<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Leads - The Brain</title>
    <link rel="icon" type="image/png" href="/favicon.png">
    <link rel="shortcut icon" href="/favicon.ico">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f8fafc;
            color: #1a202c;
            line-height: 1.6;
        }
        
        /* Header Navigation */
        .navbar {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            padding: 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
            width: 100%;
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
        
        .nav-brand {
            font-size: 1.2rem;  /* Smaller brand text */
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: .5rem;
            color: white;
            text-decoration: none;
            letter-spacing: .5px;
            opacity: .95;
        }
        
        .brand-logo { height: 100px; width: auto; filter: brightness(1.1); }
        
        .nav-menu {
            display: flex;
            list-style: none;
            gap: 2rem;
        }
        
        .nav-link {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.25rem;
            color: rgba(255, 255, 255, 0.9);
            text-decoration: none;
            font-weight: 600;
            font-size: 0.95rem;
            border-radius: 10px;
            transition: all 0.2s ease;
            position: relative;
            white-space: nowrap;
        }
        
        .nav-link:hover {
            background: rgba(255, 255, 255, 0.15);
            color: white;
            text-decoration: none;
            transform: translateY(-1px);
        }
        
        .nav-link.active {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            box-shadow: 0 4px 15px rgba(255, 255, 255, 0.1);
            font-weight: 600;
        }
        
        /* Dropdown Styles */
        .dropdown {
            position: relative;
        }
        
        .dropdown-toggle {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
        }
        
        .dropdown-toggle::after {
            content: '▼';
            font-size: 0.7rem;
            transition: transform 0.2s ease;
        }
        
        .dropdown.open .dropdown-toggle::after {
            transform: rotate(180deg);
        }
        
        .dropdown-menu {
            position: absolute;
            top: 100%;
            left: 0;
            background: white;
            border-radius: 8px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.1);
            min-width: 200px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s ease;
            z-index: 1000;
            overflow: hidden;
            margin-top: 0.5rem;
        }
        
        .dropdown.open .dropdown-menu {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        
        .dropdown-item {
            display: block;
            padding: 0.75rem 1rem;
            color: #4a5568;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.9rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .dropdown-item:last-child {
            border-bottom: none;
        }
        
        .dropdown-item:hover {
            background: rgba(37, 99, 235, 0.1);
            color: #2563eb;
            text-decoration: none;
            padding-left: 1.25rem;
        }
        
        .dropdown-item.active {
            background: rgba(37, 99, 235, 0.1);
            color: #2563eb;
            font-weight: 600;
        }
        
        /* Page Header */
        .page-header {
            background: white;
            border-bottom: 1px solid #e2e8f0;
            padding: 2rem 0;
            width: 100%;
        }
        
        .page-header-content {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 2rem;
            width: 100%;
            box-sizing: border-box;
        }
        
        .page-title {
            font-size: 2rem;
            font-weight: 700;
            color: #1a202c;
            margin-bottom: 0.5rem;
        }
        
        .page-subtitle {
            color: #718096;
            font-size: 1.1rem;
        }
        
        /* Main Container */
        .main-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
            width: 100%;
            box-sizing: border-box;
        }
        
        /* Search Section */
        .search-section {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .search-grid {
            display: grid;
            grid-template-columns: 3fr repeat(5, 1fr) auto auto;
            gap: 0.75rem;
            align-items: end;
            background: #f8fafc;
            padding: 1.25rem;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
        }
        
        .form-label {
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }
        
        .form-input, .form-select {
            padding: 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 0.95rem;
            transition: all 0.2s ease;
        }
        
        .form-input:focus, .form-select:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }
        
        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.2s ease;
            border: none;
            cursor: pointer;
            font-size: 0.95rem;
        }
        
        .btn-primary {
            background: #2563eb;
            color: white;
        }
        
        .btn-primary:hover {
            background: #1d4ed8;
            transform: translateY(-1px);
        }
        
        .btn-secondary {
            background: #f7fafc;
            color: #4a5568;
            border: 1px solid #e2e8f0;
        }
        
        .btn-secondary:hover {
            background: #edf2f7;
        }
        
        /* Lead Cards */
        .leads-grid {
            display: grid;
            gap: 1.5rem;
        }
        
        .lead-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            border: 1px solid #e2e8f0;
            transition: all 0.2s ease;
        }
        
        .lead-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .lead-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1rem;
        }
        
        .lead-main {
            display: flex;
            align-items: center;
            flex: 1;
        }
        
        .lead-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: #2563eb;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 1.2rem;
            margin-right: 1rem;
        }
        
        .lead-info {
            flex: 1;
        }
        
        .lead-name {
            font-size: 1.2rem;
            font-weight: 700;
            color: #1a202c;
            margin-bottom: 0.25rem;
        }
        
        .lead-contact {
            color: #718096;
            font-size: 0.9rem;
            margin-bottom: 0.25rem;
        }
        
        .lead-location {
            color: #718096;
            font-size: 0.85rem;
        }
        
        .lead-badges {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            align-items: flex-end;
        }
        
        .badge-row {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }
        
        .badge {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        /* Status Badges */
        .badge-new {
            background: #dbeafe;
            color: #1e40af;
        }
        
        .badge-contacted {
            background: #fef3c7;
            color: #92400e;
        }
        
        .badge-qualified {
            background: #d1fae5;
            color: #065f46;
        }
        
        .badge-converted {
            background: #dcfce7;
            color: #166534;
        }
        
        /* Source Badges */
        .badge-manual {
            background: #f3e8ff;
            color: #7c3aed;
        }
        
        .badge-web {
            background: #e0f2fe;
            color: #0277bd;
        }
        
                .badge-campaign {
            background: #fff3e0;
            color: #ef6c00;
        }
        
        /* Vici Badge */
        .badge-vici {
            background: #e1f5fe;
            color: #01579b;
        }
        
        /* Lead Type Badges */
        .badge-type-auto {
            background: #dbeafe;
            color: #1e40af;
        }
        
        .badge-type-home {
            background: #fef3c7;
            color: #d97706;
        }
        
        /* Campaign Badge */
        .badge-campaign {
            background: #f0f9ff;
            color: #0369a1;
        }
        
        /* SMS Status */
        .sms-status {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.8rem;
            font-weight: 600;
            color: #718096;
        }
        
        .sms-indicator {
            width: 10px;
            height: 10px;
            border-radius: 50%;
        }
        
        .sms-delivered { background: #10b981; }
        .sms-pending { background: #f59e0b; }
        .sms-failed { background: #ef4444; }
        .sms-none { background: #9ca3af; }
        
        /* Lead Details */
        .lead-details {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 1rem;
            margin: 1rem 0;
            padding: 1rem;
            background: #f8fafc;
            border-radius: 8px;
        }
        
        .detail-item {
            text-align: center;
        }
        
        .detail-icon {
            font-size: 1.5rem;
            margin-bottom: 0.25rem;
        }
        
        .detail-value {
            font-weight: 700;
            color: #1a202c;
            font-size: 0.9rem;
            margin-bottom: 0.25rem;
        }
        
        .detail-label {
            color: #718096;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        /* Lead Actions */
        .lead-actions {
            display: flex;
            gap: 0.75rem;
            justify-content: flex-end;
            margin-top: 1rem;
        }
        
        .btn-sm {
            padding: 0.5rem 1rem;
            font-size: 0.85rem;
        }
        
        .btn-view {
            background: #2563eb;
            color: white;
        }
        
        .btn-view:hover {
            background: #1d4ed8;
        }
        
        .btn-edit {
            background: #f7fafc;
            color: #4a5568;
            border: 1px solid #e2e8f0;
        }
        
        .btn-edit:hover {
            background: #edf2f7;
        }
        
        .btn-payload {
            background: #8b5cf6;
            color: white;
        }
        
        .btn-payload:hover {
            background: #7c3aed;
        }
        
        .btn-sms {
            background: #10b981;
            color: white;
        }
        
        .btn-sms:hover {
            background: #059669;
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #718096;
        }
        
        .empty-state-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }
        
        .create-lead-btn {
            background: #2563eb;
            color: white;
            margin-top: 1rem;
        }
        
        .create-lead-btn:hover {
            background: #1d4ed8;
        }
        
        /* Responsive Design */
        @media (max-width: 1200px) {
            .search-grid {
                grid-template-columns: 1fr 1fr 1fr;
                gap: 1rem;
            }
            
            .search-grid .form-group:first-child {
                grid-column: span 3;
            }
        }
        
        @media (max-width: 900px) {
            .search-grid {
                grid-template-columns: 1fr 1fr;
                gap: 1rem;
            }
            
            .search-grid .form-group:first-child {
                grid-column: span 2;
            }
        }
        
        @media (max-width: 768px) {
            .main-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
            width: 100%;
            box-sizing: border-box;
        }
            
            .search-grid {
                grid-template-columns: 1fr;
            }
            
            .lead-details {
                grid-template-columns: 1fr;
            }
            
            .lead-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
            
            .lead-actions {
                justify-content: center;
            }
            
            .lead-badges {
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation Header -->
    <nav class="navbar">
        <div class="nav-container">
            <a href="/admin" class="nav-brand">
                <img src="https://quotingfast.com/whitelogo" alt="QuotingFast" class="brand-logo" onerror="this.style.display='none'; this.nextElementSibling.style.display='inline';">
                <div class="logo-text" style="display: flex; flex-direction: column; align-items: center; line-height: 1;">
                    <div style="font-family: 'Orbitron', sans-serif; font-size: 0.9rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px;">The</div>
                    <div class="brand-text" style="font-family: 'Orbitron', sans-serif; font-size: 1.4rem; font-weight: 900; text-transform: uppercase; letter-spacing: 1px;">Brain</div>
                </div>
            </a>
            <ul class="nav-menu">
                <!-- Dashboard -->
                <li><a href="/admin" class="nav-link">📊 Dashboard</a></li>
                
                <!-- Leads Dropdown -->
                <li class="dropdown" id="leadsDropdown">
                    <a href="#" class="nav-link dropdown-toggle active">👥 Leads</a>
                    <div class="dropdown-menu">
                        <a href="/leads" class="dropdown-item active">📋 View All Leads</a>
                        <a href="/lead-upload" class="dropdown-item">📁 Upload CSV</a>
                        <a href="#" class="dropdown-item" onclick="alert('Lead Types management coming soon!')">🏷️ Lead Types</a>
                    </div>
                </li>
                
                <!-- Management Dropdown -->
                <li class="dropdown" id="managementDropdown">
                    <a href="#" class="nav-link dropdown-toggle">⚙️ Management</a>
                    <div class="dropdown-menu">
                        <a href="/admin/allstate-testing" class="dropdown-item">🧪 Allstate Testing</a>
                        <a href="/admin/lead-queue" class="dropdown-item">🛡️ Lead Queue Monitor</a>
                        <a href="/api-directory" class="dropdown-item">🔗 API Directory</a>
                        <a href="/campaign-directory" class="dropdown-item">📊 Campaigns</a>
                        <a href="/admin/buyer-management" class="dropdown-item">🎭 Buyer Management</a>
                        <a href="/admin/vici-reports" class="dropdown-item">📞 Vici Call Reports</a>
                        <a href="#" class="dropdown-item" onclick="alert('Integrations management coming soon!')">🔌 Integrations</a>
                    </div>
                </li>
                
                <!-- Analytics -->
                <li><a href="/analytics" class="nav-link" onclick="alert('Analytics dashboard coming soon!')">📈 Analytics</a></li>
                
                <!-- Communications -->
                <li class="dropdown" id="communicationsDropdown">
                    <a href="#" class="nav-link dropdown-toggle">💬 Communications</a>
                    <div class="dropdown-menu">
                        <a href="#" class="dropdown-item" onclick="alert('SMS/Messaging feature coming soon!')">📱 SMS Center</a>
                        <a href="#" class="dropdown-item" onclick="alert('Email campaigns coming soon!')">📧 Email Campaigns</a>
                        <a href="#" class="dropdown-item" onclick="alert('Call tracking coming soon!')">📞 Call Tracking</a>
                    </div>
                </li>
                
                <!-- Settings -->
                <li class="dropdown" id="settingsDropdown">
                    <a href="#" class="nav-link dropdown-toggle">🔧 Settings</a>
                    <div class="dropdown-menu">
                        <a href="#" class="dropdown-item" onclick="alert('User management coming soon!')">👥 Users</a>
                        <a href="#" class="dropdown-item" onclick="alert('System settings coming soon!')">⚙️ System</a>
                        <a href="#" class="dropdown-item" onclick="alert('API keys management coming soon!')">🔑 API Keys</a>
                        <a href="#" class="dropdown-item" onclick="alert('Backup settings coming soon!')">💾 Backup</a>
                    </div>
                </li>
            </ul>
  </div>
    </nav>

    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-content">
            <h1 class="page-title">All Leads</h1>
            <p class="page-subtitle">Manage and track your auto insurance leads</p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-container">
        <!-- DEBUG: Stats should appear here -->
        <!-- Statistics Cards -->
        <div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
            <div class="stat-card" style="background: white; padding: 1.5rem; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: center;">
                <div class="stat-number" style="font-size: 2.5rem; font-weight: bold; color: #2563eb; margin-bottom: 0.5rem;">{{ $stats['total_leads'] ?? 0 }}</div>
                <div class="stat-label" style="color: #6b7280; font-size: 1rem; font-weight: 500;">Total Leads</div>
            </div>
            <div class="stat-card" style="background: white; padding: 1.5rem; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: center;">
                <div class="stat-number" style="font-size: 2.5rem; font-weight: bold; color: #10b981; margin-bottom: 0.5rem;">{{ $stats['today_leads'] ?? 0 }}</div>
                <div class="stat-label" style="color: #6b7280; font-size: 1rem; font-weight: 500;">Today's Leads</div>
            </div>
            <div class="stat-card" style="background: white; padding: 1.5rem; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: center;">
                <div class="stat-number" style="font-size: 2.5rem; font-weight: bold; color: #f59e0b; margin-bottom: 0.5rem;">{{ $stats['vici_sent'] ?? 0 }}</div>
                <div class="stat-label" style="color: #6b7280; font-size: 1rem; font-weight: 500;">Sent to Vici</div>
            </div>
            <div class="stat-card" style="background: white; padding: 1.5rem; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: center;">
                <div class="stat-number" style="font-size: 2.5rem; font-weight: bold; color: #8b5cf6; margin-bottom: 0.5rem;">{{ $stats['allstate_sent'] ?? 0 }}</div>
                <div class="stat-label" style="color: #6b7280; font-size: 1rem; font-weight: 500;">Sent to Allstate</div>
            </div>
        </div>
        <!-- Search and Filters -->
        <div class="search-section">
            <form method="GET" action="/leads">
                <div class="search-grid">
                    <div class="form-group">
                        <label class="form-label">Search</label>
                        <input type="text" name="search" class="form-input" 
                               placeholder="Name, phone, email, city, state, zip, ID, external ID" 
                               value="{{ $search ?? '' }}">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="all">All Statuses</option>
                            @foreach($statuses as $statusOption)
                                <option value="{{ $statusOption }}" 
                                        {{ ($status ?? '') === $statusOption ? 'selected' : '' }}>
                                    {{ ucfirst($statusOption) }}
          </option>
        @endforeach
      </select>
    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Source</label>
                        <select name="source" class="form-select">
                            <option value="all">All Sources</option>
                            @foreach($sources as $sourceOption)
                                <option value="{{ $sourceOption }}" 
                                        {{ ($source ?? '') === $sourceOption ? 'selected' : '' }}>
                                    {{ ucfirst($sourceOption) }}
          </option>
        @endforeach
      </select>
    </div>
                    
                    <div class="form-group">
                        <label class="form-label">State</label>
                        <select name="state_filter" class="form-select">
                            <option value="all">All States</option>
                            @foreach($states as $stateOption)
                                <option value="{{ $stateOption }}" 
                                        {{ ($state_filter ?? '') === $stateOption ? 'selected' : '' }}>
                                    {{ $stateOption }}
          </option>
        @endforeach
      </select>
    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Vici Status</label>
                        <select name="vici_status" class="form-select">
                            <option value="all">All</option>
                            <option value="sent" {{ ($vici_status ?? '') === 'sent' ? 'selected' : '' }}>Sent to Vici</option>
                            <option value="not_sent" {{ ($vici_status ?? '') === 'not_sent' ? 'selected' : '' }}>Not Sent</option>
                        </select>
    </div>
    
                    <div class="form-group">
                        <label class="form-label">Date Range</label>
                        <select name="date_range" class="form-select" onchange="handleDateRange(this)">
                            <option value="today" {{ request('date_range') == 'today' ? 'selected' : '' }}>Today</option>
                            <option value="yesterday" {{ request('date_range') == 'yesterday' ? 'selected' : '' }}>Yesterday</option>
                            <option value="last_7_days" {{ request('date_range') == 'last_7_days' ? 'selected' : '' }}>Last 7 days</option>
                            <option value="last_30_days" {{ request('date_range') == 'last_30_days' ? 'selected' : '' }}>Last 30 days</option>
                            <option value="this_month" {{ request('date_range') == 'this_month' ? 'selected' : '' }}>This month</option>
                            <option value="last_month" {{ request('date_range') == 'last_month' ? 'selected' : '' }}>Last month</option>
                            <option value="custom" {{ request('date_range') == 'custom' ? 'selected' : '' }}>Custom Range</option>
                        </select>
                        <div id="customDateRange" style="display: {{ request('date_range') == 'custom' ? 'block' : 'none' }}; margin-top: 10px;">
                            <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}" placeholder="From">
                            <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}" placeholder="To" style="margin-top: 5px;">
    </div>
    </div>



                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="/leads" class="btn btn-secondary">Clear</a>
    </div>
            </form>
    </div>

        <!-- Test Message -->
        @if(isset($isTestMode) && $isTestMode)
            <div style="background: #fef3c7; padding: 1rem; border-radius: 8px; margin-bottom: 2rem; color: #92400e;">
                <strong>Test Mode:</strong> Showing sample data. Database connection may be unavailable.
            </div>
        @endif

        <!-- Leads Grid -->
        <div class="leads-grid">
            @forelse($leads as $lead)
                <div class="lead-card">
                    <div class="lead-header">
                        <div class="lead-main">
                            <div class="lead-avatar">
                                {{ strtoupper(substr($lead->first_name ?? $lead->name ?? 'L', 0, 1)) }}{{ strtoupper(substr($lead->last_name ?? '', 0, 1)) }}
                            </div>
                            <div class="lead-info">
                                                                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.5rem;">
                                    <div class="lead-name">
                                        {{ $lead->first_name ?? '' }} {{ $lead->last_name ?? '' }}
                                        @if((!isset($lead->first_name) || !$lead->first_name) && (!isset($lead->last_name) || !$lead->last_name))
                                            {{ $lead->name ?? 'Unknown Lead' }}
                                        @endif
                                        @if(isset($lead->external_lead_id) && $lead->external_lead_id)
                                            <span style="font-size: 0.8rem; color: #6b7280; font-weight: 400; margin-left: 0.5rem;">
                                                #{{ $lead->external_lead_id }}
                                            </span>
                                        @endif
                                        @if($lead->source)
                                            @php
                                                $sourceColors = [
                                                    'SURAJ_BULK' => ['bg' => '#8b5cf6', 'label' => 'Suraj'],
                                                    'LQF_BULK' => ['bg' => '#06b6d4', 'label' => 'LQF'],
                                                    'LQF' => ['bg' => '#06b6d4', 'label' => 'LQF'],
                                                    'SURAJ' => ['bg' => '#10b981', 'label' => 'Suraj'],
                                                    'API' => ['bg' => '#f59e0b', 'label' => 'API'],
                                                    'MANUAL' => ['bg' => '#6b7280', 'label' => 'Manual'],
                                                ];
                                                $sourceInfo = $sourceColors[$lead->source] ?? ['bg' => '#6b7280', 'label' => $lead->source];
                                            @endphp
                                            <span style="
                                                background: {{ $sourceInfo['bg'] }};
                                                color: white;
                                                padding: 2px 8px;
                                                border-radius: 12px;
                                                font-size: 10px;
                                                font-weight: 600;
                                                text-transform: uppercase;
                                                letter-spacing: 0.3px;
                                                margin-left: 8px;
                                                display: inline-block;
                                            ">
                                                {{ $sourceInfo['label'] }}
                                            </span>
                                        @endif
          </div>
                                    
                                    <!-- Badges and datetime on top line -->
                                    <div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
                                        <!-- Badges grouped together on left -->
                                        <div style="display: flex; gap: 0.25rem;">
                                            @if(isset($lead->type) && $lead->type)
                                                <span class="badge badge-type-{{ strtolower($lead->type) }}">
                                                    {{ ucfirst($lead->type) }}
                                                </span>
                                            @endif
                                            
                                            @if(isset($lead->campaign_id) && $lead->campaign_id)
                                                @php
                                                    try {
                                                        // Check if Campaign model exists and table exists
                                                        if (class_exists('\App\Models\Campaign') && \Schema::hasTable('campaigns')) {
                                                            $campaign = \App\Models\Campaign::where('campaign_id', $lead->campaign_id)->first();
                                                            $campaignName = $campaign ? ($campaign->display_name ?? $campaign->name ?? "Campaign #{$lead->campaign_id}") : "Campaign #{$lead->campaign_id}";
                                                        } else {
                                                            $campaignName = "Campaign #{$lead->campaign_id}";
                                                        }
                                                    } catch (\Exception $e) {
                                                        // Fallback if any error occurs
                                                        $campaignName = "Campaign #{$lead->campaign_id}";
                                                    }
                                                @endphp
                                                <span class="badge badge-campaign">
                                                    {{ $campaignName }}
                                                </span>
                                            @endif
                                            
                                            @if(isset($lead->sent_to_vici) && $lead->sent_to_vici)
                                                <span class="badge badge-vici">
                                                    Vici
                                                </span>
                                            @endif
                                        </div>
                                        
                                        <!-- Datetime on far right -->
                                        <span style="color: #6b7280; font-size: 0.75rem;">
                                            🕒 {{ $lead->created_at ? $lead->created_at->setTimezone('America/New_York')->format('M j, g:i A') : 'Unknown' }}
                                        </span>
                                    </div>
                                </div>
                                <div class="lead-contact">
                                    📞 @if(isset($lead->phone) && $lead->phone)
                                        @php
                                            $phone = preg_replace('/[^0-9]/', '', $lead->phone);
                                            if(strlen($phone) == 10) {
                                                $formatted = '(' . substr($phone, 0, 3) . ')' . substr($phone, 3, 3) . '-' . substr($phone, 6, 4);
                                            } else {
                                                $formatted = $lead->phone;
                                            }
                @endphp
                {{ $formatted }}
                <button onclick="copyToClipboard('{{ $lead->phone }}', this)" style="background: none; border: none; cursor: pointer; margin-left: 5px;" title="Copy phone number">📎</button>
                                    @else
                                        No phone
                                    @endif
                                    @if(isset($lead->email) && $lead->email)
                                        • ✉️ {{ $lead->email }}
                                    @endif
                                </div>
                                <div class="lead-location">
                                    📍 {{ $lead->city ?? '' }}@if(isset($lead->city) && $lead->city && isset($lead->state) && $lead->state), @endif{{ $lead->state ?? '' }}
                                </div>
                            </div>
        </div>
                        
                        <!-- SMS indicator only (no button) -->
                        <div class="sms-status" style="margin-top: 0.25rem;">
                            <div class="sms-indicator sms-none"></div>
                            <span>SMS: None</span>
                        </div>
                    </div>
                    
                    <div class="lead-actions" style="margin-top: 0.5rem;">
                        <a href="/agent/lead/{{ $lead->id }}?mode=view" class="btn btn-sm btn-view">
                            👁️ View
                        </a>
                        <a href="/agent/lead/{{ $lead->id }}?mode=edit" class="btn btn-sm btn-edit">
                            ✏️ Edit
                        </a>
                        <a href="/api/lead/{{ $lead->id }}/payload" class="btn btn-sm btn-payload" target="_blank">
                            📄 Payload
                        </a>
                    </div>
                </div>
            @empty
                <div class="empty-state">
                    <div class="empty-state-icon">📋</div>
                    <h3>No leads found</h3>
                    <p>Try adjusting your search criteria or create a new lead.</p>
                    <a href="#" class="btn create-lead-btn" onclick="alert('Create lead feature coming soon!')">
                        + Create New Lead
                    </a>
                </div>
            @endforelse
        </div>
    </div>

    <script>
        // Handle date range selection
        function handleDateRange(select) {
            const customDiv = document.getElementById('customDateRange');
            if (select.value === 'custom') {
                customDiv.style.display = 'block';
            } else {
                customDiv.style.display = 'none';
            }
        }
        
        // Copy to clipboard function
        function copyToClipboard(text, button) {
            navigator.clipboard.writeText(text).then(function() {
                const originalText = button.innerHTML;
                button.innerHTML = '✅';
                setTimeout(function() {
                    button.innerHTML = originalText;
                }, 2000);
            }).catch(function(err) {
                console.error('Failed to copy: ', err);
            });
        }
        
        // Dropdown functionality
        document.addEventListener('DOMContentLoaded', function() {
            const dropdowns = document.querySelectorAll('.dropdown');
            
            dropdowns.forEach(dropdown => {
                const toggle = dropdown.querySelector('.dropdown-toggle');
                const menu = dropdown.querySelector('.dropdown-menu');
                
                if (toggle && menu) {
                    toggle.addEventListener('click', function(e) {
                        e.preventDefault();
                        
                        // Close other dropdowns
                        dropdowns.forEach(otherDropdown => {
                            if (otherDropdown !== dropdown) {
                                otherDropdown.classList.remove('open');
                            }
                        });
                        
                        // Toggle current dropdown
                        dropdown.classList.toggle('open');
                    });
                }
            });
            
            // Close dropdowns when clicking outside
            document.addEventListener('click', function(e) {
                if (!e.target.closest('.dropdown')) {
                    dropdowns.forEach(dropdown => {
                        dropdown.classList.remove('open');
                    });
                }
            });
            
            // Close dropdowns on escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    dropdowns.forEach(dropdown => {
                        dropdown.classList.remove('open');
                    });
                }
            });
        });
    </script>
</body>
</html>                if (!e.target.closest('.dropdown')) {
                    dropdowns.forEach(dropdown => {
                        dropdown.classList.remove('open');
                    });
                }
            });
            
            // Close dropdowns on escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    dropdowns.forEach(dropdown => {
                        dropdown.classList.remove('open');
                    });
                }
            });
        });
    </script>
</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Leads - The Brain</title>
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
            background: #4f46e5;
            color: white;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 100;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .nav-container {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 2rem;
            height: 70px;
        }
        
        .nav-brand {
            font-size: 1.2rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            color: white;
            text-decoration: none;
        }
        
        .brand-logo {
            width: 32px;
            height: 32px;
            background: #4f46e5;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            color: white;
            border: 2px solid rgba(255,255,255,0.2);
        }
        
        .nav-menu {
            display: flex;
            list-style: none;
            gap: 2rem;
        }
        
        .nav-link {
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            padding: 0.75rem 1.25rem;
            border-radius: 8px;
            transition: all 0.2s ease;
            font-weight: 500;
        }
        
        .nav-link:hover {
            background: rgba(255,255,255,0.1);
            color: white;
        }
        
        .nav-link.active {
            background: rgba(255,255,255,0.15);
            color: white;
            font-weight: 600;
        }
        
        /* Page Header */
        .page-header {
            background: white;
            border-bottom: 1px solid #e2e8f0;
            padding: 2rem 0;
        }
        
        .page-header-content {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 2rem;
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
            grid-template-columns: 2fr 1fr 1fr 1fr 1fr auto auto;
            gap: 1rem;
            align-items: end;
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
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
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
            background: #4f46e5;
            color: white;
        }
        
        .btn-primary:hover {
            background: #4338ca;
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
            background: #4f46e5;
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
            background: #4f46e5;
            color: white;
        }
        
        .btn-view:hover {
            background: #4338ca;
        }
        
        .btn-edit {
            background: #f7fafc;
            color: #4a5568;
            border: 1px solid #e2e8f0;
        }
        
        .btn-edit:hover {
            background: #edf2f7;
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
            background: #4f46e5;
            color: white;
            margin-top: 1rem;
        }
        
        .create-lead-btn:hover {
            background: #4338ca;
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
                padding: 1rem;
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
                <div class="brand-logo">B</div>
                <span>The Brain</span>
            </a>
            <ul class="nav-menu">
                <li><a href="/admin" class="nav-link">Dashboard</a></li>
                <li><a href="/leads" class="nav-link active">Leads</a></li>
                <li><a href="#messaging" class="nav-link" onclick="alert('SMS/Messaging feature coming soon!')">Messaging</a></li>
                <li><a href="/analytics" class="nav-link">Analytics</a></li>
                <li><a href="#campaigns" class="nav-link" onclick="alert('Campaign management feature coming soon!')">Campaigns</a></li>
                <li><a href="#settings" class="nav-link" onclick="alert('Settings feature coming soon!')">Settings</a></li>
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
        <!-- Search and Filters -->
        <div class="search-section">
            <form method="GET" action="/leads">
                <div class="search-grid">
                    <div class="form-group">
                        <label class="form-label">Search</label>
                        <input type="text" name="search" class="form-input" 
                               placeholder="Name, phone, or email" 
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
                                <div class="lead-name">
                                    {{ $lead->first_name ?? '' }} {{ $lead->last_name ?? '' }}
                                    @if(!$lead->first_name && !$lead->last_name)
                                        {{ $lead->name ?? 'Unknown Lead' }}
                                    @endif
                                </div>
                                <div class="lead-contact">
                                    📞 @if($lead->phone)
                                        @php
                                            $phone = preg_replace('/[^0-9]/', '', $lead->phone);
                                            if(strlen($phone) == 10) {
                                                $formatted = '(' . substr($phone, 0, 3) . ')' . substr($phone, 3, 3) . '-' . substr($phone, 6, 4);
                                            } else {
                                                $formatted = $lead->phone;
                                            }
                                        @endphp
                                        {{ $formatted }}
                                    @else
                                        No phone
                                    @endif
                                    @if($lead->email)
                                        • ✉️ {{ $lead->email }}
                                    @endif
                                </div>
                                <div class="lead-location">
                                    📍 {{ $lead->city ?? '' }}@if($lead->city && $lead->state), @endif{{ $lead->state ?? '' }}
                                </div>
                            </div>
                        </div>
                        
                        <div class="lead-badges">
                            <div class="badge-row">
                                @if($lead->status)
                                    <span class="badge badge-{{ strtolower(str_replace(' ', '', $lead->status)) }}">
                                        {{ $lead->status }}
                                    </span>
                                @endif
                                
                                @if($lead->source)
                                    <span class="badge badge-{{ strtolower($lead->source) }}">
                                        {{ $lead->source }}
                                    </span>
                                @endif
                                
                                @if(isset($lead->sent_to_vici) && $lead->sent_to_vici)
                                    <span class="badge badge-vici">
                                        Vici
                                    </span>
                                @endif
                            </div>
                            
                            <div class="sms-status">
                                <div class="sms-indicator sms-none"></div>
                                <span>SMS: None</span>
                            </div>
                        </div>
                    </div>
                    

                    
                    <div class="lead-actions">
                        <a href="/agent/lead/{{ $lead->id }}?mode=view" class="btn btn-sm btn-view">
                            👁️ View
                        </a>
                        <a href="/agent/lead/{{ $lead->id }}?mode=edit" class="btn btn-sm btn-edit">
                            ✏️ Edit
                        </a>
                        <a href="#" class="btn btn-sm btn-sms" onclick="alert('SMS feature coming soon for {{ $lead->first_name ?? $lead->name }}!')">
                            💬 SMS
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
</body>
</html>
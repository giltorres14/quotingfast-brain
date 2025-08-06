<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>CRM Integration - {{ $buyer->full_name }} | The Brain</title>
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
        
        /* CRM Grid */
        .crm-grid {
            display: grid;
            grid-template-columns: 1fr 400px;
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
        
        /* CRM Status */
        .crm-status {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .crm-status.disabled {
            background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
        }
        
        .status-indicator {
            width: 12px;
            height: 12px;
            background: #fbbf24;
            border-radius: 50%;
            animation: pulse 2s infinite;
        }
        
        .status-indicator.connected {
            background: #34d399;
            animation: none;
        }
        
        .status-indicator.disconnected {
            background: #f87171;
            animation: none;
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        
        .status-text {
            flex: 1;
        }
        
        .status-title {
            font-weight: 600;
            margin-bottom: 0.25rem;
        }
        
        .status-subtitle {
            opacity: 0.9;
            font-size: 0.9rem;
        }
        
        /* CRM Selection */
        .crm-selection {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .crm-option {
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            padding: 1.5rem;
            cursor: pointer;
            transition: all 0.2s;
            text-align: center;
            background: white;
        }
        
        .crm-option:hover {
            border-color: #667eea;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .crm-option.selected {
            border-color: #667eea;
            background: #f0f4ff;
        }
        
        .crm-logo {
            width: 48px;
            height: 48px;
            margin: 0 auto 1rem;
            background: #f3f4f6;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }
        
        .crm-name {
            font-weight: 600;
            color: #1a202c;
            margin-bottom: 0.5rem;
        }
        
        .crm-description {
            font-size: 0.85rem;
            color: #6b7280;
        }
        
        /* Configuration Form */
        .config-form {
            display: none;
            margin-top: 2rem;
        }
        
        .config-form.active {
            display: block;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-label {
            display: block;
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.5rem;
        }
        
        .form-input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 0.9rem;
            transition: border-color 0.2s;
        }
        
        .form-input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .form-textarea {
            min-height: 100px;
            resize: vertical;
        }
        
        .form-help {
            font-size: 0.8rem;
            color: #6b7280;
            margin-top: 0.25rem;
        }
        
        .form-actions {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid #e5e7eb;
        }
        
        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
            font-size: 0.9rem;
        }
        
        .btn-primary {
            background: #667eea;
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
        
        .btn-test {
            background: #10b981;
            color: white;
        }
        
        .btn-test:hover {
            background: #059669;
        }
        
        .btn-danger {
            background: #ef4444;
            color: white;
        }
        
        .btn-danger:hover {
            background: #dc2626;
        }
        
        /* Stats Panel */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: #f8fafc;
            border-radius: 8px;
            padding: 1.5rem;
            text-align: center;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: #1a202c;
            margin-bottom: 0.25rem;
        }
        
        .stat-label {
            font-size: 0.85rem;
            color: #6b7280;
            font-weight: 500;
        }
        
        /* Field Mapping */
        .field-mapping {
            background: #f8fafc;
            border-radius: 8px;
            padding: 1.5rem;
            margin-top: 1rem;
        }
        
        .mapping-header {
            font-weight: 600;
            color: #1a202c;
            margin-bottom: 1rem;
        }
        
        .mapping-row {
            display: grid;
            grid-template-columns: 1fr auto 1fr;
            gap: 1rem;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .mapping-arrow {
            color: #6b7280;
            font-weight: 600;
        }
        
        /* Test Results */
        .test-result {
            margin-top: 1rem;
            padding: 1rem;
            border-radius: 6px;
            font-weight: 500;
        }
        
        .test-result.success {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #bbf7d0;
        }
        
        .test-result.error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
        
        /* Loading States */
        .loading {
            opacity: 0.6;
            pointer-events: none;
        }
        
        .spinner {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid #f3f3f3;
            border-top: 2px solid #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Responsive */
        @media (max-width: 1024px) {
            .crm-grid {
                grid-template-columns: 1fr;
            }
            
            .crm-selection {
                grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
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
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .form-actions {
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
            <a href="/buyer/crm-settings" class="nav-tab active">🔗 CRM Integration</a>
            <a href="/buyer/settings" class="nav-tab">⚙️ Settings</a>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="main-container">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-title">CRM Integration 🔗</h1>
            <p class="page-subtitle">
                Connect your CRM system to automatically receive leads directly in your existing workflow. No more manual data entry!
            </p>
        </div>
        
        <!-- CRM Status -->
        <div class="crm-status" id="crmStatus">
            <div class="status-indicator" id="statusIndicator"></div>
            <div class="status-text">
                <div class="status-title" id="statusTitle">CRM Integration Disabled</div>
                <div class="status-subtitle" id="statusSubtitle">Configure your CRM connection below to start receiving leads automatically</div>
            </div>
        </div>
        
        <!-- CRM Grid -->
        <div class="crm-grid">
            <!-- Configuration Panel -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">
                        <span class="card-icon">⚙️</span>
                        CRM Configuration
                    </h2>
                </div>
                
                <!-- CRM Selection -->
                <div class="crm-selection">
                    <div class="crm-option" data-crm="allstate_lead_manager" onclick="selectCRM('allstate_lead_manager')">
                        <div class="crm-logo">🏠</div>
                        <div class="crm-name">Allstate Lead Manager</div>
                        <div class="crm-description">Insurance lead platform</div>
                    </div>
                    
                    <div class="crm-option" data-crm="salesforce" onclick="selectCRM('salesforce')">
                        <div class="crm-logo">☁️</div>
                        <div class="crm-name">Salesforce</div>
                        <div class="crm-description">Enterprise CRM platform</div>
                    </div>
                    
                    <div class="crm-option" data-crm="hubspot" onclick="selectCRM('hubspot')">
                        <div class="crm-logo">🟠</div>
                        <div class="crm-name">HubSpot</div>
                        <div class="crm-description">All-in-one CRM</div>
                    </div>
                    
                    <div class="crm-option" data-crm="pipedrive" onclick="selectCRM('pipedrive')">
                        <div class="crm-logo">📊</div>
                        <div class="crm-name">Pipedrive</div>
                        <div class="crm-description">Sales-focused CRM</div>
                    </div>
                    
                    <div class="crm-option" data-crm="zoho" onclick="selectCRM('zoho')">
                        <div class="crm-logo">🔷</div>
                        <div class="crm-name">Zoho CRM</div>
                        <div class="crm-description">Business suite</div>
                    </div>
                    
                    <div class="crm-option" data-crm="gohighlevel" onclick="selectCRM('gohighlevel')">
                        <div class="crm-logo">⚡</div>
                        <div class="crm-name">GoHighLevel</div>
                        <div class="crm-description">Marketing platform</div>
                    </div>
                    
                    <div class="crm-option" data-crm="webhook" onclick="selectCRM('webhook')">
                        <div class="crm-logo">🔗</div>
                        <div class="crm-name">Custom Webhook</div>
                        <div class="crm-description">Any system</div>
                    </div>
                </div>
                
                <!-- Allstate Lead Manager Configuration -->
                <div class="config-form" id="allstate_lead_manager-config">
                    <h3 style="margin-bottom: 1rem; color: #1a202c;">Allstate Lead Manager Configuration</h3>
                    
                    <div class="form-group">
                        <label class="form-label">Posting URL</label>
                        <input type="text" class="form-input" id="alm-posting-url" placeholder="https://www.leadmanagementlab.com/api/accounts/abc123def456ghi/leads/">
                        <div class="form-help">Your unique LML posting URL from Administration → Web Lead Setup</div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Provider ID</label>
                        <input type="text" class="form-input" id="alm-provider-id" placeholder="lmn345opq678rst">
                        <div class="form-help">Unique Provider ID from your LML setup configuration</div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Default Lead Type</label>
                        <select class="form-input" id="alm-lead-type">
                            <option value="Auto">Auto Insurance</option>
                            <option value="Home">Home Insurance</option>
                            <option value="Renter">Renter Insurance</option>
                            <option value="LiveTransfer-Auto">Live Transfer - Auto</option>
                            <option value="LiveTransfer-Home">Live Transfer - Home</option>
                        </select>
                        <div class="form-help">Default lead type for leads sent to Allstate Lead Manager</div>
                    </div>
                    
                    <div class="field-mapping">
                        <div class="mapping-header">Supported Fields</div>
                        <div style="font-size: 0.85rem; color: #6b7280; line-height: 1.5;">
                            <strong>Required:</strong> FirstName, LastName, Address1, City, State, ZipCode<br>
                            <strong>Optional Contact:</strong> HomePhone, MobilePhone, WorkPhone, EmailAddress<br>
                            <strong>Personal:</strong> DOB, MaritalStatus, Homeowner, Renter<br>
                            <strong>Auto Insurance:</strong> AutoInsured, AutoCurrentCarrier, Auto1-4 (Make, Model, Year, VIN, Trim)<br>
                            <strong>Home Insurance:</strong> HomeCurrentCarrier, YearBuilt, ConstructionType, GarageType, Stories, Baths, Bedrooms, SqFootage, RoofType, AgeOfRoof, BurglarAlarm
                        </div>
                    </div>
                </div>
                
                <!-- Salesforce Configuration -->
                <div class="config-form" id="salesforce-config">
                    <h3 style="margin-bottom: 1rem; color: #1a202c;">Salesforce Configuration</h3>
                    
                    <div class="form-group">
                        <label class="form-label">Instance URL</label>
                        <input type="text" class="form-input" id="sf-instance-url" placeholder="https://yourorg.salesforce.com">
                        <div class="form-help">Your Salesforce instance URL</div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Access Token</label>
                        <input type="password" class="form-input" id="sf-access-token" placeholder="Your OAuth access token">
                        <div class="form-help">OAuth 2.0 access token for API access</div>
                    </div>
                    
                    <div class="field-mapping">
                        <div class="mapping-header">Field Mapping (Optional)</div>
                        <div class="mapping-row">
                            <select class="form-input">
                                <option>FirstName</option>
                                <option>LastName</option>
                                <option>Email</option>
                                <option>Phone</option>
                            </select>
                            <div class="mapping-arrow">→</div>
                            <select class="form-input">
                                <option>first_name</option>
                                <option>last_name</option>
                                <option>email</option>
                                <option>phone</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <!-- HubSpot Configuration -->
                <div class="config-form" id="hubspot-config">
                    <h3 style="margin-bottom: 1rem; color: #1a202c;">HubSpot Configuration</h3>
                    
                    <div class="form-group">
                        <label class="form-label">Private App Token</label>
                        <input type="password" class="form-input" id="hs-api-key" placeholder="Your HubSpot private app token">
                        <div class="form-help">Create a private app in HubSpot to get this token</div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Portal ID (Optional)</label>
                        <input type="text" class="form-input" id="hs-portal-id" placeholder="12345678">
                        <div class="form-help">Your HubSpot portal ID</div>
                    </div>
                </div>
                
                <!-- Pipedrive Configuration -->
                <div class="config-form" id="pipedrive-config">
                    <h3 style="margin-bottom: 1rem; color: #1a202c;">Pipedrive Configuration</h3>
                    
                    <div class="form-group">
                        <label class="form-label">Company Domain</label>
                        <input type="text" class="form-input" id="pd-domain" placeholder="yourcompany">
                        <div class="form-help">Your Pipedrive company domain (yourcompany.pipedrive.com)</div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">API Token</label>
                        <input type="password" class="form-input" id="pd-api-token" placeholder="Your Pipedrive API token">
                        <div class="form-help">Found in Settings > Personal preferences > API</div>
                    </div>
                </div>
                
                <!-- Custom Webhook Configuration -->
                <div class="config-form" id="webhook-config">
                    <h3 style="margin-bottom: 1rem; color: #1a202c;">Custom Webhook Configuration</h3>
                    
                    <div class="form-group">
                        <label class="form-label">Webhook URL</label>
                        <input type="url" class="form-input" id="wh-url" placeholder="https://your-system.com/webhook">
                        <div class="form-help">URL where leads will be sent via POST request</div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Authentication Method</label>
                        <select class="form-input" id="wh-auth-method" onchange="toggleAuthFields()">
                            <option value="none">None</option>
                            <option value="bearer">Bearer Token</option>
                            <option value="api_key">API Key</option>
                            <option value="basic">Basic Auth</option>
                        </select>
                    </div>
                    
                    <div class="form-group" id="wh-bearer-token" style="display: none;">
                        <label class="form-label">Bearer Token</label>
                        <input type="password" class="form-input" placeholder="Your bearer token">
                    </div>
                    
                    <div class="form-group" id="wh-api-key" style="display: none;">
                        <label class="form-label">API Key</label>
                        <input type="password" class="form-input" placeholder="Your API key">
                    </div>
                    
                    <div class="form-group" id="wh-basic-auth" style="display: none;">
                        <label class="form-label">Username</label>
                        <input type="text" class="form-input" placeholder="Username">
                        <label class="form-label" style="margin-top: 0.5rem;">Password</label>
                        <input type="password" class="form-input" placeholder="Password">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Field Mapping (JSON)</label>
                        <textarea class="form-input form-textarea" id="wh-mapping" placeholder='{"crm_field": "brain_field", "name": "first_name", "email": "email"}'></textarea>
                        <div class="form-help">Map Brain fields to your CRM fields in JSON format</div>
                    </div>
                </div>
                
                <!-- Form Actions -->
                <div class="form-actions">
                    <button class="btn btn-test" onclick="testConnection()">
                        <span class="btn-text">🧪 Test Connection</span>
                    </button>
                    <button class="btn btn-primary" onclick="saveCRMConfig()">
                        <span class="btn-text">💾 Save Configuration</span>
                    </button>
                    <button class="btn btn-secondary" onclick="resetForm()">Reset</button>
                    <button class="btn btn-danger" onclick="disableCRM()" style="margin-left: auto;">Disable CRM</button>
                </div>
                
                <!-- Test Result -->
                <div id="testResult"></div>
            </div>
            
            <!-- Stats Panel -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">
                        <span class="card-icon">📊</span>
                        Integration Statistics
                    </h2>
                </div>
                
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-number" id="totalDeliveries">0</div>
                        <div class="stat-label">Total Deliveries</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-number" id="successRate">0%</div>
                        <div class="stat-label">Success Rate</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-number" id="lastDelivery">Never</div>
                        <div class="stat-label">Last Delivery</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-number" id="failedDeliveries">0</div>
                        <div class="stat-label">Failed Deliveries</div>
                    </div>
                </div>
                
                <!-- Benefits Section -->
                <div style="background: #f0f4ff; border-radius: 8px; padding: 1.5rem; margin-top: 2rem;">
                    <h3 style="color: #1a202c; margin-bottom: 1rem; font-size: 1.1rem;">🚀 CRM Integration Benefits</h3>
                    <ul style="color: #4b5563; font-size: 0.9rem; line-height: 1.6;">
                        <li style="margin-bottom: 0.5rem;">✅ <strong>Automatic Lead Delivery</strong> - No manual data entry</li>
                        <li style="margin-bottom: 0.5rem;">✅ <strong>Real-time Sync</strong> - Leads appear instantly in your CRM</li>
                        <li style="margin-bottom: 0.5rem;">✅ <strong>Custom Field Mapping</strong> - Match your CRM structure</li>
                        <li style="margin-bottom: 0.5rem;">✅ <strong>Delivery Tracking</strong> - Monitor success rates</li>
                        <li style="margin-bottom: 0.5rem;">✅ <strong>Error Handling</strong> - Automatic retry on failures</li>
                        <li>✅ <strong>Multiple CRM Support</strong> - Works with popular platforms</li>
                    </ul>
                </div>
                
                <!-- Documentation Links -->
                <div style="margin-top: 2rem;">
                    <h3 style="color: #1a202c; margin-bottom: 1rem; font-size: 1.1rem;">📚 Setup Guides</h3>
                    <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                        <a href="#" style="color: #667eea; text-decoration: none; font-size: 0.9rem;">📖 Salesforce Integration Guide</a>
                        <a href="#" style="color: #667eea; text-decoration: none; font-size: 0.9rem;">📖 HubSpot Setup Instructions</a>
                        <a href="#" style="color: #667eea; text-decoration: none; font-size: 0.9rem;">📖 Pipedrive Configuration</a>
                        <a href="#" style="color: #667eea; text-decoration: none; font-size: 0.9rem;">📖 Custom Webhook Examples</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let selectedCRM = null;
        let currentConfig = {};

        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            loadCurrentConfig();
        });

        // Load current CRM configuration
        async function loadCurrentConfig() {
            try {
                const response = await fetch('/api/buyer/crm/config');
                if (response.ok) {
                    const data = await response.json();
                    if (data.config && data.config.enabled) {
                        currentConfig = data.config;
                        selectCRM(data.config.type);
                        populateForm(data.config);
                        updateStatus(true, data.config.type);
                        updateStats(data.stats || {});
                    }
                }
            } catch (error) {
                console.error('Failed to load CRM config:', error);
            }
        }

        // Select CRM type
        function selectCRM(crmType) {
            selectedCRM = crmType;
            
            // Update visual selection
            document.querySelectorAll('.crm-option').forEach(option => {
                option.classList.remove('selected');
            });
            document.querySelector(`[data-crm="${crmType}"]`).classList.add('selected');
            
            // Hide all config forms
            document.querySelectorAll('.config-form').forEach(form => {
                form.classList.remove('active');
            });
            
            // Show selected config form
            const configForm = document.getElementById(`${crmType}-config`);
            if (configForm) {
                configForm.classList.add('active');
            }
        }

        // Populate form with existing config
        function populateForm(config) {
            switch (config.type) {
                case 'allstate_lead_manager':
                    document.getElementById('alm-posting-url').value = config.posting_url || '';
                    document.getElementById('alm-provider-id').value = config.provider_id || '';
                    document.getElementById('alm-lead-type').value = config.lead_type || 'Auto';
                    break;
                case 'salesforce':
                    document.getElementById('sf-instance-url').value = config.instance_url || '';
                    document.getElementById('sf-access-token').value = config.access_token || '';
                    break;
                case 'hubspot':
                    document.getElementById('hs-api-key').value = config.api_key || '';
                    document.getElementById('hs-portal-id').value = config.portal_id || '';
                    break;
                case 'pipedrive':
                    document.getElementById('pd-domain').value = config.domain || '';
                    document.getElementById('pd-api-token').value = config.api_token || '';
                    break;
                case 'webhook':
                    document.getElementById('wh-url').value = config.webhook_url || '';
                    document.getElementById('wh-auth-method').value = config.auth_method || 'none';
                    document.getElementById('wh-mapping').value = JSON.stringify(config.field_mapping || {}, null, 2);
                    toggleAuthFields();
                    break;
            }
        }

        // Toggle authentication fields for webhook
        function toggleAuthFields() {
            const authMethod = document.getElementById('wh-auth-method').value;
            
            document.getElementById('wh-bearer-token').style.display = authMethod === 'bearer' ? 'block' : 'none';
            document.getElementById('wh-api-key').style.display = authMethod === 'api_key' ? 'block' : 'none';
            document.getElementById('wh-basic-auth').style.display = authMethod === 'basic' ? 'block' : 'none';
        }

        // Test CRM connection
        async function testConnection() {
            if (!selectedCRM) {
                alert('Please select a CRM first');
                return;
            }

            const config = gatherFormData();
            const testBtn = document.querySelector('.btn-test');
            const originalText = testBtn.innerHTML;
            
            testBtn.innerHTML = '<div class="spinner"></div> Testing...';
            testBtn.disabled = true;

            try {
                const response = await fetch('/api/buyer/crm/test', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(config)
                });

                const result = await response.json();
                showTestResult(result.success, result.message || result.error);

            } catch (error) {
                showTestResult(false, 'Connection test failed: ' + error.message);
            } finally {
                testBtn.innerHTML = originalText;
                testBtn.disabled = false;
            }
        }

        // Save CRM configuration
        async function saveCRMConfig() {
            if (!selectedCRM) {
                alert('Please select a CRM first');
                return;
            }

            const config = gatherFormData();
            const saveBtn = document.querySelector('.btn-primary');
            const originalText = saveBtn.innerHTML;
            
            saveBtn.innerHTML = '<div class="spinner"></div> Saving...';
            saveBtn.disabled = true;

            try {
                const response = await fetch('/api/buyer/crm/config', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(config)
                });

                const result = await response.json();
                
                if (result.success) {
                    updateStatus(true, selectedCRM);
                    showTestResult(true, 'CRM configuration saved successfully!');
                } else {
                    showTestResult(false, result.error || 'Failed to save configuration');
                }

            } catch (error) {
                showTestResult(false, 'Save failed: ' + error.message);
            } finally {
                saveBtn.innerHTML = originalText;
                saveBtn.disabled = false;
            }
        }

        // Gather form data based on selected CRM
        function gatherFormData() {
            const config = {
                type: selectedCRM,
                enabled: true
            };

            switch (selectedCRM) {
                case 'allstate_lead_manager':
                    config.posting_url = document.getElementById('alm-posting-url').value;
                    config.provider_id = document.getElementById('alm-provider-id').value;
                    config.lead_type = document.getElementById('alm-lead-type').value;
                    break;
                case 'salesforce':
                    config.instance_url = document.getElementById('sf-instance-url').value;
                    config.access_token = document.getElementById('sf-access-token').value;
                    break;
                case 'hubspot':
                    config.api_key = document.getElementById('hs-api-key').value;
                    config.portal_id = document.getElementById('hs-portal-id').value;
                    break;
                case 'pipedrive':
                    config.domain = document.getElementById('pd-domain').value;
                    config.api_token = document.getElementById('pd-api-token').value;
                    break;
                case 'webhook':
                    config.webhook_url = document.getElementById('wh-url').value;
                    config.auth_method = document.getElementById('wh-auth-method').value;
                    
                    try {
                        config.field_mapping = JSON.parse(document.getElementById('wh-mapping').value || '{}');
                    } catch (e) {
                        config.field_mapping = {};
                    }
                    break;
            }

            return config;
        }

        // Show test result
        function showTestResult(success, message) {
            const resultDiv = document.getElementById('testResult');
            resultDiv.className = `test-result ${success ? 'success' : 'error'}`;
            resultDiv.textContent = (success ? '✅ ' : '❌ ') + message;
            resultDiv.style.display = 'block';

            // Hide after 5 seconds
            setTimeout(() => {
                resultDiv.style.display = 'none';
            }, 5000);
        }

        // Update CRM status display
        function updateStatus(connected, crmType) {
            const statusDiv = document.getElementById('crmStatus');
            const indicator = document.getElementById('statusIndicator');
            const title = document.getElementById('statusTitle');
            const subtitle = document.getElementById('statusSubtitle');

            if (connected) {
                statusDiv.classList.remove('disabled');
                indicator.classList.add('connected');
                indicator.classList.remove('disconnected');
                title.textContent = `${getCRMName(crmType)} Connected`;
                subtitle.textContent = `Leads are being automatically delivered to your ${getCRMName(crmType)} account`;
            } else {
                statusDiv.classList.add('disabled');
                indicator.classList.add('disconnected');
                indicator.classList.remove('connected');
                title.textContent = 'CRM Integration Disabled';
                subtitle.textContent = 'Configure your CRM connection below to start receiving leads automatically';
            }
        }

        // Update statistics
        function updateStats(stats) {
            document.getElementById('totalDeliveries').textContent = stats.total_attempts || 0;
            document.getElementById('successRate').textContent = (stats.success_rate || 0) + '%';
            document.getElementById('failedDeliveries').textContent = stats.failed_deliveries || 0;
            document.getElementById('lastDelivery').textContent = stats.last_attempt ? 
                new Date(stats.last_attempt).toLocaleDateString() : 'Never';
        }

        // Get CRM display name
        function getCRMName(crmType) {
            const names = {
                'salesforce': 'Salesforce',
                'hubspot': 'HubSpot',
                'pipedrive': 'Pipedrive',
                'zoho': 'Zoho CRM',
                'webhook': 'Custom Webhook'
            };
            return names[crmType] || crmType;
        }

        // Reset form
        function resetForm() {
            document.querySelectorAll('.form-input').forEach(input => {
                input.value = '';
            });
            document.querySelectorAll('.crm-option').forEach(option => {
                option.classList.remove('selected');
            });
            document.querySelectorAll('.config-form').forEach(form => {
                form.classList.remove('active');
            });
            selectedCRM = null;
            document.getElementById('testResult').style.display = 'none';
        }

        // Disable CRM integration
        async function disableCRM() {
            if (!confirm('Are you sure you want to disable CRM integration? Leads will no longer be automatically delivered to your CRM.')) {
                return;
            }

            try {
                const response = await fetch('/api/buyer/crm/disable', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const result = await response.json();
                
                if (result.success) {
                    updateStatus(false);
                    resetForm();
                    showTestResult(true, 'CRM integration has been disabled');
                } else {
                    showTestResult(false, 'Failed to disable CRM integration');
                }

            } catch (error) {
                showTestResult(false, 'Error: ' + error.message);
            }
        }
    </script>
</body>
</html>
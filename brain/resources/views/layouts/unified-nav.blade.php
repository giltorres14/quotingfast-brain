<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'QuotingFast Brain')</title>
    <link rel="icon" type="image/png" href="/favicon.png">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            background: #f5f7fa;
        }
        
        /* Top Navigation Bar */
        .top-nav {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        
        .nav-container {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 20px;
        }
        
        .logo-section {
            display: flex;
            align-items: center;
            padding: 15px 0;
        }
        
        .logo-section img {
            height: 40px;
            margin-right: 15px;
        }
        
        .logo-text {
            color: white;
            font-size: 1.5rem;
            font-weight: bold;
        }
        
        /* Main Navigation Tabs */
        .main-tabs {
            display: flex;
            flex: 1;
            margin: 0 40px;
        }
        
        .tab-item {
            position: relative;
            padding: 20px 30px;
            color: rgba(255,255,255,0.9);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .tab-item:hover {
            background: rgba(255,255,255,0.1);
            color: white;
        }
        
        .tab-item.active {
            background: rgba(255,255,255,0.15);
            color: white;
        }
        
        .tab-item.active::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: white;
        }
        
        /* Dropdown Menu */
        .tab-dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            min-width: 250px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s ease;
            z-index: 100;
        }
        
        .tab-item:hover .tab-dropdown {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        
        .dropdown-section {
            padding: 10px 0;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .dropdown-section:last-child {
            border-bottom: none;
        }
        
        .dropdown-title {
            padding: 8px 20px;
            font-size: 0.75rem;
            font-weight: 600;
            color: #718096;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        
        .dropdown-item {
            display: block;
            padding: 10px 20px;
            color: #2d3748;
            text-decoration: none;
            transition: all 0.2s ease;
        }
        
        .dropdown-item:hover {
            background: #f7fafc;
            color: #667eea;
            padding-left: 25px;
        }
        
        .dropdown-item .icon {
            margin-right: 10px;
            width: 20px;
            display: inline-block;
        }
        
        /* User Section */
        .user-section {
            display: flex;
            align-items: center;
            color: white;
        }
        
        .user-name {
            margin-right: 10px;
        }
        
        .logout-btn {
            background: rgba(255,255,255,0.2);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .logout-btn:hover {
            background: rgba(255,255,255,0.3);
        }
        
        /* Content Area */
        .main-content {
            max-width: 1400px;
            margin: 20px auto;
            padding: 0 20px;
        }
        
        /* Mobile Responsive */
        @media (max-width: 768px) {
            .main-tabs {
                display: none;
            }
            
            .nav-container {
                flex-wrap: wrap;
            }
        }
    </style>
</head>
<body>
    <!-- Unified Navigation -->
    <nav class="top-nav">
        <div class="nav-container">
            <!-- Logo -->
            <div class="logo-section">
                <img src="/quotingfast_logo_white.png" alt="QuotingFast">
                <span class="logo-text">Brain</span>
            </div>
            
            <!-- Main Navigation Tabs -->
            <div class="main-tabs">
                <!-- LEADS Tab -->
                <div class="tab-item {{ request()->is('leads*') || request()->is('admin/leads*') ? 'active' : '' }}">
                    <span>📋 Leads</span>
                    <div class="tab-dropdown">
                        <div class="dropdown-section">
                            <div class="dropdown-title">Management</div>
                            <a href="/leads" class="dropdown-item">
                                <span class="icon">📊</span>All Leads
                            </a>
                            <a href="/admin/lead-queue-monitor" class="dropdown-item">
                                <span class="icon">📈</span>Queue Monitor
                            </a>
                            <a href="/admin/campaigns" class="dropdown-item">
                                <span class="icon">🎯</span>Campaigns
                            </a>
                        </div>
                        <div class="dropdown-section">
                            <div class="dropdown-title">Import & Export</div>
                            <a href="/admin/import-leads" class="dropdown-item">
                                <span class="icon">📥</span>Bulk Import
                            </a>
                            <a href="/admin/export-leads" class="dropdown-item">
                                <span class="icon">📤</span>Export Leads
                            </a>
                        </div>
                        <div class="dropdown-section">
                            <div class="dropdown-title">Analytics</div>
                            <a href="/admin/lead-analytics" class="dropdown-item">
                                <span class="icon">📊</span>Lead Analytics
                            </a>
                            <a href="/admin/conversion-reports" class="dropdown-item">
                                <span class="icon">💹</span>Conversion Reports
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- VICI Tab -->
                <div class="tab-item {{ request()->is('*vici*') ? 'active' : '' }}">
                    <span>☎️ Vici</span>
                    <div class="tab-dropdown">
                        <div class="dropdown-section">
                            <div class="dropdown-title">Reports</div>
                            <a href="/admin/vici-comprehensive-reports" class="dropdown-item">
                                <span class="icon">📊</span>Comprehensive Reports
                            </a>
                            <a href="/admin/vici-reports" class="dropdown-item">
                                <span class="icon">📈</span>Call Reports
                            </a>
                            <a href="/admin/vici-lead-flow" class="dropdown-item">
                                <span class="icon">🔄</span>Lead Flow Monitor
                            </a>
                        </div>
                        <div class="dropdown-section">
                            <div class="dropdown-title">Lead Flow Configuration</div>
                            <a href="/vici/lead-flow" class="dropdown-item">
                                <span class="icon">📋</span>Current Lead Flow
                            </a>
                            <a href="/vici/lead-flow-ab-test" class="dropdown-item" style="background: #fef3c7;">
                                <span class="icon">🔬</span><strong>A/B Test Comparison</strong>
                            </a>
                            <a href="/vici/lead-flow-visual" class="dropdown-item">
                                <span class="icon">🎨</span>Visual Flow Diagram
                            </a>
                        </div>
                        <div class="dropdown-section">
                            <div class="dropdown-title">Management</div>
                            <a href="/admin/vici-sync-management" class="dropdown-item">
                                <span class="icon">🔄</span>Sync Management
                            </a>
                            <a href="/admin/vici-lists" class="dropdown-item">
                                <span class="icon">📋</span>List Management
                            </a>
                            <a href="/admin/vici-campaigns" class="dropdown-item">
                                <span class="icon">🎯</span>Campaign Setup
                            </a>
                        </div>
                        <div class="dropdown-section">
                            <div class="dropdown-title">Compliance</div>
                            <a href="/admin/tcpa-monitor" class="dropdown-item">
                                <span class="icon">⚖️</span>TCPA Monitor
                            </a>
                            <a href="/admin/dnc-management" class="dropdown-item">
                                <span class="icon">🚫</span>DNC Management
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- SMS Tab -->
                <div class="tab-item {{ request()->is('*sms*') || request()->is('*parcelvoy*') ? 'active' : '' }}">
                    <span>💬 SMS</span>
                    <div class="tab-dropdown">
                        <div class="dropdown-section">
                            <div class="dropdown-title">Campaigns</div>
                            <a href="/admin/sms-campaigns" class="dropdown-item">
                                <span class="icon">📱</span>SMS Campaigns
                            </a>
                            <a href="/admin/sms-templates" class="dropdown-item">
                                <span class="icon">📝</span>Templates
                            </a>
                            <a href="/admin/sms-automation" class="dropdown-item">
                                <span class="icon">🤖</span>Automation Rules
                            </a>
                        </div>
                        <div class="dropdown-section">
                            <div class="dropdown-title">Analytics</div>
                            <a href="/admin/sms-reports" class="dropdown-item">
                                <span class="icon">📊</span>SMS Reports
                            </a>
                            <a href="/admin/sms-engagement" class="dropdown-item">
                                <span class="icon">💬</span>Engagement Metrics
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- BUYER PORTAL Tab -->
                <div class="tab-item {{ request()->is('*buyer*') || request()->is('*partner*') ? 'active' : '' }}">
                    <span>🤝 Buyer Portal</span>
                    <div class="tab-dropdown">
                        <div class="dropdown-section">
                            <div class="dropdown-title">Partners</div>
                            <a href="/admin/buyers" class="dropdown-item">
                                <span class="icon">👥</span>Buyer Management
                            </a>
                            <a href="/admin/buyer-performance" class="dropdown-item">
                                <span class="icon">📈</span>Performance Reports
                            </a>
                            <a href="/admin/buyer-payouts" class="dropdown-item">
                                <span class="icon">💰</span>Payouts & Billing
                            </a>
                        </div>
                        <div class="dropdown-section">
                            <div class="dropdown-title">Integration</div>
                            <a href="/admin/allstate-config" class="dropdown-item">
                                <span class="icon">🏢</span>Allstate Setup
                            </a>
                            <a href="/admin/ringba-config" class="dropdown-item">
                                <span class="icon">📞</span>RingBA Config
                            </a>
                            <a href="/admin/api-webhooks" class="dropdown-item">
                                <span class="icon">🔌</span>API & Webhooks
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- ADMIN Tab (if admin user) -->
                @if(auth()->user() && auth()->user()->is_admin)
                <div class="tab-item {{ request()->is('admin/settings*') ? 'active' : '' }}">
                    <span>⚙️ Admin</span>
                    <div class="tab-dropdown">
                        <div class="dropdown-section">
                            <div class="dropdown-title">System</div>
                            <a href="/admin/settings" class="dropdown-item">
                                <span class="icon">⚙️</span>Settings
                            </a>
                            <a href="/admin/users" class="dropdown-item">
                                <span class="icon">👤</span>User Management
                            </a>
                            <a href="/admin/logs" class="dropdown-item">
                                <span class="icon">📜</span>System Logs
                            </a>
                        </div>
                    </div>
                </div>
                @endif
            </div>
            
            <!-- User Section -->
            <div class="user-section">
                <span class="user-name">{{ auth()->user()->name ?? 'Admin' }}</span>
                <button class="logout-btn" onclick="location.href='/logout'">Logout</button>
            </div>
        </div>
    </nav>
    
    <!-- Main Content Area -->
    <div class="main-content">
        @yield('content')
    </div>
    
    <script>
        // Add active state to current tab
        document.addEventListener('DOMContentLoaded', function() {
            const currentPath = window.location.pathname;
            const tabs = document.querySelectorAll('.tab-item');
            
            tabs.forEach(tab => {
                const dropdown = tab.querySelector('.tab-dropdown');
                if (dropdown) {
                    const links = dropdown.querySelectorAll('a');
                    links.forEach(link => {
                        if (link.getAttribute('href') === currentPath) {
                            tab.classList.add('active');
                        }
                    });
                }
            });
        });
    </script>
</body>
</html>




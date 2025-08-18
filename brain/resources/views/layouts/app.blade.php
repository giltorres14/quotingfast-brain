<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'QuotingFast Brain')</title>
    <link rel="icon" type="image/png" href="/favicon.png">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: #f3f4f6;
            min-height: 100vh;
        }
        
        /* Top Navigation Bar */
        .top-nav {
            background: linear-gradient(135deg, #4A90E2 0%, #357ABD 100%);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        
        .top-nav-container {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            padding: 0 20px;
            height: 60px;
        }
        
        .logo {
            display: flex;
            align-items: center;
            margin-right: 40px;
        }
        
        .logo img {
            height: 40px;
            margin-right: 12px;
            filter: brightness(0) invert(1);
        }
        
        .logo-text {
            font-size: 1.5rem;
            font-weight: 600;
            color: white;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .main-nav {
            display: flex;
            flex: 1;
            gap: 5px;
        }
        
        .nav-item {
            padding: 10px 20px;
            color: rgba(255, 255, 255, 0.9);
            text-decoration: none;
            border-radius: 6px;
            font-weight: 500;
            transition: all 0.2s;
            position: relative;
            text-transform: uppercase;
            font-size: 0.875rem;
            letter-spacing: 0.5px;
        }
        
        .nav-item:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }
        
        .nav-item.active {
            background: rgba(255, 255, 255, 0.2);
            color: white;
        }
        
        .nav-item.active::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 10%;
            right: 10%;
            height: 3px;
            background: white;
            border-radius: 3px 3px 0 0;
        }
        
        /* Sub Navigation */
        .sub-nav {
            background: white;
            border-bottom: 1px solid #e5e7eb;
            padding: 10px 0;
            display: none;
        }
        
        .sub-nav.active {
            display: block;
        }
        
        .sub-nav-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            gap: 20px;
        }
        
        .sub-nav-item {
            color: #6b7280;
            text-decoration: none;
            font-size: 0.875rem;
            padding: 5px 10px;
            border-radius: 4px;
            transition: all 0.2s;
        }
        
        .sub-nav-item:hover {
            background: #f3f4f6;
            color: #1f2937;
        }
        
        .sub-nav-item.active {
            background: #4A90E2;
            color: white;
        }
        
        /* Main Content */
        .main-content {
            max-width: 1400px;
            margin: 20px auto;
            padding: 0 20px;
        }
        
        /* Cards */
        .card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .card-header {
            font-size: 1.25rem;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f3f4f6;
        }
        
        /* Buttons */
        .btn {
            padding: 8px 16px;
            border-radius: 6px;
            font-weight: 500;
            text-decoration: none;
            display: inline-block;
            transition: all 0.2s;
            border: none;
            cursor: pointer;
        }
        
        .btn-primary {
            background: #4A90E2;
            color: white;
        }
        
        .btn-primary:hover {
            background: #357ABD;
        }
        
        .btn-secondary {
            background: #e5e7eb;
            color: #4b5563;
        }
        
        .btn-secondary:hover {
            background: #d1d5db;
        }
        
        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: #4A90E2;
        }
        
        .stat-label {
            font-size: 0.875rem;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 5px;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .main-nav {
                flex-wrap: wrap;
            }
            
            .logo-text {
                font-size: 1.25rem;
            }
            
            .nav-item {
                font-size: 0.75rem;
                padding: 8px 12px;
            }
        }
        
        /* Card Styles */
        .card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .card-title {
            font-size: 1.125rem;
            font-weight: 600;
            margin-bottom: 16px;
            color: #1f2937;
        }
        
        .btn {
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 0.875rem;
            font-weight: 500;
            text-decoration: none;
            display: inline-block;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .btn-primary {
            background: #4A90E2;
            color: white;
        }
        
        .btn-primary:hover {
            background: #357ABD;
        }
        
        .btn-success {
            background: #10b981;
            color: white;
        }
        
        .btn-success:hover {
            background: #059669;
        }
        
        .btn-secondary {
            background: #6b7280;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #4b5563;
        }
    </style>
    
    @yield('styles')
</head>
<body>
    <!-- Top Navigation -->
    <nav class="top-nav">
        <div class="top-nav-container">
            <div class="logo">
                <img src="https://quotingfast.com/whitelogo" alt="QuotingFast" style="height: 40px; width: auto;">
                <span class="logo-text">The Brain</span>
            </div>
            
            <div class="main-nav">
                <a href="/leads" class="nav-item {{ request()->is('leads*') ? 'active' : '' }}">Leads</a>
                <a href="/vici" class="nav-item {{ request()->is('vici*') ? 'active' : '' }}">Vici</a>
                <a href="/sms" class="nav-item {{ request()->is('sms*') ? 'active' : '' }}">SMS</a>
                <a href="/buyers" class="nav-item {{ request()->is('buyers*') ? 'active' : '' }}">Buyer Portal</a>
                <a href="/admin" class="nav-item {{ request()->is('admin*') ? 'active' : '' }}">Admin</a>
            </div>
        </div>
    </nav>
    
    <!-- Sub Navigation for Leads -->
    @if(request()->is('leads*') || request()->is('admin/vendor-management*') || request()->is('campaigns/directory*'))
    <div class="sub-nav active">
        <div class="sub-nav-container">
            <a href="/leads" class="sub-nav-item {{ request()->is('leads') ? 'active' : '' }}">Dashboard</a>
            <a href="/admin/lead-queue-monitor" class="sub-nav-item {{ request()->is('admin/lead-queue-monitor') ? 'active' : '' }}">Stuck in Queue</a>
            <a href="/leads/import" class="sub-nav-item {{ request()->is('leads/import') ? 'active' : '' }}">Import</a>
            <a href="/admin/vendor-management" class="sub-nav-item {{ request()->is('admin/vendor-management') ? 'active' : '' }}">Sources/Vendors</a>
            <a href="/campaigns/directory" class="sub-nav-item {{ request()->is('campaigns/directory') ? 'active' : '' }}">Campaigns</a>
            <a href="/leads/reports" class="sub-nav-item {{ request()->is('leads/reports') ? 'active' : '' }}">Reports</a>
        </div>
    </div>
    @endif
    
    <!-- Sub Navigation for Vici -->
    @if(request()->is('vici*'))
    <div class="sub-nav active">
        <div class="sub-nav-container">
            <a href="/vici" class="sub-nav-item {{ request()->is('vici') ? 'active' : '' }}">Dashboard</a>
            <a href="/vici/reports" class="sub-nav-item {{ request()->is('vici/reports') ? 'active' : '' }}">Reports</a>
            <a href="/vici/lead-flow" class="sub-nav-item {{ request()->is('vici/lead-flow') ? 'active' : '' }}">Lead Flow</a>
            <a href="/vici/sync-status" class="sub-nav-item {{ request()->is('vici/sync-status') ? 'active' : '' }}">Sync Status</a>
            <a href="/vici/settings" class="sub-nav-item {{ request()->is('vici/settings') ? 'active' : '' }}">Settings</a>
        </div>
    </div>
    @endif
    
    <!-- Sub Navigation for SMS -->
    @if(request()->is('sms*'))
    <div class="sub-nav active">
        <div class="sub-nav-container">
            <a href="/sms" class="sub-nav-item {{ request()->is('sms') ? 'active' : '' }}">Dashboard</a>
            <a href="/sms/campaigns" class="sub-nav-item {{ request()->is('sms/campaigns') ? 'active' : '' }}">Campaigns</a>
            <a href="/sms/templates" class="sub-nav-item {{ request()->is('sms/templates') ? 'active' : '' }}">Templates</a>
            <a href="/sms/analytics" class="sub-nav-item {{ request()->is('sms/analytics') ? 'active' : '' }}">Analytics</a>
        </div>
    </div>
    @endif
    
    <!-- Sub Navigation for Buyers -->
    @if(request()->is('buyers*'))
    <div class="sub-nav active">
        <div class="sub-nav-container">
            <a href="/buyers" class="sub-nav-item {{ request()->is('buyers') ? 'active' : '' }}">Dashboard</a>
            <a href="/buyers/directory" class="sub-nav-item {{ request()->is('buyers/directory') ? 'active' : '' }}">Directory</a>
            <a href="/buyers/transfers" class="sub-nav-item {{ request()->is('buyers/transfers') ? 'active' : '' }}">Transfers</a>
            <a href="/buyers/revenue" class="sub-nav-item {{ request()->is('buyers/revenue') ? 'active' : '' }}">Revenue</a>
        </div>
    </div>
    @endif
    
    <!-- Sub Navigation for Admin -->
    @if(request()->is('admin*') && !request()->is('admin/vendor-management*'))
    <div class="sub-nav active">
        <div class="sub-nav-container">
            <a href="/admin" class="sub-nav-item {{ request()->is('admin') ? 'active' : '' }}">Dashboard</a>
            <a href="/admin/simple-dashboard" class="sub-nav-item {{ request()->is('admin/simple-dashboard') ? 'active' : '' }}">Simple Dashboard</a>
            <a href="/admin/control-center" class="sub-nav-item {{ request()->is('admin/control-center') ? 'active' : '' }}">Control Center</a>
            <a href="/admin/lead-queue-monitor" class="sub-nav-item {{ request()->is('admin/lead-queue-monitor') ? 'active' : '' }}">Lead Queue Monitor</a>
            <a href="/admin/buyer-management" class="sub-nav-item {{ request()->is('admin/buyer-management') ? 'active' : '' }}">Buyer Management</a>
        </div>
    </div>
    @endif
    
    <!-- Main Content -->
    <div class="main-content">
        @yield('content')
    </div>
    
    @yield('scripts')
</body>
</html>
            margin-right: 12px;
            filter: brightness(0) invert(1);
        }
        
        .logo-text {
            font-size: 1.5rem;
            font-weight: 600;
            color: white;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .main-nav {
            display: flex;
            flex: 1;
            gap: 5px;
        }
        
        .nav-item {
            padding: 10px 20px;
            color: rgba(255, 255, 255, 0.9);
            text-decoration: none;
            border-radius: 6px;
            font-weight: 500;
            transition: all 0.2s;
            position: relative;
            text-transform: uppercase;
            font-size: 0.875rem;
            letter-spacing: 0.5px;
        }
        
        .nav-item:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }
        
        .nav-item.active {
            background: rgba(255, 255, 255, 0.2);
            color: white;
        }
        
        .nav-item.active::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 10%;
            right: 10%;
            height: 3px;
            background: white;
            border-radius: 3px 3px 0 0;
        }
        
        /* Sub Navigation */
        .sub-nav {
            background: white;
            border-bottom: 1px solid #e5e7eb;
            padding: 10px 0;
            display: none;
        }
        
        .sub-nav.active {
            display: block;
        }
        
        .sub-nav-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            gap: 20px;
        }
        
        .sub-nav-item {
            color: #6b7280;
            text-decoration: none;
            font-size: 0.875rem;
            padding: 5px 10px;
            border-radius: 4px;
            transition: all 0.2s;
        }
        
        .sub-nav-item:hover {
            background: #f3f4f6;
            color: #1f2937;
        }
        
        .sub-nav-item.active {
            background: #4A90E2;
            color: white;
        }
        
        /* Main Content */
        .main-content {
            max-width: 1400px;
            margin: 20px auto;
            padding: 0 20px;
        }
        
        /* Cards */
        .card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .card-header {
            font-size: 1.25rem;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f3f4f6;
        }
        
        /* Buttons */
        .btn {
            padding: 8px 16px;
            border-radius: 6px;
            font-weight: 500;
            text-decoration: none;
            display: inline-block;
            transition: all 0.2s;
            border: none;
            cursor: pointer;
        }
        
        .btn-primary {
            background: #4A90E2;
            color: white;
        }
        
        .btn-primary:hover {
            background: #357ABD;
        }
        
        .btn-secondary {
            background: #e5e7eb;
            color: #4b5563;
        }
        
        .btn-secondary:hover {
            background: #d1d5db;
        }
        
        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: #4A90E2;
        }
        
        .stat-label {
            font-size: 0.875rem;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 5px;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .main-nav {
                flex-wrap: wrap;
            }
            
            .logo-text {
                font-size: 1.25rem;
            }
            
            .nav-item {
                font-size: 0.75rem;
                padding: 8px 12px;
            }
        }
        
        /* Card Styles */
        .card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .card-title {
            font-size: 1.125rem;
            font-weight: 600;
            margin-bottom: 16px;
            color: #1f2937;
        }
        
        .btn {
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 0.875rem;
            font-weight: 500;
            text-decoration: none;
            display: inline-block;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .btn-primary {
            background: #4A90E2;
            color: white;
        }
        
        .btn-primary:hover {
            background: #357ABD;
        }
        
        .btn-success {
            background: #10b981;
            color: white;
        }
        
        .btn-success:hover {
            background: #059669;
        }
        
        .btn-secondary {
            background: #6b7280;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #4b5563;
        }
    </style>
    
    @yield('styles')
</head>
<body>
    <!-- Top Navigation -->
    <nav class="top-nav">
        <div class="top-nav-container">
            <div class="logo">
                <img src="https://quotingfast.com/whitelogo" alt="QuotingFast" style="height: 40px; width: auto;">
                <span class="logo-text">The Brain</span>
            </div>
            
            <div class="main-nav">
                <a href="/leads" class="nav-item {{ request()->is('leads*') ? 'active' : '' }}">Leads</a>
                <a href="/vici" class="nav-item {{ request()->is('vici*') ? 'active' : '' }}">Vici</a>
                <a href="/sms" class="nav-item {{ request()->is('sms*') ? 'active' : '' }}">SMS</a>
                <a href="/buyers" class="nav-item {{ request()->is('buyers*') ? 'active' : '' }}">Buyer Portal</a>
                <a href="/admin" class="nav-item {{ request()->is('admin*') ? 'active' : '' }}">Admin</a>
            </div>
        </div>
    </nav>
    
    
    
    @yield('scripts')
</body>
</html>
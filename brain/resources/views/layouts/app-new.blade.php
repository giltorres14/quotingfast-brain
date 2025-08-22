<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'QuotingFast Brain')</title>
    <link rel="icon" type="image/png" href="/favicon.png">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
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
            background: white;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
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
            height: 35px;
            margin-right: 10px;
        }
        
        .logo-text {
            font-size: 1.5rem;
            font-weight: 600;
            color: #1f2937;
        }
        
        .main-nav {
            display: flex;
            gap: 30px;
            flex: 1;
        }
        
        .nav-item {
            color: #6b7280;
            text-decoration: none;
            font-weight: 500;
            padding: 8px 12px;
            border-radius: 6px;
            transition: all 0.2s;
        }
        
        .nav-item:hover {
            background: #f3f4f6;
            color: #1f2937;
        }
        
        .nav-item.active {
            background: #4A90E2;
            color: white;
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
</head>
<body>
    <!-- Top Navigation -->
    <nav class="top-nav">
        <div class="top-nav-container">
            <div class="logo">
                <img src="https://quotingfast.com/qfqflogo.png" alt="QuotingFast Logo">
                <span class="logo-text">THE BRAIN</span>
            </div>
            <div class="main-nav">
                <a href="/leads" class="nav-item {{ request()->is('leads*') ? 'active' : '' }}">LEADS</a>
                <a href="/vici" class="nav-item {{ request()->is('vici*') ? 'active' : '' }}">VICI</a>
                <a href="/sms" class="nav-item {{ request()->is('sms*') ? 'active' : '' }}">SMS</a>
                <a href="/buyer-portal" class="nav-item {{ request()->is('buyer-portal*') ? 'active' : '' }}">BUYER PORTAL</a>
                <a href="/admin" class="nav-item {{ request()->is('admin*') ? 'active' : '' }}">ADMIN</a>
            </div>
        </div>
    </nav>
    
    <!-- Sub Navigation (if section exists) -->
    @if(View::hasSection('subnav'))
        <div class="sub-nav active">
            <div class="sub-nav-container">
                <div class="sub-nav-items">
                    @yield('subnav')
                </div>
            </div>
        </div>
    @endif
    
    <!-- Main Content -->
    <div class="main-content">
        @if(View::hasSection('page-header'))
            <div class="page-header">
                @yield('page-header')
            </div>
        @endif
        
        @yield('content')
    </div>
    
    @stack('scripts')
</body>
</html>





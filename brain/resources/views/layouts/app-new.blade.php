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
            font-size: 1.25rem;
            font-weight: 600;
            color: #1f2937;
        }
        
        .main-nav {
            display: flex;
            flex: 1;
            gap: 5px;
        }
        
        .nav-item {
            padding: 8px 16px;
            color: #4b5563;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 500;
            transition: all 0.2s;
            position: relative;
        }
        
        .nav-item:hover {
            background: #f3f4f6;
            color: #1f2937;
        }
        
        .nav-item.active {
            color: white;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        /* Section-specific colors */
        .nav-item.leads.active { background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); }
        .nav-item.vici.active { background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); }
        .nav-item.sms.active { background: linear-gradient(135deg, #10b981 0%, #059669 100%); }
        .nav-item.buyers.active { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); }
        .nav-item.admin.active { background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%); }
        
        /* Sub Navigation */
        .sub-nav {
            background: white;
            border-bottom: 1px solid #e5e7eb;
            padding: 0;
        }
        
        .sub-nav-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .sub-nav-items {
            display: flex;
            gap: 20px;
            height: 48px;
            align-items: center;
        }
        
        .sub-nav-item {
            color: #6b7280;
            text-decoration: none;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 0.875rem;
            font-weight: 500;
            transition: all 0.2s;
        }
        
        .sub-nav-item:hover {
            background: #f3f4f6;
            color: #1f2937;
        }
        
        .sub-nav-item.active {
            color: #4f46e5;
            background: #eef2ff;
        }
        
        /* Content Area */
        .main-content {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }
        
        /* Page Header */
        .page-header {
            background: white;
            border-radius: 12px;
            padding: 20px 24px;
            margin-bottom: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        
        .page-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 4px;
        }
        
        .breadcrumbs {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.875rem;
            color: #6b7280;
        }
        
        .breadcrumb-separator {
            color: #9ca3af;
        }
        
        /* Cards */
        .card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }
        
        .card-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 16px;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .main-nav {
                overflow-x: auto;
                scrollbar-width: none;
            }
            
            .main-nav::-webkit-scrollbar {
                display: none;
            }
            
            .logo-text {
                display: none;
            }
        }
        
        /* Loading State */
        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid #f3f4f6;
            border-radius: 50%;
            border-top-color: #3b82f6;
            animation: spin 1s ease-in-out infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        /* Utility Classes */
        .text-success { color: #10b981; }
        .text-warning { color: #f59e0b; }
        .text-danger { color: #ef4444; }
        .text-info { color: #3b82f6; }
        
        .bg-success { background: #d1fae5; }
        .bg-warning { background: #fed7aa; }
        .bg-danger { background: #fee2e2; }
        .bg-info { background: #dbeafe; }
        
        .btn {
            padding: 8px 16px;
            border-radius: 6px;
            font-weight: 500;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-primary {
            background: #3b82f6;
            color: white;
        }
        
        .btn-primary:hover {
            background: #2563eb;
        }
        
        .btn-secondary {
            background: #6b7280;
            color: white;
        }
        
        .btn-success {
            background: #10b981;
            color: white;
        }
        
        .btn-danger {
            background: #ef4444;
            color: white;
        }
    </style>
    
    @stack('styles')
</head>
<body>
    <!-- Top Navigation -->
    <nav class="top-nav">
        <div class="top-nav-container">
            <div class="logo">
                <img src="/quotingfast-logo.png" alt="QuotingFast">
                <span class="logo-text">Brain</span>
            </div>
            
            <div class="main-nav">
                <a href="/leads" class="nav-item leads {{ request()->is('leads*') ? 'active' : '' }}">
                    📊 LEADS
                </a>
                <a href="/vici" class="nav-item vici {{ request()->is('vici*') ? 'active' : '' }}">
                    📞 VICI
                </a>
                <a href="/sms" class="nav-item sms {{ request()->is('sms*') ? 'active' : '' }}">
                    💬 SMS
                </a>
                <a href="/buyers" class="nav-item buyers {{ request()->is('buyers*') ? 'active' : '' }}">
                    🤝 BUYER PORTAL
                </a>
                <a href="/admin" class="nav-item admin {{ request()->is('admin*') ? 'active' : '' }}">
                    ⚙️ ADMIN
                </a>
            </div>
        </div>
    </nav>
    
    <!-- Sub Navigation -->
    @if(View::hasSection('subnav'))
        <div class="sub-nav">
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

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Management') - Brain</title>
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
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            min-height: 100vh;
            color: #333;
        }
        
        .header {
            overflow-x: hidden;
            width: 100%;
            max-width: 100%;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 1rem 2rem;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
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
            height: 100px;
            width: auto;
        }
        
        .title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #1e40af;
        }
        
        .brand-text {
            font-family: 'Orbitron', sans-serif;
            font-size: 1.2rem;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #2563eb;
        }
        
        .nav-links {
            display: flex;
            gap: 1rem;
            align-items: center;
        }
        
        .nav-link {
            padding: 0.5rem 1rem;
            text-decoration: none;
            color: #4a5568;
            border-radius: 0.5rem;
            transition: all 0.3s ease;
        }
        
        .nav-link:hover {
            background: #edf2f7;
            color: #1e40af;
        }
        
        .nav-link.back-btn {
            background: #f7fafc;
            border: 1px solid #e2e8f0;
            font-weight: 500;
        }
        
        .nav-link.back-btn:hover {
            background: #edf2f7;
            border-color: #cbd5e0;
        }
        
        /* Management Dropdown */
        .dropdown {
            position: relative;
            display: inline-block;
        }
        
        .dropdown-toggle {
            padding: 0.5rem 1rem;
            background: #f7fafc;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            cursor: pointer;
            color: #4a5568;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
        }
        
        .dropdown-toggle:hover {
            background: #edf2f7;
            border-color: #cbd5e0;
        }
        
        .dropdown-menu {
            display: none;
            position: absolute;
            right: 0;
            top: 100%;
            margin-top: 0.5rem;
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            min-width: 200px;
            z-index: 1000;
        }
        
        .dropdown-menu.show {
            display: block;
        }
        
        .dropdown-item {
            display: block;
            padding: 0.75rem 1rem;
            color: #4a5568;
            text-decoration: none;
            transition: all 0.3s ease;
            border-bottom: 1px solid #f7fafc;
        }
        
        .dropdown-item:last-child {
            border-bottom: none;
        }
        
        .dropdown-item:hover {
            background: #f7fafc;
            color: #1e40af;
        }
        
        .dropdown-item.active {
            background: #edf2f7;
            color: #1e40af;
            font-weight: 500;
        }
        
        .container {
            max-width: 1400px;
            margin: 2rem auto;
            padding: 0 2rem;
        }
        
        @yield('styles')
    </style>
</head>
<body>
    <div class="header">
        <div class="logo-section">
            <img src="https://quotingfast.com/whitelogo" alt="QuotingFast" class="logo" 
                 onerror="this.src='https://quotingfast.com/whitelogo'; this.onerror=null;">
            <div style="display: flex; flex-direction: column; gap: 2px;">
                <div class="logo-text" style="display: flex; flex-direction: column; align-items: center; line-height: 1;">
                    <div style="font-family: 'Orbitron', sans-serif; font-size: 0.9rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px;">The</div>
                    <div class="brand-text" style="font-family: 'Orbitron', sans-serif; font-size: 1.4rem; font-weight: 900; text-transform: uppercase; letter-spacing: 1px;">Brain</div>
                </div>
            </div>
            <div class="title">@yield('page-title', 'Management')</div>
        </div>
        <div class="nav-links">
            <a href="/admin" class="nav-link back-btn">← Admin Dashboard</a>
            <a href="/leads" class="nav-link">Leads</a>
            
            <!-- Management Dropdown -->
            <div class="dropdown">
                <a href="#" class="dropdown-toggle" onclick="toggleDropdown(event)">
                    ⚙️ Management ▼
                </a>
                <div class="dropdown-menu" id="managementDropdown">
                    <a href="/admin/control-center" class="dropdown-item {{ request()->is('admin/control-center') ? 'active' : '' }}">
                        🧠 Control Center
                    </a>
                    <a href="/admin/allstate-testing" class="dropdown-item {{ request()->is('admin/allstate-testing') ? 'active' : '' }}">
                        🧪 Allstate Testing
                    </a>
                    <a href="/admin/lead-queue" class="dropdown-item {{ request()->is('admin/lead-queue') ? 'active' : '' }}">
                        🛡️ Lead Queue Monitor
                    </a>
                    <a href="/api-directory" class="dropdown-item {{ request()->is('api-directory') ? 'active' : '' }}">
                        🔗 API Directory
                    </a>
                    <a href="/admin/campaigns" class="dropdown-item {{ request()->is('admin/campaigns') ? 'active' : '' }}">
                        📊 Campaigns
                    </a>
                    <a href="/admin/buyer-management" class="dropdown-item {{ request()->is('admin/buyer-management') ? 'active' : '' }}">
                        👥 Buyer Management
                    </a>
                    <a href="/admin/integrations" class="dropdown-item {{ request()->is('admin/integrations') ? 'active' : '' }}">
                        🔌 Integrations
                    </a>
                </div>
            </div>
            
            @yield('nav-actions')
        </div>
    </div>

    <div class="container">
        @yield('content')
    </div>

    <script>
        // Dropdown menu functionality
        function toggleDropdown(event) {
            event.preventDefault();
            event.stopPropagation();
            const dropdown = document.getElementById('managementDropdown');
            dropdown.classList.toggle('show');
        }
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const dropdown = document.getElementById('managementDropdown');
            const dropdownToggle = event.target.closest('.dropdown-toggle');
            
            if (!dropdownToggle && dropdown.classList.contains('show')) {
                dropdown.classList.remove('show');
            }
        });
        
        // Keep dropdown items in consistent order
        const menuOrder = [
            'Allstate Testing',
            'Lead Queue Monitor', 
            'API Directory',
            'Campaigns',
            'Buyer Management',
            'Integrations'
        ];
    </script>
    
    @yield('scripts')
</body>
</html>

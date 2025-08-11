<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'The Brain' }} - QuotingFast</title>
    <link rel="icon" type="image/png" href="/favicon.png">
    <link rel="shortcut icon" href="/favicon.ico">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&display=swap" rel="stylesheet">
    
    <style>
        /* Global Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: #333;
        }
        
        /* Header Styles */
        .awesome-header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
            transition: all 0.3s ease;
        }
        
        .header-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 70px;
        }
        
        /* Logo Section */
        .logo-section {
            display: flex;
            align-items: center;
            gap: 1rem;
            text-decoration: none;
            color: inherit;
            transition: transform 0.2s ease;
        }
        
        .logo-section:hover {
            transform: scale(1.02);
            text-decoration: none;
            color: inherit;
        }
        
        .logo-image {
            height: 50px; /* 3x bigger from 45px */
            width: auto;
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));
        }
        
        .logo-text {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }
        
        .logo-brand {
            font-family: 'Orbitron', sans-serif;
            font-size: 1.8rem;
            font-weight: 900;
            color: #2d3748;
            line-height: 1;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        
        .logo-subtitle {
            font-size: 0.85rem;
            color: #667eea;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            line-height: 1;
        }
        
        /* Navigation */
        .nav-menu {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            list-style: none;
        }
        
        .nav-item {
            position: relative;
        }
        
        .nav-link {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.25rem;
            color: #4a5568;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.95rem;
            border-radius: 10px;
            transition: all 0.2s ease;
            position: relative;
            white-space: nowrap;
        }
        
        .nav-link:hover {
            background: rgba(102, 126, 234, 0.1);
            color: #667eea;
            text-decoration: none;
            transform: translateY(-1px);
        }
        
        .nav-link.active {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }
        
        .nav-link.active:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
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
            color: currentColor;
        }
        
        .dropdown.open .dropdown-toggle::after {
            transform: rotate(180deg);
        }
        
        .dropdown-menu {
            position: absolute;
            top: 100%;
            left: 0;
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
            border: 1px solid rgba(0, 0, 0, 0.05);
            min-width: 220px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s ease;
            z-index: 1000;
            overflow: hidden;
        }
        
        .dropdown.open .dropdown-menu {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        
        .dropdown-item {
            display: block;
            padding: 0.875rem 1.25rem;
            color: #4a5568;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.9rem;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .dropdown-item:last-child {
            border-bottom: none;
        }
        
        .dropdown-item:hover {
            background: rgba(102, 126, 234, 0.08);
            color: #667eea;
            text-decoration: none;
            padding-left: 1.5rem;
        }
        
        .dropdown-item.active {
            background: rgba(102, 126, 234, 0.1);
            color: #667eea;
            font-weight: 600;
        }
        
        /* User Section */
        .user-section {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.5rem 1rem;
            background: rgba(102, 126, 234, 0.1);
            border-radius: 25px;
            color: #667eea;
            font-weight: 600;
            font-size: 0.9rem;
        }
        
        .user-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea, #764ba2);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 0.8rem;
        }
        
        /* Quick Actions */
        .quick-actions {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .quick-action-btn {
            padding: 0.5rem;
            background: rgba(102, 126, 234, 0.1);
            border: none;
            border-radius: 8px;
            color: #667eea;
            cursor: pointer;
            transition: all 0.2s ease;
            font-size: 1.1rem;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .quick-action-btn:hover {
            background: rgba(102, 126, 234, 0.2);
            transform: translateY(-1px);
        }
        
        /* Page Content Wrapper */
        .page-content {
            min-height: calc(100vh - 70px);
            padding: 2rem 0;
        }
        
        /* Mobile Responsiveness */
        @media (max-width: 1024px) {
            .header-container {
                padding: 0 1rem;
            }
            
            .nav-menu {
                gap: 0.25rem;
            }
            
            .nav-link {
                padding: 0.6rem 1rem;
                font-size: 0.9rem;
            }
        }
        
        @media (max-width: 768px) {
            .header-container {
                height: 60px;
            }
            
            .logo-image {
                height: 35px;
            }
            
            .logo-brand {
                font-size: 1rem;
            }
            
            .logo-subtitle {
                font-size: 0.75rem;
            }
            
            .nav-menu {
                display: none;
            }
            
            .mobile-menu-toggle {
                display: block;
                background: none;
                border: none;
                font-size: 1.5rem;
                color: #4a5568;
                cursor: pointer;
            }
        }
    </style>
</head>

<body>
    <header class="awesome-header">
        <div class="header-container">
            <!-- Logo Section -->
            <a href="/admin" class="logo-section">
                <img src="https://quotingfast.com/whitelogo" alt="QuotingFast" class="logo-image" style="height: 50px; width:auto;" onerror="this.src='https://quotingfast.com/whitelogo'; this.onerror=null;">
                <div class="logo-text"><div class="brand-text">The Brain</div></div>
            </a>
            
            <!-- Navigation Menu -->
            <nav class="nav-menu">
                <!-- Dashboard -->
                <li class="nav-item">
                    <a href="/admin" class="nav-link {{ request()->is('admin') ? 'active' : '' }}">
                        📊 Dashboard
                    </a>
                </li>
                
                <!-- Leads Dropdown -->
                <li class="nav-item dropdown" id="leadsDropdown">
                    <a href="#" class="nav-link dropdown-toggle {{ request()->is('leads*') || request()->is('lead-*') ? 'active' : '' }}">
                        👥 Leads
                    </a>
                    <div class="dropdown-menu">
                        <a href="/leads" class="dropdown-item {{ request()->is('leads') && !request()->is('leads/*') ? 'active' : '' }}">
                            📋 View All Leads
                        </a>
                        <a href="/lead-upload" class="dropdown-item {{ request()->is('lead-upload*') ? 'active' : '' }}">
                            📁 Upload CSV
                        </a>
                        <a href="#" class="dropdown-item" onclick="alert('Lead Types management coming soon!')">
                            🏷️ Lead Types
                        </a>
                    </div>
                </li>
                
                <!-- Management Dropdown -->
                <li class="nav-item dropdown" id="managementDropdown">
                    <a href="#" class="nav-link dropdown-toggle {{ request()->is('api-directory') || request()->is('campaign-directory') ? 'active' : '' }}">
                        ⚙️ Management
                    </a>
                    <div class="dropdown-menu">
                        <a href="/api-directory" class="dropdown-item {{ request()->is('api-directory*') ? 'active' : '' }}">
                            🔗 API Directory
                        </a>
                        <a href="/campaign-directory" class="dropdown-item {{ request()->is('campaign-directory*') ? 'active' : '' }}">
                            📊 Campaigns
                        </a>
                        <a href="#" class="dropdown-item" onclick="alert('Buyer Portal coming soon!')">
                            👤 Buyer Portal
                        </a>
                        <a href="#" class="dropdown-item" onclick="alert('Integrations management coming soon!')">
                            🔌 Integrations
                        </a>
                    </div>
                </li>
                
                <!-- Analytics -->
                <li class="nav-item">
                    <a href="/analytics" class="nav-link {{ request()->is('analytics*') ? 'active' : '' }}" onclick="alert('Analytics dashboard coming soon!')">
                        📈 Analytics
                    </a>
                </li>
                
                <!-- Communications -->
                <li class="nav-item dropdown" id="communicationsDropdown">
                    <a href="#" class="nav-link dropdown-toggle">
                        💬 Communications
                    </a>
                    <div class="dropdown-menu">
                        <a href="#" class="dropdown-item" onclick="alert('SMS/Messaging feature coming soon!')">
                            📱 SMS Center
                        </a>
                        <a href="#" class="dropdown-item" onclick="alert('Email campaigns coming soon!')">
                            📧 Email Campaigns
                        </a>
                        <a href="#" class="dropdown-item" onclick="alert('Call tracking coming soon!')">
                            📞 Call Tracking
                        </a>
                    </div>
                </li>
                
                <!-- Settings -->
                <li class="nav-item dropdown" id="settingsDropdown">
                    <a href="#" class="nav-link dropdown-toggle">
                        🔧 Settings
                    </a>
                    <div class="dropdown-menu">
                        <a href="#" class="dropdown-item" onclick="alert('User management coming soon!')">
                            👥 Users
                        </a>
                        <a href="#" class="dropdown-item" onclick="alert('System settings coming soon!')">
                            ⚙️ System
                        </a>
                        <a href="#" class="dropdown-item" onclick="alert('API keys management coming soon!')">
                            🔑 API Keys
                        </a>
                        <a href="#" class="dropdown-item" onclick="alert('Backup settings coming soon!')">
                            💾 Backup
                        </a>
                    </div>
                </li>
            </nav>
            
            <!-- User Section -->
            <div class="user-section">
                <div class="quick-actions">
                    <button class="quick-action-btn" title="System Status" onclick="alert('All systems operational!')">
                        🟢
                    </button>
                    <button class="quick-action-btn" title="Notifications" onclick="alert('No new notifications')">
                        🔔
                    </button>
                </div>
                
                <div class="user-info">
                    <div class="user-avatar">A</div>
                    <span>Admin</span>
                </div>
            </div>
            
            <!-- Mobile Menu Toggle (hidden on desktop) -->
            <button class="mobile-menu-toggle" style="display: none;">
                ☰
            </button>
        </div>
    </header>

    <script>
        // Dropdown functionality
        document.addEventListener('DOMContentLoaded', function() {
            const dropdowns = document.querySelectorAll('.dropdown');
            
            dropdowns.forEach(dropdown => {
                const toggle = dropdown.querySelector('.dropdown-toggle');
                const menu = dropdown.querySelector('.dropdown-menu');
                
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
        
        // Header scroll effect
        let lastScrollTop = 0;
        const header = document.querySelector('.awesome-header');
        
        window.addEventListener('scroll', function() {
            let scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            
            if (scrollTop > lastScrollTop && scrollTop > 100) {
                // Scrolling down
                header.style.transform = 'translateY(-100%)';
            } else {
                // Scrolling up
                header.style.transform = 'translateY(0)';
            }
            
            lastScrollTop = scrollTop <= 0 ? 0 : scrollTop;
        });
    </script>
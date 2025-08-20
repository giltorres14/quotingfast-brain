@extends('layouts.app')

@section('title', 'The Brain - Admin Dashboard')

@section('styles')
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&display=swap" rel="stylesheet">
<style>
    /* Dashboard specific styles */
    .dashboard-header {
        text-align: center;
        padding: 30px 0;
        margin-bottom: 30px;
    }
    
    .dashboard-title {
        font-family: 'Orbitron', monospace;
        font-size: 2.5rem;
        font-weight: 900;
        color: #1f2937;
        text-transform: uppercase;
        letter-spacing: 2px;
    }
    
    .dashboard-subtitle {
        color: #6b7280;
        margin-top: 10px;
    }
    
    /* Stats Cards */
    .stats-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    
    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
    }
    
    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.12);
    }
    
    .stat-value {
        font-size: 2.5rem;
        font-weight: 700;
        color: #4A90E2;
        margin-bottom: 5px;
    }
    
    .stat-label {
        font-size: 0.875rem;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .stat-change {
        margin-top: 10px;
        font-size: 0.875rem;
    }
    
    .stat-up {
        color: #10b981;
    }
    
    .stat-down {
        color: #ef4444;
    }
    
    /* Feature Cards */
    .feature-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
        gap: 25px;
        margin-bottom: 30px;
    }
    
    .feature-card {
        background: white;
        border-radius: 12px;
        padding: 30px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }
    
    .feature-icon {
        font-size: 3rem;
        margin-bottom: 15px;
    }
    
    .feature-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 10px;
    }
    
    .feature-description {
        color: #6b7280;
        line-height: 1.6;
        margin-bottom: 20px;
    }
    
    /* SMS Stats */
    .sms-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
        gap: 15px;
        margin-top: 20px;
    }
    
    .sms-stat {
        text-align: center;
        padding: 15px;
        background: #f9fafb;
        border-radius: 8px;
    }
    
    .sms-stat-value {
        font-size: 1.5rem;
        font-weight: 700;
        color: #4A90E2;
    }
    
    .sms-stat-label {
        font-size: 0.75rem;
        color: #6b7280;
        text-transform: uppercase;
        margin-top: 5px;
    }
    
    /* Agent Card */
    .agent-card {
        background: linear-gradient(135deg, #4A90E2 0%, #357ABD 100%);
        color: white;
        border-radius: 12px;
        padding: 25px;
        text-align: center;
    }
    
    .agent-name {
        font-size: 1.5rem;
        font-weight: 700;
        margin-bottom: 5px;
    }
    
    .agent-title {
        opacity: 0.9;
        margin-bottom: 15px;
    }
    
    .agent-stat {
        font-size: 2rem;
        font-weight: 700;
    }
    
    /* Action Buttons */
    .action-btn {
        display: inline-block;
        padding: 12px 24px;
        background: #4A90E2;
        color: white;
        text-decoration: none;
        border-radius: 8px;
        font-weight: 500;
        transition: all 0.3s ease;
    }
    
    .action-btn:hover {
        background: #357ABD;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(74, 144, 226, 0.3);
        color: white;
        text-decoration: none;
    }
    
    .action-btn-secondary {
        background: #e5e7eb;
        color: #4b5563;
    }
    
    .action-btn-secondary:hover {
        background: #d1d5db;
        color: #1f2937;
    }
    
    /* Quick Links */
    .quick-links {
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
        margin-top: 20px;
    }
    
    .quick-link {
        padding: 10px 20px;
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        text-decoration: none;
        color: #4b5563;
        font-size: 0.875rem;
        transition: all 0.2s;
    }
    
    .quick-link:hover {
        background: #4A90E2;
        color: white;
        border-color: #4A90E2;
        text-decoration: none;
    }
</style>
@endsection

@section('content')
<div class="dashboard-header">
    <h1 class="dashboard-title">Auto Insurance Leads Management System</h1>
    <p class="dashboard-subtitle">Real-time Lead Tracking & Analytics Dashboard</p>
</div>

<!-- Main Stats Row -->
<div class="stats-row">
    <div class="stat-card">
        <div class="stat-value">{{ number_format($stats['total_leads'] ?? 0) }}</div>
        <div class="stat-label">Total Leads</div>
        <div class="stat-change stat-up">↑ {{ $stats['leads_today'] ?? 0 }} today</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-value">{{ number_format($stats['new_leads'] ?? 0) }}</div>
        <div class="stat-label">New Leads</div>
        <div class="stat-change">Last 24 hours</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-value">{{ number_format($stats['contacted'] ?? 0) }}</div>
        <div class="stat-label">Contacted</div>
        <div class="stat-change">Via Vici Dialer</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-value">{{ number_format($stats['converted'] ?? 0) }}</div>
        <div class="stat-label">Converted</div>
        <div class="stat-change stat-up">{{ $stats['conversion_rate'] ?? '0' }}% rate</div>
    </div>
</div>

<!-- Feature Cards -->
<div class="feature-grid">
    <!-- Lead Management -->
    <div class="feature-card">
        <div class="feature-icon">👥</div>
        <h3 class="feature-title">Lead Management</h3>
        <p class="feature-description">
            Comprehensive lead database with advanced search, filtering, and bulk operations. 
            Track lead sources, status, and conversion metrics.
        </p>
        <div class="quick-links">
            <a href="/leads" class="quick-link">View All Leads</a>
            <a href="/leads/queue" class="quick-link">Lead Queue</a>
            <a href="/leads/import" class="quick-link">Import Leads</a>
        </div>
    </div>
    
    <!-- SMS Campaigns -->
    <div class="feature-card">
        <div class="feature-icon">💬</div>
        <h3 class="feature-title">SMS Campaigns</h3>
        <p class="feature-description">
            Send, schedule, and track SMS campaigns. Automated follow-ups, templates, 
            and compliance management for all messaging.
        </p>
        <div class="sms-stats">
            <div class="sms-stat">
                <div class="sms-stat-value">{{ number_format($sms_stats['sent'] ?? 2341) }}</div>
                <div class="sms-stat-label">Sent</div>
            </div>
            <div class="sms-stat">
                <div class="sms-stat-value">{{ $sms_stats['delivered_rate'] ?? '94' }}%</div>
                <div class="sms-stat-label">Delivered</div>
            </div>
            <div class="sms-stat">
                <div class="sms-stat-value">{{ number_format($sms_stats['replies'] ?? 187) }}</div>
                <div class="sms-stat-label">Replies</div>
            </div>
        </div>
        <div class="quick-links" style="margin-top: 20px;">
            <a href="/sms" class="action-btn">📱 SMS Dashboard</a>
        </div>
    </div>
    
    <!-- Analytics -->
    <div class="feature-card">
        <div class="feature-icon">📊</div>
        <h3 class="feature-title">Real-Time Analytics</h3>
        <p class="feature-description">
            Real-time analytics with conversion tracking, agent performance, revenue metrics, 
            and comprehensive business intelligence.
        </p>
        <div class="quick-links">
            <a href="/vici/reports" class="quick-link">Vici Reports</a>
            <a href="/leads/reports" class="quick-link">Lead Reports</a>
            <a href="/buyers/revenue" class="quick-link">Revenue Reports</a>
        </div>
    </div>
    
    <!-- Top Agent -->
    <div class="agent-card">
        <div class="feature-icon">🏆</div>
        <div class="agent-name">{{ $top_agent['name'] ?? 'Sarah M.' }}</div>
        <div class="agent-title">Top Performing Agent</div>
        <div class="agent-stat">{{ $top_agent['calls'] ?? 156 }} calls</div>
        <div style="margin-top: 10px; opacity: 0.9;">
            {{ $top_agent['conversions'] ?? 4 }} conversions today
        </div>
    </div>
</div>

<!-- Weekly Performance -->
<div class="feature-card">
    <h3 class="feature-title">📈 This Week's Performance</h3>
    <div class="stats-row" style="margin-top: 20px;">
        <div class="stat-card">
            <div class="stat-value">{{ $weekly_stats['leads'] ?? 312 }}</div>
            <div class="stat-label">New Leads</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $weekly_stats['qualified'] ?? 89 }}</div>
            <div class="stat-label">Qualified</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{{ $weekly_stats['appointments'] ?? 47 }}</div>
            <div class="stat-label">Appointments</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">${{ number_format($weekly_stats['revenue'] ?? 15600) }}</div>
            <div class="stat-label">Revenue</div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="feature-card">
    <h3 class="feature-title">⚡ Quick Actions</h3>
    <div class="quick-links">
        <a href="/admin/lead-queue-monitor" class="quick-link">📋 Lead Queue Monitor</a>
        <a href="/admin/vici-lead-flow" class="quick-link">🔄 Vici Lead Flow</a>
        <a href="/admin/vici-sync-management" class="quick-link">🔄 Vici Sync Status</a>
        <a href="/campaigns/directory" class="quick-link">📢 Campaign Directory</a>
        <a href="/admin/control-center" class="quick-link">⚙️ Control Center</a>
        <a href="/diagnostics" class="quick-link">🔧 System Diagnostics</a>
    </div>
</div>

@endsection

@section('scripts')
<script>
    // Auto-refresh stats every 30 seconds
    setInterval(function() {
        // In production, this would fetch updated stats via AJAX
        console.log('Stats refresh triggered');
    }, 30000);
</script>
@endsection
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>The Brain - Admin Dashboard</title>
    <link rel="icon" type="image/png" href="/favicon.png">
    <link rel="shortcut icon" href="/favicon.ico">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&display=swap" rel="stylesheet">
    
    <!-- QuotingFast Design System -->
    <link rel="stylesheet" href="/css/brain-design-system.css">
    
    <!-- PWA Meta Tags -->
    <meta name="theme-color" content="#2563eb">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    
    <!-- Custom overrides for legacy compatibility -->
    <style>
        /* Minimal custom styles - most moved to design system */
        body {
            background: var(--qf-gray-50);
        }
        
        /* Navigation - Using Design System */
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
            font-size: var(--qf-text-xl);
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: var(--qf-space-md);
            color: white;
        }
        
        .brand-logo {
            height: 100px;
            width: auto;
            filter: brightness(1.1);
        }
        
        .brand-text {
            font-family: 'Orbitron', sans-serif;
            font-size: 1.2rem;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .nav-menu {
            display: flex;
            list-style: none;
            gap: var(--qf-space-xl);
            align-items: center;
        }
        
        .nav-item {
            position: relative;
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
        
        /* Enhanced Dropdown Styles */
        .dropdown {
            position: relative;
        }
        
        .dropdown-toggle {
            display: flex;
            align-items: center;
            gap: var(--qf-space-sm);
            cursor: pointer;
        }
        
        .dropdown-toggle::after {
            content: '▼';
            font-size: 0.7rem;
            transition: var(--qf-transition);
        }
        
        .dropdown.open .dropdown-toggle::after {
            transform: rotate(180deg);
        }
        
        .dropdown-menu {
            position: absolute;
            top: 100%;
            left: 0;
            background: white;
            border-radius: var(--qf-radius-lg);
            box-shadow: var(--qf-shadow-xl);
            border: 1px solid var(--qf-gray-200);
            min-width: 200px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: var(--qf-transition-slow);
            z-index: 1000;
            overflow: hidden;
            margin-top: var(--qf-space-sm);
        }
        
        .dropdown.open .dropdown-menu {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        
        .dropdown-item {
            display: flex;
            align-items: center;
            gap: var(--qf-space-sm);
            padding: var(--qf-space-md) var(--qf-space-lg);
            color: var(--qf-gray-700);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.9rem;
            border-bottom: 1px solid var(--qf-gray-100);
            transition: var(--qf-transition);
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
            color: white;
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
        
        /* Feature Grid */
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }
        
        /* Feature Cards */
        .feature-card {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .feature-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 30px rgba(0,0,0,0.15);
        }
        
        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: #2563eb;
        }
        
        .feature-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            display: block;
        }
        
        .feature-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.75rem;
            color: white;
        }
        
        .feature-description {
            color: #718096;
            margin-bottom: 1.5rem;
            line-height: 1.6;
            font-size: 1rem;
        }
        
        .feature-stats {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .stat-item {
            text-align: center;
            flex: 1;
        }
        
        .stat-number {
            font-size: 1.5rem;
            font-weight: 700;
            color: #2563eb;
            display: block;
        }
        
        .stat-label {
            font-size: 0.85rem;
            color: #718096;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }
        
        .btn-primary {
            background: #2563eb;
            color: white;
        }
        
        .btn-primary:hover {
            background: #1d4ed8;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(37, 99, 235, 0.4);
        }
        
        .btn-secondary {
            background: #f7fafc;
            color: #4a5568;
            border: 1px solid #e2e8f0;
        }
        
        .btn-secondary:hover {
            background: #edf2f7;
            transform: translateY(-2px);
        }
        
        .btn-success {
            background: #48bb78;
            color: white;
        }
        
        .btn-success:hover {
            background: #38a169;
            transform: translateY(-2px);
        }
        
        .btn-warning {
            background: #ed8936;
            color: white;
        }
        
        .btn-warning:hover {
            background: #dd6b20;
            transform: translateY(-2px);
        }
        
        /* Quick Stats Bar */
        .quick-stats {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            border: 1px solid #e2e8f0;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
        }
        
        .quick-stat {
            text-align: center;
        }
        
        .quick-stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: #2563eb;
            display: block;
            margin-bottom: 0.5rem;
        }
        
        .quick-stat-label {
            color: #718096;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 0.9rem;
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
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
            
            .nav-menu {
                gap: 1rem;
            }
            
            .main-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
            width: 100%;
            box-sizing: border-box;
        }
            
            .features-grid {
                grid-template-columns: 1fr;
            }
            
            .page-header-content {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 2rem;
            width: 100%;
            box-sizing: border-box;
        }
        }
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: background 0.2s;
        }
        
        .card-button:hover {
            background: #3182ce;
        }
        
        .stats-section {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            border: 1px solid #e2e8f0;
            margin-bottom: 2rem;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
        }
        
        .stat-item {
            text-align: center;
        }
        
        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: white;
            margin-bottom: 0.5rem;
        }
        
        .stat-label {
            color: #718096;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        
        .system-status {
            background: #f0fff4;
            border: 1px solid #9ae6b4;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 2rem;
        }
        
        .status-indicator {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #22543d;
            font-weight: 500;
        }
        
        .status-dot {
            width: 8px;
            height: 8px;
            background: #38a169;
            border-radius: 50%;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        
        .endpoints-list {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            border: 1px solid #e2e8f0;
        }
        
        .endpoints-list h3 {
            margin-bottom: 1rem;
            color: white;
        }
        
        .endpoint-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 0;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .endpoint-item:last-child {
            border-bottom: none;
        }
        
        .endpoint-path {
            font-family: 'Monaco', 'Menlo', monospace;
            background: #f7fafc;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.875rem;
        }
        
        .endpoint-method {
            background: #4299e1;
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 500;
        }
        
        @media (max-width: 768px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        /* Feature cards enhanced with design system */
        .feature-card {
            background: white;
            border: 1px solid var(--qf-gray-200);
            border-radius: var(--qf-radius-lg);
            padding: var(--qf-space-xl);
            box-shadow: var(--qf-shadow-sm);
            transition: var(--qf-transition);
            position: relative;
            overflow: hidden;
        }
        
        .feature-card:hover {
            box-shadow: var(--qf-shadow-lg);
            transform: translateY(-2px);
            border-color: var(--qf-primary-light);
        }
        
        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 3px;
            background: linear-gradient(90deg, var(--qf-primary), var(--qf-secondary));
        }
        
        .feature-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: var(--qf-space-md);
            margin: var(--qf-space-lg) 0;
        }
        
        .stat-item {
            text-align: center;
            padding: var(--qf-space-md);
            background: var(--qf-gray-50);
            border-radius: var(--qf-radius-md);
            border: 1px solid var(--qf-gray-200);
        }
        
        .stat-number {
            display: block;
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--qf-primary);
            margin-bottom: var(--qf-space-xs);
        }
        
        .stat-label {
            font-size: 0.75rem;
            color: var(--qf-gray-600);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        
        .action-buttons {
            display: flex;
            gap: var(--qf-space-md);
            margin-top: var(--qf-space-lg);
        }
        
        /* Legacy button compatibility */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: var(--qf-space-sm);
            padding: var(--qf-space-sm) var(--qf-space-lg);
            border: 1px solid transparent;
            border-radius: var(--qf-radius-md);
            font-weight: 500;
            font-size: 0.875rem;
            text-decoration: none;
            cursor: pointer;
            transition: var(--qf-transition);
            box-shadow: var(--qf-shadow-sm);
        }
        
        .btn:hover {
            transform: translateY(-1px);
            box-shadow: var(--qf-shadow-md);
        }
        
        .btn-primary {
            background: var(--qf-primary);
            color: white;
            border-color: var(--qf-primary);
        }
        
        .btn-secondary {
            background: white;
            color: var(--qf-gray-700);
            border-color: var(--qf-gray-300);
        }
        
        .btn-success {
            background: var(--qf-success);
            color: white;
            border-color: var(--qf-success);
        }
        
        .btn-warning {
            background: var(--qf-warning);
            color: white;
            border-color: var(--qf-warning);
        }
    </style>
</head>
<body class="qf-fade-in">
    <!-- Navigation Header -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-brand">
                <img src="https://quotingfast.com/whitelogo" alt="QuotingFast" class="brand-logo" onerror="this.src='https://quotingfast.com/whitelogo'; this.onerror=null;">
                <div style="display: flex; flex-direction: column; gap: 2px;">
                    <div class="logo-text" style="display: flex; flex-direction: column; align-items: center; line-height: 1;">
                    <div style="font-family: 'Orbitron', sans-serif; font-size: 0.9rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px;">The</div>
                    <div class="brand-text" style="font-family: 'Orbitron', sans-serif; font-size: 1.4rem; font-weight: 900; text-transform: uppercase; letter-spacing: 1px;">Brain</div>
                </div>
                </div>
            </div>
            <ul class="nav-menu">
                <!-- Dashboard -->
                <li class="nav-item">
                    <a href="/admin" class="nav-link active">📊 Dashboard</a>
                </li>
                
                <!-- Leads Dropdown -->
                <li class="nav-item dropdown" id="leadsDropdown">
                    <a href="#" class="nav-link dropdown-toggle">👥 Leads</a>
                    <div class="dropdown-menu">
                        <a href="/leads" class="dropdown-item">📋 View All Leads</a>
                        <a href="/lead-upload" class="dropdown-item">📁 Upload CSV</a>
                        <a href="#" class="dropdown-item" onclick="alert('Lead Types management coming soon!')">🏷️ Lead Types</a>
                    </div>
                </li>
                
                <!-- Management Dropdown -->
                <li class="nav-item dropdown" id="managementDropdown">
                    <a href="#" class="nav-link dropdown-toggle">⚙️ Management</a>
                    <div class="dropdown-menu">
                        <a href="/api-directory" class="dropdown-item">🔗 API Directory</a>
                        <a href="/campaign-directory" class="dropdown-item">📊 Campaigns</a>
                        <a href="/admin/buyer-management" class="dropdown-item">🎭 Buyer Management</a>
                        <a href="/admin/allstate-testing" class="dropdown-item">🧪 Allstate Testing</a>
                        <a href="/admin/lead-queue" class="dropdown-item">🛡️ Lead Queue Monitor</a>
                        <a href="#" class="dropdown-item" onclick="alert('Integrations management coming soon!')">🔌 Integrations</a>
                    </div>
                </li>
                
                <!-- Analytics -->
                <li class="nav-item">
                    <a href="/analytics" class="nav-link" onclick="alert('Analytics dashboard coming soon!')">📈 Analytics</a>
                </li>
                
                <!-- Communications -->
                <li class="nav-item dropdown" id="communicationsDropdown">
                    <a href="#" class="nav-link dropdown-toggle">💬 Communications</a>
                    <div class="dropdown-menu">
                        <a href="#" class="dropdown-item" onclick="alert('SMS/Messaging feature coming soon!')">📱 SMS Center</a>
                        <a href="#" class="dropdown-item" onclick="alert('Email campaigns coming soon!')">📧 Email Campaigns</a>
                        <a href="#" class="dropdown-item" onclick="alert('Call tracking coming soon!')">📞 Call Tracking</a>
                    </div>
                </li>
                
                <!-- Settings -->
                <li class="nav-item dropdown" id="settingsDropdown">
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
            <h1 class="page-title">Dashboard</h1>
            <p class="page-subtitle">Auto Insurance Leads Management System</p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-container">
        <!-- Quick Stats -->
        <div class="quick-stats">
            <div class="stats-grid">
                <div class="quick-stat">
                    <span class="quick-stat-number" id="total-leads">11</span>
                    <span class="quick-stat-label">Total Leads</span>
                </div>
                <div class="quick-stat">
                    <span class="quick-stat-number" id="new-leads">11</span>
                    <span class="quick-stat-label">New Leads</span>
                </div>
                <div class="quick-stat">
                    <span class="quick-stat-number" id="contacted">0</span>
                    <span class="quick-stat-label">Contacted</span>
                </div>
                <div class="quick-stat">
                    <span class="quick-stat-number" id="converted">0</span>
                    <span class="quick-stat-label">Converted</span>
                </div>
            </div>
        </div>

        <!-- Feature Cards -->
        <div class="features-grid">
            <!-- Lead Management -->
            <div class="feature-card">
                <span class="feature-icon">👥</span>
                <h3 class="feature-title">Lead Management</h3>
                <p class="feature-description">
                    Comprehensive lead database with advanced search, filtering, and bulk operations. 
                    Track lead sources, status, and conversion metrics.
                </p>
                <div class="feature-stats">
                    <div class="stat-item">
                        <span class="stat-number" id="leads-today">47</span>
                        <span class="stat-label">Today</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number" id="leads-week">312</span>
                        <span class="stat-label">This Week</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number" id="leads-qualified">89</span>
                        <span class="stat-label">Qualified</span>
                    </div>
                </div>
                                                            <div class="action-buttons">
                                            <a href="/leads" class="btn btn-primary">
                            View Leads
                        </a>
                </div>
            </div>

            <!-- SMS Management -->
            <div class="feature-card">
                <span class="feature-icon">💬</span>
                <h3 class="feature-title">SMS Management</h3>
                <p class="feature-description">
                    Send, schedule, and track SMS campaigns. Automated follow-ups, 
                    templates, and compliance management for all messaging.
                </p>
                <div class="feature-stats">
                    <div class="stat-item">
                        <span class="stat-number" id="sms-sent">2,341</span>
                        <span class="stat-label">Sent</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number" id="sms-delivered">94.2%</span>
                        <span class="stat-label">Delivered</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number" id="sms-replies">187</span>
                        <span class="stat-label">Replies</span>
                    </div>
                </div>
                <div class="action-buttons">
                    <a href="#" class="btn btn-primary">
                        📱 SMS Dashboard
                    </a>
                    <a href="#" class="btn btn-secondary">
                        ✉️ New Campaign
                    </a>
                </div>
            </div>

            <!-- Analytics & Reporting -->
            <div class="feature-card">
                <span class="feature-icon">📊</span>
                <h3 class="feature-title">Analytics & Reporting</h3>
                <p class="feature-description">
                    Real-time analytics with conversion tracking, agent performance, 
                    revenue metrics, and comprehensive business intelligence.
                </p>
                <div class="feature-stats">
                    <div class="stat-item">
                        <span class="stat-number" id="reports-generated">156</span>
                        <span class="stat-label">Reports</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number" id="avg-call-time">4.2m</span>
                        <span class="stat-label">Avg Call</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number" id="top-agent">Sarah M.</span>
                        <span class="stat-label">Top Agent</span>
                    </div>
                </div>
                <div class="action-buttons">
                    <a href="/analytics" class="btn btn-primary">
                        📈 View Analytics
                    </a>
                    <a href="#" class="btn btn-secondary">
                        📋 Generate Report
                    </a>
                </div>
            </div>

            <!-- Lead Cost Reporting -->
            <div class="feature-card">
                <span class="feature-icon">💰</span>
                <h3 class="feature-title">Lead Cost Analytics</h3>
                <p class="feature-description">
                    Track lead acquisition costs by source, state, and time period. 
                    Monitor daily spend, ROI metrics, and cost optimization opportunities.
                </p>
                <div class="feature-stats">
                    <div class="stat-item">
                        <span class="stat-number" id="cost-today">$0.00</span>
                        <span class="stat-label">Today's Cost</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number" id="avg-cost-lead">$0.00</span>
                        <span class="stat-label">Avg/Lead</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number" id="top-cost-source">-</span>
                        <span class="stat-label">Top Source</span>
                    </div>
                </div>
                <div class="action-buttons">
                    <a href="#" class="btn btn-primary" onclick="showCostReports()">
                        💰 Cost Reports
                    </a>
                    <a href="#" class="btn btn-secondary" onclick="showCostByState()">
                        📍 By State
                    </a>
                </div>
            </div>

            <!-- Campaign Management -->
            <div class="feature-card">
                <span class="feature-icon">🎯</span>
                <h3 class="feature-title">Campaign Management</h3>
                <p class="feature-description">
                    Create, manage, and optimize marketing campaigns across multiple channels. 
                    Track ROI, conversion rates, and campaign performance.
                </p>
                <div class="feature-stats">
                    <div class="stat-item">
                        <span class="stat-number" id="active-campaigns-detail">8</span>
                        <span class="stat-label">Active</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number" id="campaign-roi">312%</span>
                        <span class="stat-label">Avg ROI</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number" id="best-campaign">Auto-23</span>
                        <span class="stat-label">Top Campaign</span>
                    </div>
                </div>
                <div class="action-buttons">
                    <a href="#" class="btn btn-primary">
                        🎯 View Campaigns
                    </a>
                    <a href="#" class="btn btn-success">
                        🚀 New Campaign
                    </a>
                </div>
            </div>

            <!-- System Monitoring -->
            <div class="feature-card">
                <span class="feature-icon">🔧</span>
                <h3 class="feature-title">System Monitoring</h3>
                <p class="feature-description">
                    Monitor system health, API integrations, webhook status, and performance metrics. 
                    Real-time alerts and diagnostics.
                </p>
                <div class="feature-stats">
                    <div class="stat-item">
                        <span class="stat-number" id="system-uptime">99.9%</span>
                        <span class="stat-label">Uptime</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number" id="api-calls">15.2K</span>
                        <span class="stat-label">API Calls</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number" id="webhooks-active">12</span>
                        <span class="stat-label">Webhooks</span>
                    </div>
                </div>
                <div class="action-buttons">
                    <a href="/webhook/status" class="btn btn-primary">
                        🔍 System Status
                    </a>
                    <a href="/test" class="btn btn-warning">
                        🧪 Run Tests
                    </a>
                </div>
            </div>

            <!-- Settings & Configuration -->
            <div class="feature-card">
                <span class="feature-icon">⚙️</span>
                <h3 class="feature-title">Settings & Configuration</h3>
                <p class="feature-description">
                    Configure system settings, API keys, user permissions, and integration parameters. 
                    Manage security and compliance settings.
                </p>
                <div class="feature-stats">
                    <div class="stat-item">
                        <span class="stat-number" id="integrations">8</span>
                        <span class="stat-label">Integrations</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number" id="users">24</span>
                        <span class="stat-label">Users</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number" id="last-backup">2h ago</span>
                        <span class="stat-label">Last Backup</span>
                    </div>
                </div>
                <div class="action-buttons">
                    <a href="#" class="btn btn-primary">
                        ⚙️ Settings
                    </a>
                    <a href="#" class="btn btn-secondary">
                        👥 Manage Users
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- QuotingFast JavaScript Enhancements -->
    <script src="/js/brain-enhancements.js"></script>
    
    <script>
        // Enhanced dashboard functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Add loading states to all buttons
            document.querySelectorAll('.btn').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    if (this.href && !this.href.includes('#')) {
                        BrainUI.setLoadingState(this, true);
                    }
                });
            });
            
            // Animate counter numbers
            animateCounters();
        });
        
        function animateCounters() {
            document.querySelectorAll('.stat-number').forEach(counter => {
                const text = counter.textContent;
                const number = parseFloat(text.replace(/[^0-9.]/g, ''));
                
                if (!isNaN(number) && number > 0) {
                    counter.setAttribute('data-count', number);
                    counter.textContent = '0';
                    
                    let current = 0;
                    const increment = number / 50;
                    const timer = setInterval(() => {
                        current += increment;
                        if (current >= number) {
                            current = number;
                            clearInterval(timer);
                        }
                        
                        if (text.includes('$')) {
                            counter.textContent = '$' + Math.floor(current).toLocaleString();
                        } else if (text.includes('%')) {
                            counter.textContent = Math.floor(current) + '%';
                        } else if (text.includes('K')) {
                            counter.textContent = (Math.floor(current * 10) / 10) + 'K';
                        } else {
                            counter.textContent = Math.floor(current).toLocaleString();
                        }
                    }, 30);
                }
            });
        }
        
        // Load basic stats with enhanced error handling
        fetch('/api/webhooks')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('webhook-calls').textContent = data.stats?.total_calls || '0';
                    BrainUI.showNotification('Dashboard data loaded successfully!', 'success', 3000);
                }
            })
            .catch(error => {
                document.getElementById('webhook-calls').textContent = 'N/A';
                console.error('Failed to load webhook stats:', error);
            });
        
        // Simulate lead count (replace with real API call when available)
        document.getElementById('total-leads').textContent = 'N/A';
        
        // Load cost analytics data
        async function loadCostAnalytics() {
            try {
                const response = await fetch('/api/reports/cost/today');
                const data = await response.json();
                
                // Update cost stats
                document.getElementById('cost-today').textContent = `$${data.summary.total_cost}`;
                document.getElementById('avg-cost-lead').textContent = `$${data.summary.average_cost_per_lead}`;
                
                // Find top cost source
                if (data.by_source && data.by_source.length > 0) {
                    const topSource = data.by_source.reduce((max, source) => 
                        source.total_cost > max.total_cost ? source : max
                    );
                    document.getElementById('top-cost-source').textContent = topSource.source;
                }
            } catch (error) {
                console.error('Failed to load cost analytics:', error);
            }
        }

        // Show cost reports modal/popup
        function showCostReports() {
            fetch('/api/reports/cost/today')
                .then(response => response.json())
                .then(data => {
                    let reportHtml = `
                        <div style="background: white; padding: 2rem; border-radius: 8px; max-width: 800px; margin: 2rem auto; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                            <h2>📊 Today's Lead Cost Report</h2>
                            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; margin: 1rem 0;">
                                <div style="text-align: center; padding: 1rem; background: #f7fafc; border-radius: 6px;">
                                    <div style="font-size: 1.5rem; font-weight: bold; color: white;">$${data.summary.total_cost}</div>
                                    <div style="color: #718096;">Total Cost Today</div>
                                </div>
                                <div style="text-align: center; padding: 1rem; background: #f7fafc; border-radius: 6px;">
                                    <div style="font-size: 1.5rem; font-weight: bold; color: white;">${data.summary.total_leads}</div>
                                    <div style="color: #718096;">Total Leads</div>
                                </div>
                                <div style="text-align: center; padding: 1rem; background: #f7fafc; border-radius: 6px;">
                                    <div style="font-size: 1.5rem; font-weight: bold; color: white;">$${data.summary.average_cost_per_lead}</div>
                                    <div style="color: #718096;">Avg Cost/Lead</div>
                                </div>
                            </div>
                            
                            <h3>💰 Cost by Source</h3>
                            <div style="margin: 1rem 0;">
                                ${data.by_source.map(source => `
                                    <div style="display: flex; justify-content: space-between; padding: 0.5rem; border-bottom: 1px solid #e2e8f0;">
                                        <span>${source.source}</span>
                                        <span><strong>$${source.total_cost}</strong> (${source.count} leads)</span>
                                    </div>
                                `).join('')}
                            </div>
                            
                            <h3>📍 Cost by State</h3>
                            <div style="margin: 1rem 0;">
                                ${data.by_state.map(state => `
                                    <div style="display: flex; justify-content: space-between; padding: 0.5rem; border-bottom: 1px solid #e2e8f0;">
                                        <span>${state.state}</span>
                                        <span><strong>$${state.total_cost}</strong> (${state.count} leads)</span>
                                    </div>
                                `).join('')}
                            </div>
                            
                            <button onclick="closeCostReport()" style="background: #4299e1; color: white; padding: 0.5rem 1rem; border: none; border-radius: 4px; margin-top: 1rem;">Close</button>
                        </div>
                        <div onclick="closeCostReport()" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;"></div>
                    `;
                    
                    const modal = document.createElement('div');
                    modal.id = 'cost-report-modal';
                    modal.style.cssText = 'position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 1001; overflow-y: auto;';
                    modal.innerHTML = reportHtml;
                    document.body.appendChild(modal);
                })
                .catch(error => {
                    alert('Failed to load cost report. Please try again.');
                    console.error('Cost report error:', error);
                });
        }

        function showCostByState() {
            const state = prompt('Enter state code (e.g., TX, CA, FL):');
            if (state) {
                window.open(`/api/reports/cost/state/${state}`, '_blank');
            }
        }

        function closeCostReport() {
            const modal = document.getElementById('cost-report-modal');
            if (modal) {
                modal.remove();
            }
        }

        // Clean up test leads
        // REMOVED: cleanupTestLeads function per user request
        // This was automatically deleting leads on deployment

        // Dropdown functionality
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
        
        // Load cost analytics on page load
        loadCostAnalytics();
        
        // Add current timestamp
        const now = new Date();
        document.title = `The Brain Admin - ${now.toLocaleDateString()}`;
    </script>
</body>
</html>
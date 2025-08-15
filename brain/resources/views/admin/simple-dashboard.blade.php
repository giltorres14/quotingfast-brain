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
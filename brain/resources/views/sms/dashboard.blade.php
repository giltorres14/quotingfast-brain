@extends('layouts.app-new')

@section('title', 'SMS Dashboard - QuotingFast Brain')

@section('subnav')
    <a href="/sms" class="sub-nav-item active">Dashboard</a>
    <a href="/sms/campaigns" class="sub-nav-item">Campaigns</a>
    <a href="/sms/templates" class="sub-nav-item">Templates</a>
    <a href="/sms/analytics" class="sub-nav-item">Analytics</a>
@endsection

@section('page-header')
    <h1 class="page-title">SMS Management Dashboard</h1>
    <div class="breadcrumbs">
        <span>Home</span>
        <span class="breadcrumb-separator">›</span>
        <span>SMS</span>
        <span class="breadcrumb-separator">›</span>
        <span>Dashboard</span>
    </div>
@endsection

@section('content')
    <div class="card">
        <h2 class="card-title">SMS Integration</h2>
        <p style="color: #6b7280; margin-bottom: 20px;">Parcelvoy SMS integration will be configured here.</p>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-top: 20px;">
            <div class="card" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white;">
                <div style="font-size: 0.875rem; opacity: 0.9;">Messages Sent</div>
                <div style="font-size: 2rem; font-weight: 700;">0</div>
                <div style="font-size: 0.75rem; margin-top: 8px; opacity: 0.8;">This month</div>
            </div>
            
            <div class="card" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); color: white;">
                <div style="font-size: 0.875rem; opacity: 0.9;">Active Campaigns</div>
                <div style="font-size: 2rem; font-weight: 700;">0</div>
                <div style="font-size: 0.75rem; margin-top: 8px; opacity: 0.8;">Running now</div>
            </div>
            
            <div class="card" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: white;">
                <div style="font-size: 0.875rem; opacity: 0.9;">Response Rate</div>
                <div style="font-size: 2rem; font-weight: 700;">0%</div>
                <div style="font-size: 0.75rem; margin-top: 8px; opacity: 0.8;">Average</div>
            </div>
        </div>
        
        <div style="margin-top: 30px; padding: 20px; background: #f9fafb; border-radius: 8px;">
            <h3 style="font-weight: 600; margin-bottom: 10px;">Coming Soon</h3>
            <p style="color: #6b7280;">SMS functionality with Parcelvoy integration is under development.</p>
        </div>
    </div>
@endsection

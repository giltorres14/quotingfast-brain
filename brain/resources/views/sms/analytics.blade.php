@extends('layouts.app-new')

@section('title', 'SMS Analytics - QuotingFast Brain')

@section('subnav')
    <a href="/sms" class="sub-nav-item">Dashboard</a>
    <a href="/sms/campaigns" class="sub-nav-item">Campaigns</a>
    <a href="/sms/templates" class="sub-nav-item">Templates</a>
    <a href="/sms/analytics" class="sub-nav-item active">Analytics</a>
@endsection

@section('page-header')
    <h1 class="page-title">SMS Analytics</h1>
    <div class="breadcrumbs">
        <span>Home</span>
        <span class="breadcrumb-separator">›</span>
        <span>SMS</span>
        <span class="breadcrumb-separator">›</span>
        <span>Analytics</span>
    </div>
@endsection

@section('content')
    <div class="card">
        <h2 class="card-title">SMS Performance Analytics</h2>
        <p style="color: #6b7280;">Track and analyze SMS campaign performance.</p>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-top: 20px;">
            <div style="padding: 16px; background: #f9fafb; border-radius: 8px; text-align: center;">
                <div style="font-size: 2rem; font-weight: 700; color: #3b82f6;">0</div>
                <div style="color: #6b7280; font-size: 0.875rem;">Messages Sent</div>
            </div>
            <div style="padding: 16px; background: #f9fafb; border-radius: 8px; text-align: center;">
                <div style="font-size: 2rem; font-weight: 700; color: #10b981;">0%</div>
                <div style="color: #6b7280; font-size: 0.875rem;">Delivery Rate</div>
            </div>
            <div style="padding: 16px; background: #f9fafb; border-radius: 8px; text-align: center;">
                <div style="font-size: 2rem; font-weight: 700; color: #f59e0b;">0%</div>
                <div style="color: #6b7280; font-size: 0.875rem;">Response Rate</div>
            </div>
            <div style="padding: 16px; background: #f9fafb; border-radius: 8px; text-align: center;">
                <div style="font-size: 2rem; font-weight: 700; color: #8b5cf6;">$0</div>
                <div style="color: #6b7280; font-size: 0.875rem;">ROI</div>
            </div>
        </div>
        
        <div style="margin-top: 30px; padding: 20px; background: #f9fafb; border-radius: 8px; text-align: center;">
            <p style="color: #6b7280;">Analytics will be available once SMS campaigns are active</p>
        </div>
    </div>
@endsection

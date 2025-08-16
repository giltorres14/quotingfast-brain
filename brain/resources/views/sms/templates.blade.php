@extends('layouts.app-new')

@section('title', 'SMS Templates - QuotingFast Brain')

@section('subnav')
    <a href="/sms" class="sub-nav-item">Dashboard</a>
    <a href="/sms/campaigns" class="sub-nav-item">Campaigns</a>
    <a href="/sms/templates" class="sub-nav-item active">Templates</a>
    <a href="/sms/analytics" class="sub-nav-item">Analytics</a>
@endsection

@section('page-header')
    <h1 class="page-title">SMS Templates</h1>
    <div class="breadcrumbs">
        <span>Home</span>
        <span class="breadcrumb-separator">›</span>
        <span>SMS</span>
        <span class="breadcrumb-separator">›</span>
        <span>Templates</span>
    </div>
@endsection

@section('content')
    <div class="card">
        <h2 class="card-title">Message Templates</h2>
        <p style="color: #6b7280;">Create and manage reusable SMS message templates.</p>
        
        <div style="text-align: center; padding: 60px 20px; background: #f9fafb; border-radius: 8px; margin-top: 20px;">
            <div style="font-size: 3rem; margin-bottom: 16px;">📝</div>
            <h3 style="font-weight: 600; margin-bottom: 8px;">No Templates Yet</h3>
            <p style="color: #6b7280;">Create message templates for quick campaign setup</p>
            <button class="btn btn-primary" style="margin-top: 16px;" disabled>Create Template (Coming Soon)</button>
        </div>
    </div>
@endsection



@extends('layouts.app-new')

@section('title', 'SMS Campaigns - QuotingFast Brain')

@section('subnav')
    <a href="/sms" class="sub-nav-item">Dashboard</a>
    <a href="/sms/campaigns" class="sub-nav-item active">Campaigns</a>
    <a href="/sms/templates" class="sub-nav-item">Templates</a>
    <a href="/sms/analytics" class="sub-nav-item">Analytics</a>
@endsection

@section('page-header')
    <h1 class="page-title">SMS Campaigns</h1>
    <div class="breadcrumbs">
        <span>Home</span>
        <span class="breadcrumb-separator">›</span>
        <span>SMS</span>
        <span class="breadcrumb-separator">›</span>
        <span>Campaigns</span>
    </div>
@endsection

@section('content')
    <div class="card">
        <h2 class="card-title">Campaign Management</h2>
        <p style="color: #6b7280;">SMS campaigns will be managed here once Parcelvoy integration is complete.</p>
        
        <div style="text-align: center; padding: 60px 20px; background: #f9fafb; border-radius: 8px; margin-top: 20px;">
            <div style="font-size: 3rem; margin-bottom: 16px;">📱</div>
            <h3 style="font-weight: 600; margin-bottom: 8px;">No Campaigns Yet</h3>
            <p style="color: #6b7280;">Create your first SMS campaign to start engaging leads</p>
            <button class="btn btn-primary" style="margin-top: 16px;" disabled>Create Campaign (Coming Soon)</button>
        </div>
    </div>
@endsection


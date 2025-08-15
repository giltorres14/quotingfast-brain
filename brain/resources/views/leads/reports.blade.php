@extends('layouts.app-new')

@section('title', 'Lead Reports - QuotingFast Brain')

@section('subnav')
    <a href="/leads" class="sub-nav-item">Overview</a>
    <a href="/leads/queue" class="sub-nav-item">Queue</a>
    <a href="/leads/search" class="sub-nav-item">Search</a>
    <a href="/leads/import" class="sub-nav-item">Import</a>
    <a href="/leads/reports" class="sub-nav-item active">Reports</a>
@endsection

@section('page-header')
    <h1 class="page-title">Lead Reports</h1>
    <div class="breadcrumbs">
        <span>Home</span>
        <span class="breadcrumb-separator">›</span>
        <span>Leads</span>
        <span class="breadcrumb-separator">›</span>
        <span>Reports</span>
    </div>
@endsection

@section('content')
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
        <!-- Lead Distribution Report -->
        <div class="card">
            <h3 style="font-weight: 600; margin-bottom: 12px;">📊 Lead Distribution</h3>
            <p style="color: #6b7280; font-size: 0.875rem; margin-bottom: 16px;">
                Analysis of leads by source, state, and campaign
            </p>
            <button class="btn btn-primary" style="width: 100%;">Generate Report</button>
        </div>
        
        <!-- Source Analysis -->
        <div class="card">
            <h3 style="font-weight: 600; margin-bottom: 12px;">📈 Source Analysis</h3>
            <p style="color: #6b7280; font-size: 0.875rem; margin-bottom: 16px;">
                Performance metrics by lead source
            </p>
            <button class="btn btn-primary" style="width: 100%;">Generate Report</button>
        </div>
        
        <!-- Conversion Metrics -->
        <div class="card">
            <h3 style="font-weight: 600; margin-bottom: 12px;">🎯 Conversion Metrics</h3>
            <p style="color: #6b7280; font-size: 0.875rem; margin-bottom: 16px;">
                Lead to sale conversion tracking
            </p>
            <button class="btn btn-primary" style="width: 100%;">Generate Report</button>
        </div>
        
        <!-- Daily Activity -->
        <div class="card">
            <h3 style="font-weight: 600; margin-bottom: 12px;">📅 Daily Activity</h3>
            <p style="color: #6b7280; font-size: 0.875rem; margin-bottom: 16px;">
                Lead volume and activity by day
            </p>
            <button class="btn btn-primary" style="width: 100%;">Generate Report</button>
        </div>
        
        <!-- Quality Score -->
        <div class="card">
            <h3 style="font-weight: 600; margin-bottom: 12px;">⭐ Quality Score</h3>
            <p style="color: #6b7280; font-size: 0.875rem; margin-bottom: 16px;">
                Lead quality analysis and scoring
            </p>
            <button class="btn btn-primary" style="width: 100%;">Generate Report</button>
        </div>
        
        <!-- TCPA Compliance -->
        <div class="card">
            <h3 style="font-weight: 600; margin-bottom: 12px;">⚖️ TCPA Compliance</h3>
            <p style="color: #6b7280; font-size: 0.875rem; margin-bottom: 16px;">
                Compliance and opt-in verification
            </p>
            <button class="btn btn-primary" style="width: 100%;">Generate Report</button>
        </div>
    </div>
@endsection

@extends('layouts.app-new')

@section('title', 'Revenue Reports - QuotingFast Brain')

@section('subnav')
    <a href="/buyers" class="sub-nav-item">Dashboard</a>
    <a href="/buyers/directory" class="sub-nav-item">Directory</a>
    <a href="/buyers/transfers" class="sub-nav-item">Transfers</a>
    <a href="/buyers/revenue" class="sub-nav-item active">Revenue</a>
@endsection

@section('page-header')
    <h1 class="page-title">Revenue Reports</h1>
    <div class="breadcrumbs">
        <span>Home</span>
        <span class="breadcrumb-separator">›</span>
        <span>Buyer Portal</span>
        <span class="breadcrumb-separator">›</span>
        <span>Revenue</span>
    </div>
@endsection

@section('content')
    <!-- Revenue Summary Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 20px;">
        <div class="card" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white;">
            <div style="font-size: 0.875rem; opacity: 0.9;">Today's Revenue</div>
            <div style="font-size: 2rem; font-weight: 700;">$0.00</div>
            <div style="font-size: 0.75rem; margin-top: 8px; opacity: 0.8;">0 transfers</div>
        </div>
        
        <div class="card" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); color: white;">
            <div style="font-size: 0.875rem; opacity: 0.9;">This Week</div>
            <div style="font-size: 2rem; font-weight: 700;">$0.00</div>
            <div style="font-size: 0.75rem; margin-top: 8px; opacity: 0.8;">0 transfers</div>
        </div>
        
        <div class="card" style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); color: white;">
            <div style="font-size: 0.875rem; opacity: 0.9;">This Month</div>
            <div style="font-size: 2rem; font-weight: 700;">$0.00</div>
            <div style="font-size: 0.75rem; margin-top: 8px; opacity: 0.8;">0 transfers</div>
        </div>
        
        <div class="card" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: white;">
            <div style="font-size: 0.875rem; opacity: 0.9;">All Time</div>
            <div style="font-size: 2rem; font-weight: 700;">$0.00</div>
            <div style="font-size: 0.75rem; margin-top: 8px; opacity: 0.8;">0 transfers</div>
        </div>
    </div>
    
    <!-- Revenue by Buyer -->
    <div class="card">
        <h2 class="card-title">Revenue by Buyer</h2>
        
        <div style="text-align: center; padding: 60px 20px; background: #f9fafb; border-radius: 8px;">
            <div style="font-size: 3rem; margin-bottom: 16px;">💰</div>
            <h3 style="font-weight: 600; margin-bottom: 8px;">No Revenue Data</h3>
            <p style="color: #6b7280;">Revenue will appear here once transfers are processed</p>
        </div>
    </div>
@endsection



@section('title', 'Revenue Reports - QuotingFast Brain')

@section('subnav')
    <a href="/buyers" class="sub-nav-item">Dashboard</a>
    <a href="/buyers/directory" class="sub-nav-item">Directory</a>
    <a href="/buyers/transfers" class="sub-nav-item">Transfers</a>
    <a href="/buyers/revenue" class="sub-nav-item active">Revenue</a>
@endsection

@section('page-header')
    <h1 class="page-title">Revenue Reports</h1>
    <div class="breadcrumbs">
        <span>Home</span>
        <span class="breadcrumb-separator">›</span>
        <span>Buyer Portal</span>
        <span class="breadcrumb-separator">›</span>
        <span>Revenue</span>
    </div>
@endsection

@section('content')
    <!-- Revenue Summary Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 20px;">
        <div class="card" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white;">
            <div style="font-size: 0.875rem; opacity: 0.9;">Today's Revenue</div>
            <div style="font-size: 2rem; font-weight: 700;">$0.00</div>
            <div style="font-size: 0.75rem; margin-top: 8px; opacity: 0.8;">0 transfers</div>
        </div>
        
        <div class="card" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); color: white;">
            <div style="font-size: 0.875rem; opacity: 0.9;">This Week</div>
            <div style="font-size: 2rem; font-weight: 700;">$0.00</div>
            <div style="font-size: 0.75rem; margin-top: 8px; opacity: 0.8;">0 transfers</div>
        </div>
        
        <div class="card" style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); color: white;">
            <div style="font-size: 0.875rem; opacity: 0.9;">This Month</div>
            <div style="font-size: 2rem; font-weight: 700;">$0.00</div>
            <div style="font-size: 0.75rem; margin-top: 8px; opacity: 0.8;">0 transfers</div>
        </div>
        
        <div class="card" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: white;">
            <div style="font-size: 0.875rem; opacity: 0.9;">All Time</div>
            <div style="font-size: 2rem; font-weight: 700;">$0.00</div>
            <div style="font-size: 0.75rem; margin-top: 8px; opacity: 0.8;">0 transfers</div>
        </div>
    </div>
    
    <!-- Revenue by Buyer -->
    <div class="card">
        <h2 class="card-title">Revenue by Buyer</h2>
        
        <div style="text-align: center; padding: 60px 20px; background: #f9fafb; border-radius: 8px;">
            <div style="font-size: 3rem; margin-bottom: 16px;">💰</div>
            <h3 style="font-weight: 600; margin-bottom: 8px;">No Revenue Data</h3>
            <p style="color: #6b7280;">Revenue will appear here once transfers are processed</p>
        </div>
    </div>
@endsection







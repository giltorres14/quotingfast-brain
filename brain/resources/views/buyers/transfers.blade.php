@extends('layouts.app-new')

@section('title', 'Lead Transfers - QuotingFast Brain')

@section('subnav')
    <a href="/buyers" class="sub-nav-item">Dashboard</a>
    <a href="/buyers/directory" class="sub-nav-item">Directory</a>
    <a href="/buyers/transfers" class="sub-nav-item active">Transfers</a>
    <a href="/buyers/revenue" class="sub-nav-item">Revenue</a>
@endsection

@section('page-header')
    <h1 class="page-title">Lead Transfers</h1>
    <div class="breadcrumbs">
        <span>Home</span>
        <span class="breadcrumb-separator">›</span>
        <span>Buyer Portal</span>
        <span class="breadcrumb-separator">›</span>
        <span>Transfers</span>
    </div>
@endsection

@section('content')
    <div class="card">
        <h2 class="card-title">Transfer History</h2>
        
        <!-- Date Filter -->
        <div style="display: flex; gap: 12px; margin-bottom: 20px;">
            <input type="date" style="padding: 8px; border: 1px solid #e5e7eb; border-radius: 6px;">
            <input type="date" style="padding: 8px; border: 1px solid #e5e7eb; border-radius: 6px;">
            <button class="btn btn-primary">Filter</button>
        </div>
        
        <!-- Transfer Stats -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 16px; margin-bottom: 20px;">
            <div style="padding: 16px; background: #f9fafb; border-radius: 8px;">
                <div style="font-size: 1.5rem; font-weight: 700;">0</div>
                <div style="color: #6b7280; font-size: 0.875rem;">Total Transfers</div>
            </div>
            <div style="padding: 16px; background: #f9fafb; border-radius: 8px;">
                <div style="font-size: 1.5rem; font-weight: 700; color: #10b981;">0</div>
                <div style="color: #6b7280; font-size: 0.875rem;">Successful</div>
            </div>
            <div style="padding: 16px; background: #f9fafb; border-radius: 8px;">
                <div style="font-size: 1.5rem; font-weight: 700; color: #ef4444;">0</div>
                <div style="color: #6b7280; font-size: 0.875rem;">Failed</div>
            </div>
            <div style="padding: 16px; background: #f9fafb; border-radius: 8px;">
                <div style="font-size: 1.5rem; font-weight: 700; color: #3b82f6;">$0</div>
                <div style="color: #6b7280; font-size: 0.875rem;">Revenue</div>
            </div>
        </div>
        
        <!-- Transfer Table -->
        <div style="text-align: center; padding: 40px 20px; background: #f9fafb; border-radius: 8px;">
            <p style="color: #9ca3af;">No transfers found for the selected period</p>
        </div>
    </div>
@endsection

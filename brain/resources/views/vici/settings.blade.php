@extends('layouts.app-new')

@section('title', 'Vici Settings - QuotingFast Brain')

@section('subnav')
    <a href="/vici" class="sub-nav-item">Dashboard</a>
    <a href="/vici/reports" class="sub-nav-item">Call Reports</a>
    <a href="/vici/lead-flow" class="sub-nav-item">Lead Flow</a>
    <a href="/vici/sync-status" class="sub-nav-item">Sync Status</a>
    <a href="/vici/settings" class="sub-nav-item active">Settings</a>
@endsection

@section('page-header')
    <h1 class="page-title">Vici Settings</h1>
    <div class="breadcrumbs">
        <span>Home</span>
        <span class="breadcrumb-separator">›</span>
        <span>Vici</span>
        <span class="breadcrumb-separator">›</span>
        <span>Settings</span>
    </div>
@endsection

@section('content')
    <div class="card">
        <h2 class="card-title">Vici Configuration</h2>
        
        <div style="display: grid; gap: 20px;">
            <!-- Connection Settings -->
            <div style="padding: 20px; background: #f9fafb; border-radius: 8px;">
                <h3 style="font-weight: 600; margin-bottom: 16px;">Connection Settings</h3>
                <div style="display: grid; gap: 12px;">
                    <div>
                        <label style="display: block; font-size: 0.875rem; font-weight: 500; margin-bottom: 4px;">Server IP</label>
                        <input type="text" value="66.175.219.105" readonly style="width: 100%; padding: 8px; border: 1px solid #e5e7eb; border-radius: 6px; background: white;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.875rem; font-weight: 500; margin-bottom: 4px;">Database</label>
                        <input type="text" value="Q6hdjl67GRigMofv" readonly style="width: 100%; padding: 8px; border: 1px solid #e5e7eb; border-radius: 6px; background: white;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.875rem; font-weight: 500; margin-bottom: 4px;">Status</label>
                        <span style="background: #d1fae5; color: #065f46; padding: 4px 12px; border-radius: 4px; font-size: 0.875rem;">Connected</span>
                    </div>
                </div>
            </div>
            
            <!-- Campaign Mapping -->
            <div style="padding: 20px; background: #f9fafb; border-radius: 8px;">
                <h3 style="font-weight: 600; margin-bottom: 16px;">Campaign Mapping</h3>
                <div style="display: grid; gap: 8px;">
                    <div style="display: flex; justify-content: space-between; padding: 8px; background: white; border-radius: 6px;">
                        <span>Autodial</span>
                        <span style="color: #10b981;">✓ Active</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding: 8px; background: white; border-radius: 6px;">
                        <span>Auto2</span>
                        <span style="color: #6b7280;">Inactive</span>
                    </div>
                </div>
            </div>
            
            <!-- List Configuration -->
            <div style="padding: 20px; background: #f9fafb; border-radius: 8px;">
                <h3 style="font-weight: 600; margin-bottom: 16px;">List Configuration</h3>
                <p style="color: #6b7280; font-size: 0.875rem; margin-bottom: 12px;">Active flow lists: 101-110, 199</p>
                <button class="btn btn-primary">Configure Lists</button>
            </div>
        </div>
    </div>
@endsection












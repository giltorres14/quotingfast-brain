@extends('layouts.app-new')

@section('title', 'Buyer Portal Dashboard - QuotingFast Brain')

@section('subnav')
    <a href="/buyers" class="sub-nav-item active">Dashboard</a>
    <a href="/buyers/directory" class="sub-nav-item">Directory</a>
    <a href="/buyers/transfers" class="sub-nav-item">Transfers</a>
    <a href="/buyers/revenue" class="sub-nav-item">Revenue</a>
@endsection

@section('page-header')
    <h1 class="page-title">Buyer Portal Dashboard</h1>
    <div class="breadcrumbs">
        <span>Home</span>
        <span class="breadcrumb-separator">›</span>
        <span>Buyer Portal</span>
        <span class="breadcrumb-separator">›</span>
        <span>Dashboard</span>
    </div>
@endsection

@section('content')
    @php
        // Get buyer statistics
        $totalBuyers = \App\Models\Buyer::count();
        $activeBuyers = \App\Models\Buyer::where('status', 'active')->count();
        
        // Get recent buyers
        $recentBuyers = \App\Models\Buyer::orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
    @endphp
    
    <!-- Metrics Row -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 20px;">
        <div class="card" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: white;">
            <div style="font-size: 0.875rem; opacity: 0.9;">Total Buyers</div>
            <div style="font-size: 2rem; font-weight: 700;">{{ number_format($totalBuyers) }}</div>
            <div style="font-size: 0.75rem; margin-top: 8px; opacity: 0.8;">Registered</div>
        </div>
        
        <div class="card" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white;">
            <div style="font-size: 0.875rem; opacity: 0.9;">Active Buyers</div>
            <div style="font-size: 2rem; font-weight: 700;">{{ number_format($activeBuyers) }}</div>
            <div style="font-size: 0.75rem; margin-top: 8px; opacity: 0.8;">Currently active</div>
        </div>
        
        <div class="card" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); color: white;">
            <div style="font-size: 0.875rem; opacity: 0.9;">Today's Transfers</div>
            <div style="font-size: 2rem; font-weight: 700;">0</div>
            <div style="font-size: 0.75rem; margin-top: 8px; opacity: 0.8;">Last 24 hours</div>
        </div>
        
        <div class="card" style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); color: white;">
            <div style="font-size: 0.875rem; opacity: 0.9;">Revenue Today</div>
            <div style="font-size: 2rem; font-weight: 700;">$0</div>
            <div style="font-size: 0.75rem; margin-top: 8px; opacity: 0.8;">From transfers</div>
        </div>
    </div>
    
    <!-- Recent Buyers -->
    <div class="card">
        <h2 class="card-title">Recent Buyers</h2>
        @if($recentBuyers->count() > 0)
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="border-bottom: 2px solid #e5e7eb;">
                            <th style="padding: 8px; text-align: left; font-weight: 600; color: #6b7280;">Name</th>
                            <th style="padding: 8px; text-align: left; font-weight: 600; color: #6b7280;">Type</th>
                            <th style="padding: 8px; text-align: left; font-weight: 600; color: #6b7280;">Status</th>
                            <th style="padding: 8px; text-align: left; font-weight: 600; color: #6b7280;">Added</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentBuyers as $buyer)
                            <tr style="border-bottom: 1px solid #f3f4f6;">
                                <td style="padding: 8px; font-weight: 500;">{{ $buyer->name }}</td>
                                <td style="padding: 8px;">{{ $buyer->type ?? 'Standard' }}</td>
                                <td style="padding: 8px;">
                                    <span style="background: {{ $buyer->status == 'active' ? '#d1fae5' : '#fee2e2' }}; 
                                                 color: {{ $buyer->status == 'active' ? '#065f46' : '#991b1b' }}; 
                                                 padding: 2px 8px; border-radius: 4px; font-size: 0.75rem;">
                                        {{ ucfirst($buyer->status ?? 'inactive') }}
                                    </span>
                                </td>
                                <td style="padding: 8px; color: #6b7280; font-size: 0.875rem;">
                                    {{ $buyer->created_at->diffForHumans() }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p style="color: #9ca3af; text-align: center; padding: 20px;">No buyers registered yet</p>
        @endif
    </div>
    
    <!-- Quick Actions -->
    <div class="card" style="margin-top: 20px;">
        <h2 class="card-title">Quick Actions</h2>
        <div style="display: flex; gap: 12px; flex-wrap: wrap;">
            <a href="/buyers/directory" class="btn btn-primary">📋 View All Buyers</a>
            <a href="/buyers/transfers" class="btn btn-success">💸 View Transfers</a>
            <a href="/buyers/revenue" class="btn btn-secondary">📊 Revenue Reports</a>
        </div>
    </div>
@endsection



@section('title', 'Buyer Portal Dashboard - QuotingFast Brain')

@section('subnav')
    <a href="/buyers" class="sub-nav-item active">Dashboard</a>
    <a href="/buyers/directory" class="sub-nav-item">Directory</a>
    <a href="/buyers/transfers" class="sub-nav-item">Transfers</a>
    <a href="/buyers/revenue" class="sub-nav-item">Revenue</a>
@endsection

@section('page-header')
    <h1 class="page-title">Buyer Portal Dashboard</h1>
    <div class="breadcrumbs">
        <span>Home</span>
        <span class="breadcrumb-separator">›</span>
        <span>Buyer Portal</span>
        <span class="breadcrumb-separator">›</span>
        <span>Dashboard</span>
    </div>
@endsection

@section('content')
    @php
        // Get buyer statistics
        $totalBuyers = \App\Models\Buyer::count();
        $activeBuyers = \App\Models\Buyer::where('status', 'active')->count();
        
        // Get recent buyers
        $recentBuyers = \App\Models\Buyer::orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
    @endphp
    
    <!-- Metrics Row -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 20px;">
        <div class="card" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: white;">
            <div style="font-size: 0.875rem; opacity: 0.9;">Total Buyers</div>
            <div style="font-size: 2rem; font-weight: 700;">{{ number_format($totalBuyers) }}</div>
            <div style="font-size: 0.75rem; margin-top: 8px; opacity: 0.8;">Registered</div>
        </div>
        
        <div class="card" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white;">
            <div style="font-size: 0.875rem; opacity: 0.9;">Active Buyers</div>
            <div style="font-size: 2rem; font-weight: 700;">{{ number_format($activeBuyers) }}</div>
            <div style="font-size: 0.75rem; margin-top: 8px; opacity: 0.8;">Currently active</div>
        </div>
        
        <div class="card" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); color: white;">
            <div style="font-size: 0.875rem; opacity: 0.9;">Today's Transfers</div>
            <div style="font-size: 2rem; font-weight: 700;">0</div>
            <div style="font-size: 0.75rem; margin-top: 8px; opacity: 0.8;">Last 24 hours</div>
        </div>
        
        <div class="card" style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); color: white;">
            <div style="font-size: 0.875rem; opacity: 0.9;">Revenue Today</div>
            <div style="font-size: 2rem; font-weight: 700;">$0</div>
            <div style="font-size: 0.75rem; margin-top: 8px; opacity: 0.8;">From transfers</div>
        </div>
    </div>
    
    <!-- Recent Buyers -->
    <div class="card">
        <h2 class="card-title">Recent Buyers</h2>
        @if($recentBuyers->count() > 0)
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="border-bottom: 2px solid #e5e7eb;">
                            <th style="padding: 8px; text-align: left; font-weight: 600; color: #6b7280;">Name</th>
                            <th style="padding: 8px; text-align: left; font-weight: 600; color: #6b7280;">Type</th>
                            <th style="padding: 8px; text-align: left; font-weight: 600; color: #6b7280;">Status</th>
                            <th style="padding: 8px; text-align: left; font-weight: 600; color: #6b7280;">Added</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentBuyers as $buyer)
                            <tr style="border-bottom: 1px solid #f3f4f6;">
                                <td style="padding: 8px; font-weight: 500;">{{ $buyer->name }}</td>
                                <td style="padding: 8px;">{{ $buyer->type ?? 'Standard' }}</td>
                                <td style="padding: 8px;">
                                    <span style="background: {{ $buyer->status == 'active' ? '#d1fae5' : '#fee2e2' }}; 
                                                 color: {{ $buyer->status == 'active' ? '#065f46' : '#991b1b' }}; 
                                                 padding: 2px 8px; border-radius: 4px; font-size: 0.75rem;">
                                        {{ ucfirst($buyer->status ?? 'inactive') }}
                                    </span>
                                </td>
                                <td style="padding: 8px; color: #6b7280; font-size: 0.875rem;">
                                    {{ $buyer->created_at->diffForHumans() }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p style="color: #9ca3af; text-align: center; padding: 20px;">No buyers registered yet</p>
        @endif
    </div>
    
    <!-- Quick Actions -->
    <div class="card" style="margin-top: 20px;">
        <h2 class="card-title">Quick Actions</h2>
        <div style="display: flex; gap: 12px; flex-wrap: wrap;">
            <a href="/buyers/directory" class="btn btn-primary">📋 View All Buyers</a>
            <a href="/buyers/transfers" class="btn btn-success">💸 View Transfers</a>
            <a href="/buyers/revenue" class="btn btn-secondary">📊 Revenue Reports</a>
        </div>
    </div>
@endsection



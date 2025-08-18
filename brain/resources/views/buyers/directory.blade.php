@extends('layouts.app-new')

@section('title', 'Buyer Directory - QuotingFast Brain')

@section('subnav')
    <a href="/buyers" class="sub-nav-item">Dashboard</a>
    <a href="/buyers/directory" class="sub-nav-item active">Directory</a>
    <a href="/buyers/transfers" class="sub-nav-item">Transfers</a>
    <a href="/buyers/revenue" class="sub-nav-item">Revenue</a>
@endsection

@section('page-header')
    <h1 class="page-title">Buyer Directory</h1>
    <div class="breadcrumbs">
        <span>Home</span>
        <span class="breadcrumb-separator">›</span>
        <span>Buyer Portal</span>
        <span class="breadcrumb-separator">›</span>
        <span>Directory</span>
    </div>
@endsection

@section('content')
    @php
        $buyers = \App\Models\Buyer::orderBy('name')->get();
    @endphp
    
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2 class="card-title" style="margin: 0;">All Buyers</h2>
            <button class="btn btn-primary">+ Add Buyer</button>
        </div>
        
        @if($buyers->count() > 0)
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="border-bottom: 2px solid #e5e7eb;">
                            <th style="padding: 12px; text-align: left; font-weight: 600; color: #6b7280;">Name</th>
                            <th style="padding: 12px; text-align: left; font-weight: 600; color: #6b7280;">Type</th>
                            <th style="padding: 12px; text-align: left; font-weight: 600; color: #6b7280;">Status</th>
                            <th style="padding: 12px; text-align: left; font-weight: 600; color: #6b7280;">Contact</th>
                            <th style="padding: 12px; text-align: left; font-weight: 600; color: #6b7280;">Added</th>
                            <th style="padding: 12px; text-align: left; font-weight: 600; color: #6b7280;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($buyers as $buyer)
                            <tr style="border-bottom: 1px solid #f3f4f6;">
                                <td style="padding: 12px; font-weight: 500;">{{ $buyer->name }}</td>
                                <td style="padding: 12px;">{{ $buyer->type ?? 'Standard' }}</td>
                                <td style="padding: 12px;">
                                    <span style="background: {{ $buyer->status == 'active' ? '#d1fae5' : '#fee2e2' }}; 
                                                 color: {{ $buyer->status == 'active' ? '#065f46' : '#991b1b' }}; 
                                                 padding: 4px 12px; border-radius: 4px; font-size: 0.75rem; font-weight: 600;">
                                        {{ ucfirst($buyer->status ?? 'inactive') }}
                                    </span>
                                </td>
                                <td style="padding: 12px; color: #6b7280;">{{ $buyer->email ?? 'N/A' }}</td>
                                <td style="padding: 12px; color: #6b7280; font-size: 0.875rem;">
                                    {{ $buyer->created_at->format('M d, Y') }}
                                </td>
                                <td style="padding: 12px;">
                                    <button class="btn btn-secondary" style="font-size: 0.875rem; padding: 4px 12px;">Edit</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div style="text-align: center; padding: 60px 20px; background: #f9fafb; border-radius: 8px;">
                <div style="font-size: 3rem; margin-bottom: 16px;">👥</div>
                <h3 style="font-weight: 600; margin-bottom: 8px;">No Buyers Yet</h3>
                <p style="color: #6b7280;">Add your first buyer to start managing transfers</p>
                <button class="btn btn-primary" style="margin-top: 16px;">Add First Buyer</button>
            </div>
        @endif
    </div>
@endsection



@section('title', 'Buyer Directory - QuotingFast Brain')

@section('subnav')
    <a href="/buyers" class="sub-nav-item">Dashboard</a>
    <a href="/buyers/directory" class="sub-nav-item active">Directory</a>
    <a href="/buyers/transfers" class="sub-nav-item">Transfers</a>
    <a href="/buyers/revenue" class="sub-nav-item">Revenue</a>
@endsection

@section('page-header')
    <h1 class="page-title">Buyer Directory</h1>
    <div class="breadcrumbs">
        <span>Home</span>
        <span class="breadcrumb-separator">›</span>
        <span>Buyer Portal</span>
        <span class="breadcrumb-separator">›</span>
        <span>Directory</span>
    </div>
@endsection

@section('content')
    @php
        $buyers = \App\Models\Buyer::orderBy('name')->get();
    @endphp
    
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2 class="card-title" style="margin: 0;">All Buyers</h2>
            <button class="btn btn-primary">+ Add Buyer</button>
        </div>
        
        @if($buyers->count() > 0)
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="border-bottom: 2px solid #e5e7eb;">
                            <th style="padding: 12px; text-align: left; font-weight: 600; color: #6b7280;">Name</th>
                            <th style="padding: 12px; text-align: left; font-weight: 600; color: #6b7280;">Type</th>
                            <th style="padding: 12px; text-align: left; font-weight: 600; color: #6b7280;">Status</th>
                            <th style="padding: 12px; text-align: left; font-weight: 600; color: #6b7280;">Contact</th>
                            <th style="padding: 12px; text-align: left; font-weight: 600; color: #6b7280;">Added</th>
                            <th style="padding: 12px; text-align: left; font-weight: 600; color: #6b7280;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($buyers as $buyer)
                            <tr style="border-bottom: 1px solid #f3f4f6;">
                                <td style="padding: 12px; font-weight: 500;">{{ $buyer->name }}</td>
                                <td style="padding: 12px;">{{ $buyer->type ?? 'Standard' }}</td>
                                <td style="padding: 12px;">
                                    <span style="background: {{ $buyer->status == 'active' ? '#d1fae5' : '#fee2e2' }}; 
                                                 color: {{ $buyer->status == 'active' ? '#065f46' : '#991b1b' }}; 
                                                 padding: 4px 12px; border-radius: 4px; font-size: 0.75rem; font-weight: 600;">
                                        {{ ucfirst($buyer->status ?? 'inactive') }}
                                    </span>
                                </td>
                                <td style="padding: 12px; color: #6b7280;">{{ $buyer->email ?? 'N/A' }}</td>
                                <td style="padding: 12px; color: #6b7280; font-size: 0.875rem;">
                                    {{ $buyer->created_at->format('M d, Y') }}
                                </td>
                                <td style="padding: 12px;">
                                    <button class="btn btn-secondary" style="font-size: 0.875rem; padding: 4px 12px;">Edit</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div style="text-align: center; padding: 60px 20px; background: #f9fafb; border-radius: 8px;">
                <div style="font-size: 3rem; margin-bottom: 16px;">👥</div>
                <h3 style="font-weight: 600; margin-bottom: 8px;">No Buyers Yet</h3>
                <p style="color: #6b7280;">Add your first buyer to start managing transfers</p>
                <button class="btn btn-primary" style="margin-top: 16px;">Add First Buyer</button>
            </div>
        @endif
    </div>
@endsection



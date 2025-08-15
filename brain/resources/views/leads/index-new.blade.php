@extends('layouts.app')

@section('title', 'All Leads')

@section('styles')
<style>
    /* Stats Grid */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }
    
    .stat-card {
        background: white;
        padding: 1.5rem;
        border-radius: 12px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        text-align: center;
    }
    
    .stat-number {
        font-size: 2.5rem;
        font-weight: bold;
        margin-bottom: 0.5rem;
    }
    
    .stat-label {
        color: #6b7280;
        font-size: 1rem;
        font-weight: 500;
    }
    
    /* Search Section */
    .search-section {
        background: white;
        padding: 1.5rem;
        border-radius: 12px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        margin-bottom: 2rem;
    }
    
    .search-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        align-items: end;
    }
    
    .form-group {
        display: flex;
        flex-direction: column;
    }
    
    .form-label {
        font-size: 0.875rem;
        font-weight: 500;
        color: #374151;
        margin-bottom: 0.25rem;
    }
    
    .form-input, .form-select {
        padding: 0.5rem;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        font-size: 0.875rem;
    }
    
    /* Lead Cards */
    .lead-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.08);
        margin-bottom: 1rem;
        transition: all 0.2s;
    }
    
    .lead-card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.12);
        transform: translateY(-2px);
    }
    
    .lead-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 1rem;
    }
    
    .lead-name {
        font-size: 1.25rem;
        font-weight: 600;
        color: #1f2937;
    }
    
    .lead-meta {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 0.75rem;
        font-size: 0.875rem;
        color: #6b7280;
    }
    
    .lead-actions {
        display: flex;
        gap: 0.5rem;
        margin-top: 1rem;
    }
    
    .btn {
        padding: 0.5rem 1rem;
        border-radius: 6px;
        font-size: 0.875rem;
        font-weight: 500;
        text-decoration: none;
        transition: all 0.2s;
        border: none;
        cursor: pointer;
    }
    
    .btn-primary {
        background: #4A90E2;
        color: white;
    }
    
    .btn-primary:hover {
        background: #357ABD;
    }
    
    .btn-secondary {
        background: #e5e7eb;
        color: #4b5563;
    }
    
    .btn-secondary:hover {
        background: #d1d5db;
    }
    
    /* Badges */
    .badge {
        padding: 0.25rem 0.75rem;
        border-radius: 1rem;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        display: inline-block;
    }
    
    .badge-auto {
        background: #dbeafe;
        color: #1e40af;
    }
    
    .badge-home {
        background: #dcfce7;
        color: #166534;
    }
    
    /* Pagination */
    .pagination {
        display: flex;
        justify-content: center;
        gap: 0.5rem;
        margin-top: 2rem;
    }
    
    .pagination a, .pagination span {
        padding: 0.5rem 0.75rem;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        color: #4b5563;
        text-decoration: none;
        font-size: 0.875rem;
    }
    
    .pagination a:hover {
        background: #f3f4f6;
    }
    
    .pagination .active {
        background: #4A90E2;
        color: white;
        border-color: #4A90E2;
    }
</style>
@endsection

@section('content')
<div class="page-header" style="margin-bottom: 2rem;">
    <h1 style="font-size: 2rem; font-weight: 700; color: #1f2937;">All Leads</h1>
    <p style="color: #6b7280; margin-top: 0.5rem;">Manage and track your auto insurance leads</p>
</div>

<!-- Statistics Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-number" style="color: #4A90E2;">{{ number_format($stats['total_leads'] ?? 0) }}</div>
        <div class="stat-label">Total Leads</div>
    </div>
    <div class="stat-card">
        <div class="stat-number" style="color: #10b981;">{{ number_format($stats['today_leads'] ?? 0) }}</div>
        <div class="stat-label">Today's Leads</div>
    </div>
    <div class="stat-card">
        <div class="stat-number" style="color: #f59e0b;">{{ number_format($stats['vici_sent'] ?? 0) }}</div>
        <div class="stat-label">Sent to Vici</div>
    </div>
    <div class="stat-card">
        <div class="stat-number" style="color: #8b5cf6;">{{ number_format($stats['allstate_sent'] ?? 0) }}</div>
        <div class="stat-label">Sent to Allstate</div>
    </div>
</div>

<!-- Search and Filters -->
<div class="search-section">
    <form method="GET" action="/leads">
        <div class="search-grid">
            <div class="form-group">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-input" 
                       placeholder="Name, phone, email, city, state, zip" 
                       value="{{ $search ?? '' }}">
            </div>
            
            <div class="form-group">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="all">All Statuses</option>
                    @foreach($statuses as $statusOption)
                        <option value="{{ $statusOption }}" 
                                {{ ($status ?? '') === $statusOption ? 'selected' : '' }}>
                            {{ ucfirst($statusOption) }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="form-group">
                <label class="form-label">Source</label>
                <select name="source" class="form-select">
                    <option value="all">All Sources</option>
                    @foreach($sources as $sourceOption)
                        <option value="{{ $sourceOption }}" 
                                {{ ($source ?? '') === $sourceOption ? 'selected' : '' }}>
                            {{ ucfirst($sourceOption) }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="form-group">
                <label class="form-label">State</label>
                <select name="state_filter" class="form-select">
                    <option value="all">All States</option>
                    @foreach($states as $stateOption)
                        <option value="{{ $stateOption }}" 
                                {{ ($state_filter ?? '') === $stateOption ? 'selected' : '' }}>
                            {{ $stateOption }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="form-group">
                <label class="form-label">Vici Status</label>
                <select name="vici_status" class="form-select">
                    <option value="all">All</option>
                    <option value="sent" {{ ($vici_status ?? '') === 'sent' ? 'selected' : '' }}>Sent to Vici</option>
                    <option value="not_sent" {{ ($vici_status ?? '') === 'not_sent' ? 'selected' : '' }}>Not Sent</option>
                </select>
            </div>
            
            <div class="form-group">
                <label class="form-label">Date Range</label>
                <select name="date_range" class="form-select">
                    <option value="today" {{ request('date_range') == 'today' ? 'selected' : '' }}>Today</option>
                    <option value="yesterday" {{ request('date_range') == 'yesterday' ? 'selected' : '' }}>Yesterday</option>
                    <option value="last_7_days" {{ request('date_range') == 'last_7_days' ? 'selected' : '' }}>Last 7 days</option>
                    <option value="last_30_days" {{ request('date_range') == 'last_30_days' ? 'selected' : '' }}>Last 30 days</option>
                    <option value="this_month" {{ request('date_range') == 'this_month' ? 'selected' : '' }}>This month</option>
                    <option value="last_month" {{ request('date_range') == 'last_month' ? 'selected' : '' }}>Last month</option>
                </select>
            </div>
            
            <button type="submit" class="btn btn-primary">Filter</button>
            <a href="/leads" class="btn btn-secondary">Clear</a>
        </div>
    </form>
</div>

<!-- Per Page Selector (moved here between search and leads) -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
    <div style="font-size: 0.875rem; color: #6b7280;">
        Showing {{ $leads->count() }} of {{ $leads->total() ?? $leads->count() }} leads
    </div>
    <form method="GET" action="/leads" style="display: flex; align-items: center; gap: 0.5rem;">
        @foreach(request()->except('per_page') as $key => $value)
            @if($value)
                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
            @endif
        @endforeach
        <label style="font-size: 0.875rem; color: #6b7280;">Per page:</label>
        <select name="per_page" class="form-select" style="width: auto;" onchange="this.form.submit()">
            <option value="20" {{ request('per_page') == 20 ? 'selected' : '' }}>20</option>
            <option value="50" {{ request('per_page', 50) == 50 ? 'selected' : '' }}>50</option>
            <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
            <option value="200" {{ request('per_page') == 200 ? 'selected' : '' }}>200</option>
        </select>
    </form>
</div>

<!-- Leads List -->
<div class="leads-container">
    @forelse($leads as $lead)
        <div class="lead-card">
            <div class="lead-header">
                <div>
                    <div class="lead-name">
                        {{ $lead->first_name ?? '' }} {{ $lead->last_name ?? '' }}
                        @if((!isset($lead->first_name) || !$lead->first_name) && (!isset($lead->last_name) || !$lead->last_name))
                            {{ $lead->name ?? 'Unknown Lead' }}
                        @endif
                        @if(isset($lead->external_lead_id) && $lead->external_lead_id)
                            <span style="font-size: 0.8rem; color: #6b7280; font-weight: 400; margin-left: 0.5rem;">
                                #{{ $lead->external_lead_id }}
                            </span>
                        @endif
                        @if($lead->source)
                            @php
                                $sourceColors = [
                                    'SURAJ_BULK' => ['bg' => '#8b5cf6', 'label' => 'Suraj Bulk'],
                                    'LQF_BULK' => ['bg' => '#ec4899', 'label' => 'LQF Bulk'],
                                    'LQF' => ['bg' => '#06b6d4', 'label' => 'LQF'],
                                    'SURAJ' => ['bg' => '#10b981', 'label' => 'Suraj'],
                                    'API' => ['bg' => '#f59e0b', 'label' => 'API'],
                                    'MANUAL' => ['bg' => '#6b7280', 'label' => 'Manual'],
                                ];
                                $sourceInfo = $sourceColors[$lead->source] ?? ['bg' => '#6b7280', 'label' => $lead->source];
                            @endphp
                            <span style="
                                background: {{ $sourceInfo['bg'] }};
                                color: white;
                                padding: 2px 8px;
                                border-radius: 12px;
                                font-size: 10px;
                                font-weight: 600;
                                text-transform: uppercase;
                                letter-spacing: 0.3px;
                                margin-left: 8px;
                                display: inline-block;
                            ">
                                {{ $sourceInfo['label'] }}
                            </span>
                        @endif
                    </div>
                    <div style="display: flex; gap: 0.5rem; margin-top: 0.5rem;">
                        @if(isset($lead->type) && $lead->type)
                            <span class="badge badge-{{ strtolower($lead->type) }}">
                                {{ ucfirst($lead->type) }}
                            </span>
                        @endif
                        @if(isset($lead->campaign_id) && $lead->campaign_id)
                            <span class="badge" style="background: #fef3c7; color: #92400e;">
                                Campaign #{{ $lead->campaign_id }}
                            </span>
                        @endif
                    </div>
                </div>
                <div style="text-align: right; font-size: 0.875rem; color: #6b7280;">
                    @if($lead->created_at)
                        {{ \Carbon\Carbon::parse($lead->created_at)->format('M d, Y g:i A') }}
                    @endif
                </div>
            </div>
            
            <div class="lead-meta">
                <div>📞 {{ $lead->phone ?? 'No phone' }}</div>
                <div>📧 {{ $lead->email ?? 'No email' }}</div>
                <div>📍 {{ $lead->city ?? '' }}{{ $lead->city && $lead->state ? ', ' : '' }}{{ $lead->state ?? '' }} {{ $lead->zip_code ?? '' }}</div>
                @if($lead->sent_to_vici)
                    <div style="color: #10b981;">✅ Sent to Vici</div>
                @endif
            </div>
            
            <div class="lead-actions">
                <a href="/agent/lead-display/{{ $lead->id }}" class="btn btn-primary">👁️ View</a>
                <a href="/agent/lead-display/{{ $lead->id }}?mode=edit" class="btn btn-secondary">✏️ Edit</a>
                @if(isset($lead->payload) && $lead->payload)
                    <button class="btn btn-secondary" onclick="alert('Payload feature coming soon')">💾 Payload</button>
                @endif
            </div>
        </div>
    @empty
        <div style="text-align: center; padding: 3rem; background: white; border-radius: 12px;">
            <p style="color: #6b7280; font-size: 1.125rem;">No leads found matching your criteria.</p>
        </div>
    @endforelse
</div>

<!-- Pagination -->
@if(method_exists($leads, 'links'))
    <div class="pagination">
        {{ $leads->appends(request()->query())->links() }}
    </div>
@endif
@endsection

@section('scripts')
<script>
    // Auto-refresh stats every 30 seconds
    setInterval(function() {
        // In production, this would fetch updated stats via AJAX
        console.log('Stats refresh triggered');
    }, 30000);
</script>
@endsection


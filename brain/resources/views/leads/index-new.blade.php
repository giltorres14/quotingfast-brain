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

<!-- Date Range Selector -->
<div style="background: white; padding: 1rem; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 1.5rem;">
    <div style="display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;">
        <label style="font-weight: 600; color: #374151;">Stats Period:</label>
        <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
            <button onclick="updateStats('today')" class="period-btn active" data-period="today">Today</button>
            <button onclick="updateStats('yesterday')" class="period-btn" data-period="yesterday">Yesterday</button>
            <button onclick="updateStats('last7')" class="period-btn" data-period="last7">Last 7 Days</button>
            <button onclick="updateStats('last30')" class="period-btn" data-period="last30">Last 30 Days</button>
            <button onclick="showCustomDatePicker()" class="period-btn" data-period="custom">Custom Range</button>
        </div>
        <div id="customDateRange" style="display: none; gap: 0.5rem; align-items: center;">
            <input type="date" id="startDate" class="form-input" style="padding: 0.375rem; border: 1px solid #d1d5db; border-radius: 6px;">
            <span>to</span>
            <input type="date" id="endDate" class="form-input" style="padding: 0.375rem; border: 1px solid #d1d5db; border-radius: 6px;">
            <button onclick="applyCustomRange()" class="btn btn-primary" style="padding: 0.375rem 1rem;">Apply</button>
        </div>
    </div>
</div>

<!-- Statistics Cards (Default to Today) -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-number" id="stat-total" style="color: #4A90E2;">{{ number_format($stats['today_leads'] ?? 0) }}</div>
        <div class="stat-label">New Leads</div>
    </div>
    <div class="stat-card">
        <div class="stat-number" id="stat-vici" style="color: #10b981;">{{ number_format($stats['today_vici'] ?? 0) }}</div>
        <div class="stat-label">Sent to Vici</div>
    </div>
    <div class="stat-card">
        <div class="stat-number" id="stat-stuck" style="color: #f59e0b;">{{ number_format($stats['today_stuck'] ?? 0) }}</div>
        <div class="stat-label">Stuck in Queue</div>
    </div>
    <div class="stat-card">
        <div class="stat-number" id="stat-conversion" style="color: #8b5cf6;">0%</div>
        <div class="stat-label">Conversion Rate</div>
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
                <div>📍 {{ $lead->city ?? '' }}{{ $lead->city && $lead->state ? ', ' : '' }}{{ $lead->state ?? '' }} {{ $lead->zip_code ?? '' }}</div>
                @if($lead->vici_list_id)
                    <div style="color: #10b981;">✅ In Vici (List {{ $lead->vici_list_id }})</div>
                @else
                    <div style="color: #f59e0b;">⏳ Not in Dialer</div>
                @endif
            </div>
            
            <div class="lead-actions">
                <a href="/agent/lead/{{ $lead->id }}?mode=view" class="btn btn-primary">👁️ View</a>
                <a href="/agent/lead/{{ $lead->id }}?mode=edit" class="btn btn-secondary">✏️ Edit</a>
                @if(isset($lead->payload) && $lead->payload)
                    <button class="btn btn-secondary" onclick='showPayload(@json($lead))'>💾 Payload</button>
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
<style>
    .period-btn {
        padding: 0.375rem 1rem;
        background: #e5e7eb;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        color: #374151;
        cursor: pointer;
        font-size: 0.875rem;
        transition: all 0.2s;
    }
    .period-btn:hover {
        background: #d1d5db;
    }
    .period-btn.active {
        background: #4A90E2;
        color: white;
        border-color: #4A90E2;
    }
</style>
<script>
    // Get current period from URL or default to today
    const urlParams = new URLSearchParams(window.location.search);
    let currentPeriod = urlParams.get('period') || '{{ $stats["current_period"] ?? "today" }}';
    
    // Initialize correct button on page load
    document.addEventListener('DOMContentLoaded', function() {
        // Get period from URL or use current
        const urlParams = new URLSearchParams(window.location.search);
        const activePeriod = urlParams.get('period') || currentPeriod;
        
        document.querySelectorAll('.period-btn').forEach(btn => {
            btn.classList.remove('active');
            if (btn.dataset.period === activePeriod) {
                btn.classList.add('active');
            }
        });
        
        // Update currentPeriod to match URL
        currentPeriod = activePeriod;
    });
    
    function updateStats(period) {
        // Update active button
        document.querySelectorAll('.period-btn').forEach(btn => {
            btn.classList.remove('active');
            if (btn.dataset.period === period) {
                btn.classList.add('active');
            }
        });
        
        // Show loading state
        document.querySelectorAll('.stat-value').forEach(el => {
            el.textContent = 'Loading...';
        });
        
        // Reload page with new period
        window.location.href = '/leads?period=' + period;
    }
        });
        
        // Hide custom date range if not custom
        if (period !== 'custom') {
            document.getElementById('customDateRange').style.display = 'none';
        }
        
        currentPeriod = period;
        
        // Calculate dates based on period
        const now = new Date();
        let startDate, endDate, label;
        
        switch(period) {
            case 'today':
                startDate = new Date(now.getFullYear(), now.getMonth(), now.getDate());
                endDate = new Date(now.getFullYear(), now.getMonth(), now.getDate() + 1);
                label = "Today's";
                break;
            case 'yesterday':
                startDate = new Date(now.getFullYear(), now.getMonth(), now.getDate() - 1);
                endDate = new Date(now.getFullYear(), now.getMonth(), now.getDate());
                label = "Yesterday's";
                break;
            case 'last7':
                startDate = new Date(now.getFullYear(), now.getMonth(), now.getDate() - 7);
                endDate = new Date(now.getFullYear(), now.getMonth(), now.getDate() + 1);
                label = "Last 7 Days";
                break;
            case 'last30':
                startDate = new Date(now.getFullYear(), now.getMonth(), now.getDate() - 30);
                endDate = new Date(now.getFullYear(), now.getMonth(), now.getDate() + 1);
                label = "Last 30 Days";
                break;
            default:
                return;
        }
        
        fetchStats(startDate, endDate, label);
    }
    
    function showCustomDatePicker() {
        const customRange = document.getElementById('customDateRange');
        customRange.style.display = customRange.style.display === 'none' ? 'flex' : 'none';
        
        // Set default dates
        const now = new Date();
        const weekAgo = new Date(now.getFullYear(), now.getMonth(), now.getDate() - 7);
        document.getElementById('startDate').value = weekAgo.toISOString().split('T')[0];
        document.getElementById('endDate').value = now.toISOString().split('T')[0];
    }
    
    function applyCustomRange() {
        const startDate = new Date(document.getElementById('startDate').value);
        const endDate = new Date(document.getElementById('endDate').value);
        endDate.setDate(endDate.getDate() + 1); // Include end date
        
        const label = `${startDate.toLocaleDateString()} - ${document.getElementById('endDate').value}`;
        fetchStats(startDate, endDate, label);
    }
    
    function fetchStats(startDate, endDate, label) {
        // Show loading state immediately
        document.getElementById('stat-total').innerHTML = '<span style="color: #6b7280;">Loading...</span>';
        document.getElementById('stat-vici').innerHTML = '<span style="color: #6b7280;">Loading...</span>';
        document.getElementById('stat-stuck').innerHTML = '<span style="color: #6b7280;">Loading...</span>';
        document.getElementById('stat-conversion').innerHTML = '<span style="color: #6b7280;">Loading...</span>';
        
        // Update labels
        document.querySelectorAll('.stat-label').forEach(el => {
            const text = el.textContent;
            if (text.includes('Leads') && !text.includes('Vici') && !text.includes('Queue')) {
                el.textContent = label + ' Leads';
            } else if (text.includes('Vici')) {
                el.textContent = 'Sent to Vici (' + label + ')';
            } else if (text.includes('Queue')) {
                el.textContent = 'Stuck in Queue (' + label + ')';
            }
        });
        
        // Always reload page with new parameters to get fresh data
        const overlay = document.createElement('div');
        overlay.style.cssText = 'position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(255,255,255,0.9);display:flex;align-items:center;justify-content:center;z-index:9999';
        overlay.innerHTML = '<div style="text-align:center"><div style="font-size:24px;color:#4A90E2;margin-bottom:10px">Loading Stats...</div><div style="color:#6b7280">Fetching ' + label + ' data</div></div>';
        document.body.appendChild(overlay);
        
        // Update URL without reloading
        const startStr = startDate.toISOString().split('T')[0];
        const endStr = endDate.toISOString().split('T')[0];
        
        // Build URL with all necessary parameters
        const url = new URL(window.location);
        
        // Clear old date params
        url.searchParams.delete('date_from');
        url.searchParams.delete('date_to');
        url.searchParams.delete('period');
        
        // Set new params based on period
        if (currentPeriod === 'custom') {
            url.searchParams.set('date_from', startStr);
            url.searchParams.set('date_to', endStr);
        }
        url.searchParams.set('period', currentPeriod);
        
        // Update URL without reload
        window.history.pushState({}, '', url.toString());
        
        // Fetch data via AJAX instead of page reload
        fetch(url.toString(), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.text())
        .then(html => {
            // Parse the response and update stats
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            
            // Update stat numbers
            const newTotal = doc.getElementById('stat-total');
            const newVici = doc.getElementById('stat-vici');
            const newStuck = doc.getElementById('stat-stuck');
            const newConversion = doc.getElementById('stat-conversion');
            
            if (newTotal) document.getElementById('stat-total').textContent = newTotal.textContent;
            if (newVici) document.getElementById('stat-vici').textContent = newVici.textContent;
            if (newStuck) document.getElementById('stat-stuck').textContent = newStuck.textContent;
            if (newConversion) document.getElementById('stat-conversion').textContent = newConversion.textContent;
            
            // Update lead cards
            const leadGrid = doc.querySelector('.lead-grid');
            if (leadGrid) {
                document.querySelector('.lead-grid').innerHTML = leadGrid.innerHTML;
            }
            
            // Remove loading overlay
            const overlay = document.querySelector('div[style*="position:fixed"]');
            if (overlay) overlay.remove();
        })
        .catch(error => {
            console.error('Error fetching stats:', error);
            // Remove loading overlay
            const overlay = document.querySelector('div[style*="position:fixed"]');
            if (overlay) overlay.remove();
            
            // Show error message
            alert('Error loading stats. Please try again.');
        });
    }
    
    function updateStatsLocally(startDate, endDate) {
        // This is a fallback - actual stats should come from server
        const daysDiff = Math.ceil((endDate - startDate) / (1000 * 60 * 60 * 24));
        const multiplier = daysDiff;
        
        // Use current values as base and multiply
        const currentTotal = parseInt(document.getElementById('stat-total').textContent.replace(/,/g, '')) || 0;
        const currentVici = parseInt(document.getElementById('stat-vici').textContent.replace(/,/g, '')) || 0;
        const currentStuck = parseInt(document.getElementById('stat-stuck').textContent.replace(/,/g, '')) || 0;
        
        if (currentPeriod === 'today') {
            // Already showing today's stats
            return;
        }
        
        // Estimate based on days
        document.getElementById('stat-total').textContent = (currentTotal * multiplier).toLocaleString();
        document.getElementById('stat-vici').textContent = (currentVici * multiplier).toLocaleString();
        document.getElementById('stat-stuck').textContent = (currentStuck * multiplier).toLocaleString();
        
        const total = currentTotal * multiplier;
        const vici = currentVici * multiplier;
        const rate = total > 0 ? ((vici / total) * 100).toFixed(1) : 0;
        document.getElementById('stat-conversion').textContent = rate + '%';
    }
    
    // Auto-refresh disabled - use manual refresh button instead
    
    
    function refreshDashboard() {
        // Show loading indicator
        const overlay = document.createElement('div');
        overlay.style.cssText = 'position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(255,255,255,0.9);display:flex;align-items:center;justify-content:center;z-index:9999';
        overlay.innerHTML = '<div style="text-align:center"><div style="font-size:24px;color:#10b981;margin-bottom:10px">🔄 Refreshing Dashboard...</div><div style="color:#6b7280">Loading latest data</div></div>';
        document.body.appendChild(overlay);
        
        // Reload the page to get fresh data
        setTimeout(() => {
            window.location.reload();
        }, 500);
    }
    
    // Show payload in modal
    function showPayload(lead) {
        // Create comprehensive payload object
        const fullData = {
            lead_info: {
                id: lead.id,
                external_lead_id: lead.external_lead_id,
                leadid_code: lead.leadid_code,
                jangle_lead_id: lead.jangle_lead_id,
                name: lead.name,
                first_name: lead.first_name,
                last_name: lead.last_name,
                phone: lead.phone,
                email: lead.email,
                source: lead.source,
                vendor_name: lead.vendor_name,
                buyer_name: lead.buyer_name,
                created_at: lead.created_at,
                opt_in_date: lead.opt_in_date
            },
            location: {
                address: lead.address,
                city: lead.city,
                state: lead.state,
                zip_code: lead.zip_code
            },
            tracking: {
                ip_address: lead.ip_address,
                user_agent: lead.user_agent,
                landing_page_url: lead.landing_page_url,
                tcpa_compliant: lead.tcpa_compliant,
                tcpa_consent_text: lead.tcpa_consent_text,
                trusted_form_cert: lead.trusted_form_cert
            },
            vici_info: {
                vici_list_id: lead.vici_list_id,
                sent_to_vici: lead.vici_list_id ? true : false
            },
            original_payload: lead.payload ? (typeof lead.payload === 'string' ? JSON.parse(lead.payload) : lead.payload) : null,
            drivers: lead.drivers,
            vehicles: lead.vehicles,
            current_policy: lead.current_policy,
            requested_policy: lead.requested_policy,
            meta: lead.meta
        };
        
        const formatted = JSON.stringify(fullData, null, 2);
        const modal = document.createElement('div');
        modal.style.cssText = 'position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);display:flex;align-items:center;justify-content:center;z-index:9999';
        modal.innerHTML = `
            <div style="background:white;padding:2rem;border-radius:8px;max-width:900px;width:90%;max-height:80vh;overflow:auto;position:relative">
                <h2 style="margin-bottom:1rem">Complete Lead Data & Payload</h2>
                <button onclick="this.closest('div').parentElement.remove()" style="position:absolute;top:1rem;right:1rem;background:#ef4444;color:white;border:none;padding:0.5rem 1rem;border-radius:4px;cursor:pointer">✕ Close</button>
                <div style="margin-bottom:1rem">
                    <button onclick="navigator.clipboard.writeText(${JSON.stringify(formatted).replace(/"/g, '&quot;')}); this.textContent='✓ Copied!'; setTimeout(()=>this.textContent='📋 Copy All',2000)" style="background:#10b981;color:white;border:none;padding:0.5rem 1rem;border-radius:4px;cursor:pointer">📋 Copy All</button>
                </div>
                <pre style="background:#f3f4f6;padding:1rem;border-radius:4px;overflow:auto;font-size:12px">${formatted}</pre>
            </div>
        `;
        document.body.appendChild(modal);
        modal.onclick = function(e) { if(e.target === modal) modal.remove(); };
    }
</script>
@endsection


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

<!-- Date Range Selector -->
<div style="background: white; padding: 1rem; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 1.5rem;">
    <div style="display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;">
        <label style="font-weight: 600; color: #374151;">Stats Period:</label>
        <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
            <button onclick="updateStats('today')" class="period-btn active" data-period="today">Today</button>
            <button onclick="updateStats('yesterday')" class="period-btn" data-period="yesterday">Yesterday</button>
            <button onclick="updateStats('last7')" class="period-btn" data-period="last7">Last 7 Days</button>
            <button onclick="updateStats('last30')" class="period-btn" data-period="last30">Last 30 Days</button>
            <button onclick="showCustomDatePicker()" class="period-btn" data-period="custom">Custom Range</button>
        </div>
        <div id="customDateRange" style="display: none; gap: 0.5rem; align-items: center;">
            <input type="date" id="startDate" class="form-input" style="padding: 0.375rem; border: 1px solid #d1d5db; border-radius: 6px;">
            <span>to</span>
            <input type="date" id="endDate" class="form-input" style="padding: 0.375rem; border: 1px solid #d1d5db; border-radius: 6px;">
            <button onclick="applyCustomRange()" class="btn btn-primary" style="padding: 0.375rem 1rem;">Apply</button>
        </div>
    </div>
</div>

<!-- Statistics Cards (Default to Today) -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-number" id="stat-total" style="color: #4A90E2;">{{ number_format($stats['today_leads'] ?? 0) }}</div>
        <div class="stat-label">New Leads</div>
    </div>
    <div class="stat-card">
        <div class="stat-number" id="stat-vici" style="color: #10b981;">{{ number_format($stats['today_vici'] ?? 0) }}</div>
        <div class="stat-label">Sent to Vici</div>
    </div>
    <div class="stat-card">
        <div class="stat-number" id="stat-stuck" style="color: #f59e0b;">{{ number_format($stats['today_stuck'] ?? 0) }}</div>
        <div class="stat-label">Stuck in Queue</div>
    </div>
    <div class="stat-card">
        <div class="stat-number" id="stat-conversion" style="color: #8b5cf6;">0%</div>
        <div class="stat-label">Conversion Rate</div>
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
                <div>📍 {{ $lead->city ?? '' }}{{ $lead->city && $lead->state ? ', ' : '' }}{{ $lead->state ?? '' }} {{ $lead->zip_code ?? '' }}</div>
                @if($lead->vici_list_id)
                    <div style="color: #10b981;">✅ In Vici (List {{ $lead->vici_list_id }})</div>
                @else
                    <div style="color: #f59e0b;">⏳ Not in Dialer</div>
                @endif
            </div>
            
            <div class="lead-actions">
                <a href="/agent/lead/{{ $lead->id }}?mode=view" class="btn btn-primary">👁️ View</a>
                <a href="/agent/lead/{{ $lead->id }}?mode=edit" class="btn btn-secondary">✏️ Edit</a>
                @if(isset($lead->payload) && $lead->payload)
                    <button class="btn btn-secondary" onclick='showPayload(@json($lead))'>💾 Payload</button>
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
<style>
    .period-btn {
        padding: 0.375rem 1rem;
        background: #e5e7eb;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        color: #374151;
        cursor: pointer;
        font-size: 0.875rem;
        transition: all 0.2s;
    }
    .period-btn:hover {
        background: #d1d5db;
    }
    .period-btn.active {
        background: #4A90E2;
        color: white;
        border-color: #4A90E2;
    }
</style>
<script>
    // Get current period from URL or default to today
    const urlParams = new URLSearchParams(window.location.search);
    let currentPeriod = urlParams.get('period') || '{{ $stats["current_period"] ?? "today" }}';
    
    // Initialize correct button on page load
    document.addEventListener('DOMContentLoaded', function() {
        // Get period from URL or use current
        const urlParams = new URLSearchParams(window.location.search);
        const activePeriod = urlParams.get('period') || currentPeriod;
        
        document.querySelectorAll('.period-btn').forEach(btn => {
            btn.classList.remove('active');
            if (btn.dataset.period === activePeriod) {
                btn.classList.add('active');
            }
        });
        
        // Update currentPeriod to match URL
        currentPeriod = activePeriod;
    });
    
    function updateStats(period) {
        // Update active button
        document.querySelectorAll('.period-btn').forEach(btn => {
            btn.classList.remove('active');
            if (btn.dataset.period === period) {
                btn.classList.add('active');
            }
        });
        
        // Show loading state
        document.querySelectorAll('.stat-value').forEach(el => {
            el.textContent = 'Loading...';
        });
        
        // Reload page with new period
        window.location.href = '/leads?period=' + period;
    }
        });
        
        // Hide custom date range if not custom
        if (period !== 'custom') {
            document.getElementById('customDateRange').style.display = 'none';
        }
        
        currentPeriod = period;
        
        // Calculate dates based on period
        const now = new Date();
        let startDate, endDate, label;
        
        switch(period) {
            case 'today':
                startDate = new Date(now.getFullYear(), now.getMonth(), now.getDate());
                endDate = new Date(now.getFullYear(), now.getMonth(), now.getDate() + 1);
                label = "Today's";
                break;
            case 'yesterday':
                startDate = new Date(now.getFullYear(), now.getMonth(), now.getDate() - 1);
                endDate = new Date(now.getFullYear(), now.getMonth(), now.getDate());
                label = "Yesterday's";
                break;
            case 'last7':
                startDate = new Date(now.getFullYear(), now.getMonth(), now.getDate() - 7);
                endDate = new Date(now.getFullYear(), now.getMonth(), now.getDate() + 1);
                label = "Last 7 Days";
                break;
            case 'last30':
                startDate = new Date(now.getFullYear(), now.getMonth(), now.getDate() - 30);
                endDate = new Date(now.getFullYear(), now.getMonth(), now.getDate() + 1);
                label = "Last 30 Days";
                break;
            default:
                return;
        }
        
        fetchStats(startDate, endDate, label);
    }
    
    function showCustomDatePicker() {
        const customRange = document.getElementById('customDateRange');
        customRange.style.display = customRange.style.display === 'none' ? 'flex' : 'none';
        
        // Set default dates
        const now = new Date();
        const weekAgo = new Date(now.getFullYear(), now.getMonth(), now.getDate() - 7);
        document.getElementById('startDate').value = weekAgo.toISOString().split('T')[0];
        document.getElementById('endDate').value = now.toISOString().split('T')[0];
    }
    
    function applyCustomRange() {
        const startDate = new Date(document.getElementById('startDate').value);
        const endDate = new Date(document.getElementById('endDate').value);
        endDate.setDate(endDate.getDate() + 1); // Include end date
        
        const label = `${startDate.toLocaleDateString()} - ${document.getElementById('endDate').value}`;
        fetchStats(startDate, endDate, label);
    }
    
    function fetchStats(startDate, endDate, label) {
        // Show loading state immediately
        document.getElementById('stat-total').innerHTML = '<span style="color: #6b7280;">Loading...</span>';
        document.getElementById('stat-vici').innerHTML = '<span style="color: #6b7280;">Loading...</span>';
        document.getElementById('stat-stuck').innerHTML = '<span style="color: #6b7280;">Loading...</span>';
        document.getElementById('stat-conversion').innerHTML = '<span style="color: #6b7280;">Loading...</span>';
        
        // Update labels
        document.querySelectorAll('.stat-label').forEach(el => {
            const text = el.textContent;
            if (text.includes('Leads') && !text.includes('Vici') && !text.includes('Queue')) {
                el.textContent = label + ' Leads';
            } else if (text.includes('Vici')) {
                el.textContent = 'Sent to Vici (' + label + ')';
            } else if (text.includes('Queue')) {
                el.textContent = 'Stuck in Queue (' + label + ')';
            }
        });
        
        // Always reload page with new parameters to get fresh data
        const overlay = document.createElement('div');
        overlay.style.cssText = 'position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(255,255,255,0.9);display:flex;align-items:center;justify-content:center;z-index:9999';
        overlay.innerHTML = '<div style="text-align:center"><div style="font-size:24px;color:#4A90E2;margin-bottom:10px">Loading Stats...</div><div style="color:#6b7280">Fetching ' + label + ' data</div></div>';
        document.body.appendChild(overlay);
        
        // Update URL without reloading
        const startStr = startDate.toISOString().split('T')[0];
        const endStr = endDate.toISOString().split('T')[0];
        
        // Build URL with all necessary parameters
        const url = new URL(window.location);
        
        // Clear old date params
        url.searchParams.delete('date_from');
        url.searchParams.delete('date_to');
        url.searchParams.delete('period');
        
        // Set new params based on period
        if (currentPeriod === 'custom') {
            url.searchParams.set('date_from', startStr);
            url.searchParams.set('date_to', endStr);
        }
        url.searchParams.set('period', currentPeriod);
        
        // Update URL without reload
        window.history.pushState({}, '', url.toString());
        
        // Fetch data via AJAX instead of page reload
        fetch(url.toString(), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.text())
        .then(html => {
            // Parse the response and update stats
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            
            // Update stat numbers
            const newTotal = doc.getElementById('stat-total');
            const newVici = doc.getElementById('stat-vici');
            const newStuck = doc.getElementById('stat-stuck');
            const newConversion = doc.getElementById('stat-conversion');
            
            if (newTotal) document.getElementById('stat-total').textContent = newTotal.textContent;
            if (newVici) document.getElementById('stat-vici').textContent = newVici.textContent;
            if (newStuck) document.getElementById('stat-stuck').textContent = newStuck.textContent;
            if (newConversion) document.getElementById('stat-conversion').textContent = newConversion.textContent;
            
            // Update lead cards
            const leadGrid = doc.querySelector('.lead-grid');
            if (leadGrid) {
                document.querySelector('.lead-grid').innerHTML = leadGrid.innerHTML;
            }
            
            // Remove loading overlay
            const overlay = document.querySelector('div[style*="position:fixed"]');
            if (overlay) overlay.remove();
        })
        .catch(error => {
            console.error('Error fetching stats:', error);
            // Remove loading overlay
            const overlay = document.querySelector('div[style*="position:fixed"]');
            if (overlay) overlay.remove();
            
            // Show error message
            alert('Error loading stats. Please try again.');
        });
    }
    
    function updateStatsLocally(startDate, endDate) {
        // This is a fallback - actual stats should come from server
        const daysDiff = Math.ceil((endDate - startDate) / (1000 * 60 * 60 * 24));
        const multiplier = daysDiff;
        
        // Use current values as base and multiply
        const currentTotal = parseInt(document.getElementById('stat-total').textContent.replace(/,/g, '')) || 0;
        const currentVici = parseInt(document.getElementById('stat-vici').textContent.replace(/,/g, '')) || 0;
        const currentStuck = parseInt(document.getElementById('stat-stuck').textContent.replace(/,/g, '')) || 0;
        
        if (currentPeriod === 'today') {
            // Already showing today's stats
            return;
        }
        
        // Estimate based on days
        document.getElementById('stat-total').textContent = (currentTotal * multiplier).toLocaleString();
        document.getElementById('stat-vici').textContent = (currentVici * multiplier).toLocaleString();
        document.getElementById('stat-stuck').textContent = (currentStuck * multiplier).toLocaleString();
        
        const total = currentTotal * multiplier;
        const vici = currentVici * multiplier;
        const rate = total > 0 ? ((vici / total) * 100).toFixed(1) : 0;
        document.getElementById('stat-conversion').textContent = rate + '%';
    }
    
    // Auto-refresh disabled - use manual refresh button instead
    
    
    function refreshDashboard() {
        // Show loading indicator
        const overlay = document.createElement('div');
        overlay.style.cssText = 'position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(255,255,255,0.9);display:flex;align-items:center;justify-content:center;z-index:9999';
        overlay.innerHTML = '<div style="text-align:center"><div style="font-size:24px;color:#10b981;margin-bottom:10px">🔄 Refreshing Dashboard...</div><div style="color:#6b7280">Loading latest data</div></div>';
        document.body.appendChild(overlay);
        
        // Reload the page to get fresh data
        setTimeout(() => {
            window.location.reload();
        }, 500);
    }
    
    // Show payload in modal
    function showPayload(lead) {
        // Create comprehensive payload object
        const fullData = {
            lead_info: {
                id: lead.id,
                external_lead_id: lead.external_lead_id,
                leadid_code: lead.leadid_code,
                jangle_lead_id: lead.jangle_lead_id,
                name: lead.name,
                first_name: lead.first_name,
                last_name: lead.last_name,
                phone: lead.phone,
                email: lead.email,
                source: lead.source,
                vendor_name: lead.vendor_name,
                buyer_name: lead.buyer_name,
                created_at: lead.created_at,
                opt_in_date: lead.opt_in_date
            },
            location: {
                address: lead.address,
                city: lead.city,
                state: lead.state,
                zip_code: lead.zip_code
            },
            tracking: {
                ip_address: lead.ip_address,
                user_agent: lead.user_agent,
                landing_page_url: lead.landing_page_url,
                tcpa_compliant: lead.tcpa_compliant,
                tcpa_consent_text: lead.tcpa_consent_text,
                trusted_form_cert: lead.trusted_form_cert
            },
            vici_info: {
                vici_list_id: lead.vici_list_id,
                sent_to_vici: lead.vici_list_id ? true : false
            },
            original_payload: lead.payload ? (typeof lead.payload === 'string' ? JSON.parse(lead.payload) : lead.payload) : null,
            drivers: lead.drivers,
            vehicles: lead.vehicles,
            current_policy: lead.current_policy,
            requested_policy: lead.requested_policy,
            meta: lead.meta
        };
        
        const formatted = JSON.stringify(fullData, null, 2);
        const modal = document.createElement('div');
        modal.style.cssText = 'position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);display:flex;align-items:center;justify-content:center;z-index:9999';
        modal.innerHTML = `
            <div style="background:white;padding:2rem;border-radius:8px;max-width:900px;width:90%;max-height:80vh;overflow:auto;position:relative">
                <h2 style="margin-bottom:1rem">Complete Lead Data & Payload</h2>
                <button onclick="this.closest('div').parentElement.remove()" style="position:absolute;top:1rem;right:1rem;background:#ef4444;color:white;border:none;padding:0.5rem 1rem;border-radius:4px;cursor:pointer">✕ Close</button>
                <div style="margin-bottom:1rem">
                    <button onclick="navigator.clipboard.writeText(${JSON.stringify(formatted).replace(/"/g, '&quot;')}); this.textContent='✓ Copied!'; setTimeout(()=>this.textContent='📋 Copy All',2000)" style="background:#10b981;color:white;border:none;padding:0.5rem 1rem;border-radius:4px;cursor:pointer">📋 Copy All</button>
                </div>
                <pre style="background:#f3f4f6;padding:1rem;border-radius:4px;overflow:auto;font-size:12px">${formatted}</pre>
            </div>
        `;
        document.body.appendChild(modal);
        modal.onclick = function(e) { if(e.target === modal) modal.remove(); };
    }
</script>
@endsection


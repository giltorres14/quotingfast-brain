@include('components.header', ['title' => 'Leads Dashboard'])

<style>
        /* Page-specific styles */
        .leads-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            border: 1px solid rgba(0,0,0,0.05);
            transition: all 0.2s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
        
        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .stat-title {
            font-size: 0.9rem;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .stat-icon {
            font-size: 1.5rem;
            opacity: 0.7;
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 0.5rem;
        }
        
        .stat-change {
            font-size: 0.85rem;
            font-weight: 500;
        }
        
        .stat-change.positive {
            color: #10b981;
        }
        
        .stat-change.negative {
            color: #ef4444;
        }
        
        /* Leads Grid */
        .leads-section {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            border: 1px solid rgba(0,0,0,0.05);
            overflow: hidden;
        }
        
        .leads-header {
            background: #f8fafc;
            padding: 1.5rem;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .leads-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1f2937;
        }
        
        .leads-actions {
            display: flex;
            gap: 1rem;
            align-items: center;
        }
        
        .search-box {
            position: relative;
        }
        
        .search-input {
            padding: 0.5rem 1rem 0.5rem 2.5rem;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 0.9rem;
            width: 250px;
            transition: border-color 0.2s;
        }
        
        .search-input:focus {
            outline: none;
            border-color: #3b82f6;
        }
        
        .search-icon {
            position: absolute;
            left: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
        }
        
        .filter-btn {
            padding: 0.5rem 1rem;
            background: #3b82f6;
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        
        .filter-btn:hover {
            background: #5a67d8;
        }
        
        /* Leads Grid */
        .leads-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 1.5rem;
            padding: 1.5rem;
        }
        
        .lead-card {
            background: #fafafa;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 1.25rem;
            transition: all 0.2s ease;
            position: relative;
        }
        
        .lead-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            border-color: #3b82f6;
        }
        
        .lead-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 0.75rem;
        }
        
        .lead-name {
            font-size: 1.1rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 0.25rem;
        }
        
        .lead-badges {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
            margin-bottom: 0.75rem;
        }
        
        .badge {
            padding: 0.25rem 0.75rem;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .badge-type-auto {
            background: #dbeafe;
            color: #1e40af;
        }
        
        .badge-type-home {
            background: #fef3c7;
            color: #d97706;
        }
        
        .badge-campaign {
            background: #f0f9ff;
            color: #0369a1;
        }
        
        .badge-vici {
            background: #dcfce7;
            color: #166534;
        }
        
        .lead-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.5rem;
            margin-bottom: 1rem;
            font-size: 0.9rem;
        }
        
        .info-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #6b7280;
        }
        
        .info-icon {
            font-size: 0.8rem;
        }
        
        .sms-status {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.85rem;
            color: #6b7280;
            margin-bottom: 0.75rem;
        }
        
        .sms-indicator {
            width: 8px;
            height: 8px;
            border-radius: 50%;
        }
        
        .sms-none {
            background: #d1d5db;
        }
        
        .sms-sent {
            background: #10b981;
        }
        
        .sms-failed {
            background: #ef4444;
        }
        
        .lead-actions {
            display: flex;
            gap: 0.5rem;
        }
        
        .btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            text-align: center;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }
        
        .btn-sm {
            padding: 0.375rem 0.75rem;
            font-size: 0.8rem;
        }
        
        .btn-view {
            background: #f3f4f6;
            color: #374151;
            border: 1px solid #d1d5db;
        }
        
        .btn-view:hover {
            background: #e5e7eb;
            text-decoration: none;
            color: #374151;
        }
        
        .btn-edit {
            background: #3b82f6;
            color: white;
        }
        
        .btn-edit:hover {
            background: #5a67d8;
            text-decoration: none;
            color: white;
        }
        
        .btn-payload {
            background: #f59e0b;
            color: white;
        }
        
        .btn-payload:hover {
            background: #d97706;
            text-decoration: none;
            color: white;
        }
        
        /* Pagination */
        .pagination-wrapper {
            padding: 1.5rem;
            border-top: 1px solid #e5e7eb;
            background: #f8fafc;
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 0.5rem;
        }
        
        .pagination a, .pagination span {
            padding: 0.5rem 0.75rem;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s ease;
        }
        
        .pagination a {
            color: #6b7280;
            background: white;
            border: 1px solid #d1d5db;
        }
        
        .pagination a:hover {
            background: #3b82f6;
            color: white;
            border-color: #3b82f6;
        }
        
        .pagination .current {
            background: #3b82f6;
            color: white;
            border: 1px solid #3b82f6;
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: #6b7280;
        }
        
        .empty-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }
        
        .empty-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #374151;
        }
        
        .empty-description {
            font-size: 1rem;
            margin-bottom: 2rem;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .leads-container {
                padding: 1rem;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .leads-grid {
                grid-template-columns: 1fr;
                padding: 1rem;
            }
            
            .leads-header {
                flex-direction: column;
                gap: 1rem;
                align-items: stretch;
            }
            
            .leads-actions {
                flex-direction: column;
            }
            
            .search-input {
                width: 100%;
            }
        }
</style>

<div class="page-content">
    <div class="leads-container">
        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-title">Total Leads</div>
                    <div class="stat-icon">👥</div>
                </div>
                <div class="stat-number">{{ $totalLeads }}</div>
                <div class="stat-change positive">+{{ $newLeadsToday }} today</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-title">Today's Leads</div>
                    <div class="stat-icon">📅</div>
                </div>
                <div class="stat-number">{{ $newLeadsToday }}</div>
                <div class="stat-change {{ $todayChange >= 0 ? 'positive' : 'negative' }}">
                    {{ $todayChange >= 0 ? '+' : '' }}{{ $todayChange }} from yesterday
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-title">This Week</div>
                    <div class="stat-icon">📊</div>
                </div>
                <div class="stat-number">{{ $weekLeads }}</div>
                <div class="stat-change {{ $weekChange >= 0 ? 'positive' : 'negative' }}">
                    {{ $weekChange >= 0 ? '+' : '' }}{{ $weekChange }} from last week
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-title">Conversion Rate</div>
                    <div class="stat-icon">🎯</div>
                </div>
                <div class="stat-number">{{ number_format($conversionRate, 1) }}%</div>
                <div class="stat-change positive">Above industry avg</div>
            </div>
        </div>

        <!-- Leads Section -->
        <div class="leads-section">
            <div class="leads-header">
                <div class="leads-title">Recent Leads ({{ $leads->total() }})</div>
                <div class="leads-actions">
                    <div class="search-box">
                        <span class="search-icon">🔍</span>
                        <input type="text" class="search-input" placeholder="Search leads..." id="searchInput">
                    </div>
                    <button class="filter-btn" onclick="toggleFilters()">
                        🔧 Filters
                    </button>
                </div>
            </div>

            @if($leads->count() > 0)
                <div class="leads-grid">
                    @foreach($leads as $lead)
                        <div class="lead-card">
                            <div class="lead-header">
                                <div>
                                    <div class="lead-name">
                                        {{ $lead->first_name ?? '' }} {{ $lead->last_name ?? '' }}
                                        @if(!$lead->first_name && !$lead->last_name)
                                            {{ $lead->name ?? 'Unknown Lead' }}
                                        @endif
                                        @if($lead->external_lead_id)
                                            <span style="font-size: 0.8rem; color: #6b7280; font-weight: 400; margin-left: 0.5rem;">
                                                #{{ $lead->external_lead_id }}
                                            </span>
                                        @endif
                                    </div>
                                    
                                    <!-- Date/Time in header -->
                                    <div style="font-size: 0.75rem; color: #6b7280; margin-top: 0.25rem;">
                                        🕒 {{ $lead->created_at ? $lead->created_at->setTimezone('America/New_York')->format('M j, g:i A') : 'Unknown' }}
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Badges -->
                            <div class="lead-badges">
                                @if($lead->type)
                                    <span class="badge badge-type-{{ strtolower($lead->type) }}">
                                        {{ ucfirst($lead->type) }}
                                    </span>
                                @endif
                                
                                @if($lead->campaign_id)
                                    @php
                                        $campaign = \App\Models\Campaign::where('campaign_id', $lead->campaign_id)->first();
                                        $campaignName = $campaign ? $campaign->display_name : "Campaign #{$lead->campaign_id}";
                                    @endphp
                                    <span class="badge badge-campaign">
                                        {{ $campaignName }}
                                    </span>
                                @endif
                                
                                @if(isset($lead->sent_to_vici) && $lead->sent_to_vici)
                                    <span class="badge badge-vici">
                                        Vici
                                    </span>
                                @endif
                            </div>
                            
                            <!-- Lead Info -->
                            <div class="lead-info">
                                <div class="info-item">
                                    <span class="info-icon">📱</span>
                                    {{ $lead->phone ?? 'No phone' }}
                                </div>
                                <div class="info-item">
                                    <span class="info-icon">📧</span>
                                    {{ $lead->email ?? 'No email' }}
                                </div>
                                <div class="info-item">
                                    <span class="info-icon">📍</span>
                                    {{ $lead->city ?? 'Unknown' }}, {{ $lead->state ?? 'Unknown' }}
                                </div>
                                <div class="info-item">
                                    <span class="info-icon">🏷️</span>
                                    {{ $lead->source ?? 'Unknown source' }}
                                </div>
                            </div>
                            
                            <!-- SMS Status -->
                            <div class="sms-status">
                                <div class="sms-indicator sms-none"></div>
                                <span>SMS: None</span>
                            </div>
                            
                            <!-- Actions -->
                            <div class="lead-actions">
                                <a href="/agent/lead/{{ $lead->id }}?mode=view" class="btn btn-sm btn-view">
                                    👁️ View
                                </a>
                                <a href="/agent/lead/{{ $lead->id }}?mode=edit" class="btn btn-sm btn-edit">
                                    ✏️ Edit
                                </a>
                                <a href="/api/lead/{{ $lead->id }}/payload" class="btn btn-sm btn-payload" target="_blank">
                                    📄 Payload
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
                
                <!-- Pagination -->
                <div class="pagination-wrapper">
                    <div class="pagination">
                        {{ $leads->links('pagination::bootstrap-4') }}
                    </div>
                </div>
            @else
                <div class="empty-state">
                    <div class="empty-icon">📭</div>
                    <div class="empty-title">No leads found</div>
                    <div class="empty-description">
                        Leads will appear here when they are received through webhooks or uploaded via CSV.
                    </div>
                    <a href="/lead-upload" class="btn btn-edit">📁 Upload CSV Leads</a>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
    // Search functionality
    document.getElementById('searchInput').addEventListener('input', function(e) {
        const searchTerm = e.target.value.toLowerCase();
        const leadCards = document.querySelectorAll('.lead-card');
        
        leadCards.forEach(card => {
            const leadName = card.querySelector('.lead-name').textContent.toLowerCase();
            const leadInfo = card.querySelector('.lead-info').textContent.toLowerCase();
            
            if (leadName.includes(searchTerm) || leadInfo.includes(searchTerm)) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    });
    
    // Filter toggle (placeholder)
    function toggleFilters() {
        alert('Advanced filters coming soon! You can search leads using the search box above.');
    }
    
    // Auto-refresh every 30 seconds
    setInterval(() => {
        if (document.visibilityState === 'visible') {
            window.location.reload();
        }
    }, 30000);
</script>

</body>
</html>
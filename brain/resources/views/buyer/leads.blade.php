<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>My Leads - {{ $buyer->full_name }} | The Brain</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: #f8fafc;
            color: #1a202c;
            line-height: 1.6;
        }
        
        /* Header */
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1.5rem 0;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .header-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo-section {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .logo {
            height: 40px;
            filter: brightness(1.2);
        }
        
        .brand-text {
            font-size: 1.5rem;
            font-weight: 700;
        }
        
        .user-section {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }
        
        .balance-display {
            background: rgba(255, 255, 255, 0.2);
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.1rem;
        }
        
        /* Navigation */
        .nav-tabs {
            background: white;
            border-bottom: 1px solid #e2e8f0;
            padding: 0 2rem;
        }
        
        .nav-container {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            gap: 2rem;
        }
        
        .nav-tab {
            padding: 1rem 0;
            color: #6b7280;
            text-decoration: none;
            font-weight: 500;
            border-bottom: 2px solid transparent;
            transition: all 0.2s;
        }
        
        .nav-tab:hover {
            color: #667eea;
            text-decoration: none;
        }
        
        .nav-tab.active {
            color: #667eea;
            border-bottom-color: #667eea;
        }
        
        /* Main Content */
        .main-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        .page-header {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        
        .page-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: #1a202c;
            margin-bottom: 0.5rem;
        }
        
        .page-subtitle {
            color: #6b7280;
            font-size: 1.1rem;
        }
        
        /* Filters */
        .filters-section {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        
        .filters-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            align-items: end;
        }
        
        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        
        .filter-label {
            font-weight: 600;
            color: #374151;
            font-size: 0.9rem;
        }
        
        .filter-input, .filter-select {
            padding: 0.75rem;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 0.9rem;
            transition: border-color 0.2s;
        }
        
        .filter-input:focus, .filter-select:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .filter-btn {
            background: #667eea;
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
            height: fit-content;
        }
        
        .filter-btn:hover {
            background: #5a67d8;
        }
        
        .clear-btn {
            background: #6b7280;
            color: white;
            border: none;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.2s;
        }
        
        .clear-btn:hover {
            background: #4b5563;
        }
        
        /* Leads Table */
        .leads-section {
            background: white;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .table-header {
            background: #f8fafc;
            padding: 1.5rem;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .table-title {
            font-size: 1.2rem;
            font-weight: 700;
            color: #1a202c;
        }
        
        .results-count {
            color: #6b7280;
            font-size: 0.9rem;
        }
        
        .leads-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .leads-table th {
            background: #f8fafc;
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            color: #374151;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .leads-table td {
            padding: 1rem;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: top;
        }
        
        .leads-table tr:hover {
            background: #f8fafc;
        }
        
        .lead-id {
            font-weight: 600;
            color: #667eea;
            text-decoration: none;
        }
        
        .lead-id:hover {
            text-decoration: underline;
        }
        
        .lead-details {
            margin-top: 0.25rem;
        }
        
        .lead-name {
            font-weight: 600;
            color: #1a202c;
        }
        
        .lead-contact {
            font-size: 0.85rem;
            color: #6b7280;
            margin-top: 0.25rem;
        }
        
        .vertical-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .vertical-auto {
            background: #dbeafe;
            color: #1e40af;
        }
        
        .vertical-home {
            background: #fef3c7;
            color: #d97706;
        }
        
        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-delivered {
            background: #dcfce7;
            color: #166534;
        }
        
        .status-returned {
            background: #fef3c7;
            color: #92400e;
        }
        
        .status-disputed {
            background: #fee2e2;
            color: #dc2626;
        }
        
        .price-display {
            font-weight: 600;
            color: #10b981;
            font-size: 1rem;
        }
        
        .date-display {
            color: #6b7280;
            font-size: 0.9rem;
        }
        
        .actions-cell {
            display: flex;
            gap: 0.5rem;
        }
        
        .action-btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 6px;
            font-size: 0.8rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            text-align: center;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }
        
        .btn-view {
            background: #f3f4f6;
            color: #374151;
            border: 1px solid #d1d5db;
        }
        
        .btn-view:hover {
            background: #e5e7eb;
            text-decoration: none;
        }
        
        .btn-return {
            background: #fef3c7;
            color: #92400e;
            border: 1px solid #fcd34d;
        }
        
        .btn-return:hover {
            background: #fde68a;
            text-decoration: none;
        }
        
        .btn-return:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        /* Pagination */
        .pagination-wrapper {
            padding: 1.5rem;
            border-top: 1px solid #e2e8f0;
            background: #f8fafc;
            display: flex;
            justify-content: center;
        }
        
        .pagination {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .pagination a, .pagination span {
            padding: 0.5rem 0.75rem;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s;
        }
        
        .pagination a {
            color: #6b7280;
            background: white;
            border: 1px solid #d1d5db;
        }
        
        .pagination a:hover {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }
        
        .pagination .current {
            background: #667eea;
            color: white;
            border: 1px solid #667eea;
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
        
        .cta-btn {
            background: #667eea;
            color: white;
            padding: 0.75rem 2rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: background 0.2s;
        }
        
        .cta-btn:hover {
            background: #5a67d8;
            text-decoration: none;
            color: white;
        }
        
        /* Return Modal */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s;
        }
        
        .modal-overlay.active {
            opacity: 1;
            visibility: visible;
        }
        
        .modal-content {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            max-width: 500px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
        }
        
        .modal-header {
            margin-bottom: 1.5rem;
        }
        
        .modal-title {
            font-size: 1.2rem;
            font-weight: 700;
            color: #1a202c;
            margin-bottom: 0.5rem;
        }
        
        .modal-subtitle {
            color: #6b7280;
            font-size: 0.9rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-label {
            display: block;
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.5rem;
        }
        
        .form-select, .form-textarea {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 0.9rem;
            transition: border-color 0.2s;
        }
        
        .form-select:focus, .form-textarea:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .form-textarea {
            resize: vertical;
            min-height: 100px;
        }
        
        .modal-actions {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
        }
        
        .btn-cancel {
            background: #6b7280;
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }
        
        .btn-cancel:hover {
            background: #4b5563;
        }
        
        .btn-confirm {
            background: #dc2626;
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }
        
        .btn-confirm:hover {
            background: #b91c1c;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .header-container {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }
            
            .user-section {
                flex-direction: column;
                gap: 1rem;
            }
            
            .nav-container {
                flex-wrap: wrap;
                gap: 1rem;
            }
            
            .main-container {
                padding: 1rem;
            }
            
            .filters-grid {
                grid-template-columns: 1fr;
            }
            
            .leads-table {
                font-size: 0.8rem;
            }
            
            .leads-table th,
            .leads-table td {
                padding: 0.5rem;
            }
            
            .actions-cell {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="header-container">
            <div class="logo-section">
                <img src="https://quotingfast.com/whitelogo" alt="QuotingFast" class="logo" onerror="this.style.display='none';">
                <div class="brand-text">The Brain</div>
            </div>
            
            <div class="user-section">
                <div class="balance-display">
                    💰 Balance: {{ $buyer->formatted_balance }}
                </div>
                
                <div class="user-info">
                    <div class="user-avatar">
                        {{ strtoupper(substr($buyer->first_name, 0, 1)) }}
                    </div>
                    <div>
                        <div style="font-weight: 600;">{{ $buyer->full_name }}</div>
                        <div style="font-size: 0.85rem; opacity: 0.8;">{{ $buyer->company ?? 'Buyer Account' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Navigation -->
    <div class="nav-tabs">
        <div class="nav-container">
            <a href="/buyer/dashboard" class="nav-tab">📊 Dashboard</a>
            <a href="/buyer/leads" class="nav-tab active">👥 My Leads</a>
            <a href="/buyer/billing" class="nav-tab">💳 Billing</a>
            <a href="/buyer/reports" class="nav-tab">📈 Reports</a>
            <a href="/buyer/settings" class="nav-tab">⚙️ Settings</a>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="main-container">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-title">My Leads 👥</h1>
            <p class="page-subtitle">
                Manage and track all your purchased leads. Return leads within 24 hours if they don't meet quality standards.
            </p>
        </div>
        
        <!-- Filters -->
        <div class="filters-section">
            <form method="GET" action="/buyer/leads">
                <div class="filters-grid">
                    <div class="filter-group">
                        <label class="filter-label">Search</label>
                        <input type="text" name="search" class="filter-input" placeholder="Lead ID, name, phone..." value="{{ request('search') }}">
                    </div>
                    
                    <div class="filter-group">
                        <label class="filter-label">Status</label>
                        <select name="status" class="filter-select">
                            <option value="">All Statuses</option>
                            <option value="delivered" {{ request('status') === 'delivered' ? 'selected' : '' }}>Delivered</option>
                            <option value="returned" {{ request('status') === 'returned' ? 'selected' : '' }}>Returned</option>
                            <option value="disputed" {{ request('status') === 'disputed' ? 'selected' : '' }}>Disputed</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label class="filter-label">Type</label>
                        <select name="vertical" class="filter-select">
                            <option value="">All Types</option>
                            <option value="auto" {{ request('vertical') === 'auto' ? 'selected' : '' }}>Auto Insurance</option>
                            <option value="home" {{ request('vertical') === 'home' ? 'selected' : '' }}>Home Insurance</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label class="filter-label">Date Range</label>
                        <select name="date_range" class="filter-select">
                            <option value="">All Time</option>
                            <option value="today" {{ request('date_range') === 'today' ? 'selected' : '' }}>Today</option>
                            <option value="week" {{ request('date_range') === 'week' ? 'selected' : '' }}>This Week</option>
                            <option value="month" {{ request('date_range') === 'month' ? 'selected' : '' }}>This Month</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <button type="submit" class="filter-btn">🔍 Filter</button>
                    </div>
                    
                    <div class="filter-group">
                        <a href="/buyer/leads" class="clear-btn">Clear</a>
                    </div>
                </div>
            </form>
        </div>
        
        <!-- Leads Table -->
        <div class="leads-section">
            <div class="table-header">
                <h2 class="table-title">Your Leads</h2>
                <div class="results-count">
                    Showing {{ $leads->count() }} of {{ $leads->total() }} leads
                </div>
            </div>
            
            @if($leads->count() > 0)
                <table class="leads-table">
                    <thead>
                        <tr>
                            <th>Lead ID</th>
                            <th>Customer Info</th>
                            <th>Type</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th>Delivered</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($leads as $buyerLead)
                        <tr>
                            <td>
                                <a href="#" class="lead-id" onclick="viewLeadDetails('{{ $buyerLead->id }}')">
                                    #{{ $buyerLead->external_lead_id }}
                                </a>
                                <div class="lead-details">
                                    <small style="color: #6b7280;">ID: {{ $buyerLead->id }}</small>
                                </div>
                            </td>
                            <td>
                                @if($buyerLead->lead_data)
                                    <div class="lead-name">
                                        {{ $buyerLead->lead_data['name'] ?? $buyerLead->lead_data['first_name'] . ' ' . $buyerLead->lead_data['last_name'] ?? 'Unknown' }}
                                    </div>
                                    <div class="lead-contact">
                                        📞 {{ $buyerLead->lead_data['phone'] ?? 'No phone' }}<br>
                                        📧 {{ $buyerLead->lead_data['email'] ?? 'No email' }}
                                    </div>
                                @else
                                    <div class="lead-name">Lead Data</div>
                                    <div class="lead-contact">Contact information available</div>
                                @endif
                            </td>
                            <td>
                                <span class="vertical-badge vertical-{{ $buyerLead->vertical }}">
                                    {{ ucfirst($buyerLead->vertical) }}
                                </span>
                            </td>
                            <td>
                                <div class="price-display">{{ $buyerLead->formatted_price }}</div>
                            </td>
                            <td>
                                <span class="status-badge status-{{ $buyerLead->status }}">
                                    {{ ucfirst($buyerLead->status) }}
                                </span>
                                @if($buyerLead->return_reason)
                                    <div style="font-size: 0.75rem; color: #6b7280; margin-top: 0.25rem;">
                                        {{ $buyerLead->return_reason }}
                                    </div>
                                @endif
                            </td>
                            <td>
                                <div class="date-display">
                                    {{ $buyerLead->delivered_at->format('M j, Y') }}<br>
                                    <small>{{ $buyerLead->delivered_at->format('g:i A') }}</small>
                                </div>
                            </td>
                            <td>
                                <div class="actions-cell">
                                    <button class="action-btn btn-view" onclick="viewLeadDetails('{{ $buyerLead->id }}')">
                                        👁️ View
                                    </button>
                                    @if($buyerLead->canReturn())
                                        <button class="action-btn btn-return" onclick="openReturnModal('{{ $buyerLead->id }}', '{{ $buyerLead->external_lead_id }}')">
                                            ↩️ Return
                                        </button>
                                    @else
                                        <button class="action-btn btn-return" disabled title="Return window expired (24 hours)">
                                            ↩️ Return
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                
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
                        @if(request()->hasAny(['search', 'status', 'vertical', 'date_range']))
                            No leads match your current filters. Try adjusting your search criteria.
                        @else
                            You haven't purchased any leads yet. Start browsing available leads to grow your business.
                        @endif
                    </div>
                    @if(!request()->hasAny(['search', 'status', 'vertical', 'date_range']))
                        <a href="/buyer/marketplace" class="cta-btn">
                            🛒 Browse Available Leads
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </div>
    
    <!-- Return Modal -->
    <div class="modal-overlay" id="returnModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Return Lead</h3>
                <p class="modal-subtitle">
                    Please provide a reason for returning this lead. Returns must be submitted within 24 hours of delivery.
                </p>
            </div>
            
            <form id="returnForm">
                <input type="hidden" id="returnLeadId" name="lead_id">
                
                <div class="form-group">
                    <label class="form-label">Return Reason *</label>
                    <select name="return_reason" class="form-select" required>
                        <option value="">Select a reason...</option>
                        <option value="Bad Contact Data">Bad Contact Data</option>
                        <option value="Duplicate Lead">Duplicate Lead</option>
                        <option value="Not Interested">Customer Not Interested</option>
                        <option value="Outside Service Area">Outside Service Area</option>
                        <option value="Invalid Information">Invalid Information</option>
                        <option value="Already Insured">Already Insured</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Additional Notes</label>
                    <textarea name="return_notes" class="form-textarea" placeholder="Provide additional details about why you're returning this lead..."></textarea>
                </div>
                
                <div class="modal-actions">
                    <button type="button" class="btn-cancel" onclick="closeReturnModal()">Cancel</button>
                    <button type="submit" class="btn-confirm">Return Lead</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Return modal functions
        function openReturnModal(leadId, externalId) {
            document.getElementById('returnLeadId').value = leadId;
            document.querySelector('.modal-subtitle').textContent = 
                `Please provide a reason for returning lead #${externalId}. Returns must be submitted within 24 hours of delivery.`;
            document.getElementById('returnModal').classList.add('active');
        }
        
        function closeReturnModal() {
            document.getElementById('returnModal').classList.remove('active');
            document.getElementById('returnForm').reset();
        }
        
        // Handle return form submission
        document.getElementById('returnForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const leadId = document.getElementById('returnLeadId').value;
            
            try {
                const response = await fetch(`/buyer/leads/${leadId}/return`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    alert('Lead returned successfully. Your account has been credited.');
                    window.location.reload();
                } else {
                    alert('Error: ' + result.message);
                }
                
            } catch (error) {
                alert('Error returning lead. Please try again.');
            }
            
            closeReturnModal();
        });
        
        // View lead details function
        function viewLeadDetails(leadId) {
            // This would open a modal or navigate to a detailed view
            alert(`Viewing details for lead ID: ${leadId}`);
            // TODO: Implement lead details view
        }
        
        // Close modal when clicking outside
        document.getElementById('returnModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeReturnModal();
            }
        });
        
        // Close modal on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeReturnModal();
            }
        });
    </script>
</body>
</html>
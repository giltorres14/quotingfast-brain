@extends('layouts.management')

@section('title', 'Campaign Management')
@section('page-title', '📊 Campaign Management')

@section('styles')
<style>
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }
    
    .stat-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        padding: 1.5rem;
        border-radius: 1rem;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    }
    
    .stat-number {
        font-size: 2.5rem;
        font-weight: 700;
        color: #1e40af;
    }
    
    .stat-label {
        color: #64748b;
        font-size: 0.875rem;
        margin-top: 0.5rem;
    }
    
    .campaign-table {
        background: white;
        border-radius: 1rem;
        overflow: hidden;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    }
    
    .table-header {
        background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
        color: white;
        padding: 1rem 1.5rem;
        font-size: 1.125rem;
        font-weight: 600;
    }
    
    table {
        width: 100%;
        border-collapse: collapse;
    }
    
    thead th {
        background: #f8fafc;
        padding: 1rem;
        text-align: left;
        font-weight: 600;
        color: #475569;
        border-bottom: 2px solid #e2e8f0;
    }
    
    tbody td {
        padding: 1rem;
        border-bottom: 1px solid #f1f5f9;
    }
    
    tbody tr:hover {
        background: #f8fafc;
    }
    
    .badge {
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.875rem;
        font-weight: 500;
        display: inline-block;
    }
    
    .badge-auto {
        background: #fef3c7;
        color: #92400e;
    }
    
    .badge-active {
        background: #d1fae5;
        color: #065f46;
    }
    
    .badge-inactive {
        background: #e5e7eb;
        color: #6b7280;
    }
    
    .btn {
        padding: 0.5rem 1rem;
        border: none;
        border-radius: 0.5rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-block;
    }
    
    .btn-primary {
        background: #3b82f6;
        color: white;
    }
    
    .btn-primary:hover {
        background: #2563eb;
    }
    
    .btn-secondary {
        background: #e5e7eb;
        color: #374151;
    }
    
    .btn-secondary:hover {
        background: #d1d5db;
    }
    
    .btn-sm {
        padding: 0.25rem 0.75rem;
        font-size: 0.875rem;
    }
    
    .edit-form {
        display: none;
    }
    
    .edit-form.active {
        display: table-row;
    }
    
    .form-group {
        margin-bottom: 1rem;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 0.25rem;
        font-weight: 500;
        color: #374151;
    }
    
    .form-control {
        width: 100%;
        padding: 0.5rem;
        border: 1px solid #d1d5db;
        border-radius: 0.375rem;
        font-size: 1rem;
    }
    
    .form-control:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }
    
    .alert {
        padding: 1rem;
        border-radius: 0.5rem;
        margin-bottom: 1rem;
    }
    
    .alert-warning {
        background: #fef3c7;
        color: #92400e;
        border: 1px solid #fde68a;
    }
    
    .alert-success {
        background: #d1fae5;
        color: #065f46;
        border: 1px solid #a7f3d0;
    }
    
    .alert-error {
        background: #fee2e2;
        color: #991b1b;
        border: 1px solid #fecaca;
    }
    
    .action-buttons {
        display: flex;
        gap: 0.5rem;
    }
    
    .needs-attention {
        background: #fef3c7;
        animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.7; }
    }
</style>
@endsection

@section('nav-actions')
<button onclick="location.reload()" class="nav-link" title="Refresh">🔄 Refresh</button>
@endsection

@section('content')
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    
    @if(session('error'))
        <div class="alert alert-error">
            {{ session('error') }}
        </div>
    @endif

    <!-- Statistics -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-number">{{ $stats['total_campaigns'] ?? 0 }}</div>
            <div class="stat-label">Total Campaigns</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">{{ $stats['auto_created'] ?? 0 }}</div>
            <div class="stat-label">Need Names (Auto-Created)</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">{{ $stats['active_campaigns'] ?? 0 }}</div>
            <div class="stat-label">Active Campaigns</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">{{ number_format($stats['total_leads'] ?? 0) }}</div>
            <div class="stat-label">Total Leads</div>
        </div>
    </div>

    @if(($stats['auto_created'] ?? 0) > 0)
        <div class="alert alert-warning">
            ⚠️ You have {{ $stats['auto_created'] }} campaigns that were auto-created from incoming leads. Please assign proper names to these campaigns.
        </div>
    @endif

    <!-- Campaign Table -->
    <div class="campaign-table">
        <div class="table-header">
            Campaign Directory
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>Campaign ID</th>
                    <th>Name</th>
                    <th>Status</th>
                    <th>Total Leads</th>
                    <th>First Seen</th>
                    <th>Last Lead</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($campaigns as $campaign)
                    <tr class="{{ $campaign->is_auto_created ? 'needs-attention' : '' }}" id="row-{{ $campaign->id }}">
                        <td><strong>#{{ $campaign->campaign_id }}</strong></td>
                        <td>
                            <span id="name-{{ $campaign->id }}">{{ $campaign->display_name }}</span>
                            @if($campaign->description)
                                <br><small style="color: #6b7280;">{{ $campaign->description }}</small>
                            @endif
                        </td>
                        <td>
                            @if($campaign->is_auto_created)
                                <span class="badge badge-auto">Auto-Created</span>
                            @elseif($campaign->status === 'active')
                                <span class="badge badge-active">Active</span>
                            @else
                                <span class="badge badge-inactive">{{ ucfirst($campaign->status) }}</span>
                            @endif
                        </td>
                        <td>{{ number_format($campaign->total_leads) }}</td>
                        <td>{{ $campaign->first_seen_at ? $campaign->first_seen_at->format('M d, Y') : 'N/A' }}</td>
                        <td>
                            @if($campaign->last_lead_received_at)
                                {{ $campaign->last_lead_received_at->diffForHumans() }}
                            @else
                                N/A
                            @endif
                        </td>
                        <td>
                            <div class="action-buttons">
                                <button onclick="editCampaign({{ $campaign->id }})" class="btn btn-primary btn-sm">
                                    ✏️ Edit
                                </button>
                                <a href="/leads?campaign={{ $campaign->campaign_id }}" class="btn btn-secondary btn-sm">
                                    👁️ View Leads
                                </a>
                            </div>
                        </td>
                    </tr>
                    
                    <!-- Edit Form Row (Hidden by default) -->
                    <tr class="edit-form" id="edit-{{ $campaign->id }}">
                        <td colspan="7" style="background: #f8fafc; padding: 1.5rem;">
                            <form method="POST" action="/admin/campaigns/{{ $campaign->id }}/update" onsubmit="return saveCampaign(event, {{ $campaign->id }})">
                                @csrf
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                                    <div class="form-group">
                                        <label>Campaign Name</label>
                                        <input type="text" 
                                               name="name" 
                                               id="name-input-{{ $campaign->id }}"
                                               class="form-control" 
                                               value="{{ $campaign->name }}" 
                                               placeholder="Enter campaign name"
                                               required>
                                    </div>
                                    <div class="form-group">
                                        <label>Status</label>
                                        <select name="status" id="status-input-{{ $campaign->id }}" class="form-control">
                                            <option value="active" {{ $campaign->status === 'active' ? 'selected' : '' }}>Active</option>
                                            <option value="paused" {{ $campaign->status === 'paused' ? 'selected' : '' }}>Paused</option>
                                            <option value="inactive" {{ $campaign->status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Description</label>
                                    <input type="text" 
                                           name="description" 
                                           id="description-input-{{ $campaign->id }}"
                                           class="form-control" 
                                           value="{{ $campaign->description }}" 
                                           placeholder="Optional description">
                                </div>
                                <div style="display: flex; gap: 1rem; margin-top: 1rem;">
                                    <button type="submit" class="btn btn-primary">
                                        💾 Save Changes
                                    </button>
                                    <button type="button" onclick="cancelEdit({{ $campaign->id }})" class="btn btn-secondary">
                                        Cancel
                                    </button>
                                </div>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 2rem; color: #6b7280;">
                            No campaigns found. Campaigns will be auto-created when leads come in with campaign IDs.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection

@section('scripts')
<script>
    function editCampaign(id) {
        // Hide all other edit forms
        document.querySelectorAll('.edit-form').forEach(form => {
            form.classList.remove('active');
        });
        
        // Show this edit form
        document.getElementById('edit-' + id).classList.add('active');
    }
    
    function cancelEdit(id) {
        document.getElementById('edit-' + id).classList.remove('active');
    }
    
    function saveCampaign(event, id) {
        event.preventDefault();
        
        const name = document.getElementById('name-input-' + id).value;
        const status = document.getElementById('status-input-' + id).value;
        const description = document.getElementById('description-input-' + id).value;
        
        // Create form data
        const formData = {
            name: name,
            status: status,
            description: description,
            _token: document.querySelector('meta[name="csrf-token"]').content
        };
        
        // Send AJAX request
        fetch('/admin/campaigns/' + id + '/update', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(formData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update the display
                document.getElementById('name-' + id).textContent = name;
                
                // Remove needs-attention class if it was auto-created
                document.getElementById('row-' + id).classList.remove('needs-attention');
                
                // Hide edit form
                cancelEdit(id);
                
                // Show success message
                location.reload(); // Reload to show updated data
            } else {
                alert('Error: ' + (data.message || 'Failed to update campaign'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to update campaign. Please try again.');
        });
        
        return false;
    }
</script>
@endsection

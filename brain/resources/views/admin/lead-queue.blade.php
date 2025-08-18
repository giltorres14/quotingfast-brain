@extends('layouts.app')

@section('title', 'Lead Queue Monitor')

@section('styles')
<style>
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1.5rem;
        margin-bottom: 2rem;
    }
    
    .stat-card {
        background: white;
        padding: 1.5rem;
        border-radius: 1rem;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        text-align: center;
    }
    
    .stat-card.pending {
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
    }
    
    .stat-card.processing {
        background: linear-gradient(135deg, #dbeafe 0%, #93c5fd 100%);
    }
    
    .stat-card.completed {
        background: linear-gradient(135deg, #d1fae5 0%, #86efac 100%);
    }
    
    .stat-card.failed {
        background: linear-gradient(135deg, #fee2e2 0%, #fca5a5 100%);
    }
    
    .stat-number {
        font-size: 3rem;
        font-weight: 700;
        color: #1e293b;
    }
    
    .stat-label {
        color: #64748b;
        font-size: 0.875rem;
        margin-top: 0.5rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .queue-table {
        background: white;
        border-radius: 1rem;
        overflow: hidden;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    }
    
    .table-header {
        background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
        color: white;
        padding: 1rem 1.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .table-title {
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
    
    .badge-pending {
        background: #fef3c7;
        color: #92400e;
    }
    
    .badge-processing {
        background: #dbeafe;
        color: #1e40af;
    }
    
    .badge-completed {
        background: #d1fae5;
        color: #065f46;
    }
    
    .badge-failed {
        background: #fee2e2;
        color: #991b1b;
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
    
    .btn-danger {
        background: #ef4444;
        color: white;
    }
    
    .btn-danger:hover {
        background: #dc2626;
    }
    
    .btn-success {
        background: #10b981;
        color: white;
    }
    
    .btn-success:hover {
        background: #059669;
    }
    
    .btn-sm {
        padding: 0.25rem 0.75rem;
        font-size: 0.875rem;
    }
    
    .bulk-actions {
        display: flex;
        gap: 0.5rem;
        align-items: center;
        padding: 1rem 1.5rem;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
    }
    
    .bulk-actions.hidden {
        display: none;
    }
    
    .checkbox-cell {
        width: 40px;
    }
    
    input[type="checkbox"] {
        width: 18px;
        height: 18px;
        cursor: pointer;
    }
    
    .error-message {
        color: #991b1b;
        font-size: 0.875rem;
        max-width: 300px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    
    .error-message:hover {
        overflow: visible;
        white-space: normal;
        position: relative;
        z-index: 10;
        background: white;
        padding: 0.5rem;
        border-radius: 0.5rem;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    }
    
    .alert {
        padding: 1rem;
        border-radius: 0.5rem;
        margin-bottom: 1rem;
    }
    
    .alert-info {
        background: #dbeafe;
        color: #1e40af;
        border: 1px solid #93c5fd;
    }
    
    .alert-success {
        background: #d1fae5;
        color: #065f46;
        border: 1px solid #86efac;
    }
    
    .alert-error {
        background: #fee2e2;
        color: #991b1b;
        border: 1px solid #fca5a5;
    }
</style>

<!-- Lead Details Modal -->
<div class="modal fade" id="leadDetailsModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Lead Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="leadDetailsContent">
                <!-- Content will be populated by JavaScript -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="processLead()">Process Now</button>
            </div>
        </div>
    </div>
</div>

<script>
function viewLeadDetails(id, name, phone, email, source, createdAt) {
    const content = `
        <div class="lead-details">
            <p><strong>Lead ID:</strong> ${id}</p>
            <p><strong>Name:</strong> ${name}</p>
            <p><strong>Phone:</strong> ${phone}</p>
            <p><strong>Email:</strong> ${email || 'Not provided'}</p>
            <p><strong>Source:</strong> ${source}</p>
            <p><strong>Created:</strong> ${createdAt}</p>
            <p><strong>Status:</strong> <span class="badge badge-warning">Pending in Queue</span></p>
            <hr>
            <p><em>This lead is waiting to be sent to Vici dialer.</em></p>
        </div>
    `;
    
    document.getElementById('leadDetailsContent').innerHTML = content;
    $('#leadDetailsModal').modal('show');
}

function processLead() {
    alert('Processing lead... (This feature will send the lead to Vici)');
    $('#leadDetailsModal').modal('hide');
}

    function showLeadDetail(leadId) {
        // Create modal
        const modal = document.createElement('div');
        modal.style.cssText = 'position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 9999;';
        
        const content = document.createElement('div');
        content.style.cssText = 'background: white; padding: 20px; border-radius: 8px; max-width: 800px; max-height: 80vh; overflow-y: auto;';
        content.innerHTML = '<h2>Loading lead details...</h2>';
        
        modal.appendChild(content);
        document.body.appendChild(modal);
        
        // Fetch lead details
        fetch('/api/leads/' + leadId)
            .then(response => response.json())
            .then(data => {
                content.innerHTML = `
                    <h2>Lead Details</h2>
                    <p><strong>Name:</strong> ${data.name || 'N/A'}</p>
                    <p><strong>Phone:</strong> ${data.phone || 'N/A'}</p>
                    <p><strong>Email:</strong> ${data.email || 'N/A'}</p>
                    <p><strong>Address:</strong> ${data.address || 'N/A'}</p>
                    <p><strong>City:</strong> ${data.city || 'N/A'}</p>
                    <p><strong>State:</strong> ${data.state || 'N/A'}</p>
                    <p><strong>Zip:</strong> ${data.zip_code || 'N/A'}</p>
                    <button onclick="this.closest('div').parentElement.remove()" style="margin-top: 20px; padding: 10px 20px; background: #3b82f6; color: white; border: none; border-radius: 4px; cursor: pointer;">Close</button>
                `;
            })
            .catch(error => {
                content.innerHTML = `
                    <h2>Error Loading Lead</h2>
                    <p>Could not load lead details.</p>
                    <button onclick="this.closest('div').parentElement.remove()" style="margin-top: 20px; padding: 10px 20px; background: #3b82f6; color: white; border: none; border-radius: 4px; cursor: pointer;">Close</button>
                `;
            });
        
        // Close on background click
        modal.onclick = function(e) {
            if (e.target === modal) {
                modal.remove();
            }
        };
    }
    </script>
@endsection

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <h1 style="font-size: 2rem; font-weight: 700; color: #1f2937; margin: 0;">🛡️ Stuck in Queue</h1>
        <div style="display: flex; gap: 0.5rem;">
            <button onclick="location.reload()" class="btn btn-secondary" title="Refresh">🔄 Refresh</button>
            <a href="/admin/lead-queue/process" class="btn btn-primary">▶️ Process Queue</a>
        </div>
    </div>
    
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

    <div class="alert alert-info">
        ℹ️ This queue captures leads that failed to process due to system errors, database issues, or webhook failures. You can review and reprocess them here.
    </div>

    <!-- Statistics -->
    <div class="stats-grid">
        <div class="stat-card pending">
            <div class="stat-number">{{ $stats['pending'] ?? 0 }}</div>
            <div class="stat-label">Pending</div>
        </div>
        <div class="stat-card processing">
            <div class="stat-number">{{ $stats['processing'] ?? 0 }}</div>
            <div class="stat-label">Processing</div>
        </div>
        <div class="stat-card completed">
            <div class="stat-number">{{ $stats['completed'] ?? 0 }}</div>
            <div class="stat-label">Completed (24h)</div>
        </div>
        <div class="stat-card failed">
            <div class="stat-number">{{ $stats['failed'] ?? 0 }}</div>
            <div class="stat-label">Failed</div>
        </div>
    </div>

    <!-- Queue Table -->
    <div class="queue-table">
        <div class="table-header">
            <div class="table-title">Recent Queue Activity</div>
            <div>
                <span style="font-size: 0.875rem; opacity: 0.9;">
                    Auto-refresh: 30s
                </span>
            </div>
        </div>
        
        <!-- Bulk Actions -->
        <div class="bulk-actions hidden" id="bulkActions">
            <span id="selectedCount">0 selected</span>
            <button onclick="bulkReprocess()" class="btn btn-success btn-sm">
                🔄 Reprocess Selected
            </button>
            <button onclick="bulkDelete()" class="btn btn-danger btn-sm">
                🗑️ Delete Selected
            </button>
            <button onclick="clearSelection()" class="btn btn-sm" style="background: #e5e7eb; color: #374151;">
                Clear Selection
            </button>
        </div>
        
        <div style="overflow-x: auto;">
        <table>
            <thead>
                <tr>
                    <th class="checkbox-cell">
                        <input type="checkbox" id="selectAll" onchange="toggleSelectAll()">
                    </th>
                    <th>ID</th>
                    <th>Date</th>
                    <th>Lead Name</th>
                    <th>Phone</th>
                    <th>Status</th>
                    <th>Attempts</th>
                    <th>Queued At</th>
                    <th>Processed At</th>
                    <th>Error</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentQueue as $item)
                    <tr data-id="{{ $item->id }}">
                        <td class="checkbox-cell">
                            <input type="checkbox" class="queue-checkbox" value="{{ $item->id }}" onchange="updateSelection()">
                        </td>
                        <td>#{{ $item->id }}</td>
                        <td>{{ \Carbon\Carbon::parse($item->created_at)->format('m/d/Y H:i') }}</td>
                        <td>{{ $item->lead_name ?? 'Unknown' }}</td>
                        <td>{{ $item->phone ?? 'N/A' }}</td>
                        <td>
                            <span class="badge badge-{{ $item->status }}">
                                {{ strtoupper($item->status) }}
                            </span>
                        </td>
                        <td>{{ $item->attempts }}</td>
                        <td>{{ $item->created_at ? $item->created_at->format('g:i:s A') : '-' }}</td>
                        <td>{{ $item->processed_at ? $item->processed_at->format('g:i:s A') : '-' }}</td>
                        <td>
                            @if($item->error_message)
                                <span class="error-message" title="{{ $item->error_message }}">
                                    {{ $item->error_message }}
                                </span>
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            <div style="display: flex; gap: 0.25rem;">
                                @if($item->status !== 'completed')
                                    <button onclick="reprocessLead({{ $item->id }})" class="btn btn-success btn-sm" title="Reprocess">
                                        🔄
                                    </button>
                                @endif
                                <button onclick="viewDetails({{ $item->id }})" class="btn btn-primary btn-sm" title="View Details">
                                    👁️
                                    </button>
                                <button onclick="deleteLead({{ $item->id }})" class="btn btn-danger btn-sm" title="Delete">
                                    🗑️
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" style="text-align: center; padding: 2rem; color: #6b7280;">
                            No leads in queue. All systems operational! 🎉
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
    </div>

<!-- Lead Details Modal -->
<div class="modal fade" id="leadDetailsModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Lead Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="leadDetailsContent">
                <!-- Content will be populated by JavaScript -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="processLead()">Process Now</button>
            </div>
        </div>
    </div>
</div>

<script>
function viewLeadDetails(id, name, phone, email, source, createdAt) {
    const content = `
        <div class="lead-details">
            <p><strong>Lead ID:</strong> ${id}</p>
            <p><strong>Name:</strong> ${name}</p>
            <p><strong>Phone:</strong> ${phone}</p>
            <p><strong>Email:</strong> ${email || 'Not provided'}</p>
            <p><strong>Source:</strong> ${source}</p>
            <p><strong>Created:</strong> ${createdAt}</p>
            <p><strong>Status:</strong> <span class="badge badge-warning">Pending in Queue</span></p>
            <hr>
            <p><em>This lead is waiting to be sent to Vici dialer.</em></p>
        </div>
    `;
    
    document.getElementById('leadDetailsContent').innerHTML = content;
    $('#leadDetailsModal').modal('show');
}

function processLead() {
    alert('Processing lead... (This feature will send the lead to Vici)');
    $('#leadDetailsModal').modal('hide');
}

    function showLeadDetail(leadId) {
        // Create modal
        const modal = document.createElement('div');
        modal.style.cssText = 'position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 9999;';
        
        const content = document.createElement('div');
        content.style.cssText = 'background: white; padding: 20px; border-radius: 8px; max-width: 800px; max-height: 80vh; overflow-y: auto;';
        content.innerHTML = '<h2>Loading lead details...</h2>';
        
        modal.appendChild(content);
        document.body.appendChild(modal);
        
        // Fetch lead details
        fetch('/api/leads/' + leadId)
            .then(response => response.json())
            .then(data => {
                content.innerHTML = `
                    <h2>Lead Details</h2>
                    <p><strong>Name:</strong> ${data.name || 'N/A'}</p>
                    <p><strong>Phone:</strong> ${data.phone || 'N/A'}</p>
                    <p><strong>Email:</strong> ${data.email || 'N/A'}</p>
                    <p><strong>Address:</strong> ${data.address || 'N/A'}</p>
                    <p><strong>City:</strong> ${data.city || 'N/A'}</p>
                    <p><strong>State:</strong> ${data.state || 'N/A'}</p>
                    <p><strong>Zip:</strong> ${data.zip_code || 'N/A'}</p>
                    <button onclick="this.closest('div').parentElement.remove()" style="margin-top: 20px; padding: 10px 20px; background: #3b82f6; color: white; border: none; border-radius: 4px; cursor: pointer;">Close</button>
                `;
            })
            .catch(error => {
                content.innerHTML = `
                    <h2>Error Loading Lead</h2>
                    <p>Could not load lead details.</p>
                    <button onclick="this.closest('div').parentElement.remove()" style="margin-top: 20px; padding: 10px 20px; background: #3b82f6; color: white; border: none; border-radius: 4px; cursor: pointer;">Close</button>
                `;
            });
        
        // Close on background click
        modal.onclick = function(e) {
            if (e.target === modal) {
                modal.remove();
            }
        };
    }
    </script>
@endsection

@section('scripts')
<script>
    // Auto-refresh every 30 seconds
    setInterval(() => {
        if (!document.querySelector('#detailsModal')) { // Don't refresh if modal is open
            location.reload();
        }
    }, 30000);
    
    function toggleSelectAll() {
        const selectAll = document.getElementById('selectAll');
        const checkboxes = document.querySelectorAll('.queue-checkbox');
        checkboxes.forEach(cb => cb.checked = selectAll.checked);
        updateSelection();
    }
    
    function updateSelection() {
        const checked = document.querySelectorAll('.queue-checkbox:checked');
        const count = checked.length;
        const bulkActions = document.getElementById('bulkActions');
        const selectedCount = document.getElementById('selectedCount');
        
        if (count > 0) {
            bulkActions.classList.remove('hidden');
            selectedCount.textContent = count + ' selected';
        } else {
            bulkActions.classList.add('hidden');
        }
        
        // Update select all checkbox
        const selectAll = document.getElementById('selectAll');
        const total = document.querySelectorAll('.queue-checkbox').length;
        selectAll.checked = count === total && total > 0;
        selectAll.indeterminate = count > 0 && count < total;
    }
    
    function clearSelection() {
        document.getElementById('selectAll').checked = false;
        toggleSelectAll();
    }
    
    function getSelectedIds() {
        return Array.from(document.querySelectorAll('.queue-checkbox:checked'))
            .map(cb => cb.value);
    }
    
    function bulkReprocess() {
        const ids = getSelectedIds();
        if (ids.length === 0) return;
        
        if (!confirm(`Reprocess ${ids.length} selected lead(s)?`)) return;
        
        fetch('/admin/lead-queue/bulk-reprocess', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ ids: ids })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + (data.message || 'Failed to reprocess leads'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to reprocess leads');
        });
    }
    
    function bulkDelete() {
        const ids = getSelectedIds();
        if (ids.length === 0) return;
        
        if (!confirm(`Delete ${ids.length} selected lead(s)? This cannot be undone.`)) return;
        
        fetch('/admin/lead-queue/bulk-delete', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ ids: ids })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + (data.message || 'Failed to delete leads'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to delete leads');
        });
    }
    
    function reprocessLead(id) {
        if (!confirm('Reprocess this lead?')) return;
        
        fetch(`/admin/lead-queue/${id}/reprocess`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + (data.message || 'Failed to reprocess lead'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to reprocess lead');
        });
    }
    
    function deleteLead(id) {
        if (!confirm('Delete this lead from queue? This cannot be undone.')) return;
        
        fetch(`/admin/lead-queue/${id}/delete`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + (data.message || 'Failed to delete lead'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to delete lead');
        });
    }
    
    function viewDetails(id) {
        // For now, just alert. You can implement a modal later
        alert('Lead details modal coming soon. Lead ID: ' + id);
    }

    function showLeadDetail(leadId) {
        // Create modal
        const modal = document.createElement('div');
        modal.style.cssText = 'position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 9999;';
        
        const content = document.createElement('div');
        content.style.cssText = 'background: white; padding: 20px; border-radius: 8px; max-width: 800px; max-height: 80vh; overflow-y: auto;';
        content.innerHTML = '<h2>Loading lead details...</h2>';
        
        modal.appendChild(content);
        document.body.appendChild(modal);
        
        // Fetch lead details
        fetch('/api/leads/' + leadId)
            .then(response => response.json())
            .then(data => {
                content.innerHTML = `
                    <h2>Lead Details</h2>
                    <p><strong>Name:</strong> ${data.name || 'N/A'}</p>
                    <p><strong>Phone:</strong> ${data.phone || 'N/A'}</p>
                    <p><strong>Email:</strong> ${data.email || 'N/A'}</p>
                    <p><strong>Address:</strong> ${data.address || 'N/A'}</p>
                    <p><strong>City:</strong> ${data.city || 'N/A'}</p>
                    <p><strong>State:</strong> ${data.state || 'N/A'}</p>
                    <p><strong>Zip:</strong> ${data.zip_code || 'N/A'}</p>
                    <button onclick="this.closest('div').parentElement.remove()" style="margin-top: 20px; padding: 10px 20px; background: #3b82f6; color: white; border: none; border-radius: 4px; cursor: pointer;">Close</button>
                `;
            })
            .catch(error => {
                content.innerHTML = `
                    <h2>Error Loading Lead</h2>
                    <p>Could not load lead details.</p>
                    <button onclick="this.closest('div').parentElement.remove()" style="margin-top: 20px; padding: 10px 20px; background: #3b82f6; color: white; border: none; border-radius: 4px; cursor: pointer;">Close</button>
                `;
            });
        
        // Close on background click
        modal.onclick = function(e) {
            if (e.target === modal) {
                modal.remove();
            }
        };
    }
    </script>

<!-- Lead Details Modal -->
<div class="modal fade" id="leadDetailsModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Lead Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="leadDetailsContent">
                <!-- Content will be populated by JavaScript -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="processLead()">Process Now</button>
            </div>
        </div>
    </div>
</div>

<script>
function viewLeadDetails(id, name, phone, email, source, createdAt) {
    const content = `
        <div class="lead-details">
            <p><strong>Lead ID:</strong> ${id}</p>
            <p><strong>Name:</strong> ${name}</p>
            <p><strong>Phone:</strong> ${phone}</p>
            <p><strong>Email:</strong> ${email || 'Not provided'}</p>
            <p><strong>Source:</strong> ${source}</p>
            <p><strong>Created:</strong> ${createdAt}</p>
            <p><strong>Status:</strong> <span class="badge badge-warning">Pending in Queue</span></p>
            <hr>
            <p><em>This lead is waiting to be sent to Vici dialer.</em></p>
        </div>
    `;
    
    document.getElementById('leadDetailsContent').innerHTML = content;
    $('#leadDetailsModal').modal('show');
}

function processLead() {
    alert('Processing lead... (This feature will send the lead to Vici)');
    $('#leadDetailsModal').modal('hide');
}

    function showLeadDetail(leadId) {
        // Create modal
        const modal = document.createElement('div');
        modal.style.cssText = 'position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 9999;';
        
        const content = document.createElement('div');
        content.style.cssText = 'background: white; padding: 20px; border-radius: 8px; max-width: 800px; max-height: 80vh; overflow-y: auto;';
        content.innerHTML = '<h2>Loading lead details...</h2>';
        
        modal.appendChild(content);
        document.body.appendChild(modal);
        
        // Fetch lead details
        fetch('/api/leads/' + leadId)
            .then(response => response.json())
            .then(data => {
                content.innerHTML = `
                    <h2>Lead Details</h2>
                    <p><strong>Name:</strong> ${data.name || 'N/A'}</p>
                    <p><strong>Phone:</strong> ${data.phone || 'N/A'}</p>
                    <p><strong>Email:</strong> ${data.email || 'N/A'}</p>
                    <p><strong>Address:</strong> ${data.address || 'N/A'}</p>
                    <p><strong>City:</strong> ${data.city || 'N/A'}</p>
                    <p><strong>State:</strong> ${data.state || 'N/A'}</p>
                    <p><strong>Zip:</strong> ${data.zip_code || 'N/A'}</p>
                    <button onclick="this.closest('div').parentElement.remove()" style="margin-top: 20px; padding: 10px 20px; background: #3b82f6; color: white; border: none; border-radius: 4px; cursor: pointer;">Close</button>
                `;
            })
            .catch(error => {
                content.innerHTML = `
                    <h2>Error Loading Lead</h2>
                    <p>Could not load lead details.</p>
                    <button onclick="this.closest('div').parentElement.remove()" style="margin-top: 20px; padding: 10px 20px; background: #3b82f6; color: white; border: none; border-radius: 4px; cursor: pointer;">Close</button>
                `;
            });
        
        // Close on background click
        modal.onclick = function(e) {
            if (e.target === modal) {
                modal.remove();
            }
        };
    }
    </script>
@endsection
        // Fetch lead details
        fetch('/api/leads/' + leadId)
            .then(response => response.json())
            .then(data => {
                content.innerHTML = `
                    <h2>Lead Details</h2>
                    <p><strong>Name:</strong> ${data.name || 'N/A'}</p>
                    <p><strong>Phone:</strong> ${data.phone || 'N/A'}</p>
                    <p><strong>Email:</strong> ${data.email || 'N/A'}</p>
                    <p><strong>Address:</strong> ${data.address || 'N/A'}</p>
                    <p><strong>City:</strong> ${data.city || 'N/A'}</p>
                    <p><strong>State:</strong> ${data.state || 'N/A'}</p>
                    <p><strong>Zip:</strong> ${data.zip_code || 'N/A'}</p>
                    <button onclick="this.closest('div').parentElement.remove()" style="margin-top: 20px; padding: 10px 20px; background: #3b82f6; color: white; border: none; border-radius: 4px; cursor: pointer;">Close</button>
                `;
            })
            .catch(error => {
                content.innerHTML = `
                    <h2>Error Loading Lead</h2>
                    <p>Could not load lead details.</p>
                    <button onclick="this.closest('div').parentElement.remove()" style="margin-top: 20px; padding: 10px 20px; background: #3b82f6; color: white; border: none; border-radius: 4px; cursor: pointer;">Close</button>
                `;
            });
        
        // Close on background click
        modal.onclick = function(e) {
            if (e.target === modal) {
                modal.remove();
            }
        };
    }
    </script>
@endsection

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <h1 style="font-size: 2rem; font-weight: 700; color: #1f2937; margin: 0;">🛡️ Stuck in Queue</h1>
        <div style="display: flex; gap: 0.5rem;">
            <button onclick="location.reload()" class="btn btn-secondary" title="Refresh">🔄 Refresh</button>
            <a href="/admin/lead-queue/process" class="btn btn-primary">▶️ Process Queue</a>
        </div>
    </div>
    
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

    <div class="alert alert-info">
        ℹ️ This queue captures leads that failed to process due to system errors, database issues, or webhook failures. You can review and reprocess them here.
    </div>

    <!-- Statistics -->
    <div class="stats-grid">
        <div class="stat-card pending">
            <div class="stat-number">{{ $stats['pending'] ?? 0 }}</div>
            <div class="stat-label">Pending</div>
        </div>
        <div class="stat-card processing">
            <div class="stat-number">{{ $stats['processing'] ?? 0 }}</div>
            <div class="stat-label">Processing</div>
        </div>
        <div class="stat-card completed">
            <div class="stat-number">{{ $stats['completed'] ?? 0 }}</div>
            <div class="stat-label">Completed (24h)</div>
        </div>
        <div class="stat-card failed">
            <div class="stat-number">{{ $stats['failed'] ?? 0 }}</div>
            <div class="stat-label">Failed</div>
        </div>
    </div>

    <!-- Queue Table -->
    <div class="queue-table">
        <div class="table-header">
            <div class="table-title">Recent Queue Activity</div>
            <div>
                <span style="font-size: 0.875rem; opacity: 0.9;">
                    Auto-refresh: 30s
                </span>
            </div>
        </div>
        
        <!-- Bulk Actions -->
        <div class="bulk-actions hidden" id="bulkActions">
            <span id="selectedCount">0 selected</span>
            <button onclick="bulkReprocess()" class="btn btn-success btn-sm">
                🔄 Reprocess Selected
            </button>
            <button onclick="bulkDelete()" class="btn btn-danger btn-sm">
                🗑️ Delete Selected
            </button>
            <button onclick="clearSelection()" class="btn btn-sm" style="background: #e5e7eb; color: #374151;">
                Clear Selection
            </button>
        </div>
        
        <div style="overflow-x: auto;">
        <table>
            <thead>
                <tr>
                    <th class="checkbox-cell">
                        <input type="checkbox" id="selectAll" onchange="toggleSelectAll()">
                    </th>
                    <th>ID</th>
                    <th>Date</th>
                    <th>Lead Name</th>
                    <th>Phone</th>
                    <th>Status</th>
                    <th>Attempts</th>
                    <th>Queued At</th>
                    <th>Processed At</th>
                    <th>Error</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentQueue as $item)
                    <tr data-id="{{ $item->id }}">
                        <td class="checkbox-cell">
                            <input type="checkbox" class="queue-checkbox" value="{{ $item->id }}" onchange="updateSelection()">
                        </td>
                        <td>#{{ $item->id }}</td>
                        <td>{{ \Carbon\Carbon::parse($item->created_at)->format('m/d/Y H:i') }}</td>
                        <td>{{ $item->lead_name ?? 'Unknown' }}</td>
                        <td>{{ $item->phone ?? 'N/A' }}</td>
                        <td>
                            <span class="badge badge-{{ $item->status }}">
                                {{ strtoupper($item->status) }}
                            </span>
                        </td>
                        <td>{{ $item->attempts }}</td>
                        <td>{{ $item->created_at ? $item->created_at->format('g:i:s A') : '-' }}</td>
                        <td>{{ $item->processed_at ? $item->processed_at->format('g:i:s A') : '-' }}</td>
                        <td>
                            @if($item->error_message)
                                <span class="error-message" title="{{ $item->error_message }}">
                                    {{ $item->error_message }}
                                </span>
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            <div style="display: flex; gap: 0.25rem;">
                                @if($item->status !== 'completed')
                                    <button onclick="reprocessLead({{ $item->id }})" class="btn btn-success btn-sm" title="Reprocess">
                                        🔄
                                    </button>
                                @endif
                                <button onclick="viewDetails({{ $item->id }})" class="btn btn-primary btn-sm" title="View Details">
                                    👁️
                                    </button>
                                <button onclick="deleteLead({{ $item->id }})" class="btn btn-danger btn-sm" title="Delete">
                                    🗑️
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" style="text-align: center; padding: 2rem; color: #6b7280;">
                            No leads in queue. All systems operational! 🎉
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
    </div>

<!-- Lead Details Modal -->
<div class="modal fade" id="leadDetailsModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Lead Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="leadDetailsContent">
                <!-- Content will be populated by JavaScript -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="processLead()">Process Now</button>
            </div>
        </div>
    </div>
</div>

<script>
function viewLeadDetails(id, name, phone, email, source, createdAt) {
    const content = `
        <div class="lead-details">
            <p><strong>Lead ID:</strong> ${id}</p>
            <p><strong>Name:</strong> ${name}</p>
            <p><strong>Phone:</strong> ${phone}</p>
            <p><strong>Email:</strong> ${email || 'Not provided'}</p>
            <p><strong>Source:</strong> ${source}</p>
            <p><strong>Created:</strong> ${createdAt}</p>
            <p><strong>Status:</strong> <span class="badge badge-warning">Pending in Queue</span></p>
            <hr>
            <p><em>This lead is waiting to be sent to Vici dialer.</em></p>
        </div>
    `;
    
    document.getElementById('leadDetailsContent').innerHTML = content;
    $('#leadDetailsModal').modal('show');
}

function processLead() {
    alert('Processing lead... (This feature will send the lead to Vici)');
    $('#leadDetailsModal').modal('hide');
}

    function showLeadDetail(leadId) {
        // Create modal
        const modal = document.createElement('div');
        modal.style.cssText = 'position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 9999;';
        
        const content = document.createElement('div');
        content.style.cssText = 'background: white; padding: 20px; border-radius: 8px; max-width: 800px; max-height: 80vh; overflow-y: auto;';
        content.innerHTML = '<h2>Loading lead details...</h2>';
        
        modal.appendChild(content);
        document.body.appendChild(modal);
        
        // Fetch lead details
        fetch('/api/leads/' + leadId)
            .then(response => response.json())
            .then(data => {
                content.innerHTML = `
                    <h2>Lead Details</h2>
                    <p><strong>Name:</strong> ${data.name || 'N/A'}</p>
                    <p><strong>Phone:</strong> ${data.phone || 'N/A'}</p>
                    <p><strong>Email:</strong> ${data.email || 'N/A'}</p>
                    <p><strong>Address:</strong> ${data.address || 'N/A'}</p>
                    <p><strong>City:</strong> ${data.city || 'N/A'}</p>
                    <p><strong>State:</strong> ${data.state || 'N/A'}</p>
                    <p><strong>Zip:</strong> ${data.zip_code || 'N/A'}</p>
                    <button onclick="this.closest('div').parentElement.remove()" style="margin-top: 20px; padding: 10px 20px; background: #3b82f6; color: white; border: none; border-radius: 4px; cursor: pointer;">Close</button>
                `;
            })
            .catch(error => {
                content.innerHTML = `
                    <h2>Error Loading Lead</h2>
                    <p>Could not load lead details.</p>
                    <button onclick="this.closest('div').parentElement.remove()" style="margin-top: 20px; padding: 10px 20px; background: #3b82f6; color: white; border: none; border-radius: 4px; cursor: pointer;">Close</button>
                `;
            });
        
        // Close on background click
        modal.onclick = function(e) {
            if (e.target === modal) {
                modal.remove();
            }
        };
    }
    </script>
@endsection

@section('scripts')
<script>
    // Auto-refresh every 30 seconds
    setInterval(() => {
        if (!document.querySelector('#detailsModal')) { // Don't refresh if modal is open
            location.reload();
        }
    }, 30000);
    
    function toggleSelectAll() {
        const selectAll = document.getElementById('selectAll');
        const checkboxes = document.querySelectorAll('.queue-checkbox');
        checkboxes.forEach(cb => cb.checked = selectAll.checked);
        updateSelection();
    }
    
    function updateSelection() {
        const checked = document.querySelectorAll('.queue-checkbox:checked');
        const count = checked.length;
        const bulkActions = document.getElementById('bulkActions');
        const selectedCount = document.getElementById('selectedCount');
        
        if (count > 0) {
            bulkActions.classList.remove('hidden');
            selectedCount.textContent = count + ' selected';
        } else {
            bulkActions.classList.add('hidden');
        }
        
        // Update select all checkbox
        const selectAll = document.getElementById('selectAll');
        const total = document.querySelectorAll('.queue-checkbox').length;
        selectAll.checked = count === total && total > 0;
        selectAll.indeterminate = count > 0 && count < total;
    }
    
    function clearSelection() {
        document.getElementById('selectAll').checked = false;
        toggleSelectAll();
    }
    
    function getSelectedIds() {
        return Array.from(document.querySelectorAll('.queue-checkbox:checked'))
            .map(cb => cb.value);
    }
    
    function bulkReprocess() {
        const ids = getSelectedIds();
        if (ids.length === 0) return;
        
        if (!confirm(`Reprocess ${ids.length} selected lead(s)?`)) return;
        
        fetch('/admin/lead-queue/bulk-reprocess', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ ids: ids })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + (data.message || 'Failed to reprocess leads'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to reprocess leads');
        });
    }
    
    function bulkDelete() {
        const ids = getSelectedIds();
        if (ids.length === 0) return;
        
        if (!confirm(`Delete ${ids.length} selected lead(s)? This cannot be undone.`)) return;
        
        fetch('/admin/lead-queue/bulk-delete', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ ids: ids })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + (data.message || 'Failed to delete leads'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to delete leads');
        });
    }
    
    function reprocessLead(id) {
        if (!confirm('Reprocess this lead?')) return;
        
        fetch(`/admin/lead-queue/${id}/reprocess`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + (data.message || 'Failed to reprocess lead'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to reprocess lead');
        });
    }
    
    function deleteLead(id) {
        if (!confirm('Delete this lead from queue? This cannot be undone.')) return;
        
        fetch(`/admin/lead-queue/${id}/delete`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + (data.message || 'Failed to delete lead'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to delete lead');
        });
    }
    
    function viewDetails(id) {
        // For now, just alert. You can implement a modal later
        alert('Lead details modal coming soon. Lead ID: ' + id);
    }

    function showLeadDetail(leadId) {
        // Create modal
        const modal = document.createElement('div');
        modal.style.cssText = 'position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 9999;';
        
        const content = document.createElement('div');
        content.style.cssText = 'background: white; padding: 20px; border-radius: 8px; max-width: 800px; max-height: 80vh; overflow-y: auto;';
        content.innerHTML = '<h2>Loading lead details...</h2>';
        
        modal.appendChild(content);
        document.body.appendChild(modal);
        
        // Fetch lead details
        fetch('/api/leads/' + leadId)
            .then(response => response.json())
            .then(data => {
                content.innerHTML = `
                    <h2>Lead Details</h2>
                    <p><strong>Name:</strong> ${data.name || 'N/A'}</p>
                    <p><strong>Phone:</strong> ${data.phone || 'N/A'}</p>
                    <p><strong>Email:</strong> ${data.email || 'N/A'}</p>
                    <p><strong>Address:</strong> ${data.address || 'N/A'}</p>
                    <p><strong>City:</strong> ${data.city || 'N/A'}</p>
                    <p><strong>State:</strong> ${data.state || 'N/A'}</p>
                    <p><strong>Zip:</strong> ${data.zip_code || 'N/A'}</p>
                    <button onclick="this.closest('div').parentElement.remove()" style="margin-top: 20px; padding: 10px 20px; background: #3b82f6; color: white; border: none; border-radius: 4px; cursor: pointer;">Close</button>
                `;
            })
            .catch(error => {
                content.innerHTML = `
                    <h2>Error Loading Lead</h2>
                    <p>Could not load lead details.</p>
                    <button onclick="this.closest('div').parentElement.remove()" style="margin-top: 20px; padding: 10px 20px; background: #3b82f6; color: white; border: none; border-radius: 4px; cursor: pointer;">Close</button>
                `;
            });
        
        // Close on background click
        modal.onclick = function(e) {
            if (e.target === modal) {
                modal.remove();
            }
        };
    }
    </script>

<!-- Lead Details Modal -->
<div class="modal fade" id="leadDetailsModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Lead Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="leadDetailsContent">
                <!-- Content will be populated by JavaScript -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="processLead()">Process Now</button>
            </div>
        </div>
    </div>
</div>

<script>
function viewLeadDetails(id, name, phone, email, source, createdAt) {
    const content = `
        <div class="lead-details">
            <p><strong>Lead ID:</strong> ${id}</p>
            <p><strong>Name:</strong> ${name}</p>
            <p><strong>Phone:</strong> ${phone}</p>
            <p><strong>Email:</strong> ${email || 'Not provided'}</p>
            <p><strong>Source:</strong> ${source}</p>
            <p><strong>Created:</strong> ${createdAt}</p>
            <p><strong>Status:</strong> <span class="badge badge-warning">Pending in Queue</span></p>
            <hr>
            <p><em>This lead is waiting to be sent to Vici dialer.</em></p>
        </div>
    `;
    
    document.getElementById('leadDetailsContent').innerHTML = content;
    $('#leadDetailsModal').modal('show');
}

function processLead() {
    alert('Processing lead... (This feature will send the lead to Vici)');
    $('#leadDetailsModal').modal('hide');
}

    function showLeadDetail(leadId) {
        // Create modal
        const modal = document.createElement('div');
        modal.style.cssText = 'position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 9999;';
        
        const content = document.createElement('div');
        content.style.cssText = 'background: white; padding: 20px; border-radius: 8px; max-width: 800px; max-height: 80vh; overflow-y: auto;';
        content.innerHTML = '<h2>Loading lead details...</h2>';
        
        modal.appendChild(content);
        document.body.appendChild(modal);
        
        // Fetch lead details
        fetch('/api/leads/' + leadId)
            .then(response => response.json())
            .then(data => {
                content.innerHTML = `
                    <h2>Lead Details</h2>
                    <p><strong>Name:</strong> ${data.name || 'N/A'}</p>
                    <p><strong>Phone:</strong> ${data.phone || 'N/A'}</p>
                    <p><strong>Email:</strong> ${data.email || 'N/A'}</p>
                    <p><strong>Address:</strong> ${data.address || 'N/A'}</p>
                    <p><strong>City:</strong> ${data.city || 'N/A'}</p>
                    <p><strong>State:</strong> ${data.state || 'N/A'}</p>
                    <p><strong>Zip:</strong> ${data.zip_code || 'N/A'}</p>
                    <button onclick="this.closest('div').parentElement.remove()" style="margin-top: 20px; padding: 10px 20px; background: #3b82f6; color: white; border: none; border-radius: 4px; cursor: pointer;">Close</button>
                `;
            })
            .catch(error => {
                content.innerHTML = `
                    <h2>Error Loading Lead</h2>
                    <p>Could not load lead details.</p>
                    <button onclick="this.closest('div').parentElement.remove()" style="margin-top: 20px; padding: 10px 20px; background: #3b82f6; color: white; border: none; border-radius: 4px; cursor: pointer;">Close</button>
                `;
            });
        
        // Close on background click
        modal.onclick = function(e) {
            if (e.target === modal) {
                modal.remove();
            }
        };
    }
    </script>
@endsection
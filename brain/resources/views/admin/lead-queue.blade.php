@extends('layouts.management')

@section('title', 'Lead Queue Monitor')
@section('page-title', '🛡️ Lead Queue Monitor')

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
@endsection

@section('nav-actions')
<button onclick="location.reload()" class="nav-link" title="Refresh">🔄 Refresh</button>
<a href="/admin/lead-queue/process" class="nav-link">▶️ Process Queue</a>
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
        
        <table>
            <thead>
                <tr>
                    <th class="checkbox-cell">
                        <input type="checkbox" id="selectAll" onchange="toggleSelectAll()">
                    </th>
                    <th>ID</th>
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
</script>
@endsection
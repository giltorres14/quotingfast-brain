@extends('layouts.app')

@section('title', 'Duplicate Lead Queue')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-lg p-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-900">Duplicate Lead Queue</h1>
            <div class="text-sm text-gray-600">
                <span class="font-semibold">{{ $duplicates->total() }}</span> pending duplicates
            </div>
        </div>

        @if($duplicates->count() > 0)
            <!-- Bulk Actions -->
            <div class="mb-4 p-4 bg-gray-50 rounded-lg">
                <div class="flex items-center gap-4">
                    <label class="flex items-center">
                        <input type="checkbox" id="select-all" class="rounded border-gray-300">
                        <span class="ml-2 text-sm font-medium">Select All</span>
                    </label>
                    <button onclick="bulkAction('reject')" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 text-sm">
                        Reject Selected
                    </button>
                    <button onclick="bulkAction('reengage')" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">
                        Re-engage Selected
                    </button>
                    <button onclick="bulkAction('update-existing')" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-sm">
                        Update Existing
                    </button>
                </div>
            </div>

            <!-- Duplicates Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white border border-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <input type="checkbox" id="select-all-table" class="rounded border-gray-300">
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vendor</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Source</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Original Lead</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Days Since</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Received</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($duplicates as $duplicate)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-4 whitespace-nowrap">
                                <input type="checkbox" class="duplicate-checkbox rounded border-gray-300" value="{{ $duplicate->id }}">
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $duplicate->phone_normalized }}
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $duplicate->vendor }}
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $duplicate->source }}
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                                @if($duplicate->originalLead)
                                    <a href="/agent/lead/{{ $duplicate->originalLead->id }}?mode=view" 
                                       class="text-blue-600 hover:text-blue-900" target="_blank">
                                        #{{ $duplicate->originalLead->id }}
                                    </a>
                                @else
                                    <span class="text-gray-400">#{{ $duplicate->original_lead_id }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $duplicate->days_since_original ?? 'N/A' }}
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $duplicate->created_at->format('M j, Y g:i A') }}
                            </td>
                            <td class="px-4 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <button onclick="viewPayload({{ $duplicate->id }})" 
                                            class="text-blue-600 hover:text-blue-900 text-xs">
                                        View
                                    </button>
                                    <button onclick="takeAction({{ $duplicate->id }}, 'reject')" 
                                            class="text-red-600 hover:text-red-900 text-xs">
                                        Reject
                                    </button>
                                    <button onclick="takeAction({{ $duplicate->id }}, 'reengage')" 
                                            class="text-blue-600 hover:text-blue-900 text-xs">
                                        Re-engage
                                    </button>
                                    <button onclick="takeAction({{ $duplicate->id }}, 'update-existing')" 
                                            class="text-green-600 hover:text-green-900 text-xs">
                                        Update
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-6">
                {{ $duplicates->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <div class="text-gray-500 text-lg mb-4">🎉 No pending duplicates!</div>
                <div class="text-gray-400 text-sm">All incoming leads are being processed normally.</div>
            </div>
        @endif
    </div>
</div>

<!-- Payload Modal -->
<div id="payload-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg max-w-4xl w-full max-h-screen overflow-y-auto">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Duplicate Lead Payload</h3>
                    <button onclick="closePayloadModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <pre id="payload-content" class="bg-gray-100 p-4 rounded text-sm overflow-x-auto"></pre>
            </div>
        </div>
    </div>
</div>

<script>
// Select all functionality
document.getElementById('select-all').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.duplicate-checkbox');
    checkboxes.forEach(checkbox => checkbox.checked = this.checked);
});

document.getElementById('select-all-table').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.duplicate-checkbox');
    checkboxes.forEach(checkbox => checkbox.checked = this.checked);
});

// Individual action
function takeAction(queueId, action) {
    if (!confirm(`Are you sure you want to ${action} this duplicate?`)) {
        return;
    }

    fetch(`/api/duplicates/${action}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ queue_id: queueId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred');
    });
}

// Bulk action
function bulkAction(action) {
    const selectedIds = Array.from(document.querySelectorAll('.duplicate-checkbox:checked'))
        .map(checkbox => checkbox.value);
    
    if (selectedIds.length === 0) {
        alert('Please select at least one duplicate');
        return;
    }

    if (!confirm(`Are you sure you want to ${action} ${selectedIds.length} duplicates?`)) {
        return;
    }

    fetch('/api/duplicates/bulk-action', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ 
            action: action, 
            queue_ids: selectedIds 
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred');
    });
}

// View payload
function viewPayload(queueId) {
    // This would need to be implemented to fetch the payload data
    // For now, just show a placeholder
    document.getElementById('payload-content').textContent = 'Payload data would be displayed here...';
    document.getElementById('payload-modal').classList.remove('hidden');
}

function closePayloadModal() {
    document.getElementById('payload-modal').classList.add('hidden');
}
</script>
@endsection


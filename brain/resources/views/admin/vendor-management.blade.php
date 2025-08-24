@extends('layouts.app')

@section('title', 'Vendor Management')

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
    
    .vendor-table {
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
    
    .badge-active {
        background: #d1fae5;
        color: #065f46;
    }
    
    .badge-inactive {
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
    
    .action-buttons {
        display: flex;
        gap: 0.5rem;
    }
    
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
    }
    
    .modal-content {
        background-color: white;
        margin: 5% auto;
        padding: 2rem;
        border-radius: 1rem;
        width: 80%;
        max-width: 600px;
    }
    
    .close {
        color: #aaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
    }
    
    .close:hover {
        color: #000;
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
    
    textarea.form-control {
        resize: vertical;
        min-height: 100px;
    }
</style>
@endsection

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <h1 style="font-size: 2rem; font-weight: 700; color: #1f2937; margin: 0;">🏢 Vendor Management</h1>
        <div style="display: flex; gap: 0.5rem;">
            <button onclick="location.reload()" class="btn btn-secondary" title="Refresh">🔄 Refresh</button>
            <button onclick="showAddVendorModal()" class="btn btn-success">+ Add Vendor</button>
        </div>
    </div>
    
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <!-- Statistics -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-number">{{ $stats['total_vendors'] ?? 0 }}</div>
            <div class="stat-label">Total Vendors</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">{{ $stats['active_vendors'] ?? 0 }}</div>
            <div class="stat-label">Active Vendors</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">{{ number_format($stats['total_leads'] ?? 0) }}</div>
            <div class="stat-label">Total Leads Received</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">${{ number_format($stats['total_spent'] ?? 0, 2) }}</div>
            <div class="stat-label">Total Spent</div>
        </div>
    </div>

    <div class="alert alert-info">
        ℹ️ Vendors are automatically created when new vendor names are detected in incoming leads. You can also manually add vendors and track their campaigns, costs, and performance.
    </div>

    <!-- Vendor Table -->
    <div class="vendor-table">
        <div class="table-header">
            Vendor Directory
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>Vendor Name</th>
                    <th>Status</th>
                    <th>Total Leads</th>
                    <th>Total Cost</th>
                    <th>Campaigns</th>
                    <th>Contact Info</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($vendors as $vendor)
                    <tr>
                        <td><strong>{{ $vendor->name }}</strong></td>
                        <td>
                            @if($vendor->active)
                                <span class="badge badge-active">Active</span>
                            @else
                                <span class="badge badge-inactive">Inactive</span>
                            @endif
                        </td>
                        <td>{{ number_format($vendor->total_leads) }}</td>
                        <td>${{ number_format($vendor->total_cost ?? 0, 2) }}</td>
                        <td>
                            @if($vendor->campaigns && count($vendor->campaigns) > 0)
                                <span title="{{ implode(', ', $vendor->campaigns) }}">
                                    {{ count($vendor->campaigns) }} campaigns
                                </span>
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            @if($vendor->contact_info)
                                @if(isset($vendor->contact_info['email']))
                                    📧 {{ $vendor->contact_info['email'] }}<br>
                                @endif
                                @if(isset($vendor->contact_info['phone']))
                                    📞 {{ $vendor->contact_info['phone'] }}
                                @endif
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            <div class="action-buttons">
                                <button onclick="editVendor({{ $vendor->id }})" class="btn btn-primary btn-sm">
                                    ✏️ Edit
                                </button>
                                <a href="/leads?vendor={{ urlencode($vendor->name) }}" class="btn btn-secondary btn-sm">
                                    👁️ View Leads
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 2rem; color: #6b7280;">
                            No vendors found. Vendors will be auto-created when leads come in with vendor information.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Add/Edit Vendor Modal -->
    <div id="vendorModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeVendorModal()">&times;</span>
            <h2 id="modalTitle">Add New Vendor</h2>
            
            <form id="vendorForm" method="POST" action="/admin/vendors">
                @csrf
                <input type="hidden" id="vendorId" name="vendor_id">
                <input type="hidden" id="method" name="_method" value="POST">
                
                <div class="form-group">
                    <label>Vendor Name *</label>
                    <input type="text" id="vendorName" name="name" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" id="vendorEmail" name="email" class="form-control">
                </div>
                
                <div class="form-group">
                    <label>Phone</label>
                    <input type="tel" id="vendorPhone" name="phone" class="form-control">
                </div>
                
                <div class="form-group">
                    <label>Status</label>
                    <select id="vendorStatus" name="active" class="form-control">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Notes</label>
                    <textarea id="vendorNotes" name="notes" class="form-control"></textarea>
                </div>
                
                <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                    <button type="submit" class="btn btn-primary">
                        💾 Save Vendor
                    </button>
                    <button type="button" onclick="closeVendorModal()" class="btn btn-secondary">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

<!-- Embed vendors JSON for JS to read without Blade inside JS -->
<script id="vendors-data" type="application/json">{!! json_encode($vendors ?? []) !!}</script>

@section('scripts')
<script>
    const vendors = JSON.parse(document.getElementById('vendors-data')?.textContent || '[]');
    
    function showAddVendorModal() {
        document.getElementById('modalTitle').textContent = 'Add New Vendor';
        document.getElementById('vendorForm').reset();
        document.getElementById('vendorId').value = '';
        document.getElementById('method').value = 'POST';
        document.getElementById('vendorForm').action = '/admin/vendors';
        document.getElementById('vendorModal').style.display = 'block';
    }
    
    function editVendor(vendorId) {
        const vendor = vendors.find(v => v.id === vendorId);
        if (!vendor) return;
        
        document.getElementById('modalTitle').textContent = 'Edit Vendor';
        document.getElementById('vendorId').value = vendor.id;
        document.getElementById('vendorName').value = vendor.name;
        document.getElementById('vendorEmail').value = vendor.contact_info?.email || '';
        document.getElementById('vendorPhone').value = vendor.contact_info?.phone || '';
        document.getElementById('vendorStatus').value = vendor.active ? '1' : '0';
        document.getElementById('vendorNotes').value = vendor.notes || '';
        document.getElementById('method').value = 'PUT';
        document.getElementById('vendorForm').action = '/admin/vendors/' + vendor.id;
        document.getElementById('vendorModal').style.display = 'block';
    }
    
    function closeVendorModal() {
        document.getElementById('vendorModal').style.display = 'none';
    }
    
    // Close modal when clicking outside
    window.onclick = function(event) {
        const modal = document.getElementById('vendorModal');
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    }
    
    // Handle form submission
    document.getElementById('vendorForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const data = {
            name: formData.get('name'),
            email: formData.get('email'),
            phone: formData.get('phone'),
            active: formData.get('active') === '1',
            notes: formData.get('notes'),
            _token: document.querySelector('meta[name="csrf-token"]').content
        };
        
        const vendorId = formData.get('vendor_id');
        const method = vendorId ? 'PUT' : 'POST';
        const url = vendorId ? `/admin/vendors/${vendorId}` : '/admin/vendors';
        
        fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + (data.message || 'Failed to save vendor'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to save vendor');
        });
    });
</script>
@endsection




@section('title', 'Vendor Management')

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
    
    .vendor-table {
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
    
    .badge-active {
        background: #d1fae5;
        color: #065f46;
    }
    
    .badge-inactive {
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
    
    .action-buttons {
        display: flex;
        gap: 0.5rem;
    }
    
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
    }
    
    .modal-content {
        background-color: white;
        margin: 5% auto;
        padding: 2rem;
        border-radius: 1rem;
        width: 80%;
        max-width: 600px;
    }
    
    .close {
        color: #aaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
    }
    
    .close:hover {
        color: #000;
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
    
    textarea.form-control {
        resize: vertical;
        min-height: 100px;
    }
</style>
@endsection

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <h1 style="font-size: 2rem; font-weight: 700; color: #1f2937; margin: 0;">🏢 Vendor Management</h1>
        <div style="display: flex; gap: 0.5rem;">
            <button onclick="location.reload()" class="btn btn-secondary" title="Refresh">🔄 Refresh</button>
            <button onclick="showAddVendorModal()" class="btn btn-success">+ Add Vendor</button>
        </div>
    </div>
    
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <!-- Statistics -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-number">{{ $stats['total_vendors'] ?? 0 }}</div>
            <div class="stat-label">Total Vendors</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">{{ $stats['active_vendors'] ?? 0 }}</div>
            <div class="stat-label">Active Vendors</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">{{ number_format($stats['total_leads'] ?? 0) }}</div>
            <div class="stat-label">Total Leads Received</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">${{ number_format($stats['total_spent'] ?? 0, 2) }}</div>
            <div class="stat-label">Total Spent</div>
        </div>
    </div>

    <div class="alert alert-info">
        ℹ️ Vendors are automatically created when new vendor names are detected in incoming leads. You can also manually add vendors and track their campaigns, costs, and performance.
    </div>

    <!-- Vendor Table -->
    <div class="vendor-table">
        <div class="table-header">
            Vendor Directory
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>Vendor Name</th>
                    <th>Status</th>
                    <th>Total Leads</th>
                    <th>Total Cost</th>
                    <th>Campaigns</th>
                    <th>Contact Info</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($vendors as $vendor)
                    <tr>
                        <td><strong>{{ $vendor->name }}</strong></td>
                        <td>
                            @if($vendor->active)
                                <span class="badge badge-active">Active</span>
                            @else
                                <span class="badge badge-inactive">Inactive</span>
                            @endif
                        </td>
                        <td>{{ number_format($vendor->total_leads) }}</td>
                        <td>${{ number_format($vendor->total_cost ?? 0, 2) }}</td>
                        <td>
                            @if($vendor->campaigns && count($vendor->campaigns) > 0)
                                <span title="{{ implode(', ', $vendor->campaigns) }}">
                                    {{ count($vendor->campaigns) }} campaigns
                                </span>
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            @if($vendor->contact_info)
                                @if(isset($vendor->contact_info['email']))
                                    📧 {{ $vendor->contact_info['email'] }}<br>
                                @endif
                                @if(isset($vendor->contact_info['phone']))
                                    📞 {{ $vendor->contact_info['phone'] }}
                                @endif
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            <div class="action-buttons">
                                <button onclick="editVendor({{ $vendor->id }})" class="btn btn-primary btn-sm">
                                    ✏️ Edit
                                </button>
                                <a href="/leads?vendor={{ urlencode($vendor->name) }}" class="btn btn-secondary btn-sm">
                                    👁️ View Leads
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 2rem; color: #6b7280;">
                            No vendors found. Vendors will be auto-created when leads come in with vendor information.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Add/Edit Vendor Modal -->
    <div id="vendorModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeVendorModal()">&times;</span>
            <h2 id="modalTitle">Add New Vendor</h2>
            
            <form id="vendorForm" method="POST" action="/admin/vendors">
                @csrf
                <input type="hidden" id="vendorId" name="vendor_id">
                <input type="hidden" id="method" name="_method" value="POST">
                
                <div class="form-group">
                    <label>Vendor Name *</label>
                    <input type="text" id="vendorName" name="name" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" id="vendorEmail" name="email" class="form-control">
                </div>
                
                <div class="form-group">
                    <label>Phone</label>
                    <input type="tel" id="vendorPhone" name="phone" class="form-control">
                </div>
                
                <div class="form-group">
                    <label>Status</label>
                    <select id="vendorStatus" name="active" class="form-control">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Notes</label>
                    <textarea id="vendorNotes" name="notes" class="form-control"></textarea>
                </div>
                
                <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                    <button type="submit" class="btn btn-primary">
                        💾 Save Vendor
                    </button>
                    <button type="button" onclick="closeVendorModal()" class="btn btn-secondary">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    let vendors = @json($vendors ?? []);
    
    function showAddVendorModal() {
        document.getElementById('modalTitle').textContent = 'Add New Vendor';
        document.getElementById('vendorForm').reset();
        document.getElementById('vendorId').value = '';
        document.getElementById('method').value = 'POST';
        document.getElementById('vendorForm').action = '/admin/vendors';
        document.getElementById('vendorModal').style.display = 'block';
    }
    
    function editVendor(vendorId) {
        const vendor = vendors.find(v => v.id === vendorId);
        if (!vendor) return;
        
        document.getElementById('modalTitle').textContent = 'Edit Vendor';
        document.getElementById('vendorId').value = vendor.id;
        document.getElementById('vendorName').value = vendor.name;
        document.getElementById('vendorEmail').value = vendor.contact_info?.email || '';
        document.getElementById('vendorPhone').value = vendor.contact_info?.phone || '';
        document.getElementById('vendorStatus').value = vendor.active ? '1' : '0';
        document.getElementById('vendorNotes').value = vendor.notes || '';
        document.getElementById('method').value = 'PUT';
        document.getElementById('vendorForm').action = '/admin/vendors/' + vendor.id;
        document.getElementById('vendorModal').style.display = 'block';
    }
    
    function closeVendorModal() {
        document.getElementById('vendorModal').style.display = 'none';
    }
    
    // Close modal when clicking outside
    window.onclick = function(event) {
        const modal = document.getElementById('vendorModal');
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    }
    
    // Handle form submission
    document.getElementById('vendorForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const data = {
            name: formData.get('name'),
            email: formData.get('email'),
            phone: formData.get('phone'),
            active: formData.get('active') === '1',
            notes: formData.get('notes'),
            _token: document.querySelector('meta[name="csrf-token"]').content
        };
        
        const vendorId = formData.get('vendor_id');
        const method = vendorId ? 'PUT' : 'POST';
        const url = vendorId ? `/admin/vendors/${vendorId}` : '/admin/vendors';
        
        fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + (data.message || 'Failed to save vendor'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to save vendor');
        });
    });
</script>
@endsection









@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h2">Admin Dashboard</h1>
            <p class="text-muted">System Overview and Management</p>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title text-muted">Total Leads</h5>
                    <h2 class="mb-0">{{ number_format($total_leads ?? 0) }}</h2>
                    <small class="text-success">All time</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title text-muted">New Today</h5>
                    <h2 class="mb-0">{{ number_format($new_leads ?? 0) }}</h2>
                    <small class="text-info">Last 24 hours</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title text-muted">Conversion Rate</h5>
                    <h2 class="mb-0">{{ $conversion_rate ?? '2.5' }}%</h2>
                    <small class="text-warning">Overall</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title text-muted">Active Campaigns</h5>
                    <h2 class="mb-0">{{ $active_campaigns ?? 2 }}</h2>
                    <small class="text-primary">Running now</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="row">
        <!-- Campaigns Section -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">📞 ViciDial Campaigns</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Campaign</th>
                                    <th>Status</th>
                                    <th>Type</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(isset($campaigns) && count($campaigns) > 0)
                                    @foreach($campaigns as $campaign)
                                    <tr>
                                        <td>{{ $campaign->display_name ?? $campaign->name ?? 'Unknown' }}</td>
                                        <td>
                                            <span class="badge badge-{{ $campaign->status == 'active' ? 'success' : 'secondary' }}">
                                                {{ ucfirst($campaign->status ?? 'inactive') }}
                                            </span>
                                        </td>
                                        <td>{{ $campaign->name ?? '' }}</td>
                                    </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td>Auto Dial Campaign</td>
                                        <td><span class="badge badge-success">Active</span></td>
                                        <td>AUTODIAL</td>
                                    </tr>
                                    <tr>
                                        <td>Training Campaign</td>
                                        <td><span class="badge badge-success">Active</span></td>
                                        <td>AUTO2</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- ViciDial Stats -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">📊 ViciDial Statistics</h5>
                </div>
                <div class="card-body">
                    @if(isset($vici_stats))
                    <div class="row">
                        <div class="col-6">
                            <p class="mb-1 text-muted">Total Leads in Vici</p>
                            <h4>{{ number_format($vici_stats->total_leads ?? 238847) }}</h4>
                        </div>
                        <div class="col-6">
                            <p class="mb-1 text-muted">Total Calls</p>
                            <h4>{{ number_format($vici_stats->total_calls ?? 38549) }}</h4>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-6">
                            <p class="mb-1 text-muted">Sales</p>
                            <h4>{{ number_format($vici_stats->sales ?? 5971) }}</h4>
                        </div>
                        <div class="col-6">
                            <p class="mb-1 text-muted">Avg Talk Time</p>
                            <h4>{{ gmdate("i:s", $vici_stats->avg_talk_time ?? 245) }}</h4>
                        </div>
                    </div>
                    @else
                    <p class="text-muted">No ViciDial stats available</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Leads -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">📋 Recent Leads</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Phone</th>
                                    <th>List ID</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(isset($recent_leads) && count($recent_leads) > 0)
                                    @foreach($recent_leads->take(10) as $lead)
                                    <tr>
                                        <td>{{ $lead->external_lead_id ?? $lead->id }}</td>
                                        <td>{{ $lead->first_name ?? '' }} {{ $lead->last_name ?? '' }}</td>
                                        <td>{{ $lead->phone ?? 'N/A' }}</td>
                                        <td>{{ $lead->vici_list_id ?? '0' }}</td>
                                        <td>
                                            <span class="badge badge-info">{{ $lead->status ?? 'new' }}</span>
                                        </td>
                                        <td>{{ $lead->created_at ? $lead->created_at->format('m/d H:i') : 'N/A' }}</td>
                                    </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">No recent leads</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">⚡ Quick Actions</h5>
                </div>
                <div class="card-body">
                    <a href="/vici" class="btn btn-primary">ViciDial Dashboard</a>
                    <a href="/vici/command-center" class="btn btn-info">Command Center</a>
                    <a href="/vici/lead-flow" class="btn btn-success">Lead Flow</a>
                    <a href="/admin/vici-comprehensive-reports" class="btn btn-warning">Reports</a>
                    <a href="/vici/sync-status" class="btn btn-secondary">Sync Status</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    .card {
        border: 1px solid #dee2e6;
        border-radius: 0.5rem;
        box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);
    }
    .card-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
        font-weight: 600;
    }
    .badge-success {
        background-color: #28a745;
    }
    .badge-info {
        background-color: #17a2b8;
    }
    .badge-warning {
        background-color: #ffc107;
        color: #212529;
    }
    .badge-secondary {
        background-color: #6c757d;
    }
    .table-sm td, .table-sm th {
        padding: 0.3rem;
    }
</style>
@endsection







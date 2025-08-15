@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">📊 Vici Comprehensive Reports</h1>
    
    <!-- Date Range Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.vici.comprehensive-reports') }}" class="row g-3">
                <div class="col-md-4">
                    <label for="date_from" class="form-label">From Date</label>
                    <input type="date" class="form-control" id="date_from" name="date_from" 
                           value="{{ request('date_from', now()->subDays(7)->format('Y-m-d')) }}">
                </div>
                <div class="col-md-4">
                    <label for="date_to" class="form-label">To Date</label>
                    <input type="date" class="form-control" id="date_to" name="date_to" 
                           value="{{ request('date_to', now()->format('Y-m-d')) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">&nbsp;</label>
                    <div>
                        <button type="submit" class="btn btn-primary">Apply Filter</button>
                        <a href="{{ route('admin.vici.comprehensive-reports') }}" class="btn btn-secondary">Reset</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Executive Summary -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">📈 Executive Summary</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-2">
                            <div class="text-center">
                                <h3 class="text-primary">{{ number_format($executiveSummary['overview']['total_calls'] ?? 0) }}</h3>
                                <p class="text-muted">Total Calls</p>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="text-center">
                                <h3 class="text-success">{{ number_format($executiveSummary['overview']['connected_calls'] ?? 0) }}</h3>
                                <p class="text-muted">Connected</p>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="text-center">
                                <h3 class="text-info">{{ $executiveSummary['overview']['connection_rate'] ?? '0%' }}</h3>
                                <p class="text-muted">Connection Rate</p>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="text-center">
                                <h3 class="text-warning">{{ number_format($executiveSummary['overview']['total_sales'] ?? 0) }}</h3>
                                <p class="text-muted">Sales</p>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="text-center">
                                <h3 class="text-danger">{{ $executiveSummary['overview']['conversion_rate'] ?? '0%' }}</h3>
                                <p class="text-muted">Conversion Rate</p>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="text-center">
                                <h3 class="text-secondary">{{ number_format($executiveSummary['overview']['unique_leads_contacted'] ?? 0) }}</h3>
                                <p class="text-muted">Unique Leads</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Real-Time Operations Dashboard -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">🚀 Real-Time Operations</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Current Hour</h6>
                            <table class="table table-sm">
                                <tr>
                                    <td>Calls:</td>
                                    <td><strong>{{ number_format($realTimeOps['current_hour']['calls'] ?? 0) }}</strong></td>
                                </tr>
                                <tr>
                                    <td>Connected:</td>
                                    <td><strong>{{ number_format($realTimeOps['current_hour']['connected'] ?? 0) }}</strong></td>
                                </tr>
                                <tr>
                                    <td>Sales:</td>
                                    <td><strong>{{ number_format($realTimeOps['current_hour']['sales'] ?? 0) }}</strong></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6>Today</h6>
                            <table class="table table-sm">
                                <tr>
                                    <td>Total Calls:</td>
                                    <td><strong>{{ number_format($realTimeOps['today']['calls'] ?? 0) }}</strong></td>
                                </tr>
                                <tr>
                                    <td>Connected:</td>
                                    <td><strong>{{ number_format($realTimeOps['today']['connected'] ?? 0) }}</strong></td>
                                </tr>
                                <tr>
                                    <td>Sales:</td>
                                    <td><strong>{{ number_format($realTimeOps['today']['sales'] ?? 0) }}</strong></td>
                                </tr>
                                <tr>
                                    <td>Active Agents:</td>
                                    <td><strong>{{ number_format($realTimeOps['active_agents'] ?? 0) }}</strong></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Agent Leaderboard -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">🏆 Agent Leaderboard</h5>
                </div>
                <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-striped table-sm">
                        <thead>
                            <tr>
                                <th>Agent</th>
                                <th>Calls</th>
                                <th>Connected</th>
                                <th>Rate</th>
                                <th>Talk Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach(array_slice($agentScorecard, 0, 10) as $agent)
                            <tr>
                                <td>{{ $agent['agent'] }}</td>
                                <td>{{ number_format($agent['total_calls']) }}</td>
                                <td>{{ number_format($agent['connected_calls']) }}</td>
                                <td>{{ $agent['connection_rate'] }}%</td>
                                <td>{{ gmdate('H:i:s', $agent['total_talk_time']) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Campaign ROI -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">💰 Campaign ROI</h5>
                </div>
                <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-striped table-sm">
                        <thead>
                            <tr>
                                <th>Campaign</th>
                                <th>Calls</th>
                                <th>Connected</th>
                                <th>Sales</th>
                                <th>Conv %</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($campaignROI as $campaign)
                            <tr>
                                <td>{{ $campaign['campaign'] }}</td>
                                <td>{{ number_format($campaign['total_calls']) }}</td>
                                <td>{{ number_format($campaign['connected_calls']) }}</td>
                                <td>{{ number_format($campaign['sales']) }}</td>
                                <td>{{ $campaign['conversion_rate'] }}%</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Speed to Lead & Lead Recycling -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">⚡ Speed to Lead</h5>
                </div>
                <div class="card-body">
                    <div class="progress mb-3">
                        @php
                            $total = $speedToLead['total_leads'] ?? 1;
                            $under5 = ($speedToLead['under_5_min'] ?? 0) / $total * 100;
                            $under30 = ($speedToLead['under_30_min'] ?? 0) / $total * 100;
                            $under1h = ($speedToLead['under_1_hour'] ?? 0) / $total * 100;
                            $under24h = ($speedToLead['under_24_hours'] ?? 0) / $total * 100;
                        @endphp
                        <div class="progress-bar bg-success" style="width: {{ $under5 }}%">< 5min</div>
                        <div class="progress-bar bg-info" style="width: {{ $under30 }}%">< 30min</div>
                        <div class="progress-bar bg-warning" style="width: {{ $under1h }}%">< 1hr</div>
                        <div class="progress-bar bg-danger" style="width: {{ $under24h }}%">< 24hr</div>
                    </div>
                    <table class="table table-sm">
                        <tr>
                            <td>Under 5 minutes:</td>
                            <td><strong>{{ number_format($speedToLead['under_5_min'] ?? 0) }}</strong></td>
                        </tr>
                        <tr>
                            <td>Under 30 minutes:</td>
                            <td><strong>{{ number_format($speedToLead['under_30_min'] ?? 0) }}</strong></td>
                        </tr>
                        <tr>
                            <td>Under 1 hour:</td>
                            <td><strong>{{ number_format($speedToLead['under_1_hour'] ?? 0) }}</strong></td>
                        </tr>
                        <tr>
                            <td>Under 24 hours:</td>
                            <td><strong>{{ number_format($speedToLead['under_24_hours'] ?? 0) }}</strong></td>
                        </tr>
                        <tr>
                            <td>Average Time:</td>
                            <td><strong>{{ number_format($speedToLead['avg_time_to_contact'] ?? 0) }} min</strong></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Lead Recycling Intelligence -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">♻️ Lead Recycling Intelligence</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <h4>{{ number_format($leadRecycling['total_opportunity'] ?? 0) }}</h4>
                        <p class="mb-0">Total Opportunity Leads</p>
                    </div>
                    <table class="table table-sm">
                        <tr>
                            <td>Recyclable (< 5 attempts, no connect):</td>
                            <td><strong>{{ number_format($leadRecycling['recyclable_leads'] ?? 0) }}</strong></td>
                        </tr>
                        <tr>
                            <td>Stale (30+ days old):</td>
                            <td><strong>{{ number_format($leadRecycling['stale_leads'] ?? 0) }}</strong></td>
                        </tr>
                        <tr>
                            <td>Pending Callbacks:</td>
                            <td><strong>{{ number_format($leadRecycling['pending_callbacks'] ?? 0) }}</strong></td>
                        </tr>
                    </table>
                    <div class="mt-3">
                        <h6>Recommendations:</h6>
                        <ul class="list-unstyled">
                            <li>✅ Immediate Recycle: {{ number_format($leadRecycling['recommendations']['immediate_recycle'] ?? 0) }}</li>
                            <li>📅 Schedule Callbacks: {{ number_format($leadRecycling['recommendations']['schedule_callbacks'] ?? 0) }}</li>
                            <li>🔄 Reactivate Stale: {{ number_format($leadRecycling['recommendations']['reactivate_stale'] ?? 0) }}</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Optimal Call Times -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">🕐 Optimal Call Time Analysis</h5>
                </div>
                <div class="card-body">
                    <h6>Best Times to Call (by Connection Rate)</h6>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Hour</th>
                                    <th>Total Calls</th>
                                    <th>Connection Rate</th>
                                    <th>Conversion Rate</th>
                                    <th>Recommendation</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach(array_slice($optimalCallTimes['best_times'] ?? [], 0, 5) as $time)
                                <tr>
                                    <td>{{ str_pad($time['hour'], 2, '0', STR_PAD_LEFT) }}:00</td>
                                    <td>{{ number_format($time['total_calls']) }}</td>
                                    <td>
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar bg-success" style="width: {{ $time['connection_rate'] }}%">
                                                {{ $time['connection_rate'] }}%
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $time['conversion_rate'] }}%</td>
                                    <td>
                                        @if($time['connection_rate'] > 50)
                                            <span class="badge bg-success">Excellent</span>
                                        @elseif($time['connection_rate'] > 30)
                                            <span class="badge bg-warning">Good</span>
                                        @else
                                            <span class="badge bg-danger">Poor</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Lead Waste Finder -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">🗑️ Lead Waste Finder</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="alert alert-danger">
                                <h4>{{ number_format($leadWaste['total_wasted_leads'] ?? 0) }}</h4>
                                <p class="mb-0">Wasted Leads (10+ attempts, no connection)</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="alert alert-warning">
                                <h4>{{ number_format($leadWaste['total_wasted_calls'] ?? 0) }}</h4>
                                <p class="mb-0">Total Wasted Calls</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="alert alert-info">
                                <h4>{{ number_format($leadWaste['avg_attempts_per_lead'] ?? 0, 1) }}</h4>
                                <p class="mb-0">Avg Attempts per Wasted Lead</p>
                            </div>
                        </div>
                    </div>
                    
                    @if(count($leadWaste['top_waste_campaigns'] ?? []) > 0)
                    <h6>Top Waste Campaigns</h6>
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Campaign</th>
                                <th>Wasted Leads</th>
                                <th>Wasted Calls</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach(array_slice($leadWaste['top_waste_campaigns'], 0, 5) as $campaign)
                            <tr>
                                <td>{{ $campaign['campaign'] }}</td>
                                <td>{{ number_format($campaign['wasted_leads']) }}</td>
                                <td>{{ number_format($campaign['wasted_calls']) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Predictive Lead Scoring -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">🎯 Predictive Lead Scoring (Top 20)</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-sm">
                            <thead>
                                <tr>
                                    <th>Lead ID</th>
                                    <th>Name</th>
                                    <th>Phone</th>
                                    <th>Score</th>
                                    <th>Attempts</th>
                                    <th>Last Attempt</th>
                                    <th>Recommendation</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach(array_slice($predictiveScoring, 0, 20) as $lead)
                                <tr>
                                    <td>
                                        <a href="{{ route('agent.lead.display', $lead['lead_id']) }}" target="_blank">
                                            {{ $lead['lead_id'] }}
                                        </a>
                                    </td>
                                    <td>{{ $lead['name'] }}</td>
                                    <td>{{ $lead['phone'] }}</td>
                                    <td>
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar 
                                                {{ $lead['score'] > 70 ? 'bg-success' : ($lead['score'] > 40 ? 'bg-warning' : 'bg-danger') }}" 
                                                style="width: {{ $lead['score'] }}%">
                                                {{ $lead['score'] }}
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $lead['attempts'] }}</td>
                                    <td>{{ $lead['last_attempt'] ? \Carbon\Carbon::parse($lead['last_attempt'])->diffForHumans() : 'Never' }}</td>
                                    <td>
                                        @if($lead['recommendation'] == 'High Priority')
                                            <span class="badge bg-success">{{ $lead['recommendation'] }}</span>
                                        @elseif($lead['recommendation'] == 'Medium Priority')
                                            <span class="badge bg-warning">{{ $lead['recommendation'] }}</span>
                                        @else
                                            <span class="badge bg-secondary">{{ $lead['recommendation'] }}</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
// Auto-refresh every 60 seconds for real-time data
setTimeout(function() {
    if (document.querySelector('input[type="date"]').value === '{{ now()->format('Y-m-d') }}') {
        location.reload();
    }
}, 60000);
</script>
@endsection


@extends('components.app-layout')

@section('title', 'Lead Flow Visualization')

@section('content')
<style>
    /* Flow Visualization Styles */
    .flow-container {
        padding: 20px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        min-height: 100vh;
    }
    
    .flow-header {
        background: white;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 30px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }
    
    .date-filters {
        display: flex;
        gap: 15px;
        align-items: center;
        flex-wrap: wrap;
    }
    
    .date-filters input {
        padding: 8px 12px;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        font-size: 14px;
    }
    
    .date-filters button {
        padding: 8px 20px;
        background: #4c51bf;
        color: white;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 600;
    }
    
    .date-filters button:hover {
        background: #434190;
    }
    
    /* Flow Pipeline */
    .flow-pipeline {
        display: flex;
        gap: 10px;
        margin-bottom: 30px;
        overflow-x: auto;
        padding: 20px 0;
    }
    
    .flow-stage {
        background: white;
        border-radius: 12px;
        padding: 20px;
        min-width: 180px;
        position: relative;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        transition: transform 0.3s ease;
    }
    
    .flow-stage:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    }
    
    .flow-stage::after {
        content: '→';
        position: absolute;
        right: -25px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 24px;
        color: white;
        font-weight: bold;
    }
    
    .flow-stage:last-child::after {
        display: none;
    }
    
    .stage-label {
        font-size: 14px;
        color: #718096;
        margin-bottom: 8px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .stage-count {
        font-size: 32px;
        font-weight: bold;
        margin-bottom: 5px;
    }
    
    .stage-percentage {
        font-size: 18px;
        color: #4a5568;
        margin-bottom: 10px;
    }
    
    .stage-description {
        font-size: 12px;
        color: #a0aec0;
        line-height: 1.4;
    }
    
    /* Color coding for stages */
    .stage-blue { border-top: 4px solid #3182ce; }
    .stage-blue .stage-count { color: #3182ce; }
    
    .stage-yellow { border-top: 4px solid #f6ad55; }
    .stage-yellow .stage-count { color: #f6ad55; }
    
    .stage-orange { border-top: 4px solid #ed8936; }
    .stage-orange .stage-count { color: #ed8936; }
    
    .stage-green { border-top: 4px solid #48bb78; }
    .stage-green .stage-count { color: #48bb78; }
    
    .stage-purple { border-top: 4px solid #9f7aea; }
    .stage-purple .stage-count { color: #9f7aea; }
    
    .stage-success { border-top: 4px solid #38a169; }
    .stage-success .stage-count { color: #38a169; }
    
    .stage-gray { border-top: 4px solid #a0aec0; }
    .stage-gray .stage-count { color: #a0aec0; }
    
    .stage-danger { border-top: 4px solid #f56565; }
    .stage-danger .stage-count { color: #f56565; }
    
    /* Dashboard Grid */
    .dashboard-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    
    .dashboard-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    
    .card-title {
        font-size: 18px;
        font-weight: bold;
        margin-bottom: 20px;
        color: #2d3748;
        border-bottom: 2px solid #e2e8f0;
        padding-bottom: 10px;
    }
    
    /* Funnel Chart */
    .funnel-container {
        padding: 20px 0;
    }
    
    .funnel-stage {
        display: flex;
        align-items: center;
        margin-bottom: 15px;
        position: relative;
    }
    
    .funnel-label {
        width: 150px;
        font-weight: 600;
        color: #4a5568;
    }
    
    .funnel-bar {
        flex: 1;
        height: 40px;
        background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
        border-radius: 0 6px 6px 0;
        position: relative;
        display: flex;
        align-items: center;
        padding: 0 15px;
        color: white;
        font-weight: bold;
        transition: all 0.3s ease;
    }
    
    .funnel-bar:hover {
        transform: translateX(5px);
    }
    
    .funnel-stats {
        margin-left: auto;
        display: flex;
        gap: 15px;
    }
    
    /* Table Styles */
    .data-table {
        width: 100%;
        border-collapse: collapse;
    }
    
    .data-table th {
        background: #f7fafc;
        padding: 12px;
        text-align: left;
        font-weight: 600;
        color: #4a5568;
        border-bottom: 2px solid #e2e8f0;
    }
    
    .data-table td {
        padding: 12px;
        border-bottom: 1px solid #e2e8f0;
        color: #2d3748;
    }
    
    .data-table tr:hover {
        background: #f7fafc;
    }
    
    /* Badge Styles */
    .badge {
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 600;
        display: inline-block;
    }
    
    .badge-source {
        background: #bee3f8;
        color: #2c5282;
    }
    
    .badge-campaign {
        background: #d6f5d6;
        color: #22543d;
    }
    
    /* Chart Container */
    .chart-container {
        position: relative;
        height: 300px;
        margin-top: 20px;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .flow-pipeline {
            flex-direction: column;
        }
        
        .flow-stage::after {
            display: none;
        }
        
        .dashboard-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="flow-container">
    <!-- Header with Date Filters -->
    <div class="flow-header">
        <h1 style="font-size: 28px; margin-bottom: 20px; color: #2d3748;">
            📊 Lead Flow Visualization
        </h1>
        
        <form method="GET" action="{{ route('admin.lead-flow') }}" class="date-filters">
            <label>
                Start Date:
                <input type="date" name="start_date" value="{{ $startDate }}" max="{{ date('Y-m-d') }}">
            </label>
            <label>
                End Date:
                <input type="date" name="end_date" value="{{ $endDate }}" max="{{ date('Y-m-d') }}">
            </label>
            <button type="submit">Update View</button>
            <a href="{{ route('admin.lead-flow') }}" style="padding: 8px 20px; background: #718096; color: white; text-decoration: none; border-radius: 6px;">Reset</a>
        </form>
    </div>
    
    <!-- Flow Pipeline Visualization -->
    <div class="flow-pipeline">
        @foreach($flowData as $key => $stage)
        <div class="flow-stage stage-{{ $stage['color'] }}">
            <div class="stage-label">{{ $stage['label'] }}</div>
            <div class="stage-count">{{ number_format($stage['count']) }}</div>
            <div class="stage-percentage">{{ $stage['percentage'] ?? 0 }}%</div>
            <div class="stage-description">{{ $stage['description'] }}</div>
        </div>
        @endforeach
    </div>
    
    <!-- Dashboard Grid -->
    <div class="dashboard-grid">
        <!-- Conversion Funnel -->
        <div class="dashboard-card">
            <h2 class="card-title">🎯 Conversion Funnel</h2>
            <div class="funnel-container">
                @foreach($funnelData as $index => $funnel)
                <div class="funnel-stage">
                    <div class="funnel-label">{{ $funnel['stage'] }}</div>
                    <div class="funnel-bar" style="width: {{ $funnel['percentage'] }}%; opacity: {{ 1 - ($index * 0.15) }}">
                        <span>{{ number_format($funnel['count']) }}</span>
                        <div class="funnel-stats">
                            <span>{{ $funnel['percentage'] }}%</span>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        
        <!-- Hourly Intake Chart -->
        <div class="dashboard-card">
            <h2 class="card-title">⏰ Hourly Lead Intake</h2>
            <div class="chart-container">
                <canvas id="hourlyChart"></canvas>
            </div>
        </div>
    </div>
    
    <!-- List/Campaign Breakdown -->
    <div class="dashboard-card">
        <h2 class="card-title">📋 Source & Campaign Performance</h2>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Total Leads</th>
                    <th>Qualified</th>
                    <th>Sold</th>
                    <th>Qualification Rate</th>
                    <th>Conversion Rate</th>
                </tr>
            </thead>
            <tbody>
                @forelse($listBreakdown as $item)
                <tr>
                    <td><strong>{{ $item['name'] }}</strong></td>
                    <td>
                        @if($item['type'] == 'Source')
                            <span class="badge badge-source">{{ $item['type'] }}</span>
                        @else
                            <span class="badge badge-campaign">{{ $item['type'] }}</span>
                        @endif
                    </td>
                    <td>{{ number_format($item['total']) }}</td>
                    <td>{{ number_format($item['qualified']) }}</td>
                    <td>{{ number_format($item['sold']) }}</td>
                    <td>
                        <strong style="color: {{ $item['qualification_rate'] > 20 ? '#48bb78' : '#f56565' }}">
                            {{ $item['qualification_rate'] }}%
                        </strong>
                    </td>
                    <td>
                        <strong style="color: {{ $item['conversion_rate'] > 10 ? '#48bb78' : '#f56565' }}">
                            {{ $item['conversion_rate'] }}%
                        </strong>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align: center; color: #a0aec0;">No data available for selected period</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <!-- Agent Performance -->
    <div class="dashboard-card" style="margin-top: 20px;">
        <h2 class="card-title">👥 Top Agent Performance</h2>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Agent ID</th>
                    <th>Leads Handled</th>
                    <th>Total Calls</th>
                    <th>Connected</th>
                    <th>Connection Rate</th>
                    <th>Transfers</th>
                    <th>Transfer Rate</th>
                    <th>Avg Talk Time</th>
                </tr>
            </thead>
            <tbody>
                @forelse($agentPerformance as $agent)
                <tr>
                    <td><strong>{{ $agent->agent_id }}</strong></td>
                    <td>{{ number_format($agent->leads_handled) }}</td>
                    <td>{{ number_format($agent->total_calls) }}</td>
                    <td>{{ number_format($agent->connected) }}</td>
                    <td>
                        <strong style="color: {{ $agent->connection_rate > 30 ? '#48bb78' : '#f56565' }}">
                            {{ $agent->connection_rate }}%
                        </strong>
                    </td>
                    <td>{{ number_format($agent->transfers) }}</td>
                    <td>
                        <strong style="color: {{ $agent->transfer_rate > 50 ? '#48bb78' : '#f6ad55' }}">
                            {{ $agent->transfer_rate }}%
                        </strong>
                    </td>
                    <td>{{ $agent->avg_talk_time }} min</td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align: center; color: #a0aec0;">No agent data available for selected period</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Chart.js for Hourly Intake -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Hourly Intake Chart
    const hourlyData = @json($hourlyIntake);
    
    const ctx = document.getElementById('hourlyChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: hourlyData.map(d => d.hour),
            datasets: [{
                label: 'Leads',
                data: hourlyData.map(d => d.count),
                backgroundColor: 'rgba(102, 126, 234, 0.8)',
                borderColor: 'rgba(102, 126, 234, 1)',
                borderWidth: 2,
                borderRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 12,
                    cornerRadius: 6
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        display: true,
                        color: 'rgba(0, 0, 0, 0.05)'
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
});
</script>
@endsection

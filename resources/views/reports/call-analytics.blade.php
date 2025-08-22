@extends('layouts.app')

@section('title', 'Call Analytics Reports')

@section('content')
<div class="container-fluid px-4">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-3">📊 Call Analytics Dashboard</h1>
            
            <!-- Date Range Selector -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-outline-primary date-filter active" data-range="today">Today</button>
                                <button type="button" class="btn btn-outline-primary date-filter" data-range="yesterday">Yesterday</button>
                                <button type="button" class="btn btn-outline-primary date-filter" data-range="week">Last 7 Days</button>
                                <button type="button" class="btn btn-outline-primary date-filter" data-range="month">Last 30 Days</button>
                                <button type="button" class="btn btn-outline-primary date-filter" data-range="all">All Time</button>
                                <button type="button" class="btn btn-outline-primary" id="custom-range-btn">Custom Range</button>
                            </div>
                        </div>
                        <div class="col-md-4 text-right">
                            <button class="btn btn-success" id="refresh-data">
                                <i class="fas fa-sync-alt"></i> Refresh
                            </button>
                            <button class="btn btn-info" id="export-csv">
                                <i class="fas fa-download"></i> Export CSV
                            </button>
                        </div>
                    </div>
                    
                    <!-- Custom Date Range (hidden by default) -->
                    <div class="row mt-3" id="custom-range-div" style="display: none;">
                        <div class="col-md-4">
                            <input type="date" class="form-control" id="start-date" placeholder="Start Date">
                        </div>
                        <div class="col-md-4">
                            <input type="date" class="form-control" id="end-date" placeholder="End Date">
                        </div>
                        <div class="col-md-4">
                            <button class="btn btn-primary" id="apply-custom-range">Apply Range</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Key Metrics Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <h5 class="card-title">Total Calls</h5>
                            <h2 class="mb-0" id="total-calls">0</h2>
                            <small id="calls-trend"></small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <h5 class="card-title">Transfers (XFER)</h5>
                            <h2 class="mb-0" id="total-transfers">0</h2>
                            <small id="transfer-rate">0%</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <h5 class="card-title">Unique Leads</h5>
                            <h2 class="mb-0" id="unique-leads">0</h2>
                            <small id="avg-calls-per-lead">0 calls/lead</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body">
                            <h5 class="card-title">Connect Rate</h5>
                            <h2 class="mb-0" id="connect-rate">0%</h2>
                            <small id="connected-calls">0 connected</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detailed Reports Tabs -->
            <div class="card">
                <div class="card-header">
                    <ul class="nav nav-tabs card-header-tabs">
                        <li class="nav-item">
                            <a class="nav-link active" data-toggle="tab" href="#overview">Overview</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#dispositions">Dispositions</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#hourly">Hourly Performance</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#agents">Agent Performance</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#lists">List Performance</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#conversion">Conversion Analysis</a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content">
                        <!-- Overview Tab -->
                        <div class="tab-pane fade show active" id="overview">
                            <div class="row">
                                <div class="col-md-6">
                                    <h5>Call Volume Trend</h5>
                                    <canvas id="volume-chart"></canvas>
                                </div>
                                <div class="col-md-6">
                                    <h5>Conversion Funnel</h5>
                                    <canvas id="funnel-chart"></canvas>
                                </div>
                            </div>
                            <div class="row mt-4">
                                <div class="col-12">
                                    <h5>Key Insights</h5>
                                    <div id="key-insights" class="alert alert-info">
                                        Loading insights...
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Dispositions Tab -->
                        <div class="tab-pane fade" id="dispositions">
                            <h5>Disposition Breakdown</h5>
                            <div class="row">
                                <div class="col-md-8">
                                    <canvas id="disposition-chart"></canvas>
                                </div>
                                <div class="col-md-4">
                                    <div id="disposition-table" class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Status</th>
                                                    <th>Count</th>
                                                    <th>%</th>
                                                </tr>
                                            </thead>
                                            <tbody id="disposition-tbody">
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Hourly Performance Tab -->
                        <div class="tab-pane fade" id="hourly">
                            <h5>Hourly Call Performance</h5>
                            <canvas id="hourly-chart"></canvas>
                            <div class="mt-3">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Hour</th>
                                            <th>Calls</th>
                                            <th>Transfers</th>
                                            <th>Transfer Rate</th>
                                            <th>Avg Duration</th>
                                        </tr>
                                    </thead>
                                    <tbody id="hourly-tbody">
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Agent Performance Tab -->
                        <div class="tab-pane fade" id="agents">
                            <h5>Agent Performance Rankings</h5>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Agent</th>
                                            <th>Calls</th>
                                            <th>Transfers</th>
                                            <th>Transfer Rate</th>
                                            <th>Avg Talk Time</th>
                                            <th>Efficiency Score</th>
                                        </tr>
                                    </thead>
                                    <tbody id="agent-tbody">
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- List Performance Tab -->
                        <div class="tab-pane fade" id="lists">
                            <h5>List Performance Analysis</h5>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>List ID</th>
                                            <th>Calls</th>
                                            <th>Unique Leads</th>
                                            <th>Transfers</th>
                                            <th>Transfer Rate</th>
                                            <th>Avg Calls/Lead</th>
                                        </tr>
                                    </thead>
                                    <tbody id="list-tbody">
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Conversion Analysis Tab -->
                        <div class="tab-pane fade" id="conversion">
                            <h5>Conversion Analysis</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <h6>Calls to Conversion Distribution</h6>
                                    <canvas id="calls-to-convert-chart"></canvas>
                                </div>
                                <div class="col-md-6">
                                    <h6>Time to Conversion</h6>
                                    <canvas id="time-to-convert-chart"></canvas>
                                </div>
                            </div>
                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="alert alert-warning">
                                        <h6>Current Performance (Real Numbers)</h6>
                                        <ul>
                                            <li><strong>Actual Transfer Rate:</strong> <span id="actual-transfer-rate">1.08%</span></li>
                                            <li><strong>Transfers:</strong> XFER + XFERA only</li>
                                            <li><strong>Average Calls to Transfer:</strong> <span id="avg-calls-to-transfer">9.7</span></li>
                                            <li><strong>Industry Average:</strong> 1-3% for shared internet leads</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    let currentRange = 'today';
    let charts = {};

    // Date filter buttons
    $('.date-filter').click(function() {
        $('.date-filter').removeClass('active');
        $(this).addClass('active');
        currentRange = $(this).data('range');
        $('#custom-range-div').hide();
        loadReports();
    });

    // Custom range button
    $('#custom-range-btn').click(function() {
        $('.date-filter').removeClass('active');
        $('#custom-range-div').toggle();
    });

    // Apply custom range
    $('#apply-custom-range').click(function() {
        const startDate = $('#start-date').val();
        const endDate = $('#end-date').val();
        if (startDate && endDate) {
            currentRange = 'custom';
            loadReports(startDate, endDate);
        }
    });

    // Refresh button
    $('#refresh-data').click(function() {
        loadReports();
    });

    // Export CSV
    $('#export-csv').click(function() {
        exportToCSV();
    });

    function loadReports(startDate = null, endDate = null) {
        // Show loading state
        $('#total-calls').html('<i class="fas fa-spinner fa-spin"></i>');
        $('#total-transfers').html('<i class="fas fa-spinner fa-spin"></i>');
        $('#unique-leads').html('<i class="fas fa-spinner fa-spin"></i>');
        $('#connect-rate').html('<i class="fas fa-spinner fa-spin"></i>');

        $.ajax({
            url: '/api/reports/call-analytics',
            method: 'POST',
            data: {
                range: currentRange,
                start_date: startDate,
                end_date: endDate,
                _token: '{{ csrf_token() }}'
            },
            success: function(data) {
                updateMetrics(data.metrics);
                updateCharts(data.charts);
                updateTables(data.tables);
                updateInsights(data.insights);
            },
            error: function(xhr) {
                console.error('Error loading reports:', xhr);
                alert('Error loading reports. Please try again.');
            }
        });
    }

    function updateMetrics(metrics) {
        $('#total-calls').text(metrics.total_calls.toLocaleString());
        $('#total-transfers').text(metrics.total_transfers.toLocaleString());
        $('#transfer-rate').text(metrics.transfer_rate + '%');
        $('#unique-leads').text(metrics.unique_leads.toLocaleString());
        $('#avg-calls-per-lead').text(metrics.avg_calls_per_lead + ' calls/lead');
        $('#connect-rate').text(metrics.connect_rate + '%');
        $('#connected-calls').text(metrics.connected_calls.toLocaleString() + ' connected');
        
        // Update conversion tab metrics
        $('#actual-transfer-rate').text(metrics.transfer_rate + '%');
        $('#avg-calls-to-transfer').text(metrics.avg_calls_to_transfer);
    }

    function updateCharts(chartData) {
        // Volume Chart
        if (charts.volume) charts.volume.destroy();
        const volumeCtx = document.getElementById('volume-chart').getContext('2d');
        charts.volume = new Chart(volumeCtx, {
            type: 'line',
            data: chartData.volume,
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: false
                    }
                }
            }
        });

        // Funnel Chart
        if (charts.funnel) charts.funnel.destroy();
        const funnelCtx = document.getElementById('funnel-chart').getContext('2d');
        charts.funnel = new Chart(funnelCtx, {
            type: 'bar',
            data: chartData.funnel,
            options: {
                indexAxis: 'y',
                responsive: true
            }
        });

        // Disposition Chart
        if (charts.disposition) charts.disposition.destroy();
        const dispCtx = document.getElementById('disposition-chart').getContext('2d');
        charts.disposition = new Chart(dispCtx, {
            type: 'doughnut',
            data: chartData.disposition,
            options: {
                responsive: true
            }
        });

        // Hourly Chart
        if (charts.hourly) charts.hourly.destroy();
        const hourlyCtx = document.getElementById('hourly-chart').getContext('2d');
        charts.hourly = new Chart(hourlyCtx, {
            type: 'bar',
            data: chartData.hourly,
            options: {
                responsive: true
            }
        });

        // Calls to Convert Chart
        if (charts.callsToConvert) charts.callsToConvert.destroy();
        const ctcCtx = document.getElementById('calls-to-convert-chart').getContext('2d');
        charts.callsToConvert = new Chart(ctcCtx, {
            type: 'bar',
            data: chartData.calls_to_convert,
            options: {
                responsive: true
            }
        });

        // Time to Convert Chart
        if (charts.timeToConvert) charts.timeToConvert.destroy();
        const ttcCtx = document.getElementById('time-to-convert-chart').getContext('2d');
        charts.timeToConvert = new Chart(ttcCtx, {
            type: 'pie',
            data: chartData.time_to_convert,
            options: {
                responsive: true
            }
        });
    }

    function updateTables(tables) {
        // Disposition table
        let dispHtml = '';
        tables.dispositions.forEach(function(row) {
            dispHtml += `<tr>
                <td>${row.status}</td>
                <td>${row.count.toLocaleString()}</td>
                <td>${row.percentage}%</td>
            </tr>`;
        });
        $('#disposition-tbody').html(dispHtml);

        // Hourly table
        let hourlyHtml = '';
        tables.hourly.forEach(function(row) {
            hourlyHtml += `<tr>
                <td>${row.hour}:00</td>
                <td>${row.calls.toLocaleString()}</td>
                <td>${row.transfers.toLocaleString()}</td>
                <td>${row.transfer_rate}%</td>
                <td>${row.avg_duration}s</td>
            </tr>`;
        });
        $('#hourly-tbody').html(hourlyHtml);

        // Agent table
        let agentHtml = '';
        tables.agents.forEach(function(row) {
            agentHtml += `<tr>
                <td>${row.agent}</td>
                <td>${row.calls.toLocaleString()}</td>
                <td>${row.transfers.toLocaleString()}</td>
                <td>${row.transfer_rate}%</td>
                <td>${row.avg_talk_time}s</td>
                <td>${row.efficiency_score}</td>
            </tr>`;
        });
        $('#agent-tbody').html(agentHtml);

        // List table
        let listHtml = '';
        tables.lists.forEach(function(row) {
            listHtml += `<tr>
                <td>${row.list_id}</td>
                <td>${row.calls.toLocaleString()}</td>
                <td>${row.unique_leads.toLocaleString()}</td>
                <td>${row.transfers.toLocaleString()}</td>
                <td>${row.transfer_rate}%</td>
                <td>${row.avg_calls_per_lead}</td>
            </tr>`;
        });
        $('#list-tbody').html(listHtml);
    }

    function updateInsights(insights) {
        let insightHtml = '<ul>';
        insights.forEach(function(insight) {
            insightHtml += `<li>${insight}</li>`;
        });
        insightHtml += '</ul>';
        $('#key-insights').html(insightHtml);
    }

    function exportToCSV() {
        window.location.href = `/api/reports/export-csv?range=${currentRange}`;
    }

    // Load initial data
    loadReports();

    // Auto-refresh every 5 minutes
    setInterval(function() {
        if (currentRange === 'today') {
            loadReports();
        }
    }, 300000);
});
</script>
@endpush
@endsection







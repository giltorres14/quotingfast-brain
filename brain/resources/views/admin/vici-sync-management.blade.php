@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">🔄 Vici Call Log Sync Management</h1>
    
    <!-- Sync Status Card -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">📊 Sync Status</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="text-center">
                                <h4>Last Complete Sync</h4>
                                <p class="text-muted">
                                    @if($lastCompleteSync)
                                        {{ \Carbon\Carbon::parse($lastCompleteSync)->format('M d, Y g:i A') }}
                                        <br>
                                        <small>({{ \Carbon\Carbon::parse($lastCompleteSync)->diffForHumans() }})</small>
                                    @else
                                        <span class="text-warning">Never</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h4>Last Incremental Sync</h4>
                                <p class="text-muted">
                                    @if($lastIncrementalSync)
                                        {{ \Carbon\Carbon::parse($lastIncrementalSync)->format('M d, Y g:i A') }}
                                        <br>
                                        <small>({{ \Carbon\Carbon::parse($lastIncrementalSync)->diffForHumans() }})</small>
                                    @else
                                        <span class="text-warning">Never</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h4>Auto-Sync Status</h4>
                                <p>
                                    @if($autoSyncEnabled)
                                        <span class="badge bg-success fs-6">ENABLED</span>
                                        <br>
                                        <small>Every 5 minutes</small>
                                    @else
                                        <span class="badge bg-danger fs-6">DISABLED</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h4>Sync Health</h4>
                                <p>
                                    @if($syncHealth == 'good')
                                        <span class="badge bg-success fs-6">✅ HEALTHY</span>
                                    @elseif($syncHealth == 'warning')
                                        <span class="badge bg-warning fs-6">⚠️ WARNING</span>
                                    @else
                                        <span class="badge bg-danger fs-6">❌ ISSUES</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Current Statistics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h4>{{ number_format($stats['total_calls'] ?? 0) }}</h4>
                    <p class="mb-0">Total Call Records</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h4>{{ number_format($stats['matched_calls'] ?? 0) }}</h4>
                    <p class="mb-0">Matched to Leads</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h4>{{ number_format($stats['orphan_calls'] ?? 0) }}</h4>
                    <p class="mb-0">Orphan Calls</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h4>{{ number_format($stats['calls_today'] ?? 0) }}</h4>
                    <p class="mb-0">Calls Today</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Manual Sync Controls -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">🎮 Manual Sync Controls</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-body">
                                    <h6>Quick Incremental Sync</h6>
                                    <p class="text-muted small">Fetch calls from last 10 minutes</p>
                                    <button class="btn btn-primary w-100" onclick="runSync('incremental')" id="btn-incremental">
                                        <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                                        Run Incremental Sync
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-body">
                                    <h6>Daily Sync</h6>
                                    <p class="text-muted small">Fetch all calls from today</p>
                                    <button class="btn btn-warning w-100" onclick="runSync('daily')" id="btn-daily">
                                        <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                                        Run Daily Sync
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-body">
                                    <h6>Full Historical Sync</h6>
                                    <p class="text-muted small">Fetch last 90 days (may take 5-10 minutes)</p>
                                    <button class="btn btn-danger w-100" onclick="runSync('full')" id="btn-full">
                                        <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                                        Run Full Sync (90 Days)
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Custom Date Range Sync -->
                    <div class="card mt-3">
                        <div class="card-body">
                            <h6>Custom Date Range Sync</h6>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label for="sync_from" class="form-label">From Date</label>
                                    <input type="date" class="form-control" id="sync_from" 
                                           value="{{ now()->subDays(7)->format('Y-m-d') }}">
                                </div>
                                <div class="col-md-4">
                                    <label for="sync_to" class="form-label">To Date</label>
                                    <input type="date" class="form-control" id="sync_to" 
                                           value="{{ now()->format('Y-m-d') }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">&nbsp;</label>
                                    <button class="btn btn-info w-100" onclick="runSync('custom')" id="btn-custom">
                                        <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                                        Run Custom Sync
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sync Progress -->
    <div class="row mb-4 d-none" id="sync-progress-section">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">⏳ Sync Progress</h5>
                </div>
                <div class="card-body">
                    <div class="progress mb-3" style="height: 30px;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" 
                             role="progressbar" id="sync-progress-bar" 
                             style="width: 0%">0%</div>
                    </div>
                    <div id="sync-status" class="text-center text-muted">
                        Initializing...
                    </div>
                    <div id="sync-details" class="mt-3">
                        <div class="row text-center">
                            <div class="col-md-3">
                                <h5 id="sync-processed">0</h5>
                                <small>Processed</small>
                            </div>
                            <div class="col-md-3">
                                <h5 id="sync-imported">0</h5>
                                <small>Imported</small>
                            </div>
                            <div class="col-md-3">
                                <h5 id="sync-orphaned">0</h5>
                                <small>Orphaned</small>
                            </div>
                            <div class="col-md-3">
                                <h5 id="sync-errors">0</h5>
                                <small>Errors</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sync Log -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">📜 Recent Sync Activity</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                        <table class="table table-sm table-striped">
                            <thead>
                                <tr>
                                    <th>Time</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                    <th>Records</th>
                                    <th>Duration</th>
                                    <th>Details</th>
                                </tr>
                            </thead>
                            <tbody id="sync-log-table">
                                @foreach($recentSyncs as $sync)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($sync->created_at)->format('M d g:i A') }}</td>
                                    <td>
                                        <span class="badge bg-{{ $sync->type == 'full' ? 'danger' : ($sync->type == 'daily' ? 'warning' : 'primary') }}">
                                            {{ ucfirst($sync->type) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($sync->status == 'completed')
                                            <span class="badge bg-success">✅ Success</span>
                                        @elseif($sync->status == 'running')
                                            <span class="badge bg-info">🔄 Running</span>
                                        @else
                                            <span class="badge bg-danger">❌ Failed</span>
                                        @endif
                                    </td>
                                    <td>{{ number_format($sync->records_processed) }}</td>
                                    <td>{{ $sync->duration ?? 'N/A' }}</td>
                                    <td>
                                        <small>{{ $sync->details }}</small>
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

    <!-- Quick Actions -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h5>Quick Actions</h5>
                    <div class="btn-group" role="group">
                        <a href="{{ route('admin.vici.comprehensive-reports') }}" class="btn btn-primary">
                            📊 View Comprehensive Reports
                        </a>
                        <button class="btn btn-success" onclick="toggleAutoSync()">
                            {{ $autoSyncEnabled ? '⏸️ Disable' : '▶️ Enable' }} Auto-Sync
                        </button>
                        <button class="btn btn-warning" onclick="matchOrphans()">
                            🔄 Match Orphan Calls
                        </button>
                        <button class="btn btn-info" onclick="refreshStats()">
                            🔄 Refresh Stats
                        </button>
                        <a href="{{ route('admin.vici-reports') }}" class="btn btn-secondary">
                            📞 View Call Reports
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let syncInterval = null;
let currentSyncType = null;

function runSync(type) {
    // Disable all buttons
    document.querySelectorAll('button').forEach(btn => btn.disabled = true);
    
    // Show spinner on clicked button
    const btnId = `btn-${type}`;
    const btn = document.getElementById(btnId);
    const spinner = btn.querySelector('.spinner-border');
    spinner.classList.remove('d-none');
    
    // Show progress section
    document.getElementById('sync-progress-section').classList.remove('d-none');
    document.getElementById('sync-progress-bar').style.width = '0%';
    document.getElementById('sync-status').innerText = 'Starting sync...';
    
    currentSyncType = type;
    
    // Prepare request data
    let data = { type: type };
    
    if (type === 'custom') {
        data.from = document.getElementById('sync_from').value;
        data.to = document.getElementById('sync_to').value;
    }
    
    // Start the sync
    fetch('{{ route("admin.vici.run-sync") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Start polling for progress
            syncInterval = setInterval(checkSyncProgress, 2000);
        } else {
            alert('Failed to start sync: ' + (data.message || 'Unknown error'));
            resetSyncUI();
        }
    })
    .catch(error => {
        alert('Error starting sync: ' + error);
        resetSyncUI();
    });
}

function checkSyncProgress() {
    fetch('{{ route("admin.vici.sync-progress") }}')
        .then(response => response.json())
        .then(data => {
            // Update progress bar
            const progress = data.progress || 0;
            document.getElementById('sync-progress-bar').style.width = progress + '%';
            document.getElementById('sync-progress-bar').innerText = progress + '%';
            
            // Update status
            document.getElementById('sync-status').innerText = data.status || 'Processing...';
            
            // Update counters
            document.getElementById('sync-processed').innerText = data.processed || 0;
            document.getElementById('sync-imported').innerText = data.imported || 0;
            document.getElementById('sync-orphaned').innerText = data.orphaned || 0;
            document.getElementById('sync-errors').innerText = data.errors || 0;
            
            // Check if complete
            if (data.complete || progress >= 100) {
                clearInterval(syncInterval);
                setTimeout(() => {
                    alert('Sync completed successfully!');
                    resetSyncUI();
                    refreshStats();
                    location.reload();
                }, 1000);
            }
        })
        .catch(error => {
            console.error('Error checking progress:', error);
        });
}

function resetSyncUI() {
    // Re-enable all buttons
    document.querySelectorAll('button').forEach(btn => btn.disabled = false);
    
    // Hide all spinners
    document.querySelectorAll('.spinner-border').forEach(spinner => {
        spinner.classList.add('d-none');
    });
    
    // Clear interval if running
    if (syncInterval) {
        clearInterval(syncInterval);
        syncInterval = null;
    }
}

function toggleAutoSync() {
    fetch('{{ route("admin.vici.toggle-auto-sync") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Failed to toggle auto-sync');
        }
    });
}

function matchOrphans() {
    if (confirm('This will attempt to match all orphan calls to leads. Continue?')) {
        fetch('{{ route("admin.vici.match-orphans") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            alert(`Matched ${data.matched || 0} orphan calls to leads.`);
            refreshStats();
        });
    }
}

function refreshStats() {
    location.reload();
}

// Auto-refresh stats every 30 seconds
setInterval(function() {
    if (!syncInterval) { // Only refresh if not syncing
        fetch('{{ route("admin.vici.stats") }}')
            .then(response => response.json())
            .then(data => {
                // Update stats cards
                document.querySelector('.card-body h4').innerText = number_format(data.total_calls || 0);
                // Update other stats...
            })
            .catch(error => console.error('Error refreshing stats:', error));
    }
}, 30000);

function number_format(num) {
    return new Intl.NumberFormat().format(num);
}
</script>
@endsection

@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">System Diagnostics Dashboard</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item active">Real-time System Health Checks</li>
    </ol>

    <!-- Quick Status Cards -->
    <div class="row">
        <div class="col-xl-3 col-md-6">
            <div class="card bg-primary text-white mb-4">
                <div class="card-body">
                    <h4>Server IP</h4>
                    <p id="server-ip" class="h5">Checking...</p>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <small>Render Egress IP for Whitelisting</small>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-success text-white mb-4">
                <div class="card-body">
                    <h4>Database</h4>
                    <p id="db-status" class="h5">Checking...</p>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <small>PostgreSQL Connection</small>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-warning text-white mb-4">
                <div class="card-body">
                    <h4>Vici Status</h4>
                    <p id="vici-status" class="h5">Checking...</p>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <small>API Connection to List 101</small>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-info text-white mb-4">
                <div class="card-body">
                    <h4>Webhooks</h4>
                    <p id="webhook-status" class="h5">Checking...</p>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <small>Active Endpoints</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Diagnostics -->
    <div class="row">
        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-server me-1"></i>
                    Vici Integration Details
                </div>
                <div class="card-body">
                    <div id="vici-details">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-database me-1"></i>
                    Database Connection Details
                </div>
                <div class="card-body">
                    <div id="db-details">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Test Tools -->
    <div class="row">
        <div class="col-xl-12">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-tools me-1"></i>
                    Test Tools
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <h5>Send Test Lead to Vici</h5>
                            <p>Send a test lead with valid 10-digit phone to List 101</p>
                            <button class="btn btn-primary" onclick="sendTestLead()">
                                <i class="fas fa-paper-plane"></i> Send Test Lead
                            </button>
                            <div id="test-lead-result" class="mt-2"></div>
                        </div>
                        <div class="col-md-4">
                            <h5>Check Webhook Status</h5>
                            <p>View all active webhook endpoints</p>
                            <button class="btn btn-info" onclick="checkWebhooks()">
                                <i class="fas fa-plug"></i> Check Webhooks
                            </button>
                            <div id="webhook-result" class="mt-2"></div>
                        </div>
                        <div class="col-md-4">
                            <h5>Refresh All Diagnostics</h5>
                            <p>Run all diagnostic checks again</p>
                            <button class="btn btn-success" onclick="runAllDiagnostics()">
                                <i class="fas fa-sync"></i> Refresh All
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Required Alert -->
    <div id="action-alert" class="alert alert-danger d-none" role="alert">
        <h4 class="alert-heading">Action Required!</h4>
        <p id="action-message"></p>
        <hr>
        <div id="action-steps"></div>
    </div>

    <!-- Success Alert -->
    <div id="success-alert" class="alert alert-success d-none" role="alert">
        <h4 class="alert-heading">System Ready!</h4>
        <p>All systems are operational. Leads will flow to Vici List 101.</p>
    </div>
</div>

<script>
// Run diagnostics on page load
document.addEventListener('DOMContentLoaded', function() {
    runAllDiagnostics();
    // Refresh every 30 seconds
    setInterval(runAllDiagnostics, 30000);
});

function runAllDiagnostics() {
    checkServerIP();
    checkDatabase();
    checkViciStatus();
    checkWebhookStatus();
}

function checkServerIP() {
    fetch('/server-egress-ip')
        .then(response => response.json())
        .then(data => {
            document.getElementById('server-ip').textContent = data.ip || 'Unknown';
        })
        .catch(error => {
            document.getElementById('server-ip').textContent = 'Error';
        });
}

function checkDatabase() {
    fetch('/test-db.php')
        .then(response => response.json())
        .then(data => {
            const internal = data.internal?.success ? '✓' : '✗';
            const external = data.external?.success ? '✓' : '✗';
            document.getElementById('db-status').textContent = 
                internal === '✓' && external === '✓' ? 'Connected' : 'Error';
            
            // Show details
            let details = '<table class="table table-sm">';
            details += '<tr><td>Internal Connection:</td><td>' + 
                (data.internal?.success ? '<span class="text-success">✓ Connected</span>' : '<span class="text-danger">✗ Failed</span>') + '</td></tr>';
            details += '<tr><td>External Connection:</td><td>' + 
                (data.external?.success ? '<span class="text-success">✓ Connected</span>' : '<span class="text-danger">✗ Failed</span>') + '</td></tr>';
            details += '<tr><td>Database:</td><td>' + (data.env?.DB_DATABASE || 'Unknown') + '</td></tr>';
            details += '<tr><td>Host:</td><td>' + (data.env?.DB_HOST || 'Unknown') + '</td></tr>';
            details += '</table>';
            document.getElementById('db-details').innerHTML = details;
        })
        .catch(error => {
            document.getElementById('db-status').textContent = 'Error';
            document.getElementById('db-details').innerHTML = '<div class="alert alert-danger">Failed to check database</div>';
        });
}

function checkViciStatus() {
    fetch('/vici-whitelist-check.php')
        .then(response => response.json())
        .then(data => {
            const status = data.overall_status === 'READY' ? 'Connected' : 'Not Ready';
            document.getElementById('vici-status').textContent = status;
            
            // Show details
            let details = '<table class="table table-sm">';
            details += '<tr><td>Render IP:</td><td>' + (data.render_ip || 'Unknown') + '</td></tr>';
            details += '<tr><td>Current IP:</td><td>' + (data.checks?.server_ip?.value || 'Unknown') + '</td></tr>';
            details += '<tr><td>Vici Connection:</td><td>' + 
                (data.checks?.vici_connectivity?.status === 'success' ? 
                    '<span class="text-success">✓ Connected</span>' : 
                    '<span class="text-danger">✗ Failed - IP not whitelisted</span>') + '</td></tr>';
            details += '<tr><td>Add Lead Test:</td><td>' + 
                (data.checks?.vici_add_lead?.status === 'success' ? 
                    '<span class="text-success">✓ Working</span>' : 
                    '<span class="text-danger">✗ Failed</span>') + '</td></tr>';
            details += '<tr><td>API User:</td><td>UploadAPI</td></tr>';
            details += '<tr><td>List ID:</td><td>101</td></tr>';
            details += '</table>';
            
            document.getElementById('vici-details').innerHTML = details;
            
            // Show action required if needed
            if (data.overall_status !== 'READY' && data.action_required) {
                document.getElementById('action-alert').classList.remove('d-none');
                document.getElementById('action-message').textContent = data.action_required.message;
                
                let steps = '<ol>';
                data.action_required.steps.forEach(step => {
                    steps += '<li>' + step + '</li>';
                });
                steps += '</ol>';
                document.getElementById('action-steps').innerHTML = steps;
            } else {
                document.getElementById('action-alert').classList.add('d-none');
                document.getElementById('success-alert').classList.remove('d-none');
            }
        })
        .catch(error => {
            document.getElementById('vici-status').textContent = 'Error';
            document.getElementById('vici-details').innerHTML = '<div class="alert alert-danger">Failed to check Vici status</div>';
        });
}

function checkWebhookStatus() {
    fetch('/webhook/status')
        .then(response => response.json())
        .then(data => {
            const count = data.total_webhooks || 0;
            document.getElementById('webhook-status').textContent = count + ' Active';
        })
        .catch(error => {
            document.getElementById('webhook-status').textContent = 'Error';
        });
}

function sendTestLead() {
    const testLead = {
        contact: {
            first_name: 'Diagnostic',
            last_name: 'Test' + Date.now(),
            phone: '614555' + Math.floor(Math.random() * 10000).toString().padStart(4, '0'),
            email: 'test@diagnostic.com',
            address: '123 Test St',
            city: 'Columbus',
            state: 'OH',
            zip_code: '43215'
        },
        drivers: [{
            name: 'Diagnostic Test',
            age: 35,
            gender: 'M'
        }],
        vehicles: [{
            year: 2023,
            make: 'Test',
            model: 'Vehicle'
        }]
    };
    
    fetch('/webhook.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(testLead)
    })
    .then(response => response.json())
    .then(data => {
        const result = document.getElementById('test-lead-result');
        if (data.success) {
            result.innerHTML = '<div class="alert alert-success">✓ Lead sent! Phone: ' + testLead.contact.phone + '</div>';
        } else {
            result.innerHTML = '<div class="alert alert-danger">✗ Failed to send lead</div>';
        }
    })
    .catch(error => {
        document.getElementById('test-lead-result').innerHTML = '<div class="alert alert-danger">✗ Error: ' + error + '</div>';
    });
}

function checkWebhooks() {
    fetch('/webhook/status')
        .then(response => response.json())
        .then(data => {
            let result = '<table class="table table-sm mt-2">';
            result += '<thead><tr><th>Endpoint</th><th>Status</th></tr></thead><tbody>';
            
            Object.keys(data.webhooks || {}).forEach(key => {
                const webhook = data.webhooks[key];
                result += '<tr><td>' + webhook.endpoint + '</td><td>' + 
                    (webhook.active ? '<span class="text-success">Active</span>' : '<span class="text-danger">Inactive</span>') + 
                    '</td></tr>';
            });
            
            result += '</tbody></table>';
            document.getElementById('webhook-result').innerHTML = result;
        })
        .catch(error => {
            document.getElementById('webhook-result').innerHTML = '<div class="alert alert-danger">✗ Error checking webhooks</div>';
        });
}
</script>
@endsection

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Lead Details - {{ $lead->name }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            font-size: 14px;
            line-height: 1.4;
            background: #f8f9fa;
            color: #333;
        }
        
        .container {
            max-width: 100%;
            padding: 16px;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 16px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 20px;
            margin-bottom: 4px;
        }
        
        .header .meta {
            opacity: 0.9;
            font-size: 12px;
        }
        
        .section {
            background: white;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 16px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .section-title {
            font-size: 16px;
            font-weight: 600;
            color: #495057;
            margin-bottom: 12px;
            padding-bottom: 8px;
            border-bottom: 2px solid #e9ecef;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 12px;
        }
        
        .info-item {
            display: flex;
            flex-direction: column;
        }
        
        .info-label {
            font-size: 11px;
            font-weight: 600;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 2px;
        }
        
        .info-value {
            font-size: 14px;
            color: #212529;
            font-weight: 500;
        }
        
        .info-value.empty {
            color: #adb5bd;
            font-style: italic;
        }
        
        .driver-card, .vehicle-card {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 6px;
            padding: 12px;
            margin-bottom: 8px;
        }
        
        .driver-card h4, .vehicle-card h4 {
            color: #495057;
            margin-bottom: 8px;
            font-size: 14px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .status-new { background: #d4edda; color: #155724; }
        .status-connected { background: #cce5ff; color: #004085; }
        .status-transfer { background: #fff3cd; color: #856404; }
        
        .transfer-section {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            text-align: center;
        }
        
        .transfer-btn {
            background: rgba(255,255,255,0.2);
            border: 2px solid rgba(255,255,255,0.3);
            color: white;
            padding: 12px 24px;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 12px;
        }
        
        .transfer-btn:hover {
            background: rgba(255,255,255,0.3);
            border-color: rgba(255,255,255,0.5);
            transform: translateY(-1px);
        }
        
        .transfer-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        
        .transfer-status {
            margin-top: 12px;
            padding: 8px;
            border-radius: 4px;
            font-size: 13px;
        }
        
        .transfer-success {
            background: rgba(255,255,255,0.2);
            color: white;
        }
        
        .transfer-error {
            background: #dc3545;
            color: white;
        }
        
        .call-metrics {
            background: #e3f2fd;
            border-left: 4px solid #2196f3;
        }
        
        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 8px;
        }
        
        .metric-item {
            text-align: center;
            padding: 8px;
            background: white;
            border-radius: 4px;
        }
        
        .metric-value {
            font-size: 18px;
            font-weight: 700;
            color: #1976d2;
        }
        
        .metric-label {
            font-size: 10px;
            color: #666;
            text-transform: uppercase;
        }
        
        @media (max-width: 600px) {
            .container { padding: 8px; }
            .info-grid { grid-template-columns: 1fr; }
            .metrics-grid { grid-template-columns: repeat(2, 1fr); }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>{{ $lead->name }}</h1>
            <div class="meta">
                Lead ID: {{ $lead->id }} | Source: {{ ucfirst($lead->source) }} | 
                Received: {{ $lead->received_at->format('M j, Y g:i A') }}
            </div>
        </div>

        <!-- Contact Information -->
        <div class="section">
            <div class="section-title">📞 Contact Information</div>
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Phone</div>
                    <div class="info-value">{{ $lead->phone ?: 'Not provided' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Email</div>
                    <div class="info-value">{{ $lead->email ?: 'Not provided' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Address</div>
                    <div class="info-value">{{ $lead->address ?: 'Not provided' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">City, State ZIP</div>
                    <div class="info-value">
                        {{ trim(($lead->city ?? '') . ', ' . ($lead->state ?? '') . ' ' . ($lead->zip_code ?? '')) ?: 'Not provided' }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Call Metrics (if available) -->
        @if($callMetrics)
        <div class="section call-metrics">
            <div class="section-title">📊 Call Metrics</div>
            <div class="metrics-grid">
                <div class="metric-item">
                    <div class="metric-value">{{ $callMetrics->call_attempts ?? 0 }}</div>
                    <div class="metric-label">Attempts</div>
                </div>
                <div class="metric-item">
                    <div class="metric-value">{{ $callMetrics->connected_time ? '✓' : '✗' }}</div>
                    <div class="metric-label">Connected</div>
                </div>
                <div class="metric-item">
                    <div class="metric-value">{{ $callMetrics->talk_time ?? 0 }}s</div>
                    <div class="metric-label">Talk Time</div>
                </div>
                <div class="metric-item">
                    <div class="metric-value">
                        <span class="status-badge status-{{ strtolower($callMetrics->call_status ?? 'new') }}">
                            {{ $callMetrics->call_status ?? 'NEW' }}
                        </span>
                    </div>
                    <div class="metric-label">Status</div>
                </div>
            </div>
        </div>
        @endif

        <!-- Drivers -->
        @if($lead->drivers && count($lead->drivers) > 0)
        <div class="section">
            <div class="section-title">👤 Drivers ({{ count($lead->drivers) }})</div>
            @foreach($lead->drivers as $index => $driver)
            <div class="driver-card">
                <h4>Driver {{ $index + 1 }}: {{ ($driver['first_name'] ?? '') . ' ' . ($driver['last_name'] ?? '') }}</h4>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Age</div>
                        <div class="info-value">
                            @if(isset($driver['birth_date']))
                                {{ \Carbon\Carbon::parse($driver['birth_date'])->age }} years
                            @else
                                Not provided
                            @endif
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Gender</div>
                        <div class="info-value">{{ $driver['gender'] ?? 'Not provided' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Marital Status</div>
                        <div class="info-value">{{ $driver['marital_status'] ?? 'Not provided' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">License State</div>
                        <div class="info-value">{{ $driver['license_state'] ?? 'Not provided' }}</div>
                    </div>
                </div>
                @if(isset($driver['accidents']) && count($driver['accidents']) > 0)
                <div style="margin-top: 8px;">
                    <div class="info-label">Recent Accidents</div>
                    <div class="info-value">{{ count($driver['accidents']) }} accident(s)</div>
                </div>
                @endif
            </div>
            @endforeach
        </div>
        @endif

        <!-- Vehicles -->
        @if($lead->vehicles && count($lead->vehicles) > 0)
        <div class="section">
            <div class="section-title">🚗 Vehicles ({{ count($lead->vehicles) }})</div>
            @foreach($lead->vehicles as $index => $vehicle)
            <div class="vehicle-card">
                <h4>Vehicle {{ $index + 1 }}: {{ ($vehicle['year'] ?? '') . ' ' . ($vehicle['make'] ?? '') . ' ' . ($vehicle['model'] ?? '') }}</h4>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Primary Use</div>
                        <div class="info-value">{{ $vehicle['primary_use'] ?? 'Not provided' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Annual Miles</div>
                        <div class="info-value">{{ isset($vehicle['annual_miles']) ? number_format($vehicle['annual_miles']) : 'Not provided' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Ownership</div>
                        <div class="info-value">{{ $vehicle['ownership'] ?? 'Not provided' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Garage</div>
                        <div class="info-value">{{ $vehicle['garage'] ?? 'Not provided' }}</div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif

        <!-- Current Policy -->
        @if($lead->current_policy)
        <div class="section">
            <div class="section-title">🛡️ Current Insurance</div>
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Insurance Company</div>
                    <div class="info-value">{{ $lead->current_policy['insurance_company'] ?? 'Not provided' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Coverage Type</div>
                    <div class="info-value">{{ $lead->current_policy['coverage_type'] ?? 'Not provided' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Expiration Date</div>
                    <div class="info-value">{{ $lead->current_policy['expiration_date'] ?? 'Not provided' }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Insured Since</div>
                    <div class="info-value">{{ $lead->current_policy['insured_since'] ?? 'Not provided' }}</div>
                </div>
            </div>
        </div>
        @endif

        <!-- Transfer Section -->
        <div class="section transfer-section">
            <div class="section-title">🔄 Transfer Lead</div>
            <p>Ready to transfer this lead to the buyer?</p>
            <button id="transferBtn" class="transfer-btn" onclick="initiateTransfer()">
                Transfer to Buyer
            </button>
            <div id="transferStatus"></div>
        </div>
    </div>

    <script>
        // Transfer functionality
        async function initiateTransfer() {
            const btn = document.getElementById('transferBtn');
            const status = document.getElementById('transferStatus');
            
            btn.disabled = true;
            btn.textContent = 'Processing...';
            status.innerHTML = '';
            
            try {
                const response = await fetch('{{ $transferUrl }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });
                
                const result = await response.json();
                
                if (result.success) {
                    status.innerHTML = '<div class="transfer-success">✅ Transfer initiated successfully!</div>';
                    btn.textContent = 'Transfer Completed';
                    
                    // Notify parent window if in iframe
                    if (window.parent !== window) {
                        window.parent.postMessage({
                            type: 'transferComplete',
                            leadId: {{ $lead->id }},
                            leadName: '{{ $lead->name }}',
                            status: 'success'
                        }, '*');
                    }
                } else {
                    throw new Error(result.error || 'Transfer failed');
                }
                
            } catch (error) {
                status.innerHTML = '<div class="transfer-error">❌ Transfer failed: ' + error.message + '</div>';
                btn.disabled = false;
                btn.textContent = 'Retry Transfer';
            }
        }
        
        // Auto-refresh call metrics every 30 seconds
        setInterval(function() {
            location.reload();
        }, 30000);
        
        // Notify parent window that iframe is loaded
        if (window.parent !== window) {
            window.parent.postMessage({
                type: 'iframeLoaded',
                leadId: {{ $lead->id }},
                leadName: '{{ $lead->name }}'
            }, '*');
        }
    </script>
</body>
</html>
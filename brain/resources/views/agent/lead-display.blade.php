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
        
        /* Ringba Qualification Form Styles */
        .qualification-form {
            background: #ffffff;
            border: 2px solid #007bff;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 12px rgba(0,123,255,0.15);
        }
        
        .qualification-header {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
            padding: 12px 16px;
            border-radius: 8px;
            margin: -20px -20px 20px -20px;
            text-align: center;
            font-weight: 600;
            font-size: 16px;
        }
        
        .lead-info-bubble {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 20px;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .lead-info-bubble .lead-name {
            font-weight: bold;
            font-size: 16px;
            color: #212529;
            margin-bottom: 4px;
        }
        
        .lead-info-bubble .lead-phone {
            font-weight: bold;
            font-size: 14px;
            color: #007bff;
            margin-bottom: 4px;
        }
        
        .lead-info-bubble .lead-address {
            font-size: 12px;
            color: #6c757d;
            margin-bottom: 8px;
        }
        
        .lead-comments {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 4px;
            padding: 8px;
            font-size: 11px;
            max-height: 60px;
            overflow-y: auto;
            color: #856404;
        }
        
        .question-group {
            margin-bottom: 16px;
            padding: 12px;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid #007bff;
        }
        
        .question-label {
            font-weight: bold;
            color: #212529;
            margin-bottom: 8px;
            font-size: 14px;
        }
        
        .question-select {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            font-size: 14px;
            background: white;
        }
        
        .question-select:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 2px rgba(0,123,255,0.25);
        }
        
        .conditional-question {
            margin-top: 12px;
            padding: 8px;
            background: #e3f2fd;
            border-radius: 4px;
            display: none;
        }
        
        .conditional-question.show {
            display: block;
        }
        
        .enrichment-buttons {
            display: flex;
            gap: 12px;
            justify-content: center;
            margin-top: 20px;
            flex-wrap: wrap;
        }
        
        .btn-enrichment {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
            min-width: 140px;
            justify-content: center;
        }
        
        .btn-insured {
            background: #28a745;
            color: white;
        }
        
        .btn-insured:hover {
            background: #1e7e34;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(40,167,69,0.3);
        }
        
        .btn-uninsured {
            background: #ffc107;
            color: #212529;
        }
        
        .btn-uninsured:hover {
            background: #e0a800;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(255,193,7,0.3);
        }
        
        .btn-homeowner {
            background: #007bff;
            color: white;
        }
        
        .btn-homeowner:hover {
            background: #0056b3;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,123,255,0.3);
        }

        @media (max-width: 600px) {
            .container { padding: 8px; }
            .info-grid { grid-template-columns: 1fr; }
            .metrics-grid { grid-template-columns: repeat(2, 1fr); }
            .enrichment-buttons { flex-direction: column; align-items: center; }
            .btn-enrichment { min-width: 200px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header - Agent View (No Admin Data) -->
        <div class="header">
            <h1>{{ $lead->name }}</h1>
            <div class="meta">
                Lead ID: {{ $lead->id }}
            </div>
        </div>

        <!-- Ringba Qualification Form -->
        <div class="qualification-form">
            <div class="qualification-header">
                🎯 Lead Qualification & Ringba Enrichment
            </div>
            
            <!-- Sticky Lead Info Bubble -->
            <div class="lead-info-bubble">
                <div class="lead-name">{{ $lead->name }}</div>
                <div class="lead-phone">{{ preg_replace('/(\d{3})(\d{3})(\d{4})/', '($1)$2-$3', preg_replace('/[^0-9]/', '', $lead->phone)) }}</div>
                <div class="lead-address">
                    {{ $lead->address ?? 'Address not provided' }}<br>
                    {{ $lead->city ?? 'City' }}, {{ $lead->state ?? 'ST' }} {{ $lead->zip_code ?? 'ZIP' }}
                </div>
                @if(isset($lead->comments) && !empty($lead->comments))
                <div class="lead-comments">
                    {{ $lead->comments }}
                </div>
                @endif
            </div>

            <!-- Qualification Questions -->
            <form id="qualificationForm">
                <!-- Insurance Questions -->
                <div class="question-group">
                    <label class="question-label">Are you currently insured?</label>
                    <select class="question-select" id="currently_insured" onchange="toggleInsuranceQuestions()">
                        <option value="">Select...</option>
                        <option value="yes">Yes</option>
                        <option value="no">No</option>
                    </select>
                    
                    <div id="insurance_questions" class="conditional-question">
                        <label class="question-label">Who is your current provider?</label>
                        <select class="question-select" id="current_provider">
                            <option value="">Select...</option>
                            <option value="state_farm">State Farm</option>
                            <option value="geico">GEICO</option>
                            <option value="progressive">Progressive</option>
                            <option value="allstate">Allstate</option>
                            <option value="farmers">Farmers</option>
                            <option value="usaa">USAA</option>
                            <option value="liberty_mutual">Liberty Mutual</option>
                            <option value="other">Other</option>
                        </select>
                        
                        <div style="margin-top: 12px;">
                            <label class="question-label">How long have you been continuously insured?</label>
                            <select class="question-select" id="insurance_duration">
                                <option value="">Select...</option>
                                <option value="under_6_months">Under 6 months</option>
                                <option value="6_months_1_year">6 months - 1 year</option>
                                <option value="1_3_years">1-3 years</option>
                                <option value="over_3_years">Over 3 years</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- License Question -->
                <div class="question-group">
                    <label class="question-label">Do you have an active driver's license?</label>
                    <select class="question-select" id="active_license">
                        <option value="">Select...</option>
                        <option value="yes">Yes</option>
                        <option value="no">No</option>
                        <option value="suspended">Suspended</option>
                    </select>
                </div>

                <!-- Risk Level Questions -->
                <div class="question-group">
                    <label class="question-label">DUI or SR22?</label>
                    <select class="question-select" id="dui_sr22" onchange="toggleDUIQuestions()">
                        <option value="">Select...</option>
                        <option value="no">No</option>
                        <option value="dui_only">DUI Only</option>
                        <option value="sr22_only">SR22 Only</option>
                        <option value="both">Both</option>
                    </select>
                    
                    <div id="dui_questions" class="conditional-question">
                        <label class="question-label">If DUI – How long ago?</label>
                        <select class="question-select" id="dui_timeframe">
                            <option value="">Select...</option>
                            <option value="1">Under 1 year</option>
                            <option value="2">1–3 years</option>
                            <option value="3">Over 3 years</option>
                        </select>
                    </div>
                </div>

                <!-- Address Questions -->
                <div class="question-group">
                    <label class="question-label">State</label>
                    <select class="question-select" id="state">
                        <option value="">Select State...</option>
                        <option value="AL" {{ ($lead->state ?? '') == 'AL' ? 'selected' : '' }}>AL</option>
                        <option value="AK" {{ ($lead->state ?? '') == 'AK' ? 'selected' : '' }}>AK</option>
                        <option value="AZ" {{ ($lead->state ?? '') == 'AZ' ? 'selected' : '' }}>AZ</option>
                        <option value="AR" {{ ($lead->state ?? '') == 'AR' ? 'selected' : '' }}>AR</option>
                        <option value="CA" {{ ($lead->state ?? '') == 'CA' ? 'selected' : '' }}>CA</option>
                        <option value="CO" {{ ($lead->state ?? '') == 'CO' ? 'selected' : '' }}>CO</option>
                        <option value="CT" {{ ($lead->state ?? '') == 'CT' ? 'selected' : '' }}>CT</option>
                        <option value="DE" {{ ($lead->state ?? '') == 'DE' ? 'selected' : '' }}>DE</option>
                        <option value="FL" {{ ($lead->state ?? '') == 'FL' ? 'selected' : '' }}>FL</option>
                        <option value="GA" {{ ($lead->state ?? '') == 'GA' ? 'selected' : '' }}>GA</option>
                        <option value="HI" {{ ($lead->state ?? '') == 'HI' ? 'selected' : '' }}>HI</option>
                        <option value="ID" {{ ($lead->state ?? '') == 'ID' ? 'selected' : '' }}>ID</option>
                        <option value="IL" {{ ($lead->state ?? '') == 'IL' ? 'selected' : '' }}>IL</option>
                        <option value="IN" {{ ($lead->state ?? '') == 'IN' ? 'selected' : '' }}>IN</option>
                        <option value="IA" {{ ($lead->state ?? '') == 'IA' ? 'selected' : '' }}>IA</option>
                        <option value="KS" {{ ($lead->state ?? '') == 'KS' ? 'selected' : '' }}>KS</option>
                        <option value="KY" {{ ($lead->state ?? '') == 'KY' ? 'selected' : '' }}>KY</option>
                        <option value="LA" {{ ($lead->state ?? '') == 'LA' ? 'selected' : '' }}>LA</option>
                        <option value="ME" {{ ($lead->state ?? '') == 'ME' ? 'selected' : '' }}>ME</option>
                        <option value="MD" {{ ($lead->state ?? '') == 'MD' ? 'selected' : '' }}>MD</option>
                        <option value="MA" {{ ($lead->state ?? '') == 'MA' ? 'selected' : '' }}>MA</option>
                        <option value="MI" {{ ($lead->state ?? '') == 'MI' ? 'selected' : '' }}>MI</option>
                        <option value="MN" {{ ($lead->state ?? '') == 'MN' ? 'selected' : '' }}>MN</option>
                        <option value="MS" {{ ($lead->state ?? '') == 'MS' ? 'selected' : '' }}>MS</option>
                        <option value="MO" {{ ($lead->state ?? '') == 'MO' ? 'selected' : '' }}>MO</option>
                        <option value="MT" {{ ($lead->state ?? '') == 'MT' ? 'selected' : '' }}>MT</option>
                        <option value="NE" {{ ($lead->state ?? '') == 'NE' ? 'selected' : '' }}>NE</option>
                        <option value="NV" {{ ($lead->state ?? '') == 'NV' ? 'selected' : '' }}>NV</option>
                        <option value="NH" {{ ($lead->state ?? '') == 'NH' ? 'selected' : '' }}>NH</option>
                        <option value="NJ" {{ ($lead->state ?? '') == 'NJ' ? 'selected' : '' }}>NJ</option>
                        <option value="NM" {{ ($lead->state ?? '') == 'NM' ? 'selected' : '' }}>NM</option>
                        <option value="NY" {{ ($lead->state ?? '') == 'NY' ? 'selected' : '' }}>NY</option>
                        <option value="NC" {{ ($lead->state ?? '') == 'NC' ? 'selected' : '' }}>NC</option>
                        <option value="ND" {{ ($lead->state ?? '') == 'ND' ? 'selected' : '' }}>ND</option>
                        <option value="OH" {{ ($lead->state ?? '') == 'OH' ? 'selected' : '' }}>OH</option>
                        <option value="OK" {{ ($lead->state ?? '') == 'OK' ? 'selected' : '' }}>OK</option>
                        <option value="OR" {{ ($lead->state ?? '') == 'OR' ? 'selected' : '' }}>OR</option>
                        <option value="PA" {{ ($lead->state ?? '') == 'PA' ? 'selected' : '' }}>PA</option>
                        <option value="RI" {{ ($lead->state ?? '') == 'RI' ? 'selected' : '' }}>RI</option>
                        <option value="SC" {{ ($lead->state ?? '') == 'SC' ? 'selected' : '' }}>SC</option>
                        <option value="SD" {{ ($lead->state ?? '') == 'SD' ? 'selected' : '' }}>SD</option>
                        <option value="TN" {{ ($lead->state ?? '') == 'TN' ? 'selected' : '' }}>TN</option>
                        <option value="TX" {{ ($lead->state ?? '') == 'TX' ? 'selected' : '' }}>TX</option>
                        <option value="UT" {{ ($lead->state ?? '') == 'UT' ? 'selected' : '' }}>UT</option>
                        <option value="VT" {{ ($lead->state ?? '') == 'VT' ? 'selected' : '' }}>VT</option>
                        <option value="VA" {{ ($lead->state ?? '') == 'VA' ? 'selected' : '' }}>VA</option>
                        <option value="WA" {{ ($lead->state ?? '') == 'WA' ? 'selected' : '' }}>WA</option>
                        <option value="WV" {{ ($lead->state ?? '') == 'WV' ? 'selected' : '' }}>WV</option>
                        <option value="WI" {{ ($lead->state ?? '') == 'WI' ? 'selected' : '' }}>WI</option>
                        <option value="WY" {{ ($lead->state ?? '') == 'WY' ? 'selected' : '' }}>WY</option>
                        <option value="DC" {{ ($lead->state ?? '') == 'DC' ? 'selected' : '' }}>DC</option>
                    </select>
                </div>

                <div class="question-group">
                    <label class="question-label">ZIP Code</label>
                    <input type="text" class="question-select" id="zip_code" value="{{ $lead->zip_code ?? '' }}" placeholder="Enter ZIP code">
                </div>

                <!-- Auto Question -->
                <div class="question-group">
                    <label class="question-label">How many cars are you going to need a quote for?</label>
                    <select class="question-select" id="num_vehicles">
                        <option value="">Select...</option>
                        <option value="1">1 Vehicle</option>
                        <option value="2">2 Vehicles</option>
                        <option value="3">3 Vehicles</option>
                        <option value="4">4+ Vehicles</option>
                    </select>
                </div>

                <!-- Home Ownership -->
                <div class="question-group">
                    <label class="question-label">Do you own or rent your home?</label>
                    <select class="question-select" id="home_status">
                        <option value="">Select...</option>
                        <option value="own">Own</option>
                        <option value="rent">Rent</option>
                        <option value="other">Other</option>
                    </select>
                </div>

                <!-- Competitive Quote -->
                <div class="question-group">
                    <label class="question-label">Have you received a quote from Allstate in the last 2 months?</label>
                    <select class="question-select" id="allstate_quote">
                        <option value="">Select...</option>
                        <option value="yes">Yes</option>
                        <option value="no">No</option>
                    </select>
                </div>

                <!-- Intent -->
                <div class="question-group">
                    <label class="question-label">Ready to speak with an agent now?</label>
                    <select class="question-select" id="ready_to_speak">
                        <option value="">Select...</option>
                        <option value="yes">Yes</option>
                        <option value="no">No</option>
                        <option value="maybe">Maybe</option>
                    </select>
                </div>
            </form>

            <!-- Ringba Enrichment Buttons -->
            <div class="enrichment-buttons">
                <button type="button" class="btn-enrichment btn-insured" onclick="enrichLead('insured')">
                    🛡️ Insured
                </button>
                <button type="button" class="btn-enrichment btn-uninsured" onclick="enrichLead('uninsured')">
                    ⚠️ Uninsured
                </button>
                <button type="button" class="btn-enrichment btn-homeowner" onclick="enrichLead('homeowner')">
                    🏠 Homeowner
                </button>
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

        <!-- Call Metrics removed from agent view - admin only data -->

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
                
                <!-- Violations & Accidents - Important for Agent -->
                <div class="info-grid" style="margin-top: 12px; padding-top: 12px; border-top: 1px solid #e9ecef;">
                    <div class="info-item">
                        <div class="info-label">Violations</div>
                        <div class="info-value">
                            @if(isset($driver['violations']) && count($driver['violations']) > 0)
                                <span style="color: #dc3545; font-weight: bold;">{{ count($driver['violations']) }} violation(s)</span>
                                <button type="button" class="btn btn-sm btn-outline-info" style="margin-left: 8px; padding: 2px 8px; font-size: 10px;" onclick="toggleDetails('violations-{{ $index }}')">View Details</button>
                                <div id="violations-{{ $index }}" class="violation-details" style="display: none; margin-top: 8px; padding: 8px; background: #fff3cd; border-radius: 4px; font-size: 11px;">
                                    @foreach($driver['violations'] as $violationIndex => $violation)
                                        <div style="margin-bottom: 6px; padding-bottom: 6px; border-bottom: 1px solid #ffeaa7;">
                                            <strong>Violation {{ $violationIndex + 1 }}:</strong><br>
                                            <strong>Type:</strong> {{ $violation['violation_type'] ?? 'Not specified' }}<br>
                                            <strong>Date:</strong> {{ $violation['violation_date'] ?? 'Not specified' }}<br>
                                            @if(isset($violation['description']))
                                                <strong>Description:</strong> {{ $violation['description'] }}<br>
                                            @endif
                                            @if(isset($violation['state']))
                                                <strong>State:</strong> {{ $violation['state'] }}<br>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @elseif(isset($driver['violations']))
                                <span style="color: #28a745;">Clean record</span>
                            @else
                                Not provided
                            @endif
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Accidents</div>
                        <div class="info-value">
                            @if(isset($driver['accidents']) && count($driver['accidents']) > 0)
                                <span style="color: #dc3545; font-weight: bold;">{{ count($driver['accidents']) }} accident(s)</span>
                                <button type="button" class="btn btn-sm btn-outline-info" style="margin-left: 8px; padding: 2px 8px; font-size: 10px;" onclick="toggleDetails('accidents-{{ $index }}')">View Details</button>
                                <div id="accidents-{{ $index }}" class="accident-details" style="display: none; margin-top: 8px; padding: 8px; background: #f8d7da; border-radius: 4px; font-size: 11px;">
                                    @foreach($driver['accidents'] as $accidentIndex => $accident)
                                        <div style="margin-bottom: 6px; padding-bottom: 6px; border-bottom: 1px solid #f5c6cb;">
                                            <strong>Accident {{ $accidentIndex + 1 }}:</strong><br>
                                            <strong>Date:</strong> {{ $accident['accident_date'] ?? 'Not specified' }}<br>
                                            <strong>Type:</strong> {{ $accident['accident_type'] ?? 'Not specified' }}<br>
                                            @if(isset($accident['description']))
                                                <strong>Description:</strong> {{ $accident['description'] }}<br>
                                            @endif
                                            @if(isset($accident['at_fault']))
                                                <strong>At Fault:</strong> {{ $accident['at_fault'] ? 'Yes' : 'No' }}<br>
                                            @endif
                                            @if(isset($accident['damage_amount']))
                                                <strong>Damage Amount:</strong> ${{ number_format($accident['damage_amount']) }}<br>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @elseif(isset($driver['accidents']))
                                <span style="color: #28a745;">No accidents</span>
                            @else
                                Not provided
                            @endif
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">License Status</div>
                        <div class="info-value">{{ $driver['license_status'] ?? 'Not provided' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Years Licensed</div>
                        <div class="info-value">{{ $driver['years_licensed'] ?? 'Not provided' }}</div>
                    </div>
                </div>
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
        
        // Auto-refresh disabled for agent view - no call metrics displayed
        
        // Ringba Qualification Form Logic
        function toggleInsuranceQuestions() {
            const insured = document.getElementById('currently_insured').value;
            const insuranceQuestions = document.getElementById('insurance_questions');
            
            if (insured === 'yes') {
                insuranceQuestions.classList.add('show');
            } else {
                insuranceQuestions.classList.remove('show');
            }
        }
        
        function toggleDUIQuestions() {
            const duiSr22 = document.getElementById('dui_sr22').value;
            const duiQuestions = document.getElementById('dui_questions');
            
            if (duiSr22 === 'dui_only' || duiSr22 === 'both') {
                duiQuestions.classList.add('show');
            } else {
                duiQuestions.classList.remove('show');
            }
        }
        
        function getFormData() {
            const form = document.getElementById('qualificationForm');
            const formData = new FormData(form);
            const data = {};
            
            // Get all form values
            const elements = form.querySelectorAll('select, input');
            elements.forEach(element => {
                data[element.id] = element.value;
            });
            
            // Map values for Ringba enrichment (per your specification)
            const enrichmentData = {
                // Basic info from lead
                phone: '{{ preg_replace("/[^0-9]/", "", $lead->phone) }}',
                first_name: '{{ $lead->first_name ?? "" }}',
                last_name: '{{ $lead->last_name ?? "" }}',
                email: '{{ $lead->email ?? "" }}',
                address: '{{ $lead->address ?? "" }}',
                city: '{{ $lead->city ?? "" }}',
                state: '{{ $lead->state ?? "" }}', // Lead state, not form state
                zip_code: data.zip_code || '{{ $lead->zip_code ?? "" }}',
                
                // Qualification parameters (exact mapping per your spec)
                insured: data.currently_insured || '', // Q1: sent as-is (Yes/No)
                license: data.active_license || '', // Q4: sent as-is  
                
                // Q5 & Q6: DUI/SR22 logic mapping
                dui: (data.dui_sr22 === 'dui_only' || data.dui_sr22 === 'both') ? 'Y' : 'N',
                sr22: (data.dui_sr22 === 'sr22_only' || data.dui_sr22 === 'both') ? 'Y' : 'N',
                dui_when: data.dui_timeframe || '', // Maps to 1, 2, or 3
                
                // Q10: Home ownership mapping
                homeowner: data.home_status === 'own' ? 'Y' : 'N', // Own = Y, Rent/Other = N
                
                // Visual-only fields (not sent in enrichment per your spec)
                // Q2: current_provider - visual only
                // Q3: insurance_duration - visual only  
                // Q7: state_input - visual only
                // Q9: num_vehicles - not currently used
                // Q11: allstate_quote - visual only
                // Q12: ready_to_speak - visual only
            };
            
            return enrichmentData;
        }
        
        function buildEnrichmentURL(baseURL, data) {
            let url = baseURL;
            
            // Replace placeholders with actual data
            Object.keys(data).forEach(key => {
                const placeholder = `<<${key}>>`;
                const value = encodeURIComponent(data[key] || '');
                url = url.replace(new RegExp(placeholder, 'g'), value);
            });
            
            return url;
        }
        
        async function enrichLead(type) {
            const data = getFormData();
            
            // Ringba enrichment URLs with correct parameter mapping
            const enrichmentURLs = {
                insured: 'https://display.ringba.com/enrich/2674154334576444838?phone=<<phone>>&first_name=<<first_name>>&last_name=<<last_name>>&email=<<email>>&address=<<address>>&city=<<city>>&state=<<state>>&zip_code=<<zip_code>>&insured=<<insured>>&license=<<license>>&dui=<<dui>>&sr22=<<sr22>>&dui_when=<<dui_when>>&homeowner=<<homeowner>>',
                
                uninsured: 'https://display.ringba.com/enrich/2676487329580844084?phone=<<phone>>&first_name=<<first_name>>&last_name=<<last_name>>&email=<<email>>&address=<<address>>&city=<<city>>&state=<<state>>&zip_code=<<zip_code>>&insured=<<insured>>&license=<<license>>&dui=<<dui>>&sr22=<<sr22>>&dui_when=<<dui_when>>&homeowner=<<homeowner>>',
                
                homeowner: 'https://display.ringba.com/enrich/2717035800150673197?phone=<<phone>>&first_name=<<first_name>>&last_name=<<last_name>>&email=<<email>>&address=<<address>>&city=<<city>>&state=<<state>>&zip_code=<<zip_code>>&insured=<<insured>>&license=<<license>>&dui=<<dui>>&sr22=<<sr22>>&dui_when=<<dui_when>>&homeowner=<<homeowner>>'
            };
            
            const baseURL = enrichmentURLs[type];
            if (!baseURL) {
                alert('Invalid enrichment type');
                return;
            }
            
            const enrichmentURL = buildEnrichmentURL(baseURL, data);
            
            // Log the enrichment for debugging
            console.log('Ringba Enrichment:', {
                type: type,
                data: data,
                url: enrichmentURL
            });
            
            // Show confirmation
            const confirmation = confirm(
                `Enrich lead with Ringba (${type.toUpperCase()})?\n\n` +
                `Lead: ${data.first_name} ${data.last_name}\n` +
                `Phone: ${data.phone}\n` +
                `Type: ${type}\n\n` +
                `This will send the lead data to Ringba for campaign enrichment.`
            );
            
            if (confirmation) {
                // Get the button that was clicked
                const button = event.target;
                const originalText = button.innerHTML;
                
                try {
                    // First, save the qualification data to the database
                    const qualificationData = {
                        ...data,
                        enrichment_type: type,
                        enrichment_data: {
                            type: type,
                            url: enrichmentURL,
                            timestamp: new Date().toISOString()
                        }
                    };
                    
                    // Save to database
                    const saveResponse = await fetch(`/agent/lead/{{ $lead->id }}/qualification`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify(qualificationData)
                    });
                    
                    if (!saveResponse.ok) {
                        throw new Error('Failed to save qualification data');
                    }
                    
                    // Open enrichment URL in new tab
                    window.open(enrichmentURL, '_blank');
                    
                    // Update button to show it was saved and enriched
                    button.innerHTML = '✅ Saved & Enriched!';
                    button.style.opacity = '0.7';
                    button.disabled = true;
                    
                    setTimeout(() => {
                        button.innerHTML = originalText;
                        button.style.opacity = '1';
                        button.disabled = false;
                    }, 5000);
                    
                } catch (error) {
                    console.error('Error saving qualification data:', error);
                    alert('Error saving qualification data. Please try again.');
                    
                    // Still open enrichment URL even if save failed
                    window.open(enrichmentURL, '_blank');
                    
                    // Update button to show enrichment happened but save failed
                    button.innerHTML = '⚠️ Enriched (Save Failed)';
                    button.style.opacity = '0.7';
                    
                    setTimeout(() => {
                        button.innerHTML = originalText;
                        button.style.opacity = '1';
                        button.disabled = false;
                    }, 5000);
                }
            }
        }
        
        // Function to toggle violation/accident details
        function toggleDetails(elementId) {
            const element = document.getElementById(elementId);
            if (element.style.display === 'none' || element.style.display === '') {
                element.style.display = 'block';
            } else {
                element.style.display = 'none';
            }
        }

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
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
            font-weight: 700;
            color: #495057;
            margin-bottom: 12px;
            padding: 10px 15px;
            border-radius: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-left: 5px solid;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        /* Colorful section headers */
        .section-title.contact { 
            background: linear-gradient(135deg, #e3f2fd, #f3e5f5); 
            border-left-color: #2196f3; 
            color: #1565c0; 
        }
        .section-title.insurance { 
            background: linear-gradient(135deg, #e8f5e8, #f1f8e9); 
            border-left-color: #4caf50; 
            color: #2e7d32; 
        }
        .section-title.drivers { 
            background: linear-gradient(135deg, #fff3e0, #fce4ec); 
            border-left-color: #ff9800; 
            color: #e65100; 
        }
        .section-title.vehicles { 
            background: linear-gradient(135deg, #f3e5f5, #e1f5fe); 
            border-left-color: #9c27b0; 
            color: #6a1b9a; 
        }
        .section-title.qualification { 
            background: linear-gradient(135deg, #ffebee, #fce4ec); 
            border-left-color: #f44336; 
            color: #c62828; 
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

        /* Edit functionality styles */
        .edit-btn {
            background: #6c757d;
            color: white;
            border: none;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            cursor: pointer;
            margin-left: 8px;
        }

        .edit-btn:hover {
            background: #5a6268;
        }

        .edit-form {
            display: none;
            background: #f8f9fa;
            padding: 12px;
            border-radius: 4px;
            margin-top: 8px;
            border: 1px solid #dee2e6;
        }

        .edit-form.show {
            display: block;
        }

        .edit-form input, .edit-form select {
            width: 100%;
            padding: 6px;
            margin: 4px 0;
            border: 1px solid #ced4da;
            border-radius: 3px;
            font-size: 12px;
        }

        .edit-form label {
            font-size: 11px;
            font-weight: bold;
            color: #495057;
            margin-top: 8px;
            display: block;
        }

        .edit-form-buttons {
            margin-top: 8px;
        }

        .save-btn, .cancel-btn, .add-btn {
            padding: 4px 12px;
            border: none;
            border-radius: 3px;
            font-size: 11px;
            cursor: pointer;
            margin-right: 4px;
        }

        .save-btn {
            background: #28a745;
            color: white;
        }

        .cancel-btn {
            background: #6c757d;
            color: white;
        }

        .add-btn {
            background: #007bff;
            color: white;
            margin-bottom: 8px;
        }

        .add-btn:hover { background: #0056b3; }
        .save-btn:hover { background: #1e7e34; }
        .cancel-btn:hover { background: #5a6268; }
        
        .save-lead-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 25px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.4);
            z-index: 1000;
            transition: all 0.3s ease;
        }
        
        .save-lead-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(40, 167, 69, 0.6);
        }
        
        .save-lead-btn:active {
            transform: translateY(0);
        }
        
        @media (max-width: 600px) {
            .container { padding: 8px; }
            .info-grid { grid-template-columns: 1fr; }
            .metrics-grid { grid-template-columns: repeat(2, 1fr); }
            .enrichment-buttons { flex-direction: column; align-items: center; }
            .btn-enrichment { min-width: 200px; }
        }
        
        /* Allstate Validation Styles */
        .validation-error {
            border: 2px solid #dc3545 !important;
            background-color: #fff5f5 !important;
            animation: validationPulse 2s ease-in-out;
        }
        
        .validation-warning {
            border: 2px solid #ffc107 !important;
            background-color: #fffbf0 !important;
        }
        
        .validation-success {
            border: 2px solid #28a745 !important;
            background-color: #f0fff0 !important;
        }
        
        @keyframes validationPulse {
            0% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7); }
            70% { box-shadow: 0 0 0 10px rgba(220, 53, 69, 0); }
            100% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0); }
        }
        
        .validation-tooltip {
            position: absolute;
            background: #dc3545;
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            z-index: 1000;
            white-space: nowrap;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        }
        
        .validation-tooltip::after {
            content: '';
            position: absolute;
            top: 100%;
            left: 50%;
            transform: translateX(-50%);
            border: 4px solid transparent;
            border-top-color: #dc3545;
        }
        
        .validation-summary {
            position: fixed;
            top: 80px;
            right: 20px;
            background: white;
            border-radius: 8px;
            padding: 16px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 1001;
            max-width: 300px;
            display: none;
        }
        
        .validation-summary.show {
            display: block;
            animation: slideInRight 0.3s ease-out;
        }
        
        .validation-summary.error {
            border-left: 4px solid #dc3545;
        }
        
        .validation-summary.warning {
            border-left: 4px solid #ffc107;
        }
        
        .validation-summary.success {
            border-left: 4px solid #28a745;
        }
        
        @keyframes slideInRight {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        
        .validation-summary h4 {
            margin: 0 0 8px 0;
            font-size: 14px;
        }
        
        .validation-summary p {
            margin: 0 0 8px 0;
            font-size: 12px;
            color: #666;
        }
        
        .validation-summary ul {
            margin: 8px 0;
            padding-left: 16px;
            font-size: 11px;
        }
        
        .validation-summary li {
            margin: 2px 0;
            color: #666;
        }
        
        .validation-close {
            position: absolute;
            top: 8px;
            right: 8px;
            background: none;
            border: none;
            font-size: 16px;
            cursor: pointer;
            color: #999;
        }
        
        .validation-close:hover {
            color: #333;
        }
        
        .enrichment-blocked {
            opacity: 0.5;
            cursor: not-allowed !important;
            pointer-events: none;
        }
        
        .enrichment-ready {
            animation: readyPulse 2s infinite;
        }
        
        @keyframes readyPulse {
            0% { box-shadow: 0 0 0 0 rgba(40, 167, 69, 0.7); }
            70% { box-shadow: 0 0 0 10px rgba(40, 167, 69, 0); }
            100% { box-shadow: 0 0 0 0 rgba(40, 167, 69, 0); }
        }
        
        .field-status-indicator {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            margin-left: 4px;
        }
        
        .field-status-indicator.missing {
            background: #dc3545;
        }
        
        .field-status-indicator.complete {
            background: #28a745;
        }
        
        .validation-progress {
            position: fixed;
            top: 60px;
            right: 20px;
            background: white;
            border-radius: 8px;
            padding: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            z-index: 999;
            font-size: 12px;
            min-width: 200px;
        }
        
        .validation-progress-bar {
            width: 100%;
            height: 6px;
            background: #e9ecef;
            border-radius: 3px;
            overflow: hidden;
            margin: 8px 0;
        }
        
        .validation-progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #dc3545, #ffc107, #28a745);
            transition: width 0.3s ease;
        }
    </style>
</head>
<body>
    <!-- Save Lead Button -->
    <button class="save-lead-btn" onclick="saveAllLeadData()">💾 Save Lead</button>
    
    <!-- Validation Progress Indicator -->
    <div id="validation-progress" class="validation-progress" style="display: none;">
        <div>Allstate Readiness: <span id="validation-percentage">0%</span></div>
        <div class="validation-progress-bar">
            <div id="validation-progress-fill" class="validation-progress-fill" style="width: 0%"></div>
        </div>
        <div id="validation-status">Checking requirements...</div>
    </div>
    
    <!-- Validation Summary Modal -->
    <div id="validation-summary" class="validation-summary">
        <button class="validation-close" onclick="closeValidationSummary()">×</button>
        <h4 id="validation-title">Validation Status</h4>
        <p id="validation-message">Checking lead requirements...</p>
        <ul id="validation-details"></ul>
    </div>
    
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
            <div class="qualification-header section-title qualification">
                🎯 Lead Qualification & Ringba Enrichment (Enhanced)
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
                        <select class="question-select" id="current_provider" onchange="updateInsuranceSection()">
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
            <div class="section-title contact">📞 Contact Information <button class="edit-btn" onclick="toggleEdit('contact')">Edit</button></div>
            <div class="info-grid" id="contact-display">
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

            <!-- Edit Form -->
            <div class="edit-form" id="contact-edit">
                <label>Phone:</label>
                <input type="text" id="edit-phone" value="{{ $lead->phone ?? '' }}" placeholder="Phone number">
                
                <label>Email:</label>
                <input type="email" id="edit-email" value="{{ $lead->email ?? '' }}" placeholder="Email address">
                
                <label>Address:</label>
                <input type="text" id="edit-address" value="{{ $lead->address ?? '' }}" placeholder="Street address">
                
                <label>City:</label>
                <input type="text" id="edit-city" value="{{ $lead->city ?? '' }}" placeholder="City">
                
                <label>State:</label>
                <select id="edit-state">
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
                
                <label>ZIP Code:</label>
                <input type="text" id="edit-zip" value="{{ $lead->zip_code ?? '' }}" placeholder="ZIP code">
                
                <div class="edit-form-buttons">
                    <button class="save-btn" onclick="saveContact()">Save</button>
                    <button class="cancel-btn" onclick="cancelEdit('contact')">Cancel</button>
                </div>
                </div>
                </div>

        <!-- Call Metrics removed from agent view - admin only data -->

        <!-- Drivers -->
        @if($lead->drivers && count($lead->drivers) > 0)
        <div class="section">
            <div class="section-title drivers">👤 Drivers ({{ count($lead->drivers) }}) <button class="add-btn" onclick="addDriver()">Add Driver</button></div>
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
                        <div class="info-value">
                            {{ $driver['gender'] ?? 'Not provided' }}
                            @if(isset($driver['gender']) && !in_array($driver['gender'], ['M', 'F', 'Male', 'Female']))
                                <span style="font-size: 10px; color: #6c757d; margin-left: 8px;">(imported data)</span>
                            @endif
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Marital Status</div>
                        <div class="info-value">
                            {{ $driver['marital_status'] ?? 'Not provided' }}
                            @if(isset($driver['marital_status']) && !in_array($driver['marital_status'], ['Single', 'Married', 'Divorced', 'Widowed', 'Separated']))
                                <span style="font-size: 10px; color: #6c757d; margin-left: 8px;">(imported data)</span>
                            @endif
                        </div>
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
                                <button type="button" class="add-btn" style="margin-left: 4px; padding: 2px 6px; font-size: 9px;" onclick="addViolation({{ $index }})">Add Violation</button>
                                <div id="violations-{{ $index }}" class="violation-details" style="display: none; margin-top: 8px; padding: 8px; background: #fff3cd; border-radius: 4px; font-size: 11px;">
                                    @foreach($driver['violations'] as $violationIndex => $violation)
                                        <div style="margin-bottom: 6px; padding-bottom: 6px; border-bottom: 1px solid #ffeaa7;">
                                            <strong>Violation {{ $violationIndex + 1 }}:</strong><br>
                                            <strong>Type:</strong> {{ $violation['violation_type'] ?? 'Not specified' }}
                                            @if(isset($violation['violation_type']) && !in_array($violation['violation_type'], ['Speeding', 'DUI/DWI', 'Reckless Driving', 'Running Red Light', 'Stop Sign Violation', 'Improper Lane Change', 'Following Too Closely', 'Failure to Yield', 'Careless Driving']))
                                                <span style="font-size: 9px; color: #6c757d;">(imported)</span>
                                            @endif<br>
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
                                <button type="button" class="add-btn" style="margin-left: 8px; padding: 2px 6px; font-size: 9px;" onclick="addViolation({{ $index }})">Add Violation</button>
                            @else
                                Not provided
                                <button type="button" class="add-btn" style="margin-left: 8px; padding: 2px 6px; font-size: 9px;" onclick="addViolation({{ $index }})">Add Violation</button>
                            @endif
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Accidents</div>
                        <div class="info-value">
                @if(isset($driver['accidents']) && count($driver['accidents']) > 0)
                                <span style="color: #dc3545; font-weight: bold;">{{ count($driver['accidents']) }} accident(s)</span>
                                <button type="button" class="btn btn-sm btn-outline-info" style="margin-left: 8px; padding: 2px 8px; font-size: 10px;" onclick="toggleDetails('accidents-{{ $index }}')">View Details</button>
                                <button type="button" class="add-btn" style="margin-left: 4px; padding: 2px 6px; font-size: 9px;" onclick="addAccident({{ $index }})">Add Accident</button>
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
                                <button type="button" class="add-btn" style="margin-left: 8px; padding: 2px 6px; font-size: 9px;" onclick="addAccident({{ $index }})">Add Accident</button>
                            @else
                                Not provided
                                <button type="button" class="add-btn" style="margin-left: 8px; padding: 2px 6px; font-size: 9px;" onclick="addAccident({{ $index }})">Add Accident</button>
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
        @else
        <!-- No Drivers Section -->
        <div class="section">
            <div class="section-title drivers">👤 Drivers (0) <button class="add-btn" onclick="addDriver()">Add Driver</button></div>
            <p style="color: #6c757d; font-style: italic; text-align: center; padding: 20px;">No drivers added yet. Click "Add Driver" to add driver information.</p>
        </div>
        @endif

        <!-- Vehicles -->
        @if($lead->vehicles && count($lead->vehicles) > 0)
        <div class="section">
            <div class="section-title vehicles">🚗 Vehicles ({{ count($lead->vehicles) }}) <button class="add-btn" onclick="addVehicle()">Add Vehicle</button></div>
            @foreach($lead->vehicles as $index => $vehicle)
            <div class="vehicle-card">
                <h4>Vehicle {{ $index + 1 }}: {{ ($vehicle['year'] ?? '') . ' ' . ($vehicle['make'] ?? '') . ' ' . ($vehicle['model'] ?? '') }}</h4>
                @if(isset($vehicle['vin']) && !empty($vehicle['vin']))
                <div style="margin-bottom: 8px; padding: 6px; background: #e3f2fd; border-radius: 4px; font-size: 12px;">
                    <strong>VIN:</strong> <span style="font-family: monospace; font-weight: bold;">{{ $vehicle['vin'] }}</span>
                </div>
                @endif
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
        @else
        <!-- No Vehicles Section -->
        <div class="section">
            <div class="section-title vehicles">🚗 Vehicles (0) <button class="add-btn" onclick="addVehicle()">Add Vehicle</button></div>
            <p style="color: #6c757d; font-style: italic; text-align: center; padding: 20px;">No vehicles added yet. Click "Add Vehicle" to add vehicle information.</p>
        </div>
        @endif

        <!-- Current Policy - Always Show -->
        <div class="section">
            <div class="section-title insurance">🛡️ Current Insurance <button class="edit-btn" onclick="toggleEdit('insurance')">Edit</button></div>
            <div class="info-grid" id="insurance-display">
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
            <div class="edit-form" id="insurance-edit" style="display: none; background: #f8f9fa; padding: 15px; border-radius: 8px; margin-top: 10px;">
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Insurance Company</div>
                        <input type="text" id="insurance_company" value="{{ $lead->current_policy['insurance_company'] ?? '' }}" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                    </div>
                    <div class="info-item">
                        <div class="info-label">Coverage Type</div>
                        <select id="coverage_type" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                            <option value="">Select Coverage Type...</option>
                            <option value="Liability Only" {{ ($lead->current_policy['coverage_type'] ?? '') == 'Liability Only' ? 'selected' : '' }}>Liability Only</option>
                            <option value="Full Coverage" {{ ($lead->current_policy['coverage_type'] ?? '') == 'Full Coverage' ? 'selected' : '' }}>Full Coverage</option>
                            <option value="Comprehensive" {{ ($lead->current_policy['coverage_type'] ?? '') == 'Comprehensive' ? 'selected' : '' }}>Comprehensive</option>
                            <option value="Collision" {{ ($lead->current_policy['coverage_type'] ?? '') == 'Collision' ? 'selected' : '' }}>Collision</option>
                        </select>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Expiration Date</div>
                        <input type="date" id="expiration_date" value="{{ $lead->current_policy['expiration_date'] ?? '' }}" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                    </div>
                    <div class="info-item">
                        <div class="info-label">Insured Since</div>
                        <input type="date" id="insured_since" value="{{ $lead->current_policy['insured_since'] ?? '' }}" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                    </div>
                </div>
                <div style="margin-top: 15px; display: flex; gap: 10px;">
                    <button onclick="saveInsurance()" style="padding: 8px 16px; background: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer;">Save</button>
                    <button onclick="cancelEdit('insurance')" style="padding: 8px 16px; background: #6c757d; color: white; border: none; border-radius: 4px; cursor: pointer;">Cancel</button>
                </div>
            </div>
        </div>

    </div>

    <script>
        
        // Auto-refresh disabled for agent view - no call metrics displayed
        
        // Allstate Validation System
        let validationData = null;
        let validationTimer = null;
        
        // Initialize validation on page load
        document.addEventListener('DOMContentLoaded', function() {
            validateAllstateReadiness();
            // Re-validate when any form field changes
            document.addEventListener('input', debounceValidation);
            document.addEventListener('change', debounceValidation);
        });
        
        function debounceValidation() {
            clearTimeout(validationTimer);
            validationTimer = setTimeout(validateAllstateReadiness, 1000);
        }
        
        async function validateAllstateReadiness() {
            try {
                const response = await fetch(`/agent/lead/{{ $lead->id }}/validate-allstate`);
                const data = await response.json();
                
                validationData = data;
                updateValidationUI(data);
                
            } catch (error) {
                console.error('Validation error:', error);
            }
        }
        
        function updateValidationUI(data) {
            const validation = data.validation;
            const summary = data.summary;
            
            // Update progress indicator
            const totalFields = Object.keys(data.required_fields.lead).length + 
                               data.required_fields.drivers.fields ? Object.keys(data.required_fields.drivers.fields).length : 0 +
                               data.required_fields.vehicles.fields ? Object.keys(data.required_fields.vehicles.fields).length : 0 + 2; // +2 for insurance fields
            
            const missingCount = Object.keys(validation.missing_fields).length;
            const completedCount = Math.max(0, totalFields - missingCount);
            const percentage = Math.round((completedCount / totalFields) * 100);
            
            document.getElementById('validation-percentage').textContent = percentage + '%';
            document.getElementById('validation-progress-fill').style.width = percentage + '%';
            document.getElementById('validation-status').textContent = 
                validation.is_valid ? 'Ready for Allstate!' : `${missingCount} fields missing`;
            
            // Show/hide progress indicator
            const progressEl = document.getElementById('validation-progress');
            if (missingCount > 0 || !validation.is_valid) {
                progressEl.style.display = 'block';
            } else {
                progressEl.style.display = 'none';
            }
            
            // Highlight missing fields
            highlightMissingFields(validation.missing_fields, data.field_mapping);
            
            // Update enrichment buttons
            updateEnrichmentButtons(validation.is_valid, summary);
        }
        
        function highlightMissingFields(missingFields, fieldMapping) {
            // Clear previous highlighting
            document.querySelectorAll('.validation-error, .validation-warning').forEach(el => {
                el.classList.remove('validation-error', 'validation-warning');
            });
            
            // Remove existing tooltips
            document.querySelectorAll('.validation-tooltip').forEach(el => el.remove());
            
            // Highlight missing fields
            Object.keys(missingFields).forEach(fieldPath => {
                const fieldLabel = missingFields[fieldPath];
                
                // Handle lead fields
                if (fieldPath.startsWith('lead.')) {
                    const fieldName = fieldPath.replace('lead.', '');
                    const selectors = fieldMapping[fieldPath] || [`#contact-${fieldName}`, `.contact-info .${fieldName}`];
                    
                    selectors.forEach(selector => {
                        const element = document.querySelector(selector);
                        if (element) {
                            element.classList.add('validation-error');
                            addValidationTooltip(element, fieldLabel);
                        }
                    });
                }
                
                // Handle insurance fields
                if (fieldPath.startsWith('insurance.')) {
                    const fieldName = fieldPath.replace('insurance.', '');
                    const selectors = fieldMapping[fieldPath] || [`#${fieldName}`, `.insurance-section .${fieldName}`];
                    
                    selectors.forEach(selector => {
                        const element = document.querySelector(selector);
                        if (element) {
                            element.classList.add('validation-error');
                            addValidationTooltip(element, fieldLabel);
                        }
                    });
                }
                
                // Handle driver/vehicle count errors
                if (fieldPath.includes('.count')) {
                    const sectionName = fieldPath.split('.')[0];
                    const sectionElement = document.querySelector(`.${sectionName}-section`);
                    if (sectionElement) {
                        sectionElement.classList.add('validation-warning');
                        addValidationTooltip(sectionElement, fieldLabel);
                    }
                }
                
                // Handle specific driver/vehicle field errors
                if (fieldPath.includes('drivers.') && !fieldPath.includes('.count')) {
                    const parts = fieldPath.split('.');
                    if (parts.length >= 3) {
                        const driverIndex = parts[1];
                        const driverCard = document.querySelector(`.driver-card[data-index="${driverIndex}"]`);
                        if (driverCard) {
                            driverCard.classList.add('validation-warning');
                            addValidationTooltip(driverCard, fieldLabel);
                        }
                    }
                }
                
                if (fieldPath.includes('vehicles.') && !fieldPath.includes('.count')) {
                    const parts = fieldPath.split('.');
                    if (parts.length >= 3) {
                        const vehicleIndex = parts[1];
                        const vehicleCard = document.querySelector(`.vehicle-card[data-index="${vehicleIndex}"]`);
                        if (vehicleCard) {
                            vehicleCard.classList.add('validation-warning');
                            addValidationTooltip(vehicleCard, fieldLabel);
                        }
                    }
                }
            });
        }
        
        function addValidationTooltip(element, message) {
            const tooltip = document.createElement('div');
            tooltip.className = 'validation-tooltip';
            tooltip.textContent = message;
            
            element.style.position = 'relative';
            element.appendChild(tooltip);
            
            // Position tooltip
            setTimeout(() => {
                const rect = element.getBoundingClientRect();
                tooltip.style.top = '-30px';
                tooltip.style.left = '50%';
                tooltip.style.transform = 'translateX(-50%)';
            }, 10);
        }
        
        function updateEnrichmentButtons(isValid, summary) {
            const enrichmentButtons = document.querySelectorAll('.btn-enrichment');
            
            enrichmentButtons.forEach(button => {
                if (isValid) {
                    button.classList.remove('enrichment-blocked');
                    button.classList.add('enrichment-ready');
                    button.title = 'Ready for Allstate enrichment';
                } else {
                    button.classList.add('enrichment-blocked');
                    button.classList.remove('enrichment-ready');
                    button.title = summary.message || 'Complete required fields first';
                }
            });
        }
        
        function showValidationSummary(summary) {
            const summaryEl = document.getElementById('validation-summary');
            const titleEl = document.getElementById('validation-title');
            const messageEl = document.getElementById('validation-message');
            const detailsEl = document.getElementById('validation-details');
            
            titleEl.textContent = summary.title;
            messageEl.textContent = summary.message;
            
            // Clear previous details
            detailsEl.innerHTML = '';
            
            // Add missing field details
            if (summary.details) {
                Object.values(summary.details).forEach(detail => {
                    const li = document.createElement('li');
                    li.textContent = detail;
                    detailsEl.appendChild(li);
                });
            }
            
            // Set appropriate styling
            summaryEl.className = `validation-summary show ${summary.status}`;
            
            // Auto-hide after 10 seconds for success messages
            if (summary.status === 'success') {
                setTimeout(() => {
                    closeValidationSummary();
                }, 10000);
            }
        }
        
        function closeValidationSummary() {
            const summaryEl = document.getElementById('validation-summary');
            summaryEl.classList.remove('show');
        }
        
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
        
        // Update bottom insurance section when top provider is selected
        function updateInsuranceSection() {
            const currentProvider = document.getElementById('current_provider').value;
            
            if (currentProvider) {
                // Map provider values to display names
                const providerNames = {
                    'state_farm': 'State Farm',
                    'geico': 'GEICO',
                    'progressive': 'Progressive',
                    'allstate': 'Allstate',
                    'farmers': 'Farmers',
                    'usaa': 'USAA',
                    'liberty_mutual': 'Liberty Mutual',
                    'other': 'Other'
                };
                
                const displayName = providerNames[currentProvider] || currentProvider;
                
                // Update the display in the bottom insurance section
                const insuranceDisplay = document.querySelector('#insurance-display .info-item:first-child .info-value');
                if (insuranceDisplay) {
                    insuranceDisplay.textContent = displayName;
                }
                
                // Also update the edit form input if it exists
                const insuranceInput = document.getElementById('insurance_company');
                if (insuranceInput) {
                    insuranceInput.value = displayName;
                }
                
                console.log('Updated insurance section:', displayName);
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
            // CRITICAL: Validate for Allstate before allowing enrichment
            if (type === 'insured') {
                // Re-validate to get latest status
                await validateAllstateReadiness();
                
                if (!validationData || !validationData.validation.is_valid) {
                    const summary = validationData ? validationData.summary : {
                        title: 'Validation Error',
                        message: 'Unable to validate lead requirements. Please refresh and try again.',
                        status: 'error',
                        details: {}
                    };
                    
                    showValidationSummary(summary);
                    
                    // Show detailed error
                    let errorMessage = summary.title + '\n\n' + summary.message;
                    
                    if (summary.details && Object.keys(summary.details).length > 0) {
                        errorMessage += '\n\nMissing Required Fields:';
                        Object.values(summary.details).forEach(detail => {
                            errorMessage += '\n• ' + detail;
                        });
                    }
                    
                    alert(errorMessage);
                    return;
                }
                
                // Special check for insurance status (Allstate only accepts insured)
                const currentlyInsured = document.getElementById('currently_insured')?.value || '';
                if (!currentlyInsured || currentlyInsured.toLowerCase() === 'no') {
                    alert('❌ ALLSTATE REQUIREMENT ERROR\n\nAllstate only accepts leads that are currently insured.\n\nThis lead shows as uninsured and cannot be enriched to Allstate.\n\nPlease update the insurance status or use a different enrichment option.');
                    return;
                }
            }
            
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
                    // First, save ALL lead data to the database (comprehensive save)
                    button.innerHTML = '⏳ Saving...';
                    
                    // Get all form data including qualification answers
                    const qualificationData = getFormData();
                    
                    // Get contact information
                    const contactData = {
                        phone: document.getElementById('contact_phone')?.value || '{{ $lead->phone }}',
                        email: document.getElementById('contact_email')?.value || '{{ $lead->email }}',
                        address: document.getElementById('contact_address')?.value || '{{ $lead->address }}',
                        city: document.getElementById('contact_city')?.value || '{{ $lead->city }}',
                        state: document.getElementById('contact_state')?.value || '{{ $lead->state }}',
                        zip_code: document.getElementById('contact_zip_code')?.value || '{{ $lead->zip_code }}'
                    };
                    
                    // Get insurance information
                    const insuranceData = {
                        insurance_company: document.getElementById('insurance_company')?.value || '{{ $lead->current_policy['insurance_company'] ?? '' }}',
                        coverage_type: document.getElementById('coverage_type')?.value || '{{ $lead->current_policy['coverage_type'] ?? '' }}',
                        expiration_date: document.getElementById('expiration_date')?.value || '{{ $lead->current_policy['expiration_date'] ?? '' }}',
                        insured_since: document.getElementById('insured_since')?.value || '{{ $lead->current_policy['insured_since'] ?? '' }}'
                    };
                    
                    // Combine all data with enrichment info
                    const allData = {
                        qualification: {
                            ...qualificationData,
                            enrichment_type: type,
                            enrichment_data: {
                                type: type,
                                url: enrichmentURL,
                                timestamp: new Date().toISOString()
                            }
                        },
                        contact: contactData,
                        insurance: insuranceData
                    };
                    
                    // Save all data to database
                    const saveResponse = await fetch(`/agent/lead/{{ $lead->id }}/save-all`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify(allData)
                    });
                    
                    if (!saveResponse.ok) {
                        throw new Error('Failed to save lead data');
                    }
                    
                    // Open enrichment URL in new tab
                    window.open(enrichmentURL, '_blank');
                    
                    // Show success confirmation
                    const successSummary = {
                        title: '✅ Enrichment Successful!',
                        message: `Lead has been successfully enriched to ${type.toUpperCase()} campaign and all data has been saved.`,
                        status: 'success',
                        details: {}
                    };
                    showValidationSummary(successSummary);
                    
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
        
        // Edit functionality
        function toggleEdit(section) {
            const display = document.getElementById(section + '-display');
            const edit = document.getElementById(section + '-edit');
            
            if (edit.classList.contains('show')) {
                edit.classList.remove('show');
                display.style.display = 'grid';
            } else {
                edit.classList.add('show');
                display.style.display = 'none';
            }
        }
        
        function cancelEdit(section) {
            const display = document.getElementById(section + '-display');
            const edit = document.getElementById(section + '-edit');
            
            edit.classList.remove('show');
            display.style.display = 'grid';
        }
        
        async function saveContact() {
            const data = {
                phone: document.getElementById('edit-phone').value,
                email: document.getElementById('edit-email').value,
                address: document.getElementById('edit-address').value,
                city: document.getElementById('edit-city').value,
                state: document.getElementById('edit-state').value,
                zip_code: document.getElementById('edit-zip').value
            };
            
            try {
                const response = await fetch(`/agent/lead/{{ $lead->id }}/contact`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    alert('Contact information updated successfully!');
                    location.reload(); // Refresh to show updated data
                } else {
                    alert('Error: ' + result.error);
                }
            } catch (error) {
                alert('Error updating contact information: ' + error.message);
            }
        }
        
        function addViolation(driverIndex) {
            showViolationModal(driverIndex);
        }
        
        function showViolationModal(driverIndex) {
            const modalHtml = `
                <div id="violationModal" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 10000; display: flex; justify-content: center; align-items: center;">
                    <div style="background: white; padding: 30px; border-radius: 12px; width: 90%; max-width: 500px; max-height: 90%; overflow-y: auto; box-shadow: 0 10px 30px rgba(0,0,0,0.3);">
                        <h3 style="margin-top: 0; color: #dc3545; border-bottom: 2px solid #f44336; padding-bottom: 10px;">⚠️ Add Violation</h3>
                        
                        <div style="margin-bottom: 15px;">
                            <label style="display: block; font-weight: bold; margin-bottom: 5px;">Violation Type *:</label>
                            <select id="violationType" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;" onchange="handleOtherSelection(this, 'violationTypeOther')" required>
                                <option value="">Select Violation Type...</option>
                                <option value="Speeding">Speeding</option>
                                <option value="DUI/DWI">DUI/DWI</option>
                                <option value="Reckless Driving">Reckless Driving</option>
                                <option value="Running Red Light">Running Red Light</option>
                                <option value="Stop Sign Violation">Stop Sign Violation</option>
                                <option value="Improper Lane Change">Improper Lane Change</option>
                                <option value="Following Too Closely">Following Too Closely</option>
                                <option value="Failure to Yield">Failure to Yield</option>
                                <option value="Careless Driving">Careless Driving</option>
                                <option value="Other">Other (specify below)</option>
                            </select>
                            <input type="text" id="violationTypeOther" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; margin-top: 5px; display: none;" placeholder="Specify violation type...">
                        </div>
                        
                        <div style="margin-bottom: 15px;">
                            <label style="display: block; font-weight: bold; margin-bottom: 5px;">Violation Date *:</label>
                            <input type="date" id="violationDate" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;" required>
                        </div>
                        
                        <div style="margin-bottom: 15px;">
                            <label style="display: block; font-weight: bold; margin-bottom: 5px;">State:</label>
                            <select id="violationState" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                                <option value="">Select State...</option>
                                <option value="AL">AL</option><option value="AK">AK</option><option value="AZ">AZ</option><option value="AR">AR</option>
                                <option value="CA">CA</option><option value="CO">CO</option><option value="CT">CT</option><option value="DE">DE</option>
                                <option value="FL">FL</option><option value="GA">GA</option><option value="HI">HI</option><option value="ID">ID</option>
                                <option value="IL">IL</option><option value="IN">IN</option><option value="IA">IA</option><option value="KS">KS</option>
                                <option value="KY">KY</option><option value="LA">LA</option><option value="ME">ME</option><option value="MD">MD</option>
                                <option value="MA">MA</option><option value="MI">MI</option><option value="MN">MN</option><option value="MS">MS</option>
                                <option value="MO">MO</option><option value="MT">MT</option><option value="NE">NE</option><option value="NV">NV</option>
                                <option value="NH">NH</option><option value="NJ">NJ</option><option value="NM">NM</option><option value="NY">NY</option>
                                <option value="NC">NC</option><option value="ND">ND</option><option value="OH">OH</option><option value="OK">OK</option>
                                <option value="OR">OR</option><option value="PA">PA</option><option value="RI">RI</option><option value="SC">SC</option>
                                <option value="SD">SD</option><option value="TN">TN</option><option value="TX">TX</option><option value="UT">UT</option>
                                <option value="VT">VT</option><option value="VA">VA</option><option value="WA">WA</option><option value="WV">WV</option>
                                <option value="WI">WI</option><option value="WY">WY</option>
                            </select>
                        </div>
                        
                        <div style="margin-bottom: 20px;">
                            <label style="display: block; font-weight: bold; margin-bottom: 5px;">Description:</label>
                            <textarea id="violationDescription" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; height: 60px; resize: vertical;" placeholder="Optional additional details..."></textarea>
                        </div>
                        
                        <div style="display: flex; gap: 10px; justify-content: flex-end;">
                            <button onclick="closeViolationModal()" style="padding: 10px 20px; background: #6c757d; color: white; border: none; border-radius: 4px; cursor: pointer;">Cancel</button>
                            <button onclick="saveViolation(${driverIndex})" style="padding: 10px 20px; background: #dc3545; color: white; border: none; border-radius: 4px; cursor: pointer;">Add Violation</button>
                        </div>
                    </div>
                </div>
            `;
            document.body.insertAdjacentHTML('beforeend', modalHtml);
        }
        
        function closeViolationModal() {
            const modal = document.getElementById('violationModal');
            if (modal) {
                modal.remove();
            }
        }
        
        async function saveViolation(driverIndex) {
            const violationType = getSelectedValue(document.getElementById('violationType'), 'violationTypeOther');
            const violationDate = document.getElementById('violationDate').value;
            const violationState = document.getElementById('violationState').value;
            const description = document.getElementById('violationDescription').value;
            
            if (!violationType || !violationDate) {
                alert('Please fill in all required fields (Violation Type and Date)');
                return;
            }
            
            // Convert date from YYYY-MM-DD to MM/DD/YYYY for display
            const dateObj = new Date(violationDate);
            const formattedDate = (dateObj.getMonth() + 1).toString().padStart(2, '0') + '/' + 
                                dateObj.getDate().toString().padStart(2, '0') + '/' + 
                                dateObj.getFullYear();
            
            const data = {
                violation_type: violationType,
                violation_date: formattedDate,
                description: description,
                state: violationState
            };
            
            try {
                const response = await fetch(`/agent/lead/{{ $lead->id }}/driver/${driverIndex}/violation`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    closeViolationModal();
                    alert('Violation added successfully!');
                    location.reload();
                } else {
                    alert('Error: ' + result.error);
                }
            } catch (error) {
                console.error('Violation addition error:', error);
                alert('Error adding violation: ' + error.message);
            }
        }
        
        function addAccident(driverIndex) {
            showAccidentModal(driverIndex);
        }
        
        function showAccidentModal(driverIndex) {
            const modalHtml = `
                <div id="accidentModal" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 10000; display: flex; justify-content: center; align-items: center;">
                    <div style="background: white; padding: 30px; border-radius: 12px; width: 90%; max-width: 500px; max-height: 90%; overflow-y: auto; box-shadow: 0 10px 30px rgba(0,0,0,0.3);">
                        <h3 style="margin-top: 0; color: #dc3545; border-bottom: 2px solid #f44336; padding-bottom: 10px;">🚗 Add Accident</h3>
                        
                        <div style="margin-bottom: 15px;">
                            <label style="display: block; font-weight: bold; margin-bottom: 5px;">Accident Date *:</label>
                            <input type="date" id="accidentDate" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;" required>
                        </div>
                        
                        <div style="margin-bottom: 15px;">
                            <label style="display: block; font-weight: bold; margin-bottom: 5px;">Accident Type *:</label>
                            <select id="accidentType" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;" onchange="handleOtherSelection(this, 'accidentTypeOther')" required>
                                <option value="">Select Accident Type...</option>
                                <option value="Rear-end">Rear-end</option>
                                <option value="Side impact">Side impact</option>
                                <option value="Head-on">Head-on</option>
                                <option value="Single vehicle">Single vehicle</option>
                                <option value="Multi-vehicle">Multi-vehicle</option>
                                <option value="Backing/Parking">Backing/Parking</option>
                                <option value="Hit and run">Hit and run</option>
                                <option value="Rollover">Rollover</option>
                                <option value="Other">Other (specify below)</option>
                            </select>
                            <input type="text" id="accidentTypeOther" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; margin-top: 5px; display: none;" placeholder="Specify accident type...">
                        </div>
                        
                        <div style="margin-bottom: 15px;">
                            <label style="display: block; font-weight: bold; margin-bottom: 5px;">At Fault:</label>
                            <select id="accidentAtFault" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                                <option value="false">No - Not at fault</option>
                                <option value="true">Yes - At fault</option>
                                <option value="partial">Partial fault</option>
                                <option value="unknown">Unknown</option>
                            </select>
                        </div>
                        
                        <div style="margin-bottom: 15px;">
                            <label style="display: block; font-weight: bold; margin-bottom: 5px;">Damage Amount:</label>
                            <select id="accidentDamageAmount" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                                <option value="0">No damage</option>
                                <option value="500">Under $500</option>
                                <option value="1000">$500 - $1,000</option>
                                <option value="2500">$1,000 - $2,500</option>
                                <option value="5000">$2,500 - $5,000</option>
                                <option value="10000">$5,000 - $10,000</option>
                                <option value="15000">Over $10,000</option>
                            </select>
                        </div>
                        
                        <div style="margin-bottom: 20px;">
                            <label style="display: block; font-weight: bold; margin-bottom: 5px;">Description:</label>
                            <textarea id="accidentDescription" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; height: 60px; resize: vertical;" placeholder="Optional additional details..."></textarea>
                        </div>
                        
                        <div style="display: flex; gap: 10px; justify-content: flex-end;">
                            <button onclick="closeAccidentModal()" style="padding: 10px 20px; background: #6c757d; color: white; border: none; border-radius: 4px; cursor: pointer;">Cancel</button>
                            <button onclick="saveAccident(${driverIndex})" style="padding: 10px 20px; background: #dc3545; color: white; border: none; border-radius: 4px; cursor: pointer;">Add Accident</button>
                        </div>
                    </div>
                </div>
            `;
            document.body.insertAdjacentHTML('beforeend', modalHtml);
        }
        
        function closeAccidentModal() {
            const modal = document.getElementById('accidentModal');
            if (modal) {
                modal.remove();
            }
        }
        
        async function saveAccident(driverIndex) {
            const accidentDate = document.getElementById('accidentDate').value;
            const accidentType = getSelectedValue(document.getElementById('accidentType'), 'accidentTypeOther');
            const atFault = document.getElementById('accidentAtFault').value;
            const damageAmount = document.getElementById('accidentDamageAmount').value;
            const description = document.getElementById('accidentDescription').value;
            
            if (!accidentDate || !accidentType) {
                alert('Please fill in all required fields (Accident Date and Type)');
                return;
            }
            
            // Convert date from YYYY-MM-DD to MM/DD/YYYY for display
            const dateObj = new Date(accidentDate);
            const formattedDate = (dateObj.getMonth() + 1).toString().padStart(2, '0') + '/' + 
                                dateObj.getDate().toString().padStart(2, '0') + '/' + 
                                dateObj.getFullYear();
            
            const data = {
                accident_date: formattedDate,
                accident_type: accidentType,
                description: description,
                at_fault: atFault,
                damage_amount: parseFloat(damageAmount) || 0
            };
            
            try {
                const response = await fetch(`/agent/lead/{{ $lead->id }}/driver/${driverIndex}/accident`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    closeAccidentModal();
                    alert('Accident added successfully!');
                    location.reload();
                } else {
                    alert('Error: ' + result.error);
                }
            } catch (error) {
                console.error('Accident addition error:', error);
                alert('Error adding accident: ' + error.message);
            }
        }
        
        // WMI (World Manufacturer Identifier) codes for VIN generation
        const wmiCodes = {
            'Toyota': ['4T1', '5TD', '4T3', '2T1', 'JTD', 'JTE'],
            'Honda': ['1HG', '2HG', '3HG', '19X', 'JHM', 'SHH'],
            'Ford': ['1FA', '1FB', '1FC', '1FD', '1FT', '3FA'],
            'Chevrolet': ['1G1', '1GC', '1GB', '2GC', '3GC', 'KL8'],
            'Nissan': ['1N4', '1N6', '3N1', 'JN1', 'JN8', '5N1'],
            'BMW': ['WBA', 'WBS', 'WBY', '4US', '5UX', 'WBX'],
            'Mercedes-Benz': ['WDD', 'WDC', 'WDF', '4JG', '5NP', 'WDJ'],
            'Audi': ['WAU', 'WA1', 'WUA', 'TRU', '4GC', '4G2'],
            'Hyundai': ['KMH', 'KMF', 'KMG', '5NP', '5NK', 'KMA'],
            'Kia': ['KNA', 'KND', 'KNM', '5XY', '5XX', 'KNB'],
            'Subaru': ['4S3', '4S4', '4S6', 'JF1', 'JF2', '4S1'],
            'Mazda': ['JM1', 'JM3', '1YV', '4F2', '4F4', 'JMZ'],
            'Volkswagen': ['WVW', 'WV1', '3VW', '1VW', '9BW', 'WVG'],
            'Jeep': ['1C4', '1J4', '1J8', '3C4', '3C8', '1C8'],
            'Ram': ['1C6', '3C6', '3C7', '1D7', '3D7', '1D3'],
            'GMC': ['1GT', '1GK', '3GT', '2GT', '1GD', '3GD'],
            'Cadillac': ['1G6', '1GY', '3G6', '1GC', '3GY', '2G6'],
            'Lexus': ['JTH', '2T2', '4T4', 'JTE', 'JTG', '5TD'],
            'Acura': ['19U', 'JH4', '2HN', '5J6', '5J8', 'JHM'],
            'Infiniti': ['JNK', 'JN1', '5N3', '3PC', 'JNR', 'JNZ']
        };

        // Vehicle data for cascading dropdowns
        const vehicleData = {
            2024: {
                'Toyota': ['Camry', 'Corolla', 'RAV4', 'Highlander', 'Prius', 'Tacoma', 'Tundra', 'Sienna'],
                'Honda': ['Civic', 'Accord', 'CR-V', 'Pilot', 'Odyssey', 'Ridgeline', 'Passport', 'HR-V'],
                'Ford': ['F-150', 'Escape', 'Explorer', 'Mustang', 'Edge', 'Expedition', 'Ranger', 'Bronco'],
                'Chevrolet': ['Silverado', 'Equinox', 'Malibu', 'Traverse', 'Tahoe', 'Suburban', 'Colorado', 'Blazer'],
                'Nissan': ['Altima', 'Sentra', 'Rogue', 'Pathfinder', 'Frontier', 'Titan', 'Murano', 'Armada'],
                'BMW': ['3 Series', '5 Series', 'X3', 'X5', 'X1', 'X7', '7 Series', '4 Series'],
                'Mercedes-Benz': ['C-Class', 'E-Class', 'GLC', 'GLE', 'A-Class', 'S-Class', 'GLS', 'CLA'],
                'Audi': ['A4', 'A6', 'Q5', 'Q7', 'A3', 'Q3', 'A8', 'Q8'],
                'Hyundai': ['Elantra', 'Sonata', 'Tucson', 'Santa Fe', 'Palisade', 'Kona', 'Venue', 'Genesis G90'],
                'Kia': ['Forte', 'Optima', 'Sorento', 'Telluride', 'Sportage', 'Soul', 'Stinger', 'Carnival']
            },
            2023: {
                'Toyota': ['Camry', 'Corolla', 'RAV4', 'Highlander', 'Prius', 'Tacoma', 'Tundra', 'Sienna'],
                'Honda': ['Civic', 'Accord', 'CR-V', 'Pilot', 'Odyssey', 'Ridgeline', 'Passport', 'HR-V'],
                'Ford': ['F-150', 'Escape', 'Explorer', 'Mustang', 'Edge', 'Expedition', 'Ranger', 'Bronco'],
                'Chevrolet': ['Silverado', 'Equinox', 'Malibu', 'Traverse', 'Tahoe', 'Suburban', 'Colorado', 'Blazer'],
                'Nissan': ['Altima', 'Sentra', 'Rogue', 'Pathfinder', 'Frontier', 'Titan', 'Murano', 'Armada']
            },
            2022: {
                'Toyota': ['Camry', 'Corolla', 'RAV4', 'Highlander', 'Prius', 'Tacoma', 'Tundra', 'Sienna'],
                'Honda': ['Civic', 'Accord', 'CR-V', 'Pilot', 'Odyssey', 'Ridgeline', 'Passport', 'HR-V'],
                'Ford': ['F-150', 'Escape', 'Explorer', 'Mustang', 'Edge', 'Expedition', 'Ranger', 'Bronco'],
                'Chevrolet': ['Silverado', 'Equinox', 'Malibu', 'Traverse', 'Tahoe', 'Suburban', 'Colorado', 'Blazer'],
                'Nissan': ['Altima', 'Sentra', 'Rogue', 'Pathfinder', 'Frontier', 'Titan', 'Murano', 'Armada']
            }
        };

        async function addVehicle() {
            showVehicleModal();
        }

        function showVehicleModal() {
            // Create modal HTML with dropdowns
            const modalHtml = `
                <div id="vehicleModal" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; display: flex; align-items: center; justify-content: center;">
                    <div style="background: white; padding: 20px; border-radius: 8px; width: 90%; max-width: 500px; max-height: 80vh; overflow-y: auto;">
                        <h3 style="margin-bottom: 15px; color: #333;">Add New Vehicle</h3>
                        
                        <div style="margin-bottom: 10px;">
                            <label style="display: block; font-weight: bold; margin-bottom: 5px;">Year:</label>
                            <select id="vehicleYear" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;" onchange="updateMakes()">
                                <option value="">Select Year...</option>
                                <option value="2024">2024</option>
                                <option value="2023">2023</option>
                                <option value="2022">2022</option>
                                <option value="2021">2021</option>
                                <option value="2020">2020</option>
                                <option value="2019">2019</option>
                                <option value="2018">2018</option>
                                <option value="2017">2017</option>
                                <option value="2016">2016</option>
                                <option value="2015">2015</option>
                            </select>
                        </div>
                        
                        <div style="margin-bottom: 10px;">
                            <label style="display: block; font-weight: bold; margin-bottom: 5px;">Make:</label>
                            <select id="vehicleMake" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;" onchange="updateModels()" disabled>
                                <option value="">Select Make...</option>
                            </select>
                        </div>
                        
                        <div style="margin-bottom: 10px;">
                            <label style="display: block; font-weight: bold; margin-bottom: 5px;">Model:</label>
                            <select id="vehicleModel" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;" onchange="generateVINPrefix()" disabled>
                                <option value="">Select Model...</option>
                            </select>
                        </div>
                        
                        <div style="margin-bottom: 10px;">
                            <label style="display: block; font-weight: bold; margin-bottom: 5px;">VIN (Vehicle Identification Number):</label>
                            <input type="text" id="vehicleVIN" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;" placeholder="Auto-generated prefix + complete as needed" maxlength="17">
                            <small style="color: #666; font-size: 11px;">Basic VIN prefix will be generated. Complete the full 17-character VIN if available.</small>
                        </div>
                        
                        <div style="margin-bottom: 10px;">
                            <label style="display: block; font-weight: bold; margin-bottom: 5px;">Primary Use:</label>
                            <select id="vehicleUse" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                                <option value="Commute">Commute to Work</option>
                                <option value="Pleasure">Pleasure/Personal</option>
                                <option value="Business">Business</option>
                                <option value="Farm">Farm Use</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        
                        <div style="margin-bottom: 10px;">
                            <label style="display: block; font-weight: bold; margin-bottom: 5px;">Annual Miles:</label>
                            <select id="vehicleMiles" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                                <option value="5000">Under 5,000</option>
                                <option value="7500">5,000 - 10,000</option>
                                <option value="12000" selected>10,000 - 15,000</option>
                                <option value="17500">15,000 - 20,000</option>
                                <option value="22500">20,000 - 25,000</option>
                                <option value="30000">Over 25,000</option>
                            </select>
                        </div>
                        
                        <div style="margin-bottom: 10px;">
                            <label style="display: block; font-weight: bold; margin-bottom: 5px;">Ownership:</label>
                            <select id="vehicleOwnership" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                                <option value="Own">Own</option>
                                <option value="Finance">Finance</option>
                                <option value="Lease">Lease</option>
                            </select>
                        </div>
                        
                        <div style="margin-bottom: 15px;">
                            <label style="display: block; font-weight: bold; margin-bottom: 5px;">Garage:</label>
                            <select id="vehicleGarage" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                                <option value="Yes">Yes - Garaged</option>
                                <option value="No">No - Street/Driveway</option>
                                <option value="Carport">Carport</option>
                            </select>
                        </div>
                        
                        <div style="display: flex; gap: 10px; justify-content: flex-end;">
                            <button onclick="closeVehicleModal()" style="padding: 8px 16px; background: #6c757d; color: white; border: none; border-radius: 4px; cursor: pointer;">Cancel</button>
                            <button onclick="saveVehicle()" style="padding: 8px 16px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer;">Add Vehicle</button>
                        </div>
                    </div>
                </div>
            `;
            
            document.body.insertAdjacentHTML('beforeend', modalHtml);
        }

        function updateMakes() {
            const year = document.getElementById('vehicleYear').value;
            const makeSelect = document.getElementById('vehicleMake');
            const modelSelect = document.getElementById('vehicleModel');
            
            // Clear and disable model dropdown
            modelSelect.innerHTML = '<option value="">Select Model...</option>';
            modelSelect.disabled = true;
            
            if (year && vehicleData[year]) {
                makeSelect.innerHTML = '<option value="">Select Make...</option>';
                Object.keys(vehicleData[year]).forEach(make => {
                    makeSelect.innerHTML += `<option value="${make}">${make}</option>`;
                });
                makeSelect.disabled = false;
            } else if (year) {
                // For years not in our data, show common makes
                const commonMakes = ['Toyota', 'Honda', 'Ford', 'Chevrolet', 'Nissan', 'BMW', 'Mercedes-Benz', 'Audi', 'Hyundai', 'Kia', 'Subaru', 'Mazda', 'Volkswagen', 'Jeep', 'Ram', 'GMC', 'Cadillac', 'Lexus', 'Acura', 'Infiniti', 'Other'];
                makeSelect.innerHTML = '<option value="">Select Make...</option>';
                commonMakes.forEach(make => {
                    makeSelect.innerHTML += `<option value="${make}">${make}</option>`;
                });
                makeSelect.disabled = false;
            } else {
                makeSelect.innerHTML = '<option value="">Select Make...</option>';
                makeSelect.disabled = true;
            }
        }

        function updateModels() {
            const year = document.getElementById('vehicleYear').value;
            const make = document.getElementById('vehicleMake').value;
            const modelSelect = document.getElementById('vehicleModel');
            
            if (year && make && vehicleData[year] && vehicleData[year][make]) {
                modelSelect.innerHTML = '<option value="">Select Model...</option>';
                vehicleData[year][make].forEach(model => {
                    modelSelect.innerHTML += `<option value="${model}">${model}</option>`;
                });
                modelSelect.disabled = false;
            } else if (year && make) {
                // Allow manual entry for models not in our data
                modelSelect.innerHTML = '<option value="">Select Model...</option><option value="Other">Other/Enter Manually</option>';
                modelSelect.disabled = false;
            } else {
                modelSelect.innerHTML = '<option value="">Select Model...</option>';
                modelSelect.disabled = true;
            }
        }

        function generateVINPrefix() {
            const year = document.getElementById('vehicleYear').value;
            const make = document.getElementById('vehicleMake').value;
            const model = document.getElementById('vehicleModel').value;
            const vinInput = document.getElementById('vehicleVIN');
            
            if (year && make && model && model !== 'Other') {
                // Get WMI code for the manufacturer
                const wmiOptions = wmiCodes[make];
                if (wmiOptions) {
                    // Use the first WMI code for simplicity
                    const wmi = wmiOptions[0];
                    
                    // Generate basic VIN structure: WMI (3) + VDS (6) + VIS (8)
                    // For basic VIN, we'll generate WMI + basic pattern
                    
                    // Get year code (10th position in VIN)
                    const yearCode = getVINYearCode(year);
                    
                    // Generate a basic VIN prefix with placeholder for completion
                    // Format: WMI + basic identifier + year code + placeholder
                    const vinPrefix = wmi + 'XXXXX' + yearCode + 'XXXXXXX';
                    
                    vinInput.value = vinPrefix;
                    vinInput.placeholder = 'Complete the remaining characters if full VIN is available';
                } else {
                    vinInput.value = '';
                    vinInput.placeholder = 'Enter complete VIN (17 characters)';
                }
            } else {
                vinInput.value = '';
                vinInput.placeholder = 'Auto-generated prefix + complete as needed';
            }
        }

        function getVINYearCode(year) {
            // VIN year codes for 10th position
            const yearCodes = {
                '2015': 'F', '2016': 'G', '2017': 'H', '2018': 'J', '2019': 'K',
                '2020': 'L', '2021': 'M', '2022': 'N', '2023': 'P', '2024': 'R'
            };
            return yearCodes[year] || 'X';
        }

        function closeVehicleModal() {
            const modal = document.getElementById('vehicleModal');
            if (modal) {
                modal.remove();
            }
        }

        async function saveVehicle() {
            const year = document.getElementById('vehicleYear').value;
            const make = document.getElementById('vehicleMake').value;
            let model = document.getElementById('vehicleModel').value;
            const vin = document.getElementById('vehicleVIN').value;
            const primaryUse = document.getElementById('vehicleUse').value;
            const annualMiles = document.getElementById('vehicleMiles').value;
            const ownership = document.getElementById('vehicleOwnership').value;
            const garage = document.getElementById('vehicleGarage').value;
            
            if (!year || !make || !model) {
                alert('Please select Year, Make, and Model');
                return;
            }
            
            // Handle manual model entry
            if (model === 'Other') {
                model = prompt('Enter vehicle model:');
                if (!model) return;
            }
            
            // Validate VIN if provided (should be 17 characters if complete)
            if (vin && vin.length > 0 && vin.length !== 17) {
                const proceed = confirm('VIN should be 17 characters. Continue anyway?');
                if (!proceed) return;
            }
            
            const data = {
                year: parseInt(year),
                make: make,
                model: model,
                vin: vin || '',
                primary_use: primaryUse,
                annual_miles: parseInt(annualMiles),
                ownership: ownership,
                garage: garage
            };
            
            try {
                const response = await fetch(`/agent/lead/{{ $lead->id }}/vehicle`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    closeVehicleModal();
                    alert('Vehicle added successfully!');
                    location.reload();
                } else {
                    alert('Error: ' + result.error);
                }
            } catch (error) {
                alert('Error adding vehicle: ' + error.message);
            }
        }
        
                function addDriver() {
            showDriverModal();
        }
        
        // Smart dropdown compatibility functions
        // These functions handle imported lead data that may not match our dropdown options
        // - Exact match: Maps imported values to dropdown options when possible
        // - Partial match: Handles common variations (e.g., "Male" -> "M")
        // - Other option: Preserves imported data using "Other" + text input
        // - Display indicators: Shows "(imported data)" for non-standard values
        function smartSelectOption(selectElement, value, otherInputId = null) {
            if (!value) return;
            
            // Try to find exact match first
            const exactMatch = Array.from(selectElement.options).find(option => 
                option.value.toLowerCase() === value.toLowerCase()
            );
            
            if (exactMatch) {
                selectElement.value = exactMatch.value;
                return;
            }
            
            // Try partial match for common variations
            const partialMatch = Array.from(selectElement.options).find(option => 
                option.text.toLowerCase().includes(value.toLowerCase()) ||
                value.toLowerCase().includes(option.text.toLowerCase())
            );
            
            if (partialMatch) {
                selectElement.value = partialMatch.value;
                return;
            }
            
            // If no match found, select "Other" and populate text input
            const otherOption = Array.from(selectElement.options).find(option => 
                option.value === 'Other'
            );
            
            if (otherOption) {
                selectElement.value = 'Other';
                if (otherInputId) {
                    const otherInput = document.getElementById(otherInputId);
                    if (otherInput) {
                        otherInput.value = value;
                        otherInput.style.display = 'block';
                    }
                }
            }
        }
        
        function handleOtherSelection(selectElement, otherInputId) {
            const otherInput = document.getElementById(otherInputId);
            if (otherInput) {
                if (selectElement.value === 'Other') {
                    otherInput.style.display = 'block';
                    otherInput.focus();
                } else {
                    otherInput.style.display = 'none';
                    otherInput.value = '';
                }
            }
        }
        
        function getSelectedValue(selectElement, otherInputId = null) {
            if (selectElement.value === 'Other' && otherInputId) {
                const otherInput = document.getElementById(otherInputId);
                return otherInput ? otherInput.value : selectElement.value;
            }
            return selectElement.value;
        }

        function showDriverModal() {
            const modalHtml = `
                <div id="driverModal" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 10000; display: flex; justify-content: center; align-items: center;">
                    <div style="background: white; padding: 30px; border-radius: 12px; width: 90%; max-width: 600px; max-height: 90%; overflow-y: auto; box-shadow: 0 10px 30px rgba(0,0,0,0.3);">
                        <h3 style="margin-top: 0; color: #e65100; border-bottom: 2px solid #ff9800; padding-bottom: 10px;">👤 Add New Driver</h3>
                        
                        <div style="margin-bottom: 15px;">
                            <label style="display: block; font-weight: bold; margin-bottom: 5px;">First Name *:</label>
                            <input type="text" id="driverFirstName" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;" required>
                        </div>
                        
                        <div style="margin-bottom: 15px;">
                            <label style="display: block; font-weight: bold; margin-bottom: 5px;">Last Name *:</label>
                            <input type="text" id="driverLastName" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;" required>
                        </div>
                        
                        <div style="margin-bottom: 15px;">
                            <label style="display: block; font-weight: bold; margin-bottom: 5px;">Date of Birth (MM/DD/YYYY) *:</label>
                            <input type="date" id="driverBirthDate" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;" required>
                        </div>
                        
                        <div style="margin-bottom: 15px;">
                            <label style="display: block; font-weight: bold; margin-bottom: 5px;">Gender:</label>
                            <select id="driverGender" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;" onchange="handleOtherSelection(this, 'driverGenderOther')">
                                <option value="M">Male</option>
                                <option value="F">Female</option>
                                <option value="Other">Other (specify below)</option>
                            </select>
                            <input type="text" id="driverGenderOther" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; margin-top: 5px; display: none;" placeholder="Specify gender...">
                        </div>
                        
                        <div style="margin-bottom: 15px;">
                            <label style="display: block; font-weight: bold; margin-bottom: 5px;">Marital Status:</label>
                            <select id="driverMaritalStatus" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;" onchange="handleOtherSelection(this, 'driverMaritalStatusOther')">
                                <option value="Single">Single</option>
                                <option value="Married">Married</option>
                                <option value="Divorced">Divorced</option>
                                <option value="Widowed">Widowed</option>
                                <option value="Separated">Separated</option>
                                <option value="Other">Other (specify below)</option>
                            </select>
                            <input type="text" id="driverMaritalStatusOther" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; margin-top: 5px; display: none;" placeholder="Specify marital status...">
                        </div>
                        
                        <div style="margin-bottom: 15px;">
                            <label style="display: block; font-weight: bold; margin-bottom: 5px;">State Licensed:</label>
                            <select id="driverLicenseState" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
                                <option value="">Select State...</option>
                                <option value="AL">AL</option><option value="AK">AK</option><option value="AZ">AZ</option><option value="AR">AR</option>
                                <option value="CA" selected>CA</option><option value="CO">CO</option><option value="CT">CT</option><option value="DE">DE</option>
                                <option value="FL">FL</option><option value="GA">GA</option><option value="HI">HI</option><option value="ID">ID</option>
                                <option value="IL">IL</option><option value="IN">IN</option><option value="IA">IA</option><option value="KS">KS</option>
                                <option value="KY">KY</option><option value="LA">LA</option><option value="ME">ME</option><option value="MD">MD</option>
                                <option value="MA">MA</option><option value="MI">MI</option><option value="MN">MN</option><option value="MS">MS</option>
                                <option value="MO">MO</option><option value="MT">MT</option><option value="NE">NE</option><option value="NV">NV</option>
                                <option value="NH">NH</option><option value="NJ">NJ</option><option value="NM">NM</option><option value="NY">NY</option>
                                <option value="NC">NC</option><option value="ND">ND</option><option value="OH">OH</option><option value="OK">OK</option>
                                <option value="OR">OR</option><option value="PA">PA</option><option value="RI">RI</option><option value="SC">SC</option>
                                <option value="SD">SD</option><option value="TN">TN</option><option value="TX">TX</option><option value="UT">UT</option>
                                <option value="VT">VT</option><option value="VA">VA</option><option value="WA">WA</option><option value="WV">WV</option>
                                <option value="WI">WI</option><option value="WY">WY</option>
                            </select>
                        </div>
                        
                        <div style="margin-bottom: 15px;">
                            <label style="display: block; font-weight: bold; margin-bottom: 5px;">License Status:</label>
                            <select id="driverLicenseStatus" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;" onchange="handleOtherSelection(this, 'driverLicenseStatusOther')">
                                <option value="Valid">Valid</option>
                                <option value="Suspended">Suspended</option>
                                <option value="Expired">Expired</option>
                                <option value="Revoked">Revoked</option>
                                <option value="Permit">Permit</option>
                                <option value="Other">Other (specify below)</option>
                            </select>
                            <input type="text" id="driverLicenseStatusOther" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; margin-top: 5px; display: none;" placeholder="Specify license status...">
                        </div>
                        
                        <div style="margin-bottom: 20px;">
                            <label style="display: block; font-weight: bold; margin-bottom: 5px;">Years Licensed:</label>
                            <select id="driverYearsLicensed" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;" onchange="handleOtherSelection(this, 'driverYearsLicensedOther')">
                                <option value="1">1 year</option>
                                <option value="2">2 years</option>
                                <option value="3">3 years</option>
                                <option value="4">4 years</option>
                                <option value="5" selected>5 years</option>
                                <option value="6">6 years</option>
                                <option value="7">7 years</option>
                                <option value="8">8 years</option>
                                <option value="9">9 years</option>
                                <option value="10">10+ years</option>
                                <option value="Other">Other (specify below)</option>
                            </select>
                            <input type="text" id="driverYearsLicensedOther" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; margin-top: 5px; display: none;" placeholder="Specify years licensed...">
                        </div>
                        
                        <div style="display: flex; gap: 10px; justify-content: flex-end;">
                            <button onclick="closeDriverModal()" style="padding: 10px 20px; background: #6c757d; color: white; border: none; border-radius: 4px; cursor: pointer;">Cancel</button>
                            <button onclick="saveDriver()" style="padding: 10px 20px; background: #ff9800; color: white; border: none; border-radius: 4px; cursor: pointer;">Add Driver</button>
                        </div>
                    </div>
                </div>
            `;
            document.body.insertAdjacentHTML('beforeend', modalHtml);
        }
        
        function closeDriverModal() {
            const modal = document.getElementById('driverModal');
            if (modal) {
                modal.remove();
            }
        }
        
        async function saveDriver() {
            const firstName = document.getElementById('driverFirstName').value;
            const lastName = document.getElementById('driverLastName').value;
            const birthDate = document.getElementById('driverBirthDate').value;
            const gender = getSelectedValue(document.getElementById('driverGender'), 'driverGenderOther');
            const maritalStatus = getSelectedValue(document.getElementById('driverMaritalStatus'), 'driverMaritalStatusOther');
            const licenseState = document.getElementById('driverLicenseState').value;
            const licenseStatus = getSelectedValue(document.getElementById('driverLicenseStatus'), 'driverLicenseStatusOther');
            const yearsLicensed = getSelectedValue(document.getElementById('driverYearsLicensed'), 'driverYearsLicensedOther');
            
            if (!firstName || !lastName || !birthDate) {
                alert('Please fill in all required fields (First Name, Last Name, Date of Birth)');
                return;
            }
            
            // Convert date from YYYY-MM-DD to MM/DD/YYYY for display
            const dateObj = new Date(birthDate);
            const formattedDate = (dateObj.getMonth() + 1).toString().padStart(2, '0') + '/' + 
                                dateObj.getDate().toString().padStart(2, '0') + '/' + 
                                dateObj.getFullYear();
            
            const data = {
                first_name: firstName,
                last_name: lastName,
                birth_date: formattedDate,
                gender: gender,
                marital_status: maritalStatus,
                license_state: licenseState,
                license_status: licenseStatus,
                years_licensed: parseInt(yearsLicensed),
                violations: [],
                accidents: []
            };
            
            try {
                const response = await fetch(`/agent/lead/{{ $lead->id }}/driver`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    closeDriverModal();
                    alert('Driver added successfully!');
                    location.reload();
                } else {
                    alert('Error: ' + result.error);
                }
            } catch (error) {
                console.error('Driver addition error:', error);
                alert('Error adding driver: ' + error.message);
            }
        }

        // Save insurance information
        async function saveInsurance() {
            const data = {
                insurance_company: document.getElementById('insurance_company').value,
                coverage_type: document.getElementById('coverage_type').value,
                expiration_date: document.getElementById('expiration_date').value,
                insured_since: document.getElementById('insured_since').value
            };
            
            try {
                const response = await fetch(`/agent/lead/{{ $lead->id }}/insurance`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    alert('Insurance information updated successfully!');
                    location.reload();
                } else {
                    alert('Error: ' + result.error);
                }
            } catch (error) {
                alert('Error updating insurance: ' + error.message);
            }
        }

        // Save all lead data (comprehensive save)
        async function saveAllLeadData() {
            const saveBtn = document.querySelector('.save-lead-btn');
            const originalText = saveBtn.innerHTML;
            saveBtn.innerHTML = '⏳ Saving...';
            saveBtn.disabled = true;
            
            try {
                // Get all form data including qualification answers
                const qualificationData = getFormData();
                
                // Get contact information
                const contactData = {
                    phone: document.getElementById('contact_phone')?.value || '{{ $lead->phone }}',
                    email: document.getElementById('contact_email')?.value || '{{ $lead->email }}',
                    address: document.getElementById('contact_address')?.value || '{{ $lead->address }}',
                    city: document.getElementById('contact_city')?.value || '{{ $lead->city }}',
                    state: document.getElementById('contact_state')?.value || '{{ $lead->state }}',
                    zip_code: document.getElementById('contact_zip_code')?.value || '{{ $lead->zip_code }}'
                };
                
                // Get insurance information
                const insuranceData = {
                    insurance_company: document.getElementById('insurance_company')?.value || '{{ $lead->current_policy['insurance_company'] ?? '' }}',
                    coverage_type: document.getElementById('coverage_type')?.value || '{{ $lead->current_policy['coverage_type'] ?? '' }}',
                    expiration_date: document.getElementById('expiration_date')?.value || '{{ $lead->current_policy['expiration_date'] ?? '' }}',
                    insured_since: document.getElementById('insured_since')?.value || '{{ $lead->current_policy['insured_since'] ?? '' }}'
                };
                
                // Combine all data
                const allData = {
                    qualification: qualificationData,
                    contact: contactData,
                    insurance: insuranceData
                };
                
                const response = await fetch(`/agent/lead/{{ $lead->id }}/save-all`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(allData)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    saveBtn.innerHTML = '✅ Saved!';
                    setTimeout(() => {
                        saveBtn.innerHTML = originalText;
                        saveBtn.disabled = false;
                    }, 2000);
                } else {
                    throw new Error(result.error || 'Failed to save');
                }
            } catch (error) {
                console.error('Error saving lead data:', error);
                alert('Error saving lead data: ' + error.message);
                saveBtn.innerHTML = originalText;
                saveBtn.disabled = false;
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
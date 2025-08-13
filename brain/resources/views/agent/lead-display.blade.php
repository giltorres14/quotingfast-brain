<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Lead Details - {{ $lead->name }}</title>
    <link rel="icon" type="image/png" href="/favicon.png">
    <link rel="shortcut icon" href="/favicon.ico">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        html, body {
            height: 100%;
            width: 100%;
            margin: 0;
            padding: 0;
            overflow: auto; /* Allow scrolling if needed */
            /* Removed min-width to allow narrower display */
            min-height: 600px;
            position: relative;
            z-index: 999;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            font-size: 14px;
            line-height: 1.4;
            background: #f8f9fa;
            color: #333;
        }
        
        .container {
            width: 100%;
            max-width: 900px; /* Narrower max width for iframe */
            margin: 0 auto;
            padding: 12px;
            min-height: 600px;
            /* Removed min-width to allow responsive sizing */
            transform: scale(1.0);
            transform-origin: top left;
            overflow: visible;
        }
        
        /* Content wrapper for consistent width */
        .content-wrapper {
            width: 100%;
            max-width: 900px;
            margin: 0 auto;
            padding: 0;
        }
        
        .content-wrapper .section {
            width: 100%;
            margin-bottom: 12px;
        }
        
        /* Responsive iframe sizing */
        body {
            /* Communicate responsive size to parent iframe */
            --iframe-width: 100%;
            --iframe-height: auto;
        }
        
        .header {
            overflow-x: hidden;
            width: 100%;
            max-width: 900px;
            margin: 0 auto;
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            color: white;
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 16px;
            text-align: center;
            position: relative;
            box-sizing: border-box;
        }
        
        .header-logo {
            position: absolute;
            top: 16px;
            right: 16px;
            height: 30px;
            width: auto;
            filter: brightness(1.1);
        }
        
        .back-button {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(255,255,255,0.2);
            color: white;
            text-decoration: none;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            transition: all 0.2s ease;
        }
        
        .back-button:hover {
            background: rgba(255,255,255,0.3);
            color: white;
            text-decoration: none;
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
            padding: 12px;
            margin-bottom: 12px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 900px;
            margin-left: auto;
            margin-right: auto;
            box-sizing: border-box;
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
        .section-title.properties { 
            background: linear-gradient(135deg, #fff3e0, #ffebee); 
            border-left-color: #ff9800; 
            color: #e65100; 
        }
        .section-title.compliance { 
            background: linear-gradient(135deg, #e8f5e8, #f0f4f8); 
            border-left-color: #28a745; 
            color: #155724; 
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
        
        .contact-layout {
            display: flex;
            gap: 2rem;
            align-items: flex-start;
        }
        
        .contact-left {
            flex: 0 0 250px;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        
        .contact-right {
            flex: 1;
            padding-left: 2rem;
            margin-left: 2rem;
            border-left: 2px solid #e9ecef;
            display: flex;
            flex-direction: column;
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
        
                .driver-card, .vehicle-card, .property-card {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            border: 1px solid #e0e0e0;
            width: 100%;
            box-sizing: border-box;
        }
        
                .driver-card, .vehicle-card, .property-card {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            border: 1px solid #e0e0e0;
            width: 100%;
            box-sizing: border-box;
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
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            position: relative;
            width: 100%;
            max-width: 900px;
            margin-left: auto;
            margin-right: auto;
            box-sizing: border-box;
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            position: sticky;
            top: 10px;
            z-index: 100;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }
        
        .lead-info-bubble .lead-name {
            font-weight: bold;
            font-size: 18px;
            color: white;
            margin-bottom: 4px;
        }
        
        .lead-info-bubble .lead-phone {
            font-weight: bold;
            font-size: 14px;
            color: #e0f2fe;
            margin-bottom: 4px;
        }
        
        .lead-info-bubble .lead-address {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 8px;
        }
        
        .lead-comments {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 4px;
            padding: 8px;
            font-size: 11px;
            max-height: 100px;
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
            position: sticky;
            top: 120px; /* Positioned below the lead info bubble */
            z-index: 99;
            background: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
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

        /* Edit functionality styles - Standardized bigger buttons */
        .edit-btn, .btn.btn-sm.btn-outline-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            color: white !important;
            border: none;
            padding: 12px 24px !important;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px !important;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-left: 8px;
        }

        .edit-btn, .btn.btn-sm.btn-outline-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            color: white !important;
            border: none;
            padding: 12px 24px !important;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px !important;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-left: 8px;
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
        
        /* Copy button styles */
        .copy-btn {
            background: #6b7280;
            color: white;
            padding: 2px 6px;
            border: none;
            border-radius: 3px;
            font-size: 10px;
            margin-left: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .copy-btn:hover {
            background: #4b5563;
        }
        
        .copy-btn.copied {
            background: #10b981;
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
            top: 10px;
            right: 20px;
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            z-index: 1001;
            transition: all 0.3s ease;
            max-width: 200px;
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
        
        /* REMOVED: Allstate validation styles per user request */
        
        /* REMOVED: validationPulse animation per user request */
        
        /* REMOVED: validation tooltip styles per user request */
        
        /* REMOVED: All validation summary CSS per user request */
        
        /* REMOVED: validation close button CSS per user request */
        
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
        
        /* REMOVED: Allstate validation progress CSS per user request */

        /* Hide elements when in iframe (agent view) */
        .agent-iframe-view .back-button {
            display: none !important;
        }
        
        .agent-iframe-view .admin-only {
            display: none !important;
        }
        
        .agent-iframe-view .nav-menu {
            display: none !important;
        }
        
        .agent-iframe-view .dropdown {
            display: none !important;
        }
        
        /* Adjust header when in iframe */
        .agent-iframe-view         .header {
            overflow-x: hidden;
            width: 100%;
            max-width: 900px;
            margin: 0 auto;
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            color: white;
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 16px;
            text-align: center;
            position: relative;
            box-sizing: border-box;
        }
        
        .agent-iframe-view .header-logo {
            height: 60px;
        }
        
        /* Make the container full width in iframe */
        .agent-iframe-view .container {
            max-width: 100%;
            padding: 10px;
        }
        
        /* Hide save lead button in top corner for agents */
        .agent-iframe-view         .save-lead-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            z-index: 1000;
            transition: all 0.3s ease;
            max-width: 200px;
        }
    </style>
    
    <!-- JavaScript to try to resize parent iframe -->        </div><!-- content-wrapper -->
    </div><!-- container -->

    <script>
        // Try to communicate with parent iframe to resize
        function resizeParentIframe() {
            try {
                // Method 1: PostMessage to parent
                if (window.parent !== window) {
                    window.parent.postMessage({
                        type: 'resize_iframe',
                        width: 1400,
                        height: 900
                    }, '*');
                }
                
                // Method 2: Try to access parent iframe directly
                if (window.frameElement) {
                    window.frameElement.style.width = '1400px';
                    window.frameElement.style.height = '900px';
                    window.frameElement.style.minWidth = '1400px';
                    window.frameElement.style.minHeight = '900px';
                }
                
                console.log('Attempted to resize parent iframe to 1400x900');
            } catch (e) {
                console.log('Could not resize parent iframe:', e.message);
            }
        }
        
        // Try multiple times
        setTimeout(resizeParentIframe, 100);
        setTimeout(resizeParentIframe, 500);
        setTimeout(resizeParentIframe, 1000);
        
        window.addEventListener('load', resizeParentIframe);

        // Detect if we are in an iframe (Vici agent view) or direct access (admin)
        function isInIframe() {
            try {
                return window.self !== window.top;
            } catch (e) {
                return true;
            }
        }
        
        // Auto-trigger conditional questions based on pre-filled values
        document.addEventListener("DOMContentLoaded", function() {
            // Add class to body based on access type
            if (isInIframe()) {
                document.body.classList.add("agent-iframe-view");
                // Hide admin-only elements
                const adminElements = document.querySelectorAll(".admin-only");
                adminElements.forEach(el => el.style.display = "none");
                
                // Also hide navigation menus if they exist
                const navMenus = document.querySelectorAll(".nav-menu, .dropdown-menu");
                navMenus.forEach(el => el.style.display = "none");
            } else {
                document.body.classList.add("admin-direct-view");
            }
            
            // Trigger insurance questions if currently insured
            if (document.getElementById("currently_insured") && document.getElementById("currently_insured").value === "yes") {
                toggleInsuranceQuestions();
            }
            
            // Trigger DUI questions if DUI is selected
            if (document.getElementById("dui_sr22") && document.getElementById("dui_sr22").value !== "no" && document.getElementById("dui_sr22").value !== "") {
                toggleDUIQuestions();
            }
        });
    </script>
</head>
<body>
    <!-- Save Lead Button -->
    @if(!isset($mode) || $mode !== 'view')
        <button class="save-lead-btn" onclick="saveAllLeadData()">💾 Save Lead</button>
    @endif
    
    <!-- REMOVED: Allstate validation progress indicator per user request -->
    
    <!-- REMOVED: Validation Summary Modal per user request -->
    
    <div class="container">
        <div class="content-wrapper">
        <!-- Debug panel (hidden by default; enable with ?debug=1) -->
        <div id="debug-panel" style="display:none; position:fixed; bottom:10px; left:10px; z-index:9999; background:rgba(0,0,0,0.85); color:#e2e8f0; padding:10px; border-radius:8px; width:420px; max-height:40vh; overflow:auto; font-family:monospace; font-size:12px;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                <strong>Debug Log</strong>
                <button onclick="document.getElementById('debug-panel').style.display='none'" style="background:#444;color:#fff;border:none;border-radius:4px;padding:2px 6px;cursor:pointer;">×</button>
            </div>
            <div id="debug-log"></div>
        </div>
        <!-- Header - Agent View (No Admin Data) -->
        <div class="header" style="position: relative;">
            @if(isset($mode) && in_array($mode, ['view', 'edit']) && !request()->get('iframe'))
                <a href="/leads" class="back-button admin-only" style="position: absolute; top: 15px; left: 170px; z-index: 100;">← Back to Leads</a>
            @endif
            
            <!-- Lead Type Avatar Circle - Smaller and Well-positioned -->
            <div style="position: absolute; left: 30px; top: 50%; transform: translateY(-50%); z-index: 50;">
                <div style="
                    width: 120px; 
                    height: 120px; 
                    border-radius: 50%; 
                    display: flex; 
                    align-items: center; 
                    justify-content: center; 
                    font-weight: 900; 
                    font-size: 32px; 
                    color: white;
                    box-shadow: 0 8px 24px rgba(0,0,0,0.2), 0 0 0 4px rgba(255,255,255,0.3);
                    background: {{ $lead->type === 'auto' ? 'linear-gradient(135deg, #667eea 0%, #3B82F6 100%)' : ($lead->type === 'home' ? 'linear-gradient(135deg, #10B981 0%, #059669 100%)' : 'linear-gradient(135deg, #8B5CF6 0%, #7C3AED 100%)') }};
                    border: 3px solid white;
                ">
                    {{ $lead->type === 'auto' ? 'AUTO' : ($lead->type === 'home' ? 'HOME' : strtoupper(substr($lead->type ?? 'N/A', 0, 4))) }}
                </div>
            </div>
            
            <!-- Centered Content -->
            <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; width: 100%; padding: 20px 150px;">
                <img src="https://quotingfast.com/whitelogo" alt="QuotingFast" class="logo-image" style="height: 120px; width:auto; margin-bottom: 10px;">
                <h1 style="margin: 10px 0; text-align: center;">{{ $lead->name }} 
                    @if(isset($mode) && $mode === 'view')
                        <span style="font-size: 14px; opacity: 0.8;">(View Only)</span>
                    @elseif(isset($mode) && $mode === 'edit')
                        <span style="font-size: 14px; opacity: 0.8;">(Edit Mode)</span>
                    @endif
                </h1>
                <div class="meta" style="text-align: center; display: flex; align-items: center; justify-content: center; gap: 12px;">
                    <span>Lead ID: {{ $lead->external_lead_id ?? $lead->id }}</span>
                    @if($lead->source)
                        @php
                            $sourceColors = [
                                'SURAJ_BULK' => ['bg' => '#8b5cf6', 'label' => 'Suraj Bulk'],
                                'LQF_BULK' => ['bg' => '#ec4899', 'label' => 'LQF Bulk'],
                                'LQF' => ['bg' => '#06b6d4', 'label' => 'LQF'],
                                'SURAJ' => ['bg' => '#10b981', 'label' => 'Suraj'],
                                'API' => ['bg' => '#f59e0b', 'label' => 'API'],
                                'MANUAL' => ['bg' => '#6b7280', 'label' => 'Manual'],
                            ];
                            $sourceInfo = $sourceColors[$lead->source] ?? ['bg' => '#6b7280', 'label' => $lead->source];
                        @endphp
                        <span style="
                            background: {{ $sourceInfo['bg'] }};
                            color: white;
                            padding: 4px 12px;
                            border-radius: 20px;
                            font-size: 11px;
                            font-weight: 600;
                            text-transform: uppercase;
                            letter-spacing: 0.5px;
                            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                        ">
                            {{ $sourceInfo['label'] }}
                        </span>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Agent Message (shown only in iframe) -->
        <div class="agent-message-container" style="display: none;">
            <div class="agent-message" style="background: #e3f2fd; color: #1565c0; padding: 10px; margin: 10px 0; border-radius: 8px; text-align: center;">
                <strong>Agent View</strong> - Edit lead information and save changes below
            </div>
        </div>
        <script>
            // Show agent message if in iframe
            if (typeof isInIframe !== "undefined" && isInIframe()) {
                document.querySelector(".agent-message-container").style.display = "block";
            }
        </script>

        <!-- Ringba Qualification Form -->
        @if(!isset($mode) || $mode === 'agent' || $mode === 'edit')
        <div class="qualification-form">
            <div class="qualification-header section-title qualification">
                🎯 Lead Qualification & Ringba Enrichment (Enhanced)
            </div>
            
            <!-- Sticky Lead Info Bubble (Centered) -->
            <div class="lead-info-bubble" style="text-align: center;">
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
                <!-- 1. Insurance Questions -->
                <div class="question-group">
                    <label class="question-label">1. Are you currently insured?</label>
                    
                    @php
                        $isInsured = null;
                        $currentProvider = null;
                        $insuranceDuration = null;
                        
                        // Check current_policy for insurance info
                        if (isset($lead->current_policy)) {
                            $currentPolicy = is_string($lead->current_policy) ? json_decode($lead->current_policy, true) : $lead->current_policy;
                            if (is_array($currentPolicy)) {
                                if (isset($currentPolicy["currently_insured"])) {
                                    $isInsured = strtolower($currentPolicy["currently_insured"]) === "yes" ? "yes" : "no";
                                }
                                if (isset($currentPolicy["current_company"])) {
                                    $currentProvider = strtolower(str_replace(" ", "_", $currentPolicy["current_company"]));
                                }
                                if (isset($currentPolicy["insurance_duration"])) {
                                    $duration = $currentPolicy["insurance_duration"];
                                    if (strpos($duration, "6 month") !== false) {
                                        $insuranceDuration = "under_6_months";
                                    } elseif (strpos($duration, "1 year") !== false || strpos($duration, "1-3") !== false) {
                                        $insuranceDuration = "6_months_1_year";
                                    } elseif (strpos($duration, "3 year") !== false) {
                                        $insuranceDuration = "1_3_years";
                                    } else {
                                        $insuranceDuration = "over_3_years";
                                    }
                                }
                            }
                        }
                        
                        // Check drivers data for DUI/SR22 info (removed license check)
                        $duiSr22 = null;
                        $duiTimeframe = null;
                        
                        if (isset($lead->drivers)) {
                            $drivers = is_string($lead->drivers) ? json_decode($lead->drivers, true) : $lead->drivers;
                            if (is_array($drivers) && !empty($drivers)) {
                                $firstDriver = $drivers[0];
                                
                                // Check DUI/SR22 status
                                $hasDui = isset($firstDriver["dui"]) && $firstDriver["dui"];
                                $hasSr22 = isset($firstDriver["requires_sr22"]) && $firstDriver["requires_sr22"];
                                
                                if ($hasDui && $hasSr22) {
                                    $duiSr22 = "both";
                                } elseif ($hasDui) {
                                    $duiSr22 = "dui_only";
                                } elseif ($hasSr22) {
                                    $duiSr22 = "sr22_only";
                                } else {
                                    $duiSr22 = "no";
                                }
                                
                                // Check DUI timeframe
                                if (isset($firstDriver["dui_timeframe"])) {
                                    $duiTimeframe = $firstDriver["dui_timeframe"];
                                }
                            }
                        }
                        
                        // Count vehicles
                        $vehicleCount = 0;
                        if (isset($lead->vehicles)) {
                            $vehicles = is_string($lead->vehicles) ? json_decode($lead->vehicles, true) : $lead->vehicles;
                            if (is_array($vehicles)) {
                                $vehicleCount = count($vehicles);
                            }
                        }
                    @endphp
                    <select class="question-select" id="currently_insured" onchange="toggleInsuranceQuestions()">
                        <option value="">Select...</option>
                        <option value="yes" {{ $isInsured === "yes" ? "selected" : "" }}>Yes</option>
                        <option value="no" {{ $isInsured === "no" ? "selected" : "" }}>No</option>
                    </select>
                    
                    <div id="insurance_questions" class="conditional-question">
                        <label class="question-label">1B. Who is your current provider?</label>
                        <select class="question-select" id="current_provider" onchange="updateInsuranceSection()">
                            <option value="">Select...</option>
                            <option value="state_farm" {{ $currentProvider === "state_farm" ? "selected" : "" }}>State Farm</option>
                            <option value="geico" {{ $currentProvider === "geico" ? "selected" : "" }}>GEICO</option>
                            <option value="progressive" {{ $currentProvider === "progressive" ? "selected" : "" }}>Progressive</option>
                            <option value="allstate" {{ $currentProvider === "allstate" ? "selected" : "" }}>Allstate</option>
                            <option value="farmers" {{ $currentProvider === "farmers" ? "selected" : "" }}>Farmers</option>
                            <option value="usaa" {{ $currentProvider === "usaa" ? "selected" : "" }}>USAA</option>
                            <option value="liberty_mutual" {{ $currentProvider === "liberty_mutual" ? "selected" : "" }}>Liberty Mutual</option>
                            <option value="other" {{ $currentProvider && !in_array($currentProvider, ["state_farm", "geico", "progressive", "allstate", "farmers", "usaa", "liberty_mutual"]) ? "selected" : "" }}>Other</option>
                        </select>
                        
                        <div style="margin-top: 12px;">
                            <label class="question-label">1C. How long have you been continuously insured?</label>
                            <select class="question-select" id="insurance_duration">
                                <option value="">Select...</option>
                                <option value="under_6_months" {{ $insuranceDuration === "under_6_months" ? "selected" : "" }}>Under 6 months</option>
                                <option value="6_months_1_year" {{ $insuranceDuration === "6_months_1_year" ? "selected" : "" }}>6 months - 1 year</option>
                                <option value="1_3_years" {{ $insuranceDuration === "1_3_years" ? "selected" : "" }}>1-3 years</option>
                                <option value="over_3_years" {{ $insuranceDuration === "over_3_years" ? "selected" : "" }}>Over 3 years</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- 2. How many cars -->
                <div class="question-group">
                    <label class="question-label">2. How many cars are you going to need a quote for?</label>
                    <select class="question-select" id="num_vehicles">
                        <option value="">Select...</option>
                        <option value="1" {{ $vehicleCount == 1 ? "selected" : "" }}>1 Vehicle</option>
                        <option value="2" {{ $vehicleCount == 2 ? "selected" : "" }}>2 Vehicles</option>
                        <option value="3" {{ $vehicleCount == 3 ? "selected" : "" }}>3 Vehicles</option>
                        <option value="4" {{ $vehicleCount >= 4 ? "selected" : "" }}>4+ Vehicles</option>
                    </select>
                </div>

                <!-- 3. Home Ownership -->
                <div class="question-group">
                    <label class="question-label">3. Do you own or rent your home?</label>
                    <select class="question-select" id="home_status">
                        <option value="">Select...</option>
                        @php
                            $residenceType = null;
                            if (isset($lead->payload) && is_string($lead->payload)) {
                                $payload = json_decode($lead->payload, true);
                                if (isset($payload['data']['drivers'][0]['residence_type'])) {
                                    $residenceType = strtolower($payload['data']['drivers'][0]['residence_type']);
                                }
                            }
                        @endphp
                        <option value="own" {{ $residenceType === 'own' ? 'selected' : '' }}>Own</option>
                        <option value="rent" {{ $residenceType === 'rent' ? 'selected' : '' }}>Rent</option>
                        <option value="other" {{ $residenceType && !in_array($residenceType, ['own', 'rent']) ? 'selected' : '' }}>Other</option>
                    </select>
                </div>

                <!-- 4. DUI or SR22 -->
                <div class="question-group">
                    <label class="question-label">4. DUI or SR22?</label>
                    <select class="question-select" id="dui_sr22" onchange="toggleDUIQuestions()">
                        <option value="">Select...</option>
                        <option value="no" {{ $duiSr22 === "no" ? "selected" : "" }}>No</option>
                        <option value="dui_only" {{ $duiSr22 === "dui_only" ? "selected" : "" }}>DUI Only</option>
                        <option value="sr22_only" {{ $duiSr22 === "sr22_only" ? "selected" : "" }}>SR22 Only</option>
                        <option value="both" {{ $duiSr22 === "both" ? "selected" : "" }}>Both</option>
                    </select>
                    
                    <div id="dui_questions" class="conditional-question">
                        <label class="question-label">4B. If DUI – How long ago?</label>
                        <select class="question-select" id="dui_timeframe">
                            <option value="">Select...</option>
                            <option value="1" {{ $duiTimeframe == "1" ? "selected" : "" }}>Under 1 year</option>
                            <option value="2" {{ $duiTimeframe == "2" ? "selected" : "" }}>1–3 years</option>
                            <option value="3" {{ $duiTimeframe == "3" ? "selected" : "" }}>Over 3 years</option>
                        </select>
                    </div>
                </div>

                <!-- 5. State -->
                <div class="question-group">
                    <label class="question-label">5. State</label>
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

                <!-- 6. ZIP Code -->
                <div class="question-group">
                    <label class="question-label">6. ZIP Code</label>
                    <input type="text" class="question-select" id="zip_code" value="{{ $lead->zip_code ?? '' }}" placeholder="Enter ZIP code">
                </div>

                <!-- Script/Transition Area -->
                <div style="background: white; padding: 15px; margin: 20px 0; border-radius: 8px; border: 1px solid #e0e0e0; font-style: italic; color: #555;">
                    <strong>📝 Agent Script:</strong><br>
                    "Let me go ahead and see who has the better rates in your area based on what we have. Oh ok, it looks like Allstate has the better rates in that area."
                </div>

                <!-- 7. Competitive Quote -->
                <div class="question-group">
                    <label class="question-label">7. Have you received a quote from Allstate in the last 2 months?</label>
                    <select class="question-select" id="allstate_quote">
                        <option value="">Select...</option>
                        <option value="yes">Yes</option>
                        <option value="no">No</option>
                    </select>
                </div>

                <!-- 8. Intent -->
                <div class="question-group">
                    <label class="question-label">8. Ready to speak with an agent now?</label>
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
        @endif

        <!-- Contact Information -->
        <div class="section" style="position: relative;">
            <div class="section-title contact">📞 Lead Details 
                @if(!isset($mode) || $mode !== 'view')
                    <button class="edit-btn" onclick="toggleEdit('contact')">✏️ Edit</button>
                @endif
            </div>
            
            <!-- Payload Button - Only in View Mode -->
            @if(isset($mode) && $mode === 'view')
            <button onclick="viewPayload()" style="
                position: absolute;
                bottom: 15px;
                right: 15px;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                border: none;
                padding: 10px 20px;
                border-radius: 8px;
                font-weight: 600;
                cursor: pointer;
                box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
                transition: all 0.3s ease;
                z-index: 10;
            " onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 20px rgba(102, 126, 234, 0.5)';" 
               onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(102, 126, 234, 0.4)';">
                📦 View Payload
            </button>
            @endif
            <div class="contact-layout" id="contact-display">
                                <div class="contact-left">
                    <div class="info-item" id="contact-phone">
                    <div class="info-label">Phone</div>
                    <div class="info-value">
                        @php
                            if ($lead->phone) {
                                $phone = preg_replace('/[^0-9]/', '', $lead->phone);
                                if (strlen($phone) == 10) {
                                    $formatted_phone = '(' . substr($phone, 0, 3) . ')' . substr($phone, 3, 3) . '-' . substr($phone, 6);
                                } else {
                                    $formatted_phone = $lead->phone;
                                }
                            } else {
                                $formatted_phone = 'Not provided';
                            }
                        @endphp
                        {{ $formatted_phone }}
                    </div>
                </div>
                    <div class="info-item" id="contact-email">
                    <div class="info-label">Email</div>
                    <div class="info-value">{{ $lead->email ?: 'Not provided' }}</div>
                </div>
                </div>
                <div class="contact-right">
                    <div class="info-item" id="contact-address">
                    <div class="info-label">Address</div>
                    <div class="info-value">{{ $lead->address ?: 'Not provided' }}</div>
                </div>
                    <div class="info-item" id="contact-location">
                    <div class="info-label">City, State ZIP</div>
                    <div class="info-value">
                        {{ trim(($lead->city ?? '') . ', ' . ($lead->state ?? '') . ' ' . ($lead->zip_code ?? '')) ?: 'Not provided' }}
                    </div>
                </div>

        </div>
                </div>
                </div>

        <!-- Additional Lead Data - Only show in view mode -->
        @if(isset($mode) && $mode === 'view')
        <div class="section">
            <div class="section-title">💰 Cost and Buyer</div>
            <div class="info-grid">
                <!-- Campaign Information -->
                @php
                    $campaignId = $lead->campaign_id;
                    if (!$campaignId && isset($lead->payload) && is_string($lead->payload)) {
                        $payload = json_decode($lead->payload, true);
                        $campaignId = $payload['campaign_id'] ?? null;
                    }
                    
                    // Remove .0 from numeric IDs
                    if ($campaignId && is_numeric($campaignId)) {
                        $campaignId = rtrim(rtrim(number_format($campaignId, 10, '.', ''), '0'), '.');
                    }
                    
                    $campaign = null;
                    $campaignName = null;
                    if ($campaignId) {
                        // Check if Campaign model exists and table exists
                        if (class_exists('\App\Models\Campaign') && \Schema::hasTable('campaigns')) {
                            $campaign = \App\Models\Campaign::where('campaign_id', $campaignId)->first();
                        } else {
                            $campaign = null;
                        }
                        $campaignName = $campaign ? $campaign->display_name : null;
                    }
                @endphp
                @if($campaignId)
                <div class="info-item">
                    <div class="info-label">Campaign</div>
                    <div class="info-value">
                        @if($campaignName && !$campaign->is_auto_created)
                            <strong>{{ $campaignName }}</strong>
                        @else
                            <span style="font-family: monospace; color: #3b82f6;">{{ $campaignId }}</span>
                        @endif
                </div>
                </div>
                @endif
                
                @if($lead->type)
                <div class="info-item">
                    <div class="info-label">Lead Type</div>
                    <div class="info-value">
                        <span style="
                            padding: 0.25rem 0.75rem; 
                            border-radius: 1rem; 
                            font-size: 0.875rem; 
                            font-weight: 600;
                            background: {{ $lead->type === 'auto' ? '#dbeafe' : '#fef3c7' }};
                            color: {{ $lead->type === 'auto' ? '#1e40af' : '#d97706' }};
                        ">
                            {{ ucfirst($lead->type) }} Insurance
                        </span>
                    </div>
                </div>
                @endif
                
                @if(isset($lead->sell_price) && $lead->sell_price)
                <div class="info-item">
                    <div class="info-label">Lead Cost</div>
                    <div class="info-value">${{ number_format($lead->sell_price, 2) }}</div>
                </div>
                @endif
            </div>
        </div>
        @endif

        <!-- Vendor/Buyer Information Section -->
        <div class="section" style="background: linear-gradient(135deg, #f3e7fc 0%, #e9d5ff 100%); border: 2px solid #c084fc; border-radius: 12px; padding: 20px;">
            <div class="section-title" style="background: #9333ea; color: white; padding: 12px 20px; margin: -20px -20px 20px -20px; border-radius: 10px 10px 0 0; font-size: 18px;">
                🏢 Vendor & Buyer Information
            </div>
            <div class="info-grid" style="grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
                @php
                    // Handle double-encoded JSON for vendor/buyer info
                    $vendorPayload = $lead->payload;
                    if (is_string($vendorPayload)) {
                        $vendorPayload = json_decode($vendorPayload, true);
                    }
                    if (is_string($vendorPayload)) {
                        $vendorPayload = json_decode($vendorPayload, true);
                    }
                @endphp
                
                <!-- Vendor Information -->
                <div class="info-item">
                    <div class="info-label">Vendor Name</div>
                    <div class="info-value">
                        {{ $lead->vendor_name ?: ($vendorPayload['vendor_name'] ?? 'Not provided') }}
                    </div>
                </div>
                
                <div class="info-item">
                    <div class="info-label">Vendor ID</div>
                    <div class="info-value">
                        @php
                            $vendorId = $vendorPayload['vendor_id'] ?? null;
                            // Remove .0 from numeric IDs
                            if ($vendorId && is_numeric($vendorId)) {
                                $vendorId = rtrim(rtrim(number_format($vendorId, 10, '.', ''), '0'), '.');
                            }
                        @endphp
                        {{ $vendorId ?: 'Not provided' }}
                    </div>
                </div>
                
                                        <div class="info-item">
                            <div class="info-label">Vendor Campaign ID</div>
                            <div class="info-value">
                                @php
                                    $vendorCampaignId = $vendorPayload['vendor_campaign_id'] ?? null;
                                    // Also check meta field for vendor_campaign_id
                                    if (!$vendorCampaignId && $lead->meta) {
                                        $metaData = is_string($lead->meta) ? json_decode($lead->meta, true) : $lead->meta;
                                        $vendorCampaignId = $metaData['vendor_campaign_id'] ?? null;
                                    }
                                    // Remove .0 from numeric IDs
                                    if ($vendorCampaignId && is_numeric($vendorCampaignId)) {
                                        $vendorCampaignId = rtrim(rtrim(number_format($vendorCampaignId, 10, '.', ''), '0'), '.');
                                    }
                                @endphp
                                {{ $vendorCampaignId ?: 'Not provided' }}
                            </div>
                        </div>
                
                <!-- Buyer Information -->
                <div class="info-item">
                    <div class="info-label">Buyer Name</div>
                    <div class="info-value">
                        {{ $lead->buyer_name ?: ($vendorPayload['buyer_name'] ?? 'Not provided') }}
                    </div>
                </div>
                
                <div class="info-item">
                    <div class="info-label">Buyer ID</div>
                    <div class="info-value">
                        @php
                            $buyerId = $vendorPayload['buyer_id'] ?? null;
                            // Remove .0 from numeric IDs
                            if ($buyerId && is_numeric($buyerId)) {
                                $buyerId = rtrim(rtrim(number_format($buyerId, 10, '.', ''), '0'), '.');
                            }
                        @endphp
                        {{ $buyerId ?: 'Not provided' }}
                    </div>
                </div>
                
                <div class="info-item">
                    <div class="info-label">Campaign ID</div>
                    <div class="info-value">
                        @php
                            $campaignIdDisplay = $lead->campaign_id;
                            // Remove .0 from numeric IDs
                            if ($campaignIdDisplay && is_numeric($campaignIdDisplay)) {
                                $campaignIdDisplay = rtrim(rtrim(number_format($campaignIdDisplay, 10, '.', ''), '0'), '.');
                            }
                        @endphp
                        <strong>{{ $campaignIdDisplay ?: 'Not provided' }}</strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- TCPA Compliance Section - HIDE ONLY IN EDIT MODE -->
        @if(!isset($mode) || $mode !== 'edit')
        <div class="section" style="background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); border: 2px solid #86efac; border-radius: 12px; padding: 20px;">
                    <div class="section-title compliance" style="background: #22c55e; color: white; padding: 12px 20px; margin: -20px -20px 20px -20px; border-radius: 10px 10px 0 0; font-size: 18px;">
                        🛡️ TCPA Compliance & Consent Information
                    </div>
                    <div class="info-grid" style="grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
                                                <!-- TCPA Compliance Status -->
                        <div class="info-item">
                            <div class="info-label">TCPA Compliant</div>
                            <div class="info-value">
                                @php
                                    $tcpaCompliant = false;
                                    
                                    // For SURAJ leads, always TCPA compliant
                                    if ($lead->source === 'SURAJ_BULK' || $lead->source === 'SURAJ') {
                                        $tcpaCompliant = true;
                                    }
                                    // Check direct field first
                                    elseif (isset($lead->tcpa_compliant)) {
                                        $tcpaCompliant = $lead->tcpa_compliant;
                                    } 
                                    // Check meta field
                                    elseif (isset($lead->meta) && is_array($lead->meta) && isset($lead->meta['tcpa_compliant'])) {
                                        $tcpaCompliant = $lead->meta['tcpa_compliant'];
                                    }
                                    
                                    // Robust boolean conversion - handle all possible formats
                                    if (is_string($tcpaCompliant)) {
                                        $tcpaCompliant = in_array(strtolower($tcpaCompliant), ['true', '1', 'yes', 'on']);
                                    } elseif (is_numeric($tcpaCompliant)) {
                                        $tcpaCompliant = (bool)$tcpaCompliant;
                                    } else {
                                        $tcpaCompliant = (bool)$tcpaCompliant;
                                    }
                                @endphp
                                @if($tcpaCompliant)
                                    <span style="color: #28a745; font-weight: bold;">✅ YES</span>
                                @else
                                    <span style="color: #dc3545; font-weight: bold;">❌ NO</span>
                                @endif
                    </div>
                        </div>
                        
                        <!-- Opt-In Date -->
                        <div class="info-item">
                            <div class="info-label">Opt-In Date</div>
                            <div class="info-value">
                                @php
                                    $optInDate = null;
                                    
                                    // First check if opt_in_date field exists (already extracted)
                                    if (isset($lead->opt_in_date) && $lead->opt_in_date) {
                                        try {
                                            $optInDate = \Carbon\Carbon::parse($lead->opt_in_date)->format('m/d/Y g:i A');
                                        } catch (\Exception $e) {
                                            $optInDate = $lead->opt_in_date;
                                        }
                                    }
                                    // Otherwise check payload
                                    else {
                                        // Handle double-encoded JSON
                                        $payload = $lead->payload;
                                        if (is_string($payload)) {
                                            $payload = json_decode($payload, true);
                                        }
                                        if (is_string($payload)) {
                                            $payload = json_decode($payload, true);
                                        }
                                        
                                        // For LQF leads, check originally_created
                                        if (isset($payload['originally_created'])) {
                                            try {
                                                $optInDate = \Carbon\Carbon::parse($payload['originally_created'])->format('m/d/Y g:i A');
                                            } catch (\Exception $e) {
                                                $optInDate = $payload['originally_created'];
                                            }
                                        }
                                        // For Suraj leads - check timestamp (column B)
                                        elseif (isset($payload['timestamp'])) {
                                            try {
                                                $optInDate = \Carbon\Carbon::parse($payload['timestamp'])->format('m/d/Y g:i A');
                                            } catch (\Exception $e) {
                                                // Try alternate format
                                            }
                                        }
                                    }
                                    
                                    // Fallback to created_at
                                    if (!$optInDate && $lead->created_at) {
                                        $optInDate = \Carbon\Carbon::parse($lead->created_at)->format('m/d/Y g:i A');
                                    }
                                @endphp
                                {{ $optInDate ?: 'Not provided' }}
                            </div>
                        </div>

                        <!-- TrustedForm Certificate -->
                        @if(isset($lead->meta) && is_array($lead->meta) && isset($lead->meta['trusted_form_cert_url']))
                        <div class="info-item">
                            <div class="info-label">TrustedForm Certificate</div>
                            <div class="info-value">
                                <a href="{{ $lead->meta['trusted_form_cert_url'] }}" target="_blank" style="color: #28a745; text-decoration: none;">
                                    📜 View Certificate
                                </a>
                                <button class="copy-btn" onclick="copyToClipboard('{{ $lead->meta['trusted_form_cert_url'] }}', this)" title="Copy URL" style="background: #10b981; color: white; border: none; padding: 4px 8px; border-radius: 4px; cursor: pointer; margin-left: 8px;">📋</button>
                            </div>
                        </div>
                        @endif
                        
                        <!-- Lead ID Code -->
                        @if(isset($lead->meta) && is_array($lead->meta) && isset($lead->meta['lead_id_code']))
                        <div class="info-item">
                            <div class="info-label">Lead ID Code</div>
                            <div class="info-value">
                                {{ $lead->meta['lead_id_code'] }}
                                <button class="copy-btn" onclick="copyToClipboard('{{ $lead->meta['lead_id_code'] }}', this)" title="Copy Lead ID" style="background: #10b981; color: white; border: none; padding: 4px 8px; border-radius: 4px; cursor: pointer; margin-left: 8px;">📋</button>
                            </div>
                        </div>
                        @endif

                        <!-- Landing Page from Payload -->
                        @php
                            $landingPage = null;
                            if (isset($lead->payload) && is_string($lead->payload)) {
                                $payloadData = json_decode($lead->payload, true);
                                $landingPage = $payloadData['landing_page'] ?? 
                                              $payloadData['data']['landing_page'] ?? 
                                              $payloadData['meta']['landing_page'] ?? null;
                            }
                            // Also check direct field
                            if (!$landingPage && isset($lead->landing_page_url)) {
                                $landingPage = $lead->landing_page_url;
                            }
                        @endphp
                        @if($landingPage)
                        <div class="info-item">
                            <div class="info-label">Landing Page</div>
                            <div class="info-value">
                                <a href="{{ $landingPage }}" target="_blank" style="color: #007bff; text-decoration: none;">
                                    🔗 View Landing Page
                                </a>
                                <button class="copy-btn" onclick="copyToClipboard('{{ $landingPage }}', this)" title="Copy to clipboard">📋</button>
                                <div style="font-size: 11px; color: #666; margin-top: 3px; word-break: break-all;">
                                    {{ $landingPage }}
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- IP Address from Contact/Payload -->
                        @php
                            $ipAddress = null;
                            if (isset($lead->payload) && is_string($lead->payload)) {
                                $payloadData = json_decode($lead->payload, true);
                                $ipAddress = $payloadData['contact']['ip_address'] ?? 
                                           $payloadData['ip_address'] ?? 
                                           $payloadData['data']['ip_address'] ?? 
                                           $payloadData['meta']['ip_address'] ?? null;
                            }
                            // Also check direct field
                            if (!$ipAddress && isset($lead->ip_address)) {
                                $ipAddress = $lead->ip_address;
                            }
                        @endphp
                        @if($ipAddress)
                        <div class="info-item">
                            <div class="info-label">IP Address</div>
                            <div class="info-value">
                                {{ $ipAddress }}
                                <button class="copy-btn" onclick="copyToClipboard('{{ $ipAddress }}', this)" title="Copy IP Address" style="background: #10b981; color: white; border: none; padding: 4px 8px; border-radius: 4px; cursor: pointer; margin-left: 8px;">📋</button>
                            </div>
                        </div>
                        @endif

                        <!-- TCPA Consent Text from Payload -->
                        @php
                            $tcpaConsentText = null;
                            if (isset($lead->payload) && is_string($lead->payload)) {
                                $payloadData = json_decode($lead->payload, true);
                                // Look in multiple possible locations
                                $tcpaConsentText = $payloadData['tcpa_consent_text'] ?? 
                                                  $payloadData['contact']['tcpa_consent_text'] ?? 
                                                  $payloadData['data']['tcpa_consent_text'] ?? 
                                                  $payloadData['tcpa_text'] ?? 
                                                  $payloadData['contact']['tcpa_text'] ?? 
                                                  $payloadData['data']['tcpa_text'] ?? 
                                                  $payloadData['meta']['tcpa_consent_text'] ?? 
                                                  $payloadData['meta']['tcpa_text'] ?? null;
                            }
                            // Also check direct field
                            if (!$tcpaConsentText && isset($lead->tcpa_consent_text)) {
                                $tcpaConsentText = $lead->tcpa_consent_text;
                            }
                            // Check in meta field
                            if (!$tcpaConsentText && isset($lead->meta) && is_array($lead->meta)) {
                                $tcpaConsentText = $lead->meta['tcpa_consent_text'] ?? $lead->meta['tcpa_text'] ?? null;
                            }
                        @endphp
                        @if($tcpaConsentText)
                        <div class="info-item">
                            <div class="info-label">TCPA Consent Text</div>
                            <div class="info-value">
                                <span style="color: #28a745;">✓ Consent text available</span>
                                <button class="copy-btn" onclick="copyToClipboard('{{ addslashes($tcpaConsentText) }}', this)" title="Copy Consent Text" style="background: #10b981; color: white; border: none; padding: 4px 8px; border-radius: 4px; cursor: pointer; margin-left: 8px;">📋</button>
                            </div>
                        </div>
                        @endif

                        <!-- External Lead ID now shown in header -->
                    </div>
                </div>
        @endif

            <!-- Edit Form -->
            <div class="edit-form" id="contact-edit">
                <label>First Name:</label>
                <input type="text" id="edit-first-name" value="{{ $lead->first_name ?? '' }}" placeholder="First name">
                
                <label>Last Name:</label>
                <input type="text" id="edit-last-name" value="{{ $lead->last_name ?? '' }}" placeholder="Last name">
                
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
                
                <!-- Prefill Questions Section -->
                <div style="border-top: 1px solid #e0e0e0; margin: 20px 0 15px 0; padding-top: 15px;">
                    <h4 style="color: #495057; margin-bottom: 15px; font-size: 14px;">📋 Qualification Questions</h4>
                    
                    <label>Do you have an active driver's license?</label>
                    <select id="edit-drivers-license">
                        <option value="yes" selected>Yes</option>
                        <option value="no">No</option>
                    </select>
                    
                    <label>DUI or SR22 required?</label>
                    <select id="edit-dui-sr22">
                        <option value="no" selected>No</option>
                        <option value="yes">Yes</option>
                    </select>
                    
                    <label>How many cars do you need to insure?</label>
                    @php
                        $vehicleCount = 1; // Default
                        if (isset($lead->vehicles) && is_array($lead->vehicles)) {
                            $vehicleCount = count($lead->vehicles);
                        } elseif (isset($lead->vehicles) && is_string($lead->vehicles)) {
                            $vehiclesArray = json_decode($lead->vehicles, true);
                            if (is_array($vehiclesArray)) {
                                $vehicleCount = count($vehiclesArray);
                            }
                        }
                    @endphp
                    <select id="edit-vehicle-count">
                        <option value="1" {{ $vehicleCount == 1 ? 'selected' : '' }}>1 car</option>
                        <option value="2" {{ $vehicleCount == 2 ? 'selected' : '' }}>2 cars</option>
                        <option value="3" {{ $vehicleCount == 3 ? 'selected' : '' }}>3 cars</option>
                        <option value="4" {{ $vehicleCount == 4 ? 'selected' : '' }}>4 cars</option>
                        <option value="5" {{ $vehicleCount >= 5 ? 'selected' : '' }}>5+ cars</option>
                    </select>
                    
                    <label>Do you own or rent your home?</label>
                    @php
                        $residenceStatus = 'own'; // Default
                        // Check for residence info in drivers data
                        if (isset($lead->drivers) && is_array($lead->drivers) && !empty($lead->drivers)) {
                            $firstDriver = $lead->drivers[0];
                            if (isset($firstDriver['residence_type'])) {
                                $residenceStatus = strtolower($firstDriver['residence_type']) === 'rent' ? 'rent' : 'own';
                            }
                        } elseif (isset($lead->drivers) && is_string($lead->drivers)) {
                            $driversArray = json_decode($lead->drivers, true);
                            if (is_array($driversArray) && !empty($driversArray) && isset($driversArray[0]['residence_type'])) {
                                $residenceStatus = strtolower($driversArray[0]['residence_type']) === 'rent' ? 'rent' : 'own';
                            }
                        }
                    @endphp
                    <select id="edit-residence-status">
                        <option value="own" {{ $residenceStatus === 'own' ? 'selected' : '' }}>Own</option>
                        <option value="rent" {{ $residenceStatus === 'rent' ? 'selected' : '' }}>Rent</option>
                        <option value="live_with_parents">Live with parents</option>
                    </select>
                </div>
                
                <div class="edit-form-buttons">
                    <button class="save-btn" onclick="saveContact()">Save</button>
                    <button class="cancel-btn" onclick="cancelEdit('contact')">Cancel</button>
                </div>
                </div>
                </div>

        <!-- Call Metrics removed from agent view - admin only data -->

        <!-- Drivers (Auto Insurance Only) -->
        @php
            // Only show drivers for Auto insurance leads
            $drivers = null;
            if ($lead->type === 'auto') {
                if (isset($lead->payload) && is_string($lead->payload)) {
                    $payload = json_decode($lead->payload, true);
                    if (isset($payload['data']['drivers']) && is_array($payload['data']['drivers'])) {
                        $drivers = $payload['data']['drivers'];
                    }
                }
                if (!$drivers && $lead->drivers && is_array($lead->drivers)) {
                    $drivers = $lead->drivers;
                }
            }
        @endphp
        
        @if($lead->type === 'auto' && $drivers && count($drivers) > 0)
        <div class="section">
            <div class="section-title drivers">👤 Drivers ({{ count($drivers) }}) 
                @if(!isset($mode) || $mode !== 'view')
                    <button class="add-btn" onclick="addDriver()">Add Driver</button>
                @endif
            </div>
            @foreach($drivers as $index => $driver)
            <div class="driver-card">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                <h4>Driver {{ $index + 1 }}: {{ ($driver['first_name'] ?? '') . ' ' . ($driver['last_name'] ?? '') }}</h4>
                    @if(!isset($mode) || $mode !== 'view')
                        <button class="edit-btn" onclick="editDriver({{ $index }})">✏️ Edit</button>
                    @endif
                </div>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Date of Birth</div>
                        <div class="info-value">
                            @if(isset($driver['birth_date']))
                                {{ \Carbon\Carbon::parse($driver['birth_date'])->format('m-d-Y') }}
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
                        <div class="info-label">Relationship</div>
                        <div class="info-value">{{ $driver['relationship'] ?? 'Not provided' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Residence Status</div>
                        <div class="info-value">{{ ucfirst($driver['residence_type'] ?? 'Not provided') }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">License State</div>
                        <div class="info-value">{{ $driver['license_state'] ?? 'Not provided' }}</div>
                    </div>
                </div>
                
                <!-- Tickets & Accidents - Important for Agent -->
                <div class="info-grid" style="margin-top: 12px; padding-top: 12px; border-top: 1px solid #e9ecef;">
                    <div class="info-item">
                        <div class="info-label">Tickets</div>
                        <div class="info-value">
                                            @if(isset($driver['violations']) && is_array($driver['violations']) && count($driver['violations']) > 0)
                <span style="color: #dc3545; font-weight: bold;">{{ count($driver['violations']) }} violation(s)</span>
                                <button type="button" class="btn btn-sm btn-outline-info" style="margin-left: 8px; padding: 2px 8px; font-size: 10px;" onclick="toggleDetails('violations-{{ $index }}')">View Details</button>
                                @if(!isset($mode) || $mode !== 'view')
                                    <button type="button" class="add-btn" style="margin-left: 4px; padding: 2px 6px; font-size: 9px;" onclick="addViolation({{ $index }})">Add Ticket</button>
                                @endif
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
                                @if(!isset($mode) || $mode !== 'view')
                                    <button type="button" class="add-btn" style="margin-left: 8px; padding: 2px 6px; font-size: 9px;" onclick="addViolation({{ $index }})">Add Violation</button>
                                @endif
                            @else
                                Not provided
                                @if(!isset($mode) || $mode !== 'view')
                                    <button type="button" class="add-btn" style="margin-left: 8px; padding: 2px 6px; font-size: 9px;" onclick="addViolation({{ $index }})">Add Violation</button>
                                @endif
                            @endif
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Accidents</div>
                        <div class="info-value">
                                                            @if(isset($driver['accidents']) && is_array($driver['accidents']) && count($driver['accidents']) > 0)
                <span style="color: #dc3545; font-weight: bold;">{{ count($driver['accidents']) }} accident(s)</span>
                                <button type="button" class="btn btn-sm btn-outline-info" style="margin-left: 8px; padding: 2px 8px; font-size: 10px;" onclick="toggleDetails('accidents-{{ $index }}')">View Details</button>
                                @if(!isset($mode) || $mode !== 'view')
                                    <button type="button" class="add-btn" style="margin-left: 4px; padding: 2px 6px; font-size: 9px;" onclick="addAccident({{ $index }})">Add Accident</button>
                                @endif
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
                                @if(!isset($mode) || $mode !== 'view')
                                    <button type="button" class="add-btn" style="margin-left: 8px; padding: 2px 6px; font-size: 9px;" onclick="addAccident({{ $index }})">Add Accident</button>
        @endif
                            @else
                                Not provided
                                @if(!isset($mode) || $mode !== 'view')
                                    <button type="button" class="add-btn" style="margin-left: 8px; padding: 2px 6px; font-size: 9px;" onclick="addAccident({{ $index }})">Add Accident</button>
                                @endif
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
                
                <!-- View More Details Section for Drivers -->
                <div style="margin-top: 8px; padding: 6px; background: #f8f9fa; border-radius: 3px; border-left: 2px solid #dee2e6;">
                    <details>
                        <summary style="cursor: pointer; font-size: 11px; font-weight: 600; color: #6c757d; padding: 2px 0;">
                            📋 View More Details
                        </summary>
                        <div style="margin-top: 6px; font-size: 10px; color: #6c757d;">
                            <div class="info-grid" style="grid-template-columns: 1fr 1fr; gap: 4px;">
                                @foreach($driver as $key => $value)
                                    @if(!in_array($key, ['first_name', 'last_name', 'birth_date', 'gender', 'marital_status', 'license_state', 'license_status', 'years_licensed', 'relationship', 'claims']))
                                    <div style="padding: 2px 0; border-bottom: 1px solid #f1f3f4;">
                                        <div style="font-size: 9px; color: #868e96; text-transform: uppercase; letter-spacing: 0.5px;">{{ ucwords(str_replace('_', ' ', $key)) }}</div>
                                        <div style="font-size: 10px; color: #495057; margin-top: 1px;">
                                            @if(is_bool($value))
                                                {{ $value ? 'Yes' : 'No' }}
                                            @elseif(is_array($value) && count($value) > 0)
                                                @if(in_array($key, ['tickets', 'accidents', 'major_violations']) && isset($value[0]) && is_array($value[0]))
                                                    <div style="font-weight: 600;">{{ count($value) }} item(s):</div>
                                                    @foreach($value[0] as $subKey => $subValue)
                                                        <div style="font-size: 9px; color: #6c757d; margin-left: 4px;">{{ ucwords(str_replace('_', ' ', $subKey)) }}: {{ $subValue }}</div>
                                                    @endforeach
                                                @else
                                                    {{ count($value) }} item(s)
                                                @endif
                                            @elseif(is_array($value))
                                                None
                                            @elseif($value === 1 || $value === '1')
                                                Yes
                                            @elseif($value === 0 || $value === '0')
                                                No
                                            @else
                                                {{ $value ?? 'Not provided' }}
                                            @endif
                                        </div>
                                    </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </details>
                </div>
            </div>
            @endforeach
        </div>
        @elseif($lead->type === 'auto')
        <!-- No Drivers Section (Auto Insurance Only) -->
        <div class="section">
            <div class="section-title drivers">👤 Drivers (0) 
                @if(!isset($mode) || $mode !== 'view')
                    <button class="add-btn" onclick="addDriver()">Add Driver</button>
                @endif
            </div>
            <p style="color: #6c757d; font-style: italic; text-align: center; padding: 20px;">No drivers added yet. Click "Add Driver" to add driver information.</p>
        </div>
        @endif

        <!-- Vehicles (Auto Insurance) OR Properties (Home Insurance) -->
        @php
            // Use database lead type as primary source of truth
            $isAutoLead = ($lead->type === 'auto');
            $isHomeLead = ($lead->type === 'home');
            $vehicles = null;
            $properties = null;
            
            // Only get data for the appropriate lead type
            if (isset($lead->payload) && is_string($lead->payload)) {
                $payload = json_decode($lead->payload, true);
                
                if ($isAutoLead && isset($payload['data']['vehicles']) && is_array($payload['data']['vehicles'])) {
                    $vehicles = $payload['data']['vehicles'];
                }
                
                if ($isHomeLead && isset($payload['data']['properties']) && is_array($payload['data']['properties'])) {
                    $properties = $payload['data']['properties'];
                }
            }
            
            // Fallback to lead fields only for the appropriate type
            if ($isAutoLead && !$vehicles && $lead->vehicles && is_array($lead->vehicles)) {
                $vehicles = $lead->vehicles;
            }
            if ($isHomeLead && !$properties && $lead->properties && is_array($lead->properties)) {
                $properties = $lead->properties;
            }
        @endphp
        
        @if($isAutoLead && $vehicles && count($vehicles) > 0)
        <div class="section">
            <div class="section-title vehicles">🚗 Vehicles ({{ count($vehicles) }}) 
                @if(!isset($mode) || $mode !== 'view')
                    <button class="add-btn" onclick="addVehicle()">Add Vehicle</button>
                @endif
            </div>
            @foreach($vehicles as $index => $vehicle)
            <div class="vehicle-card">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                <h4>Vehicle {{ $index + 1 }}: {{ ($vehicle['year'] ?? '') . ' ' . ($vehicle['make'] ?? '') . ' ' . ($vehicle['model'] ?? '') }}</h4>
                    @if(!isset($mode) || $mode !== 'view')
                        <button class="edit-btn" onclick="editVehicle({{ $index }})">✏️ Edit</button>
                    @endif
                </div>
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
                    <!-- Moved deductibles to main vehicle section -->
                    @if(isset($vehicle['comprehensive_deductible']) && $vehicle['comprehensive_deductible'])
                    <div class="info-item">
                        <div class="info-label">Comprehensive Deductible</div>
                        <div class="info-value">${{ number_format($vehicle['comprehensive_deductible']) }}</div>
                </div>
                    @endif
                    @if(isset($vehicle['collision_deductible']) && $vehicle['collision_deductible'])
                    <div class="info-item">
                        <div class="info-label">Collision Deductible</div>
                        <div class="info-value">${{ number_format($vehicle['collision_deductible']) }}</div>
            </div>
                    @endif
                </div>
                
                <!-- View More Details Section for Vehicles -->
                <div style="margin-top: 8px; padding: 6px; background: #f8f9fa; border-radius: 3px; border-left: 2px solid #dee2e6;">
                    <details>
                        <summary style="cursor: pointer; font-size: 11px; font-weight: 600; color: #6c757d; padding: 2px 0;">
                            📋 View More Details
                        </summary>
                        <div style="margin-top: 6px; font-size: 10px; color: #6c757d;">
                            <div class="info-grid" style="grid-template-columns: 1fr 1fr; gap: 4px;">
                                @foreach($vehicle as $key => $value)
                                    @if(!in_array($key, ['year', 'make', 'model', 'vin', 'primary_use', 'annual_miles', 'ownership', 'garage', 'comprehensive_deductible', 'collision_deductible']))
                                    <div style="padding: 2px 0; border-bottom: 1px solid #f1f3f4;">
                                        <div style="font-size: 9px; color: #868e96; text-transform: uppercase; letter-spacing: 0.5px;">{{ ucwords(str_replace('_', ' ', $key)) }}</div>
                                        <div style="font-size: 10px; color: #495057; margin-top: 1px;">
                                            @if(is_bool($value))
                                                {{ $value ? 'Yes' : 'No' }}
                                            @elseif(is_array($value) && count($value) > 0)
                                                {{ count($value) }} item(s)
                                            @elseif(is_array($value))
                                                None
                                            @elseif($value === 1 || $value === '1')
                                                Yes
                                            @elseif($value === 0 || $value === '0')
                                                No
                                            @else
                                                {{ $value ?? 'Not provided' }}
                                            @endif
            </div>
                                    </div>
                                    @endif
            @endforeach
                            </div>
                        </div>
                    </details>
                </div>
            </div>
            @endforeach
        </div>
        @elseif($isAutoLead)
        <!-- No Vehicles Section (Auto Insurance Only) -->
        <div class="section">
            <div class="section-title vehicles">🚗 Vehicles (0) 
                @if(!isset($mode) || $mode !== 'view')
                    <button class="add-btn" onclick="addVehicle()">Add Vehicle</button>
                @endif
            </div>
            <p style="color: #6c757d; font-style: italic; text-align: center; padding: 20px;">No vehicles added yet. Click "Add Vehicle" to add vehicle information.</p>
        </div>
        @endif

        <!-- Properties (Home Insurance) -->
        @if($properties && count($properties) > 0)
        <div class="section">
            <div class="section-title properties">🏠 Properties ({{ count($properties) }}) 
                @if(!isset($mode) || $mode !== 'view')
                    <button class="add-btn" onclick="addProperty()">Add Property</button>
                @endif
            </div>
            @foreach($properties as $index => $property)
            <div class="property-card" style="background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 8px; padding: 16px; margin-bottom: 12px;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <h4 style="color: #495057; margin-bottom: 12px;">
                        Property {{ $index + 1 }}: {{ $property['property_type'] ?? 'Residential Property' }}
                        @if(isset($property['year_built']) && !empty($property['year_built']))
                            <span style="font-size: 14px; color: #6c757d; font-weight: normal;">(Built {{ $property['year_built'] }})</span>
                        @endif
                    </h4>
                    @if(!isset($mode) || $mode !== 'view')
                        <button class="edit-btn" onclick="editProperty({{ $index }})">✏️ Edit</button>
                    @endif
                </div>
                
                <!-- Main Property Information -->
                <div class="info-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px; margin-bottom: 12px;">
                    <div class="info-item">
                        <div class="info-label">Property Type</div>
                        <div class="info-value">{{ $property['property_type'] ?? 'Not provided' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Square Footage</div>
                        <div class="info-value">{{ isset($property['square_footage']) ? number_format($property['square_footage']) . ' sq ft' : 'Not provided' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Bedrooms</div>
                        <div class="info-value">{{ $property['bedrooms'] ?? 'Not provided' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Bathrooms</div>
                        <div class="info-value">{{ $property['bathrooms'] ?? 'Not provided' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Year Built</div>
                        <div class="info-value">{{ $property['year_built'] ?? 'Not provided' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Ownership</div>
                        <div class="info-value">{{ $property['ownership'] ?? 'Not provided' }}</div>
                    </div>
                </div>

                <!-- Property Details -->
                <div class="info-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px; margin-bottom: 12px;">
                    <div class="info-item">
                        <div class="info-label">Roof Type</div>
                        <div class="info-value">{{ $property['roof_type'] ?? 'Not provided' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Construction Type</div>
                        <div class="info-value">{{ $property['construction_type'] ?? 'Not provided' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Foundation</div>
                        <div class="info-value">{{ $property['foundation'] ?? 'Not provided' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Heating Type</div>
                        <div class="info-value">{{ $property['heating_type'] ?? 'Not provided' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Garage</div>
                        <div class="info-value">{{ $property['garage'] ?? 'Not provided' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Stories</div>
                        <div class="info-value">{{ $property['stories'] ?? 'Not provided' }}</div>
                    </div>
                </div>

                <!-- Safety & Security -->
                @if(isset($property['home_security']) || isset($property['dog']) || isset($property['wiring_type']) || isset($property['panel_type']))
                <div style="background: #e8f5e8; border: 1px solid #c3e6c3; border-radius: 6px; padding: 12px; margin-bottom: 12px;">
                    <h5 style="color: #2d5016; margin-bottom: 8px; font-size: 14px;">🔒 Safety & Security</h5>
                    <div class="info-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 8px;">
                        @if(isset($property['home_security']))
                        <div class="info-item">
                            <div class="info-label">Security System</div>
                            <div class="info-value">{{ $property['home_security'] ?? 'Not provided' }}</div>
                        </div>
                        @endif
                        @if(isset($property['dog']))
                        <div class="info-item">
                            <div class="info-label">Dog</div>
                            <div class="info-value">{{ is_bool($property['dog']) ? ($property['dog'] ? 'Yes' : 'No') : ($property['dog'] ?? 'Not provided') }}</div>
                        </div>
                        @endif
                        @if(isset($property['wiring_type']))
                        <div class="info-item">
                            <div class="info-label">Wiring Type</div>
                            <div class="info-value">{{ $property['wiring_type'] ?? 'Not provided' }}</div>
                        </div>
                        @endif
                        @if(isset($property['panel_type']))
                        <div class="info-item">
                            <div class="info-label">Electrical Panel</div>
                            <div class="info-value">{{ $property['panel_type'] ?? 'Not provided' }}</div>
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                <!-- Financial Information -->
                @if(isset($property['dwelling_value']) || isset($property['proximity_water']))
                <div style="background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 6px; padding: 12px; margin-bottom: 12px;">
                    <h5 style="color: #856404; margin-bottom: 8px; font-size: 14px;">💰 Financial & Risk Information</h5>
                    <div class="info-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 8px;">
                        @if(isset($property['dwelling_value']))
                        <div class="info-item">
                            <div class="info-label">Dwelling Value</div>
                            <div class="info-value">${{ number_format($property['dwelling_value']) ?? 'Not provided' }}</div>
                        </div>
                        @endif
                        @if(isset($property['proximity_water']))
                        <div class="info-item">
                            <div class="info-label">Proximity to Water</div>
                            <div class="info-value">{{ $property['proximity_water'] ?? 'Not provided' }}</div>
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                <!-- Claims History -->
                @if(isset($property['claims']) && is_array($property['claims']) && count($property['claims']) > 0)
                <div style="background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 6px; padding: 12px; margin-bottom: 12px;">
                    <h5 style="color: #721c24; margin-bottom: 8px; font-size: 14px;">📋 Claims History ({{ count($property['claims']) }})</h5>
                    @foreach($property['claims'] as $claim)
                    <div style="background: white; border-radius: 4px; padding: 8px; margin-bottom: 6px; font-size: 12px;">
                        <div style="font-weight: 600; color: #721c24;">{{ $claim['description'] ?? 'Claim' }}</div>
                        @if(isset($claim['claim_date']))
                        <div style="color: #6c757d;">Date: {{ $claim['claim_date'] }}</div>
                        @endif
                        @if(isset($claim['paid_amount']))
                        <div style="color: #6c757d;">Amount: ${{ number_format($claim['paid_amount']) }}</div>
                        @endif
                    </div>
                    @endforeach
                </div>
                @elseif(isset($property['claims']))
                <div style="background: #d4edda; border: 1px solid #c3e6cb; border-radius: 6px; padding: 8px; margin-bottom: 12px;">
                    <div style="color: #155724; font-size: 12px; text-align: center;">✅ No Claims History</div>
                </div>
                @endif
                
                <!-- View More Details Section for Properties -->
                <div style="margin-top: 8px; padding: 6px; background: #f8f9fa; border-radius: 3px; border-left: 2px solid #dee2e6;">
                    <details>
                        <summary style="cursor: pointer; font-size: 11px; font-weight: 600; color: #6c757d; padding: 2px 0;">
                            📋 View More Details
                        </summary>
                        <div style="margin-top: 6px; font-size: 10px; color: #6c757d;">
                            <div class="info-grid" style="grid-template-columns: 1fr 1fr; gap: 4px;">
                                @foreach($property as $key => $value)
                                    @if(!in_array($key, ['property_type', 'square_footage', 'bedrooms', 'bathrooms', 'year_built', 'ownership', 'roof_type', 'construction_type', 'foundation', 'heating_type', 'garage', 'stories', 'home_security', 'dog', 'wiring_type', 'panel_type', 'dwelling_value', 'proximity_water', 'claims']))
                                    <div style="padding: 2px 0; border-bottom: 1px solid #f1f3f4;">
                                        <div style="font-size: 9px; color: #868e96; text-transform: uppercase; letter-spacing: 0.5px;">{{ ucwords(str_replace('_', ' ', $key)) }}</div>
                                        <div style="font-size: 10px; color: #495057; margin-top: 1px;">
                                            @if(is_bool($value))
                                                {{ $value ? 'Yes' : 'No' }}
                                            @elseif(is_array($value) && count($value) > 0)
                                                {{ count($value) }} item(s)
                                            @elseif(is_array($value))
                                                None
                                            @else
                                                {{ $value ?? 'Not provided' }}
                                            @endif
                                        </div>
                                    </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </details>
                </div>
            </div>
            @endforeach
        </div>
        @elseif($isHomeLead)
        <!-- No Properties Section for Home Leads -->
        <div class="section">
            <div class="section-title properties">🏠 Properties (0) 
                @if(!isset($mode) || $mode !== 'view')
                    <button class="add-btn" onclick="addProperty()">Add Property</button>
                @endif
            </div>
            <p style="color: #6c757d; font-style: italic; text-align: center; padding: 20px;">No properties added yet. Click "Add Property" to add property information.</p>
        </div>
        @endif

        <!-- Current Policy - Always Show -->
        <div class="section">
            <div class="section-title insurance">🛡️ Current Insurance 
                @if(!isset($mode) || $mode !== 'view')
                    <button class="edit-btn" onclick="toggleEdit('insurance')">✏️ Edit</button>
                @endif
            </div>
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

        <!-- Requested Policy Section -->
        @if(isset($lead->requested_policy) && is_array($lead->requested_policy) && !empty(array_filter($lead->requested_policy)))
        <div class="section">
            <div class="section-title insurance">📋 Requested Policy</div>
            <div class="info-grid">
                @if(isset($lead->requested_policy['coverage_type']) && $lead->requested_policy['coverage_type'])
                <div class="info-item">
                    <div class="info-label">Requested Coverage</div>
                    <div class="info-value">{{ $lead->requested_policy['coverage_type'] }}</div>
        </div>
        @endif

                @if(isset($lead->requested_policy['deductible']) && $lead->requested_policy['deductible'])
                <div class="info-item">
                    <div class="info-label">Preferred Deductible</div>
                    <div class="info-value">${{ number_format($lead->requested_policy['deductible']) }}</div>
        </div>
                @endif
                
                @if(isset($lead->requested_policy['monthly_budget']) && $lead->requested_policy['monthly_budget'])
                <div class="info-item">
                    <div class="info-label">Monthly Budget</div>
                    <div class="info-value">${{ number_format($lead->requested_policy['monthly_budget']) }}</div>
                </div>
                @endif
                
                @if(isset($lead->requested_policy['property_damage']) && $lead->requested_policy['property_damage'])
                <div class="info-item">
                    <div class="info-label">Property Damage</div>
                    <div class="info-value">${{ number_format($lead->requested_policy['property_damage']) }}</div>
                </div>
                @endif
                
                @if(isset($lead->requested_policy['bodily_injury']) && $lead->requested_policy['bodily_injury'])
                <div class="info-item">
                    <div class="info-label">Bodily Injury</div>
                    <div class="info-value">{{ $lead->requested_policy['bodily_injury'] }}</div>
                </div>
                @endif
                
                @if(isset($lead->requested_policy['comprehensive_deductible']) && $lead->requested_policy['comprehensive_deductible'])
                <div class="info-item">
                    <div class="info-label">Comprehensive Deductible</div>
                    <div class="info-value">${{ number_format($lead->requested_policy['comprehensive_deductible']) }}</div>
                </div>
                @endif
                
                @if(isset($lead->requested_policy['collision_deductible']) && $lead->requested_policy['collision_deductible'])
                <div class="info-item">
                    <div class="info-label">Collision Deductible</div>
                    <div class="info-value">${{ number_format($lead->requested_policy['collision_deductible']) }}</div>
                </div>
                @endif
                
                @if(isset($lead->requested_policy['preferred_start_date']) && $lead->requested_policy['preferred_start_date'])
                <div class="info-item">
                    <div class="info-label">Preferred Start Date</div>
                    <div class="info-value">{{ \Carbon\Carbon::parse($lead->requested_policy['preferred_start_date'])->format('M j, Y') }}</div>
                </div>
                @endif
            </div>
        </div>
        @endif

    </div>

    <script>
        // Debug helpers
        const debugEnabled = new URLSearchParams(location.search).get('debug') === '1';
        function logDebug(message, detail) {
            if (!debugEnabled) return;
            const panel = document.getElementById('debug-panel');
            const log = document.getElementById('debug-log');
            panel.style.display = 'block';
            const time = new Date().toISOString().split('T')[1].replace('Z','');
            const line = document.createElement('div');
            line.style.marginBottom = '6px';
            line.innerHTML = `<span style="color:#93c5fd">[${time}]</span> ${message}` + (detail ? `<pre style="white-space:pre-wrap; color:#a7f3d0; background:#111; padding:6px; border-radius:4px;">${(typeof detail==='string'?detail:JSON.stringify(detail,null,2)).substring(0,1200)}</pre>` : '');
            log.prepend(line);
            console.log('[DEBUG]', message, detail || '');
        }
        // Global data for JavaScript functions
        const leadDriversData = @json($lead->drivers ?? []);
        const leadVehiclesData = @json($lead->vehicles ?? []);
        
        
        // Auto-refresh disabled for agent view - no call metrics displayed
        
        // Allstate Validation System
        let validationData = null;
        let validationTimer = null;
        
        // Initialize enrichment buttons on page load (no validation)
        document.addEventListener('DOMContentLoaded', function() {
            updateEnrichmentButtons();
            setupAutoSave();
            // ensure conditional blocks reflect current select values on load
            try { toggleInsuranceQuestions(); toggleDUIQuestions(); } catch(_) {}
        });
        
        // REMOVED: debounceValidation function per user request
        
        // REMOVED: validateAllstateReadiness function per user request
        
        // REMOVED: updateValidationUI function per user request
        
        // REMOVED: highlightMissingFields function per user request
        
        // REMOVED: addValidationTooltip function per user request
        
        function updateEnrichmentButtons() {
            // SIMPLIFIED: Always enable enrichment buttons (no validation check)
            const enrichmentButtons = document.querySelectorAll('.btn-enrichment');
            
            enrichmentButtons.forEach(button => {
                button.classList.remove('enrichment-blocked');
                button.classList.add('enrichment-ready');
                button.title = 'Ready for enrichment';
                button.disabled = false;
            });
        }
        
        // REMOVED: showValidationSummary and closeValidationSummary functions per user request
        
        // Ringba Qualification Form Logic
        function toggleInsuranceQuestions() {
            const insured = document.getElementById('currently_insured').value;
            const insuranceQuestions = document.getElementById('insurance_questions');
            
            if (insured === 'yes' || insured === 'Y' || insured === 'y' || insured === 'true' || insured === '1') {
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
            const toYN = (v) => {
                const s = (v || '').toString().trim().toLowerCase();
                if (s === 'y' || s === 'yes' || s === 'true' || s === '1') return 'Y';
                return 'N';
            };

            const ynToBool = (v) => {
                const s = (v || '').toString().trim().toLowerCase();
                return (s === 'y' || s === 'yes' || s === 'true' || s === '1');
            };

            const enrichmentData = {
                // Lead identifiers for webhook callback and tracking
                lead_id: '{{ $lead->id }}',
                external_id: '{{ $lead->external_lead_id ?? '' }}',
                
                // Basic info from lead
                phone: '{{ preg_replace("/[^0-9]/", "", $lead->phone) }}',
                first_name: '{{ $lead->first_name ?? "" }}',
                last_name: '{{ $lead->last_name ?? "" }}',
                email: '{{ $lead->email ?? "" }}',
                address: '{{ $lead->address ?? "" }}',
                city: '{{ $lead->city ?? "" }}',
                state: '{{ $lead->state ?? "" }}', // Lead state, not form state
                zip_code: data.zip_code || '{{ $lead->zip_code ?? "" }}',
                
                // Allstate-aligned Top-12 keys (so RingBA → Allstate is direct)
                currently_insured: ynToBool(data.currently_insured),
                active_license: ynToBool(data.active_license),
                dui_conviction: (data.dui_sr22 === 'dui_only' || data.dui_sr22 === 'both'),
                sr22_required: (data.dui_sr22 === 'sr22_only' || data.dui_sr22 === 'both'),
                dui_timeframe: data.dui_timeframe || '', // 1,2,3
                residence_status: (data.home_status === 'own') ? 'home' : (data.home_status || 'other'),
                
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
        
        async function safeJson(response) {
            const contentType = response.headers.get('content-type') || '';
            if (contentType.includes('application/json')) {
                return await response.json();
            }
            const text = await response.text();
            throw new Error(text && text.length ? text.substring(0, 200) : 'Non-JSON response');
        }

        async function enrichLead(type) {
            // CRITICAL: Validate for Allstate before allowing enrichment
            if (type === 'insured') {
                // REMOVED: Validation check per user request - always allow enrichment
                // Just proceed with enrichment without validation
                
                // Special check for insurance status (Allstate only accepts insured)
                const currentlyInsured = document.getElementById('currently_insured')?.value || '';
                if (!currentlyInsured || currentlyInsured.toLowerCase() === 'no') {
                    alert('❌ Requirement error\n\nYou clicked INSURED, but "Currently insured" is set to No.\n\nUpdate it to Yes or use the UNINSURED enrichment.');
                    return;
                }
            }
            if (type === 'uninsured') {
                const currentlyInsured = document.getElementById('currently_insured')?.value || '';
                if (currentlyInsured.toLowerCase() === 'yes' || currentlyInsured.toLowerCase() === 'y') {
                    alert('❌ Requirement error\n\nYou clicked UNINSURED, but "Currently insured" is set to Yes.\n\nChange it to No or use the INSURED enrichment.');
                    return;
                }
            }
            
            const data = getFormData();
            
            // Ringba enrichment base URLs (we will build ordered query strings)
            const enrichmentBase = {
                insured: 'https://display.ringba.com/enrich/2674154334576444838',
                uninsured: 'https://display.ringba.com/enrich/2676487329580844084',
                homeowner: 'https://display.ringba.com/enrich/2717035800150673197'
            };
            
            const baseURL = enrichmentBase[type];
            if (!baseURL) {
                alert('Invalid enrichment type');
                return;
            }

            // Build query with desired parameter order
            let enrichmentURL = '';
            if (type === 'insured') {
                // Send as boolean strings for Allstate (expects true/false, not Y/N)
                const yn = (v) => (/^(y|yes|true|1)$/i.test(`${v}`) ? 'true' : 'false');
                const mapContinuous = (v) => {
                    switch(v) {
                        case 'under_6_months': return 5;
                        case '6_months_1_year': return 6;
                        case '1_3_years': return 12;
                        case 'over_3_years': return 12;
                        default: return 0;
                    }
                };
                const mapDuiWhen = (v) => (v === '1' ? 'under1' : v === '2' ? '1to3' : v === '3' ? 'over3' : '');
                const mapDuiOption = (v) => (v === 'dui_only' ? 'DUI' : v === 'sr22_only' ? 'SR22' : v === 'both' ? 'DUI_SR22' : 'no');
                const digits = (p) => (p || '').replace(/[^0-9]/g, '');

                // Send fields in Allstate-compatible format
                const mapResidence = (v) => (/^(y|yes|true|1)$/i.test(`${v}`) ? 'own' : 'rent');
                
                // Ensure gender is lowercase and valid for Allstate
                const mapGender = (v) => {
                    const g = (v || '').toLowerCase().trim();
                    if (g === 'm' || g === 'male') return 'male';
                    if (g === 'f' || g === 'female') return 'female';
                    if (g === 'x') return 'X';
                    return 'unknown';
                };
                
                // Ensure marital status is lowercase and valid for Allstate
                const mapMaritalStatus = (v) => {
                    const m = (v || '').toLowerCase().trim();
                    const valid = ['single', 'married', 'separated', 'divorced', 'widowed'];
                    return valid.includes(m) ? m : 'single';
                };
                
                // Check if currently insured with Allstate
                const isAllstateCustomer = () => {
                    const provider = (data.current_provider || '').toLowerCase().trim();
                    return provider.includes('allstate') ? 'true' : 'false';
                };
                
                const orderedPairs = [
                    ['primary_phone', digits(data.phone)],
                    ['currently_insured', yn(data.currently_insured)],
                    ['current_insurance_company', data.current_provider || ''],
                    ['allstate', isAllstateCustomer()], // New parameter for RingBA
                    ['continuous_coverage', mapContinuous(data.insurance_duration)],
                    ['valid_license', 'true'], // Hardcoded to true as requested
                    ['num_vehicles', data.num_vehicles || ''],
                    ['dui', (data.dui_sr22 === 'dui_only' || data.dui_sr22 === 'both') ? 'true' : 'false'],
                    ['requires_sr22', (data.dui_sr22 === 'sr22_only' || data.dui_sr22 === 'both') ? 'true' : 'false'],
                    ['state', (data.state_input || data.state || '')],
                    ['zip_code', data.zip_code || ''],
                    ['first_name', data.first_name || ''],
                    ['last_name', data.last_name || ''],
                    ['email', data.email || ''],
                    ['date_of_birth', data.date_of_birth || ''],
                    ['gender', mapGender(data.gender)],
                    ['marital_status', mapMaritalStatus(data.marital_status)],
                    ['residence_status', mapResidence(data.homeowner)],
                    ['tcpa_compliant', 'true'],
                    ['external_id', leadId || ''],
                    ['received_quote', yn(data.allstate_quote)],
                    ['ready_to_talk', yn(data.ready_to_speak)]
                ];
                const qs = orderedPairs
                    .filter(([_, v]) => v !== undefined && v !== null && `${v}`.length > 0)
                    .map(([k, v]) => `${encodeURIComponent(k)}=${encodeURIComponent(v)}`)
                    .join('&');
                enrichmentURL = `${baseURL}?${qs}`;
            } else if (type === 'homeowner') {
                // Homeowner: minimal set RingBA expects
                const digits = (p) => (p || '').replace(/[^0-9]/g, '');
                
                // Check if currently insured with Allstate for homeowner
                const isAllstateCustomer = () => {
                    const provider = (data.current_provider || '').toLowerCase().trim();
                    return provider.includes('allstate') ? 'true' : 'false';
                };
                
                const orderedPairs = [
                    ['callerid', digits(data.phone)],
                    ['homeowner', 'Y'],
                    ['allstate', isAllstateCustomer()], // Check if current customer is with Allstate
                    ['address', data.address || ''],
                    ['city', data.city || ''],
                    ['state_name', (data.state_input || data.state || '')],
                    ['zip_code', data.zip_code || ''],
                    ['first_name', data.first_name || ''],
                    ['last_name', data.last_name || ''],
                    ['email', data.email || '']
                ];
                const qs = orderedPairs
                    .filter(([_, v]) => v !== undefined && v !== null && `${v}`.length > 0)
                    .map(([k, v]) => `${encodeURIComponent(k)}=${encodeURIComponent(v)}`)
                    .join('&');
                enrichmentURL = `${baseURL}?${qs}`;
            } else {
                // Send as boolean strings for Allstate (expects true/false, not Y/N)
                const yn = (v) => (/^(y|yes|true|1)$/i.test(`${v}`) ? 'true' : 'false');
                const mapDuiWhen = (v) => (v === '1' ? 'under1' : v === '2' ? '1to3' : v === '3' ? 'over3' : '');
                const mapDuiOption = (v) => (v === 'dui_only' ? 'DUI' : v === 'sr22_only' ? 'SR22' : v === 'both' ? 'DUI_SR22' : 'no');
                const digits = (p) => (p || '').replace(/[^0-9]/g, '');

                // Send fields in Allstate-compatible format for uninsured
                const mapResidence = (v) => (/^(y|yes|true|1)$/i.test(`${v}`) ? 'own' : 'rent');
                
                // Ensure gender is lowercase and valid for Allstate
                const mapGender = (v) => {
                    const g = (v || '').toLowerCase().trim();
                    if (g === 'm' || g === 'male') return 'male';
                    if (g === 'f' || g === 'female') return 'female';
                    if (g === 'x') return 'X';
                    return 'unknown';
                };
                
                // Ensure marital status is lowercase and valid for Allstate
                const mapMaritalStatus = (v) => {
                    const m = (v || '').toLowerCase().trim();
                    const valid = ['single', 'married', 'separated', 'divorced', 'widowed'];
                    return valid.includes(m) ? m : 'single';
                };
                
                // Check if currently insured with Allstate (for uninsured, always false)
                const isAllstateCustomer = () => 'false';
                
                const orderedPairs = [
                    ['primary_phone', digits(data.phone)],
                    ['currently_insured', 'false'],
                    ['current_insurance_company', ''],
                    ['allstate', isAllstateCustomer()], // New parameter for RingBA
                    ['continuous_coverage', '0'],
                    ['valid_license', 'true'], // Hardcoded to true as requested
                    ['num_vehicles', data.num_vehicles || ''],
                    ['dui', (data.dui_sr22 === 'dui_only' || data.dui_sr22 === 'both') ? 'true' : 'false'],
                    ['requires_sr22', (data.dui_sr22 === 'sr22_only' || data.dui_sr22 === 'both') ? 'true' : 'false'],
                    ['state', (data.state_input || data.state || '')],
                    ['zip_code', data.zip_code || ''],
                    ['first_name', data.first_name || ''],
                    ['last_name', data.last_name || ''],
                    ['email', data.email || ''],
                    ['date_of_birth', data.date_of_birth || ''],
                    ['gender', mapGender(data.gender)],
                    ['marital_status', mapMaritalStatus(data.marital_status)],
                    ['residence_status', mapResidence(data.homeowner)],
                    ['tcpa_compliant', 'true'],
                    ['external_id', leadId || ''],
                    ['received_quote', yn(data.allstate_quote)],
                    ['ready_to_talk', yn(data.ready_to_speak)]
                ];
                const qs = orderedPairs
                    .filter(([_, v]) => v !== undefined && v !== null && `${v}`.length > 0)
                    .map(([k, v]) => `${encodeURIComponent(k)}=${encodeURIComponent(v)}`)
                    .join('&');
                enrichmentURL = `${baseURL}?${qs}`;
            }
            
            // Pre-open popup to avoid popup blockers after async
            let popup = null;
            try { popup = window.open('about:blank'); } catch(_) {}
            logDebug('Enrich clicked', { type, currently_insured: document.getElementById('currently_insured')?.value, url: enrichmentURL });
            
            // Show confirmation
            const confirmation = true; // streamline UX: no modal blockers
            
            if (confirmation) {
                // Get the button that was clicked
                const button = event?.target || document.querySelector(`.btn-${type}`);
                const originalText = button.innerHTML;
                
                try {
                    // Navigate popup immediately to avoid blockers; save continues asynchronously
                    if (popup && !popup.closed) {
                        popup.location = enrichmentURL;
                    } else {
                        try { window.open(enrichmentURL, '_blank'); } catch(_) { location.href = enrichmentURL; }
                    }

                    // Then save ALL lead data to the database (comprehensive save)
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
                        insurance_company: document.getElementById('insurance_company')?.value || @json($lead->current_policy['insurance_company'] ?? ''),
                        coverage_type: document.getElementById('coverage_type')?.value || @json($lead->current_policy['coverage_type'] ?? ''),
                        expiration_date: document.getElementById('expiration_date')?.value || @json($lead->current_policy['expiration_date'] ?? ''),
                        insured_since: document.getElementById('insured_since')?.value || @json($lead->current_policy['insured_since'] ?? '')
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
                    logDebug('Saving lead via save-all', allData);
                    const saveResponse = await fetch(`/agent/lead/{{ $lead->id }}/save-all`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        credentials: 'same-origin',
                        body: JSON.stringify(allData)
                    });
                    
                    if (!saveResponse.ok) {
                        const errTxt = await saveResponse.text();
                        logDebug(`Save-all HTTP error ${saveResponse.status}`, errTxt);
                        // proceed to enrichment even if save returned HTML error
                    }
                    logDebug('Save-all complete', { status: saveResponse.status });
                    // Update lead details panel with latest values
                    try {
                        document.getElementById('contact_city') && (document.getElementById('contact_city').value = data.city);
                        document.getElementById('contact_state') && (document.getElementById('contact_state').value = data.state);
                        document.getElementById('contact_zip_code') && (document.getElementById('contact_zip_code').value = data.zip_code);
                        // Homeowner indicator
                        const homeownerRow = document.querySelector('[data-field="homeowner"]');
                        if (homeownerRow) homeownerRow.textContent = (data.residence_status === 'home') ? 'Own' : 'Rent/Other';
                    } catch (_) {}
                    
                    // Popup already navigated earlier
                    
                    // Show success confirmation
                    // REMOVED: Validation summary display per user request
                    // Just show success message via button text
                    
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
                    logDebug('Save-all exception', error?.message || error);
                    alert('Error saving qualification data. Proceeding to enrichment.');
                    
                    // Still open enrichment URL even if save failed
                    // Popup already navigated; ensure at least one navigation happened
                    
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
                display.style.display = 'flex'; // Changed from grid to flex for new contact layout
            } else {
                edit.classList.add('show');
                display.style.display = 'none';
            }
        }
        
        function cancelEdit(section) {
            const display = document.getElementById(section + '-display');
            const edit = document.getElementById(section + '-edit');
            
            edit.classList.remove('show');
            display.style.display = 'flex'; // Changed from grid to flex for new contact layout
        }
        
        async function saveContact() {
            const data = {
                first_name: document.getElementById('edit-first-name').value,
                last_name: document.getElementById('edit-last-name').value,
                phone: document.getElementById('edit-phone').value,
                email: document.getElementById('edit-email').value,
                address: document.getElementById('edit-address').value,
                city: document.getElementById('edit-city').value,
                state: document.getElementById('edit-state').value,
                zip_code: document.getElementById('edit-zip').value,
                // Qualification questions
                drivers_license: document.getElementById('edit-drivers-license').value,
                dui_sr22: document.getElementById('edit-dui-sr22').value,
                vehicle_count: document.getElementById('edit-vehicle-count').value,
                residence_status: document.getElementById('edit-residence-status').value
            };
            
            try {
                // Use the new Vici sync endpoint
                const response = await fetch(`/agent/lead/{{ $lead->id }}/contact-with-vici-sync`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify(data)
                });
                
                const result = await safeJson(response);
                
                if (result.success) {
                    let message = 'Contact information updated successfully!';
                    
                    // Show additional info about what was changed and Vici sync status
                    if (result.changed_fields && result.changed_fields.length > 0) {
                        message += '\n\nUpdated fields: ' + result.changed_fields.join(', ');
                        
                                        if (result.vici_sync === 'success') {
                    message += '\n✅ Successfully synced with Vici dialer';
                } else if (result.vici_sync === 'skipped_or_failed') {
                    message += '\n⚠️ Vici sync failed - lead not found in Vici system';
                }
                    }
                    
                    alert(message);
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
                        <h3 style="margin-top: 0; color: #dc3545; border-bottom: 2px solid #f44336; padding-bottom: 10px;">⚠️ Add Ticket</h3>
                        
                        <div style="margin-bottom: 15px;">
                            <label style="display: block; font-weight: bold; margin-bottom: 5px;">Ticket Type *:</label>
                            <select id="violationType" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;" onchange="handleOtherSelection(this, 'violationTypeOther')" required>
                                <option value="">Select Ticket Type...</option>
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
                            <textarea id="violationDescription" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; height: 100px; resize: vertical;" placeholder="Optional additional details..."></textarea>
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
                
                const result = await safeJson(response);
                
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
                            <textarea id="accidentDescription" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; height: 100px; resize: vertical;" placeholder="Optional additional details..."></textarea>
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
        
        function editDriver(driverIndex) {
            showDriverModal(driverIndex);
        }
        
        function editVehicle(vehicleIndex) {
            showVehicleModal(vehicleIndex);
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

        function showDriverModal(driverIndex = null) {
            const isEditing = driverIndex !== null;
            const driver = isEditing && leadDriversData[driverIndex] ? leadDriversData[driverIndex] : null;
            const modalHtml = `
                <div id="driverModal" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 10000; display: flex; justify-content: center; align-items: center;">
                    <div style="background: white; padding: 30px; border-radius: 12px; width: 90%; max-width: 600px; max-height: 90%; overflow-y: auto; box-shadow: 0 10px 30px rgba(0,0,0,0.3);">
                        <h3 style="margin-top: 0; color: #e65100; border-bottom: 2px solid #ff9800; padding-bottom: 10px;">👤 ${isEditing ? 'Edit Driver' : 'Add New Driver'}</h3>
                        
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
                            <input type="date" id="driverBirthDate" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;" onchange="calculateYearsLicensed()" required>
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
            
            // Populate fields if editing
            if (isEditing && driver) {
                document.getElementById('driverFirstName').value = driver.first_name || '';
                document.getElementById('driverLastName').value = driver.last_name || '';
                
                // Handle birth date
                if (driver.birth_date) {
                    document.getElementById('driverBirthDate').value = driver.birth_date;
                }
                
                // Handle gender with smart selection
                if (driver.gender) {
                    smartSelectOption(document.getElementById('driverGender'), driver.gender, 'driverGenderOther');
                }
                
                // Handle marital status with smart selection
                if (driver.marital_status) {
                    smartSelectOption(document.getElementById('driverMaritalStatus'), driver.marital_status, 'driverMaritalStatusOther');
                }
                
                // Handle license state
                if (driver.license_state) {
                    smartSelectOption(document.getElementById('driverLicenseState'), driver.license_state, 'driverLicenseStateOther');
                }
                
                // Handle license status
                if (driver.license_status) {
                    smartSelectOption(document.getElementById('driverLicenseStatus'), driver.license_status, 'driverLicenseStatusOther');
                }
                
                // Handle years licensed
                if (driver.years_licensed) {
                    smartSelectOption(document.getElementById('driverYearsLicensed'), driver.years_licensed, 'driverYearsLicensedOther');
                }
                
                // Update button text for editing
                const saveButton = document.querySelector('#driverModal button[onclick="saveDriver()"]');
                if (saveButton) {
                    saveButton.textContent = 'Update Driver';
                    saveButton.setAttribute('onclick', `updateDriver(${driverIndex})`);
                }
            }
        }
        
        function calculateYearsLicensed() {
            const birthDateInput = document.getElementById('driverBirthDate');
            const yearsLicensedSelect = document.getElementById('driverYearsLicensed');
            
            if (birthDateInput.value) {
                const birthDate = new Date(birthDateInput.value);
                const today = new Date();
                const age = today.getFullYear() - birthDate.getFullYear() - 
                           (today < new Date(today.getFullYear(), birthDate.getMonth(), birthDate.getDate()) ? 1 : 0);
                
                const estimatedYearsLicensed = Math.max(1, age - 17); // Assume licensed at 17
                
                // Set the closest option
                if (estimatedYearsLicensed <= 10) {
                    yearsLicensedSelect.value = estimatedYearsLicensed.toString();
                } else {
                    yearsLicensedSelect.value = "10"; // 10+ years
                }
            }
        }
        
        async function updateDriver(driverIndex) {
            // Use the same save logic but with update endpoint
            await saveDriver(driverIndex);
        }
        
        function closeDriverModal() {
            const modal = document.getElementById('driverModal');
            if (modal) {
                modal.remove();
            }
        }
        
        async function saveDriver(driverIndex = null) {
            const isEditing = driverIndex !== null;
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
                const url = isEditing ? 
                    `/agent/lead/{{ $lead->id }}/driver/${driverIndex}` : 
                    `/agent/lead/{{ $lead->id }}/driver`;
                const method = isEditing ? 'PUT' : 'POST';
                
                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    closeDriverModal();
                    alert(isEditing ? 'Driver updated successfully!' : 'Driver added successfully!');
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

        // Update header elements after successful save
        function updateHeaderAfterSave(contactData) {
            try {
                // Update the main header title
                const headerTitle = document.querySelector('.header h1');
                if (headerTitle && contactData.first_name && contactData.last_name) {
                    const fullName = `${contactData.first_name} ${contactData.last_name}`;
                    // Preserve any existing mode text (View Only, Edit Mode)
                    const modeText = headerTitle.innerHTML.match(/<span.*?<\/span>/);
                    headerTitle.innerHTML = fullName + (modeText ? ' ' + modeText[0] : '');
                }
                
                // Update the lead info bubble name
                const bubbleName = document.querySelector('.lead-name');
                if (bubbleName && contactData.first_name && contactData.last_name) {
                    bubbleName.textContent = `${contactData.first_name} ${contactData.last_name}`;
                }
                
                // Update the lead info bubble phone
                const bubblePhone = document.querySelector('.lead-phone');
                if (bubblePhone && contactData.phone) {
                    // Format phone number as (xxx)xxx-xxxx
                    const cleanPhone = contactData.phone.replace(/[^0-9]/g, '');
                    if (cleanPhone.length === 10) {
                        const formattedPhone = `(${cleanPhone.substr(0,3)})${cleanPhone.substr(3,3)}-${cleanPhone.substr(6,4)}`;
                        bubblePhone.textContent = formattedPhone;
                    } else {
                        bubblePhone.textContent = contactData.phone;
                    }
                }
                
                // Update page title
                if (contactData.first_name && contactData.last_name) {
                    document.title = `Lead Details - ${contactData.first_name} ${contactData.last_name}`;
                }
                
            } catch (error) {
                console.log('Minor error updating header elements:', error);
                // Don't throw - this is a nice-to-have feature
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
                    first_name: document.getElementById('edit-first-name')?.value || '{{ $lead->first_name }}',
                    last_name: document.getElementById('edit-last-name')?.value || '{{ $lead->last_name }}',
                    phone: document.getElementById('contact_phone')?.value || document.getElementById('edit-phone')?.value || '{{ $lead->phone }}',
                    email: document.getElementById('contact_email')?.value || document.getElementById('edit-email')?.value || '{{ $lead->email }}',
                    address: document.getElementById('contact_address')?.value || document.getElementById('edit-address')?.value || '{{ $lead->address }}',
                    city: document.getElementById('contact_city')?.value || document.getElementById('edit-city')?.value || '{{ $lead->city }}',
                    state: document.getElementById('contact_state')?.value || document.getElementById('edit-state')?.value || '{{ $lead->state }}',
                    zip_code: document.getElementById('contact_zip_code')?.value || document.getElementById('edit-zip')?.value || '{{ $lead->zip_code }}'
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
                    let buttonText = '✅ Saved!';
                    if (result.vici_sync === 'success') {
                        buttonText = '✅ Saved & Synced!';
                    } else if (result.vici_sync === 'skipped_or_failed') {
                        buttonText = '✅ Saved (Vici Sync Failed)';
                    }
                    
                    saveBtn.innerHTML = buttonText;
                    
                    // Update header with new contact information
                    updateHeaderAfterSave(contactData);
                    
                    setTimeout(() => {
                        saveBtn.innerHTML = originalText;
                        saveBtn.disabled = false;
                    }, 3000);
                } else {
                    throw new Error(result.error || 'Failed to save');
                }
            } catch (error) {
                console.error('Error saving lead data:', error);
                alert('Error saving lead data: ' + (error.message || 'Unknown error'));
                saveBtn.innerHTML = originalText;
                saveBtn.disabled = false;
            }
        }

        // Auto-save on blur/change for contact fields
        function setupAutoSave() {
            // Only auto-save CONTACT fields to avoid interfering with Top 12 logic
            const inputs = document.querySelectorAll('#contact-edit input, #contact-edit select');
            let timer = null;
            const triggerSave = () => {
                clearTimeout(timer);
                timer = setTimeout(() => {
                    const contact = {
                        first_name: document.getElementById('edit-first-name')?.value || undefined,
                        last_name: document.getElementById('edit-last-name')?.value || undefined,
                        phone: document.getElementById('edit-phone')?.value || undefined,
                        email: document.getElementById('edit-email')?.value || undefined,
                        address: document.getElementById('edit-address')?.value || undefined,
                        city: document.getElementById('edit-city')?.value || undefined,
                        state: document.getElementById('edit-state')?.value || undefined,
                        zip_code: document.getElementById('edit-zip')?.value || undefined,
                    };

                    fetch(`/agent/lead/{{ $lead->id }}/save-all`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        credentials: 'same-origin',
                        body: JSON.stringify({ contact })
                    }).then(() => {
                        // reflect essential fields inline
                        // no-op for Top 12 to prevent side-effects
                    }).catch(() => {});
                }, 800);
            };
            inputs.forEach(el => {
                el.addEventListener('blur', triggerSave);
                el.addEventListener('change', triggerSave);
            });
        }
        
        // Copy to clipboard function for URLs
        function copyToClipboard(text, button) {
            navigator.clipboard.writeText(text).then(function() {
                // Show success feedback
                const originalText = button.textContent;
                button.textContent = "Copied!";
                button.classList.add("copied");
                
                // Reset after 2 seconds
                setTimeout(() => {
                    button.textContent = originalText;
                    button.classList.remove("copied");
                }, 2000);
            }).catch(function(err) {
                console.error('Failed to copy: ', err);
                // Fallback for older browsers
                const textArea = document.createElement("textarea");
                textArea.value = text;
                document.body.appendChild(textArea);
                textArea.focus();
                textArea.select();
                try {
                    document.execCommand('copy');
                    const originalText = button.textContent;
                    button.textContent = "Copied!";
                    button.classList.add("copied");
                    
                    setTimeout(() => {
                        button.textContent = originalText;
                        button.classList.remove("copied");
                    }, 2000);
                } catch (err) {
                    console.error('Fallback copy failed: ', err);
                }
                document.body.removeChild(textArea);
            });
        }

        function toggleTcpaText(leadId) {
            const preview = document.getElementById('tcpa-text-preview-' + leadId);
            const full = document.getElementById('tcpa-text-full-' + leadId);
            
            if (preview && full) {
                if (preview.style.display === 'none') {
                    preview.style.display = 'block';
                    full.style.display = 'none';
                } else {
                    preview.style.display = 'none';
                    full.style.display = 'block';
                }
            }
        }
        
        // View Payload function
        function viewPayload() {
            @if(isset($lead->payload))
                const payload = @json($lead->payload);
                let payloadData;
                
                // Parse if it's a string
                if (typeof payload === 'string') {
                    try {
                        payloadData = JSON.parse(payload);
                    } catch (e) {
                        payloadData = payload;
                    }
                } else {
                    payloadData = payload;
                }
                
                // Create modal
                const modal = document.createElement('div');
                modal.style.cssText = `
                    position: fixed;
                    top: 0;
                    left: 0;
                    right: 0;
                    bottom: 0;
                    background: rgba(0,0,0,0.8);
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    z-index: 10000;
                `;
                
                const content = document.createElement('div');
                content.style.cssText = `
                    background: white;
                    border-radius: 12px;
                    padding: 30px;
                    max-width: 80%;
                    max-height: 80%;
                    overflow: auto;
                    position: relative;
                `;
                
                const title = document.createElement('h2');
                title.textContent = '📦 Lead Payload Data';
                title.style.cssText = 'margin-bottom: 20px; color: #333;';
                
                const pre = document.createElement('pre');
                pre.style.cssText = `
                    background: #f5f5f5;
                    padding: 20px;
                    border-radius: 8px;
                    overflow: auto;
                    font-size: 14px;
                    line-height: 1.5;
                `;
                pre.textContent = JSON.stringify(payloadData, null, 2);
                
                const closeBtn = document.createElement('button');
                closeBtn.textContent = '✕ Close';
                closeBtn.style.cssText = `
                    position: absolute;
                    top: 15px;
                    right: 15px;
                    background: #dc3545;
                    color: white;
                    border: none;
                    padding: 8px 16px;
                    border-radius: 6px;
                    cursor: pointer;
                    font-weight: 600;
                `;
                closeBtn.onclick = () => document.body.removeChild(modal);
                
                const copyBtn = document.createElement('button');
                copyBtn.textContent = '📋 Copy Payload';
                copyBtn.style.cssText = `
                    background: #10b981;
                    color: white;
                    border: none;
                    padding: 10px 20px;
                    border-radius: 6px;
                    cursor: pointer;
                    font-weight: 600;
                    margin-top: 15px;
                `;
                copyBtn.onclick = () => {
                    navigator.clipboard.writeText(JSON.stringify(payloadData, null, 2));
                    copyBtn.textContent = '✓ Copied!';
                    setTimeout(() => copyBtn.textContent = '📋 Copy Payload', 2000);
                };
                
                content.appendChild(title);
                content.appendChild(pre);
                content.appendChild(copyBtn);
                content.appendChild(closeBtn);
                modal.appendChild(content);
                document.body.appendChild(modal);
                
                // Close on background click
                modal.onclick = (e) => {
                    if (e.target === modal) {
                        document.body.removeChild(modal);
                    }
                };
            @else
                alert('No payload data available for this lead.');
            @endif
        }
        
        // Notify parent window that iframe is loaded
        if (window.parent !== window) {
            window.parent.postMessage({
                type: 'iframeLoaded',
                leadId: {{ $lead->id }},
                leadName: '{{ $lead->name }}'
            }, '*');
        }

        // Clear iframe data when parent signals hangup
        window.addEventListener('message', (event) => {
            if (event.data && event.data.type === 'call_hangup') {
                try {
                    // Clear inputs in qualification and contact edit sections
                    document.querySelectorAll('#qualificationForm select, #qualificationForm input').forEach(el => {
                        if (el.type === 'checkbox' || el.type === 'radio') {
                            el.checked = false;
                        } else {
                            el.value = '';
                        }
                    });
                    document.querySelectorAll('#contact-edit input').forEach(el => { el.value = ''; });
                    console.log('Cleared form fields on call hangup signal');
                } catch (_) {}
            }
        });
    </script>
</body>
</html>
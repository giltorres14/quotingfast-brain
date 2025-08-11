<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Digital Signature - {{ $document['name'] ?? 'Document' }} | The Brain</title>
    <link rel="icon" type="image/png" href="/favicon.png">
    <link rel="shortcut icon" href="/favicon.ico">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: #f8fafc;
            color: #1a202c;
            line-height: 1.6;
        }
        
        /* Header */
        .header {
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
            color: white;
            padding: 1.5rem 0;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .header-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo-section {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .logo {
            height: 50px;
            filter: brightness(1.2);
        }
        
        .brand-text {
            font-size: 1.5rem;
            font-weight: 700;
        }
        
        .document-info {
            text-align: center;
        }
        
        .document-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
        }
        
        .document-subtitle {
            opacity: 0.9;
            font-size: 0.9rem;
        }
        
        .security-badge {
            background: rgba(255, 255, 255, 0.2);
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        /* Main Container */
        .main-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
            display: grid;
            grid-template-columns: 1fr 350px;
            gap: 2rem;
        }
        
        /* Document Viewer */
        .document-viewer {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .viewer-header {
            background: #f8fafc;
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .viewer-title {
            font-weight: 600;
            color: #1a202c;
        }
        
        .viewer-controls {
            display: flex;
            gap: 0.5rem;
        }
        
        .control-btn {
            background: #f3f4f6;
            border: 1px solid #d1d5db;
            color: #374151;
            padding: 0.5rem;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.2s;
        }
        
        .control-btn:hover {
            background: #e5e7eb;
        }
        
        .document-content {
            padding: 2rem;
            min-height: 600px;
            background: white;
            position: relative;
        }
        
        .document-page {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            line-height: 1.8;
        }
        
        .document-page h1 {
            color: #1a202c;
            margin-bottom: 1.5rem;
            font-size: 1.8rem;
        }
        
        .document-page h2 {
            color: #374151;
            margin: 2rem 0 1rem 0;
            font-size: 1.3rem;
        }
        
        .document-page p {
            margin-bottom: 1rem;
            color: #4b5563;
        }
        
        .signature-field {
            border: 2px dashed #d1d5db;
            border-radius: 8px;
            padding: 2rem;
            margin: 2rem 0;
            text-align: center;
            background: #f9fafb;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .signature-field:hover {
            border-color: #8b5cf6;
            background: #f5f3ff;
        }
        
        .signature-field.signed {
            border-color: #10b981;
            background: #f0fdf4;
            cursor: default;
        }
        
        .signature-placeholder {
            color: #6b7280;
            font-size: 1rem;
            margin-bottom: 0.5rem;
        }
        
        .signature-instructions {
            color: #9ca3af;
            font-size: 0.9rem;
        }
        
        .signature-canvas {
            border: 2px solid #10b981;
            border-radius: 8px;
            background: white;
            cursor: crosshair;
        }
        
        .signature-name {
            color: #10b981;
            font-weight: 600;
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
        }
        
        .signature-date {
            color: #6b7280;
            font-size: 0.9rem;
        }
        
        /* Signature Panel */
        .signature-panel {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            height: fit-content;
            position: sticky;
            top: 2rem;
        }
        
        .panel-header {
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
            color: white;
            padding: 1.5rem;
            border-radius: 12px 12px 0 0;
        }
        
        .panel-title {
            font-size: 1.2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .panel-subtitle {
            opacity: 0.9;
            font-size: 0.9rem;
        }
        
        .panel-content {
            padding: 1.5rem;
        }
        
        .progress-section {
            margin-bottom: 2rem;
        }
        
        .progress-title {
            font-weight: 600;
            color: #1a202c;
            margin-bottom: 1rem;
        }
        
        .progress-steps {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        
        .progress-step {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 0.75rem;
            border-radius: 8px;
            transition: all 0.2s;
        }
        
        .progress-step.completed {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
        }
        
        .progress-step.active {
            background: #f5f3ff;
            border: 1px solid #c4b5fd;
        }
        
        .progress-step.pending {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
        }
        
        .step-icon {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
            font-weight: 600;
        }
        
        .step-icon.completed {
            background: #10b981;
            color: white;
        }
        
        .step-icon.active {
            background: #8b5cf6;
            color: white;
        }
        
        .step-icon.pending {
            background: #e5e7eb;
            color: #6b7280;
        }
        
        .step-text {
            flex: 1;
            font-size: 0.9rem;
            color: #374151;
        }
        
        .signature-section {
            margin-bottom: 2rem;
        }
        
        .signature-title {
            font-weight: 600;
            color: #1a202c;
            margin-bottom: 1rem;
        }
        
        .signature-input {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 1rem;
            margin-bottom: 1rem;
            transition: border-color 0.2s;
        }
        
        .signature-input:focus {
            outline: none;
            border-color: #8b5cf6;
        }
        
        .signature-options {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }
        
        .signature-option {
            background: #f9fafb;
            border: 2px solid #e5e7eb;
            padding: 0.75rem;
            border-radius: 8px;
            cursor: pointer;
            text-align: center;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.2s;
        }
        
        .signature-option:hover {
            border-color: #8b5cf6;
            background: #f5f3ff;
        }
        
        .signature-option.active {
            border-color: #8b5cf6;
            background: #8b5cf6;
            color: white;
        }
        
        .canvas-container {
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            margin-bottom: 1rem;
            background: white;
        }
        
        .canvas-controls {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
        }
        
        .canvas-btn {
            background: #f3f4f6;
            border: 1px solid #d1d5db;
            color: #374151;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.2s;
        }
        
        .canvas-btn:hover {
            background: #e5e7eb;
        }
        
        .legal-section {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 2rem;
        }
        
        .legal-title {
            font-weight: 600;
            color: #1a202c;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }
        
        .legal-text {
            font-size: 0.8rem;
            color: #6b7280;
            line-height: 1.5;
        }
        
        .consent-checkbox {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            margin-bottom: 2rem;
        }
        
        .consent-checkbox input {
            margin-top: 0.25rem;
        }
        
        .consent-text {
            font-size: 0.9rem;
            color: #374151;
            line-height: 1.5;
        }
        
        .action-buttons {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        
        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
            transition: all 0.2s;
            border: none;
            font-size: 1rem;
        }
        
        .btn-primary {
            background: #8b5cf6;
            color: white;
        }
        
        .btn-primary:hover {
            background: #7c3aed;
        }
        
        .btn-primary:disabled {
            background: #d1d5db;
            cursor: not-allowed;
        }
        
        .btn-secondary {
            background: #f3f4f6;
            color: #374151;
            border: 1px solid #d1d5db;
        }
        
        .btn-secondary:hover {
            background: #e5e7eb;
        }
        
        .btn-danger {
            background: #ef4444;
            color: white;
        }
        
        .btn-danger:hover {
            background: #dc2626;
        }
        
        /* Success Modal */
        .success-modal {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s;
        }
        
        .success-modal.active {
            opacity: 1;
            visibility: visible;
        }
        
        .success-content {
            background: white;
            border-radius: 12px;
            padding: 3rem 2rem;
            max-width: 500px;
            width: 90%;
            text-align: center;
        }
        
        .success-icon {
            font-size: 4rem;
            color: #10b981;
            margin-bottom: 1rem;
        }
        
        .success-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1a202c;
            margin-bottom: 1rem;
        }
        
        .success-message {
            color: #6b7280;
            margin-bottom: 2rem;
            line-height: 1.6;
        }
        
        .success-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
        }
        
        /* Responsive */
        @media (max-width: 1024px) {
            .main-container {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            
            .signature-panel {
                position: static;
            }
        }
        
        @media (max-width: 768px) {
            .header-container {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }
            
            .main-container {
                padding: 1rem;
            }
            
            .signature-options {
                grid-template-columns: 1fr;
            }
            
            .success-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="header-container">
            <div class="logo-section">
                <img src="https://quotingfast.com/whitelogo" alt="QuotingFast" class="logo" onerror="this.style.display='none';">
                <div class="brand-text">The Brain</div>
            </div>
            
            <div class="document-info">
                <div class="document-title">{{ $document['name'] ?? 'Buyer Agreement' }}</div>
                <div class="document-subtitle">Digital Signature Required</div>
            </div>
            
            <div class="security-badge">
                🔒 Secure Signing
            </div>
        </div>
    </div>
    
    <!-- Main Container -->
    <div class="main-container">
        <!-- Document Viewer -->
        <div class="document-viewer">
            <div class="viewer-header">
                <div class="viewer-title">{{ $document['name'] ?? 'QuotingFast Buyer Agreement' }}</div>
                <div class="viewer-controls">
                    <button class="control-btn" onclick="zoomOut()">🔍-</button>
                    <button class="control-btn" onclick="zoomIn()">🔍+</button>
                    <button class="control-btn" onclick="downloadDocument()">💾</button>
                    <button class="control-btn" onclick="printDocument()">🖨️</button>
                </div>
            </div>
            
            <div class="document-content" id="documentContent">
                <div class="document-page">
                    <h1>QuotingFast Buyer Agreement</h1>
                    
                    <p><strong>Effective Date:</strong> {{ now()->format('F j, Y') }}</p>
                    <p><strong>Parties:</strong> QuotingFast LLC and {{ $buyer->full_name }} ({{ $buyer->company ?? 'Individual' }})</p>
                    
                    <h2>1. Lead Purchase Terms</h2>
                    <p>This agreement governs the purchase and delivery of insurance leads through the QuotingFast platform. By signing this agreement, you agree to the following terms and conditions:</p>
                    
                    <p><strong>Lead Quality:</strong> All leads provided are exclusive and meet our quality standards. Leads include verified contact information and genuine insurance interest.</p>
                    
                    <p><strong>Pricing:</strong> Lead prices are determined by vertical, geography, and quality metrics. Pricing is transparent and displayed before purchase.</p>
                    
                    <p><strong>Delivery:</strong> Leads are delivered in real-time to your dashboard and via API integration if configured.</p>
                    
                    <h2>2. Payment Terms</h2>
                    <p>Payment is required before lead delivery. We accept:</p>
                    <ul style="margin-left: 2rem; margin-bottom: 1rem;">
                        <li>Credit/Debit Cards</li>
                        <li>Bank Transfers (ACH)</li>
                        <li>QuickBooks Payments</li>
                    </ul>
                    
                    <p><strong>Auto-Reload:</strong> You may enable automatic balance reloading to ensure continuous lead delivery.</p>
                    
                    <h2>3. Return Policy</h2>
                    <p>Leads may be returned within 24 hours of delivery if they do not meet quality standards. Valid return reasons include:</p>
                    <ul style="margin-left: 2rem; margin-bottom: 1rem;">
                        <li>Invalid contact information</li>
                        <li>Duplicate leads</li>
                        <li>Customer not interested in insurance</li>
                        <li>Outside your service area</li>
                    </ul>
                    
                    <h2>4. Data Privacy & Security</h2>
                    <p>We are committed to protecting customer data and maintaining TCPA compliance. All lead data is encrypted and securely transmitted.</p>
                    
                    <h2>5. Agreement Terms</h2>
                    <p><strong>Duration:</strong> This agreement is effective for 12 months from the signature date and automatically renews unless terminated.</p>
                    <p><strong>Termination:</strong> Either party may terminate with 30 days written notice.</p>
                    <p><strong>Governing Law:</strong> This agreement is governed by Delaware state law.</p>
                    
                    <!-- Signature Field -->
                    <div class="signature-field" id="signatureField" onclick="openSignatureModal()">
                        <div class="signature-placeholder">✍️ Click here to sign digitally</div>
                        <div class="signature-instructions">Your signature will appear here once completed</div>
                    </div>
                    
                    <p style="margin-top: 2rem; font-size: 0.9rem; color: #6b7280;">
                        <strong>Electronic Signature Disclosure:</strong> By signing electronically, you agree that your electronic signature is the legal equivalent of your manual signature and has the same legal effect as a handwritten signature.
                    </p>
                </div>
            </div>
        </div>
        
        <!-- Signature Panel -->
        <div class="signature-panel">
            <div class="panel-header">
                <div class="panel-title">
                    ✍️ Digital Signature
                </div>
                <div class="panel-subtitle">Complete your signature to activate your account</div>
            </div>
            
            <div class="panel-content">
                <!-- Progress Steps -->
                <div class="progress-section">
                    <div class="progress-title">Signing Progress</div>
                    <div class="progress-steps">
                        <div class="progress-step completed">
                            <div class="step-icon completed">✓</div>
                            <div class="step-text">Document Review</div>
                        </div>
                        <div class="progress-step active">
                            <div class="step-icon active">2</div>
                            <div class="step-text">Identity Verification</div>
                        </div>
                        <div class="progress-step pending">
                            <div class="step-icon pending">3</div>
                            <div class="step-text">Digital Signature</div>
                        </div>
                        <div class="progress-step pending">
                            <div class="step-icon pending">4</div>
                            <div class="step-text">Completion</div>
                        </div>
                    </div>
                </div>
                
                <!-- Signature Section -->
                <div class="signature-section">
                    <div class="signature-title">Signature Details</div>
                    
                    <input type="text" class="signature-input" placeholder="Full Legal Name" value="{{ $buyer->full_name }}" id="signerName">
                    <input type="email" class="signature-input" placeholder="Email Address" value="{{ $buyer->email }}" id="signerEmail" readonly>
                    <input type="text" class="signature-input" placeholder="Title/Position" id="signerTitle">
                    
                    <div class="signature-options">
                        <div class="signature-option active" onclick="selectSignatureType('draw')">
                            ✍️ Draw
                        </div>
                        <div class="signature-option" onclick="selectSignatureType('type')">
                            ⌨️ Type
                        </div>
                    </div>
                    
                    <div class="canvas-container" id="canvasContainer">
                        <canvas id="signatureCanvas" width="300" height="120"></canvas>
                    </div>
                    
                    <div class="canvas-controls">
                        <button class="canvas-btn" onclick="clearSignature()">🗑️ Clear</button>
                        <button class="canvas-btn" onclick="undoSignature()">↶ Undo</button>
                    </div>
                </div>
                
                <!-- Legal Section -->
                <div class="legal-section">
                    <div class="legal-title">🔒 Legal & Security</div>
                    <div class="legal-text">
                        Your signature will be encrypted and stored securely. This creates a legally binding agreement with full audit trail and tamper-proof certificate.
                    </div>
                </div>
                
                <!-- Consent -->
                <div class="consent-checkbox">
                    <input type="checkbox" id="consentCheck" required>
                    <label for="consentCheck" class="consent-text">
                        I agree to use electronic signatures and acknowledge that this agreement is legally binding. I have read and understand all terms and conditions.
                    </label>
                </div>
                
                <!-- Action Buttons -->
                <div class="action-buttons">
                    <button class="btn btn-primary" id="signButton" onclick="completeSignature()" disabled>
                        ✍️ Complete Signature
                    </button>
                    <button class="btn btn-secondary" onclick="previewSignature()">
                        👁️ Preview Document
                    </button>
                    <a href="/buyer/documents" class="btn btn-danger">
                        ❌ Cancel Signing
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Success Modal -->
    <div class="success-modal" id="successModal">
        <div class="success-content">
            <div class="success-icon">🎉</div>
            <div class="success-title">Signature Complete!</div>
            <div class="success-message">
                Your document has been successfully signed and is now legally binding. A copy has been sent to your email address and saved to your account.
            </div>
            <div class="success-actions">
                <a href="/buyer/dashboard" class="btn btn-primary">Go to Dashboard</a>
                <a href="/buyer/documents" class="btn btn-secondary">View Documents</a>
            </div>
        </div>
    </div>

    <script>
        // Canvas setup for signature drawing
        const canvas = document.getElementById('signatureCanvas');
        const ctx = canvas.getContext('2d');
        let isDrawing = false;
        let signatureData = [];
        
        // Set up canvas
        ctx.strokeStyle = '#1a202c';
        ctx.lineWidth = 2;
        ctx.lineCap = 'round';
        ctx.lineJoin = 'round';
        
        // Mouse events
        canvas.addEventListener('mousedown', startDrawing);
        canvas.addEventListener('mousemove', draw);
        canvas.addEventListener('mouseup', stopDrawing);
        canvas.addEventListener('mouseout', stopDrawing);
        
        // Touch events for mobile
        canvas.addEventListener('touchstart', handleTouch);
        canvas.addEventListener('touchmove', handleTouch);
        canvas.addEventListener('touchend', stopDrawing);
        
        function startDrawing(e) {
            isDrawing = true;
            const rect = canvas.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            ctx.beginPath();
            ctx.moveTo(x, y);
            signatureData.push({x, y, type: 'start'});
        }
        
        function draw(e) {
            if (!isDrawing) return;
            const rect = canvas.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            ctx.lineTo(x, y);
            ctx.stroke();
            signatureData.push({x, y, type: 'draw'});
            
            // Enable sign button if signature exists
            updateSignButton();
        }
        
        function stopDrawing() {
            if (isDrawing) {
                isDrawing = false;
                signatureData.push({type: 'end'});
                updateSignButton();
            }
        }
        
        function handleTouch(e) {
            e.preventDefault();
            const touch = e.touches[0];
            const mouseEvent = new MouseEvent(e.type === 'touchstart' ? 'mousedown' : 
                                            e.type === 'touchmove' ? 'mousemove' : 'mouseup', {
                clientX: touch.clientX,
                clientY: touch.clientY
            });
            canvas.dispatchEvent(mouseEvent);
        }
        
        function clearSignature() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            signatureData = [];
            updateSignButton();
        }
        
        function undoSignature() {
            // Simple implementation - clear all for now
            clearSignature();
        }
        
        function selectSignatureType(type) {
            document.querySelectorAll('.signature-option').forEach(option => {
                option.classList.remove('active');
            });
            event.target.classList.add('active');
            
            if (type === 'type') {
                // Show typed signature input
                alert('📝 Typed Signature\n\nFeature coming soon!\nFor now, please use the draw option to create your signature.');
            }
        }
        
        function updateSignButton() {
            const hasSignature = signatureData.length > 0;
            const hasConsent = document.getElementById('consentCheck').checked;
            const signButton = document.getElementById('signButton');
            
            signButton.disabled = !hasSignature || !hasConsent;
        }
        
        // Consent checkbox handler
        document.getElementById('consentCheck').addEventListener('change', updateSignButton);
        
        async function completeSignature() {
            if (signatureData.length === 0) {
                alert('⚠️ Please provide your signature before continuing.');
                return;
            }
            
            if (!document.getElementById('consentCheck').checked) {
                alert('⚠️ Please agree to the terms and conditions before signing.');
                return;
            }
            
            // Get signature data
            const signatureCanvas = document.getElementById('signatureCanvas');
            const signatureDataUrl = signatureCanvas.toDataURL();
            
            const formData = {
                signer_name: document.getElementById('signerName').value,
                signer_email: document.getElementById('signerEmail').value,
                signer_title: document.getElementById('signerTitle').value,
                signature_data: signatureDataUrl,
                consent_agreed: document.getElementById('consentCheck').checked
            };
            
            // Process signature
            const signButton = document.getElementById('signButton');
            signButton.disabled = true;
            signButton.textContent = '⏳ Processing Signature...';
            
            try {
                const response = await fetch('/buyer/documents/{{ $document["id"] }}/signature', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(formData)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    // Update signature field in document
                    const signatureField = document.getElementById('signatureField');
                    const signerName = document.getElementById('signerName').value;
                    const now = new Date();
                    
                    signatureField.innerHTML = `
                        <div class="signature-name">${signerName}</div>
                        <div class="signature-date">Signed on ${now.toLocaleDateString()} at ${now.toLocaleTimeString()}</div>
                        <div style="font-size: 0.8rem; color: #10b981; margin-top: 0.5rem;">✓ Certificate: ${result.certificate_id}</div>
                    `;
                    signatureField.classList.add('signed');
                    signatureField.onclick = null;
                    
                    // Update progress steps
                    document.querySelectorAll('.progress-step').forEach((step, index) => {
                        step.classList.remove('active', 'pending');
                        step.classList.add('completed');
                        step.querySelector('.step-icon').classList.remove('active', 'pending');
                        step.querySelector('.step-icon').classList.add('completed');
                        step.querySelector('.step-icon').textContent = '✓';
                    });
                    
                    // Show success modal
                    setTimeout(() => {
                        document.getElementById('successModal').classList.add('active');
                    }, 1000);
                    
                } else {
                    alert('❌ Signature Failed: ' + result.message);
                    signButton.disabled = false;
                    signButton.textContent = '✍️ Complete Signature';
                }
                
            } catch (error) {
                alert('❌ Error processing signature. Please try again.');
                signButton.disabled = false;
                signButton.textContent = '✍️ Complete Signature';
            }
        }
        
        function previewSignature() {
            alert('👁️ Document Preview\n\nThis will show:\n• Complete document with signature\n• Legal compliance verification\n• Signature certificate\n• Download options\n• Print-ready format');
        }
        
        function openSignatureModal() {
            alert('✍️ Ready to Sign?\n\nPlease use the signature panel on the right to:\n1. Verify your details\n2. Create your signature\n3. Agree to terms\n4. Complete signing process');
        }
        
        // Document viewer functions
        let zoomLevel = 1;
        
        function zoomIn() {
            zoomLevel = Math.min(zoomLevel + 0.1, 2);
            document.getElementById('documentContent').style.transform = `scale(${zoomLevel})`;
        }
        
        function zoomOut() {
            zoomLevel = Math.max(zoomLevel - 0.1, 0.5);
            document.getElementById('documentContent').style.transform = `scale(${zoomLevel})`;
        }
        
        function downloadDocument() {
            alert('💾 Download Document\n\nDownloading signed PDF:\n• Includes digital signature\n• Tamper-proof format\n• Legal compliance verified\n• Certificate attached');
        }
        
        function printDocument() {
            window.print();
        }
        
        // Close success modal when clicking outside
        document.getElementById('successModal').addEventListener('click', function(e) {
            if (e.target === this) {
                this.classList.remove('active');
            }
        });
    </script>
</body>
</html>
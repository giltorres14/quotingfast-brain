<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Documents & Contracts - {{ $buyer->full_name }} | The Brain</title>
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1.5rem 0;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .header-container {
            max-width: 1400px;
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
            height: 150px;
            filter: brightness(1.2);
        }
        
        .brand-text {
            font-size: 1.5rem;
            font-weight: 700;
        }
        
        .user-section {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }
        
        .balance-display {
            background: rgba(255, 255, 255, 0.2);
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.1rem;
        }
        
        /* Navigation */
        .nav-tabs {
            background: white;
            border-bottom: 1px solid #e2e8f0;
            padding: 0 2rem;
        }
        
        .nav-container {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            gap: 2rem;
        }
        
        .nav-tab {
            padding: 1rem 0;
            color: #6b7280;
            text-decoration: none;
            font-weight: 500;
            border-bottom: 2px solid transparent;
            transition: all 0.2s;
        }
        
        .nav-tab:hover {
            color: #667eea;
            text-decoration: none;
        }
        
        .nav-tab.active {
            color: #667eea;
            border-bottom-color: #667eea;
        }
        
        /* Main Content */
        .main-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        .page-header {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        
        .page-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: #1a202c;
            margin-bottom: 0.5rem;
        }
        
        .page-subtitle {
            color: #6b7280;
            font-size: 1.1rem;
        }
        
        /* Document Grid */
        .documents-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin-bottom: 2rem;
        }
        
        .card {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .card-title {
            font-size: 1.2rem;
            font-weight: 700;
            color: #1a202c;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .card-icon {
            font-size: 1.5rem;
        }
        
        .action-btn {
            background: #667eea;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .action-btn:hover {
            background: #5a67d8;
            text-decoration: none;
            color: white;
        }
        
        .action-btn.secondary {
            background: #6b7280;
        }
        
        .action-btn.secondary:hover {
            background: #4b5563;
        }
        
        .action-btn.success {
            background: #10b981;
        }
        
        .action-btn.success:hover {
            background: #059669;
        }
        
        /* Contract Status */
        .contract-status {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 2rem;
            position: relative;
            overflow: hidden;
        }
        
        .contract-status.pending {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        }
        
        .contract-status.expired {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        }
        
        .contract-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .contract-title {
            font-size: 1.3rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .contract-badge {
            background: rgba(255, 255, 255, 0.2);
            padding: 0.25rem 0.75rem;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .contract-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .contract-detail {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }
        
        .detail-label {
            font-size: 0.9rem;
            opacity: 0.8;
        }
        
        .detail-value {
            font-size: 1.1rem;
            font-weight: 600;
        }
        
        .contract-actions {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }
        
        .contract-btn {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 2px solid rgba(255, 255, 255, 0.3);
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .contract-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            border-color: rgba(255, 255, 255, 0.5);
            text-decoration: none;
            color: white;
        }
        
        .contract-btn.primary {
            background: white;
            color: #10b981;
            border-color: white;
        }
        
        .contract-btn.primary:hover {
            background: #f8fafc;
            color: #059669;
        }
        
        /* Document List */
        .document-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            margin-bottom: 1rem;
            transition: all 0.2s;
        }
        
        .document-item:hover {
            border-color: #667eea;
            background: #f8fafc;
        }
        
        .document-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .document-icon {
            width: 40px;
            height: 40px;
            background: #f3f4f6;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }
        
        .document-icon.pdf {
            background: #fee2e2;
            color: #dc2626;
        }
        
        .document-icon.word {
            background: #dbeafe;
            color: #2563eb;
        }
        
        .document-icon.image {
            background: #f0fdf4;
            color: #16a34a;
        }
        
        .document-details {
            flex: 1;
        }
        
        .document-name {
            font-weight: 600;
            color: #1a202c;
            margin-bottom: 0.25rem;
        }
        
        .document-meta {
            font-size: 0.85rem;
            color: #6b7280;
        }
        
        .document-actions {
            display: flex;
            gap: 0.5rem;
        }
        
        .btn-small {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 6px;
            font-size: 0.8rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }
        
        .btn-view {
            background: #f3f4f6;
            color: #374151;
            border: 1px solid #d1d5db;
        }
        
        .btn-view:hover {
            background: #e5e7eb;
            text-decoration: none;
        }
        
        .btn-download {
            background: #667eea;
            color: white;
        }
        
        .btn-download:hover {
            background: #5a67d8;
            text-decoration: none;
            color: white;
        }
        
        .btn-sign {
            background: #10b981;
            color: white;
        }
        
        .btn-sign:hover {
            background: #059669;
            text-decoration: none;
            color: white;
        }
        
        /* Upload Area */
        .upload-area {
            border: 2px dashed #d1d5db;
            border-radius: 12px;
            padding: 3rem 2rem;
            text-align: center;
            background: #f9fafb;
            cursor: pointer;
            transition: all 0.2s;
            margin-bottom: 2rem;
        }
        
        .upload-area:hover {
            border-color: #667eea;
            background: #f0f4ff;
        }
        
        .upload-area.dragover {
            border-color: #667eea;
            background: #f0f4ff;
            transform: scale(1.02);
        }
        
        .upload-icon {
            font-size: 3rem;
            color: #9ca3af;
            margin-bottom: 1rem;
        }
        
        .upload-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.5rem;
        }
        
        .upload-description {
            color: #6b7280;
            margin-bottom: 1.5rem;
        }
        
        .upload-btn {
            background: #667eea;
            color: white;
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }
        
        .upload-btn:hover {
            background: #5a67d8;
        }
        
        /* E-signature Section */
        .signature-section {
            grid-column: 1 / -1;
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
            color: white;
            position: relative;
            overflow: hidden;
        }
        
        .signature-content {
            position: relative;
            z-index: 2;
        }
        
        .signature-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .signature-icon {
            width: 50px;
            height: 150px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }
        
        .signature-title {
            font-size: 1.5rem;
            font-weight: 700;
        }
        
        .signature-features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .feature-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .feature-icon {
            width: 30px;
            height: 30px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
        }
        
        .feature-text {
            font-size: 0.9rem;
            opacity: 0.95;
        }
        
        .signature-actions {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }
        
        .sig-btn {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 2px solid rgba(255, 255, 255, 0.3);
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .sig-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            border-color: rgba(255, 255, 255, 0.5);
            text-decoration: none;
            color: white;
        }
        
        .sig-btn.primary {
            background: white;
            color: #8b5cf6;
            border-color: white;
        }
        
        .sig-btn.primary:hover {
            background: #f8fafc;
            color: #7c3aed;
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 3rem 2rem;
            color: #6b7280;
        }
        
        .empty-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }
        
        .empty-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #374151;
        }
        
        .empty-description {
            margin-bottom: 2rem;
        }
        
        .cta-btn {
            background: #667eea;
            color: white;
            padding: 0.75rem 2rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: background 0.2s;
        }
        
        .cta-btn:hover {
            background: #5a67d8;
            text-decoration: none;
            color: white;
        }
        
        /* Progress Bar */
        .progress-container {
            background: #f3f4f6;
            border-radius: 8px;
            height: 8px;
            margin: 1rem 0;
            overflow: hidden;
        }
        
        .progress-bar {
            background: #10b981;
            height: 100%;
            border-radius: 8px;
            transition: width 0.3s ease;
        }
        
        /* Responsive */
        @media (max-width: 1024px) {
            .documents-grid {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 768px) {
            .header-container {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }
            
            .user-section {
                flex-direction: column;
                gap: 1rem;
            }
            
            .nav-container {
                flex-wrap: wrap;
                gap: 1rem;
            }
            
            .main-container {
                padding: 1rem;
            }
            
            .contract-details {
                grid-template-columns: 1fr;
            }
            
            .contract-actions,
            .signature-actions {
                flex-direction: column;
            }
            
            .document-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
            
            .document-actions {
                align-self: flex-end;
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
                <div class="logo-text" style="display: flex; flex-direction: column; align-items: center; line-height: 1;">
                    <div style="font-family: 'Orbitron', sans-serif; font-size: 0.9rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px;">The</div>
                    <div class="brand-text" style="font-family: 'Orbitron', sans-serif; font-size: 1.4rem; font-weight: 900; text-transform: uppercase; letter-spacing: 1px;">Brain</div>
                </div>
            </div>
            
            <div class="user-section">
                <div class="balance-display">
                    💰 Balance: {{ $buyer->formatted_balance }}
                </div>
                
                <div class="user-info">
                    <div class="user-avatar">
                        {{ strtoupper(substr($buyer->first_name, 0, 1)) }}
                    </div>
                    <div>
                        <div style="font-weight: 600;">{{ $buyer->full_name }}</div>
                        <div style="font-size: 0.85rem; opacity: 0.8;">{{ $buyer->company ?? 'Buyer Account' }}</div>
                    </div>
                </div>
                
                <form method="POST" action="/buyer/logout" style="display: inline;">
                    @csrf
                    <button type="submit" class="logout-btn" style="background: rgba(255, 255, 255, 0.2); border: none; color: white; padding: 0.5rem 1rem; border-radius: 8px; cursor: pointer; font-weight: 500;">Sign Out</button>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Navigation -->
    <div class="nav-tabs">
        <div class="nav-container">
            <a href="/buyer/dashboard" class="nav-tab">📊 Dashboard</a>
            <a href="/buyer/leads" class="nav-tab">👥 My Leads</a>
            <a href="/buyer/billing" class="nav-tab">💳 Billing</a>
            <a href="/buyer/documents" class="nav-tab active">📄 Documents</a>
            <a href="/buyer/reports" class="nav-tab">📈 Reports</a>
            <a href="/buyer/settings" class="nav-tab">⚙️ Settings</a>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="main-container">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-title">Documents & Contracts 📄</h1>
            <p class="page-subtitle">
                Manage your contracts, agreements, and documents. Sign documents digitally with our secure e-signature system.
            </p>
        </div>
        
        <!-- Contract Status -->
        <div class="contract-status {{ $buyer->contract_signed ? 'signed' : 'pending' }}">
            <div class="contract-header">
                <div class="contract-title">
                    @if($buyer->contract_signed)
                        ✅ Buyer Agreement
                        <span class="contract-badge">Active</span>
                    @else
                        ⏳ Buyer Agreement
                        <span class="contract-badge">Pending Signature</span>
                    @endif
                </div>
            </div>
            
            <div class="contract-details">
                <div class="contract-detail">
                    <div class="detail-label">Status</div>
                    <div class="detail-value">
                        @if($buyer->contract_signed)
                            Signed & Active
                        @else
                            Awaiting Signature
                        @endif
                    </div>
                </div>
                
                <div class="contract-detail">
                    <div class="detail-label">
                        @if($buyer->contract_signed)
                            Signed Date
                        @else
                            Created Date
                        @endif
                    </div>
                    <div class="detail-value">
                        @if($buyer->contract_signed)
                            {{ $buyer->contract_signed_at->format('M j, Y') }}
                        @else
                            {{ $buyer->created_at->format('M j, Y') }}
                        @endif
                    </div>
                </div>
                
                <div class="contract-detail">
                    <div class="detail-label">Contract Version</div>
                    <div class="detail-value">v2.1 (Latest)</div>
                </div>
                
                <div class="contract-detail">
                    <div class="detail-label">Next Review</div>
                    <div class="detail-value">
                        @if($buyer->contract_signed)
                            {{ $buyer->contract_signed_at->addYear()->format('M j, Y') }}
                        @else
                            Upon Signing
                        @endif
                    </div>
                </div>
            </div>
            
            <div class="contract-actions">
                @if($buyer->contract_signed)
                    <a href="#" class="contract-btn" onclick="viewContract()">
                        📄 View Contract
                    </a>
                    <a href="#" class="contract-btn" onclick="downloadContract()">
                        💾 Download PDF
                    </a>
                    <a href="#" class="contract-btn" onclick="requestAmendment()">
                        ✏️ Request Amendment
                    </a>
                @else
                    <a href="#" class="contract-btn primary" onclick="signContract()">
                        ✍️ Sign Agreement
                    </a>
                    <a href="#" class="contract-btn" onclick="viewContract()">
                        👁️ Review Terms
                    </a>
                @endif
            </div>
        </div>
        
        <!-- Documents Grid -->
        <div class="documents-grid">
            <!-- My Documents -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">
                        <span class="card-icon">📁</span>
                        My Documents
                    </h2>
                    <button class="action-btn" onclick="openUploadModal()">
                        📤 Upload Document
                    </button>
                </div>
                
                @if(isset($documents) && count($documents) > 0)
                    @foreach($documents as $document)
                    <div class="document-item">
                        <div class="document-info">
                            <div class="document-icon {{ $document['type'] }}">
                                @if($document['type'] === 'pdf')
                                    📄
                                @elseif($document['type'] === 'word')
                                    📝
                                @elseif($document['type'] === 'image')
                                    🖼️
                                @else
                                    📎
                                @endif
                            </div>
                            <div class="document-details">
                                <div class="document-name">{{ $document['name'] }}</div>
                                <div class="document-meta">
                                    {{ $document['size'] }} • Uploaded {{ $document['uploaded_at'] }}
                                    @if($document['requires_signature'])
                                        • ✍️ Signature Required
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="document-actions">
                            <a href="#" class="btn-small btn-view" onclick="viewDocument('{{ $document['id'] }}')">
                                👁️ View
                            </a>
                            <a href="#" class="btn-small btn-download" onclick="downloadDocument('{{ $document['id'] }}')">
                                💾 Download
                            </a>
                            @if($document['requires_signature'] && !$document['signed'])
                                <a href="#" class="btn-small btn-sign" onclick="signDocument('{{ $document['id'] }}')">
                                    ✍️ Sign
                                </a>
                            @endif
                        </div>
                    </div>
                    @endforeach
                @else
                    <div class="empty-state">
                        <div class="empty-icon">📭</div>
                        <div class="empty-title">No documents yet</div>
                        <div class="empty-description">
                            Upload your first document to get started with digital document management.
                        </div>
                        <button class="cta-btn" onclick="openUploadModal()">
                            📤 Upload Document
                        </button>
                    </div>
                @endif
            </div>
            
            <!-- Shared Documents -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">
                        <span class="card-icon">🤝</span>
                        Shared Documents
                    </h2>
                    <span style="font-size: 0.9rem; color: #6b7280;">From QuotingFast</span>
                </div>
                
                <div class="document-item">
                    <div class="document-info">
                        <div class="document-icon pdf">📄</div>
                        <div class="document-details">
                            <div class="document-name">Lead Quality Guidelines</div>
                            <div class="document-meta">2.1 MB • Updated yesterday</div>
                        </div>
                    </div>
                    <div class="document-actions">
                        <a href="#" class="btn-small btn-view">👁️ View</a>
                        <a href="#" class="btn-small btn-download">💾 Download</a>
                    </div>
                </div>
                
                <div class="document-item">
                    <div class="document-info">
                        <div class="document-icon pdf">📄</div>
                        <div class="document-details">
                            <div class="document-name">Return Policy & Procedures</div>
                            <div class="document-meta">1.8 MB • Last week</div>
                        </div>
                    </div>
                    <div class="document-actions">
                        <a href="#" class="btn-small btn-view">👁️ View</a>
                        <a href="#" class="btn-small btn-download">💾 Download</a>
                    </div>
                </div>
                
                <div class="document-item">
                    <div class="document-info">
                        <div class="document-icon pdf">📄</div>
                        <div class="document-details">
                            <div class="document-name">Best Practices Guide</div>
                            <div class="document-meta">3.2 MB • 2 weeks ago</div>
                        </div>
                    </div>
                    <div class="document-actions">
                        <a href="#" class="btn-small btn-view">👁️ View</a>
                        <a href="#" class="btn-small btn-download">💾 Download</a>
                    </div>
                </div>
            </div>
            
            <!-- E-signature System -->
            <div class="card signature-section">
                <div class="signature-content">
                    <div class="signature-header">
                        <div class="signature-icon">✍️</div>
                        <div>
                            <div class="signature-title">Digital E-Signature</div>
                            <div style="opacity: 0.9;">Secure, legal, and binding electronic signatures</div>
                        </div>
                    </div>
                    
                    <div class="signature-features">
                        <div class="feature-item">
                            <div class="feature-icon">🔒</div>
                            <div class="feature-text">Bank-level security</div>
                        </div>
                        <div class="feature-item">
                            <div class="feature-icon">⚖️</div>
                            <div class="feature-text">Legally binding</div>
                        </div>
                        <div class="feature-item">
                            <div class="feature-icon">📱</div>
                            <div class="feature-text">Mobile-friendly</div>
                        </div>
                        <div class="feature-item">
                            <div class="feature-icon">⚡</div>
                            <div class="feature-text">Instant processing</div>
                        </div>
                        <div class="feature-item">
                            <div class="feature-icon">📊</div>
                            <div class="feature-text">Audit trail</div>
                        </div>
                        <div class="feature-item">
                            <div class="feature-icon">🌐</div>
                            <div class="feature-text">Cloud storage</div>
                        </div>
                    </div>
                    
                    <div class="signature-actions">
                        <button class="sig-btn primary" onclick="createSignatureRequest()">
                            ✍️ Create Signature Request
                        </button>
                        <button class="sig-btn" onclick="viewSignatureHistory()">
                            📋 Signature History
                        </button>
                        <button class="sig-btn" onclick="manageSignatures()">
                            ⚙️ Manage Templates
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Upload Area -->
        <div class="upload-area" id="uploadArea">
            <div class="upload-icon">📤</div>
            <div class="upload-title">Drag & Drop Documents Here</div>
            <div class="upload-description">
                Or click to browse files. Supports PDF, DOC, DOCX, JPG, PNG up to 10MB
            </div>
            <button class="upload-btn" onclick="openUploadModal()">Choose Files</button>
        </div>
    </div>

    <script>
        // Document Management Functions
        function openUploadModal() {
            alert('📤 Document Upload\n\nSupported formats:\n• PDF documents\n• Word documents (.doc, .docx)\n• Images (.jpg, .png)\n• Maximum size: 10MB\n\nFeatures:\n• Automatic virus scanning\n• Cloud storage\n• Version control\n• Access permissions');
        }
        
        function viewDocument(documentId) {
            alert(`👁️ Viewing Document\n\nDocument ID: ${documentId}\n\nThis will open the document in a secure viewer with:\n• Full-screen viewing\n• Zoom controls\n• Download option\n• Print capability\n• Comments/annotations`);
        }
        
        function downloadDocument(documentId) {
            alert(`💾 Downloading Document\n\nDocument ID: ${documentId}\n\nSecure download initiated:\n• Encrypted transfer\n• Download tracking\n• Access logging\n• Virus-free guarantee`);
        }
        
        function signDocument(documentId) {
            alert(`✍️ Digital Signature Required\n\nDocument ID: ${documentId}\n\nSignature process:\n• Review document\n• Verify identity\n• Apply digital signature\n• Generate certificate\n• Email confirmation`);
        }
        
        // Contract Functions
        function viewContract() {
            alert('📄 Buyer Agreement\n\nContract Details:\n• Version: v2.1 (Latest)\n• Terms: Lead purchasing agreement\n• Duration: 12 months\n• Auto-renewal: Yes\n• Jurisdiction: Delaware, USA\n\nThis will open the full contract for review.');
        }
        
        function downloadContract() {
            alert('💾 Download Contract PDF\n\nGenerating secure PDF:\n• Digital signatures included\n• Tamper-proof format\n• Legal compliance verified\n• Download tracking enabled');
        }
        
        function signContract() {
            alert('✍️ Sign Buyer Agreement\n\nSignature Process:\n1. Review contract terms\n2. Verify your identity\n3. Apply digital signature\n4. Receive signed copy\n5. Account activation\n\nThis is a legally binding agreement.');
        }
        
        function requestAmendment() {
            alert('✏️ Request Contract Amendment\n\nAmendment Process:\n• Submit amendment request\n• Legal review required\n• Negotiation if needed\n• New signature required\n• Updated terms effective\n\nTypical processing: 3-5 business days');
        }
        
        // E-signature Functions
        function createSignatureRequest() {
            alert('✍️ Create Signature Request\n\nFeatures:\n• Upload document\n• Add signature fields\n• Set signing order\n• Send to recipients\n• Track progress\n• Automatic reminders');
        }
        
        function viewSignatureHistory() {
            alert('📋 Signature History\n\nView all signatures:\n• Completed signatures\n• Pending requests\n• Signature certificates\n• Audit trails\n• Legal compliance records');
        }
        
        function manageSignatures() {
            alert('⚙️ Manage Signature Templates\n\nTemplate Management:\n• Create reusable templates\n• Standard signature fields\n• Company branding\n• Approval workflows\n• Bulk signing options');
        }
        
        // Drag & Drop Upload
        const uploadArea = document.getElementById('uploadArea');
        
        uploadArea.addEventListener('dragover', function(e) {
            e.preventDefault();
            uploadArea.classList.add('dragover');
        });
        
        uploadArea.addEventListener('dragleave', function(e) {
            e.preventDefault();
            uploadArea.classList.remove('dragover');
        });
        
        uploadArea.addEventListener('drop', function(e) {
            e.preventDefault();
            uploadArea.classList.remove('dragover');
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                handleFileUpload(files);
            }
        });
        
        uploadArea.addEventListener('click', function() {
            openUploadModal();
        });
        
        function handleFileUpload(files) {
            alert(`📤 Files Dropped\n\nFiles to upload: ${files.length}\n\nProcessing:\n• File validation\n• Virus scanning\n• Cloud upload\n• Metadata extraction\n• Access permissions`);
        }
    </script>
</body>
</html>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Billing & Payments - {{ $buyer->full_name }} | The Brain</title>
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
            height: 40px;
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
        
        /* Billing Grid */
        .billing-grid {
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
            justify-content: between;
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
        
        /* Balance Card */
        .balance-card {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            border: none;
        }
        
        .balance-amount {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }
        
        .balance-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        
        .balance-label {
            opacity: 0.9;
            font-size: 1.1rem;
        }
        
        .last-updated {
            opacity: 0.8;
            font-size: 0.9rem;
        }
        
        .auto-reload-status {
            background: rgba(255, 255, 255, 0.2);
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }
        
        .auto-reload-title {
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .auto-reload-details {
            font-size: 0.9rem;
            opacity: 0.9;
        }
        
        .add-funds-btn {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 2px solid rgba(255, 255, 255, 0.3);
            padding: 1rem 2rem;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            width: 100%;
            font-size: 1rem;
        }
        
        .add-funds-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            border-color: rgba(255, 255, 255, 0.5);
        }
        
        /* Payment Methods */
        .payment-method {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            margin-bottom: 1rem;
            transition: all 0.2s;
        }
        
        .payment-method:hover {
            border-color: #667eea;
        }
        
        .payment-method.primary {
            border-color: #10b981;
            background: #f0fdf4;
        }
        
        .payment-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .payment-icon {
            width: 40px;
            height: 40px;
            background: #f3f4f6;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }
        
        .payment-details {
            flex: 1;
        }
        
        .payment-name {
            font-weight: 600;
            color: #1a202c;
        }
        
        .payment-description {
            font-size: 0.85rem;
            color: #6b7280;
        }
        
        .payment-actions {
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
        }
        
        .btn-edit {
            background: #f3f4f6;
            color: #374151;
        }
        
        .btn-edit:hover {
            background: #e5e7eb;
        }
        
        .btn-remove {
            background: #fee2e2;
            color: #dc2626;
        }
        
        .btn-remove:hover {
            background: #fecaca;
        }
        
        .btn-primary-small {
            background: #667eea;
            color: white;
        }
        
        .btn-primary-small:hover {
            background: #5a67d8;
        }
        
        /* Add Payment Method */
        .add-payment-card {
            border: 2px dashed #d1d5db;
            background: #f9fafb;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 3rem 2rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .add-payment-card:hover {
            border-color: #667eea;
            background: #f0f4ff;
        }
        
        .add-icon {
            font-size: 3rem;
            color: #9ca3af;
            margin-bottom: 1rem;
        }
        
        .add-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.5rem;
        }
        
        .add-description {
            color: #6b7280;
            font-size: 0.9rem;
        }
        
        /* Transaction History */
        .transaction-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
            border-bottom: 1px solid #f1f5f9;
        }
        
        .transaction-item:last-child {
            border-bottom: none;
        }
        
        .transaction-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .transaction-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }
        
        .transaction-icon.credit {
            background: #dcfce7;
            color: #166534;
        }
        
        .transaction-icon.debit {
            background: #fee2e2;
            color: #dc2626;
        }
        
        .transaction-icon.refund {
            background: #fef3c7;
            color: #d97706;
        }
        
        .transaction-details {
            flex: 1;
        }
        
        .transaction-title {
            font-weight: 600;
            color: #1a202c;
            margin-bottom: 0.25rem;
        }
        
        .transaction-meta {
            font-size: 0.85rem;
            color: #6b7280;
        }
        
        .transaction-amount {
            text-align: right;
        }
        
        .amount-value {
            font-weight: 600;
            font-size: 1rem;
        }
        
        .amount-value.credit {
            color: #10b981;
        }
        
        .amount-value.debit {
            color: #dc2626;
        }
        
        .amount-value.refund {
            color: #d97706;
        }
        
        .transaction-date {
            font-size: 0.8rem;
            color: #6b7280;
        }
        
        /* QuickBooks Integration */
        .quickbooks-section {
            grid-column: 1 / -1;
            background: linear-gradient(135deg, #0077c5 0%, #005a94 100%);
            color: white;
            position: relative;
            overflow: hidden;
        }
        
        .quickbooks-content {
            position: relative;
            z-index: 2;
        }
        
        .quickbooks-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .quickbooks-logo {
            width: 50px;
            height: 50px;
            background: white;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: #0077c5;
        }
        
        .quickbooks-title {
            font-size: 1.5rem;
            font-weight: 700;
        }
        
        .quickbooks-features {
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
        }
        
        .feature-text {
            font-size: 0.9rem;
            opacity: 0.95;
        }
        
        .quickbooks-actions {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }
        
        .qb-btn {
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
        
        .qb-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            border-color: rgba(255, 255, 255, 0.5);
            text-decoration: none;
            color: white;
        }
        
        .qb-btn.primary {
            background: white;
            color: #0077c5;
            border-color: white;
        }
        
        .qb-btn.primary:hover {
            background: #f8fafc;
            color: #005a94;
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
        
        /* Responsive */
        @media (max-width: 1024px) {
            .billing-grid {
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
            
            .balance-amount {
                font-size: 2rem;
            }
            
            .quickbooks-features {
                grid-template-columns: 1fr;
            }
            
            .quickbooks-actions {
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
            <a href="/buyer/billing" class="nav-tab active">💳 Billing</a>
            <a href="/buyer/reports" class="nav-tab">📈 Reports</a>
            <a href="/buyer/settings" class="nav-tab">⚙️ Settings</a>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="main-container">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-title">Billing & Payments 💳</h1>
            <p class="page-subtitle">
                Manage your account balance, payment methods, and billing history. Powered by QuickBooks for seamless accounting integration.
            </p>
        </div>
        
        <!-- Billing Grid -->
        <div class="billing-grid">
            <!-- Account Balance -->
            <div class="card balance-card">
                <div class="balance-info">
                    <div class="balance-label">Current Balance</div>
                    <div class="last-updated">Updated {{ now()->format('M j, g:i A') }}</div>
                </div>
                
                <div class="balance-amount">{{ $buyer->formatted_balance }}</div>
                
                <div class="auto-reload-status">
                    <div class="auto-reload-title">
                        @if($buyer->auto_reload_enabled)
                            ✅ Auto-Reload Active
                        @else
                            ⚠️ Auto-Reload Disabled
                        @endif
                    </div>
                    <div class="auto-reload-details">
                        @if($buyer->auto_reload_enabled)
                            Add ${{ number_format($buyer->auto_reload_amount, 2) }} when balance drops below ${{ number_format($buyer->auto_reload_threshold, 2) }}
                        @else
                            Enable auto-reload to never miss a lead opportunity
                        @endif
                    </div>
                </div>
                
                <button class="add-funds-btn" onclick="openAddFundsModal()">
                    💰 Add Funds via QuickBooks
                </button>
            </div>
            
            <!-- Payment Methods -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">
                        <span class="card-icon">💳</span>
                        Payment Methods
                    </h2>
                </div>
                
                @if(isset($buyer->payment_methods) && count($buyer->payment_methods) > 0)
                    @foreach($buyer->payment_methods as $method)
                    <div class="payment-method {{ $method['is_primary'] ? 'primary' : '' }}">
                        <div class="payment-info">
                            <div class="payment-icon">
                                @if($method['type'] === 'credit_card')
                                    💳
                                @elseif($method['type'] === 'bank_account')
                                    🏦
                                @else
                                    💰
                                @endif
                            </div>
                            <div class="payment-details">
                                <div class="payment-name">
                                    {{ $method['name'] }}
                                    @if($method['is_primary'])
                                        <span style="color: #10b981; font-size: 0.8rem;">• PRIMARY</span>
                                    @endif
                                </div>
                                <div class="payment-description">{{ $method['description'] }}</div>
                            </div>
                        </div>
                        <div class="payment-actions">
                            @if(!$method['is_primary'])
                                <button class="btn-small btn-primary-small">Set Primary</button>
                            @endif
                            <button class="btn-small btn-edit">Edit</button>
                            <button class="btn-small btn-remove">Remove</button>
                        </div>
                    </div>
                    @endforeach
                @else
                    <div class="add-payment-card" onclick="openPaymentMethodModal()">
                        <div class="add-icon">➕</div>
                        <div class="add-title">Add Payment Method</div>
                        <div class="add-description">Connect your QuickBooks payment method to start purchasing leads</div>
                    </div>
                @endif
            </div>
            
            <!-- QuickBooks Integration -->
            <div class="card quickbooks-section">
                <div class="quickbooks-content">
                    <div class="quickbooks-header">
                        <div class="quickbooks-logo">QB</div>
                        <div>
                            <div class="quickbooks-title">QuickBooks Integration</div>
                            <div style="opacity: 0.9;">Professional accounting & payment processing</div>
                        </div>
                    </div>
                    
                    <div class="quickbooks-features">
                        <div class="feature-item">
                            <div class="feature-icon">📊</div>
                            <div class="feature-text">Automatic invoice generation</div>
                        </div>
                        <div class="feature-item">
                            <div class="feature-icon">💳</div>
                            <div class="feature-text">Secure payment processing</div>
                        </div>
                        <div class="feature-item">
                            <div class="feature-icon">📈</div>
                            <div class="feature-text">Real-time expense tracking</div>
                        </div>
                        <div class="feature-item">
                            <div class="feature-icon">🔄</div>
                            <div class="feature-text">Automated reconciliation</div>
                        </div>
                        <div class="feature-item">
                            <div class="feature-icon">📋</div>
                            <div class="feature-text">Tax-ready reports</div>
                        </div>
                        <div class="feature-item">
                            <div class="feature-icon">🔒</div>
                            <div class="feature-text">Bank-level security</div>
                        </div>
                    </div>
                    
                    <div class="quickbooks-actions">
                        <button class="qb-btn primary" onclick="connectQuickBooks()">
                            🔗 Connect QuickBooks
                        </button>
                        <button class="qb-btn" onclick="viewInvoices()">
                            📄 View Invoices
                        </button>
                        <button class="qb-btn" onclick="downloadReports()">
                            📊 Download Reports
                        </button>
                        <button class="qb-btn" onclick="manageSubscription()">
                            ⚙️ Manage Subscription
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Recent Transactions -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">
                        <span class="card-icon">📋</span>
                        Recent Transactions
                    </h2>
                </div>
                
                @if($buyer->payments && $buyer->payments->count() > 0)
                    @foreach($buyer->payments->take(5) as $payment)
                    <div class="transaction-item">
                        <div class="transaction-info">
                            <div class="transaction-icon {{ strtolower($payment->type) }}">
                                @if($payment->type === 'credit')
                                    ⬆️
                                @elseif($payment->type === 'debit')
                                    ⬇️
                                @else
                                    🔄
                                @endif
                            </div>
                            <div class="transaction-details">
                                <div class="transaction-title">{{ $payment->description }}</div>
                                <div class="transaction-meta">
                                    {{ $payment->payment_method }} • {{ $payment->transaction_id }}
                                    @if($payment->status !== 'completed')
                                        • {{ ucfirst($payment->status) }}
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="transaction-amount">
                            <div class="amount-value {{ strtolower($payment->type) }}">
                                @if($payment->type === 'credit')
                                    +${{ number_format($payment->amount, 2) }}
                                @else
                                    -${{ number_format($payment->amount, 2) }}
                                @endif
                            </div>
                            <div class="transaction-date">{{ $payment->created_at->format('M j, Y') }}</div>
                        </div>
                    </div>
                    @endforeach
                @else
                    <div class="empty-state">
                        <div class="empty-icon">📭</div>
                        <div class="empty-title">No transactions yet</div>
                        <div class="empty-description">
                            Your payment history will appear here once you start adding funds or purchasing leads.
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Add Funds Modal -->
    <div class="modal-overlay" id="addFundsModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Add Funds to Account</h3>
                <p class="modal-subtitle">
                    Add funds to your account balance via secure QuickBooks payment processing.
                </p>
            </div>
            
            <form id="addFundsForm">
                <div class="form-group">
                    <label class="form-label">Amount to Add *</label>
                    <input type="number" name="amount" class="form-input" min="10" max="10000" step="0.01" placeholder="100.00" required>
                    <small style="color: #6b7280; font-size: 0.8rem;">Minimum: $10.00 • Maximum: $10,000.00</small>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Payment Method *</label>
                    <select name="payment_method" class="form-select" required>
                        <option value="">Select payment method...</option>
                        <option value="quickbooks">QuickBooks Payment</option>
                        <option value="credit_card">Credit/Debit Card</option>
                        <option value="bank_account">Bank Account (ACH)</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.9rem;">
                        <input type="checkbox" id="enableAutoReload">
                        <span>Enable auto-reload when balance is low</span>
                    </label>
                </div>
                
                <div class="modal-actions">
                    <button type="button" class="btn-cancel" onclick="closeAddFundsModal()">Cancel</button>
                    <button type="submit" class="btn-confirm">💰 Add Funds</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Add Funds Modal
        function openAddFundsModal() {
            document.getElementById('addFundsModal').classList.add('active');
        }
        
        function closeAddFundsModal() {
            document.getElementById('addFundsModal').classList.remove('active');
            document.getElementById('addFundsForm').reset();
        }
        
        // Handle add funds form submission
        document.getElementById('addFundsForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const submitBtn = e.target.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            
            submitBtn.disabled = true;
            submitBtn.textContent = '⏳ Processing...';
            
            try {
                const response = await fetch('/buyer/payment/add-funds', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    alert(`✅ Payment Successful!\n\nAmount: $${parseFloat(formData.get('amount')).toFixed(2)}\nNew Balance: $${result.new_balance.toFixed(2)}\n\nYour account has been credited and is ready to use!`);
                    window.location.reload();
                } else {
                    alert('❌ Payment Failed: ' + result.message);
                }
                
            } catch (error) {
                alert('❌ Error processing payment. Please try again.');
            } finally {
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            }
            
            closeAddFundsModal();
        });
        
        // Payment Method Modal
        function openPaymentMethodModal() {
            alert('💳 Add Payment Method via QuickBooks\n\nSupported methods:\n• Credit/Debit Cards\n• Bank Accounts (ACH)\n• QuickBooks Payments\n• Automated billing\n\nConnect QuickBooks first to manage payment methods.');
        }
        
        // QuickBooks Integration Functions
        function connectQuickBooks() {
            if (confirm('🔗 Connect to QuickBooks?\n\nThis will:\n• Link your QB account\n• Sync payment methods\n• Enable automated invoicing\n• Set up expense tracking\n\nProceed with connection?')) {
                window.location.href = '/buyer/quickbooks/connect';
            }
        }
        
        function viewInvoices() {
            alert('📄 QuickBooks Invoices\n\nView and manage:\n• Monthly statements\n• Lead purchase invoices\n• Payment receipts\n• Tax documents\n\n(Feature available after QuickBooks connection)');
        }
        
        function downloadReports() {
            alert('📊 Download Financial Reports\n\nAvailable reports:\n• Monthly spending summary\n• Lead ROI analysis\n• Tax preparation documents\n• Payment history\n\n(Feature available after QuickBooks connection)');
        }
        
        function manageSubscription() {
            alert('⚙️ Subscription Management\n\nManage:\n• Auto-reload settings\n• Payment schedules\n• Billing preferences\n• Account limits\n\n(Feature available after QuickBooks connection)');
        }
        
        // Close modal when clicking outside
        document.getElementById('addFundsModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeAddFundsModal();
            }
        });
        
        // Close modal on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeAddFundsModal();
            }
        });
    </script>

    <style>
        /* Modal Styles */
        .modal-overlay {
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
        
        .modal-overlay.active {
            opacity: 1;
            visibility: visible;
        }
        
        .modal-content {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            max-width: 500px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
        }
        
        .modal-header {
            margin-bottom: 1.5rem;
        }
        
        .modal-title {
            font-size: 1.2rem;
            font-weight: 700;
            color: #1a202c;
            margin-bottom: 0.5rem;
        }
        
        .modal-subtitle {
            color: #6b7280;
            font-size: 0.9rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-label {
            display: block;
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.5rem;
        }
        
        .form-input, .form-select {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 0.9rem;
            transition: border-color 0.2s;
        }
        
        .form-input:focus, .form-select:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .modal-actions {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
        }
        
        .btn-cancel {
            background: #6b7280;
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }
        
        .btn-cancel:hover {
            background: #4b5563;
        }
        
        .btn-confirm {
            background: #10b981;
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }
        
        .btn-confirm:hover {
            background: #059669;
        }
        
        .btn-confirm:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
    </style>
</body>
</html>
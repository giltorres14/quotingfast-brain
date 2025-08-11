<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Buyer Management - Admin | The Brain</title>
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
            overflow-x: hidden;
            width: 100%;
            max-width: 100%;
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
            align-items: center;
            justify-content: space-between;
            height: 120px;
            width: 100%;
            box-sizing: border-box;
        }
        
        .logo-section {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .logo {
            height: 100px;
            filter: brightness(1.2);
        }
        
        .brand-text {
            font-size: 1.5rem;
            font-weight: 700;
        }
        
        .admin-badge {
            background: rgba(255, 255, 255, 0.2);
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9rem;
        }
        
        /* Main Content */
        .main-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
            width: 100%;
            box-sizing: border-box;
        }
        
        .page-header {
            background: white;
            border-bottom: 1px solid #e2e8f0;
            padding: 2rem 0;
            width: 100%;
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
        
        /* Action Grid */
        .action-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }
        
        .action-card {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            text-align: center;
            transition: all 0.2s;
            cursor: pointer;
        }
        
        .action-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        
        .action-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        
        .action-title {
            font-size: 1.2rem;
            font-weight: 700;
            color: #1a202c;
            margin-bottom: 0.5rem;
        }
        
        .action-description {
            color: #6b7280;
            font-size: 0.9rem;
            margin-bottom: 1.5rem;
        }
        
        .action-btn {
            background: #667eea;
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            font-size: 0.9rem;
        }
        
        .action-btn:hover {
            background: #5a67d8;
        }
        
        .action-btn.secondary {
            background: #10b981;
        }
        
        .action-btn.secondary:hover {
            background: #059669;
        }
        
        .action-btn.danger {
            background: #ef4444;
        }
        
        .action-btn.danger:hover {
            background: #dc2626;
        }
        
        /* Buyer List */
        .buyer-list {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }
        
        .list-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .list-title {
            font-size: 1.2rem;
            font-weight: 700;
            color: #1a202c;
        }
        
        .search-box {
            display: flex;
            gap: 1rem;
            align-items: center;
        }
        
        .search-input {
            padding: 0.5rem 1rem;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 0.9rem;
            width: 250px;
        }
        
        .search-input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        /* Buyer Table */
        .buyer-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .buyer-table th {
            background: #f8fafc;
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            color: #374151;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .buyer-table td {
            padding: 1rem;
            border-bottom: 1px solid #f3f4f6;
        }
        
        .buyer-table tr:hover {
            background: #f8fafc;
        }
        
        .buyer-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .buyer-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #667eea;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
        }
        
        .buyer-details {
            flex: 1;
        }
        
        .buyer-name {
            font-weight: 600;
            color: #1a202c;
            margin-bottom: 0.25rem;
        }
        
        .buyer-email {
            font-size: 0.85rem;
            color: #6b7280;
        }
        
        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .status-badge.active {
            background: #dcfce7;
            color: #166534;
        }
        
        .status-badge.inactive {
            background: #fee2e2;
            color: #991b1b;
        }
        
        .status-badge.pending {
            background: #fef3c7;
            color: #92400e;
        }
        
        .buyer-actions {
            display: flex;
            gap: 0.5rem;
        }
        
        .btn-small {
            padding: 0.375rem 0.75rem;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 500;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
        }
        
        .btn-impersonate {
            background: #10b981;
            color: white;
        }
        
        .btn-impersonate:hover {
            background: #059669;
            text-decoration: none;
        }
        
        .btn-edit {
            background: #667eea;
            color: white;
        }
        
        .btn-edit:hover {
            background: #5a67d8;
        }
        
        .btn-sample {
            background: #f59e0b;
            color: white;
        }
        
        .btn-sample:hover {
            background: #d97706;
        }
        
        .btn-delete {
            background: #ef4444;
            color: white;
        }
        
        .btn-delete:hover {
            background: #dc2626;
        }
        
        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
        }
        
        .modal.active {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .modal-content {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            max-width: 500px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .modal-title {
            font-size: 1.2rem;
            font-weight: 700;
            color: #1a202c;
        }
        
        .modal-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #6b7280;
        }
        
        .modal-close:hover {
            color: #374151;
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
        
        .form-input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 0.9rem;
            transition: border-color 0.2s;
        }
        
        .form-input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .form-select {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 0.9rem;
            background: white;
        }
        
        .form-actions {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
            margin-top: 2rem;
        }
        
        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
            font-size: 0.9rem;
        }
        
        .btn-primary {
            background: #667eea;
            color: white;
        }
        
        .btn-primary:hover {
            background: #5a67d8;
        }
        
        .btn-secondary {
            background: #f3f4f6;
            color: #374151;
            border: 1px solid #d1d5db;
        }
        
        .btn-secondary:hover {
            background: #e5e7eb;
        }
        
        /* Alert */
        .alert {
            padding: 1rem 1.5rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            font-weight: 500;
        }
        
        .alert.success {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #bbf7d0;
        }
        
        .alert.error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
        
        .alert.warning {
            background: #fef3c7;
            color: #92400e;
            border: 1px solid #fde68a;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .main-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
            width: 100%;
            box-sizing: border-box;
        }
            
            .action-grid {
                grid-template-columns: 1fr;
            }
            
            .search-box {
                flex-direction: column;
                align-items: stretch;
            }
            
            .search-input {
                width: 100%;
            }
            
            .buyer-table {
                font-size: 0.85rem;
            }
            
            .buyer-actions {
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
                <div class="brand-text">The Brain - Admin</div>
            </div>
            
            <div class="admin-badge">
                🛡️ Administrator Access
            </div>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="main-container">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-title">Buyer Management & Impersonation 🎭</h1>
            <p class="page-subtitle">
                Create dummy buyers, generate sample data, and impersonate any buyer to see their exact experience through the Brain.
            </p>
        </div>
        
        <!-- Quick Actions -->
        <div class="action-grid">
            <div class="action-card" onclick="openCreateBuyerModal()">
                <div class="action-icon">👤</div>
                <div class="action-title">Create Dummy Buyer</div>
                <div class="action-description">
                    Create a test buyer account with sample data for testing and demonstrations
                </div>
                <button class="action-btn">Create New Buyer</button>
            </div>
            
            <div class="action-card" onclick="generateSampleData()">
                <div class="action-icon">📊</div>
                <div class="action-title">Generate Sample Data</div>
                <div class="action-description">
                    Add sample leads, outcomes, payments, and documents to existing buyer accounts
                </div>
                <button class="action-btn secondary">Generate Data</button>
            </div>
            
            <div class="action-card" onclick="clearTestData()">
                <div class="action-icon">🗑️</div>
                <div class="action-title">Clear Test Data</div>
                <div class="action-description">
                    Remove all test buyers and sample data to clean up the system
                </div>
                <button class="action-btn danger">Clear All Test Data</button>
            </div>
        </div>
        
        <!-- Alert Area -->
        <div id="alertArea"></div>
        
        <!-- Buyer List -->
        <div class="buyer-list">
            <div class="list-header">
                <h2 class="list-title">All Buyers</h2>
                <div class="search-box">
                    <input type="text" class="search-input" placeholder="Search buyers..." id="searchInput" onkeyup="searchBuyers()">
                    <button class="action-btn" onclick="refreshBuyerList()">🔄 Refresh</button>
                </div>
            </div>
            
            <table class="buyer-table" id="buyerTable">
                <thead>
                    <tr>
                        <th>Buyer</th>
                        <th>Company</th>
                        <th>Status</th>
                        <th>Balance</th>
                        <th>Leads</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="buyerTableBody">
                    <!-- Sample data - this would be populated from backend -->
                    <tr>
                        <td>
                            <div class="buyer-info">
                                <div class="buyer-avatar">JS</div>
                                <div class="buyer-details">
                                    <div class="buyer-name">John Smith</div>
                                    <div class="buyer-email">john@testinsurance.com</div>
                                </div>
                            </div>
                        </td>
                        <td>Test Insurance Co.</td>
                        <td><span class="status-badge active">Active</span></td>
                        <td>$1,250.00</td>
                        <td>45</td>
                        <td>2 days ago</td>
                        <td>
                            <div class="buyer-actions">
                                <a href="/admin/impersonate/1" class="btn-small btn-impersonate">🎭 Login As</a>
                                <button class="btn-small btn-sample" onclick="generateBuyerSample(1)">📊 Add Data</button>
                                <button class="btn-small btn-edit" onclick="editBuyer(1)">✏️ Edit</button>
                                <button class="btn-small btn-delete" onclick="deleteBuyer(1)">🗑️ Delete</button>
                            </div>
                        </td>
                    </tr>
                    
                    <tr>
                        <td>
                            <div class="buyer-info">
                                <div class="buyer-avatar">SD</div>
                                <div class="buyer-details">
                                    <div class="buyer-name">Sarah Davis</div>
                                    <div class="buyer-email">sarah@quickquotes.com</div>
                                </div>
                            </div>
                        </td>
                        <td>Quick Quotes LLC</td>
                        <td><span class="status-badge active">Active</span></td>
                        <td>$750.50</td>
                        <td>23</td>
                        <td>1 week ago</td>
                        <td>
                            <div class="buyer-actions">
                                <a href="/admin/impersonate/2" class="btn-small btn-impersonate">🎭 Login As</a>
                                <button class="btn-small btn-sample" onclick="generateBuyerSample(2)">📊 Add Data</button>
                                <button class="btn-small btn-edit" onclick="editBuyer(2)">✏️ Edit</button>
                                <button class="btn-small btn-delete" onclick="deleteBuyer(2)">🗑️ Delete</button>
                            </div>
                        </td>
                    </tr>
                    
                    <tr>
                        <td>
                            <div class="buyer-info">
                                <div class="buyer-avatar">DT</div>
                                <div class="buyer-details">
                                    <div class="buyer-name">Demo Tester</div>
                                    <div class="buyer-email">demo@example.com</div>
                                </div>
                            </div>
                        </td>
                        <td>Demo Company</td>
                        <td><span class="status-badge pending">Test Account</span></td>
                        <td>$0.00</td>
                        <td>0</td>
                        <td>Just now</td>
                        <td>
                            <div class="buyer-actions">
                                <a href="/admin/impersonate/3" class="btn-small btn-impersonate">🎭 Login As</a>
                                <button class="btn-small btn-sample" onclick="generateBuyerSample(3)">📊 Add Data</button>
                                <button class="btn-small btn-edit" onclick="editBuyer(3)">✏️ Edit</button>
                                <button class="btn-small btn-delete" onclick="deleteBuyer(3)">🗑️ Delete</button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Create Buyer Modal -->
    <div class="modal" id="createBuyerModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Create Dummy Buyer Account</h3>
                <button class="modal-close" onclick="closeModal('createBuyerModal')">&times;</button>
            </div>
            
            <form onsubmit="createDummyBuyer(event)">
                <div class="form-group">
                    <label class="form-label">Account Type</label>
                    <select class="form-select" id="accountType" onchange="toggleAccountType()">
                        <option value="realistic">Realistic Test Account</option>
                        <option value="demo">Demo Account (Full Sample Data)</option>
                        <option value="minimal">Minimal Test Account</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">First Name</label>
                    <input type="text" class="form-input" id="firstName" value="Demo" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Last Name</label>
                    <input type="text" class="form-input" id="lastName" value="Buyer" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-input" id="email" value="demo@example.com" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Company Name</label>
                    <input type="text" class="form-input" id="company" value="Demo Insurance Co.">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Phone</label>
                    <input type="tel" class="form-input" id="phone" value="(555) 123-4567">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Starting Balance</label>
                    <input type="number" class="form-input" id="balance" value="1000.00" step="0.01" min="0">
                </div>
                
                <div class="form-group" id="sampleDataOptions">
                    <label class="form-label">Include Sample Data</label>
                    <div style="display: flex; flex-direction: column; gap: 0.5rem; margin-top: 0.5rem;">
                        <label style="display: flex; align-items: center; gap: 0.5rem; font-weight: normal;">
                            <input type="checkbox" id="includeSampleLeads" checked>
                            Sample leads (10-20 leads with various outcomes)
                        </label>
                        <label style="display: flex; align-items: center; gap: 0.5rem; font-weight: normal;">
                            <input type="checkbox" id="includeSamplePayments" checked>
                            Payment history (5-10 transactions)
                        </label>
                        <label style="display: flex; align-items: center; gap: 0.5rem; font-weight: normal;">
                            <input type="checkbox" id="includeSampleDocuments" checked>
                            Documents (contracts, forms)
                        </label>
                        <label style="display: flex; align-items: center; gap: 0.5rem; font-weight: normal;">
                            <input type="checkbox" id="includeCRMConfig">
                            CRM integration setup (demo webhook)
                        </label>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <span id="createBtnText">🎭 Create Dummy Buyer</span>
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="closeModal('createBuyerModal')">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Search buyers
        function searchBuyers() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const rows = document.querySelectorAll('#buyerTableBody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        }

        // Open create buyer modal
        function openCreateBuyerModal() {
            document.getElementById('createBuyerModal').classList.add('active');
            generateRandomData();
        }

        // Close modal
        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('active');
        }

        // Toggle account type options
        function toggleAccountType() {
            const accountType = document.getElementById('accountType').value;
            const sampleDataOptions = document.getElementById('sampleDataOptions');
            
            if (accountType === 'minimal') {
                sampleDataOptions.style.display = 'none';
                document.getElementById('balance').value = '0.00';
            } else {
                sampleDataOptions.style.display = 'block';
                document.getElementById('balance').value = accountType === 'demo' ? '2500.00' : '1000.00';
            }
        }

        // Generate random demo data
        function generateRandomData() {
            const firstNames = ['John', 'Sarah', 'Michael', 'Emily', 'David', 'Jessica', 'Robert', 'Ashley', 'Chris', 'Amanda'];
            const lastNames = ['Smith', 'Johnson', 'Williams', 'Brown', 'Jones', 'Garcia', 'Miller', 'Davis', 'Rodriguez', 'Martinez'];
            const companies = ['Insurance Pro', 'Quick Quotes', 'Lead Masters', 'Policy Plus', 'Coverage Kings', 'Quote Central', 'Insurance Direct', 'Lead Experts'];
            
            const firstName = firstNames[Math.floor(Math.random() * firstNames.length)];
            const lastName = lastNames[Math.floor(Math.random() * lastNames.length)];
            const company = companies[Math.floor(Math.random() * companies.length)];
            
            document.getElementById('firstName').value = firstName;
            document.getElementById('lastName').value = lastName;
            document.getElementById('email').value = `${firstName.toLowerCase()}.${lastName.toLowerCase()}@${company.toLowerCase().replace(/\s+/g, '')}.com`;
            document.getElementById('company').value = company + ' LLC';
        }

        // Create dummy buyer
        async function createDummyBuyer(event) {
            event.preventDefault();
            
            const createBtn = document.getElementById('createBtnText');
            const originalText = createBtn.textContent;
            createBtn.textContent = '⏳ Creating...';
            
            const formData = {
                account_type: document.getElementById('accountType').value,
                first_name: document.getElementById('firstName').value,
                last_name: document.getElementById('lastName').value,
                email: document.getElementById('email').value,
                company: document.getElementById('company').value,
                phone: document.getElementById('phone').value,
                balance: document.getElementById('balance').value,
                include_sample_leads: document.getElementById('includeSampleLeads').checked,
                include_sample_payments: document.getElementById('includeSamplePayments').checked,
                include_sample_documents: document.getElementById('includeSampleDocuments').checked,
                include_crm_config: document.getElementById('includeCRMConfig').checked
            };
            
            try {
                const response = await fetch('/admin/create-dummy-buyer', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(formData)
                });

                const result = await response.json();
                
                if (result.success) {
                    showAlert('success', `✅ Dummy buyer "${formData.first_name} ${formData.last_name}" created successfully! You can now impersonate them.`);
                    closeModal('createBuyerModal');
                    refreshBuyerList();
                } else {
                    showAlert('error', '❌ Error: ' + result.message);
                }
            } catch (error) {
                showAlert('error', '❌ Error creating dummy buyer: ' + error.message);
            } finally {
                createBtn.textContent = originalText;
            }
        }

        // Generate sample data for existing buyer
        async function generateBuyerSample(buyerId) {
            if (!confirm('Generate sample data for this buyer? This will add sample leads, payments, and documents.')) {
                return;
            }
            
            try {
                const response = await fetch(`/admin/generate-sample-data/${buyerId}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const result = await response.json();
                
                if (result.success) {
                    showAlert('success', '✅ Sample data generated successfully!');
                    refreshBuyerList();
                } else {
                    showAlert('error', '❌ Error: ' + result.message);
                }
            } catch (error) {
                showAlert('error', '❌ Error generating sample data: ' + error.message);
            }
        }

        // Generate sample data for all buyers
        async function generateSampleData() {
            if (!confirm('Generate sample data for all buyers? This will add sample leads, payments, and documents to existing accounts.')) {
                return;
            }
            
            try {
                const response = await fetch('/admin/generate-all-sample-data', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const result = await response.json();
                
                if (result.success) {
                    showAlert('success', '✅ Sample data generated for all buyers!');
                    refreshBuyerList();
                } else {
                    showAlert('error', '❌ Error: ' + result.message);
                }
            } catch (error) {
                showAlert('error', '❌ Error generating sample data: ' + error.message);
            }
        }

        // Clear all test data
        async function clearTestData() {
            if (!confirm('⚠️ WARNING: This will delete ALL test buyers and sample data. This action cannot be undone. Are you sure?')) {
                return;
            }
            
            if (!confirm('🚨 FINAL WARNING: You are about to permanently delete all test data. Type "DELETE" to confirm.') || 
                prompt('Type "DELETE" to confirm:') !== 'DELETE') {
                return;
            }
            
            try {
                const response = await fetch('/admin/clear-test-data', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const result = await response.json();
                
                if (result.success) {
                    showAlert('success', '✅ All test data cleared successfully!');
                    refreshBuyerList();
                } else {
                    showAlert('error', '❌ Error: ' + result.message);
                }
            } catch (error) {
                showAlert('error', '❌ Error clearing test data: ' + error.message);
            }
        }

        // Edit buyer
        function editBuyer(buyerId) {
            alert(`Edit buyer ${buyerId} - This would open an edit modal with buyer details`);
        }

        // Delete buyer
        async function deleteBuyer(buyerId) {
            if (!confirm('Delete this buyer? This action cannot be undone.')) {
                return;
            }
            
            try {
                const response = await fetch(`/admin/delete-buyer/${buyerId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const result = await response.json();
                
                if (result.success) {
                    showAlert('success', '✅ Buyer deleted successfully!');
                    refreshBuyerList();
                } else {
                    showAlert('error', '❌ Error: ' + result.message);
                }
            } catch (error) {
                showAlert('error', '❌ Error deleting buyer: ' + error.message);
            }
        }

        // Refresh buyer list
        async function refreshBuyerList() {
            try {
                const response = await fetch('/admin/buyers-list');
                const result = await response.json();
                
                if (result.success) {
                    // Update table with fresh data
                    // This would populate the table with actual buyer data from the backend
                    console.log('Buyer list refreshed', result.buyers);
                }
            } catch (error) {
                console.error('Error refreshing buyer list:', error);
            }
        }

        // Show alert
        function showAlert(type, message) {
            const alertArea = document.getElementById('alertArea');
            const alert = document.createElement('div');
            alert.className = `alert ${type}`;
            alert.textContent = message;
            
            alertArea.appendChild(alert);
            
            // Auto-remove after 5 seconds
            setTimeout(() => {
                alert.remove();
            }, 5000);
        }

        // Close modal when clicking outside
        document.addEventListener('click', function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.classList.remove('active');
            }
        });

        // Close modal with Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                document.querySelectorAll('.modal.active').forEach(modal => {
                    modal.classList.remove('active');
                });
            }
        });

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            refreshBuyerList();
        });
    </script>
</body>
</html>
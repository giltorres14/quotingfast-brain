<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Clear Test Leads - Brain Admin</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            background: white;
            border-radius: 20px;
            padding: 40px;
            max-width: 600px;
            width: 100%;
            box-shadow: 0 20px 60px rgba(0,0,0,0.2);
        }
        
        h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 32px;
        }
        
        .subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 16px;
        }
        
        .warning-box {
            background: #fef2f2;
            border: 2px solid #ef4444;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
        }
        
        .warning-title {
            color: #dc2626;
            font-weight: bold;
            font-size: 18px;
            margin-bottom: 10px;
        }
        
        .warning-text {
            color: #7f1d1d;
            line-height: 1.6;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: #f9fafb;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
        }
        
        .stat-value {
            font-size: 36px;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: #666;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .button-group {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }
        
        .btn {
            padding: 12px 24px;
            border-radius: 10px;
            border: none;
            font-weight: 600;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            flex: 1;
        }
        
        .btn-danger {
            background: #ef4444;
            color: white;
        }
        
        .btn-danger:hover {
            background: #dc2626;
            transform: translateY(-2px);
        }
        
        .btn-danger:disabled {
            background: #fca5a5;
            cursor: not-allowed;
            transform: none;
        }
        
        .btn-secondary {
            background: #e5e7eb;
            color: #333;
        }
        
        .btn-secondary:hover {
            background: #d1d5db;
            transform: translateY(-2px);
        }
        
        .checkbox-group {
            margin: 20px 0;
            padding: 15px;
            background: #f9fafb;
            border-radius: 10px;
        }
        
        .checkbox-group label {
            display: flex;
            align-items: center;
            cursor: pointer;
            user-select: none;
        }
        
        .checkbox-group input[type="checkbox"] {
            width: 20px;
            height: 20px;
            margin-right: 10px;
            cursor: pointer;
        }
        
        .loading {
            display: none;
            text-align: center;
            padding: 20px;
        }
        
        .loading.active {
            display: block;
        }
        
        .spinner {
            border: 3px solid #f3f3f3;
            border-top: 3px solid #667eea;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto 15px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .success-message {
            display: none;
            background: #d1fae5;
            border: 2px solid #10b981;
            color: #065f46;
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
            text-align: center;
        }
        
        .success-message.active {
            display: block;
        }
        
        .error-message {
            display: none;
            background: #fee2e2;
            border: 2px solid #ef4444;
            color: #991b1b;
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
        }
        
        .error-message.active {
            display: block;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🗑️ Clear Test Leads</h1>
        <p class="subtitle">Remove all test data before going live</p>
        
        <div class="warning-box">
            <div class="warning-title">⚠️ WARNING: This action cannot be undone!</div>
            <div class="warning-text">
                This will permanently delete ALL leads, test logs, and queue items from the database. 
                Only use this before going live in production.
            </div>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value" id="leadCount">{{ $leadCount ?? 0 }}</div>
                <div class="stat-label">Total Leads</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" id="testLogCount">{{ $testLogCount ?? 0 }}</div>
                <div class="stat-label">Test Logs</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" id="queueCount">{{ $queueCount ?? 0 }}</div>
                <div class="stat-label">Queue Items</div>
            </div>
        </div>
        
        <div class="checkbox-group">
            <label>
                <input type="checkbox" id="confirmDelete">
                <span>I understand this will delete ALL {{ $leadCount ?? 0 }} leads permanently</span>
            </label>
        </div>
        
        <div class="loading" id="loadingDiv">
            <div class="spinner"></div>
            <p>Clearing test data... Please wait...</p>
        </div>
        
        <div class="success-message" id="successMessage">
            ✅ All test data has been cleared successfully!<br>
            The database is now clean and ready for production.
        </div>
        
        <div class="error-message" id="errorMessage">
            ❌ An error occurred. Please check the logs.
        </div>
        
        <div class="button-group">
            <button class="btn btn-danger" id="clearButton" onclick="clearLeads()" disabled>
                Clear All Test Leads
            </button>
            <a href="/leads" class="btn btn-secondary">
                Cancel
            </a>
        </div>
    </div>
    
    <script>
        // Enable button only when checkbox is checked
        document.getElementById('confirmDelete').addEventListener('change', function() {
            document.getElementById('clearButton').disabled = !this.checked;
        });
        
        function clearLeads() {
            if (!confirm('Final confirmation: Delete all {{ $leadCount ?? 0 }} leads?')) {
                return;
            }
            
            // Disable button and show loading
            document.getElementById('clearButton').disabled = true;
            document.getElementById('confirmDelete').disabled = true;
            document.getElementById('loadingDiv').classList.add('active');
            
            // Make API call
            fetch('/admin/clear-test-leads', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById('loadingDiv').classList.remove('active');
                
                if (data.success) {
                    document.getElementById('successMessage').classList.add('active');
                    // Update counts to 0
                    document.getElementById('leadCount').textContent = '0';
                    document.getElementById('testLogCount').textContent = '0';
                    document.getElementById('queueCount').textContent = '0';
                    
                    // Redirect after 3 seconds
                    setTimeout(() => {
                        window.location.href = '/leads';
                    }, 3000);
                } else {
                    document.getElementById('errorMessage').classList.add('active');
                    document.getElementById('errorMessage').innerHTML = 
                        '❌ Error: ' + (data.message || 'Unknown error occurred');
                }
            })
            .catch(error => {
                document.getElementById('loadingDiv').classList.remove('active');
                document.getElementById('errorMessage').classList.add('active');
                document.getElementById('errorMessage').innerHTML = 
                    '❌ Network error: ' + error.message;
            });
        }
    </script>
</body>
</html>

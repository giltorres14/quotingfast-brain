<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>CSV Lead Upload - The Brain</title>
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
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            min-height: 100vh;
            color: #333;
        }
        
        .header {
            overflow-x: hidden;
            width: 100%;
            max-width: 100%;
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
            padding: 2rem 0;
            margin-bottom: 2rem;
        }
        
        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
            display: flex;
            align-items: center;
            gap: 2rem;
        }
        
        .header-logo img {
            height: 100px;
            width: auto;
            filter: brightness(1.2);
        }
        
        .header-text h1 {
            color: white;
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .header-text p {
            color: rgba(255,255,255,0.9);
            font-size: 1.1rem;
        }
        
        .nav-back {
            position: absolute;
            left: 2rem;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(255,255,255,0.2);
            color: white;
            text-decoration: none;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.2s;
        }
        
        .nav-back:hover {
            background: rgba(255,255,255,0.3);
            color: white;
            text-decoration: none;
        }
        
        .container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 0 2rem;
        }
        
        .upload-section {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            overflow: hidden;
            margin-bottom: 2rem;
        }
        
        .section-header {
            background: #f7fafc;
            padding: 1.5rem;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .section-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 0.5rem;
        }
        
        .section-subtitle {
            color: #718096;
            font-size: 0.9rem;
        }
        
        .upload-form {
            padding: 2rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-label {
            display: block;
            font-weight: 600;
            color: #4a5568;
            margin-bottom: 0.5rem;
        }
        
        .form-input, .form-select {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.2s;
        }
        
        .form-input:focus, .form-select:focus {
            outline: none;
            border-color: #3b82f6;
        }
        
        .file-upload-area {
            border: 2px dashed #cbd5e0;
            border-radius: 12px;
            padding: 3rem;
            text-align: center;
            background: #f8f9fa;
            transition: all 0.3s;
            cursor: pointer;
        }
        
        .file-upload-area:hover {
            border-color: #3b82f6;
            background: #f0f4ff;
        }
        
        .file-upload-area.dragover {
            border-color: #3b82f6;
            background: #e6f3ff;
            transform: scale(1.02);
        }
        
        .upload-icon {
            font-size: 3rem;
            color: #a0aec0;
            margin-bottom: 1rem;
        }
        
        .upload-text {
            font-size: 1.1rem;
            color: #4a5568;
            margin-bottom: 0.5rem;
        }
        
        .upload-hint {
            font-size: 0.9rem;
            color: #718096;
        }
        
        .btn {
            padding: 0.75rem 2rem;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            transition: all 0.2s;
        }
        
        .btn-primary {
            background: #3b82f6;
            color: white;
        }
        
        .btn-primary:hover {
            background: #5a67d8;
            transform: translateY(-1px);
        }
        
        .btn-primary:disabled {
            background: #a0aec0;
            cursor: not-allowed;
            transform: none;
        }
        
        .progress-bar {
            width: 100%;
            height: 8px;
            background: #e2e8f0;
            border-radius: 4px;
            overflow: hidden;
            margin: 1rem 0;
            display: none;
        }
        
        .progress-fill {
            height: 100%;
            background: #3b82f6;
            border-radius: 4px;
            transition: width 0.3s;
            width: 0%;
        }
        
        .alert {
            padding: 1rem 1.5rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }
        
        .alert-success {
            background: #f0fff4;
            border: 1px solid #68d391;
            color: #22543d;
        }
        
        .alert-error {
            background: #fed7d7;
            border: 1px solid #fc8181;
            color: #742a2a;
        }
        
        .alert-info {
            background: #ebf8ff;
            border: 1px solid #63b3ed;
            color: #2a4365;
        }
        
        .requirements {
            background: #f7fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .requirements h3 {
            color: #2d3748;
            margin-bottom: 1rem;
        }
        
        .requirements ul {
            color: #4a5568;
            margin-left: 1.5rem;
        }
        
        .requirements li {
            margin-bottom: 0.5rem;
        }
        
        .sample-format {
            background: #1a202c;
            color: #e2e8f0;
            padding: 1rem;
            border-radius: 8px;
            font-family: monospace;
            font-size: 0.9rem;
            overflow-x: auto;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
            margin: 1rem 0;
        }
        
        .stat-item {
            text-align: center;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 8px;
        }
        
        .stat-number {
            font-size: 1.5rem;
            font-weight: 700;
            color: #3b82f6;
        }
        
        .stat-label {
            font-size: 0.8rem;
            color: #718096;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .error-list {
            max-height: 200px;
            overflow-y: auto;
            background: #fed7d7;
            border-radius: 8px;
            padding: 1rem;
            margin-top: 1rem;
        }
        
        .error-item {
            font-size: 0.9rem;
            color: #742a2a;
            margin-bottom: 0.5rem;
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="header-content" style="position: relative;">
            <a href="/leads" class="nav-back">← Back to Leads</a>
            <div class="header-logo">
                <img src="https://quotingfast.com/whitelogo" alt="QuotingFast" onerror="this.src='https://quotingfast.com/whitelogo'; this.onerror=null;">
            </div>
            <div class="header-text">
                <h1>📁 CSV Lead Upload</h1>
                <p>Bulk import leads from CSV files with validation and processing</p>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Requirements Section -->
        <div class="requirements">
            <h3>📋 CSV Format Requirements</h3>
            <ul>
                <li><strong>Required columns:</strong> first_name, last_name (or name), phone</li>
                <li><strong>Optional columns:</strong> email, address, city, state, zip_code, zip</li>
                <li><strong>File size:</strong> Maximum 10MB</li>
                <li><strong>Format:</strong> CSV files only (.csv extension)</li>
                <li><strong>Encoding:</strong> UTF-8 recommended</li>
            </ul>
            
            <h4 style="margin-top: 1.5rem; margin-bottom: 0.5rem;">Sample CSV Format:</h4>
            <div class="sample-format">
first_name,last_name,phone,email,city,state,zip_code
John,Doe,555-123-4567,john@example.com,Miami,FL,33101
Jane,Smith,555-987-6543,jane@example.com,Orlando,FL,32801
            </div>
        </div>

        <!-- Upload Form -->
        <div class="upload-section">
            <div class="section-header">
                <div class="section-title">Upload CSV File</div>
                <div class="section-subtitle">Select your CSV file and configure import settings</div>
            </div>
            
            <div class="upload-form">
                <form id="uploadForm" enctype="multipart/form-data">
                    <div class="form-group">
                        <label class="form-label">Campaign ID (Optional)</label>
                        <input type="text" name="campaign_id" class="form-input" placeholder="e.g., 1024487">
                        <small style="color: #718096;">If provided, all imported leads will be assigned to this campaign</small>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Lead Type</label>
                        <select name="lead_type" class="form-select">
                            <option value="auto">Auto Insurance</option>
                            <option value="home">Home Insurance</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">CSV File</label>
                        <div class="file-upload-area" id="fileUploadArea">
                            <div class="upload-icon">📁</div>
                            <div class="upload-text">Drag and drop your CSV file here</div>
                            <div class="upload-hint">or click to browse files</div>
                            <input type="file" name="csv_file" id="csvFile" accept=".csv" style="display: none;">
                        </div>
                        <div id="selectedFile" style="margin-top: 1rem; display: none;">
                            <strong>Selected file:</strong> <span id="fileName"></span>
                            <button type="button" onclick="clearFile()" style="margin-left: 1rem; color: #e53e3e; background: none; border: none; cursor: pointer;">✕ Remove</button>
                        </div>
                    </div>
                    
                    <div class="progress-bar" id="progressBar">
                        <div class="progress-fill" id="progressFill"></div>
                    </div>
                    
                    <div id="alertContainer"></div>
                    
                    <button type="submit" class="btn btn-primary" id="uploadBtn">
                        📤 Upload and Process Leads
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        const fileUploadArea = document.getElementById('fileUploadArea');
        const csvFile = document.getElementById('csvFile');
        const selectedFile = document.getElementById('selectedFile');
        const fileName = document.getElementById('fileName');
        const uploadForm = document.getElementById('uploadForm');
        const uploadBtn = document.getElementById('uploadBtn');
        const progressBar = document.getElementById('progressBar');
        const progressFill = document.getElementById('progressFill');
        const alertContainer = document.getElementById('alertContainer');

        // File upload area click
        fileUploadArea.addEventListener('click', () => {
            csvFile.click();
        });

        // Drag and drop handlers
        fileUploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            fileUploadArea.classList.add('dragover');
        });

        fileUploadArea.addEventListener('dragleave', () => {
            fileUploadArea.classList.remove('dragover');
        });

        fileUploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            fileUploadArea.classList.remove('dragover');
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                handleFileSelection(files[0]);
            }
        });

        // File input change
        csvFile.addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                handleFileSelection(e.target.files[0]);
            }
        });

        function handleFileSelection(file) {
            if (file.type !== 'text/csv' && !file.name.endsWith('.csv')) {
                showAlert('error', 'Please select a CSV file');
                return;
            }
            
            if (file.size > 10 * 1024 * 1024) {
                showAlert('error', 'File size must be less than 10MB');
                return;
            }
            
            fileName.textContent = file.name + ' (' + formatFileSize(file.size) + ')';
            selectedFile.style.display = 'block';
            fileUploadArea.style.display = 'none';
        }

        function clearFile() {
            csvFile.value = '';
            selectedFile.style.display = 'none';
            fileUploadArea.style.display = 'block';
        }

        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        // Form submission
        uploadForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            if (!csvFile.files[0]) {
                showAlert('error', 'Please select a CSV file');
                return;
            }
            
            const formData = new FormData(uploadForm);
            
            // Show progress
            uploadBtn.disabled = true;
            uploadBtn.textContent = '⏳ Processing...';
            progressBar.style.display = 'block';
            progressFill.style.width = '0%';
            
            // Animate progress bar
            let progress = 0;
            const progressInterval = setInterval(() => {
                progress += 5;
                if (progress <= 90) {
                    progressFill.style.width = progress + '%';
                }
            }, 100);
            
            try {
                const response = await fetch('/lead-upload/process', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: formData
                });
                
                const result = await response.json();
                
                clearInterval(progressInterval);
                progressFill.style.width = '100%';
                
                setTimeout(() => {
                    progressBar.style.display = 'none';
                    uploadBtn.disabled = false;
                    uploadBtn.textContent = '📤 Upload and Process Leads';
                    
                    if (result.success) {
                        showAlert('success', result.message);
                        showStats(result.stats);
                        clearFile();
                        uploadForm.reset();
                    } else {
                        showAlert('error', result.message);
                    }
                }, 500);
                
            } catch (error) {
                clearInterval(progressInterval);
                progressBar.style.display = 'none';
                uploadBtn.disabled = false;
                uploadBtn.textContent = '📤 Upload and Process Leads';
                showAlert('error', 'Upload failed: ' + error.message);
            }
        });

        function showAlert(type, message) {
            alertContainer.innerHTML = `
                <div class="alert alert-${type}">
                    ${message}
                </div>
            `;
            
            setTimeout(() => {
                alertContainer.innerHTML = '';
            }, 5000);
        }

        function showStats(stats) {
            const statsHtml = `
                <div class="alert alert-info">
                    <h4>📊 Upload Results</h4>
                    <div class="stats-grid">
                        <div class="stat-item">
                            <div class="stat-number">${stats.total_rows}</div>
                            <div class="stat-label">Total Rows</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number">${stats.successful}</div>
                            <div class="stat-label">Successful</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number">${stats.errors}</div>
                            <div class="stat-label">Errors</div>
                        </div>
                    </div>
                    ${stats.error_details && stats.error_details.length > 0 ? `
                        <div class="error-list">
                            <strong>Error Details:</strong>
                            ${stats.error_details.map(error => `<div class="error-item">${error}</div>`).join('')}
                        </div>
                    ` : ''}
                </div>
            `;
            
            alertContainer.innerHTML = statsHtml;
        }
    </script>
</body>
</html>
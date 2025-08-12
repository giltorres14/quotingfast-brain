<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suraj Lead Upload Portal</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
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
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 600px;
            width: 100%;
            padding: 40px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .header h1 {
            color: #333;
            font-size: 28px;
            margin-bottom: 10px;
        }
        
        .header p {
            color: #666;
            font-size: 16px;
        }
        
        .upload-area {
            border: 3px dashed #ddd;
            border-radius: 15px;
            padding: 40px;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
            background: #fafafa;
        }
        
        .upload-area.dragover {
            border-color: #667eea;
            background: #f0f4ff;
        }
        
        .upload-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .upload-icon svg {
            width: 40px;
            height: 40px;
            fill: white;
        }
        
        .upload-text {
            color: #333;
            font-size: 18px;
            margin-bottom: 10px;
        }
        
        .upload-subtext {
            color: #999;
            font-size: 14px;
        }
        
        .file-input {
            display: none;
        }
        
        .btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 25px;
            font-size: 16px;
            cursor: pointer;
            transition: transform 0.2s;
            margin-top: 20px;
        }
        
        .btn:hover {
            transform: translateY(-2px);
        }
        
        .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        .file-list {
            margin-top: 30px;
        }
        
        .file-item {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .file-info {
            display: flex;
            align-items: center;
            flex: 1;
        }
        
        .file-icon {
            width: 40px;
            height: 40px;
            background: #e9ecef;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
        }
        
        .file-details h4 {
            color: #333;
            font-size: 14px;
            margin-bottom: 5px;
        }
        
        .file-details p {
            color: #999;
            font-size: 12px;
        }
        
        .file-status {
            padding: 5px 15px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-uploading {
            background: #cce5ff;
            color: #004085;
        }
        
        .status-success {
            background: #d4edda;
            color: #155724;
        }
        
        .status-error {
            background: #f8d7da;
            color: #721c24;
        }
        
        .progress-bar {
            width: 100%;
            height: 6px;
            background: #e9ecef;
            border-radius: 3px;
            margin-top: 10px;
            overflow: hidden;
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            transition: width 0.3s ease;
        }
        
        .stats {
            margin-top: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
        }
        
        .stats h3 {
            color: #333;
            font-size: 16px;
            margin-bottom: 15px;
        }
        
        .stat-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        
        .stat-label {
            color: #666;
            font-size: 14px;
        }
        
        .stat-value {
            color: #333;
            font-weight: 600;
            font-size: 14px;
        }
        
        .alert {
            padding: 12px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📤 Suraj Lead Upload Portal</h1>
            <p>Upload CSV files to import leads into the system</p>
        </div>
        
        <div id="alertContainer"></div>
        
        <div class="upload-area" id="uploadArea">
            <div class="upload-icon">
                <svg viewBox="0 0 24 24">
                    <path d="M9 16.2L4.8 12l-1.4 1.4L9 19 21 7l-1.4-1.4L9 16.2z"/>
                </svg>
            </div>
            <div class="upload-text">Drop CSV files here or click to browse</div>
            <div class="upload-subtext">Supports .csv files up to 50MB</div>
            <input type="file" id="fileInput" class="file-input" accept=".csv" multiple>
        </div>
        
        <div class="file-list" id="fileList"></div>
        
        <button class="btn" id="uploadBtn" style="display: none; width: 100%;">
            Upload Files
        </button>
        
        <div class="stats" id="stats" style="display: none;">
            <h3>Import Statistics</h3>
            <div class="stat-row">
                <span class="stat-label">Files Uploaded:</span>
                <span class="stat-value" id="statFiles">0</span>
            </div>
            <div class="stat-row">
                <span class="stat-label">Total Rows:</span>
                <span class="stat-value" id="statRows">0</span>
            </div>
            <div class="stat-row">
                <span class="stat-label">New Leads:</span>
                <span class="stat-value" id="statNew">0</span>
            </div>
            <div class="stat-row">
                <span class="stat-label">Updated:</span>
                <span class="stat-value" id="statUpdated">0</span>
            </div>
            <div class="stat-row">
                <span class="stat-label">Duplicates:</span>
                <span class="stat-value" id="statDuplicates">0</span>
            </div>
        </div>
    </div>
    
    <script>
        const uploadArea = document.getElementById('uploadArea');
        const fileInput = document.getElementById('fileInput');
        const fileList = document.getElementById('fileList');
        const uploadBtn = document.getElementById('uploadBtn');
        const alertContainer = document.getElementById('alertContainer');
        const stats = document.getElementById('stats');
        
        let selectedFiles = [];
        
        // Click to browse
        uploadArea.addEventListener('click', () => {
            fileInput.click();
        });
        
        // File selection
        fileInput.addEventListener('change', (e) => {
            handleFiles(e.target.files);
        });
        
        // Drag and drop
        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.classList.add('dragover');
        });
        
        uploadArea.addEventListener('dragleave', () => {
            uploadArea.classList.remove('dragover');
        });
        
        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.classList.remove('dragover');
            handleFiles(e.dataTransfer.files);
        });
        
        function handleFiles(files) {
            selectedFiles = Array.from(files).filter(file => file.name.endsWith('.csv'));
            
            if (selectedFiles.length === 0) {
                showAlert('Please select CSV files only', 'error');
                return;
            }
            
            displayFiles();
            uploadBtn.style.display = 'block';
        }
        
        function displayFiles() {
            fileList.innerHTML = '';
            
            selectedFiles.forEach((file, index) => {
                const fileItem = document.createElement('div');
                fileItem.className = 'file-item';
                fileItem.innerHTML = `
                    <div class="file-info">
                        <div class="file-icon">📄</div>
                        <div class="file-details">
                            <h4>${file.name}</h4>
                            <p>${formatFileSize(file.size)}</p>
                        </div>
                    </div>
                    <div class="file-status status-pending" id="status-${index}">
                        Pending
                    </div>
                `;
                fileList.appendChild(fileItem);
            });
        }
        
        uploadBtn.addEventListener('click', async () => {
            uploadBtn.disabled = true;
            uploadBtn.textContent = 'Uploading...';
            
            for (let i = 0; i < selectedFiles.length; i++) {
                await uploadFile(selectedFiles[i], i);
            }
            
            uploadBtn.textContent = 'Upload Complete';
            showAlert('All files processed successfully!', 'success');
            stats.style.display = 'block';
        });
        
        async function uploadFile(file, index) {
            const statusEl = document.getElementById(`status-${index}`);
            statusEl.className = 'file-status status-uploading';
            statusEl.textContent = 'Uploading...';
            
            const formData = new FormData();
            formData.append('file', file);
            formData.append('duplicate_rule', 'lqf'); // Use LQF duplicate rules
            
            try {
                const response = await fetch('/suraj/upload', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    statusEl.className = 'file-status status-success';
                    statusEl.textContent = 'Imported';
                    updateStats(result.stats);
                } else {
                    statusEl.className = 'file-status status-error';
                    statusEl.textContent = 'Failed';
                }
            } catch (error) {
                statusEl.className = 'file-status status-error';
                statusEl.textContent = 'Error';
                console.error('Upload error:', error);
            }
        }
        
        function updateStats(newStats) {
            document.getElementById('statFiles').textContent = 
                parseInt(document.getElementById('statFiles').textContent) + 1;
            document.getElementById('statRows').textContent = 
                parseInt(document.getElementById('statRows').textContent) + (newStats.rows || 0);
            document.getElementById('statNew').textContent = 
                parseInt(document.getElementById('statNew').textContent) + (newStats.imported || 0);
            document.getElementById('statUpdated').textContent = 
                parseInt(document.getElementById('statUpdated').textContent) + (newStats.updated || 0);
            document.getElementById('statDuplicates').textContent = 
                parseInt(document.getElementById('statDuplicates').textContent) + (newStats.duplicates || 0);
        }
        
        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
        }
        
        function showAlert(message, type) {
            const alert = document.createElement('div');
            alert.className = `alert alert-${type}`;
            alert.textContent = message;
            alertContainer.appendChild(alert);
            
            setTimeout(() => {
                alert.remove();
            }, 5000);
        }
    </script>
</body>
</html>

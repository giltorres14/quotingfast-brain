<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API & Webhooks Directory - The Brain</title>
    <link rel="icon" type="image/png" href="/favicon.png">
    <link rel="shortcut icon" href="/favicon.ico">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: #2d3748;
        }
        
        .container { max-width: 1400px; margin: 0 auto; padding: 2rem; }
        
        /* Header */
        .header {
            overflow-x: hidden;
            width: 100%;
            max-width: 100%;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .header-content {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }
        .header-logo {
            flex-shrink: 0;
        }
        .logo-image {
            height: 100px;
            width: auto;
        }
        .header-text h1 {
            font-size: 2.5rem;
            font-weight: 800;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.5rem;
        }
        .header-text p {
            font-size: 1.1rem;
            color: #64748b;
        }
        
        /* Navigation */
        .nav-links {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
        }
        .nav-link {
            background: rgba(255, 255, 255, 0.9);
            color: #667eea;
            padding: 0.75rem 1.5rem;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.2s ease;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        .nav-link:hover {
            background: white;
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }
        
        /* Statistics Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 3rem;
        }
        .stat-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            padding: 1.5rem;
            text-align: center;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: transform 0.2s ease;
        }
        .stat-card:hover {
            transform: translateY(-2px);
        }
        .stat-number {
            font-size: 2.5rem;
            font-weight: 800;
            color: #1e293b;
            margin-bottom: 0.5rem;
        }
        .stat-label {
            font-size: 0.875rem;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-weight: 600;
        }
        
        /* Sections */
        .section {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .section-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .section-subtitle {
            color: #64748b;
            margin-bottom: 2rem;
        }
        
        /* Category Groups */
        .category-group {
            margin-bottom: 2rem;
        }
        .category-group:last-child {
            margin-bottom: 0;
        }
        .category-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 1rem;
            padding-left: 1rem;
            border-left: 4px solid #667eea;
            text-transform: capitalize;
        }
        
        /* Endpoint Grid */
        .endpoints-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 1.5rem;
        }
        .endpoint-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            overflow: hidden;
            transition: all 0.2s ease;
        }
        .endpoint-card:hover {
            background: #f1f5f9;
            border-color: #cbd5e1;
            transform: translateY(-1px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        
        /* Endpoint Header */
        .endpoint-header {
            padding: 1.5rem;
            border-bottom: 1px solid #e5e7eb;
        }
        .endpoint-name {
            font-size: 1.125rem;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 0.5rem;
        }
        .endpoint-path {
            font-family: 'JetBrains Mono', 'Fira Code', Consolas, monospace;
            font-size: 0.875rem;
            color: #3730a3;
            background: #e0e7ff;
            padding: 0.25rem 0.5rem;
            border-radius: 6px;
            margin-bottom: 0.75rem;
            display: inline-block;
            word-break: break-all;
        }
        .endpoint-meta {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            flex-wrap: wrap;
        }
        
        /* Method Badge */
        .method-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .method-get { background: #dbeafe; color: #1d4ed8; }
        .method-post { background: #fef3c7; color: #92400e; }
        .method-put { background: #fed7d7; color: #c53030; }
        .method-delete { background: #fee2e2; color: #dc2626; }
        .method-patch { background: #e9d5ff; color: #7c3aed; }
        
        /* Status Badge */
        .status-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .status-active { background: #dcfce7; color: #166534; }
        .status-testing { background: #fef3c7; color: #92400e; }
        .status-inactive { background: #fee2e2; color: #dc2626; }
        
        /* Endpoint Body */
        .endpoint-body {
            padding: 1.5rem;
        }
        .endpoint-description {
            color: #64748b;
            margin-bottom: 1rem;
            line-height: 1.5;
        }
        .endpoint-features {
            list-style: none;
            margin-bottom: 1rem;
        }
        .endpoint-features li {
            padding: 0.25rem 0;
            color: #4b5563;
            font-size: 0.875rem;
            position: relative;
            padding-left: 1.5rem;
        }
        .endpoint-features li:before {
            content: "✓";
            color: #10b981;
            font-weight: bold;
            position: absolute;
            left: 0;
        }
        
        /* Action Buttons */
        .endpoint-actions {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
        .btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 8px;
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            transition: all 0.2s ease;
        }
        .btn-test {
            background: #10b981;
            color: white;
        }
        .btn-test:hover {
            background: #059669;
            transform: translateY(-1px);
        }
        .btn-copy {
            background: #6b7280;
            color: white;
        }
        .btn-copy:hover {
            background: #4b5563;
        }
        .btn-copy.copied {
            background: #10b981;
        }
        
        /* Management Section */
        .management-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-align: center;
            padding: 2rem;
        }
        .management-section h3 {
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }
        .btn-manage {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.3);
            padding: 0.75rem 1.5rem;
            font-size: 1rem;
            font-weight: 600;
        }
        .btn-manage:hover {
            background: rgba(255, 255, 255, 0.3);
            border-color: rgba(255, 255, 255, 0.5);
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .container { padding: 1rem; }
            .header-content { flex-direction: column; text-align: center; }
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
            .endpoints-grid { grid-template-columns: 1fr; }
            .nav-links { flex-wrap: wrap; }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="header-content">
                <div class="header-logo">
                    <img src="https://quotingfast.com/whitelogo" alt="QuotingFast" class="logo-image" onerror="this.style.display='none'; this.nextElementSibling.style.display='inline';">
                    <span style="display: none; font-weight: 800; color: white; font-size: 1.5rem;">QuotingFast</span>
                </div>
                <div class="header-text">
                    <h1>🧠 API & Webhooks Directory</h1>
                    <p>Complete integration hub for The Brain lead management system</p>
                </div>
            </div>
        </div>

        <!-- Navigation -->
        <div class="nav-links">
            <a href="/admin" class="nav-link">← Back to Dashboard</a>
            <a href="/leads" class="nav-link">📊 Leads</a>
            <a href="/analytics" class="nav-link">📈 Analytics</a>
            <a href="#manage" class="nav-link">⚙️ Manage</a>
        </div>

        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number">{{ $stats['total_webhooks'] ?? 0 }}</div>
                <div class="stat-label">Webhooks</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">{{ $stats['total_apis'] ?? 0 }}</div>
                <div class="stat-label">API Endpoints</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">{{ $stats['total_tests'] ?? 0 }}</div>
                <div class="stat-label">Test Utilities</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">{{ $stats['active_endpoints'] ?? 0 }}</div>
                <div class="stat-label">Active</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">{{ $stats['total_leads'] ?? 0 }}</div>
                <div class="stat-label">Total Leads</div>
            </div>
        </div>

        <!-- Webhooks Section -->
        <div class="section">
            <div class="section-title">
                🎣 Webhook Endpoints
            </div>
            <div class="section-subtitle">
                Data intake points for external systems
            </div>
            
            @forelse($webhooks as $category => $categoryWebhooks)
                <div class="category-group">
                    <div class="category-title">{{ str_replace('_', ' ', $category) }}</div>
                    <div class="endpoints-grid">
                        @foreach($categoryWebhooks as $webhook)
                            <div class="endpoint-card">
                                <div class="endpoint-header">
                                    <div class="endpoint-name">{{ $webhook->name }}</div>
                                    <div class="endpoint-path">{{ $webhook->endpoint }}</div>
                                    <div class="endpoint-meta">
                                        <span class="method-badge method-{{ strtolower($webhook->method) }}">{{ $webhook->method }}</span>
                                        <span class="status-badge status-{{ $webhook->status }}">{{ $webhook->status }}</span>
                                    </div>
                                </div>
                                <div class="endpoint-body">
                                    <div class="endpoint-description">{{ $webhook->description }}</div>
                                    @if($webhook->features)
                                        <ul class="endpoint-features">
                                            @foreach($webhook->features as $feature)
                                                <li>{{ $feature }}</li>
                                            @endforeach
                                        </ul>
                                    @endif
                                    <div class="endpoint-actions">
                                        @if($webhook->test_url)
                                            <a href="{{ $webhook->test_url }}" class="btn btn-test" target="_blank">🧪 Test</a>
                                        @endif
                                        <button class="btn btn-copy" onclick="copyToClipboard('{{ $webhook->full_url }}', this)">📋 Copy URL</button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <p>No webhooks configured.</p>
            @endforelse
        </div>

        <!-- API Endpoints Section -->
        <div class="section">
            <div class="section-title">
                🔌 API Endpoints
            </div>
            <div class="section-subtitle">
                Programmatic access to system data and functions
            </div>
            
            @forelse($apis as $category => $categoryApis)
                <div class="category-group">
                    <div class="category-title">{{ str_replace('_', ' ', $category) }}</div>
                    <div class="endpoints-grid">
                        @foreach($categoryApis as $api)
                            <div class="endpoint-card">
                                <div class="endpoint-header">
                                    <div class="endpoint-name">{{ $api->name }}</div>
                                    <div class="endpoint-path">{{ $api->endpoint }}</div>
                                    <div class="endpoint-meta">
                                        <span class="method-badge method-{{ strtolower($api->method) }}">{{ $api->method }}</span>
                                        <span class="status-badge status-{{ $api->status }}">{{ $api->status }}</span>
                                    </div>
                                </div>
                                <div class="endpoint-body">
                                    <div class="endpoint-description">{{ $api->description }}</div>
                                    @if($api->features)
                                        <ul class="endpoint-features">
                                            @foreach($api->features as $feature)
                                                <li>{{ $feature }}</li>
                                            @endforeach
                                        </ul>
                                    @endif
                                    <div class="endpoint-actions">
                                        @if($api->test_url)
                                            <a href="{{ $api->test_url }}" class="btn btn-test" target="_blank">🧪 Test</a>
                                        @endif
                                        <button class="btn btn-copy" onclick="copyToClipboard('{{ $api->full_url }}', this)">📋 Copy URL</button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <p>No API endpoints configured.</p>
            @endforelse
        </div>

        <!-- Test Utilities Section -->
        <div class="section">
            <div class="section-title">
                🧪 Test & Debug Utilities
            </div>
            <div class="section-subtitle">
                Tools for testing integrations and debugging issues
            </div>
            
            @forelse($tests as $category => $categoryTests)
                <div class="category-group">
                    <div class="category-title">{{ str_replace('_', ' ', $category) }}</div>
                    <div class="endpoints-grid">
                        @foreach($categoryTests as $test)
                            <div class="endpoint-card">
                                <div class="endpoint-header">
                                    <div class="endpoint-name">{{ $test->name }}</div>
                                    <div class="endpoint-path">{{ $test->endpoint }}</div>
                                    <div class="endpoint-meta">
                                        <span class="method-badge method-{{ strtolower($test->method) }}">{{ $test->method }}</span>
                                        <span class="status-badge status-{{ $test->status }}">{{ $test->status }}</span>
                                    </div>
                                </div>
                                <div class="endpoint-body">
                                    <div class="endpoint-description">{{ $test->description }}</div>
                                    @if($test->features)
                                        <ul class="endpoint-features">
                                            @foreach($test->features as $feature)
                                                <li>{{ $feature }}</li>
                                            @endforeach
                                        </ul>
                                    @endif
                                    <div class="endpoint-actions">
                                        @if($test->test_url)
                                            <a href="{{ $test->test_url }}" class="btn btn-test" target="_blank">🧪 Run Test</a>
                                        @endif
                                        <button class="btn btn-copy" onclick="copyToClipboard('{{ $test->full_url }}', this)">📋 Copy URL</button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <p>No test utilities configured.</p>
            @endforelse
        </div>

        <!-- Management Section -->
        <div class="section management-section" id="manage">
            <h3>🔧 Endpoint Management</h3>
            <p style="margin-bottom: 1.5rem;">Add, edit, or remove API endpoints and webhooks</p>
            <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                <a href="/api-directory/create?type=webhook" class="btn btn-manage">+ Add Webhook</a>
                <a href="/api-directory/create?type=api" class="btn btn-manage">+ Add API Endpoint</a>
                <a href="/api-directory/create?type=test" class="btn btn-manage">+ Add Test Utility</a>
                <a href="/api-directory/manage" class="btn btn-manage">📝 Manage All</a>
            </div>
        </div>
    </div>

    <script>
        function copyToClipboard(text, button) {
            navigator.clipboard.writeText(text).then(function() {
                const originalText = button.textContent;
                button.textContent = '✅ Copied!';
                button.classList.add('copied');
                
                setTimeout(() => {
                    button.textContent = originalText;
                    button.classList.remove('copied');
                }, 2000);
            }).catch(function(err) {
                console.error('Could not copy text: ', err);
                button.textContent = '❌ Failed';
                setTimeout(() => {
                    button.textContent = '📋 Copy URL';
                }, 2000);
            });
        }
    </script>
</body>
</html>
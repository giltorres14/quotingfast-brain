<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Leads - The Brain</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f8fafc;
            color: #1a202c;
            line-height: 1.6;
        }
        
        /* Header Navigation */
        .navbar {
            background: #4f46e5;
            color: white;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 100;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .nav-container {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 2rem;
            height: 70px;
        }
        
        .nav-brand {
            font-size: 1.2rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            color: white;
            text-decoration: none;
        }
        
        .brand-logo {
            width: 32px;
            height: 32px;
            background: #4f46e5;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            color: white;
            border: 2px solid rgba(255,255,255,0.2);
        }
        
        .nav-menu {
            display: flex;
            list-style: none;
            gap: 2rem;
        }
        
        .nav-link {
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            padding: 0.75rem 1.25rem;
            border-radius: 8px;
            transition: all 0.2s ease;
            font-weight: 500;
        }
        
        .nav-link:hover {
            background: rgba(255,255,255,0.1);
            color: white;
        }
        
        .nav-link.active {
            background: rgba(255,255,255,0.15);
            color: white;
            font-weight: 600;
        }
        
        /* Page Header */
        .page-header {
            background: white;
            border-bottom: 1px solid #e2e8f0;
            padding: 2rem 0;
        }
        
        .page-header-content {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 2rem;
        }
        
        .page-title {
            font-size: 2rem;
            font-weight: 700;
            color: #1a202c;
            margin-bottom: 0.5rem;
        }
        
        .page-subtitle {
            color: #718096;
            font-size: 1.1rem;
        }
        
        /* Main Container */
        .main-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        /* Search Section */
        .search-section {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .search-grid {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr auto auto;
            gap: 1rem;
            align-items: end;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
        }
        
        .form-label {
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }
        
        .form-input, .form-select {
            padding: 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 0.95rem;
            transition: all 0.2s ease;
        }
        
        .form-input:focus, .form-select:focus {
            outline: none;
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }
        
        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.2s ease;
            border: none;
            cursor: pointer;
            font-size: 0.95rem;
        }
        
        .btn-primary {
            background: #4f46e5;
            color: white;
        }
        
        .btn-primary:hover {
            background: #4338ca;
            transform: translateY(-1px);
        }
        
        .btn-secondary {
            background: #f7fafc;
            color: #4a5568;
            border: 1px solid #e2e8f0;
        }
        
        .btn-secondary:hover {
            background: #edf2f7;
        }
    </style>
</head>
<body>
    <!-- Navigation Header -->
    <nav class="navbar">
        <div class="nav-container">
            <a href="/admin" class="nav-brand">
                <div class="brand-logo">B</div>
                <span>The Brain</span>
            </a>
            <ul class="nav-menu">
                <li><a href="/admin" class="nav-link">Dashboard</a></li>
                <li><a href="/leads" class="nav-link active">Leads</a></li>
                <li><a href="#messaging" class="nav-link" onclick="alert('SMS/Messaging feature coming soon!')">Messaging</a></li>
                <li><a href="/analytics" class="nav-link">Analytics</a></li>
                <li><a href="#campaigns" class="nav-link" onclick="alert('Campaign management feature coming soon!')">Campaigns</a></li>
                <li><a href="#settings" class="nav-link" onclick="alert('Settings feature coming soon!')">Settings</a></li>
            </ul>
        </div>
    </nav>

    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-content">
            <h1 class="page-title">All Leads</h1>
            <p class="page-subtitle">Manage and track your auto insurance leads</p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-container">
        <!-- Search and Filters -->
        <div class="search-section">
            <form method="GET" action="/leads">
                <div class="search-grid">
                    <div class="form-group">
                        <label class="form-label">Search</label>
                        <input type="text" name="search" class="form-input" 
                               placeholder="Name, phone, or email" 
                               value="{{ $search ?? '' }}">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="all">All Statuses</option>
                            @foreach($statuses as $statusOption)
                                <option value="{{ $statusOption }}" 
                                        {{ ($status ?? '') === $statusOption ? 'selected' : '' }}>
                                    {{ ucfirst($statusOption) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Source</label>
                        <select name="source" class="form-select">
                            <option value="all">All Sources</option>
                            @foreach($sources as $sourceOption)
                                <option value="{{ $sourceOption }}" 
                                        {{ ($source ?? '') === $sourceOption ? 'selected' : '' }}>
                                    {{ ucfirst($sourceOption) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="/leads" class="btn btn-secondary">Clear</a>
                </div>
            </form>
        </div>

        <!-- Test Message -->
        @if(isset($isTestMode) && $isTestMode)
            <div style="background: #fef3c7; padding: 1rem; border-radius: 8px; margin-bottom: 2rem; color: #92400e;">
                <strong>Test Mode:</strong> Showing sample data. Database connection may be unavailable.
            </div>
        @endif

        <!-- Coming Soon Message -->
        <div style="background: white; padding: 3rem; border-radius: 12px; text-align: center; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <div style="font-size: 4rem; margin-bottom: 1rem; opacity: 0.5;">🚧</div>
            <h2 style="color: #1a202c; margin-bottom: 1rem;">Beautiful Leads Cards Coming Soon!</h2>
            <p style="color: #718096; margin-bottom: 2rem;">
                We're building an amazing card-based leads interface with status bubbles, SMS indicators, and all the features you requested.
                For now, you can access individual leads through the agent interface.
            </p>
            <a href="/agent/lead/BRAIN_TEST_RINGBA" class="btn btn-primary">
                View Sample Lead (Agent Interface)
            </a>
        </div>
    </div>
</body>
</html>
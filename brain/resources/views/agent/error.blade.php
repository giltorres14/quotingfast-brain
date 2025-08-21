<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Error</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f3f4f6;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .error-container {
            background: white;
            border-radius: 12px;
            padding: 40px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            max-width: 500px;
            text-align: center;
        }
        .error-icon {
            font-size: 48px;
            margin-bottom: 20px;
        }
        h1 {
            color: #ef4444;
            font-size: 24px;
            margin-bottom: 10px;
        }
        p {
            color: #6b7280;
            margin-bottom: 20px;
            line-height: 1.5;
        }
        .lead-id {
            font-weight: 600;
            color: #374151;
            margin: 20px 0;
        }
        .retry-btn {
            background: #3b82f6;
            color: white;
            border: none;
            padding: 10px 24px;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .retry-btn:hover {
            background: #2563eb;
        }
        .error-details {
            margin-top: 20px;
            padding: 15px;
            background: #f9fafb;
            border-radius: 6px;
            font-size: 12px;
            color: #6b7280;
            text-align: left;
            font-family: monospace;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-icon">⚠️</div>
        <h1>System Error</h1>
        <p>An error occurred while loading the lead information.</p>
        
        <div class="lead-id">Lead ID: {{ $leadId ?? 'Unknown' }}</div>
        
        <a href="javascript:location.reload()" class="retry-btn">Retry</a>
        
        @if(isset($error))
        <div class="error-details">
            Error: {{ $error }}
        </div>
        @endif
    </div>
</body>
</html>
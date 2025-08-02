<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f8f9fa;
            margin: 0;
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
        
        .container {
            background: white;
            border-radius: 8px;
            padding: 40px;
            text-align: center;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            max-width: 500px;
        }
        
        .icon {
            font-size: 48px;
            margin-bottom: 16px;
        }
        
        h1 {
            color: #dc3545;
            margin-bottom: 8px;
        }
        
        p {
            color: #6c757d;
            margin-bottom: 20px;
        }
        
        .error-details {
            background: #f8f9fa;
            padding: 12px;
            border-radius: 4px;
            font-family: monospace;
            color: #495057;
            font-size: 12px;
            text-align: left;
            margin-top: 20px;
        }
        
        .retry-btn {
            background: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 20px;
        }
        
        .retry-btn:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">⚠️</div>
        <h1>System Error</h1>
        <p>An error occurred while loading the lead information.</p>
        
        @if(isset($leadId))
        <p><strong>Lead ID:</strong> {{ $leadId }}</p>
        @endif
        
        <button class="retry-btn" onclick="location.reload()">Retry</button>
        
        <div class="error-details">
            Error: {{ $error }}
        </div>
    </div>
</body>
</html>
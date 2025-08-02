<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lead Not Found</title>
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
            max-width: 400px;
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
        
        .lead-id {
            background: #f8f9fa;
            padding: 8px 12px;
            border-radius: 4px;
            font-family: monospace;
            color: #495057;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">🔍</div>
        <h1>Lead Not Found</h1>
        <p>The requested lead could not be found in the system.</p>
        <div class="lead-id">Lead ID: {{ $leadId }}</div>
    </div>
</body>
</html>
<?php
// Simple test page to verify basic functionality

echo "<!DOCTYPE html>
<html>
<head>
    <title>Simple Test Page</title>
</head>
<body>
    <h1>Simple Test Page</h1>
    <p>This is a basic PHP page to test if the server is working.</p>
    <p>Current time: " . date('Y-m-d H:i:s') . "</p>
    <h2>Testing Links:</h2>
    <ul>
        <li><a href='/leads'>Leads Page (Working)</a></li>
        <li><a href='/admin'>Admin Page (Working)</a></li>
        <li><a href='/admin/control-center'>Control Center (Testing...)</a></li>
        <li><a href='/admin/lead-flow'>Lead Flow (Testing...)</a></li>
        <li><a href='/diagnostics'>Diagnostics (Working)</a></li>
    </ul>
</body>
</html>";

<?php
// This script will be accessible via web and execute the SQL file on the server

header('Content-Type: text/plain');

echo "=== VICI UPDATE EXECUTOR ===\n\n";

// Check if SQL file was uploaded
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['sqlfile'])) {
    $uploadedFile = $_FILES['sqlfile']['tmp_name'];
    
    if (!file_exists($uploadedFile)) {
        die("Error: No file uploaded\n");
    }
    
    echo "File uploaded: " . $_FILES['sqlfile']['name'] . "\n";
    echo "Size: " . round($_FILES['sqlfile']['size'] / 1024 / 1024, 2) . " MB\n\n";
    
    // Save to temp location
    $tempFile = '/tmp/vici_update_' . time() . '.sql';
    move_uploaded_file($uploadedFile, $tempFile);
    
    echo "Executing SQL file on Vici server...\n\n";
    
    // Execute via curl to proxy
    $ch = curl_init('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        'command' => "mysql -u root Q6hdjl67GRigMofv < $tempFile 2>&1 | tail -20"
    ]));
    curl_setopt($ch, CURLOPT_TIMEOUT, 300); // 5 minutes timeout
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        $result = json_decode($response, true);
        echo "Execution result:\n";
        echo $result['output'] ?? 'No output';
        echo "\n\n";
        
        // Verify update count
        $ch = curl_init('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'command' => "mysql -u root Q6hdjl67GRigMofv -e \"SELECT COUNT(*) as total FROM vicidial_list WHERE vendor_lead_code REGEXP '^[0-9]{13}\$' AND list_id IN (6010,6011,6012,6013,6014,6015);\" 2>&1"
        ]));
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        if ($response) {
            $result = json_decode($response, true);
            echo "Verification:\n";
            echo $result['output'] ?? 'No output';
        }
    } else {
        echo "Error: Failed to execute (HTTP $httpCode)\n";
    }
    
    // Cleanup
    unlink($tempFile);
    
} else {
    // Show upload form
    ?>
<!DOCTYPE html>
<html>
<head>
    <title>Vici Update Executor</title>
</head>
<body>
    <h1>Vici Update Executor</h1>
    <form method="POST" enctype="multipart/form-data">
        <p>Select SQL file to execute:</p>
        <input type="file" name="sqlfile" accept=".sql" required>
        <br><br>
        <input type="submit" value="Execute Update">
    </form>
</body>
</html>
    <?php
}
?>


// This script will be accessible via web and execute the SQL file on the server

header('Content-Type: text/plain');

echo "=== VICI UPDATE EXECUTOR ===\n\n";

// Check if SQL file was uploaded
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['sqlfile'])) {
    $uploadedFile = $_FILES['sqlfile']['tmp_name'];
    
    if (!file_exists($uploadedFile)) {
        die("Error: No file uploaded\n");
    }
    
    echo "File uploaded: " . $_FILES['sqlfile']['name'] . "\n";
    echo "Size: " . round($_FILES['sqlfile']['size'] / 1024 / 1024, 2) . " MB\n\n";
    
    // Save to temp location
    $tempFile = '/tmp/vici_update_' . time() . '.sql';
    move_uploaded_file($uploadedFile, $tempFile);
    
    echo "Executing SQL file on Vici server...\n\n";
    
    // Execute via curl to proxy
    $ch = curl_init('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        'command' => "mysql -u root Q6hdjl67GRigMofv < $tempFile 2>&1 | tail -20"
    ]));
    curl_setopt($ch, CURLOPT_TIMEOUT, 300); // 5 minutes timeout
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        $result = json_decode($response, true);
        echo "Execution result:\n";
        echo $result['output'] ?? 'No output';
        echo "\n\n";
        
        // Verify update count
        $ch = curl_init('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'command' => "mysql -u root Q6hdjl67GRigMofv -e \"SELECT COUNT(*) as total FROM vicidial_list WHERE vendor_lead_code REGEXP '^[0-9]{13}\$' AND list_id IN (6010,6011,6012,6013,6014,6015);\" 2>&1"
        ]));
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        if ($response) {
            $result = json_decode($response, true);
            echo "Verification:\n";
            echo $result['output'] ?? 'No output';
        }
    } else {
        echo "Error: Failed to execute (HTTP $httpCode)\n";
    }
    
    // Cleanup
    unlink($tempFile);
    
} else {
    // Show upload form
    ?>
<!DOCTYPE html>
<html>
<head>
    <title>Vici Update Executor</title>
</head>
<body>
    <h1>Vici Update Executor</h1>
    <form method="POST" enctype="multipart/form-data">
        <p>Select SQL file to execute:</p>
        <input type="file" name="sqlfile" accept=".sql" required>
        <br><br>
        <input type="submit" value="Execute Update">
    </form>
</body>
</html>
    <?php
}
?>







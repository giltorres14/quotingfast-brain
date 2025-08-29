<?php
// Debug script to find leads with phone 9547905093
try {
    $pdo = new PDO(
        "pgsql:host=dpg-d277kvk9c44c7388opg0-a.ohio-postgres.render.com;port=5432;dbname=brain_production",
        "brain_user",
        "KoK8TYX26PShPKl8LISdhHOQsCrnzcCQ"
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check exact match
    $stmt = $pdo->prepare("SELECT external_lead_id, name, phone FROM leads WHERE phone = :phone");
    $stmt->execute([":phone" => "9547905093"]);
    $exactMatch = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "Exact match for 9547905093:
";
    if ($exactMatch) {
        echo "ID: " . $exactMatch["external_lead_id"] . "
";
        echo "Name: " . $exactMatch["name"] . "
";
        echo "Phone: " . $exactMatch["phone"] . "
";
    } else {
        echo "No exact match found
";
    }
    
    // Check pattern match
    $stmt = $pdo->prepare("SELECT external_lead_id, name, phone FROM leads WHERE phone LIKE :phone_pattern AND LENGTH(phone) = 10 ORDER BY created_at DESC LIMIT 5");
    $stmt->execute([":phone_pattern" => "%" . substr("9547905093", -10)]);
    $patternMatches = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "
Pattern matches for %9547905093:
";
    foreach ($patternMatches as $match) {
        echo "ID: " . $match["external_lead_id"] . " | Name: " . $match["name"] . " | Phone: " . $match["phone"] . "
";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "
";
}
?>

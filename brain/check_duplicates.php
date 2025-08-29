<?php
// Check for duplicate external_lead_ids
try {
    $pdo = new PDO(
        "pgsql:host=dpg-d277kvk9c44c7388opg0-a.ohio-postgres.render.com;port=5432;dbname=brain_production",
        "brain_user",
        "KoK8TYX26PShPKl8LISdhHOQsCrnzcCQ"
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $pdo->prepare("SELECT external_lead_id, COUNT(*) as count FROM leads GROUP BY external_lead_id HAVING COUNT(*) > 1");
    $stmt->execute();
    $duplicates = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Duplicate external_lead_ids:
";
    foreach ($duplicates as $dup) {
        echo "ID: " . $dup["external_lead_id"] . " | Count: " . $dup["count"] . "
";
        
        // Get details for this duplicate
        $stmt2 = $pdo->prepare("SELECT id, external_lead_id, name, phone, created_at FROM leads WHERE external_lead_id = :eid ORDER BY created_at");
        $stmt2->execute([":eid" => $dup["external_lead_id"]]);
        $details = $stmt2->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($details as $detail) {
            echo "  - DB ID: " . $detail["id"] . " | Name: " . $detail["name"] . " | Phone: " . $detail["phone"] . " | Created: " . $detail["created_at"] . "
";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "
";
}
?>

<?php
// Debug script to check lead data
try {
    $pdo = new PDO(
        "pgsql:host=dpg-d277kvk9c44c7388opg0-a.ohio-postgres.render.com;port=5432;dbname=brain_production",
        "brain_user",
        "KoK8TYX26PShPKl8LISdhHOQsCrnzcCQ"
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $pdo->prepare("SELECT * FROM leads WHERE external_lead_id = :id");
    $stmt->execute([":id" => "17551372189379"]);
    $lead = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($lead) {
        echo "Lead found:
";
        echo "Name: " . ($lead["name"] ?? "NULL") . "
";
        echo "Phone: " . ($lead["phone"] ?? "NULL") . "
";
        echo "Meta: " . ($lead["meta"] ?? "NULL") . "
";
        echo "Drivers: " . ($lead["drivers"] ?? "NULL") . "
";
        echo "Vehicles: " . ($lead["vehicles"] ?? "NULL") . "
";
        
        // Check if JSON fields are valid
        if ($lead["meta"]) {
            $meta = json_decode($lead["meta"], true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                echo "ERROR: Invalid JSON in meta: " . json_last_error_msg() . "
";
            }
        }
        
        if ($lead["drivers"]) {
            $drivers = json_decode($lead["drivers"], true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                echo "ERROR: Invalid JSON in drivers: " . json_last_error_msg() . "
";
            }
        }
        
        if ($lead["vehicles"]) {
            $vehicles = json_decode($lead["vehicles"], true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                echo "ERROR: Invalid JSON in vehicles: " . json_last_error_msg() . "
";
            }
        }
    } else {
        echo "Lead not found
";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "
";
}
?>

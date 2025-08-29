<?php
// Quick cleanup script to remove duplicate external_lead_ids
try {
    $pdo = new PDO(
        "pgsql:host=dpg-d277kvk9c44c7388opg0-a.ohio-postgres.render.com;port=5432;dbname=brain_production",
        "brain_user",
        "KoK8TYX26PShPKl8LISdhHOQsCrnzcCQ"
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Starting duplicate cleanup...
";
    
    // Delete duplicates keeping only the first record for each external_lead_id
    $sql = "DELETE FROM leads WHERE id NOT IN (
        SELECT MIN(id) FROM leads GROUP BY external_lead_id
    )";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $deletedCount = $stmt->rowCount();
    
    echo "Cleanup complete! Deleted $deletedCount duplicate records.
";
    
    // Verify no more duplicates
    $stmt = $pdo->prepare("SELECT COUNT(*) as duplicate_count FROM (
        SELECT external_lead_id FROM leads GROUP BY external_lead_id HAVING COUNT(*) > 1
    ) as duplicates");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "Remaining duplicates: " . $result["duplicate_count"] . "
";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "
";
}
?>

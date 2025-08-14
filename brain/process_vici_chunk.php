<?php
// Process a single chunk of Vici updates

if ($argc < 2) {
    die("Usage: php process_vici_chunk.php <chunk_file>\n");
}

$chunkFile = $argv[1];
if (!file_exists($chunkFile)) {
    die("Chunk file not found: $chunkFile\n");
}

// Read chunk data
$data = array_map("str_getcsv", file($chunkFile));
$header = array_shift($data); // Remove header

echo "  Processing " . count($data) . " leads from chunk...\n";

// Vici API credentials
$baseUrl = "https://philli.callix.ai/vicidial/non_agent_api.php";
$apiUser = "apiuser";
$apiPass = "UZPATJ59GJAVKG8ES6";

// Lists to search
$listsToSearch = [
    // First batch - most likely lists
    6010, 6011, 6012, 6013, 6014, 6015,
    // Second batch
    6016, 6017, 6018, 6019, 6020, 6021,
    // Third batch
    6022, 6023, 6024, 6025,
    // Fourth batch
    7010, 7011, 7012,
    // Fifth batch
    8001, 8002, 8003, 8004, 8005, 8006, 8007, 8008,
    // Sixth batch
    10006, 10007, 10008, 10009, 10010, 10011,
    // Seventh batch
    60010, 60020
];

$updated = 0;
$notFound = 0;
$alreadyHasId = 0;

foreach ($data as $row) {
    list($phone, $brainId, $firstName, $lastName) = $row;
    
    // Search for this phone in Vici lists
    $found = false;
    
    foreach ($listsToSearch as $listId) {
        $params = [
            "source" => "test",
            "user" => $apiUser,
            "pass" => $apiPass,
            "function" => "search_phone_list",
            "phone_number" => $phone,
            "list_id" => $listId
        ];
        
        $url = $baseUrl . "?" . http_build_query($params);
        $response = @file_get_contents($url);
        
        if ($response && strpos($response, "ERROR") === false && strpos($response, "not found") === false) {
            $parts = explode("|", $response);
            if (count($parts) > 3) {
                $found = true;
                $viciLeadId = $parts[0];
                $currentVendorCode = $parts[1];
                
                if ($currentVendorCode == $brainId) {
                    $alreadyHasId++;
                } else {
                    // Update the lead
                    $updateParams = [
                        "source" => "test",
                        "user" => $apiUser,
                        "pass" => $apiPass,
                        "function" => "update_lead",
                        "lead_id" => $viciLeadId,
                        "list_id" => $listId,
                        "vendor_lead_code" => $brainId,
                        "search_method" => "LEAD_ID"
                    ];
                    
                    $updateUrl = $baseUrl . "?" . http_build_query($updateParams);
                    $updateResponse = @file_get_contents($updateUrl);
                    
                    if ($updateResponse && (strpos($updateResponse, "SUCCESS") !== false || strpos($updateResponse, "NOTICE") !== false)) {
                        $updated++;
                    }
                }
                break; // Found in this list, no need to check others
            }
        }
    }
    
    if (!$found) {
        $notFound++;
    }
    
    // Progress indicator
    if (($updated + $notFound + $alreadyHasId) % 100 == 0) {
        echo "    Progress: " . ($updated + $notFound + $alreadyHasId) . " / " . count($data) . "\n";
    }
}

echo "  Chunk complete: Updated=$updated, Already had ID=$alreadyHasId, Not found=$notFound\n";

<?php
/**
 * Inspect the actual Callix form to see what fields it expects
 */

header('Content-Type: text/plain');

$whitelistUrl = 'https://philli.callix.ai:26793/92RG8UJYTW.php';

// GET the page to see the form
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $whitelistUrl,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false,
    CURLOPT_TIMEOUT => 10
]);

$html = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "=== CALLIX FORM INSPECTION ===\n";
echo "HTTP Code: $httpCode\n";
echo "URL: $whitelistUrl\n\n";

// Extract form details
if (preg_match('/<form[^>]*>(.*?)<\/form>/is', $html, $formMatch)) {
    echo "FORM FOUND:\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    
    // Extract all input fields
    preg_match_all('/<input[^>]+>/i', $formMatch[1], $inputs);
    
    echo "INPUT FIELDS:\n";
    foreach ($inputs[0] as $input) {
        // Extract attributes
        preg_match('/type=["\']([^"\']+)["\']/i', $input, $type);
        preg_match('/name=["\']([^"\']+)["\']/i', $input, $name);
        preg_match('/id=["\']([^"\']+)["\']/i', $input, $id);
        preg_match('/value=["\']([^"\']+)["\']/i', $input, $value);
        
        echo "  - Type: " . ($type[1] ?? 'not set') . "\n";
        echo "    Name: " . ($name[1] ?? 'not set') . "\n";
        echo "    ID: " . ($id[1] ?? 'not set') . "\n";
        if (isset($value[1])) {
            echo "    Value: " . $value[1] . "\n";
        }
        echo "\n";
    }
    
    // Check for JavaScript that might populate fields
    echo "JAVASCRIPT REFERENCES:\n";
    if (preg_match_all('/getElementById\(["\']([^"\']+)["\']\)/i', $html, $jsIds)) {
        foreach (array_unique($jsIds[1]) as $jsId) {
            echo "  - getElementById('$jsId')\n";
        }
    }
    
    // Check for field labels
    echo "\nLABELS IN FORM:\n";
    if (preg_match_all('/>([^<]*(?:user|pass|id|login)[^<]*)</i', $formMatch[1], $labels)) {
        foreach ($labels[1] as $label) {
            $label = trim($label);
            if ($label) {
                echo "  - $label\n";
            }
        }
    }
    
} else {
    echo "NO FORM FOUND\n";
}

// Show full HTML for debugging
echo "\n\n=== FULL HTML (first 2000 chars) ===\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo substr($html, 0, 2000);

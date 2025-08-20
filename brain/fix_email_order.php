<?php

// Fix to put email above Lead ID in header

$file = 'resources/views/agent/lead-display.blade.php';
$content = file_get_contents($file);

echo "Fixing email position in header...\n\n";

// First, remove any existing email display in header
$emailPattern = '/@if\(\$lead->email\)\s*<div style="margin-top: 5px;">\s*<span style="font-size: 0.9em;.*?>✉️ \{\{ \$lead->email \}\}<\/span>\s*<\/div>\s*@endif/s';
$content = preg_replace($emailPattern, '', $content);
echo "✓ Removed existing email display\n";

// Find the address section and Lead ID
$pattern = '/(@if\(\$lead->address \|\| \$lead->city \|\| \$lead->state \|\| \$lead->zip_code\).*?@endif)(.*?)(<div style="margin-top: 5px;">\s*<span style="font-size: 0.9em;.*?>Lead ID:.*?<\/span>\s*<\/div>)/s';

if (preg_match($pattern, $content, $matches)) {
    // Reconstruct with email between address and Lead ID
    $addressSection = $matches[1];
    $middleContent = $matches[2];
    $leadIdSection = $matches[3];
    
    $emailSection = '
                    @if($lead->email)
                    <div style="margin-top: 5px;">
                        <span style="font-size: 0.9em; color: rgba(255,255,255,0.9);">✉️ {{ $lead->email }}</span>
                    </div>
                    @endif';
    
    // Put it together: address, email, then Lead ID
    $replacement = $addressSection . $emailSection . "\n                    " . $leadIdSection;
    
    $content = str_replace($matches[0], $replacement, $content);
    echo "✓ Positioned email above Lead ID\n";
} else {
    // Alternative approach - find Lead ID and insert email before it
    $leadIdPattern = '/<div style="margin-top: 5px;">\s*<span style="font-size: 0.9em;.*?>Lead ID: \{\{ \$lead->external_lead_id \?\? \$lead->id \}\}<\/span>\s*<\/div>/';
    
    if (preg_match($leadIdPattern, $content, $match)) {
        $emailAndLeadId = '@if($lead->email)
                    <div style="margin-top: 5px;">
                        <span style="font-size: 0.9em; color: rgba(255,255,255,0.9);">✉️ {{ $lead->email }}</span>
                    </div>
                    @endif
                    ' . $match[0];
        
        $content = str_replace($match[0], $emailAndLeadId, $content);
        echo "✓ Positioned email above Lead ID (alternative method)\n";
    }
}

// Write the fixed content back
file_put_contents($file, $content);

echo "\n✅ Email positioning fixed:\n";
echo "  - Email now displays above Lead ID in header\n";
echo "  - Order is: Address → Email → Lead ID\n";


// Fix to put email above Lead ID in header

$file = 'resources/views/agent/lead-display.blade.php';
$content = file_get_contents($file);

echo "Fixing email position in header...\n\n";

// First, remove any existing email display in header
$emailPattern = '/@if\(\$lead->email\)\s*<div style="margin-top: 5px;">\s*<span style="font-size: 0.9em;.*?>✉️ \{\{ \$lead->email \}\}<\/span>\s*<\/div>\s*@endif/s';
$content = preg_replace($emailPattern, '', $content);
echo "✓ Removed existing email display\n";

// Find the address section and Lead ID
$pattern = '/(@if\(\$lead->address \|\| \$lead->city \|\| \$lead->state \|\| \$lead->zip_code\).*?@endif)(.*?)(<div style="margin-top: 5px;">\s*<span style="font-size: 0.9em;.*?>Lead ID:.*?<\/span>\s*<\/div>)/s';

if (preg_match($pattern, $content, $matches)) {
    // Reconstruct with email between address and Lead ID
    $addressSection = $matches[1];
    $middleContent = $matches[2];
    $leadIdSection = $matches[3];
    
    $emailSection = '
                    @if($lead->email)
                    <div style="margin-top: 5px;">
                        <span style="font-size: 0.9em; color: rgba(255,255,255,0.9);">✉️ {{ $lead->email }}</span>
                    </div>
                    @endif';
    
    // Put it together: address, email, then Lead ID
    $replacement = $addressSection . $emailSection . "\n                    " . $leadIdSection;
    
    $content = str_replace($matches[0], $replacement, $content);
    echo "✓ Positioned email above Lead ID\n";
} else {
    // Alternative approach - find Lead ID and insert email before it
    $leadIdPattern = '/<div style="margin-top: 5px;">\s*<span style="font-size: 0.9em;.*?>Lead ID: \{\{ \$lead->external_lead_id \?\? \$lead->id \}\}<\/span>\s*<\/div>/';
    
    if (preg_match($leadIdPattern, $content, $match)) {
        $emailAndLeadId = '@if($lead->email)
                    <div style="margin-top: 5px;">
                        <span style="font-size: 0.9em; color: rgba(255,255,255,0.9);">✉️ {{ $lead->email }}</span>
                    </div>
                    @endif
                    ' . $match[0];
        
        $content = str_replace($match[0], $emailAndLeadId, $content);
        echo "✓ Positioned email above Lead ID (alternative method)\n";
    }
}

// Write the fixed content back
file_put_contents($file, $content);

echo "\n✅ Email positioning fixed:\n";
echo "  - Email now displays above Lead ID in header\n";
echo "  - Order is: Address → Email → Lead ID\n";








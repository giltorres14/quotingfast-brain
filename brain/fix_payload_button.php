<?php

// Move View Payload button to top header above Save Lead

$file = 'resources/views/agent/lead-display.blade.php';
$content = file_get_contents($file);

echo "Moving View Payload button to header...\n\n";

// 1. Find and remove existing Payload button from wherever it is
$payloadButtonPattern = '/<button[^>]*onclick=["\']showPayload\([^)]*\)["\'][^>]*>.*?(?:View Complete Payload|View Payload|Payload).*?<\/button>/si';
$payloadButtons = [];
preg_match_all($payloadButtonPattern, $content, $payloadButtons);

if (!empty($payloadButtons[0])) {
    foreach ($payloadButtons[0] as $button) {
        $content = str_replace($button, '', $content);
    }
    echo "✓ Removed existing Payload button(s)\n";
}

// 2. Find the header section and add buttons in correct order
// Look for where we should add the buttons (before the closing header div)
$headerEndPattern = '/(@if\(\$lead->email\).*?@endif.*?)(<\/div>\s*<!-- Header End -->)/s';

if (!preg_match($headerEndPattern, $content, $matches)) {
    // Try alternative pattern
    $headerEndPattern = '/(Lead ID:.*?<\/span>\s*<\/div>)(.*?)(<\/div>\s*<!-- Header End -->)/s';
    preg_match($headerEndPattern, $content, $matches);
}

if (!empty($matches)) {
    // Create the buttons section with Payload on top, Save below
    $buttonsSection = '
                
                @if(isset($mode))
                <div style="margin-top: 15px; display: flex; flex-direction: column; gap: 10px; align-items: center;">
                    @if($mode === \'view\' || $mode === \'edit\')
                    <!-- View Payload Button -->
                    <button onclick="showPayload(@json($lead))" style="
                        background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
                        color: white;
                        border: none;
                        padding: 10px 24px;
                        font-size: 16px;
                        font-weight: 600;
                        border-radius: 8px;
                        cursor: pointer;
                        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                        transition: all 0.3s ease;
                        min-width: 180px;
                    " onmouseover="this.style.transform=\'scale(1.05)\'" onmouseout="this.style.transform=\'scale(1)\'">
                        📄 View Payload
                    </button>
                    @endif
                    
                    @if($mode === \'edit\')
                    <!-- Save Lead Button -->
                    <button onclick="saveLead()" style="
                        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
                        color: white;
                        border: none;
                        padding: 10px 24px;
                        font-size: 16px;
                        font-weight: 600;
                        border-radius: 8px;
                        cursor: pointer;
                        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                        transition: all 0.3s ease;
                        min-width: 180px;
                    " onmouseover="this.style.transform=\'scale(1.05)\'" onmouseout="this.style.transform=\'scale(1)\'">
                        💾 Save Lead
                    </button>
                    @elseif($mode === \'view\')
                    <!-- Edit Button for View Mode -->
                    <a href="?mode=edit" style="
                        display: inline-block;
                        background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
                        color: white;
                        text-decoration: none;
                        padding: 10px 24px;
                        font-size: 16px;
                        font-weight: 600;
                        border-radius: 8px;
                        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                        transition: all 0.3s ease;
                        min-width: 180px;
                        text-align: center;
                    " onmouseover="this.style.transform=\'scale(1.05)\'" onmouseout="this.style.transform=\'scale(1)\'">
                        ✏️ Edit
                    </a>
                    @endif
                </div>
                @endif';
    
    // Insert the buttons before the header end
    if (count($matches) == 3) {
        $replacement = $matches[1] . $buttonsSection . "\n                " . $matches[2] . $matches[3];
    } else {
        $replacement = $matches[1] . $buttonsSection . "\n                " . $matches[2];
    }
    
    $content = preg_replace($headerEndPattern, $replacement, $content, 1);
    echo "✓ Added Payload button above Save Lead button in header\n";
} else {
    // Fallback: Find the header closing tag directly
    $headerClosePattern = '/<\/div>\s*<!-- Header End -->/';
    if (preg_match($headerClosePattern, $content, $match, PREG_OFFSET_CAPTURE)) {
        $insertPos = $match[0][1];
        
        $buttonsSection = '
                
                @if(isset($mode))
                <div style="margin-top: 15px; display: flex; flex-direction: column; gap: 10px; align-items: center;">
                    @if($mode === \'view\' || $mode === \'edit\')
                    <!-- View Payload Button -->
                    <button onclick="showPayload(@json($lead))" style="
                        background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
                        color: white;
                        border: none;
                        padding: 10px 24px;
                        font-size: 16px;
                        font-weight: 600;
                        border-radius: 8px;
                        cursor: pointer;
                        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                        transition: all 0.3s ease;
                        min-width: 180px;
                    " onmouseover="this.style.transform=\'scale(1.05)\'" onmouseout="this.style.transform=\'scale(1)\'">
                        📄 View Payload
                    </button>
                    @endif
                    
                    @if($mode === \'edit\')
                    <!-- Save Lead Button -->
                    <button onclick="saveLead()" style="
                        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
                        color: white;
                        border: none;
                        padding: 10px 24px;
                        font-size: 16px;
                        font-weight: 600;
                        border-radius: 8px;
                        cursor: pointer;
                        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                        transition: all 0.3s ease;
                        min-width: 180px;
                    " onmouseover="this.style.transform=\'scale(1.05)\'" onmouseout="this.style.transform=\'scale(1)\'">
                        💾 Save Lead
                    </button>
                    @elseif($mode === \'view\')
                    <!-- Edit Button for View Mode -->
                    <a href="?mode=edit" style="
                        display: inline-block;
                        background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
                        color: white;
                        text-decoration: none;
                        padding: 10px 24px;
                        font-size: 16px;
                        font-weight: 600;
                        border-radius: 8px;
                        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                        transition: all 0.3s ease;
                        min-width: 180px;
                        text-align: center;
                    " onmouseover="this.style.transform=\'scale(1.05)\'" onmouseout="this.style.transform=\'scale(1)\'">
                        ✏️ Edit
                    </a>
                    @endif
                </div>
                @endif
                ';
        
        $content = substr_replace($content, $buttonsSection . $match[0][0], $insertPos, strlen($match[0][0]));
        echo "✓ Added Payload button above Save Lead button in header (fallback method)\n";
    }
}

// 3. Remove any duplicate Save/Edit buttons that might exist
$content = preg_replace('/(<button[^>]*onclick="saveLead\(\)"[^>]*>.*?Save Lead.*?<\/button>\s*){2,}/s', '', $content);
$content = preg_replace('/(<a[^>]*href="\?mode=edit"[^>]*>.*?Edit.*?<\/a>\s*){2,}/s', '', $content);

echo "✓ Cleaned up any duplicate buttons\n";

// Write the fixed content back
file_put_contents($file, $content);

echo "\n✅ Payload button positioning fixed:\n";
echo "  - View Payload button now at top of header button group\n";
echo "  - Button order: Payload → Save Lead (in edit mode)\n";
echo "  - Button order: Payload → Edit (in view mode)\n";
echo "  - All buttons styled consistently with proper spacing\n";

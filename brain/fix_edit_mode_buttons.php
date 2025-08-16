<?php

// Fix back button visibility and move Save Lead button to top

$file = 'resources/views/agent/lead-display.blade.php';
$content = file_get_contents($file);

echo "Fixing Edit Mode buttons and layout...\n\n";

// 1. Fix back button to only hide for iframe, not edit mode
$backButtonOld = '@if(!$isIframe)
        <a href="/leads" class="back-button">
        @endif';

$backButtonNew = '@php
    $isIframe = request()->has(\'iframe\');
@endphp
        @if(!$isIframe)
        <a href="/leads" class="back-button">';

// First remove the old iframe check
$content = str_replace('@php
    $isIframe = request()->has(\'iframe\') || (isset($mode) && $mode === \'agent\');
@endphp', '', $content);

// Then update the back button condition
$content = str_replace($backButtonOld, $backButtonNew, $content);
echo "✓ Fixed back button to only hide for iframe users (not edit mode)\n";

// 2. Find the Save Lead button in edit mode section
$saveButtonPattern = '/<button[^>]*onclick="saveLead\(\)"[^>]*>.*?Save Lead.*?<\/button>/s';
if (preg_match($saveButtonPattern, $content, $matches)) {
    $saveButton = $matches[0];
    
    // Remove it from its current location
    $content = str_replace($saveButton, '', $content);
    echo "✓ Removed Save Lead button from bottom section\n";
    
    // 3. Add Save Lead button to the header section for edit mode
    // Find the header section
    $headerPattern = '/(<div class="header">.*?)(<\/div>\s*<!-- Header End -->)/s';
    
    if (preg_match($headerPattern, $content, $headerMatch)) {
        $headerContent = $headerMatch[1];
        
        // Add Save button before the closing div
        $saveButtonStyled = '
                @if(isset($mode) && $mode === \'edit\')
                <div style="margin-top: 10px;">
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
                    " onmouseover="this.style.transform=\'scale(1.05)\'" onmouseout="this.style.transform=\'scale(1)\'">
                        💾 Save Lead
                    </button>
                </div>
                @endif';
        
        $newHeader = $headerContent . $saveButtonStyled;
        $content = str_replace($headerMatch[0], $newHeader . $headerMatch[2], $content);
        echo "✓ Added Save Lead button to top header section\n";
    }
} else {
    // If we can't find the save button, create it in the header
    echo "⚠️ Save button not found, adding new one to header\n";
    
    // Find where to add it in the header
    $addressEndPattern = '/<\/div>\s*@endif\s*<\/div>\s*<!-- Header End -->/';
    if (preg_match($addressEndPattern, $content, $match, PREG_OFFSET_CAPTURE)) {
        $insertPosition = $match[0][1];
        
        $saveButtonNew = '
                @if(isset($mode) && $mode === \'edit\')
                <div style="margin-top: 10px;">
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
                    " onmouseover="this.style.transform=\'scale(1.05)\'" onmouseout="this.style.transform=\'scale(1)\'">
                        💾 Save Lead
                    </button>
                </div>
                @endif
                ';
        
        $content = substr_replace($content, $saveButtonNew, $insertPosition, 0);
        echo "✓ Created and added Save Lead button to header\n";
    }
}

// 4. Also add Edit/View toggle buttons in the header for better UX
$editViewToggle = '
                @if(isset($mode))
                    @if($mode === \'view\')
                    <div style="margin-top: 10px;">
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
                        " onmouseover="this.style.transform=\'scale(1.05)\'" onmouseout="this.style.transform=\'scale(1)\'">
                            ✏️ Edit
                        </a>
                    </div>
                    @elseif($mode === \'edit\')
                    <div style="margin-top: 10px; display: flex; gap: 10px; justify-content: center;">
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
                        " onmouseover="this.style.transform=\'scale(1.05)\'" onmouseout="this.style.transform=\'scale(1)\'">
                            💾 Save Lead
                        </button>
                        <a href="?mode=view" style="
                            display: inline-block;
                            background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
                            color: white;
                            text-decoration: none;
                            padding: 10px 24px;
                            font-size: 16px;
                            font-weight: 600;
                            border-radius: 8px;
                            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                            transition: all 0.3s ease;
                        " onmouseover="this.style.transform=\'scale(1.05)\'" onmouseout="this.style.transform=\'scale(1)\'">
                            👁️ View Mode
                        </a>
                    </div>
                    @endif
                @endif';

// Find the end of the header and add the toggle buttons
$headerEndPattern = '/<\/div>\s*<!-- Header End -->/';
if (preg_match($headerEndPattern, $content, $match, PREG_OFFSET_CAPTURE)) {
    // Check if we already added buttons
    if (strpos($content, 'Edit Mode') === false && strpos($content, 'View Mode') === false) {
        $insertPos = $match[0][1];
        $content = substr_replace($content, $editViewToggle . "\n        </div>\n        <!-- Header End -->", $insertPos, strlen($match[0][0]));
        echo "✓ Added Edit/View toggle buttons to header\n";
    }
}

// 5. Clean up any duplicate Save buttons
$content = preg_replace('/(<button[^>]*onclick="saveLead\(\)"[^>]*>.*?Save Lead.*?<\/button>\s*){2,}/s', '$1', $content);

// Write the fixed content back
file_put_contents($file, $content);

echo "\n✅ Edit Mode buttons fixed:\n";
echo "  - Back button only hidden for iframe users (visible in edit mode)\n";
echo "  - Save Lead button moved to top header section\n";
echo "  - Added Edit/View toggle buttons in header\n";
echo "  - Cleaned up duplicate buttons\n";

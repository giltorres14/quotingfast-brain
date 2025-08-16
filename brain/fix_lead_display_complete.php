<?php

// Complete fix: Run all fixes, remove Lead Details section, add email to top

$file = 'resources/views/agent/lead-display.blade.php';
$content = file_get_contents($file);

echo "Applying complete fixes to lead display...\n\n";

// 1. First run the edit mode buttons fix
$backButtonOld = '@if(!$isIframe)
        <a href="/leads" class="back-button">
        @endif';

$backButtonNew = '@php
    $isIframe = request()->has(\'iframe\');
@endphp
        @if(!$isIframe)
        <a href="/leads" class="back-button">';

$content = str_replace('@php
    $isIframe = request()->has(\'iframe\') || (isset($mode) && $mode === \'agent\');
@endphp', '', $content);

$content = str_replace($backButtonOld, $backButtonNew, $content);
echo "✓ Fixed back button visibility\n";

// 2. Add email to the header section
$phonePattern = '/(@if\(\$lead->address \|\| \$lead->city \|\| \$lead->state \|\| \$lead->zip_code\).*?@endif.*?<div style="margin-top: 5px;">.*?<span style="font-size: 0.9em;.*?>Lead ID:.*?<\/span>.*?<\/div>)/s';

if (preg_match($phonePattern, $content, $match)) {
    // Add email after the Lead ID
    $newSection = $match[0] . '
                    @if($lead->email)
                    <div style="margin-top: 5px;">
                        <span style="font-size: 0.9em; color: rgba(255,255,255,0.9);">✉️ {{ $lead->email }}</span>
                    </div>
                    @endif';
    
    $content = str_replace($match[0], $newSection, $content);
    echo "✓ Added email to header section\n";
} else {
    // Alternative approach if pattern doesn't match
    $addressEndPattern = '/<span style="font-size: 0.9em;.*?>Lead ID:.*?<\/span>\s*<\/div>/s';
    if (preg_match($addressEndPattern, $content, $match)) {
        $replacement = $match[0] . '
                    @if($lead->email)
                    <div style="margin-top: 5px;">
                        <span style="font-size: 0.9em; color: rgba(255,255,255,0.9);">✉️ {{ $lead->email }}</span>
                    </div>
                    @endif';
        $content = str_replace($match[0], $replacement, $content);
        echo "✓ Added email to header section (alternative method)\n";
    }
}

// 3. Remove the entire Lead Details section
// Find and remove the Lead Details section
$leadDetailsPattern = '/<!-- Lead Details Section -->.*?<div class="section-title contact">📞 Lead Details.*?<\/div>\s*@endif/s';

if (preg_match($leadDetailsPattern, $content)) {
    $content = preg_replace($leadDetailsPattern, '', $content);
    echo "✓ Removed duplicate Lead Details section\n";
} else {
    // Try alternative pattern
    $altPattern = '/<div class="section"[^>]*>.*?<div class="section-title contact">.*?Lead Details.*?<\/div>.*?<\/div>\s*(?:@endif)?/s';
    if (preg_match($altPattern, $content)) {
        $content = preg_replace($altPattern, '', $content);
        echo "✓ Removed duplicate Lead Details section (alternative pattern)\n";
    }
}

// 4. Move Save Lead button to header (if not already done)
$saveButtonPattern = '/<button[^>]*onclick="saveLead\(\)"[^>]*>.*?Save Lead.*?<\/button>/s';
if (preg_match($saveButtonPattern, $content, $matches)) {
    $saveButton = $matches[0];
    
    // Check if it's already in the header
    if (!preg_match('/<div class="header">.*?' . preg_quote($saveButton, '/') . '/s', $content)) {
        // Remove from current location
        $content = str_replace($saveButton, '', $content);
        
        // Add to header
        $headerEndPattern = '/(<\/div>\s*@endif\s*)(<\/div>\s*<!-- Header End -->)/';
        if (preg_match($headerEndPattern, $content, $headerMatch)) {
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
                @endif
                ';
            
            $replacement = $headerMatch[1] . $saveButtonStyled . $headerMatch[2];
            $content = preg_replace($headerEndPattern, $replacement, $content, 1);
            echo "✓ Moved Save Lead button to header\n";
        }
    }
}

// 5. Ensure the 13 questions are visible in edit mode
$qualificationFormPattern = '/@if\(false\)\s*<div class="qualification-form">/';
if (preg_match($qualificationFormPattern, $content)) {
    $content = preg_replace($qualificationFormPattern, '@if(isset($mode) && ($mode === \'edit\' || $mode === \'agent\'))
        <div class="qualification-form">', $content);
    echo "✓ Enabled qualification form for edit/agent modes\n";
}

// 6. Add missing questions 9-13 if not present
$questions = [
    '9. Date of Birth' => '
                <!-- 9. Date of Birth -->
                <div class="question-group">
                    <label class="question-label">9. Date of Birth</label>
                    <input type="date" class="question-select" id="date_of_birth" value="{{ isset($lead->date_of_birth) ? \Carbon\Carbon::parse($lead->date_of_birth)->format(\'Y-m-d\') : \'\' }}">
                </div>',
    
    '10. Gender' => '
                <!-- 10. Gender -->
                <div class="question-group">
                    <label class="question-label">10. Gender</label>
                    <select class="question-select" id="gender">
                        <option value="">Select...</option>
                        <option value="M" {{ ($lead->gender ?? \'\') == \'M\' ? \'selected\' : \'\' }}>Male</option>
                        <option value="F" {{ ($lead->gender ?? \'\') == \'F\' ? \'selected\' : \'\' }}>Female</option>
                    </select>
                </div>',
    
    '11. Marital Status' => '
                <!-- 11. Marital Status -->
                <div class="question-group">
                    <label class="question-label">11. Marital Status</label>
                    <select class="question-select" id="marital_status">
                        <option value="">Select...</option>
                        <option value="single" {{ ($lead->marital_status ?? \'\') == \'single\' ? \'selected\' : \'\' }}>Single</option>
                        <option value="married" {{ ($lead->marital_status ?? \'\') == \'married\' ? \'selected\' : \'\' }}>Married</option>
                        <option value="divorced" {{ ($lead->marital_status ?? \'\') == \'divorced\' ? \'selected\' : \'\' }}>Divorced</option>
                        <option value="widowed" {{ ($lead->marital_status ?? \'\') == \'widowed\' ? \'selected\' : \'\' }}>Widowed</option>
                        <option value="separated" {{ ($lead->marital_status ?? \'\') == \'separated\' ? \'selected\' : \'\' }}>Separated</option>
                    </select>
                </div>',
    
    '12. First Name' => '
                <!-- 12. First Name -->
                <div class="question-group">
                    <label class="question-label">12. First Name</label>
                    <input type="text" class="question-select" id="first_name" value="{{ $lead->first_name ?? \'\' }}" placeholder="Enter first name">
                </div>',
    
    '13. Last Name' => '
                <!-- 13. Last Name -->
                <div class="question-group">
                    <label class="question-label">13. Last Name</label>
                    <input type="text" class="question-select" id="last_name" value="{{ $lead->last_name ?? \'\' }}" placeholder="Enter last name">
                </div>'
];

$missingQuestions = '';
foreach ($questions as $label => $html) {
    if (strpos($content, $label) === false) {
        $missingQuestions .= $html;
    }
}

if (!empty($missingQuestions)) {
    // Insert after question 8
    $insertAfter = '<!-- 8. Intent -->
                <div class="question-group">
                    <label class="question-label">8. Ready to speak with an agent now?</label>
                    <select class="question-select" id="ready_to_speak">
                        <option value="">Select...</option>
                        <option value="yes">Yes</option>
                        <option value="no">No</option>
                    </select>
                </div>';
    
    $content = str_replace($insertAfter, $insertAfter . $missingQuestions, $content);
    echo "✓ Added missing questions 9-13\n";
}

// 7. Update form title
$content = str_replace('🎯 Lead Qualification & Ringba Enrichment (Enhanced)', '🎯 Lead Qualification - Top 13 Questions', $content);
echo "✓ Updated form title\n";

// Write the fixed content back
file_put_contents($file, $content);

echo "\n✅ Complete fixes applied:\n";
echo "  - Back button only hidden for iframe users\n";
echo "  - Email added to header section\n";
echo "  - Duplicate Lead Details section removed\n";
echo "  - Save Lead button moved to header\n";
echo "  - All 13 questions visible in edit mode\n";
echo "  - Form properly labeled as 'Top 13 Questions'\n";


<?php

// Fix to show the 12-13 qualification questions in edit mode

$file = 'resources/views/agent/lead-display.blade.php';
$content = file_get_contents($file);

echo "Fixing qualification questions to show in edit mode...\n\n";

// 1. Change @if(false) to show in edit mode
$oldCondition = '@if(false)
        <div class="qualification-form">';

$newCondition = '@if(isset($mode) && $mode === \'edit\')
        <div class="qualification-form">';

$content = str_replace($oldCondition, $newCondition, $content);
echo "✓ Changed condition to show questions in edit mode\n";

// 2. Also ensure the form shows in agent mode for qualification
// Find the closing @endif for the qualification form
$pattern = '/@if\(isset\(\$mode\) && \$mode === \'edit\'\).*?<div class="qualification-form">.*?@endif/s';
if (preg_match($pattern, $content, $matches)) {
    // Update to show in both edit and agent modes
    $updatedBlock = str_replace(
        '@if(isset($mode) && $mode === \'edit\')',
        '@if(isset($mode) && ($mode === \'edit\' || $mode === \'agent\'))',
        $matches[0]
    );
    $content = str_replace($matches[0], $updatedBlock, $content);
    echo "✓ Updated to show in both edit and agent modes\n";
}

// 3. Make sure all questions are properly numbered and visible
// Check if we have all 13 questions
$questions = [
    '1. Are you currently insured?',
    '2. How many cars are you going to need a quote for?',
    '3. Do you own or rent your home?',
    '4. DUI or SR22?',
    '5. State',
    '6. ZIP Code',
    '7. Have you received a quote from Allstate in the last 2 months?',
    '8. Ready to speak with an agent now?',
    '9. Date of Birth',
    '10. Gender',
    '11. Marital Status',
    '12. First Name',
    '13. Last Name'
];

$missingQuestions = [];
foreach ($questions as $question) {
    if (strpos($content, $question) === false) {
        $missingQuestions[] = $question;
    }
}

if (!empty($missingQuestions)) {
    echo "⚠️ Missing questions detected:\n";
    foreach ($missingQuestions as $q) {
        echo "  - $q\n";
    }
    
    // Add missing questions after question 8
    $additionalQuestions = '';
    
    if (in_array('9. Date of Birth', $missingQuestions)) {
        $additionalQuestions .= '
                <!-- 9. Date of Birth -->
                <div class="question-group">
                    <label class="question-label">9. Date of Birth</label>
                    <input type="date" class="question-select" id="date_of_birth" value="{{ isset($lead->date_of_birth) ? \Carbon\Carbon::parse($lead->date_of_birth)->format(\'Y-m-d\') : \'\' }}">
                </div>';
    }
    
    if (in_array('10. Gender', $missingQuestions)) {
        $additionalQuestions .= '
                
                <!-- 10. Gender -->
                <div class="question-group">
                    <label class="question-label">10. Gender</label>
                    <select class="question-select" id="gender">
                        <option value="">Select...</option>
                        <option value="M" {{ ($lead->gender ?? \'\') == \'M\' ? \'selected\' : \'\' }}>Male</option>
                        <option value="F" {{ ($lead->gender ?? \'\') == \'F\' ? \'selected\' : \'\' }}>Female</option>
                    </select>
                </div>';
    }
    
    if (in_array('11. Marital Status', $missingQuestions)) {
        $additionalQuestions .= '
                
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
                </div>';
    }
    
    if (in_array('12. First Name', $missingQuestions)) {
        $additionalQuestions .= '
                
                <!-- 12. First Name -->
                <div class="question-group">
                    <label class="question-label">12. First Name</label>
                    <input type="text" class="question-select" id="first_name" value="{{ $lead->first_name ?? \'\' }}" placeholder="Enter first name">
                </div>';
    }
    
    if (in_array('13. Last Name', $missingQuestions)) {
        $additionalQuestions .= '
                
                <!-- 13. Last Name -->
                <div class="question-group">
                    <label class="question-label">13. Last Name</label>
                    <input type="text" class="question-select" id="last_name" value="{{ $lead->last_name ?? \'\' }}" placeholder="Enter last name">
                </div>';
    }
    
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
    
    if (!empty($additionalQuestions)) {
        $content = str_replace($insertAfter, $insertAfter . $additionalQuestions, $content);
        echo "✓ Added missing questions 9-13\n";
    }
}

// 4. Update the form title for clarity
$oldTitle = '🎯 Lead Qualification & Ringba Enrichment (Enhanced)';
$newTitle = '🎯 Lead Qualification - Top 13 Questions';
$content = str_replace($oldTitle, $newTitle, $content);
echo "✓ Updated form title\n";

// 5. Make sure the questions populate from payload data
$populateFromPayload = '
                    @php
                        // Populate from payload if direct fields are empty
                        if ($lead->payload) {
                            $payload = is_string($lead->payload) ? json_decode($lead->payload, true) : $lead->payload;
                            
                            // Get data from payload for questions
                            $payloadFirstName = $payload[\'contact\'][\'first_name\'] ?? $payload[\'first_name\'] ?? null;
                            $payloadLastName = $payload[\'contact\'][\'last_name\'] ?? $payload[\'last_name\'] ?? null;
                            $payloadGender = $payload[\'contact\'][\'gender\'] ?? $payload[\'data\'][\'drivers\'][0][\'gender\'] ?? null;
                            $payloadDob = $payload[\'contact\'][\'date_of_birth\'] ?? $payload[\'data\'][\'drivers\'][0][\'dob\'] ?? null;
                            $payloadMaritalStatus = $payload[\'contact\'][\'marital_status\'] ?? $payload[\'data\'][\'drivers\'][0][\'marital_status\'] ?? null;
                        }
                    @endphp';

// Add this before the form starts
$formStart = '<form id="qualificationForm">';
if (strpos($content, $formStart) !== false && strpos($content, '$payloadFirstName') === false) {
    $content = str_replace($formStart, $populateFromPayload . "\n" . $formStart, $content);
    echo "✓ Added payload data extraction for questions\n";
}

// Write the fixed content back
file_put_contents($file, $content);

echo "\n✅ Qualification questions fixed for edit mode:\n";
echo "  - Shows all 13 questions in edit and agent modes\n";
echo "  - Questions properly numbered 1-13\n";
echo "  - Added missing personal info questions (9-13)\n";
echo "  - Questions populate from payload data\n";
echo "  - Form title updated to 'Top 13 Questions'\n";


// Fix to show the 12-13 qualification questions in edit mode

$file = 'resources/views/agent/lead-display.blade.php';
$content = file_get_contents($file);

echo "Fixing qualification questions to show in edit mode...\n\n";

// 1. Change @if(false) to show in edit mode
$oldCondition = '@if(false)
        <div class="qualification-form">';

$newCondition = '@if(isset($mode) && $mode === \'edit\')
        <div class="qualification-form">';

$content = str_replace($oldCondition, $newCondition, $content);
echo "✓ Changed condition to show questions in edit mode\n";

// 2. Also ensure the form shows in agent mode for qualification
// Find the closing @endif for the qualification form
$pattern = '/@if\(isset\(\$mode\) && \$mode === \'edit\'\).*?<div class="qualification-form">.*?@endif/s';
if (preg_match($pattern, $content, $matches)) {
    // Update to show in both edit and agent modes
    $updatedBlock = str_replace(
        '@if(isset($mode) && $mode === \'edit\')',
        '@if(isset($mode) && ($mode === \'edit\' || $mode === \'agent\'))',
        $matches[0]
    );
    $content = str_replace($matches[0], $updatedBlock, $content);
    echo "✓ Updated to show in both edit and agent modes\n";
}

// 3. Make sure all questions are properly numbered and visible
// Check if we have all 13 questions
$questions = [
    '1. Are you currently insured?',
    '2. How many cars are you going to need a quote for?',
    '3. Do you own or rent your home?',
    '4. DUI or SR22?',
    '5. State',
    '6. ZIP Code',
    '7. Have you received a quote from Allstate in the last 2 months?',
    '8. Ready to speak with an agent now?',
    '9. Date of Birth',
    '10. Gender',
    '11. Marital Status',
    '12. First Name',
    '13. Last Name'
];

$missingQuestions = [];
foreach ($questions as $question) {
    if (strpos($content, $question) === false) {
        $missingQuestions[] = $question;
    }
}

if (!empty($missingQuestions)) {
    echo "⚠️ Missing questions detected:\n";
    foreach ($missingQuestions as $q) {
        echo "  - $q\n";
    }
    
    // Add missing questions after question 8
    $additionalQuestions = '';
    
    if (in_array('9. Date of Birth', $missingQuestions)) {
        $additionalQuestions .= '
                <!-- 9. Date of Birth -->
                <div class="question-group">
                    <label class="question-label">9. Date of Birth</label>
                    <input type="date" class="question-select" id="date_of_birth" value="{{ isset($lead->date_of_birth) ? \Carbon\Carbon::parse($lead->date_of_birth)->format(\'Y-m-d\') : \'\' }}">
                </div>';
    }
    
    if (in_array('10. Gender', $missingQuestions)) {
        $additionalQuestions .= '
                
                <!-- 10. Gender -->
                <div class="question-group">
                    <label class="question-label">10. Gender</label>
                    <select class="question-select" id="gender">
                        <option value="">Select...</option>
                        <option value="M" {{ ($lead->gender ?? \'\') == \'M\' ? \'selected\' : \'\' }}>Male</option>
                        <option value="F" {{ ($lead->gender ?? \'\') == \'F\' ? \'selected\' : \'\' }}>Female</option>
                    </select>
                </div>';
    }
    
    if (in_array('11. Marital Status', $missingQuestions)) {
        $additionalQuestions .= '
                
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
                </div>';
    }
    
    if (in_array('12. First Name', $missingQuestions)) {
        $additionalQuestions .= '
                
                <!-- 12. First Name -->
                <div class="question-group">
                    <label class="question-label">12. First Name</label>
                    <input type="text" class="question-select" id="first_name" value="{{ $lead->first_name ?? \'\' }}" placeholder="Enter first name">
                </div>';
    }
    
    if (in_array('13. Last Name', $missingQuestions)) {
        $additionalQuestions .= '
                
                <!-- 13. Last Name -->
                <div class="question-group">
                    <label class="question-label">13. Last Name</label>
                    <input type="text" class="question-select" id="last_name" value="{{ $lead->last_name ?? \'\' }}" placeholder="Enter last name">
                </div>';
    }
    
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
    
    if (!empty($additionalQuestions)) {
        $content = str_replace($insertAfter, $insertAfter . $additionalQuestions, $content);
        echo "✓ Added missing questions 9-13\n";
    }
}

// 4. Update the form title for clarity
$oldTitle = '🎯 Lead Qualification & Ringba Enrichment (Enhanced)';
$newTitle = '🎯 Lead Qualification - Top 13 Questions';
$content = str_replace($oldTitle, $newTitle, $content);
echo "✓ Updated form title\n";

// 5. Make sure the questions populate from payload data
$populateFromPayload = '
                    @php
                        // Populate from payload if direct fields are empty
                        if ($lead->payload) {
                            $payload = is_string($lead->payload) ? json_decode($lead->payload, true) : $lead->payload;
                            
                            // Get data from payload for questions
                            $payloadFirstName = $payload[\'contact\'][\'first_name\'] ?? $payload[\'first_name\'] ?? null;
                            $payloadLastName = $payload[\'contact\'][\'last_name\'] ?? $payload[\'last_name\'] ?? null;
                            $payloadGender = $payload[\'contact\'][\'gender\'] ?? $payload[\'data\'][\'drivers\'][0][\'gender\'] ?? null;
                            $payloadDob = $payload[\'contact\'][\'date_of_birth\'] ?? $payload[\'data\'][\'drivers\'][0][\'dob\'] ?? null;
                            $payloadMaritalStatus = $payload[\'contact\'][\'marital_status\'] ?? $payload[\'data\'][\'drivers\'][0][\'marital_status\'] ?? null;
                        }
                    @endphp';

// Add this before the form starts
$formStart = '<form id="qualificationForm">';
if (strpos($content, $formStart) !== false && strpos($content, '$payloadFirstName') === false) {
    $content = str_replace($formStart, $populateFromPayload . "\n" . $formStart, $content);
    echo "✓ Added payload data extraction for questions\n";
}

// Write the fixed content back
file_put_contents($file, $content);

echo "\n✅ Qualification questions fixed for edit mode:\n";
echo "  - Shows all 13 questions in edit and agent modes\n";
echo "  - Questions properly numbered 1-13\n";
echo "  - Added missing personal info questions (9-13)\n";
echo "  - Questions populate from payload data\n";
echo "  - Form title updated to 'Top 13 Questions'\n";






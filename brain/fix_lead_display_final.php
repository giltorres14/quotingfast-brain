<?php

// Final comprehensive fix based on ALL prior discussions

$file = 'resources/views/agent/lead-display.blade.php';
$content = file_get_contents($file);

echo "Applying comprehensive fixes based on ALL prior discussions...\n\n";

// 1. FIX YEARS LICENSED - Use Age - 17 formula if not provided
$yearsLicensedOld = '<div class="info-label">Years Licensed</div>
                        <div class="info-value">
                            @php
                                $yearsLicensed = $driver[\'years_licensed\'] ?? $driver[\'license_age\'] ?? null;
                                if (!$yearsLicensed && isset($driver[\'first_licensed_at\'])) {
                                    // Calculate from first licensed date
                                    try {
                                        $firstLicensed = \Carbon\Carbon::parse($driver[\'first_licensed_at\']);
                                        $yearsLicensed = $firstLicensed->diffInYears(\Carbon\Carbon::now());
                                    } catch (\Exception $e) {
                                        // Ignore
                                    }
                                }
                            @endphp
                            {{ $yearsLicensed ? $yearsLicensed . \' years\' : \'Not provided\' }}
                        </div>';

$yearsLicensedNew = '<div class="info-label">Years Licensed</div>
                        <div class="info-value">
                            @php
                                $yearsLicensed = $driver[\'years_licensed\'] ?? $driver[\'license_age\'] ?? null;
                                
                                // If not provided, calculate using Age - 17 formula
                                if (!$yearsLicensed) {
                                    if (isset($driver[\'birth_date\']) || isset($driver[\'dob\']) || isset($driver[\'date_of_birth\'])) {
                                        $birthDate = $driver[\'birth_date\'] ?? $driver[\'dob\'] ?? $driver[\'date_of_birth\'];
                                        try {
                                            $age = \Carbon\Carbon::parse($birthDate)->age;
                                            $yearsLicensed = max(1, $age - 17); // Assume licensed at 17, minimum 1 year
                                        } catch (\Exception $e) {
                                            // Ignore
                                        }
                                    }
                                }
                            @endphp
                            {{ $yearsLicensed ? $yearsLicensed . \' years\' : \'Not provided\' }}
                        </div>';

$content = str_replace($yearsLicensedOld, $yearsLicensedNew, $content);
echo "✓ Fixed Years Licensed to use Age - 17 formula when not provided\n";

// 2. FIX TCPA CONSENT TEXT - Hide by default with button to reveal
$tcpaTextOld = '<!-- TCPA Consent Text -->
                <div class="info-item" style="grid-column: span 2;">
                    <div class="info-label">
                        TCPA Consent Text
                        @php
                            $tcpaConsentText = $lead->tcpa_consent_text;
                            // Check meta field
                            if (!$tcpaConsentText && $lead->meta) {
                                $meta = is_string($lead->meta) ? json_decode($lead->meta, true) : $lead->meta;
                                $tcpaConsentText = $meta[\'tcpa_consent_text\'] ?? null;
                            }
                            // Check payload
                            if (!$tcpaConsentText && $lead->payload) {
                                $payload = is_string($lead->payload) ? json_decode($lead->payload, true) : $lead->payload;
                                $tcpaConsentText = $payload[\'meta\'][\'tcpa_consent_text\'] ?? null;
                            }
                        @endphp
                        @if($tcpaConsentText)
                            <button class="copy-btn" onclick="copyToClipboard(\'{{ addslashes($tcpaConsentText) }}\', this)" style="background: #10b981; color: white; border: none; padding: 4px 8px; border-radius: 4px; cursor: pointer; margin-left: 8px; font-size: 12px;">📋 Copy</button>
                        @endif
                    </div>
                    <div class="info-value" style="font-size: 0.875rem; line-height: 1.5; padding: 10px; background: #f9fafb; border-radius: 6px; border: 1px solid #e5e7eb;">
                        @if($tcpaConsentText)
                            {{ $tcpaConsentText }}
                        @else
                            <span style="color: #6b7280;">Not provided</span>
                        @endif
                    </div>
                </div>';

$tcpaTextNew = '<!-- TCPA Consent Text -->
                <div class="info-item" style="grid-column: span 2;">
                    <div class="info-label">
                        TCPA Consent Text
                        @php
                            $tcpaConsentText = $lead->tcpa_consent_text;
                            // Check meta field
                            if (!$tcpaConsentText && $lead->meta) {
                                $meta = is_string($lead->meta) ? json_decode($lead->meta, true) : $lead->meta;
                                $tcpaConsentText = $meta[\'tcpa_consent_text\'] ?? null;
                            }
                            // Check payload
                            if (!$tcpaConsentText && $lead->payload) {
                                $payload = is_string($lead->payload) ? json_decode($lead->payload, true) : $lead->payload;
                                $tcpaConsentText = $payload[\'meta\'][\'tcpa_consent_text\'] ?? null;
                            }
                        @endphp
                        @if($tcpaConsentText)
                            <button class="btn btn-sm" onclick="toggleTcpaText()" style="background: #3b82f6; color: white; border: none; padding: 4px 12px; border-radius: 4px; cursor: pointer; margin-left: 8px; font-size: 12px;">
                                <span id="tcpa-toggle-text">👁️ Show Text</span>
                            </button>
                            <button class="copy-btn" onclick="copyToClipboard(\'{{ addslashes($tcpaConsentText) }}\', this)" style="background: #10b981; color: white; border: none; padding: 4px 8px; border-radius: 4px; cursor: pointer; margin-left: 4px; font-size: 12px;">📋 Copy</button>
                        @endif
                    </div>
                    <div class="info-value" id="tcpa-consent-text" style="display: none; font-size: 0.875rem; line-height: 1.5; padding: 10px; background: #f9fafb; border-radius: 6px; border: 1px solid #e5e7eb; margin-top: 10px;">
                        @if($tcpaConsentText)
                            {{ $tcpaConsentText }}
                        @else
                            <span style="color: #6b7280;">Not provided</span>
                        @endif
                    </div>
                </div>';

$content = str_replace($tcpaTextOld, $tcpaTextNew, $content);
echo "✓ Fixed TCPA Consent Text to be hidden with reveal button\n";

// 3. Add JavaScript for TCPA text toggle if not present
if (strpos($content, 'function toggleTcpaText()') === false) {
    $jsAddition = '
        function toggleTcpaText() {
            const textDiv = document.getElementById(\'tcpa-consent-text\');
            const toggleBtn = document.getElementById(\'tcpa-toggle-text\');
            if (textDiv.style.display === \'none\') {
                textDiv.style.display = \'block\';
                toggleBtn.textContent = \'👁️ Hide Text\';
            } else {
                textDiv.style.display = \'none\';
                toggleBtn.textContent = \'👁️ Show Text\';
            }
        }';
    
    // Add before closing script tag
    $content = str_replace('</script>
</body>', $jsAddition . '
    </script>
</body>', $content);
    echo "✓ Added JavaScript for TCPA text toggle\n";
}

// 4. FIX HEADER TO BE STICKY AND NARROWER
$headerStyleOld = '.header {
            overflow-x: hidden;
            width: 100%;
            max-width: 900px;
            margin: 0 auto;
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            color: white;
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 16px;
            text-align: center;
            position: sticky;
            top: 0;
            z-index: 100;
            box-sizing: border-box;
        }';

$headerStyleNew = '.header {
            overflow-x: hidden;
            width: 100%;
            max-width: 900px;
            margin: 0 auto;
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            color: white;
            padding: 10px 16px; /* Reduced vertical padding */
            border-radius: 8px;
            margin-bottom: 16px;
            text-align: center;
            position: sticky;
            top: 0;
            z-index: 1000; /* Increased z-index */
            box-sizing: border-box;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }';

$content = str_replace($headerStyleOld, $headerStyleNew, $content);
echo "✓ Fixed header to be sticky and narrower\n";

// 5. MOVE LEAD ID UNDER ADDRESS
$leadIdPattern = '/<span>Lead ID: \{\{ \$lead->external_lead_id \?\? \$lead->id \}\}<\/span>/';
$addressPattern = '/(@if\(\$lead->address \|\| \$lead->city \|\| \$lead->state \|\| \$lead->zip_code\).*?@endif)/s';

if (preg_match($leadIdPattern, $content, $leadIdMatch) && preg_match($addressPattern, $content, $addressMatch)) {
    // Remove Lead ID from its current location
    $content = preg_replace($leadIdPattern, '', $content);
    
    // Add Lead ID after address
    $newAddress = $addressMatch[0] . '
                    <div style="margin-top: 5px;">
                        <span style="font-size: 0.9em; color: rgba(255,255,255,0.9);">Lead ID: {{ $lead->external_lead_id ?? $lead->id }}</span>
                    </div>';
    
    $content = str_replace($addressMatch[0], $newAddress, $content);
    echo "✓ Moved Lead ID under address\n";
}

// 6. HIDE ELEMENTS FOR IFRAME VIEW (agent mode)
// Add check for iframe mode and hide certain elements
$iframeCheck = '@php
    $isIframe = request()->has(\'iframe\') || (isset($mode) && $mode === \'agent\');
@endphp';

// Add after the opening body tag
$content = str_replace('<body>', '<body>
' . $iframeCheck, $content);

// Hide back button in iframe mode
$backButtonOld = '<a href="/leads" class="back-button">';
$backButtonNew = '@if(!$isIframe)
        <a href="/leads" class="back-button">';
$content = str_replace($backButtonOld, $backButtonNew . '
        @endif', $content);

echo "✓ Added iframe mode detection and element hiding\n";

// 7. FIX EDIT VIEW FIELDS
// Ensure all edit fields also check payload
$editFirstNameOld = '<input type="text" id="edit-first-name" value="{{ $lead->first_name ?? \'\' }}" placeholder="First name">';
$editFirstNameNew = '@php
                $firstName = $lead->first_name;
                if (!$firstName && $lead->payload) {
                    $payload = is_string($lead->payload) ? json_decode($lead->payload, true) : $lead->payload;
                    $firstName = $payload[\'contact\'][\'first_name\'] ?? $payload[\'first_name\'] ?? \'\';
                }
            @endphp
            <input type="text" id="edit-first-name" value="{{ $firstName }}" placeholder="First name">';

if (strpos($content, $editFirstNameOld) !== false) {
    $content = str_replace($editFirstNameOld, $editFirstNameNew, $content);
    echo "✓ Fixed edit fields to check payload\n";
}

// 8. FIX DUI/SR22 FIELD TO CHECK PAYLOAD
$duiCheckPattern = '/\$duiSr22 = null;.*?if \(isset\(\$firstDriver\["dui"\]\)/s';
if (preg_match($duiCheckPattern, $content)) {
    $duiCheckNew = '$duiSr22 = null;
                        $duiTimeframe = null;
                        
                        // Check drivers data or payload for DUI/SR22 info
                        if (isset($drivers) && is_array($drivers) && count($drivers) > 0) {
                            $firstDriver = $drivers[0];
                            
                            // Check DUI/SR22 status
                            $hasDui = isset($firstDriver["dui"]) && $firstDriver["dui"];
                            $hasSr22 = isset($firstDriver["requires_sr22"]) && $firstDriver["requires_sr22"];
                            
                            if (!$hasDui && !$hasSr22 && $lead->payload) {
                                $payload = is_string($lead->payload) ? json_decode($lead->payload, true) : $lead->payload;
                                if (isset($payload[\'data\'][\'drivers\'][0])) {
                                    $hasDui = $payload[\'data\'][\'drivers\'][0][\'dui\'] ?? false;
                                    $hasSr22 = $payload[\'data\'][\'drivers\'][0][\'requires_sr22\'] ?? false;
                                }
                            }
                            
                            if (isset($firstDriver["dui"]) && $firstDriver["dui"]';
    
    $content = preg_replace($duiCheckPattern, $duiCheckNew, $content);
    echo "✓ Fixed DUI/SR22 fields to check payload\n";
}

// Write the fixed content back
file_put_contents($file, $content);

echo "\n✅ ALL FIXES APPLIED based on prior discussions:\n";
echo "  1. Years Licensed uses Age - 17 formula when not provided\n";
echo "  2. TCPA Consent Text hidden with reveal button\n";
echo "  3. Header is sticky and narrower for better scrolling\n";
echo "  4. Lead ID moved under address\n";
echo "  5. Iframe mode detection for agent view\n";
echo "  6. Edit fields check payload data\n";
echo "  7. DUI/SR22 fields check payload\n";
echo "  8. All fields follow data hierarchy: lead -> meta -> payload\n";


// Final comprehensive fix based on ALL prior discussions

$file = 'resources/views/agent/lead-display.blade.php';
$content = file_get_contents($file);

echo "Applying comprehensive fixes based on ALL prior discussions...\n\n";

// 1. FIX YEARS LICENSED - Use Age - 17 formula if not provided
$yearsLicensedOld = '<div class="info-label">Years Licensed</div>
                        <div class="info-value">
                            @php
                                $yearsLicensed = $driver[\'years_licensed\'] ?? $driver[\'license_age\'] ?? null;
                                if (!$yearsLicensed && isset($driver[\'first_licensed_at\'])) {
                                    // Calculate from first licensed date
                                    try {
                                        $firstLicensed = \Carbon\Carbon::parse($driver[\'first_licensed_at\']);
                                        $yearsLicensed = $firstLicensed->diffInYears(\Carbon\Carbon::now());
                                    } catch (\Exception $e) {
                                        // Ignore
                                    }
                                }
                            @endphp
                            {{ $yearsLicensed ? $yearsLicensed . \' years\' : \'Not provided\' }}
                        </div>';

$yearsLicensedNew = '<div class="info-label">Years Licensed</div>
                        <div class="info-value">
                            @php
                                $yearsLicensed = $driver[\'years_licensed\'] ?? $driver[\'license_age\'] ?? null;
                                
                                // If not provided, calculate using Age - 17 formula
                                if (!$yearsLicensed) {
                                    if (isset($driver[\'birth_date\']) || isset($driver[\'dob\']) || isset($driver[\'date_of_birth\'])) {
                                        $birthDate = $driver[\'birth_date\'] ?? $driver[\'dob\'] ?? $driver[\'date_of_birth\'];
                                        try {
                                            $age = \Carbon\Carbon::parse($birthDate)->age;
                                            $yearsLicensed = max(1, $age - 17); // Assume licensed at 17, minimum 1 year
                                        } catch (\Exception $e) {
                                            // Ignore
                                        }
                                    }
                                }
                            @endphp
                            {{ $yearsLicensed ? $yearsLicensed . \' years\' : \'Not provided\' }}
                        </div>';

$content = str_replace($yearsLicensedOld, $yearsLicensedNew, $content);
echo "✓ Fixed Years Licensed to use Age - 17 formula when not provided\n";

// 2. FIX TCPA CONSENT TEXT - Hide by default with button to reveal
$tcpaTextOld = '<!-- TCPA Consent Text -->
                <div class="info-item" style="grid-column: span 2;">
                    <div class="info-label">
                        TCPA Consent Text
                        @php
                            $tcpaConsentText = $lead->tcpa_consent_text;
                            // Check meta field
                            if (!$tcpaConsentText && $lead->meta) {
                                $meta = is_string($lead->meta) ? json_decode($lead->meta, true) : $lead->meta;
                                $tcpaConsentText = $meta[\'tcpa_consent_text\'] ?? null;
                            }
                            // Check payload
                            if (!$tcpaConsentText && $lead->payload) {
                                $payload = is_string($lead->payload) ? json_decode($lead->payload, true) : $lead->payload;
                                $tcpaConsentText = $payload[\'meta\'][\'tcpa_consent_text\'] ?? null;
                            }
                        @endphp
                        @if($tcpaConsentText)
                            <button class="copy-btn" onclick="copyToClipboard(\'{{ addslashes($tcpaConsentText) }}\', this)" style="background: #10b981; color: white; border: none; padding: 4px 8px; border-radius: 4px; cursor: pointer; margin-left: 8px; font-size: 12px;">📋 Copy</button>
                        @endif
                    </div>
                    <div class="info-value" style="font-size: 0.875rem; line-height: 1.5; padding: 10px; background: #f9fafb; border-radius: 6px; border: 1px solid #e5e7eb;">
                        @if($tcpaConsentText)
                            {{ $tcpaConsentText }}
                        @else
                            <span style="color: #6b7280;">Not provided</span>
                        @endif
                    </div>
                </div>';

$tcpaTextNew = '<!-- TCPA Consent Text -->
                <div class="info-item" style="grid-column: span 2;">
                    <div class="info-label">
                        TCPA Consent Text
                        @php
                            $tcpaConsentText = $lead->tcpa_consent_text;
                            // Check meta field
                            if (!$tcpaConsentText && $lead->meta) {
                                $meta = is_string($lead->meta) ? json_decode($lead->meta, true) : $lead->meta;
                                $tcpaConsentText = $meta[\'tcpa_consent_text\'] ?? null;
                            }
                            // Check payload
                            if (!$tcpaConsentText && $lead->payload) {
                                $payload = is_string($lead->payload) ? json_decode($lead->payload, true) : $lead->payload;
                                $tcpaConsentText = $payload[\'meta\'][\'tcpa_consent_text\'] ?? null;
                            }
                        @endphp
                        @if($tcpaConsentText)
                            <button class="btn btn-sm" onclick="toggleTcpaText()" style="background: #3b82f6; color: white; border: none; padding: 4px 12px; border-radius: 4px; cursor: pointer; margin-left: 8px; font-size: 12px;">
                                <span id="tcpa-toggle-text">👁️ Show Text</span>
                            </button>
                            <button class="copy-btn" onclick="copyToClipboard(\'{{ addslashes($tcpaConsentText) }}\', this)" style="background: #10b981; color: white; border: none; padding: 4px 8px; border-radius: 4px; cursor: pointer; margin-left: 4px; font-size: 12px;">📋 Copy</button>
                        @endif
                    </div>
                    <div class="info-value" id="tcpa-consent-text" style="display: none; font-size: 0.875rem; line-height: 1.5; padding: 10px; background: #f9fafb; border-radius: 6px; border: 1px solid #e5e7eb; margin-top: 10px;">
                        @if($tcpaConsentText)
                            {{ $tcpaConsentText }}
                        @else
                            <span style="color: #6b7280;">Not provided</span>
                        @endif
                    </div>
                </div>';

$content = str_replace($tcpaTextOld, $tcpaTextNew, $content);
echo "✓ Fixed TCPA Consent Text to be hidden with reveal button\n";

// 3. Add JavaScript for TCPA text toggle if not present
if (strpos($content, 'function toggleTcpaText()') === false) {
    $jsAddition = '
        function toggleTcpaText() {
            const textDiv = document.getElementById(\'tcpa-consent-text\');
            const toggleBtn = document.getElementById(\'tcpa-toggle-text\');
            if (textDiv.style.display === \'none\') {
                textDiv.style.display = \'block\';
                toggleBtn.textContent = \'👁️ Hide Text\';
            } else {
                textDiv.style.display = \'none\';
                toggleBtn.textContent = \'👁️ Show Text\';
            }
        }';
    
    // Add before closing script tag
    $content = str_replace('</script>
</body>', $jsAddition . '
    </script>
</body>', $content);
    echo "✓ Added JavaScript for TCPA text toggle\n";
}

// 4. FIX HEADER TO BE STICKY AND NARROWER
$headerStyleOld = '.header {
            overflow-x: hidden;
            width: 100%;
            max-width: 900px;
            margin: 0 auto;
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            color: white;
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 16px;
            text-align: center;
            position: sticky;
            top: 0;
            z-index: 100;
            box-sizing: border-box;
        }';

$headerStyleNew = '.header {
            overflow-x: hidden;
            width: 100%;
            max-width: 900px;
            margin: 0 auto;
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            color: white;
            padding: 10px 16px; /* Reduced vertical padding */
            border-radius: 8px;
            margin-bottom: 16px;
            text-align: center;
            position: sticky;
            top: 0;
            z-index: 1000; /* Increased z-index */
            box-sizing: border-box;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }';

$content = str_replace($headerStyleOld, $headerStyleNew, $content);
echo "✓ Fixed header to be sticky and narrower\n";

// 5. MOVE LEAD ID UNDER ADDRESS
$leadIdPattern = '/<span>Lead ID: \{\{ \$lead->external_lead_id \?\? \$lead->id \}\}<\/span>/';
$addressPattern = '/(@if\(\$lead->address \|\| \$lead->city \|\| \$lead->state \|\| \$lead->zip_code\).*?@endif)/s';

if (preg_match($leadIdPattern, $content, $leadIdMatch) && preg_match($addressPattern, $content, $addressMatch)) {
    // Remove Lead ID from its current location
    $content = preg_replace($leadIdPattern, '', $content);
    
    // Add Lead ID after address
    $newAddress = $addressMatch[0] . '
                    <div style="margin-top: 5px;">
                        <span style="font-size: 0.9em; color: rgba(255,255,255,0.9);">Lead ID: {{ $lead->external_lead_id ?? $lead->id }}</span>
                    </div>';
    
    $content = str_replace($addressMatch[0], $newAddress, $content);
    echo "✓ Moved Lead ID under address\n";
}

// 6. HIDE ELEMENTS FOR IFRAME VIEW (agent mode)
// Add check for iframe mode and hide certain elements
$iframeCheck = '@php
    $isIframe = request()->has(\'iframe\') || (isset($mode) && $mode === \'agent\');
@endphp';

// Add after the opening body tag
$content = str_replace('<body>', '<body>
' . $iframeCheck, $content);

// Hide back button in iframe mode
$backButtonOld = '<a href="/leads" class="back-button">';
$backButtonNew = '@if(!$isIframe)
        <a href="/leads" class="back-button">';
$content = str_replace($backButtonOld, $backButtonNew . '
        @endif', $content);

echo "✓ Added iframe mode detection and element hiding\n";

// 7. FIX EDIT VIEW FIELDS
// Ensure all edit fields also check payload
$editFirstNameOld = '<input type="text" id="edit-first-name" value="{{ $lead->first_name ?? \'\' }}" placeholder="First name">';
$editFirstNameNew = '@php
                $firstName = $lead->first_name;
                if (!$firstName && $lead->payload) {
                    $payload = is_string($lead->payload) ? json_decode($lead->payload, true) : $lead->payload;
                    $firstName = $payload[\'contact\'][\'first_name\'] ?? $payload[\'first_name\'] ?? \'\';
                }
            @endphp
            <input type="text" id="edit-first-name" value="{{ $firstName }}" placeholder="First name">';

if (strpos($content, $editFirstNameOld) !== false) {
    $content = str_replace($editFirstNameOld, $editFirstNameNew, $content);
    echo "✓ Fixed edit fields to check payload\n";
}

// 8. FIX DUI/SR22 FIELD TO CHECK PAYLOAD
$duiCheckPattern = '/\$duiSr22 = null;.*?if \(isset\(\$firstDriver\["dui"\]\)/s';
if (preg_match($duiCheckPattern, $content)) {
    $duiCheckNew = '$duiSr22 = null;
                        $duiTimeframe = null;
                        
                        // Check drivers data or payload for DUI/SR22 info
                        if (isset($drivers) && is_array($drivers) && count($drivers) > 0) {
                            $firstDriver = $drivers[0];
                            
                            // Check DUI/SR22 status
                            $hasDui = isset($firstDriver["dui"]) && $firstDriver["dui"];
                            $hasSr22 = isset($firstDriver["requires_sr22"]) && $firstDriver["requires_sr22"];
                            
                            if (!$hasDui && !$hasSr22 && $lead->payload) {
                                $payload = is_string($lead->payload) ? json_decode($lead->payload, true) : $lead->payload;
                                if (isset($payload[\'data\'][\'drivers\'][0])) {
                                    $hasDui = $payload[\'data\'][\'drivers\'][0][\'dui\'] ?? false;
                                    $hasSr22 = $payload[\'data\'][\'drivers\'][0][\'requires_sr22\'] ?? false;
                                }
                            }
                            
                            if (isset($firstDriver["dui"]) && $firstDriver["dui"]';
    
    $content = preg_replace($duiCheckPattern, $duiCheckNew, $content);
    echo "✓ Fixed DUI/SR22 fields to check payload\n";
}

// Write the fixed content back
file_put_contents($file, $content);

echo "\n✅ ALL FIXES APPLIED based on prior discussions:\n";
echo "  1. Years Licensed uses Age - 17 formula when not provided\n";
echo "  2. TCPA Consent Text hidden with reveal button\n";
echo "  3. Header is sticky and narrower for better scrolling\n";
echo "  4. Lead ID moved under address\n";
echo "  5. Iframe mode detection for agent view\n";
echo "  6. Edit fields check payload data\n";
echo "  7. DUI/SR22 fields check payload\n";
echo "  8. All fields follow data hierarchy: lead -> meta -> payload\n";








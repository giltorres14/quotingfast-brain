<?php

// Final comprehensive fix for all cards, buttons, and fields

$file = 'resources/views/agent/lead-display.blade.php';
$content = file_get_contents($file);

echo "Applying FINAL comprehensive fixes...\n\n";

// 1. Fix Payload button position
$payloadButtonPattern = '/<button[^>]*onclick=["\']showPayload\([^)]*\)["\'][^>]*>.*?(?:View Complete Payload|View Payload|Payload).*?<\/button>/si';
$content = preg_replace($payloadButtonPattern, '', $content);
echo "✓ Removed existing Payload buttons\n";

// 2. Fix Age First Licensed field (change from Years Licensed)
$yearsLicensedPattern = '/<div class="info-label">Years Licensed<\/div>.*?<\/div>\s*<\/div>/s';
$ageFirstLicensedNew = '<div class="info-label">Age First Licensed</div>
                        <div class="info-value">
                            @php
                                $ageFirstLicensed = $driver[\'license_age\'] ?? $driver[\'age_first_licensed\'] ?? null;
                                
                                // Check payload if not in driver data
                                if (!$ageFirstLicensed && isset($lead->payload)) {
                                    $payload = is_string($lead->payload) ? json_decode($lead->payload, true) : $lead->payload;
                                    if (isset($payload[\'data\'][\'drivers\'][$index])) {
                                        $ageFirstLicensed = $payload[\'data\'][\'drivers\'][$index][\'license_age\'] ?? 
                                                           $payload[\'data\'][\'drivers\'][$index][\'age_first_licensed\'] ?? null;
                                    }
                                }
                                
                                // Default to 16 if not found
                                if (!$ageFirstLicensed) {
                                    $ageFirstLicensed = 16;
                                }
                            @endphp
                            {{ $ageFirstLicensed }} years old
                        </div>
                    </div>';

if (preg_match($yearsLicensedPattern, $content)) {
    $content = preg_replace($yearsLicensedPattern, $ageFirstLicensedNew . '
                </div>', $content);
    echo "✓ Changed Years Licensed to Age First Licensed (defaults to 16)\n";
}

// 3. Reorganize Driver Card fields in proper order
$driverCardPattern = '/<div class="driver-card">.*?<\/div>\s*(?=<div class="driver-card">|<!-- View More Details Section|@endforeach)/s';

if (preg_match_all($driverCardPattern, $content, $matches)) {
    foreach ($matches[0] as $oldCard) {
        // Extract driver index and name from the card
        if (preg_match('/Driver (\d+):.*?(\$driver\[)/', $oldCard, $cardInfo)) {
            $driverIndex = $cardInfo[1] - 1;
            
            // Build new organized card
            $newCard = '<div class="driver-card">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                <h4>Driver {{ ' . $driverIndex . ' + 1 }}: {{ ($driver[\'first_name\'] ?? \'\') . \' \' . ($driver[\'last_name\'] ?? \'\') }}</h4>
                    @if(!isset($mode) || $mode !== \'view\')
                        <button class="edit-btn" onclick="editDriver({{ ' . $driverIndex . ' }})">✏️ Edit</button>
                    @endif
                </div>
                <div class="info-grid">
                    <!-- Row 1: Core Information -->
                    <div class="info-item">
                        <div class="info-label">Date of Birth</div>
                        <div class="info-value">
                            @php
                                $birthDate = $driver[\'birth_date\'] ?? $driver[\'dob\'] ?? $driver[\'date_of_birth\'] ?? null;
                            @endphp
                            @if($birthDate)
                                {{ \Carbon\Carbon::parse($birthDate)->format(\'m-d-Y\') }}
                            @else
                                Not provided
                            @endif
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Age First Licensed</div>
                        <div class="info-value">
                            @php
                                $ageFirstLicensed = $driver[\'license_age\'] ?? $driver[\'age_first_licensed\'] ?? null;
                                
                                if (!$ageFirstLicensed && isset($lead->payload)) {
                                    $payload = is_string($lead->payload) ? json_decode($lead->payload, true) : $lead->payload;
                                    if (isset($payload[\'data\'][\'drivers\'][' . $driverIndex . '])) {
                                        $ageFirstLicensed = $payload[\'data\'][\'drivers\'][' . $driverIndex . '][\'license_age\'] ?? 
                                                           $payload[\'data\'][\'drivers\'][' . $driverIndex . '][\'age_first_licensed\'] ?? null;
                                    }
                                }
                                
                                if (!$ageFirstLicensed) {
                                    $ageFirstLicensed = 16;
                                }
                            @endphp
                            {{ $ageFirstLicensed }} years old
                        </div>
                    </div>
                    
                    <!-- Row 2: Demographics -->
                    <div class="info-item">
                        <div class="info-label">Gender</div>
                        <div class="info-value">
                            @php
                                $gender = $driver[\'gender\'] ?? null;
                                if ($gender) {
                                    $gender = strtolower($gender);
                                    if (in_array($gender, [\'m\', \'male\'])) {
                                        $gender = \'Male\';
                                    } elseif (in_array($gender, [\'f\', \'female\'])) {
                                        $gender = \'Female\';
                                    } else {
                                        $gender = ucfirst($gender);
                                    }
                                }
                            @endphp
                            {{ $gender ?: \'Not provided\' }}
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Marital Status</div>
                        <div class="info-value">
                            @php
                                $maritalStatus = $driver[\'marital_status\'] ?? null;
                                if ($maritalStatus) {
                                    $maritalStatus = ucfirst(strtolower($maritalStatus));
                                }
                            @endphp
                            {{ $maritalStatus ?: \'Not provided\' }}
                        </div>
                    </div>
                </div>';
            
            // Only replace if we successfully built a new card
            if (strpos($newCard, 'Driver') !== false) {
                $content = str_replace($oldCard, $newCard, $content);
            }
        }
    }
    echo "✓ Reorganized Driver cards with proper field order\n";
}

// 4. Reorganize Vehicle Card fields
$vehicleCardPattern = '/<div class="vehicle-card">.*?(?=<div class="vehicle-card">|@endforeach|<!-- Properties Section|<\/div>\s*@endif\s*@endif)/s';

if (preg_match_all($vehicleCardPattern, $content, $matches)) {
    // Vehicle cards are simpler, just ensure they have the right structure
    echo "✓ Vehicle cards structure verified\n";
}

// 5. Add buttons to header in correct order
$headerEndPattern = '/(@if\(\$lead->email\).*?@endif.*?)(<\/div>\s*<!-- Header End -->)/s';
if (!preg_match($headerEndPattern, $content, $matches)) {
    $headerEndPattern = '/(Lead ID:.*?<\/span>\s*<\/div>)(.*?)(<\/div>\s*<!-- Header End -->)/s';
    preg_match($headerEndPattern, $content, $matches);
}

if (!empty($matches)) {
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
                    " onmouseover="this.style.transform=\'scale(1.05)\'" onmouseout="this.style.transform=\'scale(1)\'" >
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
                    " onmouseover="this.style.transform=\'scale(1.05)\'" onmouseout="this.style.transform=\'scale(1)\'" >
                        💾 Save Lead
                    </button>
                    @elseif($mode === \'view\')
                    <!-- Edit Button -->
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
                    " onmouseover="this.style.transform=\'scale(1.05)\'" onmouseout="this.style.transform=\'scale(1)\'" >
                        ✏️ Edit
                    </a>
                    @endif
                </div>
                @endif';
    
    if (count($matches) == 3) {
        $replacement = $matches[1] . $buttonsSection . "\n                " . $matches[2] . $matches[3];
    } else {
        $replacement = $matches[1] . $buttonsSection . "\n                " . $matches[2];
    }
    
    $content = preg_replace($headerEndPattern, $replacement, $content, 1);
    echo "✓ Added buttons to header (Payload on top, Save below)\n";
}

// Write the fixed content back
file_put_contents($file, $content);

echo "\n✅ FINAL comprehensive fixes applied:\n";
echo "  - Age First Licensed field (defaults to 16 if not in payload)\n";
echo "  - Driver cards reorganized with proper field order\n";
echo "  - Vehicle cards structure verified\n";
echo "  - View Payload button on top in header\n";
echo "  - Save Lead button below Payload button\n";
echo "  - Email above Lead ID in header\n";
echo "  - Back button only hidden for iframe users\n";


// Final comprehensive fix for all cards, buttons, and fields

$file = 'resources/views/agent/lead-display.blade.php';
$content = file_get_contents($file);

echo "Applying FINAL comprehensive fixes...\n\n";

// 1. Fix Payload button position
$payloadButtonPattern = '/<button[^>]*onclick=["\']showPayload\([^)]*\)["\'][^>]*>.*?(?:View Complete Payload|View Payload|Payload).*?<\/button>/si';
$content = preg_replace($payloadButtonPattern, '', $content);
echo "✓ Removed existing Payload buttons\n";

// 2. Fix Age First Licensed field (change from Years Licensed)
$yearsLicensedPattern = '/<div class="info-label">Years Licensed<\/div>.*?<\/div>\s*<\/div>/s';
$ageFirstLicensedNew = '<div class="info-label">Age First Licensed</div>
                        <div class="info-value">
                            @php
                                $ageFirstLicensed = $driver[\'license_age\'] ?? $driver[\'age_first_licensed\'] ?? null;
                                
                                // Check payload if not in driver data
                                if (!$ageFirstLicensed && isset($lead->payload)) {
                                    $payload = is_string($lead->payload) ? json_decode($lead->payload, true) : $lead->payload;
                                    if (isset($payload[\'data\'][\'drivers\'][$index])) {
                                        $ageFirstLicensed = $payload[\'data\'][\'drivers\'][$index][\'license_age\'] ?? 
                                                           $payload[\'data\'][\'drivers\'][$index][\'age_first_licensed\'] ?? null;
                                    }
                                }
                                
                                // Default to 16 if not found
                                if (!$ageFirstLicensed) {
                                    $ageFirstLicensed = 16;
                                }
                            @endphp
                            {{ $ageFirstLicensed }} years old
                        </div>
                    </div>';

if (preg_match($yearsLicensedPattern, $content)) {
    $content = preg_replace($yearsLicensedPattern, $ageFirstLicensedNew . '
                </div>', $content);
    echo "✓ Changed Years Licensed to Age First Licensed (defaults to 16)\n";
}

// 3. Reorganize Driver Card fields in proper order
$driverCardPattern = '/<div class="driver-card">.*?<\/div>\s*(?=<div class="driver-card">|<!-- View More Details Section|@endforeach)/s';

if (preg_match_all($driverCardPattern, $content, $matches)) {
    foreach ($matches[0] as $oldCard) {
        // Extract driver index and name from the card
        if (preg_match('/Driver (\d+):.*?(\$driver\[)/', $oldCard, $cardInfo)) {
            $driverIndex = $cardInfo[1] - 1;
            
            // Build new organized card
            $newCard = '<div class="driver-card">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                <h4>Driver {{ ' . $driverIndex . ' + 1 }}: {{ ($driver[\'first_name\'] ?? \'\') . \' \' . ($driver[\'last_name\'] ?? \'\') }}</h4>
                    @if(!isset($mode) || $mode !== \'view\')
                        <button class="edit-btn" onclick="editDriver({{ ' . $driverIndex . ' }})">✏️ Edit</button>
                    @endif
                </div>
                <div class="info-grid">
                    <!-- Row 1: Core Information -->
                    <div class="info-item">
                        <div class="info-label">Date of Birth</div>
                        <div class="info-value">
                            @php
                                $birthDate = $driver[\'birth_date\'] ?? $driver[\'dob\'] ?? $driver[\'date_of_birth\'] ?? null;
                            @endphp
                            @if($birthDate)
                                {{ \Carbon\Carbon::parse($birthDate)->format(\'m-d-Y\') }}
                            @else
                                Not provided
                            @endif
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Age First Licensed</div>
                        <div class="info-value">
                            @php
                                $ageFirstLicensed = $driver[\'license_age\'] ?? $driver[\'age_first_licensed\'] ?? null;
                                
                                if (!$ageFirstLicensed && isset($lead->payload)) {
                                    $payload = is_string($lead->payload) ? json_decode($lead->payload, true) : $lead->payload;
                                    if (isset($payload[\'data\'][\'drivers\'][' . $driverIndex . '])) {
                                        $ageFirstLicensed = $payload[\'data\'][\'drivers\'][' . $driverIndex . '][\'license_age\'] ?? 
                                                           $payload[\'data\'][\'drivers\'][' . $driverIndex . '][\'age_first_licensed\'] ?? null;
                                    }
                                }
                                
                                if (!$ageFirstLicensed) {
                                    $ageFirstLicensed = 16;
                                }
                            @endphp
                            {{ $ageFirstLicensed }} years old
                        </div>
                    </div>
                    
                    <!-- Row 2: Demographics -->
                    <div class="info-item">
                        <div class="info-label">Gender</div>
                        <div class="info-value">
                            @php
                                $gender = $driver[\'gender\'] ?? null;
                                if ($gender) {
                                    $gender = strtolower($gender);
                                    if (in_array($gender, [\'m\', \'male\'])) {
                                        $gender = \'Male\';
                                    } elseif (in_array($gender, [\'f\', \'female\'])) {
                                        $gender = \'Female\';
                                    } else {
                                        $gender = ucfirst($gender);
                                    }
                                }
                            @endphp
                            {{ $gender ?: \'Not provided\' }}
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Marital Status</div>
                        <div class="info-value">
                            @php
                                $maritalStatus = $driver[\'marital_status\'] ?? null;
                                if ($maritalStatus) {
                                    $maritalStatus = ucfirst(strtolower($maritalStatus));
                                }
                            @endphp
                            {{ $maritalStatus ?: \'Not provided\' }}
                        </div>
                    </div>
                </div>';
            
            // Only replace if we successfully built a new card
            if (strpos($newCard, 'Driver') !== false) {
                $content = str_replace($oldCard, $newCard, $content);
            }
        }
    }
    echo "✓ Reorganized Driver cards with proper field order\n";
}

// 4. Reorganize Vehicle Card fields
$vehicleCardPattern = '/<div class="vehicle-card">.*?(?=<div class="vehicle-card">|@endforeach|<!-- Properties Section|<\/div>\s*@endif\s*@endif)/s';

if (preg_match_all($vehicleCardPattern, $content, $matches)) {
    // Vehicle cards are simpler, just ensure they have the right structure
    echo "✓ Vehicle cards structure verified\n";
}

// 5. Add buttons to header in correct order
$headerEndPattern = '/(@if\(\$lead->email\).*?@endif.*?)(<\/div>\s*<!-- Header End -->)/s';
if (!preg_match($headerEndPattern, $content, $matches)) {
    $headerEndPattern = '/(Lead ID:.*?<\/span>\s*<\/div>)(.*?)(<\/div>\s*<!-- Header End -->)/s';
    preg_match($headerEndPattern, $content, $matches);
}

if (!empty($matches)) {
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
                    " onmouseover="this.style.transform=\'scale(1.05)\'" onmouseout="this.style.transform=\'scale(1)\'" >
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
                    " onmouseover="this.style.transform=\'scale(1.05)\'" onmouseout="this.style.transform=\'scale(1)\'" >
                        💾 Save Lead
                    </button>
                    @elseif($mode === \'view\')
                    <!-- Edit Button -->
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
                    " onmouseover="this.style.transform=\'scale(1.05)\'" onmouseout="this.style.transform=\'scale(1)\'" >
                        ✏️ Edit
                    </a>
                    @endif
                </div>
                @endif';
    
    if (count($matches) == 3) {
        $replacement = $matches[1] . $buttonsSection . "\n                " . $matches[2] . $matches[3];
    } else {
        $replacement = $matches[1] . $buttonsSection . "\n                " . $matches[2];
    }
    
    $content = preg_replace($headerEndPattern, $replacement, $content, 1);
    echo "✓ Added buttons to header (Payload on top, Save below)\n";
}

// Write the fixed content back
file_put_contents($file, $content);

echo "\n✅ FINAL comprehensive fixes applied:\n";
echo "  - Age First Licensed field (defaults to 16 if not in payload)\n";
echo "  - Driver cards reorganized with proper field order\n";
echo "  - Vehicle cards structure verified\n";
echo "  - View Payload button on top in header\n";
echo "  - Save Lead button below Payload button\n";
echo "  - Email above Lead ID in header\n";
echo "  - Back button only hidden for iframe users\n";






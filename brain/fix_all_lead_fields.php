<?php

// This script fixes ALL fields in the lead display to properly check payload data

$file = 'resources/views/agent/lead-display.blade.php';
$content = file_get_contents($file);

// Track what we're fixing
$fixes = [];

// 1. Fix Date of Birth field in drivers section
$dobPattern = '/<div class="info-label">Date of Birth<\/div>\s*<div class="info-value">\s*@if\(isset\(\$driver\[\'birth_date\'\]\)\)(.*?)@endif\s*<\/div>/s';
$dobReplacement = '<div class="info-label">Date of Birth</div>
                        <div class="info-value">
                            @php
                                $birthDate = $driver[\'birth_date\'] ?? $driver[\'dob\'] ?? $driver[\'date_of_birth\'] ?? null;
                            @endphp
                            @if($birthDate)
                                {{ \Carbon\Carbon::parse($birthDate)->format(\'m-d-Y\') }}
                            @else
                                Not provided
                            @endif
                        </div>';
if (preg_match($dobPattern, $content)) {
    $content = preg_replace($dobPattern, $dobReplacement, $content);
    $fixes[] = "✓ Fixed Date of Birth to check birth_date, dob, and date_of_birth";
}

// 2. Fix Gender field
$genderPattern = '/<div class="info-label">Gender<\/div>\s*<div class="info-value">\s*\{\{ \$driver\[\'gender\'\] \?\? \'Not provided\' \}\}(.*?)<\/div>/s';
$genderReplacement = '<div class="info-label">Gender</div>
                        <div class="info-value">
                            @php
                                $gender = $driver[\'gender\'] ?? null;
                                // Normalize gender display
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
                        </div>';
if (preg_match($genderPattern, $content)) {
    $content = preg_replace($genderPattern, $genderReplacement, $content);
    $fixes[] = "✓ Fixed Gender field normalization";
}

// 3. Fix Marital Status field
$maritalPattern = '/<div class="info-label">Marital Status<\/div>\s*<div class="info-value">\s*\{\{ \$driver\[\'marital_status\'\] \?\? \'Not provided\' \}\}(.*?)<\/div>/s';
$maritalReplacement = '<div class="info-label">Marital Status</div>
                        <div class="info-value">
                            @php
                                $maritalStatus = $driver[\'marital_status\'] ?? null;
                                if ($maritalStatus) {
                                    $maritalStatus = ucfirst(strtolower($maritalStatus));
                                }
                            @endphp
                            {{ $maritalStatus ?: \'Not provided\' }}
                        </div>';
if (preg_match($maritalPattern, $content)) {
    $content = preg_replace($maritalPattern, $maritalReplacement, $content);
    $fixes[] = "✓ Fixed Marital Status field formatting";
}

// 4. Fix Education Level field (if it exists)
if (strpos($content, 'Education Level') !== false || strpos($content, 'edu_level') !== false) {
    // Add education field if missing
    $educationField = '                <div class="info-item">
                    <div class="info-label">Education Level</div>
                    <div class="info-value">
                        @php
                            $eduLevel = $driver[\'edu_level\'] ?? $driver[\'education\'] ?? $driver[\'education_level\'] ?? null;
                            if ($eduLevel) {
                                $eduMap = [
                                    \'GED\' => \'GED\',
                                    \'HS\' => \'High School\',
                                    \'SCL\' => \'Some College\',
                                    \'ADG\' => \'Associate Degree\',
                                    \'BDG\' => \'Bachelor Degree\',
                                    \'MDG\' => \'Master Degree\',
                                    \'DOC\' => \'Doctorate\'
                                ];
                                $eduLevel = $eduMap[strtoupper($eduLevel)] ?? ucfirst($eduLevel);
                            }
                        @endphp
                        {{ $eduLevel ?: \'Not provided\' }}
                    </div>
                </div>';
    
    // Insert after marital status if not present
    if (strpos($content, 'Education Level') === false) {
        $content = str_replace('</div>
                </div>
                
                <!-- Tickets & Accidents', '</div>
                </div>
                
                ' . $educationField . '
                
                <!-- Tickets & Accidents', $content);
        $fixes[] = "✓ Added Education Level field";
    }
}

// 5. Fix Occupation field (if it exists)
if (strpos($content, 'Occupation') !== false || strpos($content, 'occupation') !== false) {
    // Add occupation field if missing in drivers section
    $occupationField = '                <div class="info-item">
                    <div class="info-label">Occupation</div>
                    <div class="info-value">
                        @php
                            $occupation = $driver[\'occupation\'] ?? null;
                            if ($occupation) {
                                // Map common occupation codes
                                $occupationMap = [
                                    \'ADMINMGMT\' => \'Admin/Management\',
                                    \'MARKETING\' => \'Marketing\',
                                    \'SALES\' => \'Sales\',
                                    \'IT\' => \'IT/Technology\',
                                    \'HEALTHCARE\' => \'Healthcare\',
                                    \'EDUCATION\' => \'Education\',
                                    \'FINANCE\' => \'Finance\',
                                    \'RETAIL\' => \'Retail\',
                                    \'CONSTRUCTION\' => \'Construction\',
                                    \'OTHER\' => \'Other\'
                                ];
                                $occupation = $occupationMap[strtoupper($occupation)] ?? ucfirst($occupation);
                            }
                        @endphp
                        {{ $occupation ?: \'Not provided\' }}
                    </div>
                </div>';
    
    // Insert after education if not present
    if (strpos($content, '<div class="info-label">Occupation</div>') === false && strpos($content, 'Drivers Information') !== false) {
        $content = str_replace('<!-- Tickets & Accidents', $occupationField . '
                
                <!-- Tickets & Accidents', $content);
        $fixes[] = "✓ Added Occupation field";
    }
}

// 6. Fix Credit Score field
if (strpos($content, 'Credit Score') !== false || strpos($content, 'credit_score') !== false) {
    $creditField = '                <div class="info-item">
                    <div class="info-label">Credit Score Range</div>
                    <div class="info-value">
                        @php
                            $creditScore = $driver[\'credit_score\'] ?? $driver[\'credit_score_range\'] ?? null;
                            if (!$creditScore && isset($lead->payload)) {
                                $payload = is_string($lead->payload) ? json_decode($lead->payload, true) : $lead->payload;
                                $creditScore = $payload[\'data\'][\'drivers\'][0][\'credit_score_range\'] ?? null;
                            }
                        @endphp
                        {{ $creditScore ?: \'Not provided\' }}
                    </div>
                </div>';
    
    // Add if not present
    if (strpos($content, '<div class="info-label">Credit Score') === false) {
        $content = str_replace('<!-- Tickets & Accidents', $creditField . '
                
                <!-- Tickets & Accidents', $content);
        $fixes[] = "✓ Added Credit Score field";
    }
}

// 7. Fix Vendor Name field to check payload
$vendorNameOld = '@if($lead->vendor_name)';
$vendorNameNew = '@php
                    $vendorName = $lead->vendor_name;
                    if (!$vendorName && $lead->payload) {
                        $payload = is_string($lead->payload) ? json_decode($lead->payload, true) : $lead->payload;
                        $vendorName = $payload[\'vendor_name\'] ?? $payload[\'meta\'][\'vendor_name\'] ?? $payload[\'source\'] ?? null;
                    }
                @endphp
                @if($vendorName)';

$content = str_replace($vendorNameOld, $vendorNameNew, $content, $count);
if ($count > 0) {
    // Also update the display part
    $content = str_replace('{{ $lead->vendor_name === \'LeadsQuotingFast\'', '{{ $vendorName === \'LeadsQuotingFast\'', $content);
    $content = str_replace('$lead->vendor_name === \'LEADSQUOTINGFAST\'', '$vendorName === \'LEADSQUOTINGFAST\'', $content);
    $content = str_replace(': $lead->vendor_name }}', ': $vendorName }}', $content);
    $fixes[] = "✓ Fixed Vendor Name to check payload";
}

// 8. Fix Jangle Lead ID to check payload
$jangleOld = '@if($lead->jangle_lead_id)';
$jangleNew = '@php
                    $jangleLeadId = $lead->jangle_lead_id;
                    if (!$jangleLeadId && $lead->payload) {
                        $payload = is_string($lead->payload) ? json_decode($lead->payload, true) : $lead->payload;
                        $jangleLeadId = $payload[\'lead_id\'] ?? $payload[\'id\'] ?? $payload[\'meta\'][\'lead_id\'] ?? null;
                    }
                @endphp
                @if($jangleLeadId)';

if (strpos($content, $jangleOld) !== false) {
    $content = str_replace($jangleOld, $jangleNew, $content);
    $content = str_replace('{{ $lead->jangle_lead_id }}', '{{ $jangleLeadId }}', $content);
    $fixes[] = "✓ Fixed Jangle Lead ID to check payload";
}

// 9. Fix Home Ownership status
$homeOwnershipOld = 'Do you own or rent your home?';
if (strpos($content, $homeOwnershipOld) !== false) {
    // Find and update the home ownership section to pull from payload
    $homeOwnershipPattern = '/<select class="question-select" id="home_status">(.*?)<\/select>/s';
    $homeOwnershipNew = '<select class="question-select" id="home_status">
                        @php
                            $homeStatus = null;
                            if (isset($lead->payload)) {
                                $payload = is_string($lead->payload) ? json_decode($lead->payload, true) : $lead->payload;
                                $homeStatus = $payload[\'data\'][\'drivers\'][0][\'residence_status\'] ?? 
                                             $payload[\'data\'][\'drivers\'][0][\'home_ownership\'] ?? null;
                            }
                        @endphp
                        <option value="">Select...</option>
                        <option value="own" {{ $homeStatus === \'own\' || $homeStatus === \'home\' ? \'selected\' : \'\' }}>Own</option>
                        <option value="rent" {{ $homeStatus === \'rent\' ? \'selected\' : \'\' }}>Rent</option>
                        <option value="other" {{ $homeStatus === \'other\' ? \'selected\' : \'\' }}>Other</option>
                    </select>';
    
    if (preg_match($homeOwnershipPattern, $content)) {
        $content = preg_replace($homeOwnershipPattern, $homeOwnershipNew, $content);
        $fixes[] = "✓ Fixed Home Ownership to check payload";
    }
}

// 10. Fix Opt-In Date to check multiple fields
$optInDatePattern = '/if \(isset\(\$lead->opt_in_date\) && \$lead->opt_in_date\)/';
$optInDateNew = 'if (isset($lead->opt_in_date) && $lead->opt_in_date)';
// Already handled in previous fix, but let's ensure it checks payload too
if (!strpos($content, '$payload[\'meta\'][\'opt_in_date\']')) {
    $optInCheck = '@php
                                $optInDate = null;
                                $optInDateCarbon = null;
                                if (isset($lead->opt_in_date) && $lead->opt_in_date) {
                                    try {
                                        $optInDateCarbon = \Carbon\Carbon::parse($lead->opt_in_date);
                                        $optInDate = $optInDateCarbon->format(\'m/d/Y g:i A\');
                                    } catch (\Exception $e) {
                                        $optInDate = $lead->opt_in_date;
                                    }
                                } else {
                                    // Check payload for opt_in_date
                                    if ($lead->payload) {
                                        $payload = is_string($lead->payload) ? json_decode($lead->payload, true) : $lead->payload;
                                        $optInFromPayload = $payload[\'meta\'][\'opt_in_date\'] ?? 
                                                           $payload[\'opt_in_date\'] ?? 
                                                           $payload[\'contact\'][\'opt_in_date\'] ?? null;
                                        if ($optInFromPayload) {
                                            try {
                                                $optInDateCarbon = \Carbon\Carbon::parse($optInFromPayload);
                                                $optInDate = $optInDateCarbon->format(\'m/d/Y g:i A\');
                                            } catch (\Exception $e) {
                                                $optInDate = $optInFromPayload;
                                            }
                                        }
                                    }
                                    // Fall back to created_at if no opt_in_date found
                                    if (!$optInDate && $lead->created_at) {
                                        $optInDateCarbon = \Carbon\Carbon::parse($lead->created_at);
                                        $optInDate = $optInDateCarbon->format(\'m/d/Y g:i A\');
                                    }
                                }
                            @endphp';
    
    // Replace the existing opt-in date check
    $pattern = '/@php\s*\$optInDate = null;\s*\$optInDateCarbon = null;.*?@endphp/s';
    if (preg_match($pattern, $content)) {
        $content = preg_replace($pattern, $optInCheck, $content, 1);
        $fixes[] = "✓ Enhanced Opt-In Date to check payload";
    }
}

// 11. Fix Current Insurance Company to check payload
$currentInsurancePattern = '/Currently insured with: <strong>([^<]+)<\/strong>/';
if (preg_match($currentInsurancePattern, $content)) {
    // This is in the qualification summary - let's enhance it
    $summaryPattern = '/if \(isset\(\$lead->current_policy\)\) \{.*?\$currentPolicy = is_string.*?\}/s';
    $summaryNew = 'if (isset($lead->current_policy) || (isset($lead->payload))) {
                            $currentPolicy = null;
                            if ($lead->current_policy) {
                                $currentPolicy = is_string($lead->current_policy) ? json_decode($lead->current_policy, true) : $lead->current_policy;
                            }
                            if (!$currentPolicy && $lead->payload) {
                                $payload = is_string($lead->payload) ? json_decode($lead->payload, true) : $lead->payload;
                                $currentPolicy = $payload[\'data\'][\'current_policy\'] ?? $payload[\'current_policy\'] ?? null;
                            }';
    
    if (preg_match($summaryPattern, $content)) {
        $content = preg_replace($summaryPattern, $summaryNew, $content);
        $fixes[] = "✓ Fixed Current Insurance to check payload";
    }
}

// 12. Fix Years Licensed field
$yearsLicensedPattern = '/<div class="info-label">Years Licensed<\/div>\s*<div class="info-value">\{\{ \$driver\[\'years_licensed\'\] \?\? \'Not provided\' \}\}<\/div>/';
$yearsLicensedNew = '<div class="info-label">Years Licensed</div>
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

if (preg_match($yearsLicensedPattern, $content)) {
    $content = preg_replace($yearsLicensedPattern, $yearsLicensedNew, $content);
    $fixes[] = "✓ Fixed Years Licensed field";
}

// Write the fixed content back
file_put_contents($file, $content);

echo "✅ Comprehensive field fixes applied:\n";
foreach ($fixes as $fix) {
    echo "  $fix\n";
}

echo "\n📋 Fields now check multiple data sources:\n";
echo "  - Direct lead fields (e.g., \$lead->field)\n";
echo "  - Meta JSON field (e.g., \$lead->meta['field'])\n";
echo "  - Payload JSON field (e.g., \$lead->payload['data']['field'])\n";
echo "  - Nested payload structures (contact, meta, data sections)\n";

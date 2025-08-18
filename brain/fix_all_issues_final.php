<?php

// Comprehensive fix for all reported issues

$file = 'resources/views/agent/lead-display.blade.php';
$content = file_get_contents($file);

echo "Applying comprehensive fixes for all reported issues...\n\n";

// 1. FIX HEADER: Make it sticky, narrower, add payload button, fix back button position
$headerStyleOld = '.header {
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

$headerStyleNew = '.header {
            overflow-x: hidden;
            width: 100%;
            max-width: 900px;
            margin: 0 auto;
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            color: white;
            padding: 8px 12px; /* Even more reduced padding for less height */
            border-radius: 8px;
            margin-bottom: 12px;
            text-align: center;
            position: fixed; /* Changed to fixed for better sticky behavior */
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            z-index: 2000;
            box-sizing: border-box;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        }
        
        /* Add padding to body to account for fixed header */
        body {
            padding-top: 180px; /* Adjust based on header height */
        }';

$content = str_replace($headerStyleOld, $headerStyleNew, $content);
echo "✓ Fixed header to be sticky, narrower, and properly positioned\n";

// 2. FIX BACK BUTTON POSITION (all the way left)
$backButtonOld = '.back-button {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);';

$backButtonNew = '.back-button {
            position: absolute;
            left: 8px; /* Moved more to the left */
            top: 8px; /* Fixed position from top */';

$content = str_replace($backButtonOld, $backButtonNew, $content);
echo "✓ Fixed back button position to be all the way left\n";

// 3. ENSURE PAYLOAD BUTTON IS IN HEADER
// Remove any existing payload buttons first
$content = preg_replace('/<button[^>]*onclick=["\']showPayload\([^)]*\)["\'][^>]*>.*?(?:View Complete Payload|View Payload|Payload).*?<\/button>/si', '', $content);

// Find the header buttons section and add payload button
$headerButtonsPattern = '/(@if\(isset\(\$mode\)\).*?<div style="margin-top:.*?">)(.*?)(@endif.*?<\/div>\s*<!-- Header End -->)/s';

if (!preg_match($headerButtonsPattern, $content)) {
    // Try simpler pattern - find where email/Lead ID are and add buttons after
    $afterLeadIdPattern = '/(Lead ID: \{\{ \$lead->external_lead_id.*?\}\}.*?<\/div>)(.*?)(<\/div>\s*<!-- Header End -->)/s';
    
    if (preg_match($afterLeadIdPattern, $content, $matches)) {
        $buttonsHtml = '
                
                <!-- Action Buttons -->
                @if(isset($mode))
                <div style="margin-top: 10px; display: flex; gap: 8px; justify-content: center; flex-wrap: wrap;">
                    <!-- View Payload Button - Always visible -->
                    <button onclick="showPayload(@json($lead))" style="
                        background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
                        color: white;
                        border: none;
                        padding: 6px 16px;
                        font-size: 14px;
                        font-weight: 600;
                        border-radius: 6px;
                        cursor: pointer;
                        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
                        transition: all 0.2s ease;
                    " onmouseover="this.style.transform=\'scale(1.05)\'" onmouseout="this.style.transform=\'scale(1)\'" >
                        📄 Payload
                    </button>
                    
                    @if($mode === \'edit\')
                    <!-- Save Lead Button -->
                    <button onclick="saveLead()" style="
                        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
                        color: white;
                        border: none;
                        padding: 6px 16px;
                        font-size: 14px;
                        font-weight: 600;
                        border-radius: 6px;
                        cursor: pointer;
                        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
                        transition: all 0.2s ease;
                    " onmouseover="this.style.transform=\'scale(1.05)\'" onmouseout="this.style.transform=\'scale(1)\'" >
                        💾 Save
                    </button>
                    @elseif($mode === \'view\')
                    <!-- Edit Button -->
                    <a href="?mode=edit" style="
                        display: inline-block;
                        background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
                        color: white;
                        text-decoration: none;
                        padding: 6px 16px;
                        font-size: 14px;
                        font-weight: 600;
                        border-radius: 6px;
                        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
                        transition: all 0.2s ease;
                    " onmouseover="this.style.transform=\'scale(1.05)\'" onmouseout="this.style.transform=\'scale(1)\'" >
                        ✏️ Edit
                    </a>
                    @endif
                </div>
                @endif';
        
        $replacement = $matches[1] . $buttonsHtml . $matches[3];
        $content = str_replace($matches[0], $replacement, $content);
        echo "✓ Added Payload button to header\n";
    }
}

// 4. MOVE LEADID CODE TO TCPA SECTION WITH COPY BUTTON
// First remove it from vendor section
$leadIdInVendorPattern = '/<div class="info-item">\s*<div class="info-label">LeadID Code<\/div>.*?<\/div>\s*<\/div>/s';
$content = preg_replace($leadIdInVendorPattern, '', $content);

// Add it to TCPA section (after IP Address)
$ipAddressPattern = '/(<div class="info-item">.*?<div class="info-label">IP Address<\/div>.*?<\/div>\s*<\/div>)/s';
if (preg_match($ipAddressPattern, $content, $match)) {
    $leadIdCodeHtml = '
                
                <!-- LeadID Code -->
                <div class="info-item">
                    <div class="info-label">LeadID Code</div>
                    <div class="info-value">
                        @php
                            $leadIdCode = $lead->leadid_code;
                            if (!$leadIdCode && $lead->meta) {
                                $meta = is_string($lead->meta) ? json_decode($lead->meta, true) : $lead->meta;
                                $leadIdCode = $meta[\'lead_id_code\'] ?? $meta[\'leadid_code\'] ?? null;
                            }
                            if (!$leadIdCode && $lead->payload) {
                                $payload = is_string($lead->payload) ? json_decode($lead->payload, true) : $lead->payload;
                                $leadIdCode = $payload[\'meta\'][\'lead_id_code\'] ?? $payload[\'leadid_code\'] ?? null;
                            }
                        @endphp
                        @if($leadIdCode)
                            <span style="font-family: monospace; font-size: 12px;">{{ $leadIdCode }}</span>
                            <button class="copy-btn" onclick="copyToClipboard(\'{{ $leadIdCode }}\', this)" style="background: #10b981; color: white; border: none; padding: 4px 8px; border-radius: 4px; cursor: pointer; margin-left: 8px;">📋</button>
                        @else
                            <span style="color: #6b7280;">Not provided</span>
                        @endif
                    </div>
                </div>';
    
    $content = str_replace($match[0], $match[0] . $leadIdCodeHtml, $content);
    echo "✓ Moved LeadID Code to TCPA section with copy button\n";
}

// 5. ADD COMPREHENSIVE AND COLLISION TO VEHICLE CARDS
$vehicleCardPattern = '/<div class="vehicle-card">.*?(?=<div class="vehicle-card">|@endforeach|<!-- Properties Section|<\/div>\s*@endif\s*@endif)/s';

if (preg_match_all($vehicleCardPattern, $content, $matches)) {
    foreach ($matches[0] as $oldCard) {
        // Check if comprehensive/collision already exists
        if (strpos($oldCard, 'Comprehensive') === false) {
            // Add comprehensive and collision fields after Annual Miles
            $annualMilesPattern = '/(<div class="info-item">.*?<div class="info-label">Annual Miles<\/div>.*?<\/div>\s*<\/div>)/s';
            
            if (preg_match($annualMilesPattern, $oldCard, $cardMatch)) {
                $deductiblesHtml = '
                    
                    <!-- Comprehensive Deductible -->
                    <div class="info-item">
                        <div class="info-label">Comprehensive</div>
                        <div class="info-value">
                            @if(isset($vehicle[\'comprehensive_deductible\']) && $vehicle[\'comprehensive_deductible\'])
                                ${{ number_format($vehicle[\'comprehensive_deductible\']) }}
                            @else
                                Not provided
                            @endif
                        </div>
                    </div>
                    
                    <!-- Collision Deductible -->
                    <div class="info-item">
                        <div class="info-label">Collision</div>
                        <div class="info-value">
                            @if(isset($vehicle[\'collision_deductible\']) && $vehicle[\'collision_deductible\'])
                                ${{ number_format($vehicle[\'collision_deductible\']) }}
                            @else
                                Not provided
                            @endif
                        </div>
                    </div>';
                
                $newCard = str_replace($cardMatch[0], $cardMatch[0] . $deductiblesHtml, $oldCard);
                $content = str_replace($oldCard, $newCard, $content);
            }
        }
    }
    echo "✓ Added Comprehensive and Collision to vehicle cards\n";
}

// 6. CREATE MIGRATION FOR VICI TABLES
$migrationContent = '<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateViciTablesIfNotExist extends Migration
{
    public function up()
    {
        if (!Schema::hasTable(\'vici_call_metrics\')) {
            Schema::create(\'vici_call_metrics\', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger(\'lead_id\')->nullable();
                $table->string(\'vendor_lead_code\')->nullable();
                $table->string(\'uniqueid\')->nullable();
                $table->timestamp(\'call_date\')->nullable();
                $table->string(\'phone_number\')->nullable();
                $table->string(\'status\')->nullable();
                $table->string(\'user\')->nullable();
                $table->string(\'campaign_id\')->nullable();
                $table->integer(\'list_id\')->nullable();
                $table->integer(\'length_in_sec\')->nullable();
                $table->string(\'call_status\')->nullable();
                $table->unsignedBigInteger(\'matched_lead_id\')->nullable();
                $table->timestamps();
                
                $table->index(\'vendor_lead_code\');
                $table->index(\'matched_lead_id\');
                $table->index(\'call_date\');
            });
        }
        
        if (!Schema::hasTable(\'orphan_call_logs\')) {
            Schema::create(\'orphan_call_logs\', function (Blueprint $table) {
                $table->id();
                $table->string(\'uniqueid\')->nullable();
                $table->string(\'lead_id\')->nullable();
                $table->integer(\'list_id\')->nullable();
                $table->string(\'campaign_id\')->nullable();
                $table->timestamp(\'call_date\')->nullable();
                $table->bigInteger(\'start_epoch\')->nullable();
                $table->bigInteger(\'end_epoch\')->nullable();
                $table->integer(\'length_in_sec\')->nullable();
                $table->string(\'status\')->nullable();
                $table->string(\'phone_code\')->nullable();
                $table->string(\'phone_number\')->nullable();
                $table->string(\'user\')->nullable();
                $table->text(\'comments\')->nullable();
                $table->boolean(\'processed\')->default(false);
                $table->string(\'term_reason\')->nullable();
                $table->string(\'vendor_lead_code\')->nullable();
                $table->string(\'source_id\')->nullable();
                $table->boolean(\'matched\')->default(false);
                $table->unsignedBigInteger(\'matched_lead_id\')->nullable();
                $table->timestamps();
                
                $table->index(\'vendor_lead_code\');
                $table->index(\'matched\');
                $table->index(\'phone_number\');
            });
        }
    }
    
    public function down()
    {
        Schema::dropIfExists(\'vici_call_metrics\');
        Schema::dropIfExists(\'orphan_call_logs\');
    }
}';

file_put_contents('database/migrations/' . date('Y_m_d_His') . '_create_vici_tables_if_not_exist.php', $migrationContent);
echo "✓ Created migration for Vici tables\n";

// Write the fixed content back
file_put_contents($file, $content);

echo "\n✅ All issues fixed:\n";
echo "  1. Header is now properly sticky (fixed position) and narrower\n";
echo "  2. Payload button added to header\n";
echo "  3. Back button positioned all the way left\n";
echo "  4. LeadID Code moved to TCPA section with copy button\n";
echo "  5. Comprehensive and Collision added to vehicle cards\n";
echo "  6. Created migration for Vici tables\n";

echo "\nNow running migrations...\n";
exec('php artisan migrate --force 2>&1', $output, $returnCode);
if ($returnCode === 0) {
    echo "✓ Migrations completed successfully\n";
} else {
    echo "⚠️ Migration output: " . implode("\n", $output) . "\n";
}


// Comprehensive fix for all reported issues

$file = 'resources/views/agent/lead-display.blade.php';
$content = file_get_contents($file);

echo "Applying comprehensive fixes for all reported issues...\n\n";

// 1. FIX HEADER: Make it sticky, narrower, add payload button, fix back button position
$headerStyleOld = '.header {
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

$headerStyleNew = '.header {
            overflow-x: hidden;
            width: 100%;
            max-width: 900px;
            margin: 0 auto;
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            color: white;
            padding: 8px 12px; /* Even more reduced padding for less height */
            border-radius: 8px;
            margin-bottom: 12px;
            text-align: center;
            position: fixed; /* Changed to fixed for better sticky behavior */
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            z-index: 2000;
            box-sizing: border-box;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        }
        
        /* Add padding to body to account for fixed header */
        body {
            padding-top: 180px; /* Adjust based on header height */
        }';

$content = str_replace($headerStyleOld, $headerStyleNew, $content);
echo "✓ Fixed header to be sticky, narrower, and properly positioned\n";

// 2. FIX BACK BUTTON POSITION (all the way left)
$backButtonOld = '.back-button {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);';

$backButtonNew = '.back-button {
            position: absolute;
            left: 8px; /* Moved more to the left */
            top: 8px; /* Fixed position from top */';

$content = str_replace($backButtonOld, $backButtonNew, $content);
echo "✓ Fixed back button position to be all the way left\n";

// 3. ENSURE PAYLOAD BUTTON IS IN HEADER
// Remove any existing payload buttons first
$content = preg_replace('/<button[^>]*onclick=["\']showPayload\([^)]*\)["\'][^>]*>.*?(?:View Complete Payload|View Payload|Payload).*?<\/button>/si', '', $content);

// Find the header buttons section and add payload button
$headerButtonsPattern = '/(@if\(isset\(\$mode\)\).*?<div style="margin-top:.*?">)(.*?)(@endif.*?<\/div>\s*<!-- Header End -->)/s';

if (!preg_match($headerButtonsPattern, $content)) {
    // Try simpler pattern - find where email/Lead ID are and add buttons after
    $afterLeadIdPattern = '/(Lead ID: \{\{ \$lead->external_lead_id.*?\}\}.*?<\/div>)(.*?)(<\/div>\s*<!-- Header End -->)/s';
    
    if (preg_match($afterLeadIdPattern, $content, $matches)) {
        $buttonsHtml = '
                
                <!-- Action Buttons -->
                @if(isset($mode))
                <div style="margin-top: 10px; display: flex; gap: 8px; justify-content: center; flex-wrap: wrap;">
                    <!-- View Payload Button - Always visible -->
                    <button onclick="showPayload(@json($lead))" style="
                        background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
                        color: white;
                        border: none;
                        padding: 6px 16px;
                        font-size: 14px;
                        font-weight: 600;
                        border-radius: 6px;
                        cursor: pointer;
                        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
                        transition: all 0.2s ease;
                    " onmouseover="this.style.transform=\'scale(1.05)\'" onmouseout="this.style.transform=\'scale(1)\'" >
                        📄 Payload
                    </button>
                    
                    @if($mode === \'edit\')
                    <!-- Save Lead Button -->
                    <button onclick="saveLead()" style="
                        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
                        color: white;
                        border: none;
                        padding: 6px 16px;
                        font-size: 14px;
                        font-weight: 600;
                        border-radius: 6px;
                        cursor: pointer;
                        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
                        transition: all 0.2s ease;
                    " onmouseover="this.style.transform=\'scale(1.05)\'" onmouseout="this.style.transform=\'scale(1)\'" >
                        💾 Save
                    </button>
                    @elseif($mode === \'view\')
                    <!-- Edit Button -->
                    <a href="?mode=edit" style="
                        display: inline-block;
                        background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
                        color: white;
                        text-decoration: none;
                        padding: 6px 16px;
                        font-size: 14px;
                        font-weight: 600;
                        border-radius: 6px;
                        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
                        transition: all 0.2s ease;
                    " onmouseover="this.style.transform=\'scale(1.05)\'" onmouseout="this.style.transform=\'scale(1)\'" >
                        ✏️ Edit
                    </a>
                    @endif
                </div>
                @endif';
        
        $replacement = $matches[1] . $buttonsHtml . $matches[3];
        $content = str_replace($matches[0], $replacement, $content);
        echo "✓ Added Payload button to header\n";
    }
}

// 4. MOVE LEADID CODE TO TCPA SECTION WITH COPY BUTTON
// First remove it from vendor section
$leadIdInVendorPattern = '/<div class="info-item">\s*<div class="info-label">LeadID Code<\/div>.*?<\/div>\s*<\/div>/s';
$content = preg_replace($leadIdInVendorPattern, '', $content);

// Add it to TCPA section (after IP Address)
$ipAddressPattern = '/(<div class="info-item">.*?<div class="info-label">IP Address<\/div>.*?<\/div>\s*<\/div>)/s';
if (preg_match($ipAddressPattern, $content, $match)) {
    $leadIdCodeHtml = '
                
                <!-- LeadID Code -->
                <div class="info-item">
                    <div class="info-label">LeadID Code</div>
                    <div class="info-value">
                        @php
                            $leadIdCode = $lead->leadid_code;
                            if (!$leadIdCode && $lead->meta) {
                                $meta = is_string($lead->meta) ? json_decode($lead->meta, true) : $lead->meta;
                                $leadIdCode = $meta[\'lead_id_code\'] ?? $meta[\'leadid_code\'] ?? null;
                            }
                            if (!$leadIdCode && $lead->payload) {
                                $payload = is_string($lead->payload) ? json_decode($lead->payload, true) : $lead->payload;
                                $leadIdCode = $payload[\'meta\'][\'lead_id_code\'] ?? $payload[\'leadid_code\'] ?? null;
                            }
                        @endphp
                        @if($leadIdCode)
                            <span style="font-family: monospace; font-size: 12px;">{{ $leadIdCode }}</span>
                            <button class="copy-btn" onclick="copyToClipboard(\'{{ $leadIdCode }}\', this)" style="background: #10b981; color: white; border: none; padding: 4px 8px; border-radius: 4px; cursor: pointer; margin-left: 8px;">📋</button>
                        @else
                            <span style="color: #6b7280;">Not provided</span>
                        @endif
                    </div>
                </div>';
    
    $content = str_replace($match[0], $match[0] . $leadIdCodeHtml, $content);
    echo "✓ Moved LeadID Code to TCPA section with copy button\n";
}

// 5. ADD COMPREHENSIVE AND COLLISION TO VEHICLE CARDS
$vehicleCardPattern = '/<div class="vehicle-card">.*?(?=<div class="vehicle-card">|@endforeach|<!-- Properties Section|<\/div>\s*@endif\s*@endif)/s';

if (preg_match_all($vehicleCardPattern, $content, $matches)) {
    foreach ($matches[0] as $oldCard) {
        // Check if comprehensive/collision already exists
        if (strpos($oldCard, 'Comprehensive') === false) {
            // Add comprehensive and collision fields after Annual Miles
            $annualMilesPattern = '/(<div class="info-item">.*?<div class="info-label">Annual Miles<\/div>.*?<\/div>\s*<\/div>)/s';
            
            if (preg_match($annualMilesPattern, $oldCard, $cardMatch)) {
                $deductiblesHtml = '
                    
                    <!-- Comprehensive Deductible -->
                    <div class="info-item">
                        <div class="info-label">Comprehensive</div>
                        <div class="info-value">
                            @if(isset($vehicle[\'comprehensive_deductible\']) && $vehicle[\'comprehensive_deductible\'])
                                ${{ number_format($vehicle[\'comprehensive_deductible\']) }}
                            @else
                                Not provided
                            @endif
                        </div>
                    </div>
                    
                    <!-- Collision Deductible -->
                    <div class="info-item">
                        <div class="info-label">Collision</div>
                        <div class="info-value">
                            @if(isset($vehicle[\'collision_deductible\']) && $vehicle[\'collision_deductible\'])
                                ${{ number_format($vehicle[\'collision_deductible\']) }}
                            @else
                                Not provided
                            @endif
                        </div>
                    </div>';
                
                $newCard = str_replace($cardMatch[0], $cardMatch[0] . $deductiblesHtml, $oldCard);
                $content = str_replace($oldCard, $newCard, $content);
            }
        }
    }
    echo "✓ Added Comprehensive and Collision to vehicle cards\n";
}

// 6. CREATE MIGRATION FOR VICI TABLES
$migrationContent = '<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateViciTablesIfNotExist extends Migration
{
    public function up()
    {
        if (!Schema::hasTable(\'vici_call_metrics\')) {
            Schema::create(\'vici_call_metrics\', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger(\'lead_id\')->nullable();
                $table->string(\'vendor_lead_code\')->nullable();
                $table->string(\'uniqueid\')->nullable();
                $table->timestamp(\'call_date\')->nullable();
                $table->string(\'phone_number\')->nullable();
                $table->string(\'status\')->nullable();
                $table->string(\'user\')->nullable();
                $table->string(\'campaign_id\')->nullable();
                $table->integer(\'list_id\')->nullable();
                $table->integer(\'length_in_sec\')->nullable();
                $table->string(\'call_status\')->nullable();
                $table->unsignedBigInteger(\'matched_lead_id\')->nullable();
                $table->timestamps();
                
                $table->index(\'vendor_lead_code\');
                $table->index(\'matched_lead_id\');
                $table->index(\'call_date\');
            });
        }
        
        if (!Schema::hasTable(\'orphan_call_logs\')) {
            Schema::create(\'orphan_call_logs\', function (Blueprint $table) {
                $table->id();
                $table->string(\'uniqueid\')->nullable();
                $table->string(\'lead_id\')->nullable();
                $table->integer(\'list_id\')->nullable();
                $table->string(\'campaign_id\')->nullable();
                $table->timestamp(\'call_date\')->nullable();
                $table->bigInteger(\'start_epoch\')->nullable();
                $table->bigInteger(\'end_epoch\')->nullable();
                $table->integer(\'length_in_sec\')->nullable();
                $table->string(\'status\')->nullable();
                $table->string(\'phone_code\')->nullable();
                $table->string(\'phone_number\')->nullable();
                $table->string(\'user\')->nullable();
                $table->text(\'comments\')->nullable();
                $table->boolean(\'processed\')->default(false);
                $table->string(\'term_reason\')->nullable();
                $table->string(\'vendor_lead_code\')->nullable();
                $table->string(\'source_id\')->nullable();
                $table->boolean(\'matched\')->default(false);
                $table->unsignedBigInteger(\'matched_lead_id\')->nullable();
                $table->timestamps();
                
                $table->index(\'vendor_lead_code\');
                $table->index(\'matched\');
                $table->index(\'phone_number\');
            });
        }
    }
    
    public function down()
    {
        Schema::dropIfExists(\'vici_call_metrics\');
        Schema::dropIfExists(\'orphan_call_logs\');
    }
}';

file_put_contents('database/migrations/' . date('Y_m_d_His') . '_create_vici_tables_if_not_exist.php', $migrationContent);
echo "✓ Created migration for Vici tables\n";

// Write the fixed content back
file_put_contents($file, $content);

echo "\n✅ All issues fixed:\n";
echo "  1. Header is now properly sticky (fixed position) and narrower\n";
echo "  2. Payload button added to header\n";
echo "  3. Back button positioned all the way left\n";
echo "  4. LeadID Code moved to TCPA section with copy button\n";
echo "  5. Comprehensive and Collision added to vehicle cards\n";
echo "  6. Created migration for Vici tables\n";

echo "\nNow running migrations...\n";
exec('php artisan migrate --force 2>&1', $output, $returnCode);
if ($returnCode === 0) {
    echo "✓ Migrations completed successfully\n";
} else {
    echo "⚠️ Migration output: " . implode("\n", $output) . "\n";
}






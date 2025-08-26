@extends('layouts.app')

@section('content')
<?php
// Initialize all variables to prevent undefined errors
$lead = $lead ?? new stdClass();
$lead->id = $lead->id ?? request()->route('leadId');
$lead->name = $lead->name ?? 'Unknown';
$lead->phone = $lead->phone ?? '';
$lead->email = $lead->email ?? '';
$lead->address = $lead->address ?? '';
$lead->city = $lead->city ?? '';
$lead->state = $lead->state ?? '';
$lead->zip = $lead->zip ?? ($lead->zip_code ?? '');
$lead->external_lead_id = $lead->external_lead_id ?? $lead->id;
$lead->type = $lead->type ?? 'unknown';
$lead->tcpa_compliant = $lead->tcpa_compliant ?? false;
$lead->opt_in_date = $lead->opt_in_date ?? null;
$lead->trusted_form_cert_url = $lead->trusted_form_cert_url ?? null;

// Parse JSON fields if they're strings
$vehicles = isset($lead->vehicles) ? (is_string($lead->vehicles) ? json_decode($lead->vehicles, true) : $lead->vehicles) : [];
$drivers = isset($lead->drivers) ? (is_string($lead->drivers) ? json_decode($lead->drivers, true) : $lead->drivers) : [];
$current_policy = isset($lead->current_policy) ? (is_string($lead->current_policy) ? json_decode($lead->current_policy, true) : $lead->current_policy) : [];
$meta = isset($lead->meta) ? (is_string($lead->meta) ? (json_decode($lead->meta, true) ?: []) : (is_array($lead->meta) ? $lead->meta : [])) : [];

// Determine lead type
// Phone formatter helper
if (!function_exists('formatPhoneForDisplay')) {
    function formatPhoneForDisplay($phone) {
        $digits = preg_replace('/\D+/', '', (string)$phone);
        if (strlen($digits) === 10) {
            return sprintf('(%.3s) %.3s-%.4s', $digits, substr($digits, 3, 3), substr($digits, 6));
        }
        return $phone ?: 'N/A';
    }
}

$displayType = 'AUTO';
if (!empty($lead->type) && strtolower($lead->type) !== 'unknown') {
    $displayType = strtoupper($lead->type);
} elseif (!empty($vehicles)) {
    $displayType = 'AUTO';
}

// Check mode
$isEditMode = request()->get('mode') === 'edit';
?>

<div class="min-h-screen bg-gray-50">
    <!-- Sticky Header -->
    <div style="position: fixed !important; top: 0; left: 0; right: 0; z-index: 9999 !important; background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); color: #fff; box-shadow: 0 4px 16px rgba(0,0,0,0.25);">
        <div class="container mx-auto px-4 py-4">
            <div class="flex items-start justify-between">
                <!-- Left section -->
                <div class="flex flex-col items-start space-y-2">
                    <?php if (empty($isIframe)): ?>
                    <a href="/leads" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50" style="display:inline-flex; align-items:center; color:#4b5563; background:#fff; border:1px solid #d1d5db; border-radius:6px; padding:6px 8px;">
                        <svg width="16" height="16" style="margin-right:8px; flex-shrink:0; color:#4b5563; display:inline-block;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Back to Leads
                    </a>
                    <?php endif; ?>
                    <!-- Avatar -->
                    <div class="h-16 w-16 rounded-full bg-indigo-600 flex items-center justify-center text-white font-bold text-lg" style="margin-top: 8px;">
                        <?php echo substr($displayType, 0, 4); ?>
                    </div>
                </div>

                <!-- Center section -->
                <div class="flex-1 text-center px-4">
                    <div class="text-sm mb-1" style="color:#e5edff;">
                        <?php echo $isEditMode ? 'Edit Mode' : 'View Only'; ?>
                    </div>
                    <div class="font-bold text-lg" style="color:#fff;">
                        <?php echo htmlspecialchars($lead->name); ?>
                    </div>
                    <div class="text-base font-semibold" style="line-height: 1.4; color:#f0f6ff;">
                        <?php echo htmlspecialchars(formatPhoneForDisplay($lead->phone)); ?><br>
                        <?php echo htmlspecialchars($lead->email); ?>
                    </div>
                    <?php 
                    $streetLine = trim((string)($lead->address ?? ''));
                    $cityStateZip = trim(implode(', ', array_filter([
                        $lead->city ?? '',
                        $lead->state ?? ''
                    ])) . ' ' . ($lead->zip ?? ''));
                    ?>
                    <?php if (!empty($streetLine)): ?>
                    <div class="text-sm mt-1" style="color:#dbeafe;">
                        <?php echo htmlspecialchars($streetLine); ?>
                    </div>
                    <?php endif; ?>
                    <?php if (trim($cityStateZip) !== ''): ?>
                    <div class="text-sm" style="color:#dbeafe;">
                        <?php echo htmlspecialchars(trim($cityStateZip)); ?>
                    </div>
                    <?php endif; ?>
                    <div class="text-sm" style="color:#dbeafe;">Lead ID: <?php echo htmlspecialchars($lead->external_lead_id); ?></div>
                </div>

                <!-- Right section -->
                <div class="flex flex-col items-end space-y-2">
                    <?php if (empty($isIframe)): ?>
                        <a href="/lead/<?php echo $lead->id; ?>/payload-view" 
                           target="_blank"
                           class="inline-flex items-center px-4 py-2 border border-white/30 shadow-sm text-sm font-medium rounded-md text-indigo-900 bg-white hover:bg-indigo-50">
                            View Payload
                        </a>
                        <?php if (!$isEditMode): ?>
                        <a href="?mode=edit" 
                           class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-700 hover:bg-indigo-800">
                            Edit Lead
                        </a>
                        <?php else: ?>
                        <a href="?mode=view" 
                           class="inline-flex items-center px-4 py-2 border border-white/30 shadow-sm text-sm font-medium rounded-md text-indigo-900 bg-white hover:bg-indigo-50">
                            View Mode
                        </a>
                        <div class="mt-6 self-end">
                            <button type="button" onclick="saveQualification()" class="inline-flex items-center px-4 py-2 border border-white/30 shadow-sm text-sm font-medium rounded-md text-indigo-900 bg-white hover:bg-indigo-50">Save</button>
                        </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container mx-auto px-4" style="padding-top: 120px;">
        <?php if ($isEditMode): ?>
            <!-- Edit Mode - Show questions first under top header -->
            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-2xl font-bold mb-6">Agent Qualification Questions</h2>
                <form method="POST" action="/agent/lead/<?php echo $lead->id; ?>/qualify" id="qualificationFormTop">
                    <input type="hidden" name="_token" value="<?php echo csrf_token(); ?>">
                    <div class="space-y-6">
                        <!-- 1. Are you currently insured? -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">1. Are you currently insured?</label>
                            <select id="currently_insured" name="currently_insured" onchange="toggleInsuranceQuestions()" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                <option value="">Select...</option>
                                <option value="yes">Yes</option>
                                <option value="no">No</option>
                            </select>
                            
                            <!-- Conditional insurance questions -->
                            <div id="insurance_questions" style="display: none; margin-top: 12px; padding-left: 20px; border-left: 3px solid #3b82f6;">
                                <div style="margin-bottom: 12px;">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">1B. Who is your current provider?</label>
                                    <select name="current_provider" id="current_provider" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                        <option value="">Select...</option>
                                        <option value="state_farm">State Farm</option>
                                        <option value="geico">GEICO</option>
                                        <option value="progressive">Progressive</option>
                                        <option value="allstate">Allstate</option>
                                        <option value="farmers">Farmers</option>
                                        <option value="usaa">USAA</option>
                                        <option value="liberty_mutual">Liberty Mutual</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">1C. How long have you been continuously insured?</label>
                                    <select name="insurance_duration" id="insurance_duration" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                        <option value="">Select...</option>
                                        <option value="under_6_months">Under 6 months</option>
                                        <option value="6_months_1_year">6 months - 1 year</option>
                                        <option value="1_3_years">1-3 years</option>
                                        <option value="over_3_years">Over 3 years</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- 2. How many cars are you going to need a quote for? -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">2. How many cars are you going to need a quote for?</label>
                            <select name="num_vehicles" id="num_vehicles" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                <option value="">Select...</option>
                                <option value="1">1 Vehicle</option>
                                <option value="2">2 Vehicles</option>
                                <option value="3">3 Vehicles</option>
                                <option value="4">4+ Vehicles</option>
                            </select>
                        </div>

                        <!-- 3. Do you own or rent your home? -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">3. Do you own or rent your home?</label>
                            <select name="home_status" id="home_status" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                <option value="">Select...</option>
                                <option value="own">Own</option>
                                <option value="rent">Rent</option>
                                <option value="other">Other</option>
                            </select>
                        </div>

                        <!-- 4. DUI or SR22? -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">4. DUI or SR22?</label>
                            <select name="dui_sr22" id="dui_sr22" onchange="toggleDUIQuestions()" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                <option value="">Select...</option>
                                <option value="no">No</option>
                                <option value="dui_only">DUI Only</option>
                                <option value="sr22_only">SR22 Only</option>
                                <option value="both">Both</option>
                            </select>
                            
                            <!-- Conditional DUI timeframe question -->
                            <div id="dui_questions" style="display: none; margin-top: 12px; padding-left: 20px; border-left: 3px solid #3b82f6;">
                                <label class="block text-sm font-medium text-gray-700 mb-2">4B. If DUI – How long ago?</label>
                                <select name="dui_timeframe" id="dui_timeframe" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                    <option value="">Select...</option>
                                    <option value="1">Under 1 year</option>
                                    <option value="2">1–3 years</option>
                                    <option value="3">Over 3 years</option>
                                </select>
                            </div>
                        </div>

                        <!-- 5. State -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">5. State</label>
                            <select name="state" id="state" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                <option value="">Select State...</option>
                                <?php
                                $states = ['AL','AK','AZ','AR','CA','CO','CT','DE','FL','GA','HI','ID','IL','IN','IA','KS','KY','LA','ME','MD','MA','MI','MN','MS','MO','MT','NE','NV','NH','NJ','NM','NY','NC','ND','OH','OK','OR','PA','RI','SC','SD','TN','TX','UT','VT','VA','WA','WV','WI','WY','DC'];
                                foreach ($states as $state) {
                                    $selected = ($lead->state ?? '') == $state ? 'selected' : '';
                                    echo "<option value=\"$state\" $selected>$state</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <!-- 6. ZIP Code -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">6. ZIP Code</label>
                            <input type="text" name="zip_code" id="zip_code" value="<?php echo htmlspecialchars($lead->zip_code ?? ''); ?>" 
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                                   placeholder="Enter ZIP code">
                        </div>

                        <!-- Agent Script Section -->
                        <div style="background: #f3f4f6; padding: 15px; margin: 20px 0; border-radius: 8px; border: 1px solid #d1d5db; font-style: italic; color: #4b5563;">
                            <strong>📝 Agent Script:</strong><br>
                            "Let me go ahead and see who has the better rates in your area based on what we have. Oh ok, it looks like Allstate has the better rates in that area."
                        </div>

                        <!-- 7. Have you received a quote from Allstate in the last 2 months? -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">7. Have you received a quote from Allstate in the last 2 months?</label>
                            <select name="allstate_quote" id="allstate_quote" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                <option value="">Select...</option>
                                <option value="yes">Yes</option>
                                <option value="no">No</option>
                            </select>
                        </div>

                        <!-- 8. Ready to speak with an agent now? -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">8. Ready to speak with an agent now?</label>
                            <select name="ready_to_speak" id="ready_to_speak" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                <option value="">Select...</option>
                                <option value="yes">Yes</option>
                                <option value="no">No</option>
                                <option value="maybe">Maybe</option>
                            </select>
                        </div>

                        <!-- Lead Details (editable in edit mode) -->
                        <div class="bg-gray-50 border border-gray-200 rounded-md p-4">
                            <h4 class="text-md font-semibold mb-3">Lead Details</h4>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                                    <input type="text" name="name" value="<?php echo htmlspecialchars($lead->name ?? ''); ?>" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" placeholder="John Doe">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                    <input type="email" name="email" value="<?php echo htmlspecialchars($lead->email ?? ''); ?>" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" placeholder="name@example.com">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                                    <input type="text" name="phone" value="<?php echo htmlspecialchars($lead->phone ?? ''); ?>" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" placeholder="(555) 555-5555">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                                    <input type="text" name="address" value="<?php echo htmlspecialchars($lead->address ?? ''); ?>" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" placeholder="123 Main St">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">City</label>
                                    <input type="text" name="city" value="<?php echo htmlspecialchars($lead->city ?? ''); ?>" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" placeholder="Tampa">
                                </div>
                                <!-- State and ZIP already captured by Questions 5 and 6 (state, zip_code) -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Lead Type</label>
                                    <select name="type" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                        <?php $t = strtolower($lead->type ?? ''); ?>
                                        <option value="auto" <?php echo $t === 'auto' ? 'selected' : ''; ?>>Auto</option>
                                        <option value="home" <?php echo $t === 'home' ? 'selected' : ''; ?>>Home</option>
                                        <option value="unknown" <?php echo ($t === 'unknown' || !$t) ? 'selected' : ''; ?>>Unknown</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Submit buttons -->
                        <div class="flex justify-end pt-6">
                            <button type="button" onclick="saveQualification()" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700">Save</button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Enrichment buttons (centered, larger, brighter) -->
            <div class="bg-white shadow rounded-lg p-6 mt-6">
                <div class="flex flex-wrap gap-4 justify-center">
                    <button type="button" onclick="enrichLead('insured')" class="w-48 h-12 text-lg font-semibold rounded-md bg-blue-700 hover:bg-blue-800 text-white flex items-center justify-center gap-2 shadow">🛡️ <span>Insured</span></button>
                    <button type="button" onclick="enrichLead('uninsured')" class="w-48 h-12 text-lg font-semibold rounded-md bg-amber-600 hover:bg-amber-700 text-white flex items-center justify-center gap-2 shadow">⚠️ <span>Uninsured</span></button>
                    <button type="button" onclick="enrichLead('homeowner')" class="w-48 h-12 text-lg font-semibold rounded-md bg-green-700 hover:bg-green-800 text-white flex items-center justify-center gap-2 shadow">🏠 <span>Homeowner</span></button>
                </div>
            </div>

            <!-- Edit Mode - Panels under questions per requested order -->
            <div class="space-y-6 mb-6">
                <!-- Contact Information (hide in edit unless needed) -->
                <div class="bg-white shadow rounded-lg p-6 <?php echo $isEditMode ? 'hidden' : ''; ?>">
                    <h3 class="text-lg font-semibold mb-4">Contact Information</h3>
                    <dl class="grid grid-cols-1 gap-x-4 gap-y-4 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Name</dt>
                            <dd class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($lead->name); ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Phone</dt>
                            <dd class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($lead->phone ?: 'N/A'); ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Email</dt>
                            <dd class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($lead->email ?: 'N/A'); ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Address</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                <?php 
                                $addressParts = array_filter([
                                    $lead->address,
                                    $lead->city,
                                    $lead->state,
                                    $lead->zip ?? $lead->zip_code ?? null,
                                ]);
                                echo htmlspecialchars(implode(', ', $addressParts) ?: 'N/A');
                                ?>
                            </dd>
                        </div>
                    </dl>
                </div>

                <!-- Lead Information (edit mode: partially hidden via CSS) -->
                <div class="bg-white shadow rounded-lg p-6 <?php echo $isEditMode ? 'edit-condensed' : ''; ?>">
                    <h3 class="text-lg font-semibold mb-4">Lead Information</h3>
                    <dl class="grid grid-cols-1 gap-x-4 gap-y-4 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Lead Type</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                <span class="px-2 py-1 text-xs font-medium rounded-full 
                                    <?php echo strtolower($displayType) === 'auto' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800'; ?>">
                                    <?php echo $displayType; ?>
                                </span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">External Lead ID</dt>
                            <dd class="mt-1 text-sm text-gray-900 font-mono text-xs">
                                <?php echo htmlspecialchars($lead->external_lead_id); ?>
                                <?php if (!empty($lead->external_lead_id)): ?>
                                <button type="button" class="ml-2 text-[10px] px-2 py-0.5 rounded bg-green-600 text-white" onclick="navigator.clipboard.writeText('<?php echo htmlspecialchars($lead->external_lead_id, ENT_QUOTES); ?>'); this.textContent='✓'; setTimeout(()=>this.textContent='📋',1500)">📋</button>
                                <?php endif; ?>
                            </dd>
                        </div>
                        <?php if (!empty($lead->jangle_lead_id)): ?>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Jangle ID</dt>
                            <dd class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($lead->jangle_lead_id); ?></dd>
                        </div>
                        <?php endif; ?>
                        <?php if (!$isEditMode): ?>
                            <?php if (!empty($lead->source)): ?>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Source</dt>
                                <dd class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($lead->source); ?></dd>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($lead->campaign_id)): ?>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Campaign</dt>
                                <dd class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($lead->campaign_id); ?></dd>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($lead->received_at)): ?>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Received</dt>
                                <dd class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($lead->received_at); ?></dd>
                            </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </dl>
                </div>

                <!-- TCPA Compliance -->
                <div class="bg-white shadow rounded-lg p-6 <?php echo $isEditMode ? 'edit-condensed' : ''; ?>">
                    <h3 class="text-lg font-semibold mb-4">TCPA Compliance</h3>
                    <dl class="grid grid-cols-1 gap-x-4 gap-y-4 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">TCPA Consent</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                <?php echo ($lead->tcpa_compliant ?? ($meta['tcpa_compliant'] ?? false)) ? '✅ Yes' : '❌ No'; ?>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Opt-in Date</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                <?php 
                                $optInRaw = $lead->opt_in_date ?? ($meta['opt_in_date'] ?? ($meta['originally_created'] ?? null));
                                $optInFmt = $optInRaw;
                                if (!empty($optInRaw)) {
                                    try {
                                        $dt = new DateTime($optInRaw);
                                        $optInFmt = $dt->format('m-d-Y');
                                    } catch (Exception $e) {
                                        $optInFmt = $optInRaw;
                                    }
                                }
                                echo htmlspecialchars($optInFmt ?: 'N/A');
                                ?>
                            </dd>
                        </div>
                        <?php if (!$isEditMode): ?>
                            <?php 
                            $trusted = $lead->trusted_form_cert_url ?? ($meta['trusted_form_cert_url'] ?? null);
                            if (!empty($trusted)): ?>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">TrustedForm Certificate</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    <a href="<?php echo htmlspecialchars($trusted); ?>" 
                                       target="_blank" 
                                       class="text-blue-600 hover:underline">
                                        View Certificate
                                    </a>
                                    <button type="button" class="ml-2 text-xs px-2 py-0.5 rounded bg-green-600 text-white" onclick="navigator.clipboard.writeText('<?php echo htmlspecialchars($trusted, ENT_QUOTES); ?>'); this.textContent='✓'; setTimeout(()=>this.textContent='📋',1500)">📋</button>
                                </dd>
                            </div>
                            <?php endif; ?>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">LeadiD Code</dt>
                                <dd class="mt-1 text-sm text-gray-900 font-mono text-xs"><?php 
                                    $leadIdCode = $lead->leadid_token ?? ($meta['leadid_token'] ?? ($meta['lead_id_code'] ?? null));
                                    echo htmlspecialchars($leadIdCode ?: 'N/A');
                                ?></dd>
                            </div>
                            <?php 
                            $landing = $lead->landing_page_url ?? ($meta['landing_page_url'] ?? null);
                            if (!empty($landing)): ?>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Landing Page</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    <a href="<?php echo htmlspecialchars($landing); ?>" target="_blank" class="text-blue-600 hover:underline text-xs break-all"><?php echo htmlspecialchars($landing); ?></a>
                                    <button type="button" class="ml-2 text-xs px-2 py-0.5 rounded bg-green-600 text-white" onclick="navigator.clipboard.writeText('<?php echo htmlspecialchars($landing, ENT_QUOTES); ?>'); this.textContent='✓'; setTimeout(()=>this.textContent='📋',1500)">📋</button>
                                </dd>
                            </div>
                            <?php endif; ?>
                            <?php 
                            $tcpaText = $lead->tcpa_text ?? ($meta['tcpa_consent_text'] ?? null);
                            if (!empty($tcpaText)): ?>
                            <div class="sm:col-span-2">
                                <dt class="text-sm font-medium text-gray-500">TCPA Text</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    <details>
                                        <summary class="text-blue-700 cursor-pointer inline-flex items-center">View consent text</summary>
                                        <div class="bg-gray-50 p-2 rounded text-xs mt-2"><?php echo nl2br(htmlspecialchars($tcpaText)); ?></div>
                                        <button type="button" class="mt-2 text-xs px-2 py-0.5 rounded bg-green-600 text-white" onclick="navigator.clipboard.writeText('<?php echo htmlspecialchars($tcpaText, ENT_QUOTES); ?>'); this.textContent='✓ Copied'; setTimeout(()=>this.textContent='📋 Copy',1500)">📋 Copy</button>
                                    </details>
                                </dd>
                            </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </dl>
                </div>

                <!-- Drivers first, then Vehicles -->
                <?php if (!empty($drivers)): ?>
                <div class="bg-white shadow rounded-lg p-6" data-section="drivers-edit">
                    <h3 class="text-lg font-semibold mb-4">Drivers</h3>
                    <div class="space-y-4">
                        <?php foreach ($drivers as $driver): ?>
                        <div class="border-l-4 border-green-500 pl-4">
                            <p class="font-medium">
                                <?php 
                                $driverName = trim(($driver['first_name'] ?? '') . ' ' . ($driver['last_name'] ?? ''));
                                echo htmlspecialchars($driverName ?: 'Driver');
                                ?>
                            </p>
                            <?php if (!empty($driver['license_status'])): ?>
                            <p class="text-sm text-gray-600">License: <?php echo htmlspecialchars($driver['license_status']); ?></p>
                            <?php endif; ?>
                            <details class="mt-2">
                                <summary class="text-sm text-green-700 cursor-pointer">More details</summary>
                                <div class="mt-2 grid grid-cols-1 sm:grid-cols-2 gap-2 text-sm text-gray-700">
                                    <?php 
                                    $driverDetails = [
                                        'relationship' => 'Relationship',
                                        'birth_date' => 'DOB',
                                        'gender' => 'Gender',
                                        'marital_status' => 'Marital Status',
                                        'license_state' => 'License State',
                                        'license_status' => 'License Status',
                                        'age_licensed' => 'Age Licensed',
                                        'requires_sr22' => 'Requires SR-22',
                                        'education' => 'Education',
                                        'occupation' => 'Occupation',
                                        'months_at_residence' => 'Months at Residence',
                                        'license_ever_suspended' => 'License Ever Suspended'
                                    ];
                                    foreach ($driverDetails as $key => $label) {
                                        if (isset($driver[$key]) && $driver[$key] !== '' && $driver[$key] !== null) {
                                            $val = is_bool($driver[$key]) ? ($driver[$key] ? 'Yes' : 'No') : $driver[$key];
                                            echo '<div><span class="text-gray-500">' . htmlspecialchars($label) . ':</span> ' . htmlspecialchars((string)$val) . '</div>';
                                        }
                                    }
                                    $counts = [
                                        'tickets' => 'Tickets',
                                        'accidents' => 'Accidents',
                                        'claims' => 'Claims'
                                    ];
                                    foreach ($counts as $k => $label) {
                                        if (isset($driver[$k]) && is_array($driver[$k])) {
                                            echo '<div><span class="text-gray-500">' . htmlspecialchars($label) . ':</span> ' . count($driver[$k]) . '</div>';
                                        }
                                    }
                                    ?>
                                </div>
                            </details>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <!-- Edit toggle and inputs -->
                    <div class="mt-4">
                        <button type="button" class="text-sm text-blue-700 underline" onclick="toggleEditSection('driversEditForm')">Edit</button>
                        <div id="driversEditForm" class="mt-3 hidden">
                            <?php foreach ($drivers as $idx => $driver): ?>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-3">
                                <input type="text" name="drivers[<?php echo $idx; ?>][first_name]" value="<?php echo htmlspecialchars($driver['first_name'] ?? ''); ?>" class="border rounded px-2 py-1" placeholder="First name">
                                <input type="text" name="drivers[<?php echo $idx; ?>][last_name]" value="<?php echo htmlspecialchars($driver['last_name'] ?? ''); ?>" class="border rounded px-2 py-1" placeholder="Last name">
                                <input type="text" name="drivers[<?php echo $idx; ?>][license_status]" value="<?php echo htmlspecialchars($driver['license_status'] ?? ''); ?>" class="border rounded px-2 py-1" placeholder="License status">
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <?php if (!empty($vehicles)): ?>
                <div class="bg-white shadow rounded-lg p-6" data-section="vehicles-edit">
                    <h3 class="text-lg font-semibold mb-4">Vehicles</h3>
                    <div class="space-y-4">
                        <?php foreach ($vehicles as $vehicle): ?>
                        <div class="border-l-4 border-blue-500 pl-4">
                            <p class="font-medium">
                                <?php 
                                $vehicleDesc = array_filter([
                                    $vehicle['year'] ?? '',
                                    $vehicle['make'] ?? '',
                                    $vehicle['model'] ?? ''
                                ]);
                                echo htmlspecialchars(implode(' ', $vehicleDesc) ?: 'Vehicle');
                                ?>
                            </p>
                            <?php if (!empty($vehicle['vin'])): ?>
                            <p class="text-sm text-gray-600">VIN: <?php echo htmlspecialchars($vehicle['vin']); ?></p>
                            <?php endif; ?>
                            <?php if (!empty($vehicle['primary_use'])): ?>
                            <p class="text-sm text-gray-600">Use: <?php echo htmlspecialchars($vehicle['primary_use']); ?></p>
                            <?php endif; ?>
                            <details class="mt-2">
                                <summary class="text-sm text-blue-700 cursor-pointer">More details</summary>
                                <div class="mt-2 grid grid-cols-1 sm:grid-cols-2 gap-2 text-sm text-gray-700">
                                    <?php 
                                    $vehicleDetails = [
                                        'ownership' => 'Ownership',
                                        'annual_miles' => 'Annual Miles',
                                        'weekly_commute_days' => 'Commute Days/Week',
                                        'one_way_distance' => 'One-way Commute (mi)',
                                        'garage' => 'Garage Type',
                                        'alarm' => 'Alarm',
                                        'rental' => 'Rental',
                                        'towing' => 'Towing',
                                        'collision_deductible' => 'Collision Deductible',
                                        'comprehensive_deductible' => 'Comprehensive Deductible',
                                        'submodel' => 'Trim/Submodel',
                                        'salvaged' => 'Salvaged'
                                    ];
                                    foreach ($vehicleDetails as $key => $label) {
                                        if (isset($vehicle[$key]) && $vehicle[$key] !== '' && $vehicle[$key] !== null) {
                                            $val = is_bool($vehicle[$key]) ? ($vehicle[$key] ? 'Yes' : 'No') : $vehicle[$key];
                                            echo '<div><span class="text-gray-500">' . htmlspecialchars($label) . ':</span> ' . htmlspecialchars((string)$val) . '</div>';
                                        }
                                    }
                                    ?>
                                </div>
                            </details>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <!-- Edit toggle and inputs -->
                    <div class="mt-4">
                        <button type="button" class="text-sm text-blue-700 underline" onclick="toggleEditSection('vehiclesEditForm')">Edit</button>
                        <div id="vehiclesEditForm" class="mt-3 hidden">
                            <?php foreach ($vehicles as $idx => $vehicle): ?>
                            <div class="grid grid-cols-1 sm:grid-cols-4 gap-3 mb-3">
                                <input type="text" name="vehicles[<?php echo $idx; ?>][year]" value="<?php echo htmlspecialchars($vehicle['year'] ?? ''); ?>" class="border rounded px-2 py-1" placeholder="Year">
                                <input type="text" name="vehicles[<?php echo $idx; ?>][make]" value="<?php echo htmlspecialchars($vehicle['make'] ?? ''); ?>" class="border rounded px-2 py-1" placeholder="Make">
                                <input type="text" name="vehicles[<?php echo $idx; ?>][model]" value="<?php echo htmlspecialchars($vehicle['model'] ?? ''); ?>" class="border rounded px-2 py-1" placeholder="Model">
                                <input type="text" name="vehicles[<?php echo $idx; ?>][vin]" value="<?php echo htmlspecialchars($vehicle['vin'] ?? ''); ?>" class="border rounded px-2 py-1" placeholder="VIN">
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Current Insurance Policy (editable, always visible in edit) -->
                <div class="bg-white shadow rounded-lg p-6" data-section="current-policy-edit">
                    <h3 class="text-lg font-semibold mb-4">Current Insurance Policy</h3>
                    <div id="currentPolicyEdit" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Insurance Company</label>
                            <input type="text" name="current_policy[company]" value="<?php echo htmlspecialchars($current_policy['company'] ?? ''); ?>" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" placeholder="e.g., GEICO">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Coverage Type</label>
                            <input type="text" name="current_policy[coverage_type]" value="<?php echo htmlspecialchars($current_policy['coverage_type'] ?? ''); ?>" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" placeholder="State Minimum / Full Coverage">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Expiration Date</label>
                            <input type="date" name="current_policy[expiration_date]" value="<?php echo htmlspecialchars($current_policy['expiration_date'] ?? ''); ?>" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Monthly Premium</label>
                            <input type="number" step="0.01" name="current_policy[monthly_premium]" value="<?php echo htmlspecialchars($current_policy['monthly_premium'] ?? ''); ?>" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" placeholder="e.g., 125">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Edit Mode - Qualification Form (legacy block retained but hidden) -->
            <div class="bg-white shadow rounded-lg p-6 hidden">
                <h2 class="text-2xl font-bold mb-6">Qualify Lead - Top 13 Questions</h2>
                <form method="POST" action="/agent/lead/<?php echo $lead->id; ?>/qualify" id="qualificationForm">
                    <input type="hidden" name="_token" value="<?php echo csrf_token(); ?>">
                    
                    <div class="space-y-6">
                        <!-- Question 1: Ready to speak -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                1. Are you ready to speak with an agent about auto insurance?
                            </label>
                            <select name="ready_to_speak" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                <option value="">Select...</option>
                                <option value="yes">Yes</option>
                                <option value="no">No</option>
                            </select>
                        </div>

                        <!-- Question 2: Shopping for rates -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                2. Are you shopping for better rates?
                            </label>
                            <select name="shopping_for_rates" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                <option value="">Select...</option>
                                <option value="yes">Yes</option>
                                <option value="no">No</option>
                            </select>
                        </div>

                        <!-- Question 3: Currently insured -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                3. Do you currently have auto insurance?
                            </label>
                            <select name="currently_insured" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                <option value="">Select...</option>
                                <option value="yes">Yes</option>
                                <option value="no">No</option>
                            </select>
                        </div>

                        <!-- Question 4: Insurance company -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                4. Who is your current insurance provider?
                            </label>
                            <input type="text" name="current_insurance_company" 
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                                   placeholder="Enter company name">
                        </div>

                        <!-- Question 5: Number of vehicles -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                5. How many vehicles need insurance?
                            </label>
                            <select name="num_vehicles" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                <option value="">Select...</option>
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                                <option value="4">4</option>
                                <option value="5+">5 or more</option>
                            </select>
                        </div>

                        <!-- Question 6: Number of drivers -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                6. How many drivers will be on the policy?
                            </label>
                            <select name="num_drivers" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                <option value="">Select...</option>
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                                <option value="4">4</option>
                                <option value="5+">5 or more</option>
                            </select>
                        </div>

                        <!-- Question 7: Valid license -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                7. Do all drivers have valid licenses?
                            </label>
                            <select name="valid_licenses" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                <option value="">Select...</option>
                                <option value="yes">Yes</option>
                                <option value="no">No</option>
                            </select>
                        </div>

                        <!-- Question 8: DUI/DWI -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                8. Any DUI/DWI in the last 5 years?
                            </label>
                            <select name="dui_dwi" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                <option value="">Select...</option>
                                <option value="yes">Yes</option>
                                <option value="no">No</option>
                            </select>
                        </div>

                        <!-- Question 9: SR-22 -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                9. Do you need SR-22 filing?
                            </label>
                            <select name="sr22_required" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                <option value="">Select...</option>
                                <option value="yes">Yes</option>
                                <option value="no">No</option>
                            </select>
                        </div>

                        <!-- Question 10: Homeowner -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                10. Do you own your home?
                            </label>
                            <select name="homeowner" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                <option value="">Select...</option>
                                <option value="yes">Yes</option>
                                <option value="no">No</option>
                            </select>
                        </div>

                        <!-- Question 11: Continuous coverage -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                11. Have you had continuous coverage for the last 6 months?
                            </label>
                            <select name="continuous_coverage" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                <option value="">Select...</option>
                                <option value="yes">Yes</option>
                                <option value="no">No</option>
                            </select>
                        </div>

                        <!-- Question 12: Best time to call -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                12. Best time to reach you?
                            </label>
                            <select name="best_time_to_call" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                <option value="">Select...</option>
                                <option value="morning">Morning (9am-12pm)</option>
                                <option value="afternoon">Afternoon (12pm-5pm)</option>
                                <option value="evening">Evening (5pm-8pm)</option>
                                <option value="anytime">Anytime</option>
                            </select>
                        </div>

                        <!-- Question 13: Transfer to Allstate -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                13. Would you like to speak with an Allstate agent now?
                            </label>
                            <select name="transfer_to_allstate" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                <option value="">Select...</option>
                                <option value="yes">Yes - Transfer Now</option>
                                <option value="later">Maybe Later</option>
                                <option value="no">No</option>
                            </select>
                        </div>

                        <!-- Submit buttons -->
                        <div class="flex justify-between pt-6">
                            <button type="submit" 
                                    class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700">
                                Save Qualification
                            </button>
                            <a href="?mode=view" 
                               class="bg-gray-300 text-gray-700 px-6 py-2 rounded-md hover:bg-gray-400">
                                Cancel
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        <?php else: ?>
            <!-- View Mode - Display All Information -->
            <div class="space-y-6" id="leadSections">
                <!-- Contact Information removed (now in top panel) -->

                <!-- Lead Information -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="text-lg font-semibold mb-4">Lead Information</h3>
                    <dl class="grid grid-cols-1 gap-x-4 gap-y-4 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Lead Type</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                <span class="px-2 py-1 text-xs font-medium rounded-full 
                                    <?php echo strtolower($displayType) === 'auto' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800'; ?>">
                                    <?php echo $displayType; ?>
                                </span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">External Lead ID</dt>
                            <dd class="mt-1 text-sm text-gray-900 font-mono text-xs">
                                <?php echo htmlspecialchars($lead->external_lead_id); ?>
                                <?php if (!empty($lead->external_lead_id)): ?>
                                <button type="button" class="ml-2 text-[10px] px-2 py-0.5 rounded bg-green-600 text-white" onclick="navigator.clipboard.writeText('<?php echo htmlspecialchars($lead->external_lead_id, ENT_QUOTES); ?>'); this.textContent='✓'; setTimeout(()=>this.textContent='📋',1500)">📋</button>
                                <?php endif; ?>
                            </dd>
                        </div>
                        <?php if (!empty($lead->jangle_lead_id)): ?>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Jangle ID</dt>
                            <dd class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($lead->jangle_lead_id); ?></dd>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($lead->source)): ?>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Source</dt>
                            <dd class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($lead->source); ?></dd>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($lead->campaign_id)): ?>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Campaign</dt>
                            <dd class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($lead->campaign_id); ?></dd>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($lead->received_at)): ?>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Received</dt>
                            <dd class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($lead->received_at); ?></dd>
                        </div>
                        <?php endif; ?>
                    </dl>
                </div>

                <!-- TCPA Compliance -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="text-lg font-semibold mb-4">TCPA Compliance</h3>
                    <dl class="grid grid-cols-1 gap-x-4 gap-y-4 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">TCPA Consent</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                <?php echo ($lead->tcpa_compliant ?? ($meta['tcpa_compliant'] ?? false)) ? '✅ Yes' : '❌ No'; ?>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Opt-in Date</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                <?php 
                                $optInRaw = $lead->opt_in_date ?? ($meta['opt_in_date'] ?? ($meta['originally_created'] ?? null));
                                $optInFmt = $optInRaw;
                                if (!empty($optInRaw)) {
                                    try {
                                        $dt = new DateTime($optInRaw);
                                        $optInFmt = $dt->format('m-d-Y');
                                    } catch (Exception $e) {
                                        $optInFmt = $optInRaw;
                                    }
                                }
                                echo htmlspecialchars($optInFmt ?: 'N/A');
                                ?>
                            </dd>
                        </div>
                        <?php 
                        $trusted = $lead->trusted_form_cert_url ?? ($meta['trusted_form_cert_url'] ?? null);
                        if (!empty($trusted)): ?>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">TrustedForm Certificate</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                <a href="<?php echo htmlspecialchars($trusted); ?>" 
                                   target="_blank" 
                                   class="text-blue-600 hover:underline">
                                    View Certificate
                                </a>
                                <button type="button" class="ml-2 text-xs px-2 py-0.5 rounded bg-green-600 text-white" onclick="navigator.clipboard.writeText('<?php echo htmlspecialchars($trusted, ENT_QUOTES); ?>'); this.textContent='✓'; setTimeout(()=>this.textContent='📋',1500)">📋</button>
                            </dd>
                        </div>
                        <?php endif; ?>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">LeadiD Code</dt>
                            <dd class="mt-1 text-sm text-gray-900 font-mono text-xs"><?php 
                                $leadIdCode = $lead->leadid_token ?? ($meta['leadid_token'] ?? ($meta['lead_id_code'] ?? null));
                                echo htmlspecialchars($leadIdCode ?: 'N/A');
                            ?></dd>
                        </div>
                        <?php 
                        $landing = $lead->landing_page_url ?? ($meta['landing_page_url'] ?? null);
                        if (!empty($landing)): ?>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Landing Page</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                <a href="<?php echo htmlspecialchars($landing); ?>" target="_blank" class="text-blue-600 hover:underline text-xs break-all"><?php echo htmlspecialchars($landing); ?></a>
                                <button type="button" class="ml-2 text-xs px-2 py-0.5 rounded bg-green-600 text-white" onclick="navigator.clipboard.writeText('<?php echo htmlspecialchars($landing, ENT_QUOTES); ?>'); this.textContent='✓'; setTimeout(()=>this.textContent='📋',1500)">📋</button>
                            </dd>
                        </div>
                        <?php endif; ?>
                        <?php 
                        $tcpaText = $lead->tcpa_text ?? ($meta['tcpa_consent_text'] ?? null);
                        if (!empty($tcpaText)): ?>
                        <div class="sm:col-span-2">
                            <dt class="text-sm font-medium text-gray-500">TCPA Text</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                <details>
                                    <summary class="text-blue-700 cursor-pointer inline-flex items-center">View consent text</summary>
                                    <div class="bg-gray-50 p-2 rounded text-xs mt-2"><?php echo nl2br(htmlspecialchars($tcpaText)); ?></div>
                                    <button type="button" class="mt-2 text-xs px-2 py-0.5 rounded bg-green-600 text-white" onclick="navigator.clipboard.writeText('<?php echo htmlspecialchars($tcpaText, ENT_QUOTES); ?>'); this.textContent='✓ Copied'; setTimeout(()=>this.textContent='📋 Copy',1500)">📋 Copy</button>
                                </details>
                            </dd>
                        </div>
                        <?php endif; ?>
                    </dl>
                </div>

                

                <?php if (!empty($vehicles)): ?>
                <!-- Vehicles Section -->
                <div class="bg-white shadow rounded-lg p-6" data-section="vehicles">
                    <h3 class="text-lg font-semibold mb-4">Vehicles</h3>
                    <div class="space-y-4">
                        <?php foreach ($vehicles as $vehicle): ?>
                        <div class="border-l-4 border-blue-500 pl-4">
                            <p class="font-medium">
                                <?php 
                                $vehicleDesc = array_filter([
                                    $vehicle['year'] ?? '',
                                    $vehicle['make'] ?? '',
                                    $vehicle['model'] ?? ''
                                ]);
                                echo htmlspecialchars(implode(' ', $vehicleDesc) ?: 'Vehicle');
                                ?>
                            </p>
                            <?php if (!empty($vehicle['vin'])): ?>
                            <p class="text-sm text-gray-600">VIN: <?php echo htmlspecialchars($vehicle['vin']); ?></p>
                            <?php endif; ?>
                            <?php if (!empty($vehicle['primary_use'])): ?>
                            <p class="text-sm text-gray-600">Use: <?php echo htmlspecialchars($vehicle['primary_use']); ?></p>
                            <?php endif; ?>
                            <details class="mt-2">
                                <summary class="text-sm text-blue-700 cursor-pointer">More details</summary>
                                <div class="mt-2 grid grid-cols-1 sm:grid-cols-2 gap-2 text-sm text-gray-700">
                                    <?php 
                                    $vehicleDetails = [
                                        'ownership' => 'Ownership',
                                        'annual_miles' => 'Annual Miles',
                                        'weekly_commute_days' => 'Commute Days/Week',
                                        'one_way_distance' => 'One-way Commute (mi)',
                                        'garage' => 'Garage Type',
                                        'alarm' => 'Alarm',
                                        'rental' => 'Rental',
                                        'towing' => 'Towing',
                                        'collision_deductible' => 'Collision Deductible',
                                        'comprehensive_deductible' => 'Comprehensive Deductible',
                                        'submodel' => 'Trim/Submodel',
                                        'salvaged' => 'Salvaged'
                                    ];
                                    foreach ($vehicleDetails as $key => $label) {
                                        if (isset($vehicle[$key]) && $vehicle[$key] !== '' && $vehicle[$key] !== null) {
                                            $val = is_bool($vehicle[$key]) ? ($vehicle[$key] ? 'Yes' : 'No') : $vehicle[$key];
                                            echo '<div><span class="text-gray-500">' . htmlspecialchars($label) . ':</span> ' . htmlspecialchars((string)$val) . '</div>';
                                        }
                                    }
                                    ?>
                                </div>
                            </details>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <?php if (!empty($drivers)): ?>
                <!-- Drivers Section -->
                <div class="bg-white shadow rounded-lg p-6" data-section="drivers">
                    <h3 class="text-lg font-semibold mb-4">Drivers</h3>
                    <div class="space-y-4">
                        <?php foreach ($drivers as $driver): ?>
                        <div class="border-l-4 border-green-500 pl-4">
                            <p class="font-medium">
                                <?php 
                                $driverName = trim(
                                    ($driver['first_name'] ?? '') . ' ' . 
                                    ($driver['last_name'] ?? '')
                                );
                                echo htmlspecialchars($driverName ?: 'Driver');
                                ?>
                            </p>
                            <?php if (!empty($driver['license_status'])): ?>
                            <p class="text-sm text-gray-600">License: <?php echo htmlspecialchars($driver['license_status']); ?></p>
                            <?php endif; ?>
                            <?php if (!empty($driver['dob']) || !empty($driver['birth_date'])): ?>
                            <p class="text-sm text-gray-600">DOB: <?php echo htmlspecialchars($driver['dob'] ?? $driver['birth_date']); ?></p>
                            <?php endif; ?>
                            <details class="mt-2">
                                <summary class="text-sm text-green-700 cursor-pointer">More details</summary>
                                <div class="mt-2 grid grid-cols-1 sm:grid-cols-2 gap-2 text-sm text-gray-700">
                                    <?php 
                                    $driverDetails = [
                                        'relationship' => 'Relationship',
                                        'gender' => 'Gender',
                                        'marital_status' => 'Marital Status',
                                        'license_state' => 'License State',
                                        'license_status' => 'License Status',
                                        'age_licensed' => 'Age Licensed',
                                        'requires_sr22' => 'Requires SR-22',
                                        'education' => 'Education',
                                        'occupation' => 'Occupation',
                                        'months_at_residence' => 'Months at Residence',
                                        'license_ever_suspended' => 'License Ever Suspended'
                                    ];
                                    foreach ($driverDetails as $key => $label) {
                                        if (isset($driver[$key]) && $driver[$key] !== '' && $driver[$key] !== null) {
                                            $val = is_bool($driver[$key]) ? ($driver[$key] ? 'Yes' : 'No') : $driver[$key];
                                            echo '<div><span class="text-gray-500">' . htmlspecialchars($label) . ':</span> ' . htmlspecialchars((string)$val) . '</div>';
                                        }
                                    }
                                    $counts = [
                                        'tickets' => 'Tickets',
                                        'accidents' => 'Accidents',
                                        'claims' => 'Claims'
                                    ];
                                    foreach ($counts as $k => $label) {
                                        if (isset($driver[$k]) && is_array($driver[$k])) {
                                            echo '<div><span class="text-gray-500">' . htmlspecialchars($label) . ':</span> ' . count($driver[$k]) . '</div>';
                                        }
                                    }
                                    ?>
                                </div>
                            </details>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Current Insurance Policy -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="text-lg font-semibold mb-4">Current Insurance Policy</h3>
                    <dl class="grid grid-cols-1 gap-x-4 gap-y-4 sm:grid-cols-2 <?php echo $isEditMode ? '' : (empty($current_policy) ? 'hidden' : ''); ?>">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Insurance Company</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                <?php if ($isEditMode): ?>
                                    <input type="text" name="current_policy[company]" value="<?php echo htmlspecialchars($current_policy['company'] ?? ''); ?>" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" placeholder="e.g., GEICO">
                                <?php else: ?>
                                    <?php echo htmlspecialchars($current_policy['company'] ?? ''); ?>
                                <?php endif; ?>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Coverage Type</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                <?php if ($isEditMode): ?>
                                    <input type="text" name="current_policy[coverage_type]" value="<?php echo htmlspecialchars($current_policy['coverage_type'] ?? ''); ?>" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" placeholder="State Minimum / Full Coverage">
                                <?php else: ?>
                                    <?php echo htmlspecialchars($current_policy['coverage_type'] ?? ''); ?>
                                <?php endif; ?>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Expiration Date</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                <?php if ($isEditMode): ?>
                                    <input type="date" name="current_policy[expiration_date]" value="<?php echo htmlspecialchars($current_policy['expiration_date'] ?? ''); ?>" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                <?php else: ?>
                                    <?php echo htmlspecialchars($current_policy['expiration_date'] ?? ''); ?>
                                <?php endif; ?>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Monthly Premium</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                <?php if ($isEditMode): ?>
                                    <input type="number" step="0.01" name="current_policy[monthly_premium]" value="<?php echo htmlspecialchars($current_policy['monthly_premium'] ?? ''); ?>" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" placeholder="e.g., 125">
                                <?php else: ?>
                                    <?php echo isset($current_policy['monthly_premium']) ? '$' . htmlspecialchars($current_policy['monthly_premium']) : ''; ?>
                                <?php endif; ?>
                            </dd>
                        </div>
                    </dl>
                </div>

                <!-- removed duplicate Lead Information section: now rendered above per spec -->

                <!-- removed duplicate TCPA section: now rendered above per spec -->

                <!-- removed Technical Details for ordering cleanliness; can re-add later if needed -->
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function toggleEditSection(id){
    const el = document.getElementById(id);
    if(!el) return;
    el.classList.toggle('hidden');
}

async function saveQualification(){
    const formEl = document.getElementById('qualificationFormTop');
    if(!formEl){ return; }
    try {
        // 1) Persist contact fields (first/last/phone/email/address/city/state/zip) before qualification
        try {
            const pick = (sel, fallback='') => {
                const el = document.querySelector(sel);
                return (el && (el.value ?? el.textContent)) ? (el.value ?? el.textContent) : fallback;
            };
            const contactPayload = {
                first_name: pick('input[name="first_name"]', '<?php echo $lead->first_name ?? '';?>'),
                last_name:  pick('input[name="last_name"]',  '<?php echo $lead->last_name ?? '';?>'),
                phone:      pick('input[name="phone"]',      '<?php echo $lead->phone ?? '';?>'),
                email:      pick('input[name="email"]',      '<?php echo $lead->email ?? '';?>'),
                address:    pick('input[name="address"]',    '<?php echo $lead->address ?? '';?>'),
                city:       pick('input[name="city"]',       '<?php echo $lead->city ?? '';?>'),
                state:      (document.getElementById('state')?.value || pick('input[name="state"]', '<?php echo $lead->state ?? '';?>')),
                zip_code:   (document.getElementById('zip_code')?.value || pick('input[name="zip_code"]', '<?php echo $lead->zip_code ?? '';?>')),
            };
            // Try CSRF-free GET first, fallback to JSON PUT
            const qs = new URLSearchParams(contactPayload).toString();
            let respContact = await fetch(`/api/lead/${leadId}/contact-save?` + qs, { method: 'GET' });
            if (!respContact.ok) {
                respContact = await fetch(`/api/lead/${leadId}/contact`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(contactPayload)
                });
            }
            if (!respContact.ok) {
                const errTxt = await respContact.text();
                showToast('Save failed (contact): ' + errTxt, 'error');
                return;
            }
        } catch (e) {
            showToast('Save error (contact): ' + (e?.message || e), 'error');
            return;
        }

        // 2) Persist qualification and sub-sections
        const formData = new FormData(formEl);
        // Include inline edit sections (drivers, vehicles, current policy)
        document.querySelectorAll('[name^="drivers["]').forEach(el=>formData.append(el.getAttribute('name'), el.value));
        document.querySelectorAll('[name^="vehicles["]').forEach(el=>formData.append(el.getAttribute('name'), el.value));
        document.querySelectorAll('[name^="current_policy["]').forEach(el=>formData.append(el.getAttribute('name'), el.value));
        formData.append('as_json', '1');
        const resp = await fetch(formEl.action, { method: 'POST', body: formData });
        if(!resp.ok){
            const err = await resp.text();
            showToast('Save failed: ' + err, 'error');
            return;
        }
        showToast('Saved');
    } catch(e){
        showToast('Save error: ' + (e?.message || e), 'error');
    }
}
<?php
// Compute defaults for Allstate-required fields from primary driver/payload
$primaryDriver = [];
if (is_array($drivers) && !empty($drivers)) {
    $primaryDriver = $drivers[0];
}
$defaultDob = $primaryDriver['dob'] ?? $primaryDriver['birth_date'] ?? ($lead->date_of_birth ?? null);
$defaultGender = $primaryDriver['gender'] ?? null;
$defaultMarital = $primaryDriver['marital_status'] ?? null;
?>

// Get lead ID from PHP
const leadId = '<?php echo $lead->id; ?>';
const defaultDob = '<?php echo htmlspecialchars($defaultDob ?? "", ENT_QUOTES); ?>';
const defaultGender = '<?php echo htmlspecialchars($defaultGender ?? "", ENT_QUOTES); ?>';
const defaultMarital = '<?php echo htmlspecialchars($defaultMarital ?? "", ENT_QUOTES); ?>';

// Toggle insurance questions based on currently insured selection
function toggleInsuranceQuestions() {
    const insured = document.getElementById('currently_insured').value;
    const insuranceQuestions = document.getElementById('insurance_questions');
    
    if (insured === 'yes') {
        insuranceQuestions.style.display = 'block';
    } else {
        insuranceQuestions.style.display = 'none';
    }
}

// Toggle DUI questions based on DUI/SR22 selection
function toggleDUIQuestions() {
    const duiSr22 = document.getElementById('dui_sr22').value;
    const duiQuestions = document.getElementById('dui_questions');
    
    if (duiSr22 === 'dui_only' || duiSr22 === 'both') {
        duiQuestions.style.display = 'block';
    } else {
        duiQuestions.style.display = 'none';
    }
}

// Get form data for enrichment
function getFormData() {
    const data = {
        phone: '<?php echo $lead->phone ?? ''; ?>',
        first_name: '<?php echo $lead->first_name ?? ''; ?>',
        last_name: '<?php echo $lead->last_name ?? ''; ?>',
        email: '<?php echo $lead->email ?? ''; ?>',
        address: '<?php echo $lead->address ?? ''; ?>',
        city: '<?php echo $lead->city ?? ''; ?>',
        state: document.getElementById('state')?.value || '<?php echo $lead->state ?? ''; ?>',
        zip_code: document.getElementById('zip_code')?.value || '<?php echo $lead->zip_code ?? ''; ?>',
        currently_insured: document.getElementById('currently_insured')?.value || '',
        current_provider: document.getElementById('current_provider')?.value || '',
        insurance_duration: document.getElementById('insurance_duration')?.value || '',
        num_vehicles: document.getElementById('num_vehicles')?.value || '',
        home_status: document.getElementById('home_status')?.value || '',
        dui_sr22: document.getElementById('dui_sr22')?.value || '',
        dui_timeframe: document.getElementById('dui_timeframe')?.value || '',
        allstate_quote: document.getElementById('allstate_quote')?.value || '',
        ready_to_speak: document.getElementById('ready_to_speak')?.value || '',
        // Allstate-required fields (sourced from payload/primary driver)
        date_of_birth: defaultDob || '',
        gender: defaultGender || '',
        marital_status: defaultMarital || ''
    };
    return data;
}

// Enrich lead function for RingBA
async function enrichLead(type) {
    // Validate based on type
    if (type === 'insured') {
        const currentlyInsured = document.getElementById('currently_insured')?.value || '';
        if (!currentlyInsured || currentlyInsured.toLowerCase() === 'no') {
            alert('❌ Requirement error\n\nYou clicked INSURED, but "Currently insured" is set to No.\n\nUpdate it to Yes or use the UNINSURED enrichment.');
            return;
        }
    }
    if (type === 'uninsured') {
        const currentlyInsured = document.getElementById('currently_insured')?.value || '';
        if (currentlyInsured.toLowerCase() === 'yes') {
            alert('❌ Requirement error\n\nYou clicked UNINSURED, but "Currently insured" is set to Yes.\n\nChange it to No or use the INSURED enrichment.');
            return;
        }
    }
    
    const data = getFormData();

    // Save first, then enrich
    try {
        const formEl = document.getElementById('qualificationFormTop');
        if (formEl) {
            const formData = new FormData(formEl);
            formData.append('as_json', '1');
            const resp = await fetch(formEl.action, { method: 'POST', body: formData });
            if (!resp.ok) {
                const err = await resp.text();
                alert('Save failed before enrichment: ' + err);
                return;
            }
        }
    } catch (e) {
        alert('Could not save before enrichment: ' + (e?.message || e));
        return;
    }
    
    // RingBA enrichment base URLs
    const enrichmentBase = {
        insured: 'https://display.ringba.com/enrich/2674154334576444838',
        uninsured: 'https://display.ringba.com/enrich/2676487329580844084',
        homeowner: 'https://display.ringba.com/enrich/2717035800150673197'
    };
    
    const baseURL = enrichmentBase[type];
    if (!baseURL) {
        alert('Invalid enrichment type');
        return;
    }

    // Helper functions for data mapping
    const yn = (v) => (/^(y|yes|true|1)$/i.test(`${v}`) ? 'true' : 'false');
    const mapContinuous = (v) => {
        switch(v) {
            case 'under_6_months': return 5;
            case '6_months_1_year': return 6;
            case '1_3_years': return 12;
            case 'over_3_years': return 24;
            default: return 0;
        }
    };
    const digits = (p) => (p || '').replace(/[^0-9]/g, '');
    const mapResidence = (v) => (v === 'own' ? 'own' : 'rent');
    const isAllstateCustomer = () => {
        const provider = (data.current_provider || '').toLowerCase().trim();
        return provider.includes('allstate') ? 'true' : 'false';
    };

    // Build query parameters based on type
    let orderedPairs = [];
    
    if (type === 'insured') {
        orderedPairs = [
            ['primary_phone', digits(data.phone)],
            ['currently_insured', 'true'],
            ['current_insurance_company', data.current_provider || ''],
            ['allstate', isAllstateCustomer()],
            ['continuous_coverage', mapContinuous(data.insurance_duration)],
            ['valid_license', 'true'],
            ['num_vehicles', data.num_vehicles || ''],
            ['dui', (data.dui_sr22 === 'dui_only' || data.dui_sr22 === 'both') ? 'true' : 'false'],
            ['requires_sr22', (data.dui_sr22 === 'sr22_only' || data.dui_sr22 === 'both') ? 'true' : 'false'],
            ['state', data.state || ''],
            ['zip_code', data.zip_code || ''],
            ['first_name', data.first_name || ''],
            ['last_name', data.last_name || ''],
            ['email', data.email || ''],
            ['date_of_birth', data.date_of_birth || ''],
            ['gender', (function(){
                const g = (data.gender || '').toLowerCase().trim();
                if (g === 'm' || g === 'male') return 'male';
                if (g === 'f' || g === 'female') return 'female';
                if (g === 'x') return 'X';
                return 'unknown';
            })()],
            ['marital_status', (function(){
                const m = (data.marital_status || '').toLowerCase().trim();
                const valid = ['single','married','separated','divorced','widowed'];
                return valid.includes(m) ? m : (m || 'single');
            })()],
            ['residence_status', mapResidence(data.home_status)],
            ['tcpa_compliant', 'true'],
            ['external_id', leadId || ''],
            ['received_quote', yn(data.allstate_quote)],
            ['ready_to_talk', yn(data.ready_to_speak)]
        ];
    } else if (type === 'uninsured') {
        orderedPairs = [
            ['primary_phone', digits(data.phone)],
            ['currently_insured', 'false'],
            ['current_insurance_company', ''],
            ['allstate', 'false'],
            ['continuous_coverage', '0'],
            ['valid_license', 'true'],
            ['num_vehicles', data.num_vehicles || ''],
            ['dui', (data.dui_sr22 === 'dui_only' || data.dui_sr22 === 'both') ? 'true' : 'false'],
            ['requires_sr22', (data.dui_sr22 === 'sr22_only' || data.dui_sr22 === 'both') ? 'true' : 'false'],
            ['state', data.state || ''],
            ['zip_code', data.zip_code || ''],
            ['first_name', data.first_name || ''],
            ['last_name', data.last_name || ''],
            ['email', data.email || ''],
            ['residence_status', mapResidence(data.home_status)],
            ['tcpa_compliant', 'true'],
            ['external_id', leadId || ''],
            ['received_quote', yn(data.allstate_quote)],
            ['ready_to_talk', yn(data.ready_to_speak)]
        ];
    } else if (type === 'homeowner') {
        orderedPairs = [
            ['callerid', digits(data.phone)],
            ['homeowner', 'Y'],
            ['allstate', isAllstateCustomer()],
            ['address', data.address || ''],
            ['city', data.city || ''],
            ['state_name', data.state || ''],
            ['zip_code', data.zip_code || ''],
            ['first_name', data.first_name || ''],
            ['last_name', data.last_name || ''],
            ['email', data.email || '']
        ];
    }
    
    // Build query string
    const qs = orderedPairs
        .filter(([_, v]) => v !== undefined && v !== null && `${v}`.length > 0)
        .map(([k, v]) => `${encodeURIComponent(k)}=${encodeURIComponent(v)}`)
        .join('&');
    
    const enrichmentURL = `${baseURL}?${qs}`;
    
    // Open in new window
    const popup = window.open(enrichmentURL, '_blank');
    if (!popup) {
        showToast('Please allow popups for enrichment features.', 'error');
    }
}

// Simple JavaScript for form handling
document.addEventListener('DOMContentLoaded', function() {
    // Toast container
    if (!document.getElementById('toast-container')) {
        const c = document.createElement('div');
        c.id = 'toast-container';
        c.style.position = 'fixed';
        c.style.top = '90px';
        c.style.right = '16px';
        c.style.zIndex = '99999';
        c.style.display = 'flex';
        c.style.flexDirection = 'column';
        c.style.gap = '8px';
        document.body.appendChild(c);
    }
    // Initialize conditional fields on page load
    toggleInsuranceQuestions();
    toggleDUIQuestions();

    const form = document.getElementById('qualificationFormTop');
    if (form) {
        form.addEventListener('submit', function(e) {
            // Add any validation here if needed
            console.log('Form submitted');
        });
    }
    
    window.copyPayload = async (btn) => {
        try {
            const url = btn.getAttribute('data-url');
            const res = await fetch(url);
            const text = await res.text();
            await navigator.clipboard.writeText(text);
            const original = btn.textContent;
            btn.textContent = '✓ Copied!';
            setTimeout(()=>btn.textContent = original, 1500);
        } catch (err) {
            console.error('Copy failed', err);
        }
    };
});

// Simple toast helper (auto-dismiss, non-blocking)
function showToast(message, type = 'success') {
    try {
        const container = document.getElementById('toast-container') || (() => {
            const c = document.createElement('div');
            c.id = 'toast-container';
            c.style.position = 'fixed';
            c.style.top = '90px';
            c.style.right = '16px';
            c.style.zIndex = '99999';
            c.style.display = 'flex';
            c.style.flexDirection = 'column';
            c.style.gap = '8px';
            document.body.appendChild(c);
            return c;
        })();
        const toast = document.createElement('div');
        toast.textContent = message;
        toast.style.padding = '10px 14px';
        toast.style.borderRadius = '6px';
        toast.style.color = '#fff';
        toast.style.boxShadow = '0 6px 18px rgba(0,0,0,0.15)';
        toast.style.fontSize = '14px';
        toast.style.opacity = '0';
        toast.style.transition = 'opacity 150ms ease, transform 150ms ease';
        toast.style.transform = 'translateY(-6px)';
        const colors = type === 'error' ? ['#dc2626', '#991b1b'] : ['#16a34a', '#166534'];
        toast.style.background = `linear-gradient(135deg, ${colors[0]} 0%, ${colors[1]} 100%)`;
        container.appendChild(toast);
        requestAnimationFrame(() => { toast.style.opacity = '1'; toast.style.transform = 'translateY(0)'; });
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(-6px)';
            setTimeout(() => { toast.remove(); }, 200);
        }, 2000);
    } catch (_) { /* no-op */ }
}
</script>
@endsection
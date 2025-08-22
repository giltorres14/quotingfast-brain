#!/usr/bin/env php
<?php
/**
 * CREATE CLEAN LEAD DISPLAY FROM SCRATCH
 * This will create a working version without any Blade errors
 */

echo "\n🔧 CREATING CLEAN LEAD DISPLAY PAGE\n";
echo "====================================\n\n";

$cleanBlade = <<<'BLADE'
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
$lead->zip = $lead->zip ?? '';
$lead->external_lead_id = $lead->external_lead_id ?? $lead->id;
$lead->type = $lead->type ?? 'unknown';
$lead->tcpa_compliant = $lead->tcpa_compliant ?? false;
$lead->opt_in_date = $lead->opt_in_date ?? null;
$lead->trusted_form_cert_url = $lead->trusted_form_cert_url ?? null;

// Parse JSON fields if they're strings
$vehicles = isset($lead->vehicles) ? (is_string($lead->vehicles) ? json_decode($lead->vehicles, true) : $lead->vehicles) : [];
$drivers = isset($lead->drivers) ? (is_string($lead->drivers) ? json_decode($lead->drivers, true) : $lead->drivers) : [];
$current_policy = isset($lead->current_policy) ? (is_string($lead->current_policy) ? json_decode($lead->current_policy, true) : $lead->current_policy) : [];

// Determine lead type
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
    <div style="position: fixed !important; top: 0; left: 0; right: 0; z-index: 9999 !important; background: white; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <div class="container mx-auto px-4 py-4">
            <div class="flex items-center justify-between">
                <!-- Left section -->
                <div class="flex items-center space-x-4">
                    <a href="/leads" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        <svg class="mr-2 -ml-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Back to Leads
                    </a>
                    
                    <!-- Avatar -->
                    <div class="h-16 w-16 rounded-full bg-indigo-600 flex items-center justify-center text-white font-bold text-lg" style="margin-top: 8px;">
                        <?php echo substr($displayType, 0, 4); ?>
                    </div>
                </div>

                <!-- Center section -->
                <div class="flex-1 text-center px-4">
                    <div class="text-sm text-gray-500 mb-1">
                        <?php echo $isEditMode ? 'Edit Mode' : 'View Only'; ?>
                    </div>
                    <div class="font-bold text-lg">
                        <?php echo htmlspecialchars($lead->name); ?>
                    </div>
                    <div class="text-gray-600 text-base font-semibold" style="line-height: 1.4;">
                        <?php echo htmlspecialchars($lead->phone); ?><br>
                        <?php echo htmlspecialchars($lead->email); ?>
                    </div>
                    <div class="text-sm text-gray-500 mt-1">
                        Lead ID: <?php echo htmlspecialchars($lead->external_lead_id); ?>
                    </div>
                </div>

                <!-- Right section -->
                <div class="flex items-center space-x-2">
                    <a href="/api/lead/<?php echo $lead->id; ?>/payload" 
                       target="_blank"
                       class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        View Payload
                    </a>
                    <?php if (!$isEditMode): ?>
                    <a href="?mode=edit" 
                       class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                        Edit Lead
                    </a>
                    <?php else: ?>
                    <a href="?mode=view" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        View Mode
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container mx-auto px-4" style="padding-top: 120px;">
        <?php if ($isEditMode): ?>
            <!-- Edit Mode - Qualification Form -->
            <div class="bg-white shadow rounded-lg p-6">
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
            <div class="space-y-6">
                <!-- Contact Information -->
                <div class="bg-white shadow rounded-lg p-6">
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
                                    $lead->zip
                                ]);
                                echo htmlspecialchars(implode(', ', $addressParts) ?: 'N/A');
                                ?>
                            </dd>
                        </div>
                    </dl>
                </div>

                <?php if (!empty($vehicles)): ?>
                <!-- Vehicles Section -->
                <div class="bg-white shadow rounded-lg p-6">
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
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <?php if (!empty($drivers)): ?>
                <!-- Drivers Section -->
                <div class="bg-white shadow rounded-lg p-6">
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
                            <?php if (!empty($driver['dob'])): ?>
                            <p class="text-sm text-gray-600">DOB: <?php echo htmlspecialchars($driver['dob']); ?></p>
                            <?php endif; ?>
                            <?php if (!empty($driver['license_status'])): ?>
                            <p class="text-sm text-gray-600">License: <?php echo htmlspecialchars($driver['license_status']); ?></p>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- TCPA Compliance -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="text-lg font-semibold mb-4">TCPA Compliance</h3>
                    <dl class="grid grid-cols-1 gap-x-4 gap-y-4 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">TCPA Consent</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                <?php echo $lead->tcpa_compliant ? '✅ Yes' : '❌ No'; ?>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Opt-in Date</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                <?php echo htmlspecialchars($lead->opt_in_date ?: 'N/A'); ?>
                            </dd>
                        </div>
                        <?php if (!empty($lead->trusted_form_cert_url)): ?>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">TrustedForm</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                <a href="<?php echo htmlspecialchars($lead->trusted_form_cert_url); ?>" 
                                   target="_blank" 
                                   class="text-blue-600 hover:underline">
                                    View Certificate
                                </a>
                            </dd>
                        </div>
                        <?php endif; ?>
                    </dl>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Simple JavaScript for form handling
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('qualificationForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            // Add any validation here if needed
            console.log('Form submitted');
        });
    }
});
</script>
@endsection
BLADE;

// Save the clean version
$outputPath = __DIR__ . '/resources/views/agent/lead-display.blade.php';
$backupPath = __DIR__ . '/resources/views/agent/lead-display.backup.' . date('YmdHis') . '.blade.php';

// Backup current
if (file_exists($outputPath)) {
    copy($outputPath, $backupPath);
    echo "✅ Backed up current file to: " . basename($backupPath) . "\n";
}

// Write clean version
file_put_contents($outputPath, $cleanBlade);
echo "✅ Created clean lead-display.blade.php\n\n";

// Verify it's balanced
$content = file_get_contents($outputPath);
$ifCount = substr_count($content, '@if');
$endifCount = substr_count($content, '@endif');
$extendsCount = substr_count($content, '@extends');
$sectionCount = substr_count($content, '@section');
$endsectionCount = substr_count($content, '@endsection');

echo "📊 Blade directive counts:\n";
echo "  @extends: $extendsCount\n";
echo "  @section: $sectionCount\n";
echo "  @endsection: $endsectionCount\n";
echo "  @if: $ifCount\n";
echo "  @endif: $endifCount\n";
echo "\n";

if ($ifCount === $endifCount && $sectionCount === $endsectionCount) {
    echo "✅ All Blade directives are balanced!\n";
    echo "\n🚀 Ready to deploy:\n";
    echo "   git add -A && git commit -m 'Clean lead display page' && git push\n";
} else {
    echo "❌ Blade directives are not balanced!\n";
}


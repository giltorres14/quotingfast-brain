@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Sticky Header -->
    <div class="fixed top-0 left-0 right-0 bg-white shadow-lg z-50" style="position: fixed !important; z-index: 9999 !important;">
        <div class="container mx-auto px-4 py-4">
            <div class="flex items-center justify-between">
                <!-- Left section with back button and avatar -->
                <div class="flex items-center space-x-4">
                    <a href="/leads" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <svg class="mr-2 -ml-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Back to Leads
                    </a>
                    
                    <!-- Avatar circle -->
                    <div class="flex-shrink-0">
                        <div class="h-16 w-16 rounded-full bg-indigo-600 flex items-center justify-center text-white font-bold text-lg" style="margin-top: 8px;">
                            <?php
                            $leadType = 'AUTO'; // Default
                            if (isset($lead->type) && !empty($lead->type) && strtolower($lead->type) !== 'unknown') {
                                $leadType = strtoupper($lead->type);
                            } elseif (isset($lead->vehicles) && !empty($lead->vehicles)) {
                                $vehicles = is_string($lead->vehicles) ? json_decode($lead->vehicles, true) : $lead->vehicles;
                                if (!empty($vehicles)) {
                                    $leadType = 'AUTO';
                                }
                            }
                            echo substr($leadType, 0, 4);
                            ?>
                        </div>
                    </div>
                </div>

                <!-- Center section with contact info -->
                <div class="flex-1 text-center px-4">
                    <div class="text-sm text-gray-500 mb-1">
                        <?php echo isset($_GET['mode']) && $_GET['mode'] === 'edit' ? 'Edit Mode' : 'View Only'; ?>
                    </div>
                    <div class="font-bold text-lg">
                        <?php echo htmlspecialchars($lead->name ?? 'Unknown'); ?>
                    </div>
                    <div class="text-gray-600 text-base font-semibold" style="line-height: 1.4;">
                        <?php echo htmlspecialchars($lead->phone ?? ''); ?><br>
                        <?php echo htmlspecialchars($lead->email ?? ''); ?>
                    </div>
                    <div class="text-sm text-gray-500 mt-1">
                        Lead ID: <?php echo htmlspecialchars($lead->external_lead_id ?? $lead->id ?? ''); ?>
                    </div>
                </div>

                <!-- Right section with buttons -->
                <div class="flex items-center space-x-2">
                    <a href="/api/lead/<?php echo $lead->id; ?>/payload" 
                       target="_blank"
                       class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        View Payload
                    </a>
                    <?php if (!isset($_GET['mode']) || $_GET['mode'] !== 'edit'): ?>
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

    <!-- Main Content (with padding for fixed header) -->
    <div class="container mx-auto px-4" style="padding-top: 120px;">
        <?php if (isset($_GET['mode']) && $_GET['mode'] === 'edit'): ?>
            <!-- Edit Mode - Qualification Form -->
            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-2xl font-bold mb-6">Qualify Lead</h2>
                <form method="POST" action="/agent/lead/<?php echo $lead->id; ?>/qualify">
                    <!-- Form fields here -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Ready to speak with agent?</label>
                        <select name="ready_to_speak" class="mt-1 block w-full border-gray-300 rounded-md">
                            <option value="yes">Yes</option>
                            <option value="no">No</option>
                        </select>
                    </div>
                    <!-- Add more form fields as needed -->
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-md">Save Qualification</button>
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
                            <dd class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($lead->name ?? 'N/A'); ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Phone</dt>
                            <dd class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($lead->phone ?? 'N/A'); ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Email</dt>
                            <dd class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($lead->email ?? 'N/A'); ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Address</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                <?php echo htmlspecialchars($lead->address ?? ''); ?>
                                <?php echo htmlspecialchars($lead->city ?? ''); ?>
                                <?php echo htmlspecialchars($lead->state ?? ''); ?>
                                <?php echo htmlspecialchars($lead->zip ?? ''); ?>
                            </dd>
                        </div>
                    </dl>
                </div>

                <?php 
                // Handle vehicles data
                $vehicles = null;
                if (isset($lead->vehicles)) {
                    if (is_string($lead->vehicles)) {
                        $vehicles = json_decode($lead->vehicles, true);
                    } else {
                        $vehicles = $lead->vehicles;
                    }
                }
                
                if (!empty($vehicles)): 
                ?>
                <!-- Vehicles Section -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="text-lg font-semibold mb-4">Vehicles</h3>
                    <div class="space-y-4">
                        <?php foreach ($vehicles as $vehicle): ?>
                        <div class="border-l-4 border-blue-500 pl-4">
                            <p class="font-medium">
                                <?php echo htmlspecialchars($vehicle['year'] ?? ''); ?> 
                                <?php echo htmlspecialchars($vehicle['make'] ?? ''); ?> 
                                <?php echo htmlspecialchars($vehicle['model'] ?? ''); ?>
                            </p>
                            <?php if (!empty($vehicle['vin'])): ?>
                            <p class="text-sm text-gray-600">VIN: <?php echo htmlspecialchars($vehicle['vin']); ?></p>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <?php 
                // Handle drivers data
                $drivers = null;
                if (isset($lead->drivers)) {
                    if (is_string($lead->drivers)) {
                        $drivers = json_decode($lead->drivers, true);
                    } else {
                        $drivers = $lead->drivers;
                    }
                }
                
                if (!empty($drivers)): 
                ?>
                <!-- Drivers Section -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="text-lg font-semibold mb-4">Drivers</h3>
                    <div class="space-y-4">
                        <?php foreach ($drivers as $driver): ?>
                        <div class="border-l-4 border-green-500 pl-4">
                            <p class="font-medium">
                                <?php echo htmlspecialchars($driver['first_name'] ?? ''); ?> 
                                <?php echo htmlspecialchars($driver['last_name'] ?? ''); ?>
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
                                <?php echo isset($lead->tcpa_compliant) && $lead->tcpa_compliant ? '✅ Yes' : '❌ No'; ?>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Opt-in Date</dt>
                            <dd class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($lead->opt_in_date ?? 'N/A'); ?></dd>
                        </div>
                        <?php if (!empty($lead->trusted_form_cert_url)): ?>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">TrustedForm</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                <a href="<?php echo htmlspecialchars($lead->trusted_form_cert_url); ?>" target="_blank" class="text-blue-600 hover:underline">
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
@endsection
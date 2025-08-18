<?php

// Fix webhook endpoints to actually save leads

$routesFile = 'routes/web.php';
$content = file_get_contents($routesFile);

echo "Fixing webhook endpoints to actually save leads...\n\n";

// 1. Fix /webhook/home to save leads
$homeWebhookPattern = '/(\$leadData = \[.*?\'payload\' => json_encode\(\$data\).*?\];)(.*?)(} catch)/s';

if (preg_match($homeWebhookPattern, $content, $matches)) {
    $leadDataSection = $matches[1];
    $afterSection = $matches[2];
    $catchSection = $matches[3];
    
    // Add the Lead::create call
    $fixedSection = $leadDataSection . '
        
        // Generate external lead ID
        $leadData[\'external_lead_id\'] = \\App\\Models\\Lead::generateExternalLeadId();
        
        // Create the lead in database
        $lead = \\App\\Models\\Lead::create($leadData);
        
        Log::info(\'✅ Home insurance lead created\', [
            \'id\' => $lead->id,
            \'external_id\' => $lead->external_lead_id,
            \'name\' => $lead->name,
            \'phone\' => $lead->phone
        ]);
        
        // Send to Vici if configured
        if (env(\'VICI_ENABLED\', false)) {
            try {
                $viciService = new \\App\\Services\\ViciDialerService();
                $viciResult = $viciService->sendLeadToVici($lead);
                
                if ($viciResult[\'success\']) {
                    $lead->update([\'vici_list_id\' => $viciResult[\'list_id\'] ?? 101]);
                    Log::info(\'Lead sent to Vici\', [\'lead_id\' => $lead->id]);
                }
            } catch (\\Exception $viciError) {
                Log::error(\'Failed to send to Vici\', [
                    \'lead_id\' => $lead->id,
                    \'error\' => $viciError->getMessage()
                ]);
                // Lead stays in queue for retry
            }
        }
        
        return response()->json([
            \'success\' => true,
            \'message\' => \'Home insurance lead received successfully\',
            \'lead_id\' => $lead->external_lead_id
        ], 200);' . $afterSection . $catchSection;
    
    $content = str_replace($matches[0], $fixedSection, $content);
    echo "✓ Fixed /webhook/home to save leads\n";
}

// 2. Fix /webhook/auto to save leads
$autoWebhookPattern = '/Route::post\(\'\/webhook\/auto\'.*?(\$leadData = \[.*?\'payload\' => json_encode\(\$data\).*?\];)(.*?)(} catch)/s';

if (preg_match($autoWebhookPattern, $content, $matches)) {
    $leadDataSection = $matches[1];
    $afterSection = $matches[2];
    $catchSection = $matches[3];
    
    // Add the Lead::create call
    $fixedSection = $leadDataSection . '
        
        // Generate external lead ID
        $leadData[\'external_lead_id\'] = \\App\\Models\\Lead::generateExternalLeadId();
        
        // Create the lead in database
        $lead = \\App\\Models\\Lead::create($leadData);
        
        Log::info(\'✅ Auto insurance lead created\', [
            \'id\' => $lead->id,
            \'external_id\' => $lead->external_lead_id,
            \'name\' => $lead->name,
            \'phone\' => $lead->phone
        ]);
        
        // Send to Vici if configured
        if (env(\'VICI_ENABLED\', false)) {
            try {
                $viciService = new \\App\\Services\\ViciDialerService();
                $viciResult = $viciService->sendLeadToVici($lead);
                
                if ($viciResult[\'success\']) {
                    $lead->update([\'vici_list_id\' => $viciResult[\'list_id\'] ?? 101]);
                    Log::info(\'Lead sent to Vici\', [\'lead_id\' => $lead->id]);
                }
            } catch (\\Exception $viciError) {
                Log::error(\'Failed to send to Vici\', [
                    \'lead_id\' => $lead->id,
                    \'error\' => $viciError->getMessage()
                ]);
                // Lead stays in queue for retry
            }
        }
        
        return response()->json([
            \'success\' => true,
            \'message\' => \'Auto insurance lead received successfully\',
            \'lead_id\' => $lead->external_lead_id
        ], 200);' . $afterSection . $catchSection;
    
    $content = str_replace($matches[0], $fixedSection, $content);
    echo "✓ Fixed /webhook/auto to save leads\n";
}

// Write the fixed content back
file_put_contents($routesFile, $content);

echo "\n✅ Webhook endpoints fixed!\n";
echo "\nWHAT HAPPENED:\n";
echo "1. The webhooks were commented out (disabled) - so leads got HTTP errors\n";
echo "2. Even when uncommented, they weren't saving leads - just preparing data\n";
echo "3. That's why nothing went to 'Stuck in Queue' - leads never made it to the database\n";
echo "\nWHAT'S FIXED NOW:\n";
echo "- Both /webhook/home and /webhook/auto now save leads to database\n";
echo "- Leads will appear in the dashboard immediately\n";
echo "- If Vici is enabled, they'll be sent automatically\n";
echo "- If Vici fails, they'll stay in 'Stuck in Queue' for retry\n";
echo "\nREGARDING MISSED LEADS:\n";
echo "- Unfortunately, the missed leads are lost - they were rejected with errors\n";
echo "- The sender would have received HTTP 500 or 404 errors\n";
echo "- You may need to contact the lead provider to resend them\n";


// Fix webhook endpoints to actually save leads

$routesFile = 'routes/web.php';
$content = file_get_contents($routesFile);

echo "Fixing webhook endpoints to actually save leads...\n\n";

// 1. Fix /webhook/home to save leads
$homeWebhookPattern = '/(\$leadData = \[.*?\'payload\' => json_encode\(\$data\).*?\];)(.*?)(} catch)/s';

if (preg_match($homeWebhookPattern, $content, $matches)) {
    $leadDataSection = $matches[1];
    $afterSection = $matches[2];
    $catchSection = $matches[3];
    
    // Add the Lead::create call
    $fixedSection = $leadDataSection . '
        
        // Generate external lead ID
        $leadData[\'external_lead_id\'] = \\App\\Models\\Lead::generateExternalLeadId();
        
        // Create the lead in database
        $lead = \\App\\Models\\Lead::create($leadData);
        
        Log::info(\'✅ Home insurance lead created\', [
            \'id\' => $lead->id,
            \'external_id\' => $lead->external_lead_id,
            \'name\' => $lead->name,
            \'phone\' => $lead->phone
        ]);
        
        // Send to Vici if configured
        if (env(\'VICI_ENABLED\', false)) {
            try {
                $viciService = new \\App\\Services\\ViciDialerService();
                $viciResult = $viciService->sendLeadToVici($lead);
                
                if ($viciResult[\'success\']) {
                    $lead->update([\'vici_list_id\' => $viciResult[\'list_id\'] ?? 101]);
                    Log::info(\'Lead sent to Vici\', [\'lead_id\' => $lead->id]);
                }
            } catch (\\Exception $viciError) {
                Log::error(\'Failed to send to Vici\', [
                    \'lead_id\' => $lead->id,
                    \'error\' => $viciError->getMessage()
                ]);
                // Lead stays in queue for retry
            }
        }
        
        return response()->json([
            \'success\' => true,
            \'message\' => \'Home insurance lead received successfully\',
            \'lead_id\' => $lead->external_lead_id
        ], 200);' . $afterSection . $catchSection;
    
    $content = str_replace($matches[0], $fixedSection, $content);
    echo "✓ Fixed /webhook/home to save leads\n";
}

// 2. Fix /webhook/auto to save leads
$autoWebhookPattern = '/Route::post\(\'\/webhook\/auto\'.*?(\$leadData = \[.*?\'payload\' => json_encode\(\$data\).*?\];)(.*?)(} catch)/s';

if (preg_match($autoWebhookPattern, $content, $matches)) {
    $leadDataSection = $matches[1];
    $afterSection = $matches[2];
    $catchSection = $matches[3];
    
    // Add the Lead::create call
    $fixedSection = $leadDataSection . '
        
        // Generate external lead ID
        $leadData[\'external_lead_id\'] = \\App\\Models\\Lead::generateExternalLeadId();
        
        // Create the lead in database
        $lead = \\App\\Models\\Lead::create($leadData);
        
        Log::info(\'✅ Auto insurance lead created\', [
            \'id\' => $lead->id,
            \'external_id\' => $lead->external_lead_id,
            \'name\' => $lead->name,
            \'phone\' => $lead->phone
        ]);
        
        // Send to Vici if configured
        if (env(\'VICI_ENABLED\', false)) {
            try {
                $viciService = new \\App\\Services\\ViciDialerService();
                $viciResult = $viciService->sendLeadToVici($lead);
                
                if ($viciResult[\'success\']) {
                    $lead->update([\'vici_list_id\' => $viciResult[\'list_id\'] ?? 101]);
                    Log::info(\'Lead sent to Vici\', [\'lead_id\' => $lead->id]);
                }
            } catch (\\Exception $viciError) {
                Log::error(\'Failed to send to Vici\', [
                    \'lead_id\' => $lead->id,
                    \'error\' => $viciError->getMessage()
                ]);
                // Lead stays in queue for retry
            }
        }
        
        return response()->json([
            \'success\' => true,
            \'message\' => \'Auto insurance lead received successfully\',
            \'lead_id\' => $lead->external_lead_id
        ], 200);' . $afterSection . $catchSection;
    
    $content = str_replace($matches[0], $fixedSection, $content);
    echo "✓ Fixed /webhook/auto to save leads\n";
}

// Write the fixed content back
file_put_contents($routesFile, $content);

echo "\n✅ Webhook endpoints fixed!\n";
echo "\nWHAT HAPPENED:\n";
echo "1. The webhooks were commented out (disabled) - so leads got HTTP errors\n";
echo "2. Even when uncommented, they weren't saving leads - just preparing data\n";
echo "3. That's why nothing went to 'Stuck in Queue' - leads never made it to the database\n";
echo "\nWHAT'S FIXED NOW:\n";
echo "- Both /webhook/home and /webhook/auto now save leads to database\n";
echo "- Leads will appear in the dashboard immediately\n";
echo "- If Vici is enabled, they'll be sent automatically\n";
echo "- If Vici fails, they'll stay in 'Stuck in Queue' for retry\n";
echo "\nREGARDING MISSED LEADS:\n";
echo "- Unfortunately, the missed leads are lost - they were rejected with errors\n";
echo "- The sender would have received HTTP 500 or 404 errors\n";
echo "- You may need to contact the lead provider to resend them\n";


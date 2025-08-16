<?php

echo "=== CREATING FAILSAFE PROTECTION SYSTEM ===\n\n";

// 1. Create a fallback webhook that ALWAYS works
$fallbackWebhook = '
// CRITICAL: Universal Fallback Webhook - NEVER DISABLE THIS
// This catches ALL webhook attempts and stores them, even if other routes fail
Route::any(\'/webhook/{type?}\', function ($type = \'unknown\') {
    $data = request()->all();
    
    // Store raw webhook data no matter what
    try {
        \DB::table(\'webhook_raw_logs\')->insert([
            \'endpoint\' => request()->path(),
            \'type\' => $type,
            \'method\' => request()->method(),
            \'headers\' => json_encode(request()->headers->all()),
            \'payload\' => json_encode($data),
            \'ip_address\' => request()->ip(),
            \'created_at\' => now(),
            \'processed\' => false
        ]);
    } catch (\Exception $e) {
        // Even if database fails, log to file
        $emergencyLog = storage_path(\'logs/emergency_webhooks.log\');
        file_put_contents($emergencyLog, json_encode([
            \'timestamp\' => now()->toIso8601String(),
            \'endpoint\' => request()->path(),
            \'data\' => $data,
            \'error\' => $e->getMessage()
        ]) . PHP_EOL, FILE_APPEND | LOCK_EX);
    }
    
    // Try to process as lead
    try {
        if (isset($data[\'contact\'][\'phone\'])) {
            $contact = $data[\'contact\'];
            $leadData = [
                \'name\' => trim(($contact[\'first_name\'] ?? \'\') . \' \' . ($contact[\'last_name\'] ?? \'\')),
                \'phone\' => preg_replace(\'/[^0-9]/\', \'\', $contact[\'phone\']),
                \'email\' => $contact[\'email\'] ?? null,
                \'state\' => $contact[\'state\'] ?? \'Unknown\',
                \'source\' => \'webhook-fallback\',
                \'type\' => $type === \'home\' ? \'home\' : ($type === \'auto\' ? \'auto\' : detectLeadType($data)),
                \'external_lead_id\' => \App\Models\Lead::generateExternalLeadId(),
                \'payload\' => json_encode($data),
                \'created_at\' => now(),
                \'updated_at\' => now()
            ];
            
            $lead = \App\Models\Lead::create($leadData);
            
            return response()->json([
                \'success\' => true,
                \'message\' => \'Lead received via fallback route\',
                \'lead_id\' => $lead->external_lead_id
            ], 200);
        }
    } catch (\Exception $e) {
        \Log::error(\'Fallback webhook processing failed\', [
            \'error\' => $e->getMessage(),
            \'data\' => $data
        ]);
    }
    
    // Always return success to prevent retries that could lose data
    return response()->json([
        \'success\' => true,
        \'message\' => \'Data received and logged for processing\'
    ], 200);
})->where(\'type\', \'.*\');';

// 2. Create webhook monitoring system
$monitoringSystem = '
// Webhook Health Check Endpoint
Route::get(\'/webhook/health\', function () {
    $checks = [];
    
    // Check if main webhooks exist
    $routes = Route::getRoutes();
    $webhookRoutes = [
        \'/webhook/home\' => false,
        \'/webhook/auto\' => false,
        \'/api-webhook\' => false,
        \'/webhook/{type?}\' => false // Fallback
    ];
    
    foreach ($routes as $route) {
        $uri = $route->uri();
        if (isset($webhookRoutes[$uri])) {
            $webhookRoutes[$uri] = true;
        }
    }
    
    // Check recent webhook activity
    $lastWebhook = \DB::table(\'webhook_raw_logs\')
        ->orderBy(\'created_at\', \'desc\')
        ->first();
    
    $lastLead = \App\Models\Lead::orderBy(\'created_at\', \'desc\')->first();
    
    // Check for unprocessed webhooks
    $unprocessedCount = \DB::table(\'webhook_raw_logs\')
        ->where(\'processed\', false)
        ->count();
    
    $status = [
        \'healthy\' => !in_array(false, $webhookRoutes),
        \'routes\' => $webhookRoutes,
        \'last_webhook_received\' => $lastWebhook ? $lastWebhook->created_at : \'Never\',
        \'last_lead_created\' => $lastLead ? $lastLead->created_at : \'Never\',
        \'unprocessed_webhooks\' => $unprocessedCount,
        \'timestamp\' => now()->toIso8601String()
    ];
    
    // Send alert if unhealthy
    if (!$status[\'healthy\'] || $unprocessedCount > 10) {
        \Log::critical(\'WEBHOOK SYSTEM UNHEALTHY\', $status);
        // Could also send email/SMS alert here
    }
    
    return response()->json($status, $status[\'healthy\'] ? 200 : 503);
});';

// 3. Create database migration for webhook logs
$migration = '<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWebhookRawLogsTable extends Migration
{
    public function up()
    {
        Schema::create(\'webhook_raw_logs\', function (Blueprint $table) {
            $table->id();
            $table->string(\'endpoint\');
            $table->string(\'type\')->nullable();
            $table->string(\'method\', 10);
            $table->json(\'headers\')->nullable();
            $table->json(\'payload\');
            $table->string(\'ip_address\')->nullable();
            $table->boolean(\'processed\')->default(false);
            $table->text(\'processing_error\')->nullable();
            $table->timestamps();
            
            $table->index(\'processed\');
            $table->index(\'created_at\');
            $table->index(\'endpoint\');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists(\'webhook_raw_logs\');
    }
}';

file_put_contents('database/migrations/' . date('Y_m_d_His') . '_create_webhook_raw_logs_table.php', $migration);
echo "✓ Created webhook_raw_logs migration\n";

// 4. Create recovery command
$recoveryCommand = '<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Lead;
use Illuminate\Support\Facades\DB;

class ProcessUnprocessedWebhooks extends Command
{
    protected $signature = \'webhooks:process-unprocessed\';
    protected $description = \'Process any unprocessed webhook logs and create leads\';
    
    public function handle()
    {
        $unprocessed = DB::table(\'webhook_raw_logs\')
            ->where(\'processed\', false)
            ->orderBy(\'created_at\', \'asc\')
            ->get();
        
        $this->info(\'Found \' . $unprocessed->count() . \' unprocessed webhooks\');
        
        $processed = 0;
        $failed = 0;
        
        foreach ($unprocessed as $log) {
            try {
                $data = json_decode($log->payload, true);
                
                if (isset($data[\'contact\'][\'phone\'])) {
                    $contact = $data[\'contact\'];
                    
                    // Check if lead already exists
                    $existingLead = Lead::where(\'phone\', preg_replace(\'/[^0-9]/\', \'\', $contact[\'phone\']))
                        ->where(\'created_at\', \'>=\', $log->created_at)
                        ->first();
                    
                    if (!$existingLead) {
                        $leadData = [
                            \'name\' => trim(($contact[\'first_name\'] ?? \'\') . \' \' . ($contact[\'last_name\'] ?? \'\')),
                            \'phone\' => preg_replace(\'/[^0-9]/\', \'\', $contact[\'phone\']),
                            \'email\' => $contact[\'email\'] ?? null,
                            \'state\' => $contact[\'state\'] ?? \'Unknown\',
                            \'source\' => \'webhook-recovery\',
                            \'type\' => str_contains($log->endpoint, \'home\') ? \'home\' : \'auto\',
                            \'external_lead_id\' => Lead::generateExternalLeadId(),
                            \'payload\' => $log->payload,
                            \'created_at\' => $log->created_at,
                            \'updated_at\' => now()
                        ];
                        
                        Lead::create($leadData);
                        $this->info(\'Created lead from webhook log #\' . $log->id);
                    }
                    
                    DB::table(\'webhook_raw_logs\')
                        ->where(\'id\', $log->id)
                        ->update([\'processed\' => true]);
                    
                    $processed++;
                } else {
                    $this->warn(\'No contact phone in webhook log #\' . $log->id);
                    $failed++;
                }
            } catch (\Exception $e) {
                $this->error(\'Failed to process webhook log #\' . $log->id . \': \' . $e->getMessage());
                
                DB::table(\'webhook_raw_logs\')
                    ->where(\'id\', $log->id)
                    ->update([\'processing_error\' => $e->getMessage()]);
                
                $failed++;
            }
        }
        
        $this->info(\'Processed: \' . $processed . \', Failed: \' . $failed);
    }
}';

file_put_contents('app/Console/Commands/ProcessUnprocessedWebhooks.php', $recoveryCommand);
echo "✓ Created webhook recovery command\n";

// 5. Create monitoring script
$monitoringScript = '#!/bin/bash

# Webhook Monitoring Script - Run via cron every 5 minutes

# Check webhook health
HEALTH_CHECK=$(curl -s https://quotingfast-brain-ohio.onrender.com/webhook/health)
HEALTHY=$(echo $HEALTH_CHECK | jq -r \'.healthy\')

if [ "$HEALTHY" != "true" ]; then
    echo "ALERT: Webhook system unhealthy!"
    echo $HEALTH_CHECK
    
    # Send alert (configure your alert method)
    # Example: Send email
    # echo "Webhook system is unhealthy: $HEALTH_CHECK" | mail -s "Brain Webhook Alert" admin@quotingfast.com
fi

# Check for recent leads
LAST_LEAD=$(echo $HEALTH_CHECK | jq -r \'.last_lead_created\')
LAST_WEBHOOK=$(echo $HEALTH_CHECK | jq -r \'.last_webhook_received\')

echo "Last webhook: $LAST_WEBHOOK"
echo "Last lead: $LAST_LEAD"

# Process any unprocessed webhooks
cd /path/to/brain
php artisan webhooks:process-unprocessed';

file_put_contents('monitor_webhooks.sh', $monitoringScript);
chmod('monitor_webhooks.sh', 0755);
echo "✓ Created monitoring script\n";

echo "\n=== PROTECTION SYSTEM COMPONENTS ===\n\n";

echo "1. **FALLBACK WEBHOOK ROUTE**\n";
echo "   - Catches ANY webhook attempt to /webhook/*\n";
echo "   - Stores raw data in webhook_raw_logs table\n";
echo "   - Falls back to emergency file logging if DB fails\n";
echo "   - Always returns 200 OK to prevent data loss\n\n";

echo "2. **RAW WEBHOOK LOGGING**\n";
echo "   - Every webhook hit is logged with full payload\n";
echo "   - Can be reprocessed later if main processing fails\n";
echo "   - Stores headers, IP, timestamp for debugging\n\n";

echo "3. **HEALTH CHECK ENDPOINT**\n";
echo "   - /webhook/health shows system status\n";
echo "   - Checks if routes are active\n";
echo "   - Shows unprocessed webhook count\n";
echo "   - Returns 503 if unhealthy\n\n";

echo "4. **RECOVERY COMMAND**\n";
echo "   - php artisan webhooks:process-unprocessed\n";
echo "   - Processes any webhooks that failed\n";
echo "   - Prevents duplicate lead creation\n";
echo "   - Can be run manually or via cron\n\n";

echo "5. **MONITORING SCRIPT**\n";
echo "   - Run monitor_webhooks.sh via cron every 5 minutes\n";
echo "   - Checks webhook health\n";
echo "   - Processes unprocessed webhooks\n";
echo "   - Can send alerts if system is unhealthy\n\n";

echo "=== SETUP INSTRUCTIONS ===\n\n";
echo "1. Run migrations: php artisan migrate\n";
echo "2. Add fallback route to routes/web.php (at the END of file)\n";
echo "3. Add health check route to routes/web.php\n";
echo "4. Set up cron job: */5 * * * * /path/to/brain/monitor_webhooks.sh\n";
echo "5. Configure alerts in monitor_webhooks.sh\n\n";

echo "This system ensures:\n";
echo "✓ Webhooks are NEVER lost (stored raw even if processing fails)\n";
echo "✓ System self-monitors and alerts on issues\n";
echo "✓ Failed webhooks can be reprocessed\n";
echo "✓ Multiple layers of fallback protection\n";

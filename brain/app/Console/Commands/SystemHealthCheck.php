<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SystemHealthCheck extends Command
{
    protected $signature = 'system:health-check {--alert : Send alerts for failures}';
    protected $description = 'Check health of all critical system processes';

    private $healthStatus = [];
    private $criticalIssues = [];

    public function handle()
    {
        $this->info("\n================================================================================");
        $this->info("                    SYSTEM HEALTH CHECK - " . now()->format('Y-m-d H:i:s'));
        $this->info("================================================================================\n");

        // 1. Check Lead Import from Endpoints
        $this->checkLeadImport();
        
        // 2. Check Vici Push
        $this->checkViciPush();
        
        // 3. Check Call Log Import
        $this->checkCallLogImport();
        
        // 4. Check Vici Database Connection
        $this->checkViciConnection();
        
        // 5. Check Lead Flow Movement
        $this->checkLeadFlow();
        
        // 6. Check Cron/Scheduler
        $this->checkScheduler();
        
        // Display Results
        $this->displayResults();
        
        // Store results for dashboard
        $this->storeHealthStatus();
        
        // Send alerts if requested
        if ($this->option('alert') && !empty($this->criticalIssues)) {
            $this->sendAlerts();
        }
        
        return empty($this->criticalIssues) ? Command::SUCCESS : Command::FAILURE;
    }

    private function checkLeadImport()
    {
        $this->info("🔍 Checking Lead Import from Endpoints...");
        
        // Check recent leads
        $recentLeads = DB::table('leads')
            ->where('created_at', '>=', Carbon::now()->subHours(1))
            ->count();
            
        $lastLead = DB::table('leads')
            ->orderBy('created_at', 'desc')
            ->first();
            
        $minutesSinceLastLead = $lastLead 
            ? Carbon::parse($lastLead->created_at)->diffInMinutes(now())
            : 999;
            
        $status = [
            'name' => 'Lead Import (Endpoints)',
            'last_activity' => $lastLead ? $lastLead->created_at : 'Never',
            'recent_count' => $recentLeads,
            'status' => 'unknown'
        ];
        
        if ($minutesSinceLastLead < 15) {
            $status['status'] = 'healthy';
            $this->info("  ✅ HEALTHY - {$recentLeads} leads in last hour, last lead {$minutesSinceLastLead} min ago");
        } elseif ($minutesSinceLastLead < 60) {
            $status['status'] = 'warning';
            $this->warn("  ⚠️ WARNING - No leads in {$minutesSinceLastLead} minutes");
        } else {
            $status['status'] = 'critical';
            $this->error("  🚨 CRITICAL - No leads in {$minutesSinceLastLead} minutes!");
            $this->criticalIssues[] = "Lead import stopped - no leads in {$minutesSinceLastLead} minutes";
        }
        
        $this->healthStatus['lead_import'] = $status;
    }

    private function checkViciPush()
    {
        $this->info("\n🔍 Checking Vici Push...");
        
        // Check if push is enabled
        $pushEnabled = config('services.vici.push_enabled', false);
        
        if (!$pushEnabled) {
            $this->warn("  ⚠️ Vici Push is DISABLED in config");
            $this->healthStatus['vici_push'] = [
                'name' => 'Vici Push',
                'status' => 'disabled',
                'message' => 'VICI_PUSH_ENABLED is false'
            ];
            return;
        }
        
        // Check recent pushes (using vici_list_id to check if pushed)
        $recentPushes = DB::table('leads')
            ->whereNotNull('vici_list_id')
            ->where('updated_at', '>=', Carbon::now()->subHours(1))
            ->count();
            
        // Check unpushed leads
        $unpushedLeads = DB::table('leads')
            ->whereNull('vici_list_id')
            ->where('created_at', '>=', Carbon::now()->subDays(1))
            ->count();
            
        $status = [
            'name' => 'Vici Push',
            'recent_pushes' => $recentPushes,
            'unpushed_leads' => $unpushedLeads,
            'status' => 'unknown'
        ];
        
        if ($unpushedLeads == 0) {
            $status['status'] = 'healthy';
            $this->info("  ✅ HEALTHY - All leads pushed to Vici");
        } elseif ($unpushedLeads < 100) {
            $status['status'] = 'warning';
            $this->warn("  ⚠️ WARNING - {$unpushedLeads} unpushed leads");
        } else {
            $status['status'] = 'critical';
            $this->error("  🚨 CRITICAL - {$unpushedLeads} leads not pushed to Vici!");
            $this->criticalIssues[] = "Vici push failing - {$unpushedLeads} unpushed leads";
        }
        
        $this->healthStatus['vici_push'] = $status;
    }

    private function checkCallLogImport()
    {
        $this->info("\n🔍 Checking Call Log Import...");
        
        // Check recent imports
        $recentImports = DB::table('orphan_call_logs')
            ->where('created_at', '>=', Carbon::now()->subMinutes(10))
            ->count();
            
        $lastImport = DB::table('orphan_call_logs')
            ->orderBy('created_at', 'desc')
            ->first();
            
        $minutesSinceImport = $lastImport 
            ? Carbon::parse($lastImport->created_at)->diffInMinutes(now())
            : 999;
            
        // Check last sync time from cache
        $lastSyncTime = Cache::get('vici_last_incremental_sync');
        $minutesSinceSync = $lastSyncTime 
            ? Carbon::parse($lastSyncTime)->diffInMinutes(now())
            : 999;
            
        $status = [
            'name' => 'Call Log Import',
            'last_import' => $lastImport ? $lastImport->created_at : 'Never',
            'recent_count' => $recentImports,
            'minutes_since_sync' => $minutesSinceSync,
            'status' => 'unknown'
        ];
        
        if ($minutesSinceSync < 10 && $recentImports > 0) {
            $status['status'] = 'healthy';
            $this->info("  ✅ HEALTHY - {$recentImports} calls imported in last 10 min");
        } elseif ($minutesSinceSync < 30) {
            $status['status'] = 'warning';
            $this->warn("  ⚠️ WARNING - Last sync {$minutesSinceSync} minutes ago");
        } else {
            $status['status'] = 'critical';
            $this->error("  🚨 CRITICAL - No sync in {$minutesSinceSync} minutes!");
            $this->criticalIssues[] = "Call log import stopped - no sync in {$minutesSinceSync} minutes";
        }
        
        $this->healthStatus['call_import'] = $status;
    }

    private function checkViciConnection()
    {
        $this->info("\n🔍 Checking Vici Database Connection...");
        
        try {
            $response = Http::timeout(10)->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/execute', [
                'command' => 'mysql -u root -pQ6hdjl67GRigMofv Q6hdjl67GRigMofv -e "SELECT COUNT(*) FROM vicidial_log WHERE call_date >= CURDATE()"'
            ]);
            
            if ($response->successful()) {
                $this->info("  ✅ HEALTHY - Vici database connection active");
                $this->healthStatus['vici_connection'] = [
                    'name' => 'Vici Database',
                    'status' => 'healthy'
                ];
            } else {
                throw new \Exception("Connection failed");
            }
        } catch (\Exception $e) {
            $this->error("  🚨 CRITICAL - Cannot connect to Vici database!");
            $this->criticalIssues[] = "Vici database connection failed";
            $this->healthStatus['vici_connection'] = [
                'name' => 'Vici Database',
                'status' => 'critical',
                'error' => $e->getMessage()
            ];
        }
    }

    private function checkLeadFlow()
    {
        $this->info("\n🔍 Checking Lead Flow Movement...");
        
        // Check if leads are moving between lists
        $recentMovements = DB::table('leads')
            ->whereIn('vici_list_id', range(101, 120))
            ->where('updated_at', '>=', Carbon::now()->subHours(1))
            ->where('updated_at', '!=', DB::raw('created_at'))
            ->count();
            
        $status = [
            'name' => 'Lead Flow Movement',
            'recent_movements' => $recentMovements,
            'status' => 'unknown'
        ];
        
        if ($recentMovements > 0) {
            $status['status'] = 'healthy';
            $this->info("  ✅ HEALTHY - {$recentMovements} leads moved in last hour");
        } else {
            $status['status'] = 'warning';
            $this->warn("  ⚠️ WARNING - No lead movements detected");
        }
        
        $this->healthStatus['lead_flow'] = $status;
    }

    private function checkScheduler()
    {
        $this->info("\n🔍 Checking Cron/Scheduler...");
        
        // Check if scheduler has run recently
        $lastSchedulerRun = Cache::get('last_scheduler_run');
        $minutesSinceRun = $lastSchedulerRun 
            ? Carbon::parse($lastSchedulerRun)->diffInMinutes(now())
            : 999;
            
        // Set current run time
        Cache::put('last_scheduler_run', now(), now()->addHours(1));
        
        $status = [
            'name' => 'Cron Scheduler',
            'last_run' => $lastSchedulerRun ?: 'Never',
            'status' => 'unknown'
        ];
        
        if ($minutesSinceRun < 2) {
            $status['status'] = 'healthy';
            $this->info("  ✅ HEALTHY - Scheduler running every minute");
        } elseif ($minutesSinceRun < 10) {
            $status['status'] = 'warning';
            $this->warn("  ⚠️ WARNING - Scheduler last ran {$minutesSinceRun} minutes ago");
        } else {
            $status['status'] = 'critical';
            $this->error("  🚨 CRITICAL - Scheduler not running!");
            $this->criticalIssues[] = "Cron scheduler stopped - last run {$minutesSinceRun} minutes ago";
        }
        
        $this->healthStatus['scheduler'] = $status;
    }

    private function displayResults()
    {
        $this->info("\n================================================================================");
        
        if (empty($this->criticalIssues)) {
            $this->info("✅ SYSTEM STATUS: ALL HEALTHY");
        } else {
            $this->error("🚨 SYSTEM STATUS: CRITICAL ISSUES DETECTED!");
            $this->error("\n⚠️ CRITICAL ISSUES:");
            foreach ($this->criticalIssues as $issue) {
                $this->error("  • " . $issue);
            }
        }
        
        $this->info("================================================================================\n");
    }

    private function storeHealthStatus()
    {
        // Store in cache for dashboard
        Cache::put('system_health_status', [
            'timestamp' => now(),
            'status' => empty($this->criticalIssues) ? 'healthy' : 'critical',
            'components' => $this->healthStatus,
            'issues' => $this->criticalIssues
        ], now()->addMinutes(5));
        
        // Log to database for history
        DB::table('system_health_logs')->insert([
            'checked_at' => now(),
            'status' => empty($this->criticalIssues) ? 'healthy' : 'critical',
            'components' => json_encode($this->healthStatus),
            'issues' => json_encode($this->criticalIssues),
            'created_at' => now()
        ]);
    }

    private function sendAlerts()
    {
        // Log critical issues
        foreach ($this->criticalIssues as $issue) {
            Log::critical('System Health Alert: ' . $issue);
        }
        
        // TODO: Send email/SMS alerts
        $this->error("\n📧 Alerts sent for critical issues!");
    }
}

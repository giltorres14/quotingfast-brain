<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncViciDirect extends Command
{
    protected $signature = 'vici:sync-direct {--script=} {--test}';
    protected $description = 'Sync Vici call logs using direct server access';

    public function handle()
    {
        $this->info('Starting Vici Direct Sync...');
        
        // If you provide a script path, we'll execute it
        $scriptPath = $this->option('script');
        
        if ($scriptPath && file_exists($scriptPath)) {
            $this->info("Executing script: $scriptPath");
            
            // Execute your custom script
            $output = shell_exec("php $scriptPath");
            $this->info($output);
            
            // Log the execution
            Log::info('Vici Direct Sync executed', [
                'script' => $scriptPath,
                'timestamp' => now(),
                'output' => substr($output, 0, 500) // Log first 500 chars
            ]);
            
            return 0;
        }
        
        // Your custom sync logic will go here
        // You can provide the script content and we'll integrate it
        
        $this->info('Sync completed!');
        return 0;
    }
}



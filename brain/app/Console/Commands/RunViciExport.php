<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RunViciExport extends Command
{
    protected $signature = 'vici:run-export {--db-name=Colh42mUsWs40znH : Vici database name}';
    protected $description = 'Run Vici export script via proxy and process CSV';

    public function handle()
    {
        $dbName = $this->option('db-name');
        
        $this->info('Running Vici export script...');
        
        try {
            // Call our proxy endpoint
            $response = Http::timeout(120)
                ->withHeaders([
                    'X-API-Key' => env('VICI_PROXY_KEY', 'your-secret-key-here')
                ])
                ->post('https://quotingfast-brain-ohio.onrender.com/vici-proxy/run-export', [
                    'db_name' => $dbName
                ]);
            
            if ($response->successful()) {
                $data = $response->json();
                
                if ($data['success']) {
                    $this->info('Export successful!');
                    $this->info('Filename: ' . ($data['filename'] ?? 'N/A'));
                    
                    if (isset($data['process_output'])) {
                        $this->info('Processing output:');
                        $this->line($data['process_output']);
                    }
                    
                    Log::info('Vici export completed', $data);
                } else {
                    $this->error('Export failed: ' . ($data['message'] ?? 'Unknown error'));
                    Log::warning('Vici export failed', $data);
                }
            } else {
                $this->error('HTTP request failed: ' . $response->status());
                Log::error('Vici export HTTP failed', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
            }
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error('Exception: ' . $e->getMessage());
            Log::error('Vici export exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }
}





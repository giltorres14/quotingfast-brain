<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ViciCallMetrics;
use App\Models\Lead;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessViciCsv extends Command
{
    protected $signature = 'vici:process-csv {file : Path to CSV file} {--test : Run in test mode}';
    protected $description = 'Process Vici call log CSV file and import to database';

    public function handle()
    {
        $file = $this->argument('file');
        $isTest = $this->option('test');
        
        if (!file_exists($file)) {
            $this->error("File not found: {$file}");
            return 1;
        }

        $this->info('Processing Vici CSV file: ' . $file);
        
        $handle = fopen($file, 'r');
        if (!$handle) {
            $this->error("Cannot open file: {$file}");
            return 1;
        }

        $header = null;
        $imported = 0;
        $skipped = 0;
        $orphaned = 0;
        $lineNum = 0;

        while (($data = fgetcsv($handle)) !== false) {
            $lineNum++;
            
            // First line is header
            if ($lineNum === 1) {
                $header = $data;
                continue;
            }

            // Map data to associative array
            $row = array_combine($header, $data);
            
            if (!$row) {
                $this->warn("Failed to parse line {$lineNum}");
                $skipped++;
                continue;
            }

            try {
                // Clean phone number
                $phoneNumber = preg_replace('/\D/', '', $row['phone_number']);
                
                // Find matching lead
                $lead = Lead::where('phone', $phoneNumber)
                    ->orWhere('phone', 'LIKE', '%' . substr($phoneNumber, -10))
                    ->first();

                if (!$lead && $row['lead_id']) {
                    // Try to find by Vici lead_id
                    $lead = Lead::where('vici_lead_id', $row['lead_id'])->first();
                }

                if (!$lead) {
                    // Store as orphan call for later matching
                    $this->storeOrphanCall($row);
                    $orphaned++;
                    if ($orphaned <= 5) {
                        $this->warn("No lead found for phone: {$phoneNumber}, stored as orphan");
                    }
                    continue;
                }

                // Create or update ViciCallMetrics
                $metrics = ViciCallMetrics::updateOrCreate(
                    [
                        'lead_id' => $lead->id,
                        'call_date' => Carbon::parse($row['call_date'])
                    ],
                    [
                        'vici_lead_id' => $row['lead_id'],
                        'list_id' => $row['list_id'],
                        'phone_number' => $phoneNumber,
                        'campaign_id' => $row['campaign_id'],
                        'status' => $row['status'],
                        'length_in_sec' => (int)$row['length_in_sec'],
                        'server_ip' => $row['server_ip'],
                        'extension' => $row['extension'],
                        'channel' => $row['channel'],
                        'outbound_cid' => $row['outbound_cid'],
                        'sip_hangup_cause' => $row['sip_hangup_cause'],
                        'sip_hangup_reason' => $row['sip_hangup_reason'],
                        'first_call_time' => Carbon::parse($row['call_date']),
                        'last_call_time' => Carbon::parse($row['call_date']),
                        'total_calls' => 1,
                        'connected' => $row['status'] === 'CONNECT' ? 1 : 0,
                        'not_interested' => $row['status'] === 'NI' ? 1 : 0,
                        'voicemail' => in_array($row['status'], ['VM', 'VOICEMAIL']) ? 1 : 0,
                        'no_answer' => $row['status'] === 'NA' ? 1 : 0,
                        'avg_talk_time' => $row['status'] === 'CONNECT' ? (int)$row['length_in_sec'] : 0,
                        'total_talk_time' => $row['status'] === 'CONNECT' ? (int)$row['length_in_sec'] : 0,
                    ]
                );

                $imported++;
                
                if ($imported % 100 === 0) {
                    $this->info("Processed {$imported} call logs...");
                }

            } catch (\Exception $e) {
                $this->error("Error processing line {$lineNum}: " . $e->getMessage());
                $skipped++;
            }
        }

        fclose($handle);

        $this->info("Import complete!");
        $this->info("- Imported: {$imported}");
        $this->info("- Orphaned: {$orphaned}");  
        $this->info("- Skipped: {$skipped}");

        Log::info('Vici CSV processed', [
            'file' => $file,
            'imported' => $imported,
            'orphaned' => $orphaned,
            'skipped' => $skipped
        ]);

        return 0;
    }

    private function storeOrphanCall($row)
    {
        // Store in orphan_call_logs table for later matching
        \DB::table('orphan_call_logs')->insert([
            'call_date' => Carbon::parse($row['call_date']),
            'vici_lead_id' => $row['lead_id'],
            'list_id' => $row['list_id'],
            'phone_number' => preg_replace('/\D/', '', $row['phone_number']),
            'campaign_id' => $row['campaign_id'],
            'status' => $row['status'],
            'length_in_sec' => (int)$row['length_in_sec'],
            'server_ip' => $row['server_ip'],
            'extension' => $row['extension'],
            'channel' => $row['channel'],
            'outbound_cid' => $row['outbound_cid'],
            'sip_hangup_cause' => $row['sip_hangup_cause'],
            'sip_hangup_reason' => $row['sip_hangup_reason'],
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }
}


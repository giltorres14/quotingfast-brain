<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Lead;
use App\Models\ViciCallMetrics;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ViciCallWebhookController extends Controller
{
    /**
     * Handle incoming Vici call status webhook
     * This can be called by Vici's DNC (Dial Next Call) or URL integration
     */
    public function handleCallStatus(Request $request)
    {
        // Log incoming webhook data
        Log::info('📞 Vici Call Status Webhook Received', [
            'data' => $request->all(),
            'ip' => $request->ip()
        ]);
        
        try {
            // Extract call data from request
            $callData = $this->extractCallData($request);
            
            // Find the lead
            $lead = $this->findLead($callData);
            
            if (!$lead) {
                Log::warning('Lead not found for Vici call webhook', $callData);
                return response()->json([
                    'success' => false,
                    'message' => 'Lead not found'
                ], 404);
            }
            
            // Update or create call metrics
            $metrics = $this->updateCallMetrics($lead, $callData);
            
            // Update lead status based on disposition
            $this->updateLeadStatus($lead, $callData);
            
            // Handle special dispositions (transfers, sales, etc.)
            $this->handleSpecialDispositions($lead, $metrics, $callData);
            
            Log::info('✅ Vici call webhook processed successfully', [
                'lead_id' => $lead->id,
                'metrics_id' => $metrics->id
            ]);
            
            return response()->json([
                'success' => true,
                'lead_id' => $lead->id,
                'metrics_id' => $metrics->id
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error processing Vici call webhook', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Handle agent disposition webhook
     * Called when agent sets disposition in Vici
     */
    public function handleDisposition(Request $request)
    {
        Log::info('📋 Vici Disposition Webhook Received', [
            'data' => $request->all()
        ]);
        
        try {
            $data = $request->all();
            
            // Find lead by vendor_lead_code or phone
            $lead = null;
            
            if (!empty($data['vendor_lead_code'])) {
                $lead = Lead::where('external_lead_id', $data['vendor_lead_code'])->first();
            }
            
            if (!$lead && !empty($data['phone_number'])) {
                $phone = preg_replace('/[^0-9]/', '', $data['phone_number']);
                $lead = Lead::where('phone', $phone)
                    ->orWhere('phone', '1' . $phone)
                    ->first();
            }
            
            if (!$lead) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lead not found'
                ], 404);
            }
            
            // Update or create call metrics
            $metrics = ViciCallMetrics::updateOrCreate(
                [
                    'lead_id' => $lead->id,
                    'vici_lead_id' => $data['lead_id'] ?? null
                ],
                [
                    'disposition' => $data['status'] ?? $data['dispo'] ?? null,
                    'agent_id' => $data['user'] ?? $data['agent'] ?? null,
                    'campaign_id' => $data['campaign_id'] ?? null,
                    'call_status' => 'DISPOSED',
                    'last_call_time' => now(),
                    'notes' => $data['comments'] ?? null
                ]
            );
            
            // Update lead status
            $dispositionMap = [
                'SALE' => 'SOLD',
                'DNC' => 'DO_NOT_CALL',
                'NI' => 'NOT_INTERESTED',
                'B' => 'BUSY',
                'A' => 'ANSWERING_MACHINE',
                'N' => 'NO_ANSWER',
                'CALLBK' => 'CALLBACK',
                'XFER' => 'TRANSFERRED'
            ];
            
            $leadStatus = $dispositionMap[$data['status']] ?? 'CONTACTED';
            $lead->update(['status' => $leadStatus]);
            
            // Add to call history
            $metrics->addCallAttempt([
                'disposition' => $data['status'],
                'agent' => $data['user'] ?? null,
                'comments' => $data['comments'] ?? null
            ]);
            
            return response()->json([
                'success' => true,
                'lead_id' => $lead->id,
                'status' => $leadStatus
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error processing disposition webhook', [
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Handle real-time call events
     * Can be integrated with Vici's real-time monitoring
     */
    public function handleRealTimeEvent(Request $request)
    {
        $event = $request->input('event');
        $data = $request->all();
        
        Log::info("🔴 Vici Real-Time Event: $event", $data);
        
        switch ($event) {
            case 'call_start':
                return $this->handleCallStart($data);
                
            case 'call_connect':
                return $this->handleCallConnect($data);
                
            case 'call_end':
                return $this->handleCallEnd($data);
                
            case 'transfer_init':
                return $this->handleTransferInit($data);
                
            case 'transfer_complete':
                return $this->handleTransferComplete($data);
                
            default:
                Log::warning("Unknown Vici event type: $event");
                return response()->json(['success' => true]);
        }
    }
    
    /**
     * Extract call data from various Vici webhook formats
     */
    private function extractCallData(Request $request)
    {
        $data = $request->all();
        
        // Normalize field names from different Vici webhook formats
        return [
            'lead_id' => $data['lead_id'] ?? $data['vicidial_id'] ?? null,
            'vendor_lead_code' => $data['vendor_lead_code'] ?? $data['vendor_id'] ?? null,
            'phone_number' => $data['phone_number'] ?? $data['phone'] ?? null,
            'list_id' => $data['list_id'] ?? null,
            'campaign_id' => $data['campaign_id'] ?? $data['campaign'] ?? null,
            'agent_id' => $data['user'] ?? $data['agent'] ?? $data['agent_user'] ?? null,
            'status' => $data['status'] ?? $data['dispo'] ?? null,
            'call_time' => $data['call_time'] ?? $data['length_in_sec'] ?? null,
            'talk_time' => $data['talk_time'] ?? $data['talk_sec'] ?? null,
            'wait_time' => $data['wait_time'] ?? $data['queue_seconds'] ?? null,
            'uniqueid' => $data['uniqueid'] ?? null,
            'comments' => $data['comments'] ?? null
        ];
    }
    
    /**
     * Find lead from call data
     */
    private function findLead($callData)
    {
        // Try vendor_lead_code first (Brain's external_lead_id)
        if (!empty($callData['vendor_lead_code'])) {
            $lead = Lead::where('external_lead_id', $callData['vendor_lead_code'])->first();
            if ($lead) return $lead;
        }
        
        // Try phone number
        if (!empty($callData['phone_number'])) {
            $phone = preg_replace('/[^0-9]/', '', $callData['phone_number']);
            if (strlen($phone) === 11 && substr($phone, 0, 1) === '1') {
                $phone = substr($phone, 1);
            }
            
            $lead = Lead::where('phone', $phone)
                ->orWhere('phone', '1' . $phone)
                ->orderBy('created_at', 'desc')
                ->first();
        }
        
        return $lead;
    }
    
    /**
     * Update or create call metrics
     */
    private function updateCallMetrics($lead, $callData)
    {
        $metrics = ViciCallMetrics::firstOrNew([
            'lead_id' => $lead->id,
            'vici_lead_id' => $callData['lead_id']
        ]);
        
        // Update metrics
        $metrics->fill([
            'campaign_id' => $callData['campaign_id'],
            'list_id' => $callData['list_id'],
            'agent_id' => $callData['agent_id'],
            'phone_number' => $callData['phone_number'],
            'call_status' => $callData['status'],
            'disposition' => $callData['status'],
            'last_call_time' => now(),
            'vici_payload' => $callData
        ]);
        
        // Update call counts and times
        if (!$metrics->exists) {
            $metrics->first_call_time = now();
            $metrics->call_attempts = 1;
        } else {
            $metrics->call_attempts++;
        }
        
        // Update talk time if provided
        if (!empty($callData['talk_time'])) {
            $metrics->talk_time = (int) $callData['talk_time'];
            $metrics->connected_time = now();
            $metrics->markConnected($callData['talk_time']);
        }
        
        $metrics->save();
        
        // Add to call history
        $metrics->addCallAttempt([
            'status' => $callData['status'],
            'agent' => $callData['agent_id'],
            'talk_time' => $callData['talk_time'] ?? 0,
            'wait_time' => $callData['wait_time'] ?? 0,
            'uniqueid' => $callData['uniqueid'] ?? null
        ]);
        
        return $metrics;
    }
    
    /**
     * Update lead status based on disposition
     */
    private function updateLeadStatus($lead, $callData)
    {
        $status = $callData['status'] ?? '';
        
        // Map Vici dispositions to lead statuses
        $statusMap = [
            'SALE' => 'SOLD',
            'XFER' => 'TRANSFERRED',
            'DNC' => 'DO_NOT_CALL',
            'NI' => 'NOT_INTERESTED',
            'CALLBK' => 'CALLBACK_SCHEDULED',
            'A' => 'VOICEMAIL',
            'B' => 'BUSY',
            'N' => 'NO_ANSWER',
            'DROP' => 'DROPPED',
            'INCALL' => 'IN_CALL',
            'QUEUE' => 'IN_QUEUE'
        ];
        
        if (isset($statusMap[$status])) {
            $lead->update(['status' => $statusMap[$status]]);
        } elseif (!empty($status)) {
            $lead->update(['status' => 'CONTACTED']);
        }
    }
    
    /**
     * Handle special dispositions
     */
    private function handleSpecialDispositions($lead, $metrics, $callData)
    {
        $status = $callData['status'] ?? '';
        
        switch ($status) {
            case 'SALE':
                // Mark as qualified and ready for buyer
                $lead->update([
                    'qualified' => true,
                    'qualified_at' => now(),
                    'qualified_by' => $callData['agent_id']
                ]);
                break;
                
            case 'XFER':
                // Handle transfer
                $metrics->requestTransfer('buyer');
                break;
                
            case 'DNC':
                // Mark as do not call
                $lead->update([
                    'do_not_call' => true,
                    'dnc_date' => now()
                ]);
                break;
                
            case 'CALLBK':
                // Schedule callback
                // You can integrate with scheduling system here
                break;
        }
    }
    
    /**
     * Handle call start event
     */
    private function handleCallStart($data)
    {
        Log::info('📞 Call Started', $data);
        
        // Find lead and create initial metrics
        $lead = $this->findLead($data);
        if ($lead) {
            ViciCallMetrics::updateOrCreate(
                ['lead_id' => $lead->id],
                [
                    'call_status' => 'CALLING',
                    'agent_id' => $data['agent_id'] ?? null,
                    'campaign_id' => $data['campaign_id'] ?? null
                ]
            );
        }
        
        return response()->json(['success' => true]);
    }
    
    /**
     * Handle call connect event
     */
    private function handleCallConnect($data)
    {
        Log::info('✅ Call Connected', $data);
        
        $lead = $this->findLead($data);
        if ($lead) {
            $metrics = ViciCallMetrics::where('lead_id', $lead->id)->first();
            if ($metrics) {
                $metrics->markConnected();
                $lead->update(['status' => 'IN_CALL']);
            }
        }
        
        return response()->json(['success' => true]);
    }
    
    /**
     * Handle call end event
     */
    private function handleCallEnd($data)
    {
        Log::info('📴 Call Ended', $data);
        
        $lead = $this->findLead($data);
        if ($lead) {
            $metrics = ViciCallMetrics::where('lead_id', $lead->id)->first();
            if ($metrics) {
                $metrics->update([
                    'hangup_time' => now(),
                    'call_duration' => $data['duration'] ?? null
                ]);
            }
        }
        
        return response()->json(['success' => true]);
    }
    
    /**
     * Handle transfer init event
     */
    private function handleTransferInit($data)
    {
        Log::info('🔄 Transfer Initiated', $data);
        
        $lead = $this->findLead($data);
        if ($lead) {
            $metrics = ViciCallMetrics::where('lead_id', $lead->id)->first();
            if ($metrics) {
                $metrics->requestTransfer($data['destination'] ?? 'unknown');
            }
        }
        
        return response()->json(['success' => true]);
    }
    
    /**
     * Handle transfer complete event
     */
    private function handleTransferComplete($data)
    {
        Log::info('✅ Transfer Complete', $data);
        
        $lead = $this->findLead($data);
        if ($lead) {
            $metrics = ViciCallMetrics::where('lead_id', $lead->id)->first();
            if ($metrics) {
                $metrics->update([
                    'transfer_status' => 'COMPLETED',
                    'transfer_destination' => $data['destination'] ?? null
                ]);
            }
            
            $lead->update(['status' => 'TRANSFERRED']);
        }
        
        return response()->json(['success' => true]);
    }
}



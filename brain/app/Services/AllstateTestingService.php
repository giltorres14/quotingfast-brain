<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\AllstateTestLog;
use App\Services\AutoQualificationService;
use App\Services\AllstateCallTransferService;
use Illuminate\Support\Facades\Log;

class AllstateTestingService
{
    protected $autoQualificationService;
    protected $allstateService;

    public function __construct()
    {
        $this->autoQualificationService = new AutoQualificationService();
        $this->allstateService = new AllstateCallTransferService();
    }

    /**
     * Process a lead for Allstate testing (bypassing Vici)
     */
    public function processLeadForTesting($lead, $testSession = null)
    {
        $startTime = microtime(true);
        
        try {
            Log::info('Starting Allstate testing process for lead', [
                'lead_id' => $lead->id,
                'lead_name' => $lead->name,
                'test_session' => $testSession
            ]);

            // Step 1: Auto-generate qualification data (simulate agent questions)
            $qualificationData = $this->autoQualificationService->generateQualificationData($lead);
            
            // Step 2: Track data sources for transparency
            $dataSources = $this->trackDataSources($lead, $qualificationData);
            
            // Step 3: Send to Allstate API
            $allstateResult = $this->allstateService->transferCall($lead, 'auto-insurance', $qualificationData);
            
            $endTime = microtime(true);
            $responseTimeMs = round(($endTime - $startTime) * 1000);
            
            // Step 4: Log everything for dashboard monitoring
            $testLog = $this->logTestResult($lead, $qualificationData, $dataSources, $allstateResult, $responseTimeMs, $testSession);
            
            Log::info('Allstate testing process completed', [
                'lead_id' => $lead->id,
                'success' => $allstateResult['success'] ?? false,
                'response_time_ms' => $responseTimeMs,
                'test_log_id' => $testLog->id
            ]);
            
            return [
                'success' => true,
                'test_log_id' => $testLog->id,
                'allstate_result' => $allstateResult,
                'qualification_data' => $qualificationData,
                'data_sources' => $dataSources,
                'response_time_ms' => $responseTimeMs
            ];
            
        } catch (\Exception $e) {
            $endTime = microtime(true);
            $responseTimeMs = round(($endTime - $startTime) * 1000);
            
            Log::error('Allstate testing process failed', [
                'lead_id' => $lead->id,
                'error' => $e->getMessage(),
                'response_time_ms' => $responseTimeMs
            ]);
            
            // Still log the failure for monitoring
            $testLog = $this->logTestFailure($lead, $e, $responseTimeMs, $testSession);
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'test_log_id' => $testLog->id,
                'response_time_ms' => $responseTimeMs
            ];
        }
    }

    /**
     * Track where each piece of data came from for transparency
     */
    private function trackDataSources($lead, $qualificationData)
    {
        $drivers = json_decode($lead->drivers ?? '[]', true) ?: [];
        $vehicles = json_decode($lead->vehicles ?? '[]', true) ?: [];
        $currentPolicy = json_decode($lead->current_policy ?? '{}', true) ?: [];
        $payload = json_decode($lead->payload ?? '{}', true) ?: [];

        $dataSources = [];

        // Track each qualification field
        foreach ($qualificationData as $field => $value) {
            $dataSources[$field] = $this->determineDataSource($field, $value, $lead, $drivers, $vehicles, $currentPolicy, $payload, $qualificationData);
        }

        return $dataSources;
    }

    /**
     * Determine where a specific data field came from
     */
    private function determineDataSource($field, $value, $lead, $drivers, $vehicles, $currentPolicy, $payload, $qualificationData = [])
    {
        switch ($field) {
            // If agent answered in Top 12, prefer that and label it
            case 'state':
                if (!empty($qualificationData['state'])) return 'top12';
                return !empty($lead->state) ? 'lead_data' : 'default';

            case 'zip_code':
                if (!empty($qualificationData['zip_code'])) return 'top12';
                return !empty($lead->zip_code) ? 'lead_data' : 'default';
            case 'currently_insured':
                if (isset($qualificationData['currently_insured'])) return 'top12';
                if (!empty($currentPolicy['current_insurance'])) return 'current_policy';
                if (!empty($payload['current_insurance'])) return 'payload';
                foreach ($drivers as $driver) {
                    if (!empty($driver['current_insurance'])) return 'driver_data';
                }
                return 'smart_logic';

            case 'current_company':
                if (!empty($currentPolicy['current_company'])) return 'current_policy';
                if (!empty($payload['current_insurance'])) return 'payload';
                return 'smart_logic';

            case 'state':
                return !empty($lead->state) ? 'lead_data' : 'default';

            case 'zip_code':
                return !empty($lead->zip_code) ? 'lead_data' : 'default';

            case 'num_vehicles':
                return !empty($vehicles) ? 'vehicle_data' : 'default';

            case 'home_status':
                foreach ($drivers as $driver) {
                    if (!empty($driver['residence_type'])) return 'driver_data';
                }
                if (!empty($payload['residence_type'])) return 'payload';
                return 'default';

            case 'active_license':
                foreach ($drivers as $driver) {
                    if (!empty($driver['license_status'])) return 'driver_data';
                }
                return 'default';

            case 'dui_sr22':
                if (isset($qualificationData['dui_sr22'])) return 'top12';
                foreach ($drivers as $driver) {
                    if (!empty($driver['dui_conviction']) || !empty($driver['sr22_required'])) return 'driver_data';
                }
                if (isset($payload['dui_sr22'])) return 'payload';
                return 'default';

            case 'occupation':
                foreach ($drivers as $driver) {
                    if (!empty($driver['occupation'])) return 'driver_data';
                }
                return 'default';

            case 'education_level':
                foreach ($drivers as $driver) {
                    if (!empty($driver['education'])) return 'driver_data';
                }
                return 'default';

            case 'date_of_birth':
                foreach ($drivers as $driver) {
                    if (!empty($driver['birth_date']) || !empty($driver['date_of_birth'])) return 'driver_data';
                }
                return 'default';

            case 'gender':
                foreach ($drivers as $driver) {
                    if (!empty($driver['gender'])) return 'driver_data';
                }
                return 'default';

            case 'marital_status':
                foreach ($drivers as $driver) {
                    if (!empty($driver['marital_status'])) return 'driver_data';
                }
                return 'default';

            case 'years_licensed':
                foreach ($drivers as $driver) {
                    if (!empty($driver['years_licensed'])) return 'driver_data';
                }
                return 'default';

            case 'credit_score':
                if (!empty($qualificationData['credit_score']) || !empty($qualificationData['credit_score_range'])) return 'top12';
                foreach ($drivers as $driver) {
                    if (!empty($driver['credit_score'])) return 'driver_data';
                }
                if (!empty($payload['credit_score']) || !empty($payload['credit_score_range'])) return 'payload';
                return 'default';

            case 'current_premium':
                if (!empty($currentPolicy['premium'])) return 'current_policy';
                if (!empty($currentPolicy['monthly_premium'])) return 'current_policy';
                return 'default';

            case 'policy_expires':
                if (!empty($currentPolicy['expiration_date'])) return 'current_policy';
                if (!empty($currentPolicy['policy_expires'])) return 'current_policy';
                return 'default';

            case 'lead_quality_score':
            case 'motivation_level':
            case 'urgency':
            case 'best_time_to_call':
            case 'agent_notes':
                return 'auto_calculated';

            default:
                return 'smart_logic';
        }
    }

    /**
     * Log successful test result
     */
    private function logTestResult($lead, $qualificationData, $dataSources, $allstateResult, $responseTimeMs, $testSession)
    {
        return AllstateTestLog::create([
            'lead_id' => $lead->id,
            'external_lead_id' => $lead->external_lead_id,
            'lead_name' => $lead->name,
            'lead_type' => $lead->type ?? 'auto',
            'lead_phone' => $lead->phone,
            'lead_email' => $lead->email,
            'qualification_data' => $qualificationData,
            'data_sources' => $dataSources,
            'allstate_payload' => $allstateResult['payload'] ?? [],
            'allstate_endpoint' => $allstateResult['endpoint'] ?? 'unknown',
            'response_status' => $allstateResult['status_code'] ?? 0,
            'allstate_response' => $allstateResult['allstate_response'] ?? [],
            'success' => $allstateResult['success'] ?? false,
            'error_message' => $allstateResult['error'] ?? null,
            'validation_errors' => $this->extractValidationErrors($allstateResult),
            'sent_at' => now(),
            'response_time_ms' => $responseTimeMs,
            'test_environment' => env('ALLSTATE_API_ENV', 'testing'),
            'test_session' => $testSession,
            'notes' => 'Auto-processed via testing bypass (Vici skipped)'
        ]);
    }

    /**
     * Log failed test result
     */
    private function logTestFailure($lead, $exception, $responseTimeMs, $testSession)
    {
        return AllstateTestLog::create([
            'lead_id' => $lead->id,
            'external_lead_id' => $lead->external_lead_id,
            'lead_name' => $lead->name,
            'lead_type' => $lead->type ?? 'auto',
            'lead_phone' => $lead->phone,
            'lead_email' => $lead->email,
            'qualification_data' => [],
            'data_sources' => [],
            'allstate_payload' => [],
            'allstate_endpoint' => 'unknown',
            'response_status' => 0,
            'allstate_response' => [],
            'success' => false,
            'error_message' => $exception->getMessage(),
            'validation_errors' => [],
            'sent_at' => now(),
            'response_time_ms' => $responseTimeMs,
            'test_environment' => env('ALLSTATE_API_ENV', 'testing'),
            'test_session' => $testSession,
            'notes' => 'Processing failed before reaching Allstate API'
        ]);
    }

    /**
     * Extract validation errors from Allstate response
     */
    private function extractValidationErrors($allstateResult)
    {
        if (!isset($allstateResult['allstate_response'])) {
            return [];
        }

        $response = $allstateResult['allstate_response'];
        
        // Check for validation errors in response
        if (isset($response['properties'])) {
            return $response['properties'];
        }
        
        if (isset($response['errors'])) {
            return $response['errors'];
        }
        
        if (isset($response['error']) && is_array($response['error'])) {
            return $response['error'];
        }
        
        return [];
    }

    /**
     * Get recent test results for dashboard
     */
    public function getRecentTestResults($limit = 50)
    {
        return AllstateTestLog::with('lead')
            ->orderBy('sent_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get test statistics
     */
    public function getTestStatistics($testSession = null)
    {
        $query = AllstateTestLog::query();
        
        if ($testSession) {
            $query->where('test_session', $testSession);
        }
        
        $total = $query->count();
        $successful = $query->where('success', true)->count();
        $failed = $query->where('success', false)->count();
        $avgResponseTime = $query->avg('response_time_ms');
        
        return [
            'total_tests' => $total,
            'successful' => $successful,
            'failed' => $failed,
            'success_rate' => $total > 0 ? round(($successful / $total) * 100, 1) : 0,
            'avg_response_time_ms' => round($avgResponseTime ?? 0, 0),
            'last_test' => $query->latest('sent_at')->first()?->sent_at
        ];
    }
}
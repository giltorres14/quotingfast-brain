<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ViciReportsService;
use Carbon\Carbon;

class ViciReportsController extends Controller
{
    private $reportsService;
    
    public function __construct(ViciReportsService $reportsService)
    {
        $this->reportsService = $reportsService;
    }
    
    /**
     * Display comprehensive Vici reports dashboard
     */
    public function comprehensiveReports(Request $request)
    {
        // Get date range from request or default to last 7 days
        $dateFrom = $request->get('date_from') 
            ? Carbon::parse($request->get('date_from'))->startOfDay() 
            : Carbon::now()->subDays(7)->startOfDay();
            
        $dateTo = $request->get('date_to') 
            ? Carbon::parse($request->get('date_to'))->endOfDay() 
            : Carbon::now()->endOfDay();
        
        // Fetch all reports
        $data = [
            'executiveSummary' => $this->reportsService->getExecutiveSummary($dateFrom, $dateTo),
            'agentScorecard' => $this->reportsService->getAgentScorecard($dateFrom, $dateTo),
            'campaignROI' => $this->reportsService->getCampaignROI($dateFrom, $dateTo),
            'speedToLead' => $this->reportsService->getSpeedToLead($dateFrom, $dateTo),
            'leadRecycling' => $this->reportsService->getLeadRecyclingIntelligence($dateFrom, $dateTo),
            'optimalCallTimes' => $this->reportsService->getOptimalCallTimeAnalysis($dateFrom, $dateTo),
            'leadWaste' => $this->reportsService->getLeadWasteFinder($dateFrom, $dateTo),
            'predictiveScoring' => $this->reportsService->getPredictiveLeadScoring(20),
            'realTimeOps' => $this->reportsService->getRealTimeOperationsDashboard()
        ];
        
        return view('admin.vici-comprehensive-reports', $data);
    }
    
    /**
     * Export reports to CSV
     */
    public function exportReports(Request $request, $reportType)
    {
        $dateFrom = $request->get('date_from') 
            ? Carbon::parse($request->get('date_from'))->startOfDay() 
            : Carbon::now()->subDays(7)->startOfDay();
            
        $dateTo = $request->get('date_to') 
            ? Carbon::parse($request->get('date_to'))->endOfDay() 
            : Carbon::now()->endOfDay();
        
        $data = [];
        $filename = '';
        
        switch ($reportType) {
            case 'agent-scorecard':
                $data = $this->reportsService->getAgentScorecard($dateFrom, $dateTo);
                $filename = 'agent_scorecard_' . now()->format('Y-m-d') . '.csv';
                break;
                
            case 'campaign-roi':
                $data = $this->reportsService->getCampaignROI($dateFrom, $dateTo);
                $filename = 'campaign_roi_' . now()->format('Y-m-d') . '.csv';
                break;
                
            case 'lead-journey':
                $data = $this->reportsService->getLeadJourneyTimeline(null, $dateFrom, $dateTo);
                $filename = 'lead_journey_' . now()->format('Y-m-d') . '.csv';
                break;
                
            case 'executive-summary':
                $data = [$this->reportsService->getExecutiveSummary($dateFrom, $dateTo)];
                $filename = 'executive_summary_' . now()->format('Y-m-d') . '.csv';
                break;
                
            default:
                abort(404, 'Report type not found');
        }
        
        // Convert to CSV
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function() use ($data) {
            $file = fopen('php://output', 'w');
            
            // Add headers
            if (!empty($data)) {
                fputcsv($file, array_keys(is_array($data[0]) ? $data[0] : (array)$data[0]));
                
                // Add data
                foreach ($data as $row) {
                    fputcsv($file, is_array($row) ? $row : (array)$row);
                }
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
    
    /**
     * API endpoint for real-time data
     */
    public function realTimeData()
    {
        return response()->json($this->reportsService->getRealTimeOperationsDashboard());
    }
    
    /**
     * Lead journey timeline for specific lead
     */
    public function leadJourney($leadId)
    {
        $journey = $this->reportsService->getLeadJourneyTimeline($leadId);
        
        return view('admin.lead-journey', [
            'journey' => $journey,
            'leadId' => $leadId
        ]);
    }
}



namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ViciReportsService;
use Carbon\Carbon;

class ViciReportsController extends Controller
{
    private $reportsService;
    
    public function __construct(ViciReportsService $reportsService)
    {
        $this->reportsService = $reportsService;
    }
    
    /**
     * Display comprehensive Vici reports dashboard
     */
    public function comprehensiveReports(Request $request)
    {
        // Get date range from request or default to last 7 days
        $dateFrom = $request->get('date_from') 
            ? Carbon::parse($request->get('date_from'))->startOfDay() 
            : Carbon::now()->subDays(7)->startOfDay();
            
        $dateTo = $request->get('date_to') 
            ? Carbon::parse($request->get('date_to'))->endOfDay() 
            : Carbon::now()->endOfDay();
        
        // Fetch all reports
        $data = [
            'executiveSummary' => $this->reportsService->getExecutiveSummary($dateFrom, $dateTo),
            'agentScorecard' => $this->reportsService->getAgentScorecard($dateFrom, $dateTo),
            'campaignROI' => $this->reportsService->getCampaignROI($dateFrom, $dateTo),
            'speedToLead' => $this->reportsService->getSpeedToLead($dateFrom, $dateTo),
            'leadRecycling' => $this->reportsService->getLeadRecyclingIntelligence($dateFrom, $dateTo),
            'optimalCallTimes' => $this->reportsService->getOptimalCallTimeAnalysis($dateFrom, $dateTo),
            'leadWaste' => $this->reportsService->getLeadWasteFinder($dateFrom, $dateTo),
            'predictiveScoring' => $this->reportsService->getPredictiveLeadScoring(20),
            'realTimeOps' => $this->reportsService->getRealTimeOperationsDashboard()
        ];
        
        return view('admin.vici-comprehensive-reports', $data);
    }
    
    /**
     * Export reports to CSV
     */
    public function exportReports(Request $request, $reportType)
    {
        $dateFrom = $request->get('date_from') 
            ? Carbon::parse($request->get('date_from'))->startOfDay() 
            : Carbon::now()->subDays(7)->startOfDay();
            
        $dateTo = $request->get('date_to') 
            ? Carbon::parse($request->get('date_to'))->endOfDay() 
            : Carbon::now()->endOfDay();
        
        $data = [];
        $filename = '';
        
        switch ($reportType) {
            case 'agent-scorecard':
                $data = $this->reportsService->getAgentScorecard($dateFrom, $dateTo);
                $filename = 'agent_scorecard_' . now()->format('Y-m-d') . '.csv';
                break;
                
            case 'campaign-roi':
                $data = $this->reportsService->getCampaignROI($dateFrom, $dateTo);
                $filename = 'campaign_roi_' . now()->format('Y-m-d') . '.csv';
                break;
                
            case 'lead-journey':
                $data = $this->reportsService->getLeadJourneyTimeline(null, $dateFrom, $dateTo);
                $filename = 'lead_journey_' . now()->format('Y-m-d') . '.csv';
                break;
                
            case 'executive-summary':
                $data = [$this->reportsService->getExecutiveSummary($dateFrom, $dateTo)];
                $filename = 'executive_summary_' . now()->format('Y-m-d') . '.csv';
                break;
                
            default:
                abort(404, 'Report type not found');
        }
        
        // Convert to CSV
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function() use ($data) {
            $file = fopen('php://output', 'w');
            
            // Add headers
            if (!empty($data)) {
                fputcsv($file, array_keys(is_array($data[0]) ? $data[0] : (array)$data[0]));
                
                // Add data
                foreach ($data as $row) {
                    fputcsv($file, is_array($row) ? $row : (array)$row);
                }
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
    
    /**
     * API endpoint for real-time data
     */
    public function realTimeData()
    {
        return response()->json($this->reportsService->getRealTimeOperationsDashboard());
    }
    
    /**
     * Lead journey timeline for specific lead
     */
    public function leadJourney($leadId)
    {
        $journey = $this->reportsService->getLeadJourneyTimeline($leadId);
        
        return view('admin.lead-journey', [
            'journey' => $journey,
            'leadId' => $leadId
        ]);
    }
}



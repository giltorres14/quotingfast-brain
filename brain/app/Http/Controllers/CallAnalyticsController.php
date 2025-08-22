<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CallAnalyticsController extends Controller
{
    public function index()
    {
        return view('reports.call-analytics');
    }
    
    public function getAnalytics(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->subDays(7)->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));
        
        // Get overview metrics
        $overview = DB::table('vici_call_metrics')
            ->whereBetween('last_call_date', [$startDate, $endDate])
            ->selectRaw('
                COUNT(DISTINCT lead_id) as total_leads,
                SUM(total_calls) as total_calls,
                AVG(total_calls) as avg_calls_per_lead,
                SUM(CASE WHEN connected = true THEN 1 ELSE 0 END) as connected_leads,
                ROUND(AVG(CASE WHEN connected = true THEN 1 ELSE 0 END) * 100, 2) as connect_rate
            ')
            ->first();
            
        // Get disposition breakdown
        $dispositions = DB::table('orphan_call_logs')
            ->whereBetween('call_date', [$startDate, $endDate])
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->orderBy('count', 'desc')
            ->get();
            
        // Get hourly performance
        $hourlyData = DB::table('orphan_call_logs')
            ->whereBetween('call_date', [$startDate, $endDate])
            ->selectRaw('
                EXTRACT(HOUR FROM call_date) as hour,
                COUNT(*) as total_calls,
                SUM(CASE WHEN status IN (\'XFER\', \'XFERA\') THEN 1 ELSE 0 END) as transfers,
                ROUND(AVG(length_in_sec), 2) as avg_duration
            ')
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();
            
        // Get agent performance
        $agentData = DB::table('orphan_call_logs')
            ->whereBetween('call_date', [$startDate, $endDate])
            ->select('agent_user', DB::raw('
                COUNT(*) as total_calls,
                SUM(CASE WHEN status IN (\'XFER\', \'XFERA\') THEN 1 ELSE 0 END) as transfers,
                ROUND(AVG(length_in_sec), 2) as avg_duration,
                ROUND(SUM(CASE WHEN status IN (\'XFER\', \'XFERA\') THEN 1 ELSE 0 END)::NUMERIC / COUNT(*) * 100, 2) as conversion_rate
            '))
            ->groupBy('agent_user')
            ->orderBy('total_calls', 'desc')
            ->limit(20)
            ->get();
            
        // Get list performance
        $listData = DB::table('orphan_call_logs')
            ->whereBetween('call_date', [$startDate, $endDate])
            ->select('list_id', DB::raw('
                COUNT(*) as total_calls,
                COUNT(DISTINCT phone_number) as unique_leads,
                SUM(CASE WHEN status IN (\'XFER\', \'XFERA\') THEN 1 ELSE 0 END) as transfers
            '))
            ->groupBy('list_id')
            ->orderBy('total_calls', 'desc')
            ->get();
            
        return response()->json([
            'overview' => $overview,
            'dispositions' => $dispositions,
            'hourly' => $hourlyData,
            'agents' => $agentData,
            'lists' => $listData
        ]);
    }
    
    public function exportCSV(Request $request)
    {
        $type = $request->input('type', 'overview');
        $startDate = $request->input('start_date', Carbon::now()->subDays(7)->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));
        
        $filename = "call_analytics_{$type}_" . date('Y-m-d') . ".csv";
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];
        
        $callback = function() use ($type, $startDate, $endDate) {
            $file = fopen('php://output', 'w');
            
            switch($type) {
                case 'dispositions':
                    fputcsv($file, ['Status', 'Count']);
                    $data = DB::table('orphan_call_logs')
                        ->whereBetween('call_date', [$startDate, $endDate])
                        ->select('status', DB::raw('COUNT(*) as count'))
                        ->groupBy('status')
                        ->orderBy('count', 'desc')
                        ->get();
                    foreach($data as $row) {
                        fputcsv($file, [$row->status, $row->count]);
                    }
                    break;
                    
                case 'agents':
                    fputcsv($file, ['Agent', 'Total Calls', 'Transfers', 'Avg Duration', 'Conversion Rate']);
                    $data = DB::table('orphan_call_logs')
                        ->whereBetween('call_date', [$startDate, $endDate])
                        ->select('agent_user', DB::raw('
                            COUNT(*) as total_calls,
                            SUM(CASE WHEN status IN (\'XFER\', \'XFERA\') THEN 1 ELSE 0 END) as transfers,
                            ROUND(AVG(length_in_sec), 2) as avg_duration,
                            ROUND(SUM(CASE WHEN status IN (\'XFER\', \'XFERA\') THEN 1 ELSE 0 END)::NUMERIC / COUNT(*) * 100, 2) as conversion_rate
                        '))
                        ->groupBy('agent_user')
                        ->orderBy('total_calls', 'desc')
                        ->get();
                    foreach($data as $row) {
                        fputcsv($file, [
                            $row->agent_user,
                            $row->total_calls,
                            $row->transfers,
                            $row->avg_duration,
                            $row->conversion_rate
                        ]);
                    }
                    break;
                    
                default:
                    fputcsv($file, ['Metric', 'Value']);
                    $overview = DB::table('vici_call_metrics')
                        ->whereBetween('last_call_date', [$startDate, $endDate])
                        ->selectRaw('
                            COUNT(DISTINCT lead_id) as total_leads,
                            SUM(total_calls) as total_calls,
                            AVG(total_calls) as avg_calls_per_lead,
                            SUM(CASE WHEN connected = true THEN 1 ELSE 0 END) as connected_leads,
                            ROUND(AVG(CASE WHEN connected = true THEN 1 ELSE 0 END) * 100, 2) as connect_rate
                        ')
                        ->first();
                    fputcsv($file, ['Total Leads', $overview->total_leads ?? 0]);
                    fputcsv($file, ['Total Calls', $overview->total_calls ?? 0]);
                    fputcsv($file, ['Avg Calls per Lead', $overview->avg_calls_per_lead ?? 0]);
                    fputcsv($file, ['Connected Leads', $overview->connected_leads ?? 0]);
                    fputcsv($file, ['Connect Rate %', $overview->connect_rate ?? 0]);
                    break;
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
}






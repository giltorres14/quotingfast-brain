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
        $range = $request->input('range', 'today');
        $startDate = null;
        $endDate = Carbon::now();

        // Determine date range
        switch ($range) {
            case 'today':
                $startDate = Carbon::today();
                break;
            case 'yesterday':
                $startDate = Carbon::yesterday();
                $endDate = Carbon::yesterday()->endOfDay();
                break;
            case 'week':
                $startDate = Carbon::now()->subDays(7);
                break;
            case 'month':
                $startDate = Carbon::now()->subDays(30);
                break;
            case 'all':
                $startDate = Carbon::create(2024, 1, 1); // Or earliest date in DB
                break;
            case 'custom':
                $startDate = Carbon::parse($request->input('start_date'));
                $endDate = Carbon::parse($request->input('end_date'))->endOfDay();
                break;
        }

        // Build query with date filter
        $query = DB::table('orphan_call_logs')
            ->whereBetween('call_date', [$startDate, $endDate]);

        // Get overall metrics
        $metrics = $this->getMetrics(clone $query);
        
        // Get chart data
        $charts = $this->getChartData(clone $query, $startDate, $endDate);
        
        // Get table data
        $tables = $this->getTableData(clone $query);
        
        // Generate insights
        $insights = $this->generateInsights($metrics);

        return response()->json([
            'metrics' => $metrics,
            'charts' => $charts,
            'tables' => $tables,
            'insights' => $insights
        ]);
    }

    private function getMetrics($query)
    {
        $transferDispositions = ['XFER', 'XFERA'];
        
        $totalCalls = $query->count();
        $uniqueLeads = $query->distinct('phone_number')->count('phone_number');
        
        $transfers = $query->whereIn('status', $transferDispositions)
            ->distinct('phone_number')
            ->count('phone_number');
        
        $connectedCalls = $query->where('length_in_sec', '>', 30)->count();
        
        $avgCallsPerLead = $uniqueLeads > 0 ? round($totalCalls / $uniqueLeads, 1) : 0;
        $transferRate = $uniqueLeads > 0 ? round(($transfers / $uniqueLeads) * 100, 2) : 0;
        $connectRate = $totalCalls > 0 ? round(($connectedCalls / $totalCalls) * 100, 1) : 0;
        
        // Get avg calls to transfer
        $avgCallsToTransfer = 0;
        if ($transfers > 0) {
            $transferredPhones = $query->whereIn('status', $transferDispositions)
                ->distinct()
                ->pluck('phone_number');
            
            $totalCallsToTransferred = DB::table('orphan_call_logs')
                ->whereIn('phone_number', $transferredPhones)
                ->count();
            
            $avgCallsToTransfer = round($totalCallsToTransferred / count($transferredPhones), 1);
        }

        return [
            'total_calls' => $totalCalls,
            'unique_leads' => $uniqueLeads,
            'total_transfers' => $transfers,
            'transfer_rate' => $transferRate,
            'connected_calls' => $connectedCalls,
            'connect_rate' => $connectRate,
            'avg_calls_per_lead' => $avgCallsPerLead,
            'avg_calls_to_transfer' => $avgCallsToTransfer
        ];
    }

    private function getChartData($query, $startDate, $endDate)
    {
        $transferDispositions = ['XFER', 'XFERA'];
        
        // Volume chart - daily calls
        $volumeData = [];
        $current = clone $startDate;
        while ($current <= $endDate) {
            $dayCount = (clone $query)->whereDate('call_date', $current)->count();
            $volumeData['labels'][] = $current->format('M d');
            $volumeData['data'][] = $dayCount;
            $current->addDay();
        }
        
        // Funnel chart
        $totalCalls = $query->count();
        $answered = $query->where('status', 'A')->count();
        $connected = $query->where('length_in_sec', '>', 30)->count();
        $transfers = $query->whereIn('status', $transferDispositions)->count();
        
        $funnelData = [
            'labels' => ['Total Calls', 'Answered', 'Connected >30s', 'Transfers'],
            'datasets' => [[
                'data' => [$totalCalls, $answered, $connected, $transfers],
                'backgroundColor' => ['#007bff', '#28a745', '#ffc107', '#dc3545']
            ]]
        ];
        
        // Disposition chart
        $dispositions = $query->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get();
        
        $dispData = [
            'labels' => $dispositions->pluck('status')->toArray(),
            'datasets' => [[
                'data' => $dispositions->pluck('count')->toArray(),
                'backgroundColor' => [
                    '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', 
                    '#9966FF', '#FF9F40', '#FF6384', '#C9CBCF',
                    '#4BC0C0', '#FF6384'
                ]
            ]]
        ];
        
        // Hourly chart
        $hourlyData = $query->selectRaw('EXTRACT(HOUR FROM call_date) as hour, COUNT(*) as calls')
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();
        
        $hourlyChartData = [
            'labels' => range(0, 23),
            'datasets' => [[
                'label' => 'Calls',
                'data' => array_fill(0, 24, 0),
                'backgroundColor' => '#007bff'
            ]]
        ];
        
        foreach ($hourlyData as $hour) {
            $hourlyChartData['datasets'][0]['data'][intval($hour->hour)] = $hour->calls;
        }
        
        // Calls to convert distribution
        $callsToConvertData = [
            'labels' => ['1-3', '4-6', '7-10', '11-15', '16-20', '20+'],
            'datasets' => [[
                'label' => 'Transfers',
                'data' => [20, 35, 25, 10, 5, 5], // Sample data - would need complex query
                'backgroundColor' => '#28a745'
            ]]
        ];
        
        // Time to convert
        $timeToConvertData = [
            'labels' => ['<1 hour', '1-24 hours', '1-3 days', '3-7 days', '7+ days'],
            'datasets' => [[
                'data' => [15, 40, 25, 15, 5], // Sample data
                'backgroundColor' => [
                    '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF'
                ]
            ]]
        ];

        return [
            'volume' => [
                'labels' => $volumeData['labels'] ?? [],
                'datasets' => [[
                    'label' => 'Calls',
                    'data' => $volumeData['data'] ?? [],
                    'borderColor' => '#007bff',
                    'tension' => 0.1
                ]]
            ],
            'funnel' => $funnelData,
            'disposition' => $dispData,
            'hourly' => $hourlyChartData,
            'calls_to_convert' => $callsToConvertData,
            'time_to_convert' => $timeToConvertData
        ];
    }

    private function getTableData($query)
    {
        $transferDispositions = ['XFER', 'XFERA'];
        
        // Disposition table
        $totalCalls = $query->count();
        $dispositions = $query->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->orderBy('count', 'desc')
            ->limit(15)
            ->get()
            ->map(function($item) use ($totalCalls) {
                return [
                    'status' => $item->status ?: 'UNKNOWN',
                    'count' => $item->count,
                    'percentage' => $totalCalls > 0 ? round(($item->count / $totalCalls) * 100, 1) : 0
                ];
            });
        
        // Hourly table
        $hourlyStats = $query->selectRaw('
                EXTRACT(HOUR FROM call_date) as hour,
                COUNT(*) as calls,
                COUNT(CASE WHEN status IN (\'' . implode('\',\'', $transferDispositions) . '\') THEN 1 END) as transfers,
                AVG(length_in_sec) as avg_duration
            ')
            ->groupBy('hour')
            ->orderBy('hour')
            ->get()
            ->map(function($item) {
                return [
                    'hour' => intval($item->hour),
                    'calls' => $item->calls,
                    'transfers' => $item->transfers,
                    'transfer_rate' => $item->calls > 0 ? round(($item->transfers / $item->calls) * 100, 1) : 0,
                    'avg_duration' => round($item->avg_duration)
                ];
            });
        
        // Agent table
        $agents = $query->select('agent_id', DB::raw('
                COUNT(*) as calls,
                COUNT(CASE WHEN status IN (\'' . implode('\',\'', $transferDispositions) . '\') THEN 1 END) as transfers,
                AVG(length_in_sec) as avg_talk_time
            '))
            ->whereNotNull('agent_id')
            ->where('agent_id', '!=', '')
            ->groupBy('agent_id')
            ->having('calls', '>', 10)
            ->orderBy('transfers', 'desc')
            ->limit(20)
            ->get()
            ->map(function($item) {
                $transferRate = $item->calls > 0 ? round(($item->transfers / $item->calls) * 100, 1) : 0;
                $efficiency = ($transferRate * 100 + (60 / max($item->avg_talk_time, 1)) * 50) / 150;
                return [
                    'agent' => $item->agent_id,
                    'calls' => $item->calls,
                    'transfers' => $item->transfers,
                    'transfer_rate' => $transferRate,
                    'avg_talk_time' => round($item->avg_talk_time),
                    'efficiency_score' => round($efficiency, 1)
                ];
            });
        
        // List table
        $lists = $query->select('list_id', DB::raw('
                COUNT(*) as calls,
                COUNT(DISTINCT phone_number) as unique_leads,
                COUNT(CASE WHEN status IN (\'' . implode('\',\'', $transferDispositions) . '\') THEN 1 END) as transfers
            '))
            ->whereNotNull('list_id')
            ->where('list_id', '>', 0)
            ->groupBy('list_id')
            ->having('calls', '>', 100)
            ->orderBy('calls', 'desc')
            ->limit(15)
            ->get()
            ->map(function($item) {
                return [
                    'list_id' => $item->list_id,
                    'calls' => $item->calls,
                    'unique_leads' => $item->unique_leads,
                    'transfers' => $item->transfers,
                    'transfer_rate' => $item->calls > 0 ? round(($item->transfers / $item->calls) * 100, 2) : 0,
                    'avg_calls_per_lead' => $item->unique_leads > 0 ? round($item->calls / $item->unique_leads, 1) : 0
                ];
            });

        return [
            'dispositions' => $dispositions,
            'hourly' => $hourlyStats,
            'agents' => $agents,
            'lists' => $lists
        ];
    }

    private function generateInsights($metrics)
    {
        $insights = [];
        
        // Transfer rate insight
        if ($metrics['transfer_rate'] < 1) {
            $insights[] = "⚠️ Transfer rate is {$metrics['transfer_rate']}% - below 1% threshold. Focus on script optimization.";
        } elseif ($metrics['transfer_rate'] < 2) {
            $insights[] = "📊 Transfer rate is {$metrics['transfer_rate']}% - within normal range for shared leads (1-3%).";
        } else {
            $insights[] = "✅ Transfer rate is {$metrics['transfer_rate']}% - above average for shared leads!";
        }
        
        // Calls per lead insight
        if ($metrics['avg_calls_per_lead'] > 10) {
            $insights[] = "⚠️ Averaging {$metrics['avg_calls_per_lead']} calls per lead - consider reducing to 6-8 max.";
        } else {
            $insights[] = "✅ Call volume is optimal at {$metrics['avg_calls_per_lead']} calls per lead.";
        }
        
        // Connect rate insight
        if ($metrics['connect_rate'] < 10) {
            $insights[] = "📞 Low connect rate ({$metrics['connect_rate']}%) - implement local presence dialing.";
        }
        
        // Calls to transfer insight
        if ($metrics['avg_calls_to_transfer'] > 15) {
            $insights[] = "🔄 Takes {$metrics['avg_calls_to_transfer']} calls to get a transfer - too many attempts.";
        }
        
        $insights[] = "💡 Remember: XFER and XFERA are the only true transfer dispositions.";
        
        return $insights;
    }

    public function exportCSV(Request $request)
    {
        $range = $request->input('range', 'today');
        // Implementation for CSV export
        // ... (would generate CSV based on range)
        
        $filename = "call_analytics_{$range}_" . date('Y-m-d') . ".csv";
        
        return response()->streamDownload(function() use ($range) {
            echo "Date,Calls,Transfers,Transfer Rate\n";
            // Add actual data here
        }, $filename);
    }
}






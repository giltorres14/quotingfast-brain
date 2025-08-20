@extends('layouts.app')

@section('title', 'Leads Dashboard')

@section('content')
    @php
        // Get lead statistics
        $totalLeads = \App\Models\Lead::count();
        $todayLeads = \App\Models\Lead::whereDate('created_at', today())->count();
        $weekLeads = \App\Models\Lead::where('created_at', '>=', now()->subWeek())->count();
        $monthLeads = \App\Models\Lead::where('created_at', '>=', now()->subMonth())->count();
        
        // Get source breakdown
        $sourceStats = \App\Models\Lead::select('source', \DB::raw('COUNT(*) as count'))
            ->groupBy('source')
            ->orderBy('count', 'desc')
            ->limit(5)
            ->get();
            
        // Get recent leads
        $recentLeads = \App\Models\Lead::orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
    @endphp
    
    <!-- Metrics Row -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 20px;">
        <div class="card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
            <div style="font-size: 0.875rem; opacity: 0.9;">Total Leads</div>
            <div style="font-size: 2rem; font-weight: 700;">{{ number_format($totalLeads) }}</div>
            <div style="font-size: 0.75rem; margin-top: 8px; opacity: 0.8;">All time</div>
        </div>
        
        <div class="card" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); color: white;">
            <div style="font-size: 0.875rem; opacity: 0.9;">Today's Leads</div>
            <div style="font-size: 2rem; font-weight: 700;">{{ number_format($todayLeads) }}</div>
            <div style="font-size: 0.75rem; margin-top: 8px; opacity: 0.8;">Last 24 hours</div>
        </div>
        
        <div class="card" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white;">
            <div style="font-size: 0.875rem; opacity: 0.9;">This Week</div>
            <div style="font-size: 2rem; font-weight: 700;">{{ number_format($weekLeads) }}</div>
            <div style="font-size: 0.75rem; margin-top: 8px; opacity: 0.8;">Last 7 days</div>
        </div>
        
        <div class="card" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: white;">
            <div style="font-size: 0.875rem; opacity: 0.9;">This Month</div>
            <div style="font-size: 2rem; font-weight: 700;">{{ number_format($monthLeads) }}</div>
            <div style="font-size: 0.75rem; margin-top: 8px; opacity: 0.8;">Last 30 days</div>
        </div>
    </div>
    
    <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 20px;">
        <!-- Source Breakdown -->
        <div class="card">
            <h2 class="card-title">Top Lead Sources</h2>
            <div style="display: flex; flex-direction: column; gap: 12px;">
                @foreach($sourceStats as $source)
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-weight: 500;">{{ $source->source ?: 'Unknown' }}</span>
                        <span style="background: #e5e7eb; padding: 4px 12px; border-radius: 12px; font-size: 0.875rem;">
                            {{ number_format($source->count) }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
        
        <!-- Recent Leads -->
        <div class="card">
            <h2 class="card-title">Recent Leads</h2>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="border-bottom: 2px solid #e5e7eb;">
                            <th style="padding: 8px; text-align: left; font-weight: 600; color: #6b7280;">ID</th>
                            <th style="padding: 8px; text-align: left; font-weight: 600; color: #6b7280;">Name</th>
                            <th style="padding: 8px; text-align: left; font-weight: 600; color: #6b7280;">Phone</th>
                            <th style="padding: 8px; text-align: left; font-weight: 600; color: #6b7280;">Source</th>
                            <th style="padding: 8px; text-align: left; font-weight: 600; color: #6b7280;">Created</th>
                            <th style="padding: 8px; text-align: left; font-weight: 600; color: #6b7280;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentLeads as $lead)
                            <tr style="border-bottom: 1px solid #f3f4f6;">
                                <td style="padding: 8px;">{{ $lead->external_lead_id }}</td>
                                <td style="padding: 8px; font-weight: 500;">{{ $lead->name }}</td>
                                <td style="padding: 8px;">{{ $lead->phone }}</td>
                                <td style="padding: 8px;">
                                    <span style="background: #eef2ff; color: #4f46e5; padding: 2px 8px; border-radius: 4px; font-size: 0.75rem;">
                                        {{ $lead->source ?: 'Unknown' }}
                                    </span>
                                </td>
                                <td style="padding: 8px; color: #6b7280; font-size: 0.875rem;">
                                    {{ $lead->created_at->diffForHumans() }}
                                </td>
                                <td style="padding: 8px;">
                                    <a href="/agent/lead/{{ $lead->id }}" class="btn btn-primary" style="font-size: 0.875rem; padding: 4px 12px;">
                                        View
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="card" style="margin-top: 20px;">
        <h2 class="card-title">Quick Actions</h2>
        <div style="display: flex; gap: 12px; flex-wrap: wrap;">
            <a href="/leads/queue" class="btn btn-primary">📋 View Lead Queue</a>
            <a href="/leads/import" class="btn btn-success">📥 Import Leads</a>
            <a href="/leads/search" class="btn btn-secondary">🔍 Search Leads</a>
            <a href="/leads/reports" class="btn" style="background: #f3f4f6; color: #1f2937;">📊 View Reports</a>
        </div>
    </div>
@endsection


@section('title', 'Leads Dashboard')

@section('content')
    @php
        // Get lead statistics
        $totalLeads = \App\Models\Lead::count();
        $todayLeads = \App\Models\Lead::whereDate('created_at', today())->count();
        $weekLeads = \App\Models\Lead::where('created_at', '>=', now()->subWeek())->count();
        $monthLeads = \App\Models\Lead::where('created_at', '>=', now()->subMonth())->count();
        
        // Get source breakdown
        $sourceStats = \App\Models\Lead::select('source', \DB::raw('COUNT(*) as count'))
            ->groupBy('source')
            ->orderBy('count', 'desc')
            ->limit(5)
            ->get();
            
        // Get recent leads
        $recentLeads = \App\Models\Lead::orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
    @endphp
    
    <!-- Metrics Row -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 20px;">
        <div class="card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
            <div style="font-size: 0.875rem; opacity: 0.9;">Total Leads</div>
            <div style="font-size: 2rem; font-weight: 700;">{{ number_format($totalLeads) }}</div>
            <div style="font-size: 0.75rem; margin-top: 8px; opacity: 0.8;">All time</div>
        </div>
        
        <div class="card" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); color: white;">
            <div style="font-size: 0.875rem; opacity: 0.9;">Today's Leads</div>
            <div style="font-size: 2rem; font-weight: 700;">{{ number_format($todayLeads) }}</div>
            <div style="font-size: 0.75rem; margin-top: 8px; opacity: 0.8;">Last 24 hours</div>
        </div>
        
        <div class="card" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white;">
            <div style="font-size: 0.875rem; opacity: 0.9;">This Week</div>
            <div style="font-size: 2rem; font-weight: 700;">{{ number_format($weekLeads) }}</div>
            <div style="font-size: 0.75rem; margin-top: 8px; opacity: 0.8;">Last 7 days</div>
        </div>
        
        <div class="card" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: white;">
            <div style="font-size: 0.875rem; opacity: 0.9;">This Month</div>
            <div style="font-size: 2rem; font-weight: 700;">{{ number_format($monthLeads) }}</div>
            <div style="font-size: 0.75rem; margin-top: 8px; opacity: 0.8;">Last 30 days</div>
        </div>
    </div>
    
    <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 20px;">
        <!-- Source Breakdown -->
        <div class="card">
            <h2 class="card-title">Top Lead Sources</h2>
            <div style="display: flex; flex-direction: column; gap: 12px;">
                @foreach($sourceStats as $source)
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-weight: 500;">{{ $source->source ?: 'Unknown' }}</span>
                        <span style="background: #e5e7eb; padding: 4px 12px; border-radius: 12px; font-size: 0.875rem;">
                            {{ number_format($source->count) }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
        
        <!-- Recent Leads -->
        <div class="card">
            <h2 class="card-title">Recent Leads</h2>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="border-bottom: 2px solid #e5e7eb;">
                            <th style="padding: 8px; text-align: left; font-weight: 600; color: #6b7280;">ID</th>
                            <th style="padding: 8px; text-align: left; font-weight: 600; color: #6b7280;">Name</th>
                            <th style="padding: 8px; text-align: left; font-weight: 600; color: #6b7280;">Phone</th>
                            <th style="padding: 8px; text-align: left; font-weight: 600; color: #6b7280;">Source</th>
                            <th style="padding: 8px; text-align: left; font-weight: 600; color: #6b7280;">Created</th>
                            <th style="padding: 8px; text-align: left; font-weight: 600; color: #6b7280;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentLeads as $lead)
                            <tr style="border-bottom: 1px solid #f3f4f6;">
                                <td style="padding: 8px;">{{ $lead->external_lead_id }}</td>
                                <td style="padding: 8px; font-weight: 500;">{{ $lead->name }}</td>
                                <td style="padding: 8px;">{{ $lead->phone }}</td>
                                <td style="padding: 8px;">
                                    <span style="background: #eef2ff; color: #4f46e5; padding: 2px 8px; border-radius: 4px; font-size: 0.75rem;">
                                        {{ $lead->source ?: 'Unknown' }}
                                    </span>
                                </td>
                                <td style="padding: 8px; color: #6b7280; font-size: 0.875rem;">
                                    {{ $lead->created_at->diffForHumans() }}
                                </td>
                                <td style="padding: 8px;">
                                    <a href="/agent/lead/{{ $lead->id }}" class="btn btn-primary" style="font-size: 0.875rem; padding: 4px 12px;">
                                        View
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="card" style="margin-top: 20px;">
        <h2 class="card-title">Quick Actions</h2>
        <div style="display: flex; gap: 12px; flex-wrap: wrap;">
            <a href="/leads/queue" class="btn btn-primary">📋 View Lead Queue</a>
            <a href="/leads/import" class="btn btn-success">📥 Import Leads</a>
            <a href="/leads/search" class="btn btn-secondary">🔍 Search Leads</a>
            <a href="/leads/reports" class="btn" style="background: #f3f4f6; color: #1f2937;">📊 View Reports</a>
        </div>
    </div>
@endsection




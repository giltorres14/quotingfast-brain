@extends('layouts.app')

@section('title', 'Vici Dashboard')

@section('content')
    @php
        // Get Vici metrics
        $totalCalls = \App\Models\ViciCallMetrics::count();
        $todayCalls = \App\Models\ViciCallMetrics::whereDate('created_at', today())->count();
        $connectedCalls = \App\Models\ViciCallMetrics::where('call_status', 'XFER')->count();
        $orphanCalls = \App\Models\OrphanCallLog::unmatched()->count();
        
        // Get list distribution (simulated for now)
        $listDistribution = [
            ['list' => '101 - New', 'count' => 0, 'color' => '#48bb78'],
            ['list' => '102 - Aggressive', 'count' => 0, 'color' => '#ed8936'],
            ['list' => '103 - Callback', 'count' => 0, 'color' => '#9f7aea'],
            ['list' => '104 - Phase 1', 'count' => 0, 'color' => '#4299e1'],
            ['list' => '106 - Phase 2', 'count' => 0, 'color' => '#68d391'],
            ['list' => '108 - Phase 3', 'count' => 0, 'color' => '#fc8181'],
            ['list' => '110 - Archive', 'count' => 0, 'color' => '#a0aec0'],
            ['list' => '199 - DNC', 'count' => 0, 'color' => '#2d3748'],
        ];
        
        // Get recent call activity
        $recentCalls = \App\Models\ViciCallMetrics::orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
    @endphp
    
    <!-- Metrics Row -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 20px;">
        <div class="card" style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); color: white;">
            <div style="font-size: 0.875rem; opacity: 0.9;">Total Calls</div>
            <div style="font-size: 2rem; font-weight: 700;">{{ number_format($totalCalls) }}</div>
            <div style="font-size: 0.75rem; margin-top: 8px; opacity: 0.8;">All time</div>
        </div>
        
        <div class="card" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); color: white;">
            <div style="font-size: 0.875rem; opacity: 0.9;">Today's Calls</div>
            <div style="font-size: 2rem; font-weight: 700;">{{ number_format($todayCalls) }}</div>
            <div style="font-size: 0.75rem; margin-top: 8px; opacity: 0.8;">Last 24 hours</div>
        </div>
        
        <div class="card" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white;">
            <div style="font-size: 0.875rem; opacity: 0.9;">Connected</div>
            <div style="font-size: 2rem; font-weight: 700;">{{ number_format($connectedCalls) }}</div>
            <div style="font-size: 0.75rem; margin-top: 8px; opacity: 0.8;">Transferred calls</div>
        </div>
        
        <div class="card" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); color: white;">
            <div style="font-size: 0.875rem; opacity: 0.9;">Orphan Calls</div>
            <div style="font-size: 2rem; font-weight: 700;">{{ number_format($orphanCalls) }}</div>
            <div style="font-size: 0.75rem; margin-top: 8px; opacity: 0.8;">Unmatched</div>
        </div>
    </div>
    
    <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 20px;">
        <!-- List Distribution -->
        <div class="card">
            <h2 class="card-title">Lead Flow Distribution</h2>
            <div style="display: flex; flex-direction: column; gap: 8px;">
                @foreach($listDistribution as $list)
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <div style="width: 12px; height: 12px; border-radius: 50%; background: {{ $list['color'] }};"></div>
                            <span style="font-size: 0.875rem;">{{ $list['list'] }}</span>
                        </div>
                        <span style="background: #f3f4f6; padding: 2px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 600;">
                            {{ number_format($list['count']) }}
                        </span>
                    </div>
                @endforeach
            </div>
            <div style="margin-top: 16px; padding-top: 16px; border-top: 1px solid #e5e7eb;">
                <a href="/vici/lead-flow" class="btn btn-primary" style="width: 100%; text-align: center;">
                    View Lead Flow Monitor →
                </a>
            </div>
        </div>
        
        <!-- Recent Call Activity -->
        <div class="card">
            <h2 class="card-title">Recent Call Activity</h2>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="border-bottom: 2px solid #e5e7eb;">
                            <th style="padding: 8px; text-align: left; font-weight: 600; color: #6b7280;">Lead ID</th>
                            <th style="padding: 8px; text-align: left; font-weight: 600; color: #6b7280;">Phone</th>
                            <th style="padding: 8px; text-align: left; font-weight: 600; color: #6b7280;">Status</th>
                            <th style="padding: 8px; text-align: left; font-weight: 600; color: #6b7280;">Duration</th>
                            <th style="padding: 8px; text-align: left; font-weight: 600; color: #6b7280;">Agent</th>
                            <th style="padding: 8px; text-align: left; font-weight: 600; color: #6b7280;">Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentCalls as $call)
                            <tr style="border-bottom: 1px solid #f3f4f6;">
                                <td style="padding: 8px;">{{ $call->vici_lead_id }}</td>
                                <td style="padding: 8px;">{{ $call->phone_number }}</td>
                                <td style="padding: 8px;">
                                    <span style="background: {{ $call->call_status == 'XFER' ? '#d1fae5' : '#fee2e2' }}; 
                                                 color: {{ $call->call_status == 'XFER' ? '#065f46' : '#991b1b' }}; 
                                                 padding: 2px 8px; border-radius: 4px; font-size: 0.75rem;">
                                        {{ $call->call_status }}
                                    </span>
                                </td>
                                <td style="padding: 8px;">{{ gmdate('i:s', $call->call_duration ?? 0) }}</td>
                                <td style="padding: 8px;">{{ $call->agent_name ?? 'N/A' }}</td>
                                <td style="padding: 8px; color: #6b7280; font-size: 0.875rem;">
                                    {{ $call->created_at->diffForHumans() }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" style="padding: 20px; text-align: center; color: #9ca3af;">
                                    No recent call activity
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- System Status -->
    <div class="card" style="margin-top: 20px;">
        <h2 class="card-title">System Status</h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="width: 12px; height: 12px; border-radius: 50%; background: #10b981;"></div>
                <div>
                    <div style="font-weight: 600;">Lead Flow</div>
                    <div style="font-size: 0.875rem; color: #6b7280;">Active - Running</div>
                </div>
            </div>
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="width: 12px; height: 12px; border-radius: 50%; background: #10b981;"></div>
                <div>
                    <div style="font-weight: 600;">Call Sync</div>
                    <div style="font-size: 0.875rem; color: #6b7280;">Every 5 minutes</div>
                </div>
            </div>
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="width: 12px; height: 12px; border-radius: 50%; background: #10b981;"></div>
                <div>
                    <div style="font-weight: 600;">TCPA Compliance</div>
                    <div style="font-size: 0.875rem; color: #6b7280;">Enforced</div>
                </div>
            </div>
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="width: 12px; height: 12px; border-radius: 50%; background: #f59e0b;"></div>
                <div>
                    <div style="font-weight: 600;">Orphan Calls</div>
                    <div style="font-size: 0.875rem; color: #6b7280;">{{ $orphanCalls }} pending</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="card" style="margin-top: 20px;">
        <h2 class="card-title">Quick Actions</h2>
        <div style="display: flex; gap: 12px; flex-wrap: wrap;">
            <a href="/vici/reports" class="btn btn-primary">📊 View Call Reports</a>
            <a href="/vici/lead-flow" class="btn btn-success">📈 Monitor Lead Flow</a>
            <a href="/vici/sync-status" class="btn btn-secondary">🔄 Check Sync Status</a>
            <a href="/vici/settings" class="btn" style="background: #f3f4f6; color: #1f2937;">⚙️ Vici Settings</a>
        </div>
    </div>
@endsection


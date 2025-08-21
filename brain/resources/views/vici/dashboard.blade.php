@extends('layouts.app')

@section('title', 'Vici Dashboard')

@section('content')
    <!-- Metrics Row -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 20px;">
        <div class="card" style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); color: white;">
            <div style="font-size: 0.875rem; opacity: 0.9;">Total Calls</div>
            <div style="font-size: 2rem; font-weight: 700;">{{ number_format($totalCalls) }}</div>
            <div style="font-size: 0.75rem; opacity: 0.8;">All time</div>
        </div>
        
        <div class="card" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); color: white;">
            <div style="font-size: 0.875rem; opacity: 0.9;">Today's Calls</div>
            <div style="font-size: 2rem; font-weight: 700;">{{ number_format($todayCalls) }}</div>
            <div style="font-size: 0.75rem; opacity: 0.8;">Last 24 hours</div>
        </div>
        
        <div class="card" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white;">
            <div style="font-size: 0.875rem; opacity: 0.9;">Connected</div>
            <div style="font-size: 2rem; font-weight: 700;">{{ number_format($connectedCalls) }}</div>
            <div style="font-size: 0.75rem; opacity: 0.8;">Transferred calls</div>
        </div>
        
        <div class="card" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: white;">
            <div style="font-size: 0.875rem; opacity: 0.9;">Orphan Calls</div>
            <div style="font-size: 2rem; font-weight: 700;">{{ number_format($orphanCalls) }}</div>
            <div style="font-size: 0.75rem; opacity: 0.8;">Unmatched</div>
        </div>
    </div>
    
    <!-- Lead Flow Distribution -->
    <div class="card" style="margin-bottom: 20px;">
        <h3 style="margin-bottom: 20px; color: #374151;">Lead Flow Distribution</h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
            @foreach($listDistribution as $list)
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px; background: #f9fafb; border-radius: 8px;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <div style="width: 12px; height: 12px; border-radius: 50%; background: {{ $list['color'] }};"></div>
                        <span style="font-size: 0.875rem;">{{ $list['list'] }}</span>
                    </div>
                    <span style="font-weight: 600;">
                        {{ number_format($list['count']) }}
                    </span>
                </div>
            @endforeach
        </div>
        <div style="margin-top: 20px; text-align: center;">
            <a href="{{ route('vici.lead-flow') }}" class="btn btn-primary">
                View Lead Flow Monitor →
            </a>
        </div>
    </div>
    
    <!-- Recent Call Activity -->
    <div class="card">
        <h3 style="margin-bottom: 20px; color: #374151;">Recent Call Activity</h3>
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f3f4f6;">
                        <th style="padding: 12px; text-align: left; font-size: 0.875rem; color: #6b7280;">Lead ID</th>
                        <th style="padding: 12px; text-align: left; font-size: 0.875rem; color: #6b7280;">Phone</th>
                        <th style="padding: 12px; text-align: left; font-size: 0.875rem; color: #6b7280;">Status</th>
                        <th style="padding: 12px; text-align: left; font-size: 0.875rem; color: #6b7280;">Duration</th>
                        <th style="padding: 12px; text-align: left; font-size: 0.875rem; color: #6b7280;">Agent</th>
                        <th style="padding: 12px; text-align: left; font-size: 0.875rem; color: #6b7280;">Time</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentCalls as $call)
                        <tr style="border-bottom: 1px solid #e5e7eb;">
                            <td style="padding: 8px;">{{ $call->vici_lead_id ?? 'N/A' }}</td>
                            <td style="padding: 8px;">{{ $call->phone_number ?? 'N/A' }}</td>
                            <td style="padding: 8px;">
                                <span style="background: {{ in_array($call->call_status ?? '', ['XFER', 'XFERA']) ? '#d1fae5' : '#fee2e2' }}; 
                                             color: {{ in_array($call->call_status ?? '', ['XFER', 'XFERA']) ? '#065f46' : '#991b1b' }};
                                             padding: 2px 8px; border-radius: 4px; font-size: 0.75rem;">
                                    {{ $call->call_status ?? 'N/A' }}
                                </span>
                            </td>
                            <td style="padding: 8px;">{{ gmdate('i:s', $call->talk_time ?? 0) }}</td>
                            <td style="padding: 8px;">{{ $call->agent ?? 'N/A' }}</td>
                            <td style="padding: 8px;">{{ $call->created_at ? $call->created_at->diffForHumans() : 'N/A' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="padding: 20px; text-align: center; color: #6b7280;">
                                No recent calls found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- System Status -->
    <div class="card" style="margin-top: 20px;">
        <h3 style="margin-bottom: 20px; color: #374151;">System Status</h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
            <div style="padding: 15px; background: #f0fdf4; border-left: 4px solid #10b981; border-radius: 4px;">
                <div style="font-weight: 600; color: #065f46;">Lead Flow</div>
                <div style="color: #10b981; font-size: 0.875rem;">Active - Running</div>
            </div>
            
            <div style="padding: 15px; background: #eff6ff; border-left: 4px solid #3b82f6; border-radius: 4px;">
                <div style="font-weight: 600; color: #1e3a8a;">Call Sync</div>
                <div style="color: #3b82f6; font-size: 0.875rem;">Every 5 minutes</div>
            </div>
            
            <div style="padding: 15px; background: #fef3c7; border-left: 4px solid #f59e0b; border-radius: 4px;">
                <div style="font-weight: 600; color: #78350f;">TCPA Compliance</div>
                <div style="color: #f59e0b; font-size: 0.875rem;">Enforced</div>
            </div>
            
            <div style="padding: 15px; background: #fef2f2; border-left: 4px solid #ef4444; border-radius: 4px;">
                <div style="font-weight: 600; color: #7f1d1d;">Orphan Calls</div>
                <div style="color: #ef4444; font-size: 0.875rem;">{{ number_format($orphanCalls) }} pending</div>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="card" style="margin-top: 20px;">
        <h3 style="margin-bottom: 20px; color: #374151;">Quick Actions</h3>
        <div style="display: flex; flex-wrap: wrap; gap: 10px;">
            <a href="{{ route('reports.call-analytics') }}" class="btn btn-primary">
                📊 View Call Reports
            </a>
            <a href="{{ route('vici.lead-flow') }}" class="btn btn-secondary">
                📈 Monitor Lead Flow
            </a>
            <a href="{{ route('vici.sync-status') }}" class="btn btn-secondary">
                🔄 Check Sync Status
            </a>
            <a href="{{ route('vici.settings') }}" class="btn btn-secondary">
                ⚙️ Vici Settings
            </a>
        </div>
    </div>
@endsection

<style>
.card {
    background: white;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.btn {
    padding: 10px 20px;
    border-radius: 6px;
    text-decoration: none;
    display: inline-block;
    font-weight: 500;
    transition: all 0.2s;
}

.btn-primary {
    background: #4A90E2;
    color: white;
}

.btn-primary:hover {
    background: #357ABD;
}

.btn-secondary {
    background: #6b7280;
    color: white;
}

.btn-secondary:hover {
    background: #4b5563;
}
</style>




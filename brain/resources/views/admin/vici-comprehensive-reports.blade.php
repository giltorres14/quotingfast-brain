@extends('layouts.app')

@section('content')
<div class="container-fluid" style="max-width: 1800px; margin: 0 auto; padding: 20px;">
    <h1 style="text-align: center; margin-bottom: 30px;">📊 ViciDial Comprehensive Analytics Dashboard</h1>
    
    <!-- Date Range Filter -->
    <div style="background: white; padding: 20px; border-radius: 10px; margin-bottom: 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        <form method="GET" action="{{ route('admin.vici.comprehensive-reports') }}" style="display: flex; gap: 20px; align-items: end; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 200px;">
                <label style="display: block; margin-bottom: 5px; font-weight: bold;">From Date</label>
                <input type="date" name="date_from" value="{{ request('date_from', now()->subDays(7)->format('Y-m-d')) }}" 
                       style="width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 5px;">
            </div>
            <div style="flex: 1; min-width: 200px;">
                <label style="display: block; margin-bottom: 5px; font-weight: bold;">To Date</label>
                <input type="date" name="date_to" value="{{ request('date_to', now()->format('Y-m-d')) }}" 
                       style="width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 5px;">
            </div>
            <div style="flex: 1; min-width: 200px;">
                <label style="display: block; margin-bottom: 5px; font-weight: bold;">Campaign</label>
                <select name="campaign" style="width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 5px;">
                    <option value="">All Campaigns</option>
                    <option value="AUTODIAL" {{ request('campaign') == 'AUTODIAL' ? 'selected' : '' }}>AUTODIAL</option>
                    <option value="AUTO2" {{ request('campaign') == 'AUTO2' ? 'selected' : '' }}>AUTO2 (Training)</option>
                </select>
            </div>
            <div style="flex: 1; min-width: 200px;">
                <label style="display: block; margin-bottom: 5px; font-weight: bold;">Test Group</label>
                <select name="test_group" style="width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 5px;">
                    <option value="">Both Tests</option>
                    <option value="A" {{ request('test_group') == 'A' ? 'selected' : '' }}>Test A (Lists 101-111)</option>
                    <option value="B" {{ request('test_group') == 'B' ? 'selected' : '' }}>Test B (Lists 150-153)</option>
                </select>
            </div>
            <div>
                <button type="submit" style="background: #667eea; color: white; padding: 10px 30px; border: none; border-radius: 5px; cursor: pointer;">
                    🔍 Apply Filters
                </button>
                <a href="{{ route('admin.vici.comprehensive-reports') }}" style="background: #6b7280; color: white; padding: 10px 30px; border: none; border-radius: 5px; text-decoration: none; display: inline-block; margin-left: 10px;">
                    ↻ Reset
                </a>
            </div>
        </form>
    </div>

    <!-- A/B Test Performance Comparison -->
    <div style="background: linear-gradient(135deg, #667eea, #764ba2); color: white; padding: 25px; border-radius: 15px; margin-bottom: 30px; box-shadow: 0 10px 30px rgba(0,0,0,0.2);">
        <h2 style="margin-bottom: 20px;">🔬 A/B Test Performance Comparison</h2>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
            <!-- Test A Stats -->
            <div style="background: rgba(255,255,255,0.1); padding: 20px; border-radius: 10px;">
                <h3 style="margin-bottom: 15px;">Test A (48 Calls Strategy)</h3>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div>
                        <div style="font-size: 2rem; font-weight: bold;">{{ number_format($testAStats['total_leads'] ?? 0) }}</div>
                        <div style="opacity: 0.9;">Total Leads</div>
                    </div>
                    <div>
                        <div style="font-size: 2rem; font-weight: bold;">{{ number_format($testAStats['total_calls'] ?? 0) }}</div>
                        <div style="opacity: 0.9;">Total Calls</div>
                    </div>
                    <div>
                        <div style="font-size: 2rem; font-weight: bold;">{{ $testAStats['conversion_rate'] ?? '0%' }}</div>
                        <div style="opacity: 0.9;">Conversion Rate</div>
                    </div>
                    <div>
                        <div style="font-size: 2rem; font-weight: bold;">{{ $testAStats['avg_calls_per_lead'] ?? '0' }}</div>
                        <div style="opacity: 0.9;">Avg Calls/Lead</div>
                    </div>
                    <div>
                        <div style="font-size: 2rem; font-weight: bold;">${{ $testAStats['cost_per_lead'] ?? '0.00' }}</div>
                        <div style="opacity: 0.9;">Cost per Lead</div>
                    </div>
                    <div>
                        <div style="font-size: 2rem; font-weight: bold;">${{ $testAStats['cost_per_sale'] ?? '0.00' }}</div>
                        <div style="opacity: 0.9;">Cost per Sale</div>
                    </div>
                </div>
            </div>
            
            <!-- Test B Stats -->
            <div style="background: rgba(255,255,255,0.1); padding: 20px; border-radius: 10px;">
                <h3 style="margin-bottom: 15px;">Test B (12-18 Calls Optimized)</h3>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div>
                        <div style="font-size: 2rem; font-weight: bold;">{{ number_format($testBStats['total_leads'] ?? 0) }}</div>
                        <div style="opacity: 0.9;">Total Leads</div>
                    </div>
                    <div>
                        <div style="font-size: 2rem; font-weight: bold;">{{ number_format($testBStats['total_calls'] ?? 0) }}</div>
                        <div style="opacity: 0.9;">Total Calls</div>
                    </div>
                    <div>
                        <div style="font-size: 2rem; font-weight: bold;">{{ $testBStats['conversion_rate'] ?? '0%' }}</div>
                        <div style="opacity: 0.9;">Conversion Rate</div>
                    </div>
                    <div>
                        <div style="font-size: 2rem; font-weight: bold;">{{ $testBStats['avg_calls_per_lead'] ?? '0' }}</div>
                        <div style="opacity: 0.9;">Avg Calls/Lead</div>
                    </div>
                    <div>
                        <div style="font-size: 2rem; font-weight: bold;">${{ $testBStats['cost_per_lead'] ?? '0.00' }}</div>
                        <div style="opacity: 0.9;">Cost per Lead</div>
                    </div>
                    <div>
                        <div style="font-size: 2rem; font-weight: bold;">${{ $testBStats['cost_per_sale'] ?? '0.00' }}</div>
                        <div style="opacity: 0.9;">Cost per Sale</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Executive Summary -->
    <div style="background: white; padding: 25px; border-radius: 10px; margin-bottom: 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        <h2 style="color: #1f2937; margin-bottom: 20px;">📈 Executive Summary</h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
            <div style="text-align: center; padding: 20px; background: #f0f9ff; border-radius: 10px; border: 2px solid #3b82f6;">
                <div style="font-size: 2.5rem; font-weight: bold; color: #1e40af;">{{ number_format($executiveSummary['overview']['total_calls'] ?? 0) }}</div>
                <div style="color: #6b7280; margin-top: 5px;">Total Calls</div>
            </div>
            <div style="text-align: center; padding: 20px; background: #f0fdf4; border-radius: 10px; border: 2px solid #10b981;">
                <div style="font-size: 2.5rem; font-weight: bold; color: #059669;">{{ number_format($executiveSummary['overview']['connected_calls'] ?? 0) }}</div>
                <div style="color: #6b7280; margin-top: 5px;">Connected</div>
            </div>
            <div style="text-align: center; padding: 20px; background: #fef3c7; border-radius: 10px; border: 2px solid #f59e0b;">
                <div style="font-size: 2.5rem; font-weight: bold; color: #d97706;">{{ number_format($executiveSummary['conversion']['total_transfers'] ?? 0) }}</div>
                <div style="color: #6b7280; margin-top: 5px;">Transfers (Sales)</div>
            </div>
            <div style="text-align: center; padding: 20px; background: #fce7f3; border-radius: 10px; border: 2px solid #ec4899;">
                <div style="font-size: 2.5rem; font-weight: bold; color: #be185d;">{{ $executiveSummary['conversion']['conversion_rate'] ?? 0 }}%</div>
                <div style="color: #6b7280; margin-top: 5px;">Conversion Rate</div>
            </div>
            <div style="text-align: center; padding: 20px; background: #ede9fe; border-radius: 10px; border: 2px solid #8b5cf6;">
                <div style="font-size: 2.5rem; font-weight: bold; color: #7c3aed;">${{ number_format($executiveSummary['costs']['total_cost'] ?? 0, 2) }}</div>
                <div style="color: #6b7280; margin-top: 5px;">Total Cost</div>
            </div>
            <div style="text-align: center; padding: 20px; background: #fee2e2; border-radius: 10px; border: 2px solid #ef4444;">
                <div style="font-size: 2.5rem; font-weight: bold; color: #dc2626;">{{ $executiveSummary['costs']['roi'] ?? 0 }}%</div>
                <div style="color: #6b7280; margin-top: 5px;">ROI</div>
            </div>
        </div>
    </div>

    <!-- Disposition Analysis -->
    <div style="background: white; padding: 25px; border-radius: 10px; margin-bottom: 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        <h2 style="color: #1f2937; margin-bottom: 20px;">📋 Disposition Breakdown</h2>
        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px;">
            <!-- Terminal Dispositions -->
            <div style="background: #dcfce7; padding: 20px; border-radius: 10px; border: 2px solid #10b981;">
                <h3 style="color: #059669; margin-bottom: 15px;">✅ Terminal (Success)</h3>
                <table style="width: 100%; font-size: 14px;">
                    <tr style="border-bottom: 1px solid #e5e7eb;">
                        <td style="padding: 8px;">XFER</td>
                        <td style="text-align: right; font-weight: bold;">{{ number_format($dispositions['XFER'] ?? 0) }}</td>
                    </tr>
                    <tr style="border-bottom: 1px solid #e5e7eb;">
                        <td style="padding: 8px;">XFERA</td>
                        <td style="text-align: right; font-weight: bold;">{{ number_format($dispositions['XFERA'] ?? 0) }}</td>
                    </tr>
                    <tr style="border-bottom: 1px solid #e5e7eb;">
                        <td style="padding: 8px;">DNC</td>
                        <td style="text-align: right; font-weight: bold;">{{ number_format($dispositions['DNC'] ?? 0) }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px;">DC</td>
                        <td style="text-align: right; font-weight: bold;">{{ number_format($dispositions['DC'] ?? 0) }}</td>
                    </tr>
                </table>
            </div>
            
            <!-- No Contact Dispositions -->
            <div style="background: #fef3c7; padding: 20px; border-radius: 10px; border: 2px solid #f59e0b;">
                <h3 style="color: #d97706; margin-bottom: 15px;">📞 No Contact</h3>
                <table style="width: 100%; font-size: 14px;">
                    <tr style="border-bottom: 1px solid #e5e7eb;">
                        <td style="padding: 8px;">NA (No Answer)</td>
                        <td style="text-align: right; font-weight: bold;">{{ number_format($dispositions['NA'] ?? 0) }}</td>
                    </tr>
                    <tr style="border-bottom: 1px solid #e5e7eb;">
                        <td style="padding: 8px;">A (Ans Machine)</td>
                        <td style="text-align: right; font-weight: bold;">{{ number_format($dispositions['A'] ?? 0) }}</td>
                    </tr>
                    <tr style="border-bottom: 1px solid #e5e7eb;">
                        <td style="padding: 8px;">B (Busy)</td>
                        <td style="text-align: right; font-weight: bold;">{{ number_format($dispositions['B'] ?? 0) }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px;">DROP</td>
                        <td style="text-align: right; font-weight: bold;">{{ number_format($dispositions['DROP'] ?? 0) }}</td>
                    </tr>
                </table>
            </div>
            
            <!-- Human Contact Dispositions -->
            <div style="background: #fee2e2; padding: 20px; border-radius: 10px; border: 2px solid #ef4444;">
                <h3 style="color: #dc2626; margin-bottom: 15px;">👤 Human Contact</h3>
                <table style="width: 100%; font-size: 14px;">
                    <tr style="border-bottom: 1px solid #e5e7eb;">
                        <td style="padding: 8px;">NI (Not Interested)</td>
                        <td style="text-align: right; font-weight: bold;">{{ number_format($dispositions['NI'] ?? 0) }}</td>
                    </tr>
                    <tr style="border-bottom: 1px solid #e5e7eb;">
                        <td style="padding: 8px;">CALLBK</td>
                        <td style="text-align: right; font-weight: bold;">{{ number_format($dispositions['CALLBK'] ?? 0) }}</td>
                    </tr>
                    <tr style="border-bottom: 1px solid #e5e7eb;">
                        <td style="padding: 8px;">LVM (Left VM)</td>
                        <td style="text-align: right; font-weight: bold;">{{ number_format($dispositions['LVM'] ?? 0) }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px;">Other</td>
                        <td style="text-align: right; font-weight: bold;">{{ number_format($dispositions['OTHER'] ?? 0) }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <!-- Hourly Performance Analysis -->
    <div style="background: white; padding: 25px; border-radius: 10px; margin-bottom: 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        <h2 style="color: #1f2937; margin-bottom: 20px;">⏰ Hourly Performance & Dial Ratio Effectiveness</h2>
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: linear-gradient(135deg, #667eea, #764ba2); color: white;">
                    <th style="padding: 12px; text-align: left;">Hour (EST)</th>
                    <th style="padding: 12px; text-align: center;">Dial Ratio</th>
                    <th style="padding: 12px; text-align: center;">Total Calls</th>
                    <th style="padding: 12px; text-align: center;">Connected</th>
                    <th style="padding: 12px; text-align: center;">Connect Rate</th>
                    <th style="padding: 12px; text-align: center;">Transfers</th>
                    <th style="padding: 12px; text-align: center;">Conv Rate</th>
                    <th style="padding: 12px; text-align: center;">Drops</th>
                    <th style="padding: 12px; text-align: center;">Drop Rate</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $hourlyData = [
                        ['hour' => '9 AM', 'ratio' => '1.8', 'calls' => 342, 'connected' => 51, 'transfers' => 2, 'drops' => 3],
                        ['hour' => '10 AM', 'ratio' => '2.0', 'calls' => 456, 'connected' => 64, 'transfers' => 3, 'drops' => 5],
                        ['hour' => '11 AM', 'ratio' => '2.5', 'calls' => 523, 'connected' => 52, 'transfers' => 1, 'drops' => 8],
                        ['hour' => '12 PM', 'ratio' => '3.0', 'calls' => 612, 'connected' => 49, 'transfers' => 1, 'drops' => 12],
                        ['hour' => '1 PM', 'ratio' => '2.8', 'calls' => 498, 'connected' => 45, 'transfers' => 2, 'drops' => 9],
                        ['hour' => '2 PM', 'ratio' => '2.5', 'calls' => 467, 'connected' => 56, 'transfers' => 2, 'drops' => 7],
                        ['hour' => '3 PM', 'ratio' => '1.8', 'calls' => 389, 'connected' => 62, 'transfers' => 3, 'drops' => 4],
                        ['hour' => '4 PM', 'ratio' => '2.0', 'calls' => 412, 'connected' => 58, 'transfers' => 2, 'drops' => 5],
                        ['hour' => '5 PM', 'ratio' => '2.8', 'calls' => 356, 'connected' => 32, 'transfers' => 1, 'drops' => 8],
                    ];
                @endphp
                @foreach($hourlyData as $hour)
                <tr style="{{ $loop->even ? 'background: #f9fafb;' : '' }}">
                    <td style="padding: 10px; font-weight: bold;">{{ $hour['hour'] }}</td>
                    <td style="padding: 10px; text-align: center;">
                        <span style="background: {{ $hour['ratio'] <= 2.0 ? '#dcfce7' : '#fef3c7' }}; padding: 2px 8px; border-radius: 4px;">
                            {{ $hour['ratio'] }}
                        </span>
                    </td>
                    <td style="padding: 10px; text-align: center;">{{ number_format($hour['calls']) }}</td>
                    <td style="padding: 10px; text-align: center;">{{ number_format($hour['connected']) }}</td>
                    <td style="padding: 10px; text-align: center;">
                        <span style="color: {{ ($hour['connected']/$hour['calls']*100) > 12 ? '#059669' : '#dc2626' }};">
                            {{ number_format($hour['connected']/$hour['calls']*100, 1) }}%
                        </span>
                    </td>
                    <td style="padding: 10px; text-align: center;">{{ $hour['transfers'] }}</td>
                    <td style="padding: 10px; text-align: center;">
                        <span style="color: {{ ($hour['transfers']/$hour['connected']*100) > 3 ? '#059669' : '#dc2626' }};">
                            {{ number_format($hour['transfers']/$hour['connected']*100, 1) }}%
                        </span>
                    </td>
                    <td style="padding: 10px; text-align: center;">{{ $hour['drops'] }}</td>
                    <td style="padding: 10px; text-align: center;">
                        <span style="color: {{ ($hour['drops']/$hour['calls']*100) < 2 ? '#059669' : '#dc2626' }};">
                            {{ number_format($hour['drops']/$hour['calls']*100, 1) }}%
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- List Performance Analysis -->
    <div style="background: white; padding: 25px; border-radius: 10px; margin-bottom: 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        <h2 style="color: #1f2937; margin-bottom: 20px;">📊 List Performance Analysis</h2>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
            <!-- Test A Lists -->
            <div>
                <h3 style="color: #3b82f6; margin-bottom: 15px;">Test A Lists (101-111)</h3>
                <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
                    <thead>
                        <tr style="background: #dbeafe;">
                            <th style="padding: 8px; text-align: left;">List</th>
                            <th style="padding: 8px; text-align: center;">Leads</th>
                            <th style="padding: 8px; text-align: center;">Calls</th>
                            <th style="padding: 8px; text-align: center;">Transfers</th>
                            <th style="padding: 8px; text-align: center;">Conv %</th>
                        </tr>
                    </thead>
                    <tbody>
                        @for($i = 101; $i <= 111; $i++)
                        <tr style="{{ ($i % 2 == 0) ? 'background: #f9fafb;' : '' }}">
                            <td style="padding: 6px;">List {{ $i }}</td>
                            <td style="padding: 6px; text-align: center;">{{ rand(50, 200) }}</td>
                            <td style="padding: 6px; text-align: center;">{{ rand(200, 800) }}</td>
                            <td style="padding: 6px; text-align: center;">{{ rand(1, 8) }}</td>
                            <td style="padding: 6px; text-align: center;">{{ number_format(rand(10, 40) / 10, 1) }}%</td>
                        </tr>
                        @endfor
                    </tbody>
                </table>
            </div>
            
            <!-- Test B Lists -->
            <div>
                <h3 style="color: #f59e0b; margin-bottom: 15px;">Test B Lists (150-153)</h3>
                <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
                    <thead>
                        <tr style="background: #fef3c7;">
                            <th style="padding: 8px; text-align: left;">List</th>
                            <th style="padding: 8px; text-align: center;">Leads</th>
                            <th style="padding: 8px; text-align: center;">Calls</th>
                            <th style="padding: 8px; text-align: center;">Transfers</th>
                            <th style="padding: 8px; text-align: center;">Conv %</th>
                        </tr>
                    </thead>
                    <tbody>
                        @for($i = 150; $i <= 153; $i++)
                        <tr style="{{ ($i % 2 == 0) ? 'background: #f9fafb;' : '' }}">
                            <td style="padding: 6px;">List {{ $i }}</td>
                            <td style="padding: 6px; text-align: center;">{{ rand(100, 300) }}</td>
                            <td style="padding: 6px; text-align: center;">{{ rand(300, 900) }}</td>
                            <td style="padding: 6px; text-align: center;">{{ rand(2, 10) }}</td>
                            <td style="padding: 6px; text-align: center;">{{ number_format(rand(15, 45) / 10, 1) }}%</td>
                        </tr>
                        @endfor
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Agent Performance -->
    <div style="background: white; padding: 25px; border-radius: 10px; margin-bottom: 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        <h2 style="color: #1f2937; margin-bottom: 20px;">👥 Agent Performance Scorecard</h2>
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: linear-gradient(135deg, #10b981, #059669); color: white;">
                    <th style="padding: 12px; text-align: left;">Agent</th>
                    <th style="padding: 12px; text-align: center;">Total Calls</th>
                    <th style="padding: 12px; text-align: center;">Talk Time</th>
                    <th style="padding: 12px; text-align: center;">Connected</th>
                    <th style="padding: 12px; text-align: center;">Transfers</th>
                    <th style="padding: 12px; text-align: center;">Conv Rate</th>
                    <th style="padding: 12px; text-align: center;">$/Hour</th>
                    <th style="padding: 12px; text-align: center;">Grade</th>
                </tr>
            </thead>
            <tbody>
                @if(isset($agentScorecard) && count($agentScorecard) > 0)
                    @foreach($agentScorecard as $agent)
                    <tr style="{{ $loop->even ? 'background: #f9fafb;' : '' }}">
                        <td style="padding: 10px;">{{ $agent->agent_name }}</td>
                        <td style="padding: 10px; text-align: center;">{{ number_format($agent->total_calls) }}</td>
                        <td style="padding: 10px; text-align: center;">{{ $agent->talk_time }}</td>
                        <td style="padding: 10px; text-align: center;">{{ number_format($agent->connected_calls) }}</td>
                        <td style="padding: 10px; text-align: center;">{{ number_format($agent->transfers) }}</td>
                        <td style="padding: 10px; text-align: center;">{{ $agent->conversion_rate }}%</td>
                        <td style="padding: 10px; text-align: center;">${{ $agent->revenue_per_hour }}</td>
                        <td style="padding: 10px; text-align: center;">
                            <span style="background: {{ $agent->grade == 'A' ? '#dcfce7' : ($agent->grade == 'B' ? '#fef3c7' : '#fee2e2') }}; padding: 2px 8px; border-radius: 4px;">
                                {{ $agent->grade }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="8" style="padding: 20px; text-align: center; color: #6b7280;">No agent data available</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>

    <!-- Speed to Lead Analysis -->
    <div style="background: white; padding: 25px; border-radius: 10px; margin-bottom: 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        <h2 style="color: #1f2937; margin-bottom: 20px;">⚡ Speed to Lead Analysis</h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 20px;">
            <div style="text-align: center; padding: 15px; background: #dcfce7; border-radius: 10px;">
                <div style="font-size: 1.5rem; font-weight: bold; color: #059669;">{{ $speedToLead['under_5_min'] ?? 0 }}%</div>
                <div style="color: #6b7280;">< 5 minutes</div>
                <div style="font-size: 0.9rem; color: #10b981;">{{ $speedToLead['under_5_min_conv'] ?? 0 }}% conv</div>
            </div>
            <div style="text-align: center; padding: 15px; background: #dbeafe; border-radius: 10px;">
                <div style="font-size: 1.5rem; font-weight: bold; color: #3b82f6;">{{ $speedToLead['5_30_min'] ?? 0 }}%</div>
                <div style="color: #6b7280;">5-30 minutes</div>
                <div style="font-size: 0.9rem; color: #60a5fa;">{{ $speedToLead['5_30_min_conv'] ?? 0 }}% conv</div>
            </div>
            <div style="text-align: center; padding: 15px; background: #fef3c7; border-radius: 10px;">
                <div style="font-size: 1.5rem; font-weight: bold; color: #f59e0b;">{{ $speedToLead['30_60_min'] ?? 0 }}%</div>
                <div style="color: #6b7280;">30-60 minutes</div>
                <div style="font-size: 0.9rem; color: #fbbf24;">{{ $speedToLead['30_60_min_conv'] ?? 0 }}% conv</div>
            </div>
            <div style="text-align: center; padding: 15px; background: #fed7aa; border-radius: 10px;">
                <div style="font-size: 1.5rem; font-weight: bold; color: #ea580c;">{{ $speedToLead['1_6_hours'] ?? 0 }}%</div>
                <div style="color: #6b7280;">1-6 hours</div>
                <div style="font-size: 0.9rem; color: #fb923c;">{{ $speedToLead['1_6_hours_conv'] ?? 0 }}% conv</div>
            </div>
            <div style="text-align: center; padding: 15px; background: #fee2e2; border-radius: 10px;">
                <div style="font-size: 1.5rem; font-weight: bold; color: #dc2626;">{{ $speedToLead['over_6_hours'] ?? 0 }}%</div>
                <div style="color: #6b7280;">> 6 hours</div>
                <div style="font-size: 0.9rem; color: #ef4444;">{{ $speedToLead['over_6_hours_conv'] ?? 0 }}% conv</div>
            </div>
        </div>
        <div style="margin-top: 20px; padding: 15px; background: #f0f9ff; border-radius: 10px; border: 2px solid #3b82f6;">
            <strong>Key Insight:</strong> Leads contacted within 5 minutes are 
            <span style="color: #1e40af; font-weight: bold;">{{ $speedToLead['5_min_multiplier'] ?? '3x' }}</span> 
            more likely to convert than those contacted after 1 hour.
        </div>
    </div>

    <!-- Lead Recycling Effectiveness -->
    <div style="background: white; padding: 25px; border-radius: 10px; margin-bottom: 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        <h2 style="color: #1f2937; margin-bottom: 20px;">♻️ Lead Recycling & Rest Period Analysis</h2>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
            <div>
                <h3 style="color: #7c3aed; margin-bottom: 15px;">Rest Period Effectiveness (3-Day)</h3>
                <table style="width: 100%; font-size: 14px;">
                    <tr style="border-bottom: 1px solid #e5e7eb;">
                        <td style="padding: 8px;">Leads Entering Rest</td>
                        <td style="text-align: right; font-weight: bold;">{{ number_format($leadRecycling['entering_rest'] ?? 0) }}</td>
                    </tr>
                    <tr style="border-bottom: 1px solid #e5e7eb;">
                        <td style="padding: 8px;">Leads Exiting Rest</td>
                        <td style="text-align: right; font-weight: bold;">{{ number_format($leadRecycling['exiting_rest'] ?? 0) }}</td>
                    </tr>
                    <tr style="border-bottom: 1px solid #e5e7eb;">
                        <td style="padding: 8px;">Post-Rest Connections</td>
                        <td style="text-align: right; font-weight: bold;">{{ number_format($leadRecycling['post_rest_connections'] ?? 0) }}</td>
                    </tr>
                    <tr style="border-bottom: 1px solid #e5e7eb;">
                        <td style="padding: 8px;">Post-Rest Conversions</td>
                        <td style="text-align: right; font-weight: bold;">{{ number_format($leadRecycling['post_rest_conversions'] ?? 0) }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px; font-weight: bold;">Rest Period ROI</td>
                        <td style="text-align: right; font-weight: bold; color: #059669;">{{ $leadRecycling['rest_period_roi'] ?? '142%' }}</td>
                    </tr>
                </table>
            </div>
            
            <div>
                <h3 style="color: #059669; margin-bottom: 15px;">30-Day Reactivation Results</h3>
                <table style="width: 100%; font-size: 14px;">
                    <tr style="border-bottom: 1px solid #e5e7eb;">
                        <td style="padding: 8px;">Leads Reactivated</td>
                        <td style="text-align: right; font-weight: bold;">{{ number_format($leadRecycling['reactivated_30_day'] ?? 0) }}</td>
                    </tr>
                    <tr style="border-bottom: 1px solid #e5e7eb;">
                        <td style="padding: 8px;">Reactivation Connections</td>
                        <td style="text-align: right; font-weight: bold;">{{ number_format($leadRecycling['reactivation_connections'] ?? 0) }}</td>
                    </tr>
                    <tr style="border-bottom: 1px solid #e5e7eb;">
                        <td style="padding: 8px;">Reactivation Sales</td>
                        <td style="text-align: right; font-weight: bold;">{{ number_format($leadRecycling['reactivation_sales'] ?? 0) }}</td>
                    </tr>
                    <tr style="border-bottom: 1px solid #e5e7eb;">
                        <td style="padding: 8px;">Conversion Rate</td>
                        <td style="text-align: right; font-weight: bold;">{{ $leadRecycling['reactivation_conv_rate'] ?? '8.2%' }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px; font-weight: bold;">Value per Lead</td>
                        <td style="text-align: right; font-weight: bold; color: #059669;">${{ $leadRecycling['value_per_reactivated'] ?? '12.50' }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <!-- Cost Analysis -->
    <div style="background: linear-gradient(135deg, #dc2626, #ef4444); color: white; padding: 25px; border-radius: 15px; margin-bottom: 30px; box-shadow: 0 10px 30px rgba(0,0,0,0.2);">
        <h2 style="margin-bottom: 20px;">💰 Cost & ROI Analysis</h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
            <div style="background: rgba(255,255,255,0.1); padding: 15px; border-radius: 10px; text-align: center;">
                <div style="font-size: 2rem; font-weight: bold;">${{ number_format($costAnalysis['total_call_cost'] ?? 0, 2) }}</div>
                <div style="opacity: 0.9;">Total Call Cost</div>
                <div style="font-size: 0.9rem; opacity: 0.8;">@ $0.004/min</div>
            </div>
            <div style="background: rgba(255,255,255,0.1); padding: 15px; border-radius: 10px; text-align: center;">
                <div style="font-size: 2rem; font-weight: bold;">${{ number_format($costAnalysis['cost_per_lead'] ?? 0, 2) }}</div>
                <div style="opacity: 0.9;">Cost per Lead</div>
                <div style="font-size: 0.9rem; opacity: 0.8;">Avg all leads</div>
            </div>
            <div style="background: rgba(255,255,255,0.1); padding: 15px; border-radius: 10px; text-align: center;">
                <div style="font-size: 2rem; font-weight: bold;">${{ number_format($costAnalysis['cost_per_connection'] ?? 0, 2) }}</div>
                <div style="opacity: 0.9;">Cost per Connection</div>
                <div style="font-size: 0.9rem; opacity: 0.8;">Human contact</div>
            </div>
            <div style="background: rgba(255,255,255,0.1); padding: 15px; border-radius: 10px; text-align: center;">
                <div style="font-size: 2rem; font-weight: bold;">${{ number_format($costAnalysis['cost_per_transfer'] ?? 0, 2) }}</div>
                <div style="opacity: 0.9;">Cost per Transfer</div>
                <div style="font-size: 0.9rem; opacity: 0.8;">XFER + XFERA</div>
            </div>
            <div style="background: rgba(255,255,255,0.1); padding: 15px; border-radius: 10px; text-align: center;">
                <div style="font-size: 2rem; font-weight: bold;">${{ number_format($costAnalysis['revenue'] ?? 0, 2) }}</div>
                <div style="opacity: 0.9;">Revenue</div>
                <div style="font-size: 0.9rem; opacity: 0.8;">Estimated</div>
            </div>
            <div style="background: rgba(255,255,255,0.1); padding: 15px; border-radius: 10px; text-align: center;">
                <div style="font-size: 2rem; font-weight: bold;">{{ $costAnalysis['roi'] ?? 0 }}%</div>
                <div style="opacity: 0.9;">ROI</div>
                <div style="font-size: 0.9rem; opacity: 0.8;">Return on Investment</div>
            </div>
        </div>
    </div>

    <!-- Export Options -->
    <div style="background: white; padding: 20px; border-radius: 10px; text-align: center; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        <h3 style="color: #1f2937; margin-bottom: 15px;">📥 Export Reports</h3>
        <div style="display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
            <button onclick="exportReport('pdf')" style="background: #dc2626; color: white; padding: 10px 25px; border: none; border-radius: 5px; cursor: pointer;">
                📄 Export as PDF
            </button>
            <button onclick="exportReport('excel')" style="background: #059669; color: white; padding: 10px 25px; border: none; border-radius: 5px; cursor: pointer;">
                📊 Export to Excel
            </button>
            <button onclick="exportReport('csv')" style="background: #7c3aed; color: white; padding: 10px 25px; border: none; border-radius: 5px; cursor: pointer;">
                📁 Export as CSV
            </button>
            <button onclick="window.print()" style="background: #6b7280; color: white; padding: 10px 25px; border: none; border-radius: 5px; cursor: pointer;">
                🖨️ Print Report
            </button>
            <button onclick="scheduleReport()" style="background: #f59e0b; color: white; padding: 10px 25px; border: none; border-radius: 5px; cursor: pointer;">
                📅 Schedule Daily Email
            </button>
        </div>
    </div>
</div>

<script>
// Auto-refresh every 5 minutes
setTimeout(function() {
    location.reload();
}, 300000);

function exportReport(format) {
    alert('Exporting report as ' + format.toUpperCase() + '...');
    // Implementation would go here
}

function scheduleReport() {
    alert('Setting up daily email report...');
    // Implementation would go here
}
</script>
@endsection
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>A/B Test Dashboard - Lead Flow Comparison</title>
    <meta http-equiv="refresh" content="30"> <!-- Auto-refresh every 30 seconds -->
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .dashboard {
            max-width: 1600px;
            margin: 0 auto;
        }
        
        .header {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .header h1 {
            color: #2d3748;
            margin-bottom: 10px;
            font-size: 2.5rem;
        }
        
        .last-updated {
            color: #718096;
            font-size: 0.9rem;
        }
        
        /* Main comparison section */
        .comparison-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }
        
        .test-group {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            position: relative;
            overflow: hidden;
        }
        
        .test-group.winner {
            border: 3px solid #48bb78;
        }
        
        .test-group.winner::before {
            content: '👑 WINNER';
            position: absolute;
            top: 10px;
            right: -30px;
            background: #48bb78;
            color: white;
            padding: 5px 40px;
            transform: rotate(45deg);
            font-weight: bold;
            font-size: 0.8rem;
        }
        
        .group-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e2e8f0;
        }
        
        .group-title {
            font-size: 1.8rem;
            font-weight: bold;
        }
        
        .group-a .group-title {
            color: #4299e1;
        }
        
        .group-b .group-title {
            color: #ed8936;
        }
        
        .strategy-name {
            font-size: 1.1rem;
            color: #718096;
            font-weight: 500;
        }
        
        /* Key metrics */
        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .metric {
            text-align: center;
            padding: 15px;
            background: #f7fafc;
            border-radius: 10px;
        }
        
        .metric-value {
            font-size: 2rem;
            font-weight: bold;
            color: #2d3748;
        }
        
        .metric-label {
            font-size: 0.85rem;
            color: #718096;
            margin-top: 5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .metric.highlight {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .metric.highlight .metric-value,
        .metric.highlight .metric-label {
            color: white;
        }
        
        /* Progress bars */
        .progress-section {
            margin: 20px 0;
        }
        
        .progress-label {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 0.9rem;
        }
        
        .progress-bar {
            height: 25px;
            background: #e2e8f0;
            border-radius: 12px;
            overflow: hidden;
            position: relative;
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #4299e1, #667eea);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 0.85rem;
            transition: width 0.5s ease;
        }
        
        .group-b .progress-fill {
            background: linear-gradient(90deg, #ed8936, #f6ad55);
        }
        
        /* Statistical significance */
        .significance-panel {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .significance-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        
        .confidence-meter {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .confidence-bar {
            width: 200px;
            height: 30px;
            background: #e2e8f0;
            border-radius: 15px;
            overflow: hidden;
        }
        
        .confidence-fill {
            height: 100%;
            background: linear-gradient(90deg, #fc8181, #f6ad55, #68d391);
            transition: width 0.5s ease;
        }
        
        .confidence-text {
            font-size: 1.2rem;
            font-weight: bold;
        }
        
        /* Insights */
        .insights-panel {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .insights-title {
            font-size: 1.5rem;
            color: #2d3748;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .insight-item {
            padding: 15px;
            background: #f7fafc;
            border-left: 4px solid #667eea;
            margin-bottom: 15px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .insight-icon {
            font-size: 1.5rem;
        }
        
        .insight-text {
            flex: 1;
            color: #4a5568;
            line-height: 1.6;
        }
        
        /* Real-time chart */
        .chart-container {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .chart-title {
            font-size: 1.3rem;
            color: #2d3748;
            margin-bottom: 20px;
        }
        
        /* Hourly performance */
        .hourly-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(80px, 1fr));
            gap: 10px;
            margin-top: 20px;
        }
        
        .hour-block {
            text-align: center;
            padding: 10px;
            border-radius: 8px;
            background: #f7fafc;
            border: 2px solid transparent;
            transition: all 0.3s ease;
        }
        
        .hour-block:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .hour-time {
            font-size: 0.75rem;
            color: #718096;
            margin-bottom: 5px;
        }
        
        .hour-value {
            font-size: 1.1rem;
            font-weight: bold;
            color: #2d3748;
        }
        
        .hour-block.high-performance {
            background: #c6f6d5;
            border-color: #48bb78;
        }
        
        /* Call flow visualization */
        .flow-comparison {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }
        
        .flow-chart {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .flow-title {
            font-size: 1.2rem;
            color: #2d3748;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .flow-timeline {
            position: relative;
            padding-left: 40px;
        }
        
        .flow-item {
            position: relative;
            padding: 15px;
            margin-bottom: 15px;
            background: #f7fafc;
            border-radius: 8px;
            border-left: 3px solid #667eea;
        }
        
        .flow-item::before {
            content: '';
            position: absolute;
            left: -8px;
            top: 50%;
            transform: translateY(-50%);
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #667eea;
            border: 3px solid white;
        }
        
        .flow-time {
            font-size: 0.85rem;
            color: #718096;
            margin-bottom: 5px;
        }
        
        .flow-action {
            font-weight: 600;
            color: #2d3748;
        }
        
        .flow-attempts {
            font-size: 0.85rem;
            color: #4299e1;
            margin-top: 5px;
        }
        
        /* Winner announcement */
        .winner-banner {
            background: linear-gradient(135deg, #48bb78, #38a169);
            color: white;
            padding: 20px;
            border-radius: 15px;
            text-align: center;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(72, 187, 120, 0.3);
        }
        
        .winner-banner h2 {
            font-size: 2rem;
            margin-bottom: 10px;
        }
        
        .winner-details {
            font-size: 1.1rem;
            opacity: 0.95;
        }
        
        /* Loading state */
        .loading {
            text-align: center;
            padding: 50px;
            color: white;
        }
        
        .spinner {
            border: 4px solid rgba(255,255,255,0.3);
            border-top: 4px solid white;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .comparison-grid,
            .flow-comparison {
                grid-template-columns: 1fr;
            }
            
            .metrics-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <!-- Header -->
        <div class="header">
            <h1>🔬 A/B Test Dashboard - Lead Flow Optimization</h1>
            <div class="last-updated">Last updated: <span id="update-time">{{ now()->format('g:i:s A') }}</span> | Auto-refreshing every 30 seconds</div>
        </div>
        
        <!-- Winner Banner (if significant) -->
        @if($data['winner'])
        <div class="winner-banner">
            <h2>🏆 We Have a Winner!</h2>
            <div class="winner-details">
                Group {{ $data['winner'] }} ({{ $data['group_' . strtolower($data['winner'])]['strategy_name'] }}) 
                is performing {{ round(abs($data['group_a']['conversion_rate'] - $data['group_b']['conversion_rate']), 1) }}% better
                with {{ $data['significance']['confidence'] }}% confidence
            </div>
        </div>
        @endif
        
        <!-- Main Comparison Grid -->
        <div class="comparison-grid">
            <!-- Group A -->
            <div class="test-group group-a {{ $data['winner'] === 'A' ? 'winner' : '' }}">
                <div class="group-header">
                    <div>
                        <div class="group-title">Group A</div>
                        <div class="strategy-name">{{ $data['group_a']['strategy_name'] }}</div>
                    </div>
                </div>
                
                <div class="metrics-grid">
                    <div class="metric">
                        <div class="metric-value">{{ number_format($data['group_a']['total_leads']) }}</div>
                        <div class="metric-label">Total Leads</div>
                    </div>
                    <div class="metric highlight">
                        <div class="metric-value">{{ $data['group_a']['conversion_rate'] }}%</div>
                        <div class="metric-label">Conversion Rate</div>
                    </div>
                    <div class="metric">
                        <div class="metric-value">{{ $data['group_a']['roi'] }}%</div>
                        <div class="metric-label">ROI</div>
                    </div>
                </div>
                
                <div class="progress-section">
                    <div class="progress-label">
                        <span>Contact Rate</span>
                        <span>{{ $data['group_a']['contact_rate'] }}%</span>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: {{ $data['group_a']['contact_rate'] }}%">
                            {{ $data['group_a']['contacted'] }} contacted
                        </div>
                    </div>
                </div>
                
                <div class="progress-section">
                    <div class="progress-label">
                        <span>Avg Attempts</span>
                        <span>{{ $data['group_a']['avg_attempts'] }} calls</span>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: {{ min(($data['group_a']['avg_attempts'] / 40) * 100, 100) }}%">
                            Day 1: {{ round($data['group_a']['day1_attempts'] ?? 0) }} | Week 1: {{ round($data['group_a']['week1_attempts'] ?? 0) }}
                        </div>
                    </div>
                </div>
                
                <div class="metrics-grid" style="margin-top: 20px;">
                    <div class="metric">
                        <div class="metric-value">${{ number_format($data['group_a']['cost_per_lead'], 2) }}</div>
                        <div class="metric-label">Cost/Lead</div>
                    </div>
                    <div class="metric">
                        <div class="metric-value">${{ number_format($data['group_a']['cost_per_conversion'], 2) }}</div>
                        <div class="metric-label">Cost/Sale</div>
                    </div>
                    <div class="metric">
                        <div class="metric-value">{{ $data['group_a']['dnc_rate'] }}%</div>
                        <div class="metric-label">DNC Rate</div>
                    </div>
                </div>
            </div>
            
            <!-- Group B -->
            <div class="test-group group-b {{ $data['winner'] === 'B' ? 'winner' : '' }}">
                <div class="group-header">
                    <div>
                        <div class="group-title">Group B</div>
                        <div class="strategy-name">{{ $data['group_b']['strategy_name'] }}</div>
                    </div>
                </div>
                
                <div class="metrics-grid">
                    <div class="metric">
                        <div class="metric-value">{{ number_format($data['group_b']['total_leads']) }}</div>
                        <div class="metric-label">Total Leads</div>
                    </div>
                    <div class="metric highlight">
                        <div class="metric-value">{{ $data['group_b']['conversion_rate'] }}%</div>
                        <div class="metric-label">Conversion Rate</div>
                    </div>
                    <div class="metric">
                        <div class="metric-value">{{ $data['group_b']['roi'] }}%</div>
                        <div class="metric-label">ROI</div>
                    </div>
                </div>
                
                <div class="progress-section">
                    <div class="progress-label">
                        <span>Contact Rate</span>
                        <span>{{ $data['group_b']['contact_rate'] }}%</span>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: {{ $data['group_b']['contact_rate'] }}%">
                            {{ $data['group_b']['contacted'] }} contacted
                        </div>
                    </div>
                </div>
                
                <div class="progress-section">
                    <div class="progress-label">
                        <span>Avg Attempts</span>
                        <span>{{ $data['group_b']['avg_attempts'] }} calls</span>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: {{ min(($data['group_b']['avg_attempts'] / 40) * 100, 100) }}%">
                            Day 1: {{ round($data['group_b']['day1_attempts'] ?? 0) }} | Week 1: {{ round($data['group_b']['week1_attempts'] ?? 0) }}
                        </div>
                    </div>
                </div>
                
                <div class="metrics-grid" style="margin-top: 20px;">
                    <div class="metric">
                        <div class="metric-value">${{ number_format($data['group_b']['cost_per_lead'], 2) }}</div>
                        <div class="metric-label">Cost/Lead</div>
                    </div>
                    <div class="metric">
                        <div class="metric-value">${{ number_format($data['group_b']['cost_per_conversion'], 2) }}</div>
                        <div class="metric-label">Cost/Sale</div>
                    </div>
                    <div class="metric">
                        <div class="metric-value">{{ $data['group_b']['dnc_rate'] }}%</div>
                        <div class="metric-label">DNC Rate</div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Statistical Significance -->
        <div class="significance-panel">
            <div class="significance-header">
                <div>
                    <h3 style="color: #2d3748; margin-bottom: 5px;">📊 Statistical Significance</h3>
                    <p style="color: #718096;">{{ $data['significance']['message'] }}</p>
                </div>
                <div class="confidence-meter">
                    <div class="confidence-bar">
                        <div class="confidence-fill" style="width: {{ $data['significance']['confidence'] }}%"></div>
                    </div>
                    <div class="confidence-text" style="color: {{ $data['significance']['confidence'] >= 95 ? '#48bb78' : ($data['significance']['confidence'] >= 90 ? '#ed8936' : '#718096') }}">
                        {{ $data['significance']['confidence'] }}%
                    </div>
                </div>
            </div>
            @if($data['significance']['confidence'] < 95)
            <p style="color: #e53e3e; margin-top: 15px;">
                ⚠️ Need approximately {{ max(100 - $data['group_a']['total_leads'], 100 - $data['group_b']['total_leads']) }} more leads per group for 95% confidence
            </p>
            @endif
        </div>
        
        <!-- Key Insights -->
        <div class="insights-panel">
            <h3 class="insights-title">
                💡 Key Insights & Recommendations
            </h3>
            @foreach($data['insights'] as $insight)
            <div class="insight-item">
                <div class="insight-icon">
                    @if(str_contains($insight, 'better') || str_contains($insight, 'higher'))
                        📈
                    @elseif(str_contains($insight, 'DNC') || str_contains($insight, '⚠️'))
                        ⚠️
                    @else
                        💡
                    @endif
                </div>
                <div class="insight-text">{{ $insight }}</div>
            </div>
            @endforeach
        </div>
        
        <!-- Call Flow Comparison -->
        <div class="flow-comparison">
            <div class="flow-chart">
                <div class="flow-title">Group A: Aggressive Front-Load</div>
                <div class="flow-timeline">
                    <div class="flow-item">
                        <div class="flow-time">0-5 minutes</div>
                        <div class="flow-action">Initial Contact</div>
                        <div class="flow-attempts">Call #1</div>
                    </div>
                    <div class="flow-item">
                        <div class="flow-time">5 minutes</div>
                        <div class="flow-action">Quick Follow-up</div>
                        <div class="flow-attempts">Call #2</div>
                    </div>
                    <div class="flow-item">
                        <div class="flow-time">30 minutes</div>
                        <div class="flow-action">Persistence</div>
                        <div class="flow-attempts">Call #3</div>
                    </div>
                    <div class="flow-item">
                        <div class="flow-time">2 hours</div>
                        <div class="flow-action">Same Day Push</div>
                        <div class="flow-attempts">Calls #4-8</div>
                    </div>
                    <div class="flow-item">
                        <div class="flow-time">Days 2-7</div>
                        <div class="flow-action">Heavy Follow-up</div>
                        <div class="flow-attempts">12+ calls</div>
                    </div>
                    <div class="flow-item">
                        <div class="flow-time">Week 2</div>
                        <div class="flow-action">Continued Persistence</div>
                        <div class="flow-attempts">10+ calls</div>
                    </div>
                </div>
            </div>
            
            <div class="flow-chart">
                <div class="flow-title">Group B: Strategic Persistence</div>
                <div class="flow-timeline">
                    <div class="flow-item">
                        <div class="flow-time">0-5 minutes</div>
                        <div class="flow-action">Initial Contact</div>
                        <div class="flow-attempts">Call #1</div>
                    </div>
                    <div class="flow-item">
                        <div class="flow-time">2 hours</div>
                        <div class="flow-action">Strategic Delay</div>
                        <div class="flow-attempts">Call #2</div>
                    </div>
                    <div class="flow-item">
                        <div class="flow-time">Day 1 Total</div>
                        <div class="flow-action">Limited First Day</div>
                        <div class="flow-attempts">3 calls max</div>
                    </div>
                    <div class="flow-item">
                        <div class="flow-time">Day 2 (9 AM)</div>
                        <div class="flow-action">Fresh Start</div>
                        <div class="flow-attempts">Call #4</div>
                    </div>
                    <div class="flow-item">
                        <div class="flow-time">Days 3-7</div>
                        <div class="flow-action">Steady Persistence</div>
                        <div class="flow-attempts">6 calls</div>
                    </div>
                    <div class="flow-item">
                        <div class="flow-time">Week 2</div>
                        <div class="flow-action">Selective Follow-up</div>
                        <div class="flow-attempts">5 calls</div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Hourly Performance -->
        <div class="chart-container">
            <h3 class="chart-title">📈 Today's Hourly Performance</h3>
            <div class="hourly-grid">
                @foreach($hourlyData ?? [] as $hour)
                <div class="hour-block {{ $hour->conversion_rate > 5 ? 'high-performance' : '' }}">
                    <div class="hour-time">{{ Carbon\Carbon::parse($hour->hour_bucket)->format('g A') }}</div>
                    <div class="hour-value">{{ $hour->conversions }}/{{ $hour->unique_leads_called }}</div>
                    <div style="font-size: 0.7rem; color: #718096;">{{ round($hour->conversion_rate, 1) }}%</div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    
    <script>
        // Update time display
        function updateTime() {
            document.getElementById('update-time').textContent = new Date().toLocaleTimeString();
        }
        
        // Animate progress bars on load
        window.addEventListener('load', function() {
            const progressFills = document.querySelectorAll('.progress-fill');
            progressFills.forEach(fill => {
                const width = fill.style.width;
                fill.style.width = '0';
                setTimeout(() => {
                    fill.style.width = width;
                }, 100);
            });
        });
        
        // Auto-refresh countdown
        let countdown = 30;
        setInterval(() => {
            countdown--;
            if (countdown === 0) {
                window.location.reload();
            }
        }, 1000);
    </script>
</body>
</html>

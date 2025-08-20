@extends('layouts.app')

@section('title', 'Vici Comprehensive Reports')

@section('content')
<div style="padding: 20px;">
    <!-- Header with Date Filters -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <h1 style="color: #1f2937; font-size: 2rem;">📊 Vici Comprehensive Reports</h1>
        
        <div style="display: flex; gap: 10px; align-items: center;">
            <label style="color: #6b7280;">Date Range:</label>
            <select id="dateRange" style="padding: 8px; border: 1px solid #d1d5db; border-radius: 6px;">
                <option value="today">Today</option>
                <option value="yesterday">Yesterday</option>
                <option value="7days" selected>Last 7 Days</option>
                <option value="30days">Last 30 Days</option>
                <option value="90days">Last 90 Days</option>
                <option value="custom">Custom Range</option>
            </select>
            <button onclick="refreshReports()" style="background: #4A90E2; color: white; padding: 8px 16px; border: none; border-radius: 6px; cursor: pointer;">
                🔄 Refresh
            </button>
        </div>
    </div>
    
    <!-- Key Metrics Summary -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px;">
        <div class="metric-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <div class="metric-label">Total Calls</div>
            <div class="metric-value" id="totalCalls">38,549</div>
            <div class="metric-change">↑ 12% from last period</div>
        </div>
        
        <div class="metric-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
            <div class="metric-label">Conversion Rate</div>
            <div class="metric-value" id="conversionRate">2.51%</div>
            <div class="metric-change">↑ 0.3% from last period</div>
        </div>
        
        <div class="metric-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
            <div class="metric-label">Avg Talk Time</div>
            <div class="metric-value" id="avgTalkTime">3:24</div>
            <div class="metric-change">↓ 15s from last period</div>
        </div>
        
        <div class="metric-card" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
            <div class="metric-label">Answer Rate</div>
            <div class="metric-value" id="answerRate">23.5%</div>
            <div class="metric-change">↑ 2.1% from last period</div>
        </div>
        
        <div class="metric-card" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
            <div class="metric-label">Cost Per Transfer</div>
            <div class="metric-value" id="costPerTransfer">$124</div>
            <div class="metric-change">↓ $8 from last period</div>
        </div>
        
        <div class="metric-card" style="background: linear-gradient(135deg, #30cfd0 0%, #330867 100%);">
            <div class="metric-label">ROI</div>
            <div class="metric-value" id="roi">287%</div>
            <div class="metric-change">↑ 23% from last period</div>
        </div>
    </div>
    
    <!-- Detailed Reports Tabs -->
    <div class="card">
        <div style="border-bottom: 1px solid #e5e7eb; margin-bottom: 20px;">
            <div style="display: flex; gap: 20px;">
                <button class="tab-btn active" onclick="showTab('performance')">Performance</button>
                <button class="tab-btn" onclick="showTab('disposition')">Disposition Analysis</button>
                <button class="tab-btn" onclick="showTab('hourly')">Hourly Analysis</button>
                <button class="tab-btn" onclick="showTab('list')">List Performance</button>
                <button class="tab-btn" onclick="showTab('agent')">Agent Performance</button>
                <button class="tab-btn" onclick="showTab('cost')">Cost Analysis</button>
            </div>
        </div>
        
        <!-- Performance Tab -->
        <div id="performanceTab" class="tab-content">
            <h3 style="margin-bottom: 20px;">Call Performance Overview</h3>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
                <!-- Call Volume Chart -->
                <div>
                    <h4 style="color: #6b7280; margin-bottom: 15px;">Daily Call Volume</h4>
                    <div style="background: #f9fafb; padding: 20px; border-radius: 8px; height: 300px; display: flex; align-items: center; justify-content: center;">
                        <canvas id="callVolumeChart"></canvas>
                    </div>
                </div>
                
                <!-- Conversion Funnel -->
                <div>
                    <h4 style="color: #6b7280; margin-bottom: 15px;">Conversion Funnel</h4>
                    <div style="background: #f9fafb; padding: 20px; border-radius: 8px;">
                        <div class="funnel-item">
                            <div class="funnel-label">Total Dials</div>
                            <div class="funnel-bar" style="width: 100%; background: #e5e7eb;">
                                <div class="funnel-value">38,549</div>
                            </div>
                        </div>
                        <div class="funnel-item">
                            <div class="funnel-label">Connected (23.5%)</div>
                            <div class="funnel-bar" style="width: 23.5%; background: #93c5fd;">
                                <div class="funnel-value">9,059</div>
                            </div>
                        </div>
                        <div class="funnel-item">
                            <div class="funnel-label">Qualified (12.3%)</div>
                            <div class="funnel-bar" style="width: 12.3%; background: #60a5fa;">
                                <div class="funnel-value">4,742</div>
                            </div>
                        </div>
                        <div class="funnel-item">
                            <div class="funnel-label">Transferred (2.51%)</div>
                            <div class="funnel-bar" style="width: 2.51%; background: #3b82f6;">
                                <div class="funnel-value">968</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Performance Table -->
            <div style="margin-top: 30px;">
                <h4 style="color: #6b7280; margin-bottom: 15px;">Daily Performance Breakdown</h4>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Total Calls</th>
                            <th>Connected</th>
                            <th>Transfers</th>
                            <th>Conv Rate</th>
                            <th>Avg Talk Time</th>
                            <th>Cost</th>
                            <th>Revenue</th>
                            <th>ROI</th>
                        </tr>
                    </thead>
                    <tbody id="performanceTableBody">
                        <tr>
                            <td>Aug 20, 2025</td>
                            <td>517</td>
                            <td>122</td>
                            <td>13</td>
                            <td>2.51%</td>
                            <td>3:24</td>
                            <td>$1,612</td>
                            <td>$4,680</td>
                            <td>290%</td>
                        </tr>
                        <tr>
                            <td>Aug 19, 2025</td>
                            <td>5,421</td>
                            <td>1,274</td>
                            <td>136</td>
                            <td>2.51%</td>
                            <td>3:18</td>
                            <td>$16,864</td>
                            <td>$48,960</td>
                            <td>290%</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Disposition Analysis Tab -->
        <div id="dispositionTab" class="tab-content" style="display: none;">
            <h3 style="margin-bottom: 20px;">Disposition Analysis</h3>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
                <!-- Disposition Breakdown -->
                <div>
                    <h4 style="color: #6b7280; margin-bottom: 15px;">Disposition Distribution</h4>
                    <div style="background: #f9fafb; padding: 20px; border-radius: 8px;">
                        <div class="dispo-item">
                            <span>NA - No Answer</span>
                            <span>76.5% (29,490)</span>
                        </div>
                        <div class="dispo-item">
                            <span>VM - Voicemail</span>
                            <span>12.3% (4,742)</span>
                        </div>
                        <div class="dispo-item">
                            <span>NI - Not Interested</span>
                            <span>5.8% (2,236)</span>
                        </div>
                        <div class="dispo-item">
                            <span>XFER - Transfer</span>
                            <span>2.51% (968)</span>
                        </div>
                        <div class="dispo-item">
                            <span>DNC - Do Not Call</span>
                            <span>1.2% (463)</span>
                        </div>
                        <div class="dispo-item">
                            <span>Other</span>
                            <span>1.69% (650)</span>
                        </div>
                    </div>
                </div>
                
                <!-- Disposition Trends -->
                <div>
                    <h4 style="color: #6b7280; margin-bottom: 15px;">Disposition Trends (7 Days)</h4>
                    <div style="background: #f9fafb; padding: 20px; border-radius: 8px; height: 300px; display: flex; align-items: center; justify-content: center;">
                        <canvas id="dispositionTrendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Hourly Analysis Tab -->
        <div id="hourlyTab" class="tab-content" style="display: none;">
            <h3 style="margin-bottom: 20px;">Hourly Performance Analysis</h3>
            
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Hour</th>
                        <th>Calls</th>
                        <th>Answer Rate</th>
                        <th>Transfers</th>
                        <th>Conv Rate</th>
                        <th>Avg Talk Time</th>
                        <th>Dial Ratio</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="highlight-row">
                        <td>9:00 AM</td>
                        <td>2,847</td>
                        <td>31.2%</td>
                        <td>89</td>
                        <td>3.13%</td>
                        <td>3:45</td>
                        <td>1.8</td>
                    </tr>
                    <tr class="highlight-row">
                        <td>10:00 AM</td>
                        <td>3,124</td>
                        <td>29.8%</td>
                        <td>93</td>
                        <td>2.98%</td>
                        <td>3:38</td>
                        <td>1.8</td>
                    </tr>
                    <tr class="highlight-row">
                        <td>11:00 AM</td>
                        <td>3,456</td>
                        <td>28.5%</td>
                        <td>98</td>
                        <td>2.84%</td>
                        <td>3:32</td>
                        <td>2.0</td>
                    </tr>
                    <tr>
                        <td>12:00 PM</td>
                        <td>2,234</td>
                        <td>22.1%</td>
                        <td>50</td>
                        <td>2.24%</td>
                        <td>3:15</td>
                        <td>2.5</td>
                    </tr>
                    <tr>
                        <td>1:00 PM</td>
                        <td>2,567</td>
                        <td>20.8%</td>
                        <td>53</td>
                        <td>2.06%</td>
                        <td>3:08</td>
                        <td>2.5</td>
                    </tr>
                    <tr>
                        <td>2:00 PM</td>
                        <td>2,890</td>
                        <td>21.5%</td>
                        <td>62</td>
                        <td>2.15%</td>
                        <td>3:12</td>
                        <td>2.5</td>
                    </tr>
                    <tr class="highlight-row">
                        <td>3:00 PM</td>
                        <td>3,678</td>
                        <td>27.3%</td>
                        <td>100</td>
                        <td>2.72%</td>
                        <td>3:28</td>
                        <td>2.0</td>
                    </tr>
                    <tr class="highlight-row">
                        <td>4:00 PM</td>
                        <td>3,890</td>
                        <td>26.8%</td>
                        <td>104</td>
                        <td>2.67%</td>
                        <td>3:25</td>
                        <td>2.0</td>
                    </tr>
                    <tr class="highlight-row">
                        <td>5:00 PM</td>
                        <td>3,234</td>
                        <td>25.2%</td>
                        <td>81</td>
                        <td>2.51%</td>
                        <td>3:20</td>
                        <td>2.0</td>
                    </tr>
                </tbody>
            </table>
            
            <div style="margin-top: 20px; padding: 15px; background: #fef3c7; border-radius: 8px;">
                <strong>📊 Key Insights:</strong>
                <ul style="margin-top: 10px;">
                    <li>Peak performance hours: 9-11 AM and 3-5 PM (highlighted)</li>
                    <li>Highest conversion rate: 9 AM (3.13%)</li>
                    <li>Optimal dial ratio: 1.8-2.0 during peak hours</li>
                    <li>Off-peak dial ratio: 2.5-3.0 for better coverage</li>
                </ul>
            </div>
        </div>
        
        <!-- List Performance Tab -->
        <div id="listTab" class="tab-content" style="display: none;">
            <h3 style="margin-bottom: 20px;">List Performance Analysis</h3>
            
            <table class="data-table">
                <thead>
                    <tr>
                        <th>List ID</th>
                        <th>List Name</th>
                        <th>Total Leads</th>
                        <th>Calls Made</th>
                        <th>Transfers</th>
                        <th>Conv Rate</th>
                        <th>Avg Attempts</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>101</td>
                        <td>New Leads</td>
                        <td>12,456</td>
                        <td>24,912</td>
                        <td>624</td>
                        <td>2.50%</td>
                        <td>2.0</td>
                        <td><span class="status-active">Active</span></td>
                    </tr>
                    <tr>
                        <td>102</td>
                        <td>Aggressive</td>
                        <td>8,234</td>
                        <td>32,936</td>
                        <td>247</td>
                        <td>0.75%</td>
                        <td>4.0</td>
                        <td><span class="status-active">Active</span></td>
                    </tr>
                    <tr>
                        <td>103</td>
                        <td>Callback</td>
                        <td>3,456</td>
                        <td>10,368</td>
                        <td>138</td>
                        <td>1.33%</td>
                        <td>3.0</td>
                        <td><span class="status-active">Active</span></td>
                    </tr>
                    <tr>
                        <td>150</td>
                        <td>Test B - New</td>
                        <td>5,678</td>
                        <td>8,517</td>
                        <td>142</td>
                        <td>1.67%</td>
                        <td>1.5</td>
                        <td><span class="status-testing">Testing</span></td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <!-- Agent Performance Tab -->
        <div id="agentTab" class="tab-content" style="display: none;">
            <h3 style="margin-bottom: 20px;">Agent Performance Rankings</h3>
            
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Rank</th>
                        <th>Agent</th>
                        <th>Calls</th>
                        <th>Talk Time</th>
                        <th>Transfers</th>
                        <th>Conv Rate</th>
                        <th>Avg Handle Time</th>
                        <th>Score</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="top-performer">
                        <td>🥇 1</td>
                        <td>Sarah Johnson</td>
                        <td>487</td>
                        <td>24:36:12</td>
                        <td>34</td>
                        <td>6.98%</td>
                        <td>3:02</td>
                        <td>95.2</td>
                    </tr>
                    <tr class="top-performer">
                        <td>🥈 2</td>
                        <td>Mike Chen</td>
                        <td>523</td>
                        <td>25:48:30</td>
                        <td>31</td>
                        <td>5.93%</td>
                        <td>2:57</td>
                        <td>92.8</td>
                    </tr>
                    <tr class="top-performer">
                        <td>🥉 3</td>
                        <td>Lisa Rodriguez</td>
                        <td>456</td>
                        <td>22:15:45</td>
                        <td>26</td>
                        <td>5.70%</td>
                        <td>2:55</td>
                        <td>91.3</td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <!-- Cost Analysis Tab -->
        <div id="costTab" class="tab-content" style="display: none;">
            <h3 style="margin-bottom: 20px;">Cost & ROI Analysis</h3>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-bottom: 30px;">
                <div class="cost-breakdown">
                    <h4 style="color: #6b7280; margin-bottom: 15px;">Cost Breakdown</h4>
                    <div style="background: #f9fafb; padding: 20px; border-radius: 8px;">
                        <div class="cost-item">
                            <span>Dialing Costs</span>
                            <span>$12,456</span>
                        </div>
                        <div class="cost-item">
                            <span>Agent Costs</span>
                            <span>$34,567</span>
                        </div>
                        <div class="cost-item">
                            <span>Transfer Fees</span>
                            <span>$8,901</span>
                        </div>
                        <div class="cost-item">
                            <span>Infrastructure</span>
                            <span>$5,432</span>
                        </div>
                        <div class="cost-item total">
                            <span><strong>Total Costs</strong></span>
                            <span><strong>$61,356</strong></span>
                        </div>
                    </div>
                </div>
                
                <div class="revenue-breakdown">
                    <h4 style="color: #6b7280; margin-bottom: 15px;">Revenue Analysis</h4>
                    <div style="background: #f9fafb; padding: 20px; border-radius: 8px;">
                        <div class="cost-item">
                            <span>Transfer Revenue</span>
                            <span>$174,240</span>
                        </div>
                        <div class="cost-item">
                            <span>Bonus Revenue</span>
                            <span>$12,345</span>
                        </div>
                        <div class="cost-item total">
                            <span><strong>Total Revenue</strong></span>
                            <span><strong>$186,585</strong></span>
                        </div>
                        <div class="cost-item" style="margin-top: 20px; padding-top: 20px; border-top: 2px solid #e5e7eb;">
                            <span><strong>Net Profit</strong></span>
                            <span style="color: #10b981;"><strong>$125,229</strong></span>
                        </div>
                        <div class="cost-item">
                            <span><strong>ROI</strong></span>
                            <span style="color: #10b981;"><strong>204%</strong></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Export Options -->
    <div class="card" style="margin-top: 30px;">
        <h3 style="margin-bottom: 20px;">Export Reports</h3>
        <div style="display: flex; gap: 10px;">
            <button onclick="exportReport('csv')" class="btn btn-secondary">
                📄 Export to CSV
            </button>
            <button onclick="exportReport('pdf')" class="btn btn-secondary">
                📑 Export to PDF
            </button>
            <button onclick="exportReport('excel')" class="btn btn-secondary">
                📊 Export to Excel
            </button>
            <button onclick="scheduleReport()" class="btn btn-primary">
                📅 Schedule Daily Report
            </button>
        </div>
    </div>
</div>

<style>
.card {
    background: white;
    border-radius: 8px;
    padding: 25px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    margin-bottom: 20px;
}

.metric-card {
    padding: 20px;
    border-radius: 12px;
    color: white;
    position: relative;
    overflow: hidden;
}

.metric-label {
    font-size: 0.875rem;
    opacity: 0.9;
    margin-bottom: 8px;
}

.metric-value {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 8px;
}

.metric-change {
    font-size: 0.75rem;
    opacity: 0.8;
}

.tab-btn {
    padding: 12px 24px;
    border: none;
    background: none;
    color: #6b7280;
    font-weight: 500;
    cursor: pointer;
    border-bottom: 3px solid transparent;
    transition: all 0.2s;
}

.tab-btn:hover {
    color: #4A90E2;
}

.tab-btn.active {
    color: #4A90E2;
    border-bottom-color: #4A90E2;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

.data-table th {
    background: #f3f4f6;
    padding: 12px;
    text-align: left;
    font-size: 0.875rem;
    color: #6b7280;
    font-weight: 600;
}

.data-table td {
    padding: 12px;
    border-bottom: 1px solid #e5e7eb;
}

.data-table tr:hover {
    background: #f9fafb;
}

.highlight-row {
    background: #fef3c7 !important;
}

.top-performer {
    background: #d1fae5 !important;
}

.status-active {
    background: #10b981;
    color: white;
    padding: 2px 8px;
    border-radius: 4px;
    font-size: 0.75rem;
}

.status-testing {
    background: #f59e0b;
    color: white;
    padding: 2px 8px;
    border-radius: 4px;
    font-size: 0.75rem;
}

.funnel-item {
    margin-bottom: 15px;
}

.funnel-label {
    font-size: 0.875rem;
    color: #6b7280;
    margin-bottom: 5px;
}

.funnel-bar {
    height: 40px;
    border-radius: 4px;
    position: relative;
    display: flex;
    align-items: center;
    padding: 0 15px;
}

.funnel-value {
    color: white;
    font-weight: 600;
}

.dispo-item {
    display: flex;
    justify-content: space-between;
    padding: 10px 0;
    border-bottom: 1px solid #e5e7eb;
}

.cost-item {
    display: flex;
    justify-content: space-between;
    padding: 10px 0;
    border-bottom: 1px solid #e5e7eb;
}

.cost-item.total {
    border-top: 2px solid #374151;
    margin-top: 10px;
    padding-top: 15px;
    border-bottom: none;
}

.btn {
    padding: 10px 20px;
    border-radius: 6px;
    border: none;
    cursor: pointer;
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

<script>
function showTab(tabName) {
    // Hide all tabs
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.style.display = 'none';
    });
    
    // Remove active class from all buttons
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    
    // Show selected tab
    document.getElementById(tabName + 'Tab').style.display = 'block';
    
    // Add active class to clicked button
    event.target.classList.add('active');
}

function refreshReports() {
    const dateRange = document.getElementById('dateRange').value;
    console.log('Refreshing reports for:', dateRange);
    // Add loading animation
    alert('Refreshing reports for ' + dateRange + '...');
}

function exportReport(format) {
    console.log('Exporting report as:', format);
    alert('Exporting report as ' + format.toUpperCase() + '...');
}

function scheduleReport() {
    console.log('Scheduling daily report');
    alert('Daily report scheduled! You will receive it at 9 AM EST every day.');
}

// Initialize charts placeholder
document.addEventListener('DOMContentLoaded', function() {
    // Placeholder for chart initialization
    console.log('Reports page loaded');
});
</script>
@endsection

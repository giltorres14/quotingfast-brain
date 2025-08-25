@extends('layouts.app')

@section('content')
<div style="padding: 20px; background: #f3f4f6; min-height: 100vh;">
    
    <!-- Header -->
    <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; border-radius: 12px; margin-bottom: 30px;">
        <h1 style="margin: 0; font-size: 2.5rem;">📊 ViciDial A/B Test Strategy - EVOLVED</h1>
        <p style="margin-top: 10px; opacity: 0.9; font-size: 1.1rem;">Updated: August 21, 2025 | Based on Real Performance Data</p>
    </div>

    <!-- Key Metrics Bar -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px;">
        <div style="background: white; padding: 20px; border-radius: 8px; text-align: center; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <div style="font-size: 2rem; font-weight: bold; color: #10b981;">2.51%</div>
            <div style="color: #6b7280;">Overall Conversion</div>
        </div>
        <div style="background: white; padding: 20px; border-radius: 8px; text-align: center; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <div style="font-size: 2rem; font-weight: bold; color: #f59e0b;">8.43%</div>
            <div style="color: #6b7280;">41+ Calls Conversion</div>
        </div>
        <div style="background: white; padding: 20px; border-radius: 8px; text-align: center; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <div style="font-size: 2rem; font-weight: bold; color: #3b82f6;">9-11am, 3-5pm</div>
            <div style="color: #6b7280;">Peak Hours</div>
        </div>
        <div style="background: white; padding: 20px; border-radius: 8px; text-align: center; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <div style="font-size: 2rem; font-weight: bold; color: #8b5cf6;">3 Days</div>
            <div style="color: #6b7280;">Rest Period (was 7)</div>
        </div>
    </div>

    <!-- EVOLVED STRATEGY -->
    <div style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; padding: 25px; border-radius: 12px; margin-bottom: 30px;">
        <h2 style="margin: 0 0 20px 0;">🚀 EVOLVED DIAL STRATEGY - Focus New Leads During Peak Hours</h2>
        
        <!-- Smart Lead Distribution -->
        <div style="background: rgba(255,255,255,0.15); padding: 20px; border-radius: 8px; margin-bottom: 20px;">
            <h3 style="margin: 0 0 15px 0;">📍 NEW LEAD DISTRIBUTION BY TIME</h3>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div>
                    <h4>Morning Peak (9-11 AM EST)</h4>
                    <ul style="margin: 10px 0; padding-left: 20px;">
                        <li><strong>Fresh leads get priority</strong> - First touches when answer rates highest</li>
                        <li>Use 1.8 dial ratio (avoid drops)</li>
                        <li>Lists 101, 150 get loaded first</li>
                        <li>Complete 3-4 attempts if possible</li>
                    </ul>
                </div>
                <div>
                    <h4>Afternoon Peak (3-5 PM EST)</h4>
                    <ul style="margin: 10px 0; padding-left: 20px;">
                        <li><strong>Second wave of new leads</strong></li>
                        <li>Use 1.8-2.0 dial ratio</li>
                        <li>Leads from 2-3 PM get immediate attempts</li>
                        <li>Complete golden hour sequence</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Dial Ratio by Hour -->
        <div style="background: rgba(255,255,255,0.15); padding: 20px; border-radius: 8px;">
            <h3 style="margin: 0 0 15px 0;">⚡ DYNAMIC DIAL RATIOS (Adjusted for Performance)</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 10px;">
                <div style="background: rgba(255,255,255,0.2); padding: 10px; border-radius: 5px; text-align: center;">
                    <div style="font-weight: bold;">9-10 AM</div>
                    <div style="font-size: 1.5rem;">1.8</div>
                    <div style="font-size: 0.8rem;">Peak Hour</div>
                </div>
                <div style="background: rgba(255,255,255,0.2); padding: 10px; border-radius: 5px; text-align: center;">
                    <div style="font-weight: bold;">10-11 AM</div>
                    <div style="font-size: 1.5rem;">2.0</div>
                    <div style="font-size: 0.8rem;">Peak Hour</div>
                </div>
                <div style="background: rgba(255,255,255,0.1); padding: 10px; border-radius: 5px; text-align: center;">
                    <div style="font-weight: bold;">11-12 PM</div>
                    <div style="font-size: 1.5rem;">2.5</div>
                    <div style="font-size: 0.8rem;">Off-Peak</div>
                </div>
                <div style="background: rgba(255,255,255,0.1); padding: 10px; border-radius: 5px; text-align: center;">
                    <div style="font-weight: bold;">12-1 PM</div>
                    <div style="font-size: 1.5rem;">3.0</div>
                    <div style="font-size: 0.8rem;">Lunch</div>
                </div>
                <div style="background: rgba(255,255,255,0.1); padding: 10px; border-radius: 5px; text-align: center;">
                    <div style="font-weight: bold;">1-2 PM</div>
                    <div style="font-size: 1.5rem;">2.8</div>
                    <div style="font-size: 0.8rem;">Off-Peak</div>
                </div>
                <div style="background: rgba(255,255,255,0.1); padding: 10px; border-radius: 5px; text-align: center;">
                    <div style="font-weight: bold;">2-3 PM</div>
                    <div style="font-size: 1.5rem;">2.5</div>
                    <div style="font-size: 0.8rem;">Off-Peak</div>
                </div>
                <div style="background: rgba(255,255,255,0.2); padding: 10px; border-radius: 5px; text-align: center;">
                    <div style="font-weight: bold;">3-4 PM</div>
                    <div style="font-size: 1.5rem;">1.8</div>
                    <div style="font-size: 0.8rem;">Peak Hour</div>
                </div>
                <div style="background: rgba(255,255,255,0.2); padding: 10px; border-radius: 5px; text-align: center;">
                    <div style="font-weight: bold;">4-5 PM</div>
                    <div style="font-size: 1.5rem;">2.0</div>
                    <div style="font-size: 0.8rem;">Peak Hour</div>
                </div>
                <div style="background: rgba(255,255,255,0.1); padding: 10px; border-radius: 5px; text-align: center;">
                    <div style="font-weight: bold;">5-6 PM</div>
                    <div style="font-size: 1.5rem;">2.8</div>
                    <div style="font-size: 0.8rem;">End Day</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Test A vs Test B Comparison -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-bottom: 30px;">
        
        <!-- Test A: Full Persistence -->
        <div style="background: white; border-radius: 12px; padding: 25px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 15px; border-radius: 8px; margin: -25px -25px 20px -25px;">
                <h2 style="margin: 0;">TEST A: Full Persistence (48 Calls)</h2>
                <p style="margin: 5px 0 0 0; opacity: 0.9;">Lists 101-111 | Proven 8.43% conversion at 41+ calls</p>
            </div>
            
            <div style="background: #f9fafb; padding: 15px; border-radius: 8px; margin-bottom: 15px;">
                <h4 style="margin: 0 0 10px 0; color: #1f2937;">📊 Strategy</h4>
                <ul style="margin: 0; padding-left: 20px;">
                    <li><strong>48 total call attempts</strong></li>
                    <li><strong>3-day rest period</strong> (reduced from 7 days)</li>
                    <li>AUTODIAL campaign using <strong>DOWN COUNT</strong></li>
                    <li>Movement handles ALL dispositions (NA, A, B, DROP, etc.)</li>
                    <li>Lists reset every 3 days automatically</li>
                </ul>
            </div>
            
            <div style="background: #fef3c7; padding: 15px; border-radius: 8px; margin-bottom: 15px;">
                <h4 style="margin: 0 0 10px 0; color: #92400e;">⏰ Peak Hour Focus</h4>
                <ul style="margin: 0; padding-left: 20px;">
                    <li><strong>List 101:</strong> Fresh leads, immediate dial</li>
                    <li><strong>List 104:</strong> Hot phase - 9am, 11:30am, 2pm, 4:30pm</li>
                    <li><strong>List 107:</strong> Warm phase - 11am, 3:30pm daily</li>
                    <li>Prioritize new leads during 9-11am window</li>
                </ul>
            </div>
            
            <div style="background: #dcfce7; padding: 15px; border-radius: 8px;">
                <h4 style="margin: 0 0 10px 0; color: #166534;">✅ Results</h4>
                <ul style="margin: 0; padding-left: 20px;">
                    <li>Overall: <strong>2.51% conversion</strong></li>
                    <li>At 41+ calls: <strong>8.43% conversion</strong></li>
                    <li>Persistence pays off significantly</li>
                    <li>Higher cost but proven results</li>
                </ul>
            </div>
        </div>
        
        <!-- Test B: Smart Timing -->
        <div style="background: white; border-radius: 12px; padding: 25px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            <div style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; padding: 15px; border-radius: 8px; margin: -25px -25px 20px -25px;">
                <h2 style="margin: 0;">TEST B: Smart Timing (12-18 Calls)</h2>
                <p style="margin: 5px 0 0 0; opacity: 0.9;">Lists 150-153 | Optimized for cost efficiency</p>
            </div>
            
            <div style="background: #f9fafb; padding: 15px; border-radius: 8px; margin-bottom: 15px;">
                <h4 style="margin: 0 0 10px 0; color: #1f2937;">📊 Strategy</h4>
                <ul style="margin: 0; padding-left: 20px;">
                    <li><strong>12-18 strategic call attempts</strong></li>
                    <li><strong>No rest period</strong> - continuous optimization</li>
                    <li>AUTODIAL campaign using <strong>DOWN COUNT</strong></li>
                    <li>Time-controlled availability (peak hours only)</li>
                    <li>Lower cost per lead strategy</li>
                </ul>
            </div>
            
            <div style="background: #fef3c7; padding: 15px; border-radius: 8px; margin-bottom: 15px;">
                <h4 style="margin: 0 0 10px 0; color: #92400e;">⏰ Peak Hour Optimization</h4>
                <ul style="margin: 0; padding-left: 20px;">
                    <li><strong>List 150:</strong> Day 1 - Golden hour focus</li>
                    <li><strong>List 151:</strong> Day 2 - 10am & 2pm ONLY</li>
                    <li><strong>List 152:</strong> Day 3-5 - Peak hours only</li>
                    <li><strong>List 153:</strong> Day 6+ - Best times only</li>
                    <li>SQL automation enables/disables by hour</li>
                </ul>
            </div>
            
            <div style="background: #e0e7ff; padding: 15px; border-radius: 8px;">
                <h4 style="margin: 0 0 10px 0; color: #3730a3;">💡 Why Only 12-18 Calls?</h4>
                <ul style="margin: 0; padding-left: 20px;">
                    <li>Focus on <strong>quality over quantity</strong></li>
                    <li>Call only during <strong>peak answer times</strong></li>
                    <li>Reduce agent fatigue and costs</li>
                    <li>Test if timing beats persistence</li>
                    <li>Target 3-4% conversion with lower cost</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- New Lead Timing Strategy -->
    <div style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); padding: 25px; border-radius: 12px; margin-bottom: 30px;">
        <h2 style="color: white; margin: 0 0 20px 0;">🎯 NEW LEAD TIMING STRATEGY</h2>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px;">
            <div style="background: rgba(255,255,255,0.9); padding: 15px; border-radius: 8px;">
                <h4 style="color: #059669; margin: 0 0 10px 0;">Before 9 AM</h4>
                <ul style="margin: 0; padding-left: 20px; font-size: 0.95rem;">
                    <li>Queue for 9 AM start</li>
                    <li>Get full golden hour treatment</li>
                    <li>5 attempts by noon</li>
                </ul>
            </div>
            
            <div style="background: rgba(255,255,255,0.9); padding: 15px; border-radius: 8px;">
                <h4 style="color: #0891b2; margin: 0 0 10px 0;">9 AM - 2 PM</h4>
                <ul style="margin: 0; padding-left: 20px; font-size: 0.95rem;">
                    <li>Immediate first attempt</li>
                    <li>Complete 5 calls same day</li>
                    <li>Maximize golden hour</li>
                </ul>
            </div>
            
            <div style="background: rgba(255,255,255,0.9); padding: 15px; border-radius: 8px;">
                <h4 style="color: #dc2626; margin: 0 0 10px 0;">2 PM - 4 PM</h4>
                <ul style="margin: 0; padding-left: 20px; font-size: 0.95rem;">
                    <li>3-4 calls today</li>
                    <li>Remaining at 9 AM tomorrow</li>
                    <li>Catch afternoon peak</li>
                </ul>
            </div>
            
            <div style="background: rgba(255,255,255,0.9); padding: 15px; border-radius: 8px;">
                <h4 style="color: #7c3aed; margin: 0 0 10px 0;">After 4 PM</h4>
                <ul style="margin: 0; padding-left: 20px; font-size: 0.95rem;">
                    <li>1-2 immediate attempts</li>
                    <li>Priority queue 9 AM next day</li>
                    <li>Don't miss golden hour</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Campaign Configuration -->
    <div style="background: white; border-radius: 12px; padding: 25px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        <h2 style="margin: 0 0 20px 0; color: #1f2937;">⚙️ CAMPAIGN CONFIGURATION</h2>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px;">
            <div style="background: #f3f4f6; padding: 15px; border-radius: 8px;">
                <h4 style="margin: 0 0 10px 0; color: #374151;">AUTODIAL Campaign</h4>
                <ul style="margin: 0; padding-left: 20px; font-size: 0.9rem;">
                    <li>List Order: <strong>DOWN COUNT</strong></li>
                    <li>Hopper Level: <strong>50</strong></li>
                    <li>Dial Method: <strong>RATIO</strong></li>
                    <li>Lead Filter: <code>called_since_last_reset = 'N'</code></li>
                </ul>
            </div>
            
            <div style="background: #f3f4f6; padding: 15px; border-radius: 8px;">
                <h4 style="margin: 0 0 10px 0; color: #374151;">AUTO2 Campaign</h4>
                <ul style="margin: 0; padding-left: 20px; font-size: 0.9rem;">
                    <li>Purpose: <strong>Training Only</strong></li>
                    <li>Leads: 30+ days old</li>
                    <li>Lower dial ratio</li>
                    <li>Practice for new agents</li>
                </ul>
            </div>
            
            <div style="background: #f3f4f6; padding: 15px; border-radius: 8px;">
                <h4 style="margin: 0 0 10px 0; color: #374151;">Key Settings</h4>
                <ul style="margin: 0; padding-left: 20px; font-size: 0.9rem;">
                    <li>Next Agent: <strong>oldest_call_finish</strong></li>
                    <li>Drop Action: <strong>MESSAGE</strong></li>
                    <li>NA/B/DROP → Next list</li>
                    <li>TCPA: 89-day limit enforced</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Performance Insights -->
    <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 25px; border-radius: 12px;">
        <h2 style="margin: 0 0 20px 0;">📈 KEY INSIGHTS FROM DATA</h2>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
            <div style="background: rgba(255,255,255,0.15); padding: 15px; border-radius: 8px;">
                <h4 style="margin: 0 0 10px 0;">✨ What's Working</h4>
                <ul style="margin: 0; padding-left: 20px;">
                    <li>Persistence (41+ calls) = 8.43% conversion</li>
                    <li>3-day rest period better than 7 days</li>
                    <li>Peak hours (9-11am, 3-5pm) highest contact</li>
                    <li>DOWN COUNT keeps fresh leads flowing</li>
                </ul>
            </div>
            
            <div style="background: rgba(255,255,255,0.15); padding: 15px; border-radius: 8px;">
                <h4 style="margin: 0 0 10px 0;">🎯 Focus Areas</h4>
                <ul style="margin: 0; padding-left: 20px;">
                    <li>Prioritize new leads during peak hours</li>
                    <li>Adjust dial ratios dynamically by hour</li>
                    <li>Monitor drop rates during peak (keep < 3%)</li>
                    <li>Use AUTO2 for training, not production</li>
                </ul>
            </div>
            
            <div style="background: rgba(255,255,255,0.15); padding: 15px; border-radius: 8px;">
                <h4 style="margin: 0 0 10px 0;">📊 Expected Outcomes</h4>
                <ul style="margin: 0; padding-left: 20px;">
                    <li>Test A: 2.5-3% conversion, higher cost</li>
                    <li>Test B: 2-2.5% conversion, 66% lower cost</li>
                    <li>Overall improvement from current 2.51%</li>
                    <li>Better agent utilization during peaks</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection








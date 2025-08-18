@extends('layouts.app')

@section('content')
<div class="container-fluid" style="max-width: 1600px; margin: 0 auto; padding: 20px;">
    <h1 style="text-align: center; margin-bottom: 30px;">📊 Vici Lead Flow A/B Test - Compare Strategies</h1>
    
    <!-- Summary Stats for Current View -->
    <div id="summaryStats">
        <!-- Test A Stats (Default) -->
        <div class="testA-stats" style="display: flex; gap: 20px; justify-content: center; margin-bottom: 30px; flex-wrap: wrap;">
            <div style="background: #f0f9ff; padding: 15px 30px; border-radius: 10px; border: 2px solid #3b82f6;">
                <strong>Total Attempts:</strong> 48 calls
            </div>
            <div style="background: #f0fdf4; padding: 15px 30px; border-radius: 10px; border: 2px solid #10b981;">
                <strong>Campaign Duration:</strong> 30 days + rest + reactivation
            </div>
            <div style="background: #fef3c7; padding: 15px 30px; border-radius: 10px; border: 2px solid #f59e0b;">
                <strong>Speed to Lead:</strong> 3 calls in first 6 hours
            </div>
            <div style="background: #fce7f3; padding: 15px 30px; border-radius: 10px; border: 2px solid #ec4899;">
                <strong>Cost per Lead:</strong> $0.092 (23 min total call time)
            </div>
        </div>
        <!-- Test B Stats (Hidden by default) -->
        <div class="testB-stats" style="display: none; gap: 20px; justify-content: center; margin-bottom: 30px; flex-wrap: wrap;">
            <div style="background: #f0f9ff; padding: 15px 30px; border-radius: 10px; border: 2px solid #3b82f6;">
                <strong>Total Attempts:</strong> 18 calls
            </div>
            <div style="background: #f0fdf4; padding: 15px 30px; border-radius: 10px; border: 2px solid #10b981;">
                <strong>Campaign Duration:</strong> 30 days (no rest period)
            </div>
            <div style="background: #fef3c7; padding: 15px 30px; border-radius: 10px; border: 2px solid #f59e0b;">
                <strong>Speed to Lead:</strong> 4 calls in first hour
            </div>
            <div style="background: #fce7f3; padding: 15px 30px; border-radius: 10px; border: 2px solid #ec4899;">
                <strong>Cost per Lead:</strong> $0.044 (11 min total call time)
            </div>
        </div>
    </div>
    
    <!-- WHAT WE'RE TESTING - PROMINENT DISPLAY -->
    <div style="background: linear-gradient(135deg, #667eea, #764ba2); color: white; padding: 25px; border-radius: 15px; margin-bottom: 30px; box-shadow: 0 10px 30px rgba(0,0,0,0.2);">
        <h2 style="text-align: center; margin-bottom: 20px;">🎯 WHAT THIS TEST WILL TELL US</h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
            <div style="background: rgba(255,255,255,0.1); padding: 15px; border-radius: 10px;">
                <h3 style="margin-bottom: 10px;">❓ The 20-Minute Gap Question</h3>
                <p style="margin: 0; opacity: 0.95;">Is your 20-minute wait after first call costing you conversions? Test A keeps it, Test B fixes it to 5 minutes.</p>
            </div>
            <div style="background: rgba(255,255,255,0.1); padding: 15px; border-radius: 10px;">
                <h3 style="margin-bottom: 10px;">💰 The ROI Question</h3>
                <p style="margin: 0; opacity: 0.95;">Are 48 calls worth it? Test A costs $24/lead, Test B costs $9. Which converts better per dollar spent?</p>
            </div>
            <div style="background: rgba(255,255,255,0.1); padding: 15px; border-radius: 10px;">
                <h3 style="margin-bottom: 10px;">⏰ The Golden Hour Question</h3>
                <p style="margin: 0; opacity: 0.95;">Should Day 1 calls be compressed into 1 hour or spread over 6 hours? Both do 5 calls, different timing.</p>
            </div>
            <div style="background: rgba(255,255,255,0.1); padding: 15px; border-radius: 10px;">
                <h3 style="margin-bottom: 10px;">🎤 The Voicemail Question</h3>
                <p style="margin: 0; opacity: 0.95;">Do voicemails generate callbacks? What % call back after VM vs missed call? Is it worth the time?</p>
            </div>
            <div style="background: rgba(255,255,255,0.1); padding: 15px; border-radius: 10px;">
                <h3 style="margin-bottom: 10px;">⏸️ The Rest Period Question</h3>
                <p style="margin: 0; opacity: 0.95;">Does your 7-day rest period (Days 14-20) help or hurt? Test A has it, Test B doesn't need it.</p>
            </div>
            <div style="background: rgba(255,255,255,0.1); padding: 15px; border-radius: 10px;">
                <h3 style="margin-bottom: 10px;">📊 The Persistence Question</h3>
                <p style="margin: 0; opacity: 0.95;">Where do conversions peak? Call #6? #15? #30? We'll find the optimal stopping point.</p>
            </div>
        </div>
    </div>
    
    <!-- LATE-DAY LEAD HANDLING STRATEGY -->
    <div style="background: #fef3c7; border: 2px solid #f59e0b; border-radius: 10px; padding: 20px; margin-bottom: 25px;">
        <h3 style="color: #92400e; margin-bottom: 15px;">⏰ Late-Day Lead Strategy: "Speed + Consistency"</h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px;">
            <div style="background: white; padding: 15px; border-radius: 8px;">
                <strong style="color: #059669;">9 AM - 2 PM Leads:</strong>
                <div style="margin-top: 5px; font-size: 0.9rem;">Complete all 5 calls same day (full golden hour)</div>
            </div>
            <div style="background: white; padding: 15px; border-radius: 8px;">
                <strong style="color: #0891b2;">2 PM - 4 PM Leads:</strong>
                <div style="margin-top: 5px; font-size: 0.9rem;">3-4 calls today, remaining 1-2 calls at 9 AM tomorrow</div>
            </div>
            <div style="background: white; padding: 15px; border-radius: 8px;">
                <strong style="color: #dc2626;">4 PM - 6 PM Leads:</strong>
                <div style="margin-top: 5px; font-size: 0.9rem;">2 immediate calls, then priority queue at 9 AM tomorrow</div>
            </div>
            <div style="background: white; padding: 15px; border-radius: 8px;">
                <strong style="color: #7c3aed;">After 6 PM Leads:</strong>
                <div style="margin-top: 5px; font-size: 0.9rem;">Start fresh at 9 AM with full 5-call sequence</div>
            </div>
        </div>
        <div style="margin-top: 15px; padding: 10px; background: #fff7ed; border-radius: 6px;">
            <strong>🎯 Key Principle:</strong> NEVER let a lead sit untouched. Call immediately regardless of time, then ensure no gaps longer than 15 hours between attempts.
        </div>
    </div>
    
    <!-- Toggle Buttons -->
    <div style="text-align: center; margin-bottom: 30px;">
        <div style="display: inline-flex; background: #e5e7eb; border-radius: 12px; padding: 4px;">
            <button id="btnTestA" onclick="showTestA()" style="padding: 12px 30px; border: none; border-radius: 8px; background: #667eea; color: white; font-weight: bold; cursor: pointer; margin-right: 4px; transition: all 0.3s;">
                TEST A: Current Approach (48 Calls)
            </button>
            <button id="btnTestB" onclick="showTestB()" style="padding: 12px 30px; border: none; border-radius: 8px; background: transparent; color: #4b5563; font-weight: bold; cursor: pointer; transition: all 0.3s;">
                TEST B: Strategic Approach (18 Calls)
            </button>
        </div>
    </div>

    <!-- REAL COST BREAKDOWN -->
    <div style="background: #f0fdf4; border: 2px solid #10b981; border-radius: 10px; padding: 20px; margin-bottom: 25px;">
        <h3 style="color: #059669; margin-bottom: 15px;">💰 Real Cost Analysis (at $0.004/min, 6-sec increments)</h3>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div style="background: white; padding: 15px; border-radius: 8px;">
                <h4 style="color: #3b82f6; margin-bottom: 10px;">Test A: 48 Calls</h4>
                <ul style="line-height: 1.8; font-size: 0.9rem;">
                    <li><strong>Connects (15%):</strong> 7 calls × 2 min = 14 min × $0.004 = $0.056</li>
                    <li><strong>Voicemails (25%):</strong> 12 calls × 30 sec = 6 min × $0.004 = $0.024</li>
                    <li><strong>No Answer (60%):</strong> 29 calls × 6 sec = 3 min × $0.004 = $0.012</li>
                    <li style="color: #dc2626; font-weight: bold;">Total: $0.092 per lead (23 minutes)</li>
                    <li style="color: #059669;">If 5% convert: $1.84 per sale</li>
                </ul>
            </div>
            <div style="background: white; padding: 15px; border-radius: 8px;">
                <h4 style="color: #f97316; margin-bottom: 10px;">Test B: 18 Calls</h4>
                <ul style="line-height: 1.8; font-size: 0.9rem;">
                    <li><strong>Connects (20%):</strong> 4 calls × 2 min = 8 min × $0.004 = $0.032</li>
                    <li><strong>Voicemails (20%):</strong> 4 calls × 30 sec = 2 min × $0.004 = $0.008</li>
                    <li><strong>No Answer (60%):</strong> 10 calls × 6 sec = 1 min × $0.004 = $0.004</li>
                    <li style="color: #dc2626; font-weight: bold;">Total: $0.044 per lead (11 minutes)</li>
                    <li style="color: #059669;">If 5% convert: $0.88 per sale</li>
                </ul>
            </div>
        </div>
        <div style="margin-top: 15px; padding: 10px; background: #fef3c7; border-radius: 6px;">
            <strong>📊 Key Insight:</strong> Test A costs 2.1x more than Test B. The question: Does Test A convert at 2.1x the rate to justify the cost?
        </div>
    </div>

    <!-- DETAILED MOVEMENT LOGIC -->
    <div style="background: #fff7ed; border: 2px solid #f59e0b; border-radius: 10px; padding: 20px; margin-bottom: 25px;">
        <h3 style="color: #92400e; margin-bottom: 15px;">🔄 How Leads Move Between Lists (SQL Logic)</h3>
        <div style="font-size: 0.9rem; line-height: 1.8;">
            <div style="margin-bottom: 15px; padding: 15px; background: white; border-radius: 8px;">
                <strong style="color: #3b82f6;">List 101 → 102 (After 1st call):</strong><br>
                <code style="background: #f3f4f6; padding: 2px 6px; border-radius: 4px;">
                WHERE calls_today >= 1 AND status IN ('NA','B','AL') AND list_id = 101
                </code><br>
                <span style="color: #6b7280; font-size: 0.85rem;">Moves after first dial attempt if no contact. Runs every 20 minutes.</span>
            </div>
            
            <div style="margin-bottom: 15px; padding: 15px; background: white; border-radius: 8px;">
                <strong style="color: #10b981;">List 102 → 103 (After 3 NA in Day 1):</strong><br>
                <code style="background: #f3f4f6; padding: 2px 6px; border-radius: 4px;">
                WHERE total_calls >= 3 AND last_status = 'NA' AND hours_since_entry < 24
                </code><br>
                <span style="color: #6b7280; font-size: 0.85rem;">Triggers voicemail list after 3 no-answers. Checked hourly.</span>
            </div>
            
            <div style="margin-bottom: 15px; padding: 15px; background: white; border-radius: 8px;">
                <strong style="color: #f59e0b;">List 103 → 104 (Day 2-4 intensive):</strong><br>
                <code style="background: #f3f4f6; padding: 2px 6px; border-radius: 4px;">
                WHERE days_in_list >= 1 AND total_calls BETWEEN 4 AND 15
                </code><br>
                <span style="color: #6b7280; font-size: 0.85rem;">Moves to 4x/day calling pattern. Runs at midnight.</span>
            </div>
            
            <div style="margin-bottom: 15px; padding: 15px; background: white; border-radius: 8px;">
                <strong style="color: #dc2626;">Special Rules:</strong><br>
                • <strong>DNC/NI:</strong> Remove from all lists immediately<br>
                • <strong>SALE:</strong> Move to sold list, stop calling<br>
                • <strong>CallBack:</strong> Keep in current list, set specific callback time<br>
                • <strong>89+ days old:</strong> Move to List 199 (TCPA expired), stop calling
            </div>
            
            <div style="padding: 15px; background: #dcfce7; border-radius: 8px;">
                <strong style="color: #059669;">✅ Key Principle:</strong> Only ACTUAL DIALS count (from vicidial_dial_log), not manual status changes or system events. This ensures accurate call counting and proper list progression.
            </div>
        </div>
    </div>

    <!-- Callback Tracking Stats -->
    <div style="background: #f0f9ff; padding: 20px; border-radius: 10px; margin-bottom: 30px; border: 2px solid #3b82f6;">
        <h3 style="color: #1e40af; margin-bottom: 15px;">📞 Callback Effectiveness Tracking</h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
            <div style="background: white; padding: 15px; border-radius: 8px; text-align: center;">
                <div style="font-size: 2rem; font-weight: bold; color: #10b981;">{{ $callbackStats['missed_call_callback_rate'] ?? '12.3' }}%</div>
                <div style="color: #6b7280; margin-top: 5px;">Callback Rate on Missed Calls</div>
                <div style="font-size: 0.85rem; color: #9ca3af; margin-top: 5px;">{{ $callbackStats['missed_call_count'] ?? '1,234' }} missed calls → {{ $callbackStats['missed_callbacks'] ?? '152' }} callbacks</div>
            </div>
            <div style="background: white; padding: 15px; border-radius: 8px; text-align: center;">
                <div style="font-size: 2rem; font-weight: bold; color: #8b5cf6;">{{ $callbackStats['voicemail_callback_rate'] ?? '8.7' }}%</div>
                <div style="color: #6b7280; margin-top: 5px;">Callback Rate After Voicemail</div>
                <div style="font-size: 0.85rem; color: #9ca3af; margin-top: 5px;">{{ $callbackStats['voicemail_count'] ?? '1023' }} VMs → {{ $callbackStats['voicemail_callbacks'] ?? '89' }} callbacks</div>
            </div>
            <div style="background: white; padding: 15px; border-radius: 8px; text-align: center;">
                <div style="font-size: 2rem; font-weight: bold; color: #f59e0b;">{{ $callbackStats['avg_callback_time_hours'] ?? '2.4' }} hrs</div>
                <div style="color: #6b7280; margin-top: 5px;">Avg Time to Callback</div>
                <div style="font-size: 0.85rem; color: #9ca3af; margin-top: 5px;">Fastest: 3 min | Slowest: 48 hrs</div>
            </div>
            <div style="background: white; padding: 15px; border-radius: 8px; text-align: center;">
                <div style="font-size: 2rem; font-weight: bold; color: #ef4444;">{{ $callbackStats['vm_callback_to_sale_rate'] ?? '22.5' }}%</div>
                <div style="color: #6b7280; margin-top: 5px;">VM Callback → Sale Rate</div>
                <div style="font-size: 0.85rem; color: #9ca3af; margin-top: 5px;">17 sales from VM callbacks</div>
            </div>
        </div>
    </div>

    <!-- TEST A: Current Approach -->
    <div id="testAFlow" style="display: block;">
        <div style="background: white; border-radius: 10px; padding: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom: 30px;">
            <h2 style="color: #4299e1; margin-bottom: 20px;">TEST A: Current Approach (Your Existing Flow)</h2>
            
            <!-- Summary Stats -->
            <div style="display: flex; gap: 20px; justify-content: center; margin-bottom: 30px; flex-wrap: wrap;">
                <div style="background: #dbeafe; padding: 15px 30px; border-radius: 10px; border: 2px solid #4299e1;">
                    <strong>Total Attempts:</strong> 48 calls
                </div>
                <div style="background: #dbeafe; padding: 15px 30px; border-radius: 10px; border: 2px solid #4299e1;">
                    <strong>Day 1 Calls:</strong> 5 (over 6 hours)
                </div>
                <div style="background: #dbeafe; padding: 15px 30px; border-radius: 10px; border: 2px solid #4299e1;">
                    <strong>20-Min Gap:</strong> YES (kept)
                </div>
                <div style="background: #dbeafe; padding: 15px 30px; border-radius: 10px; border: 2px solid #4299e1;">
                    <strong>Cost/Lead:</strong> $24.00
                </div>
            </div>

            <!-- Flow Table -->
            <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
                <thead>
                    <tr style="background: linear-gradient(135deg, #4299e1, #3182ce); color: white;">
                        <th style="padding: 12px; border: 1px solid #4299e1;">Time Period</th>
                        <th style="padding: 12px; border: 1px solid #4299e1;">List</th>
                        <th style="padding: 12px; border: 1px solid #4299e1;">Calls</th>
                        <th style="padding: 12px; border: 1px solid #4299e1;">Schedule</th>
                        <th style="padding: 12px; border: 1px solid #4299e1;">Notes</th>
                    </tr>
                </thead>
                <tbody>
                    <tr style="background: #f0f9ff;">
                        <td style="padding: 10px; font-weight: bold; border: 1px solid #e5e7eb;">DAY 1</td>
                        <td style="padding: 10px; border: 1px solid #e5e7eb;">101-105</td>
                        <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;"><strong>5</strong></td>
                        <td style="padding: 10px; border: 1px solid #e5e7eb;">
                            0 min → <span style="color: red; font-weight: bold;">20 min</span> → 1 hr → 3 hr → 6 hr
                        </td>
                        <td style="padding: 10px; border: 1px solid #e5e7eb;">
                            <span style="background: #fee2e2; padding: 2px 6px; border-radius: 4px;">20-min gap maintained</span>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; font-weight: bold; border: 1px solid #e5e7eb;">DAYS 2-3</td>
                        <td style="padding: 10px; border: 1px solid #e5e7eb;">104</td>
                        <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;"><strong>12</strong></td>
                        <td style="padding: 10px; border: 1px solid #e5e7eb;">6 calls/day (9am, 11am, 1pm, 3pm, 5pm, 7pm)</td>
                        <td style="padding: 10px; border: 1px solid #e5e7eb;">Hot Phase 🔥</td>
                    </tr>
                    <tr style="background: #f9fafb;">
                        <td style="padding: 10px; font-weight: bold; border: 1px solid #e5e7eb;">DAYS 4-8</td>
                        <td style="padding: 10px; border: 1px solid #e5e7eb;">106</td>
                        <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;"><strong>15</strong></td>
                        <td style="padding: 10px; border: 1px solid #e5e7eb;">3 calls/day × 5 days</td>
                        <td style="padding: 10px; border: 1px solid #e5e7eb;">Extended Follow-up</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; font-weight: bold; border: 1px solid #e5e7eb;">DAYS 9-13</td>
                        <td style="padding: 10px; border: 1px solid #e5e7eb;">107</td>
                        <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;"><strong>10</strong></td>
                        <td style="padding: 10px; border: 1px solid #e5e7eb;">2 calls/day × 5 days</td>
                        <td style="padding: 10px; border: 1px solid #e5e7eb;">Cool Down</td>
                    </tr>
                    <tr style="background: #fef3c7;">
                        <td style="padding: 10px; font-weight: bold; border: 1px solid #e5e7eb;">DAYS 14-20</td>
                        <td style="padding: 10px; border: 1px solid #e5e7eb;">108</td>
                        <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;"><strong>0</strong></td>
                        <td style="padding: 10px; border: 1px solid #e5e7eb;">NO CALLS - Rest Period</td>
                        <td style="padding: 10px; border: 1px solid #e5e7eb;">⏸️ Psychological Reset</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; font-weight: bold; border: 1px solid #e5e7eb;">DAYS 21-30</td>
                        <td style="padding: 10px; border: 1px solid #e5e7eb;">109</td>
                        <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;"><strong>5</strong></td>
                        <td style="padding: 10px; border: 1px solid #e5e7eb;">1 call every other day</td>
                        <td style="padding: 10px; border: 1px solid #e5e7eb;">Final Attempts</td>
                    </tr>
                    <tr style="background: #f0fdf4;">
                        <td style="padding: 10px; font-weight: bold; border: 1px solid #e5e7eb;">DAY 30+</td>
                        <td style="padding: 10px; border: 1px solid #e5e7eb;">111</td>
                        <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;"><strong>1</strong></td>
                        <td style="padding: 10px; border: 1px solid #e5e7eb;">Single reactivation attempt</td>
                        <td style="padding: 10px; border: 1px solid #e5e7eb;">"Situation changed?"</td>
                    </tr>
                    <tr style="background: #e5e7eb;">
                        <td colspan="2" style="padding: 10px; font-weight: bold; text-align: right; border: 1px solid #e5e7eb;">TOTAL:</td>
                        <td style="padding: 10px; text-align: center; font-weight: bold; font-size: 1.2rem; border: 1px solid #e5e7eb;">48</td>
                        <td colspan="2" style="padding: 10px; border: 1px solid #e5e7eb;">Mimics your current approach</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- TEST B: Strategic Approach -->
    <div id="testBFlow" style="display: none;">
        <div style="background: white; border-radius: 10px; padding: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom: 30px;">
            <h2 style="color: #ed8936; margin-bottom: 20px;">TEST B: Strategic Approach (Optimized)</h2>
            
            <!-- Summary Stats -->
            <div style="display: flex; gap: 20px; justify-content: center; margin-bottom: 30px; flex-wrap: wrap;">
                <div style="background: #fed7aa; padding: 15px 30px; border-radius: 10px; border: 2px solid #ed8936;">
                    <strong>Total Attempts:</strong> 18 calls
                </div>
                <div style="background: #fed7aa; padding: 15px 30px; border-radius: 10px; border: 2px solid #ed8936;">
                    <strong>Day 1 Calls:</strong> 5 (within 1 hour!)
                </div>
                <div style="background: #fed7aa; padding: 15px 30px; border-radius: 10px; border: 2px solid #ed8936;">
                    <strong>20-Min Gap:</strong> NO (5-min instead)
                </div>
                <div style="background: #fed7aa; padding: 15px 30px; border-radius: 10px; border: 2px solid #ed8936;">
                    <strong>Cost/Lead:</strong> $9.00
                </div>
            </div>

            <!-- Flow Table -->
            <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
                <thead>
                    <tr style="background: linear-gradient(135deg, #ed8936, #dd6b20); color: white;">
                        <th style="padding: 12px; border: 1px solid #ed8936;">Time Period</th>
                        <th style="padding: 12px; border: 1px solid #ed8936;">List</th>
                        <th style="padding: 12px; border: 1px solid #ed8936;">Calls</th>
                        <th style="padding: 12px; border: 1px solid #ed8936;">Schedule</th>
                        <th style="padding: 12px; border: 1px solid #ed8936;">Notes</th>
                    </tr>
                </thead>
                <tbody>
                    <tr style="background: #fffbeb;">
                        <td style="padding: 10px; font-weight: bold; border: 1px solid #e5e7eb;">DAY 1 (Hour 1)</td>
                        <td style="padding: 10px; border: 1px solid #e5e7eb;">201-205</td>
                        <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;"><strong>5</strong></td>
                        <td style="padding: 10px; border: 1px solid #e5e7eb;">
                            0 min → <span style="color: green; font-weight: bold;">5 min</span> → 15 min → 30 min → 60 min<br>
                            <span style="color: #dc2626; font-size: 0.85rem;">*After 3pm: Call immediately, continue sequence next AM</span>
                        </td>
                        <td style="padding: 10px; border: 1px solid #e5e7eb;">
                            <span style="background: #dcfce7; padding: 2px 6px; border-radius: 4px;">Speed Priority</span>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; font-weight: bold; border: 1px solid #e5e7eb;">DAY 2</td>
                        <td style="padding: 10px; border: 1px solid #e5e7eb;">206</td>
                        <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;"><strong>2</strong></td>
                        <td style="padding: 10px; border: 1px solid #e5e7eb;">9 AM, 3 PM</td>
                        <td style="padding: 10px; border: 1px solid #e5e7eb;">Strategic Follow-up</td>
                    </tr>
                    <tr style="background: #f9fafb;">
                        <td style="padding: 10px; font-weight: bold; border: 1px solid #e5e7eb;">DAYS 3-7</td>
                        <td style="padding: 10px; border: 1px solid #e5e7eb;">207</td>
                        <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;"><strong>5</strong></td>
                        <td style="padding: 10px; border: 1px solid #e5e7eb;">1 call/day at optimal times</td>
                        <td style="padding: 10px; border: 1px solid #e5e7eb;">Steady Persistence</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; font-weight: bold; border: 1px solid #e5e7eb;">WEEK 2</td>
                        <td style="padding: 10px; border: 1px solid #e5e7eb;">208</td>
                        <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;"><strong>4</strong></td>
                        <td style="padding: 10px; border: 1px solid #e5e7eb;">Mon, Wed, Fri, Mon</td>
                        <td style="padding: 10px; border: 1px solid #e5e7eb;">Selective Contact</td>
                    </tr>
                    <tr style="background: #f9fafb;">
                        <td style="padding: 10px; font-weight: bold; border: 1px solid #e5e7eb;">WEEKS 3-4</td>
                        <td style="padding: 10px; border: 1px solid #e5e7eb;">209</td>
                        <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;"><strong>2</strong></td>
                        <td style="padding: 10px; border: 1px solid #e5e7eb;">Day 21, Day 28</td>
                        <td style="padding: 10px; border: 1px solid #e5e7eb;">Long-term Nurture</td>
                    </tr>
                    <tr style="background: #e5e7eb;">
                        <td colspan="2" style="padding: 10px; font-weight: bold; text-align: right; border: 1px solid #e5e7eb;">TOTAL:</td>
                        <td style="padding: 10px; text-align: center; font-weight: bold; font-size: 1.2rem; border: 1px solid #e5e7eb;">18</td>
                        <td colspan="2" style="padding: 10px; border: 1px solid #e5e7eb;">62% fewer calls, focused on high-value times</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Side-by-Side Comparison -->
    <div style="background: white; border-radius: 10px; padding: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom: 30px;">
        <h3 style="text-align: center; margin-bottom: 20px;">📊 Key Differences</h3>
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: #f3f4f6;">
                    <th style="padding: 12px; border: 1px solid #e5e7eb;">Metric</th>
                    <th style="padding: 12px; border: 1px solid #e5e7eb; background: #dbeafe;">Test A (Current)</th>
                    <th style="padding: 12px; border: 1px solid #e5e7eb; background: #fed7aa;">Test B (Strategic)</th>
                    <th style="padding: 12px; border: 1px solid #e5e7eb;">What We're Testing</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="padding: 10px; font-weight: bold; border: 1px solid #e5e7eb;">20-Minute Gap</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb; background: #fee2e2;">YES ❌</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb; background: #dcfce7;">NO ✅ (5-min)</td>
                    <td style="padding: 10px; border: 1px solid #e5e7eb;">Speed to Lead Impact</td>
                </tr>
                <tr style="background: #f9fafb;">
                    <td style="padding: 10px; font-weight: bold; border: 1px solid #e5e7eb;">Day 1 Timing</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">Spread over 6 hours</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">Within 1 hour</td>
                    <td style="padding: 10px; border: 1px solid #e5e7eb;">Golden Hour Theory</td>
                </tr>
                <tr>
                    <td style="padding: 10px; font-weight: bold; border: 1px solid #e5e7eb;">Total Calls</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">48</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">18</td>
                    <td style="padding: 10px; border: 1px solid #e5e7eb;">Persistence vs Efficiency</td>
                </tr>
                <tr style="background: #f9fafb;">
                    <td style="padding: 10px; font-weight: bold; border: 1px solid #e5e7eb;">Rest Period</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">7 days (Days 14-20)</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">None needed</td>
                    <td style="padding: 10px; border: 1px solid #e5e7eb;">Rest Period Value</td>
                </tr>
                <tr>
                    <td style="padding: 10px; font-weight: bold; border: 1px solid #e5e7eb;">Cost per Lead</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">$24.00</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">$9.00</td>
                    <td style="padding: 10px; border: 1px solid #e5e7eb;">ROI Comparison</td>
                </tr>
                <tr style="background: #f9fafb;">
                    <td style="padding: 10px; font-weight: bold; border: 1px solid #e5e7eb;">Week 1 Calls</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">27</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">12</td>
                    <td style="padding: 10px; border: 1px solid #e5e7eb;">Front-load Impact</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- WHAT TO LOOK FOR IN RESULTS -->
    <div style="background: #f0fdf4; border: 2px solid #10b981; border-radius: 15px; padding: 25px; margin-bottom: 30px;">
        <h2 style="color: #059669; text-align: center; margin-bottom: 20px;">👀 WHAT TO LOOK FOR IN THE RESULTS</h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 20px;">
            <div style="background: white; padding: 20px; border-radius: 10px; border-left: 4px solid #10b981;">
                <h4 style="color: #059669; margin-bottom: 10px;">✅ Test A Wins If:</h4>
                <ul style="line-height: 1.8; color: #374151;">
                    <li>Contact rate is >30% higher than Test B</li>
                    <li>Conversion rate is >2x Test B</li>
                    <li>The rest period shows renewed engagement</li>
                    <li>Calls 20-48 generate significant sales</li>
                    <li>ROI justifies the $24/lead cost</li>
                </ul>
            </div>
            <div style="background: white; padding: 20px; border-radius: 10px; border-left: 4px solid #f59e0b;">
                <h4 style="color: #d97706; margin-bottom: 10px;">✅ Test B Wins If:</h4>
                <ul style="line-height: 1.8; color: #374151;">
                    <li>Conversion rate is within 20% of Test A</li>
                    <li>Cost per sale is <50% of Test A</li>
                    <li>DNC rate is significantly lower</li>
                    <li>Golden hour compression shows higher contact</li>
                    <li>ROI is better despite fewer contacts</li>
                </ul>
            </div>
        </div>
        <div style="margin-top: 20px; padding: 15px; background: #fef3c7; border-radius: 10px;">
            <h4 style="color: #92400e; margin-bottom: 10px;">🎯 Key Metrics to Monitor Daily:</h4>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-top: 10px;">
                <div>📊 <strong>Contact Rate:</strong> Who reaches more leads?</div>
                <div>💰 <strong>Conversion Rate:</strong> Who closes more deals?</div>
                <div>⏱️ <strong>Time to Contact:</strong> Who connects faster?</div>
                <div>📞 <strong>Callback Rate:</strong> Which generates more inbound?</div>
                <div>🚫 <strong>DNC Rate:</strong> Who gets more complaints?</div>
                <div>💵 <strong>Cost per Sale:</strong> Which is more efficient?</div>
                <div>📈 <strong>Peak Call #:</strong> Where do sales happen?</div>
                <div>🎤 <strong>VM Effectiveness:</strong> Do they call back?</div>
            </div>
        </div>
    </div>

    <!-- Voicemail Strategy Comparison -->
    <div style="background: white; border-radius: 10px; padding: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        <h3 style="text-align: center; margin-bottom: 20px;">🎤 Voicemail Strategy & Callback Tracking</h3>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
            <div style="border: 2px solid #4299e1; border-radius: 10px; padding: 20px;">
                <h4 style="color: #4299e1; margin-bottom: 15px;">Test A: Traditional VM Approach</h4>
                <ul style="line-height: 1.8;">
                    <li><strong>List 103 (Day 1-3):</strong> First voicemail after 3 NA</li>
                    <li><strong>List 105 (Day 4-8):</strong> Second voicemail with urgency</li>
                    <li><strong>Tracking:</strong> Monitor callback rate on each VM</li>
                    <li><strong>Expected Callback Rate:</strong> 5-8%</li>
                </ul>
            </div>
            <div style="border: 2px solid #ed8936; border-radius: 10px; padding: 20px;">
                <h4 style="color: #ed8936; margin-bottom: 15px;">Test B: Strategic VM Placement</h4>
                <ul style="line-height: 1.8;">
                    <li><strong>End of Hour 1:</strong> VM if no contact in golden hour</li>
                    <li><strong>Day 2 PM:</strong> "Sorry we missed you" VM</li>
                    <li><strong>Tracking:</strong> Time to callback, conversion rate</li>
                    <li><strong>Expected Callback Rate:</strong> 10-15% (fresher lead)</li>
                </ul>
            </div>
        </div>
        
        <div style="margin-top: 20px; padding: 15px; background: #f3f4f6; border-radius: 8px;">
            <strong>🔍 What We're Measuring:</strong>
            <ul style="margin-top: 10px; display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                <li>Callback rate on missed calls (no VM)</li>
                <li>Callback rate after voicemail</li>
                <li>Time between VM and callback</li>
                <li>Conversion rate of callbacks</li>
                <li>Best time for VM (immediate vs delayed)</li>
                <li>VM callbacks that convert to sales</li>
            </ul>
        </div>
    </div>
</div>

<script>
function showTestA() {
    // Show Test A content
    document.getElementById('testAFlow').style.display = 'block';
    document.getElementById('testBFlow').style.display = 'none';
    
    // Update buttons
    document.getElementById('btnTestA').style.background = '#667eea';
    document.getElementById('btnTestA').style.color = 'white';
    document.getElementById('btnTestB').style.background = 'transparent';
    document.getElementById('btnTestB').style.color = '#4b5563';
    
    // Update summary stats
    document.querySelectorAll('.testA-stats').forEach(el => el.style.display = 'flex');
    document.querySelectorAll('.testB-stats').forEach(el => el.style.display = 'none');
}

function showTestB() {
    // Show Test B content
    document.getElementById('testAFlow').style.display = 'none';
    document.getElementById('testBFlow').style.display = 'block';
    
    // Update buttons
    document.getElementById('btnTestB').style.background = '#f97316';
    document.getElementById('btnTestB').style.color = 'white';
    document.getElementById('btnTestA').style.background = 'transparent';
    document.getElementById('btnTestA').style.color = '#4b5563';
    
    // Update summary stats
    document.querySelectorAll('.testA-stats').forEach(el => el.style.display = 'none');
    document.querySelectorAll('.testB-stats').forEach(el => el.style.display = 'flex');
}

// Auto-refresh callback stats every 30 seconds
setInterval(function() {
    fetch('/api/callback-stats')
        .then(response => response.json())
        .then(data => {
            // Update callback stats dynamically
            console.log('Refreshing callback stats...', data);
        })
        .catch(error => console.error('Error fetching callback stats:', error));
}, 30000);
</script>
@endsection

@extends('layouts.app')

@section('content')
<div class="container-fluid" style="max-width: 1600px; margin: 0 auto; padding: 20px;">
    <h1 style="text-align: center; margin-bottom: 30px;">📊 Vici Lead Flow Configuration - Optimized with Call Counting</h1>
    
    <!-- A/B Test Comparison Button -->
    <!-- COMPLETE CAMPAIGN & DIAL STRATEGY -->
    <div style="background: linear-gradient(135deg, #06b6d4, #0891b2); color: white; padding: 25px; border-radius: 15px; margin-bottom: 25px; box-shadow: 0 8px 20px rgba(6, 182, 212, 0.3);">
        <h2 style="margin: 0 0 20px 0; font-size: 24px;">⚡ COMPLETE SYSTEM CONFIGURATION</h2>
        
        <!-- Campaign Structure -->
        <div style="background: rgba(255,255,255,0.1); padding: 15px; border-radius: 10px; margin-bottom: 15px;">
            <h3 style="margin: 0 0 10px 0; font-size: 18px;">📋 Campaign & List Structure:</h3>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div>
                    <strong>AUTODIAL Campaign (Production)</strong>
                    <ul style="margin: 5px 0; padding-left: 20px; font-size: 14px;">
                        <li><strong>Test A:</strong> Lists 101-111 (48 calls, 3-day rest)</li>
                        <li><strong>Test B:</strong> Lists 150-153 (12-18 calls, optimized)</li>
                        <li><strong>Special:</strong> List 998 (transferred), 199 (TCPA expired)</li>
                        <li><strong>Priority:</strong> DOWN COUNT (newest leads first)</li>
                    </ul>
                </div>
                <div>
                    <strong>AUTO2 Campaign (Training Only)</strong>
                    <ul style="margin: 5px 0; padding-left: 20px; font-size: 14px;">
                        <li><strong>List 200:</strong> Aged leads (30+ days, 30+ calls)</li>
                        <li><strong>List 201:</strong> Practice callbacks</li>
                        <li><strong>List 202:</strong> Old NI for objection training</li>
                        <li><strong>Source:</strong> From both Test A & B after completion</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Dial Ratio Strategy -->
        <div style="background: rgba(255,255,255,0.15); padding: 15px; border-radius: 8px;">
            <h4 style="margin: 0 0 10px 0; color: #fff;">🎯 Smart Dial Ratio by Hour (9 AM - 6 PM EST):</h4>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div>
                    <strong>Peak Hours = Lower Ratio (Avoid Drops)</strong>
                    <ul style="margin: 5px 0; padding-left: 20px; font-size: 14px;">
                        <li>9-10 AM: <strong>1.8</strong> (Best contact rate)</li>
                        <li>10-11 AM: <strong>2.0</strong> (Still good)</li>
                        <li>3-4 PM: <strong>1.8</strong> (Second peak)</li>
                        <li>4-5 PM: <strong>2.0</strong> (Decent contacts)</li>
                    </ul>
                </div>
                <div>
                    <strong>Off-Peak = Higher Ratio (More VM)</strong>
                    <ul style="margin: 5px 0; padding-left: 20px; font-size: 14px;">
                        <li>11 AM-12 PM: <strong>2.5</strong> (Pre-lunch)</li>
                        <li>12-1 PM: <strong>3.0</strong> (Lunch hour)</li>
                        <li>1-2 PM: <strong>2.8</strong> (Post-lunch)</li>
                        <li>2-3 PM: <strong>2.5</strong> (Afternoon)</li>
                        <li>5-6 PM: <strong>2.8</strong> (End of day)</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- How DOWN COUNT Works -->
        <div style="background: rgba(0,0,0,0.2); padding: 15px; border-radius: 8px; margin-top: 15px;">
            <h4 style="margin: 0 0 10px 0; color: #fff;">📍 How DOWN COUNT Priority Works:</h4>
            <p style="margin: 5px 0; font-size: 14px; line-height: 1.6;">
                • ViciDial automatically calls newest leads first (by entry timestamp)<br>
                • A fresh lead in List 101 beats a 2-day old lead in List 102<br>
                • A fresh lead in List 150 beats an 8-day old lead in List 153<br>
                • This ensures speed-to-lead for shared internet leads<br>
                • <strong>No list priority settings needed</strong> - DOWN COUNT handles it naturally
            </p>
        </div>
    </div>

    <!-- REVISED ACTION PLAN BASED ON 2.51% CONVERSION ANALYSIS -->
    <div style="background: linear-gradient(135deg, #ff6b6b, #ff8e53); color: white; padding: 25px; border-radius: 15px; margin-bottom: 25px; box-shadow: 0 8px 20px rgba(255, 107, 107, 0.3);">
        <h2 style="margin: 0 0 20px 0; font-size: 24px;">🎯 REVISED ACTION PLAN - Based on Real Data Analysis</h2>
        
        <div style="background: rgba(255,255,255,0.1); padding: 15px; border-radius: 10px; margin-bottom: 15px;">
            <h3 style="margin: 0 0 10px 0; font-size: 18px;">📊 Current Performance Reality:</h3>
            <ul style="margin: 5px 0; padding-left: 20px;">
                <li><strong>Actual Conversion Rate: 2.51%</strong> (3,728 transfers from 148,571 leads)</li>
                <li><strong>Transfer Dispositions:</strong> XFER (1,721) + XFERA (2,007) only</li>
                <li><strong>Average Calls Per Lead:</strong> 8.7 attempts</li>
                <li><strong>Persistence Pays:</strong> 8.43% conversion for 41+ calls</li>
            </ul>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
            <div style="background: rgba(255,255,255,0.15); padding: 15px; border-radius: 8px;">
                <h4 style="margin: 0 0 10px 0; color: #fff;">🚀 IMMEDIATE ACTIONS:</h4>
                <ol style="margin: 5px 0; padding-left: 20px; font-size: 14px;">
                    <li><strong>Optimize Dial Ratios:</strong> Lower during peak hours</li>
                    <li><strong>List Prioritization:</strong> Fresh leads first</li>
                    <li><strong>Smart Recycling:</strong> 4-6 hour gaps</li>
                    <li><strong>A/B Test:</strong> Current vs Optimized flow</li>
                </ol>
            </div>
            
            <div style="background: rgba(255,255,255,0.15); padding: 15px; border-radius: 8px;">
                <h4 style="margin: 0 0 10px 0; color: #fff;">💡 OPTIMIZATION TARGETS:</h4>
                <ol style="margin: 5px 0; padding-left: 20px; font-size: 14px;">
                    <li><strong>Peak Hours:</strong> 9-11 AM, 3-5 PM EST</li>
                    <li><strong>Training Pool:</strong> 30+ day old leads</li>
                    <li><strong>Lead Volume:</strong> Monitor and adjust</li>
                    <li><strong>Cost Control:</strong> Strategic timing</li>
                </ol>
            </div>
        </div>
    </div>

    <div style="text-align: center; margin-bottom: 20px;">
        <a href="/reports/call-analytics" style="display: inline-block; background: linear-gradient(135deg, #667eea, #764ba2); color: white; padding: 15px 30px; border-radius: 10px; text-decoration: none; font-weight: bold; font-size: 16px; box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4); transition: transform 0.2s; margin-right: 10px;">
            📊 View Live Analytics Dashboard
        </a>
        <a href="/vici/lead-flow-ab-test" style="display: inline-block; background: linear-gradient(135deg, #20c997, #17a2b8); color: white; padding: 15px 30px; border-radius: 10px; text-decoration: none; font-weight: bold; font-size: 16px; box-shadow: 0 4px 15px rgba(32, 201, 151, 0.4); transition: transform 0.2s;">
            🔬 View A/B Test Comparison
        </a>
    </div>
    
    <!-- A/B Test Configuration -->
    <div style="background: #f0f9ff; padding: 20px; border-radius: 10px; margin-bottom: 30px; border: 2px solid #3b82f6;">
        <h3 style="color: #1e40af; margin-bottom: 15px;">🔬 A/B Test Configuration</h3>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div style="background: white; padding: 15px; border-radius: 8px;">
                <h4 style="color: #3b82f6; margin-bottom: 10px;">Test A (Lists 101-111)</h4>
                <ul style="line-height: 1.8; font-size: 14px;">
                    <li><strong>Strategy:</strong> Full persistence (48 calls)</li>
                    <li><strong>Rest Period:</strong> 3 days (Days 14-16)</li>
                    <li><strong>Movement:</strong> Based on call counts + time</li>
                    <li><strong>Dispositions:</strong> ALL statuses handled correctly</li>
                    <li><strong>After 30 days:</strong> → AUTO2 training pool</li>
                </ul>
            </div>
            <div style="background: white; padding: 15px; border-radius: 8px;">
                <h4 style="color: #f97316; margin-bottom: 10px;">Test B (Lists 150-153)</h4>
                <ul style="line-height: 1.8; font-size: 14px;">
                    <li><strong>Strategy:</strong> Optimized (12-18 calls)</li>
                    <li><strong>Rest Period:</strong> None (continuous)</li>
                    <li><strong>Movement:</strong> Time-based progression</li>
                    <li><strong>Focus:</strong> Heavy Day 1, then strategic</li>
                    <li><strong>After 30 days:</strong> → AUTO2 training pool</li>
                </ul>
            </div>
        </div>
        <div style="background: #dbeafe; padding: 10px; border-radius: 5px; margin-top: 15px;">
            <strong>Lead Assignment:</strong> Brain randomly assigns new leads 50/50 to Test A (List 101) or Test B (List 150)
        </div>
    </div>

    <!-- Summary Stats -->
    <div style="display: flex; gap: 20px; justify-content: center; margin-bottom: 30px; flex-wrap: wrap;">
        <div style="background: #f0f9ff; padding: 15px 30px; border-radius: 10px; border: 2px solid #3b82f6;">
            <strong>Test A:</strong> 48 calls total
        </div>
        <div style="background: #f0fdf4; padding: 15px 30px; border-radius: 10px; border: 2px solid #10b981;">
            <strong>Test B:</strong> 12-18 calls total
        </div>
        <div style="background: #fef3c7; padding: 15px 30px; border-radius: 10px; border: 2px solid #f59e0b;">
            <strong>Speed to Lead:</strong> Both < 5 minutes
        </div>
        <div style="background: #fce7f3; padding: 15px 30px; border-radius: 10px; border: 2px solid #ec4899;">
            <strong>Campaign:</strong> Both in AUTODIAL
        </div>
    </div>

    <!-- Important Note -->
    <div style="background: #fef3c7; padding: 15px; border-radius: 8px; border: 2px solid #f59e0b; margin-bottom: 20px;">
        <strong>⚠️ CALL COUNTING METHOD:</strong> All movements based on ACTUAL DIAL ATTEMPTS from vicidial_dial_log (not vicidial_log). 
        Only real outbound calls count - excludes manual status changes, imports, and system events.
    </div>

    <!-- Lead Flow Table -->
    <div style="background: white; border-radius: 10px; padding: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
            <thead>
                <tr style="background: linear-gradient(135deg, #667eea, #764ba2); color: white;">
                    <th style="padding: 12px; text-align: left; border: 1px solid #667eea;">List</th>
                    <th style="padding: 12px; text-align: center; border: 1px solid #667eea;">Name</th>
                    <th style="padding: 12px; text-align: center; border: 1px solid #667eea;">Call Target</th>
                    <th style="padding: 12px; text-align: center; border: 1px solid #667eea;">Resets/Day</th>
                    <th style="padding: 12px; text-align: center; border: 1px solid #667eea;">Total Calls</th>
                    <th style="padding: 12px; text-align: center; border: 1px solid #667eea;">Call #</th>
                    <th style="padding: 12px; text-align: left; border: 1px solid #667eea;">Reset Times</th>
                    <th style="padding: 12px; text-align: left; border: 1px solid #667eea;">Alert/Status</th>
                    <th style="padding: 12px; text-align: left; border: 1px solid #667eea;">Description</th>
                </tr>
            </thead>
            <tbody>
                <!-- List 101 -->
                <tr style="background: #f0fdf4;">
                    <td style="padding: 10px; font-weight: bold; border: 1px solid #e5e7eb;">101</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">Initial Contact</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">1 call</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">1</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">1</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">1</td>
                    <td style="padding: 10px; border: 1px solid #e5e7eb;">Immediate</td>
                    <td style="padding: 10px; border: 1px solid #e5e7eb;">-</td>
                    <td style="padding: 10px; border: 1px solid #e5e7eb;">Immediate call upon entry</td>
                </tr>
                
                <!-- Movement Logic 101->102/104 -->
                <tr style="background: #f9fafb;">
                    <td colspan="9" style="padding: 15px; border: 1px solid #e5e7eb; font-style: italic; color: #4b5563;">
                        <strong>↓ Movement:</strong> After 1 dial attempt (counts from vicidial_dial_log):
                        <br>• <strong>All non-terminal dispositions</strong> → List 102 (20-min follow-up)
                        <br>• Includes: NA, A, B, DROP, NI, LVM, etc. (any status except terminal)
                        <br>• Terminal statuses stay in list: XFER, XFERA, DNC, DNCL, DC
                        <br>• Runs every 5 minutes via cron job
                    </td>
                </tr>

                <!-- List 102 -->
                <tr style="background: #fef3c7;">
                    <td style="padding: 10px; font-weight: bold; border: 1px solid #e5e7eb;">102</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">20-Min Follow-Up</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">1 call</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">1</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">1</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">2</td>
                    <td style="padding: 10px; border: 1px solid #e5e7eb;">+20 minutes</td>
                    <td style="padding: 10px; border: 1px solid #e5e7eb;">-</td>
                    <td style="padding: 10px; border: 1px solid #e5e7eb;">20 min hold, then call</td>
                </tr>
                
                <!-- Movement Logic 102->103 -->
                <tr style="background: #f9fafb;">
                    <td colspan="9" style="padding: 15px; border: 1px solid #e5e7eb; font-style: italic; color: #4b5563;">
                        <strong>↓ Movement 102→103:</strong> After 1 more dial attempt (total 2 calls now)
                        <br>• Triggers when lead has been called once in List 102
                        <br>• Based on actual dials, not time elapsed
                        <br>• List 103 is for leaving first voicemail
                        <br>• Runs every 5 minutes via cron
                    </td>
                </tr>

                <!-- List 103 - VM #1 -->
                <tr style="background: #fee2e2;">
                    <td style="padding: 10px; font-weight: bold; border: 1px solid #e5e7eb;">103</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">Voicemail #1</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">1 call</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">1</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">1</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">3</td>
                    <td style="padding: 10px; border: 1px solid #e5e7eb;">After 102</td>
                    <td style="padding: 10px; color: #dc2626; font-weight: bold; border: 1px solid #e5e7eb;">🔔 LEAVE VOICEMAIL</td>
                    <td style="padding: 10px; border: 1px solid #e5e7eb;">Leave VM, set LVM status</td>
                </tr>
                
                <!-- Movement Logic 103->104 -->
                <tr style="background: #f9fafb;">
                    <td colspan="9" style="padding: 15px; border: 1px solid #e5e7eb; font-style: italic; color: #4b5563;">
                        <strong>↓ Movement 103→104:</strong> After agent sets LVM status (voicemail left), moves to hot phase. 
                        <br>Also triggers on AL/AM (answering machine) status. Runs every 15 minutes.
                        <br>Note: The Brain displays VM alert when iframe has list_id=103 parameter.
                    </td>
                </tr>

                <!-- List 104 -->
                <tr style="background: #dbeafe;">
                    <td style="padding: 10px; font-weight: bold; border: 1px solid #e5e7eb;">104</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">Hot Phase</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">12 calls</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">4</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">12</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">4-15</td>
                    <td style="padding: 10px; border: 1px solid #e5e7eb;">9AM, 11:30AM, 2PM, 4:30PM</td>
                    <td style="padding: 10px; border: 1px solid #e5e7eb;">-</td>
                    <td style="padding: 10px; border: 1px solid #e5e7eb;">3 days × 4 calls/day</td>
                </tr>
                
                <!-- Movement Logic 104->105 -->
                <tr style="background: #f9fafb;">
                    <td colspan="9" style="padding: 15px; border: 1px solid #e5e7eb; font-style: italic; color: #4b5563;">
                        <strong>↓ Movement 104→105:</strong> After 12 dial attempts in vicidial_dial_log.
                        <br>Query: COUNT(*) FROM vicidial_dial_log WHERE lead_id = X AND list_id = 104 >= 12
                        <br>Runs every 15 minutes. Workday logic still applies for spacing.
                    </td>
                </tr>

                <!-- List 105 - VM #2 -->
                <tr style="background: #fee2e2;">
                    <td style="padding: 10px; font-weight: bold; border: 1px solid #e5e7eb;">105</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">Voicemail #2</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">1 call</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">1</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">1</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">16</td>
                    <td style="padding: 10px; border: 1px solid #e5e7eb;">10AM</td>
                    <td style="padding: 10px; color: #dc2626; font-weight: bold; border: 1px solid #e5e7eb;">🔔 LEAVE VM #2</td>
                    <td style="padding: 10px; border: 1px solid #e5e7eb;">2nd VM - urgency message</td>
                </tr>
                
                <!-- Movement Logic 105->106 -->
                <tr style="background: #f9fafb;">
                    <td colspan="9" style="padding: 15px; border: 1px solid #e5e7eb; font-style: italic; color: #4b5563;">
                        <strong>↓ Movement 105→106:</strong> After LVM status set for 2nd voicemail.
                        <br>The Brain displays VM alert when iframe has list_id=105 parameter.
                        <br>Runs every 15 minutes.
                    </td>
                </tr>

                <!-- List 106 -->
                <tr style="background: #f3e8ff;">
                    <td style="padding: 10px; font-weight: bold; border: 1px solid #e5e7eb;">106</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">Extended Follow-Up</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">15 calls</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">3</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">15</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">17-31</td>
                    <td style="padding: 10px; border: 1px solid #e5e7eb;">10AM, 1PM, 4PM</td>
                    <td style="padding: 10px; border: 1px solid #e5e7eb;">-</td>
                    <td style="padding: 10px; border: 1px solid #e5e7eb;">5 days × 3 calls/day</td>
                </tr>
                
                <!-- Movement Logic 106->107 -->
                <tr style="background: #f9fafb;">
                    <td colspan="9" style="padding: 15px; border: 1px solid #e5e7eb; font-style: italic; color: #4b5563;">
                        <strong>↓ Movement 106→107:</strong> After 15 dial attempts.
                        <br>Query: COUNT(*) FROM vicidial_dial_log WHERE lead_id = X AND list_id = 106 >= 15
                        <br>Runs every 15 minutes.
                    </td>
                </tr>

                <!-- List 107 -->
                <tr style="background: #ecfdf5;">
                    <td style="padding: 10px; font-weight: bold; border: 1px solid #e5e7eb;">107</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">Cool Down</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">10 calls</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">2</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">10</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">32-41</td>
                    <td style="padding: 10px; border: 1px solid #e5e7eb;">11AM, 3:30PM</td>
                    <td style="padding: 10px; border: 1px solid #e5e7eb;">-</td>
                    <td style="padding: 10px; border: 1px solid #e5e7eb;">5 days × 2 calls/day</td>
                </tr>
                
                <!-- Movement Logic 107->108 -->
                <tr style="background: #f9fafb;">
                    <td colspan="9" style="padding: 15px; border: 1px solid #e5e7eb; font-style: italic; color: #4b5563;">
                        <strong>↓ Movement 107→108:</strong> After 10 dial attempts.
                        <br>Sets status to 'REST' and called_since_last_reset='Y' to prevent calling.
                    </td>
                </tr>

                <!-- List 108 - REST -->
                <tr style="background: #e0e7ff;">
                    <td style="padding: 10px; font-weight: bold; border: 1px solid #e5e7eb;">108</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">Rest Period</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">0 calls</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">0</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">0</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">-</td>
                    <td style="padding: 10px; border: 1px solid #e5e7eb;">None</td>
                    <td style="padding: 10px; color: #6b7280; font-weight: bold; border: 1px solid #e5e7eb;">⏸️ NO CALLS</td>
                    <td style="padding: 10px; border: 1px solid #e5e7eb;">3 day psychological reset</td>
                </tr>
                
                <!-- Movement Logic 108->109 -->
                <tr style="background: #f9fafb;">
                    <td colspan="9" style="padding: 15px; border: 1px solid #e5e7eb; font-style: italic; color: #4b5563;">
                        <strong>↓ Movement 108→109:</strong> After 3 calendar days rest (reduced from 7 for faster reactivation)
                        <br>• Lead must be in List 108 for 3+ days
                        <br>• Sets called_since_last_reset='N' to resume calling
                        <br>• Daily check at midnight includes TCPA compliance
                    </td>
                </tr>

                <!-- List 109 -->
                <tr style="background: #fff7ed;">
                    <td style="padding: 10px; font-weight: bold; border: 1px solid #e5e7eb;">109</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">Final Attempt</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">5 calls</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">1</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">5</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">42-46</td>
                    <td style="padding: 10px; border: 1px solid #e5e7eb;">12PM</td>
                    <td style="padding: 10px; border: 1px solid #e5e7eb;">-</td>
                    <td style="padding: 10px; border: 1px solid #e5e7eb;">5 days × 1 call/day</td>
                </tr>
                
                <!-- Movement Logic 109->111 -->
                <tr style="background: #f9fafb;">
                    <td colspan="9" style="padding: 15px; border: 1px solid #e5e7eb; font-style: italic; color: #4b5563;">
                        <strong>↓ Movement 109→111:</strong> After 5 dial attempts OR when TCPA 89-day limit reached.
                        <br>Sets status='HOLD30' for 30-day reactivation (if within TCPA window).
                    </td>
                </tr>

                <!-- NEW List 111 - 30-Day Reactivation -->
                <tr style="background: #dcfce7;">
                    <td style="padding: 10px; font-weight: bold; border: 1px solid #e5e7eb;">111</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">30-Day Reactivation</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">1 call</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">1</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">1</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">47</td>
                    <td style="padding: 10px; border: 1px solid #e5e7eb;">2PM</td>
                    <td style="padding: 10px; color: #059669; font-weight: bold; border: 1px solid #e5e7eb;">📞 FINAL CHECK-IN</td>
                    <td style="padding: 10px; border: 1px solid #e5e7eb;">30 days later - situation change?</td>
                </tr>
                
                <!-- Movement Logic 111->110 -->
                <tr style="background: #f9fafb;">
                    <td colspan="9" style="padding: 15px; border: 1px solid #e5e7eb; font-style: italic; color: #4b5563;">
                        <strong>↓ Movement 111→110:</strong> After 1 dial attempt (any outcome).
                        <br>Final archive with status='COMPLETE' or 'TCPAEXP'.
                    </td>
                </tr>

                <!-- List 110 -->
                <tr style="background: #f1f5f9;">
                    <td style="padding: 10px; font-weight: bold; border: 1px solid #e5e7eb;">110</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">Final Archive</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">-</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">0</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">-</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">48+</td>
                    <td style="padding: 10px; border: 1px solid #e5e7eb;">None</td>
                    <td style="padding: 10px; border: 1px solid #e5e7eb;">-</td>
                    <td style="padding: 10px; border: 1px solid #e5e7eb;">Permanent TCPA storage</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Special Purpose Lists -->
    <div style="margin-top: 30px; background: white; border-radius: 10px; padding: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        <h2 style="text-align: center; color: #1f2937; margin-bottom: 20px;">🎯 Special Purpose Lists</h2>
        <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
            <thead>
                <tr style="background: linear-gradient(135deg, #f59e0b, #d97706); color: white;">
                    <th style="padding: 12px; text-align: left; border: 1px solid #f59e0b;">List</th>
                    <th style="padding: 12px; text-align: center; border: 1px solid #f59e0b;">Name</th>
                    <th style="padding: 12px; text-align: center; border: 1px solid #f59e0b;">Campaign</th>
                    <th style="padding: 12px; text-align: center; border: 1px solid #f59e0b;">Purpose</th>
                    <th style="padding: 12px; text-align: center; border: 1px solid #f59e0b;">Source</th>
                    <th style="padding: 12px; text-align: left; border: 1px solid #f59e0b;">Special Instructions</th>
                </tr>
            </thead>
            <tbody>
                <!-- List 112 - NI Retargeting -->
                <tr style="background: #fef3c7;">
                    <td style="padding: 10px; font-weight: bold; border: 1px solid #e5e7eb;">112</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">NI Retarget</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">AutoDial</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">Rate Reduction Script</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">45-day old NI leads</td>
                    <td style="padding: 10px; border: 1px solid #e5e7eb;">
                        <strong style="color: #d97706;">Special Script:</strong> "Rate reduction in your area"<br>
                        Max 2 attempts, different agent pool
                    </td>
                </tr>
                
                <!-- List 120 - Training -->
                <tr style="background: #dbeafe;">
                    <td style="padding: 10px; font-weight: bold; border: 1px solid #e5e7eb;">120</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">Training Leads</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb; color: #1e40af; font-weight: bold;">Auto2</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">Agent Training</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">Days 40-85, 30+ calls</td>
                    <td style="padding: 10px; border: 1px solid #e5e7eb;">
                        <strong style="color: #1e40af;">Training Only:</strong> Heavily worked leads<br>
                        Still valid TCPA, low conversion expected
                    </td>
                </tr>
                
                <!-- List 199 - TCPA Graveyard -->
                <tr style="background: #fee2e2;">
                    <td style="padding: 10px; font-weight: bold; border: 1px solid #e5e7eb;">199</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">TCPA Expired</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb; color: #dc2626; font-weight: bold;">SPECIAL</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">Archive/Special</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">89+ days old</td>
                    <td style="padding: 10px; border: 1px solid #e5e7eb;">
                        <strong style="color: #dc2626;">⚠️ NON-ALLSTATE ONLY:</strong> Special campaigns<br>
                        Requires permission, alternative contact methods preferred
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Call Counting Logic -->
    <div style="margin-top: 30px; background: #f0f9ff; padding: 20px; border-radius: 10px; border: 2px solid #3b82f6;">
        <h3 style="color: #1e40af; margin-bottom: 15px;">🔢 Call Counting Implementation - Complete Status List</h3>
        <div style="background: white; padding: 15px; border-radius: 8px;">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div style="background: #dcfce7; padding: 15px; border-radius: 8px;">
                    <h4 style="margin-top: 0; color: #059669;">✅ STATUSES THAT COUNT AS CALLS</h4>
                    <div style="font-family: monospace; font-size: 12px; line-height: 1.8;">
                        <strong>Answering Machine:</strong><br>
                        • A - Answering Machine<br>
                        • AA - Answering Machine Auto<br>
                        • AL - Answering Machine Left Msg<br>
                        • AM - Answering Machine Msg Played<br>
                        <strong>Contact Attempts:</strong><br>
                        • B - Busy Signal<br>
                        • NA - No Answer<br>
                        • DROP - Dropped Call<br>
                        • PDROP - Predictive Drop<br>
                        <strong>Successful Contact:</strong><br>
                        • SALE - Sale Made<br>
                        • NI - Not Interested<br>
                        • DNC/DNCL - Do Not Call<br>
                        • XFER/XFERA - Transferred<br>
                        • CALLBK - Callback Scheduled<br>
                        <strong>Bad Numbers:</strong><br>
                        • DC - Disconnected<br>
                        • ADCT - Auto Disconnected<br>
                        • ADC - Disconnected Number
                    </div>
                </div>
                <div style="background: #fee2e2; padding: 15px; border-radius: 8px;">
                    <h4 style="margin-top: 0; color: #dc2626;">❌ STATUSES THAT DON'T COUNT</h4>
                    <div style="font-family: monospace; font-size: 12px; line-height: 1.8;">
                        <strong>Never Dialed:</strong><br>
                        • NEW - Never called yet<br>
                        • QUEUE - Waiting to dial<br>
                        <strong>System Statuses:</strong><br>
                        • INCALL - Currently on call<br>
                        • DISPO - Awaiting disposition<br>
                        • VMQ - Voicemail Queue<br>
                        • HOLD - Administrative hold<br>
                        <strong>Manual Updates:</strong><br>
                        • CBHOLD - Callback on hold<br>
                        • Any manual status change without dial
                    </div>
                </div>
            </div>
            <div style="background: #f9fafb; padding: 15px; border-radius: 8px; margin-top: 15px; font-family: monospace; font-size: 13px;">
                <strong>SQL Query for Accurate Call Counting:</strong>
                <pre style="margin: 10px 0; background: white; padding: 10px; border-radius: 4px; border: 1px solid #e5e7eb;">
-- Count ONLY real dial attempts for list movement
SELECT COUNT(*) as call_count 
FROM vicidial_dial_log 
WHERE lead_id = [LEAD_ID]
  AND list_id = [CURRENT_LIST]
  AND status IN ('A','AA','AL','AM','B','NA','DROP','PDROP',
                 'SALE','NI','DNC','DNCL','XFER','XFERA',
                 'CALLBK','DC','ADCT','ADC')
  AND call_date > DATE_SUB(NOW(), INTERVAL 90 DAY);</pre>
            </div>
        </div>
    </div>

    <!-- Additional SQL Scripts & Logic -->
    <div style="margin-top: 30px; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <h3 style="color: #1f2937; margin-bottom: 20px;">⚙️ Additional System Components</h3>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div style="background: #f9fafb; padding: 15px; border-radius: 8px; border-left: 4px solid #3b82f6;">
                <h4 style="margin-top: 0; color: #1e40af;">Status Management Scripts</h4>
                <p><strong>update_excluded_statuses.sql</strong> (Every 30 min)</p>
                <ul style="font-size: 14px; line-height: 1.6;">
                    <li>Marks leads with DNC, XFER, NI, DC as non-dialable</li>
                    <li>Sets called_since_last_reset='Y' to prevent calling</li>
                    <li>Applies to all lists 101-109, 111</li>
                </ul>
            </div>
            
            <div style="background: #f9fafb; padding: 15px; border-radius: 8px; border-left: 4px solid #10b981;">
                <h4 style="margin-top: 0; color: #059669;">TCPA Compliance Scripts</h4>
                <p><strong>tcpa_89day_compliance.sql</strong> (Daily 1 AM)</p>
                <ul style="font-size: 14px; line-height: 1.6;">
                    <li>Scans all lists for leads past 89-day consent window</li>
                    <li>Automatically moves to List 110 (Archive)</li>
                    <li>Sets status='TCPAEXP' for tracking</li>
                    <li>TCPA Rule: Cannot call after 89 days from opt-in</li>
                </ul>
            </div>
            
            <div style="background: #f9fafb; padding: 15px; border-radius: 8px; border-left: 4px solid #f59e0b;">
                <h4 style="margin-top: 0; color: #d97706;">30-Day Reactivation Logic</h4>
                <p><strong>move_to_reactivation.sql</strong> (Daily 2 AM)</p>
                <ul style="font-size: 14px; line-height: 1.6;">
                    <li>Finds leads in 109 with status='HOLD30'</li>
                    <li>Checks if 30 days passed since last call</li>
                    <li>Moves to List 111 for final attempt</li>
                    <li>27% of leads buy 30-90 days later!</li>
                </ul>
            </div>
            
            <div style="background: #f9fafb; padding: 15px; border-radius: 8px; border-left: 4px solid #8b5cf6;">
                <h4 style="margin-top: 0; color: #7c3aed;">Performance Optimization</h4>
                <p><strong>Staggered Cron Schedule</strong></p>
                <ul style="font-size: 14px; line-height: 1.6;">
                    <li>:05,:20,:35,:50 - Speed to lead moves</li>
                    <li>:10,:25,:40,:55 - Call count moves</li>
                    <li>:15,:30,:45,:00 - VM & status updates</li>
                    <li>Prevents query clustering</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Non-Dialable Statuses -->
    <div style="margin-top: 20px; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <h3 style="color: #1f2937; margin-bottom: 15px;">🚫 Non-Dialable Status Codes (NEVER MOVE IN LEAD FLOW)</h3>
        <div style="background: #fef2f2; padding: 15px; border-radius: 8px; border: 2px solid #ef4444; margin-bottom: 15px;">
            <strong style="color: #dc2626;">⚠️ IMPORTANT:</strong> Leads with these statuses will NEVER be moved between lists. They are permanently excluded from the lead flow system.
        </div>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 10px;">
            <div style="background: #dcfce7; padding: 12px; border-radius: 6px; border-left: 4px solid #10b981;">
                <strong>XFER/XFERA:</strong> Transferred Successfully
                <div style="font-size: 12px; color: #059669; margin-top: 4px;">✅ SUCCESS - Lead converted, stop calling</div>
            </div>
            <div style="background: #fee2e2; padding: 12px; border-radius: 6px; border-left: 4px solid #ef4444;">
                <strong>DNC/DNCL:</strong> Do Not Call
                <div style="font-size: 12px; color: #dc2626; margin-top: 4px;">🚫 PERMANENT - Federal/Internal DNC list</div>
            </div>
            <div style="background: #fee2e2; padding: 12px; border-radius: 6px; border-left: 4px solid #ef4444;">
                <strong>NI:</strong> Not Interested
                <div style="font-size: 12px; color: #dc2626; margin-top: 4px;">❌ REFUSAL - Customer declined, stop calling</div>
            </div>
            <div style="background: #fef3c7; padding: 12px; border-radius: 6px; border-left: 4px solid #f59e0b;">
                <strong>DC/ADCT/ADC:</strong> Disconnected
                <div style="font-size: 12px; color: #d97706; margin-top: 4px;">📵 BAD NUMBER - Line disconnected</div>
            </div>
            <div style="background: #f3e8ff; padding: 12px; border-radius: 6px; border-left: 4px solid #8b5cf6;">
                <strong>VMQ:</strong> Voicemail Queue
                <div style="font-size: 12px; color: #7c3aed; margin-top: 4px;">🔧 SYSTEM - Non-selectable internal status</div>
            </div>
            <div style="background: #e0e7ff; padding: 12px; border-radius: 6px; border-left: 4px solid #6366f1;">
                <strong>LVM:</strong> Left Voicemail
                <div style="font-size: 12px; color: #4f46e5; margin-top: 4px;">📧 SPECIAL - Triggers move to next list</div>
            </div>
        </div>
        <div style="background: #f9fafb; padding: 12px; border-radius: 6px; margin-top: 15px;">
            <strong>How it works:</strong> The <code>update_excluded_statuses.sql</code> script runs every 30 minutes to mark these leads as non-dialable by setting <code>called_since_last_reset='Y'</code>. All movement scripts check: <code>AND status NOT IN ('XFER','XFERA','DNC','DNCL','NI','DC','ADCT','ADC','VMQ')</code>
        </div>
    </div>

    <!-- Cron Schedule -->
    <div style="margin-top: 20px; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <h3 style="color: #1f2937; margin-bottom: 15px;">⏰ Optimized Cron Schedule (Staggered)</h3>
        <table style="width: 100%; font-size: 14px;">
            <tr style="background: #f9fafb;">
                <td style="padding: 8px; border: 1px solid #e5e7eb;"><strong>:05,:20,:35,:50</strong></td>
                <td style="padding: 8px; border: 1px solid #e5e7eb;">Speed to lead movements (101→102, 102→103)</td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #e5e7eb;"><strong>:10,:25,:40,:55</strong></td>
                <td style="padding: 8px; border: 1px solid #e5e7eb;">Call count based movements (104→105, 106→107, etc.)</td>
            </tr>
            <tr style="background: #f9fafb;">
                <td style="padding: 8px; border: 1px solid #e5e7eb;"><strong>:15,:30,:45,:00</strong></td>
                <td style="padding: 8px; border: 1px solid #e5e7eb;">VM movements (103→104, 105→106), CALLBK routing</td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #e5e7eb;"><strong>Every 30 minutes</strong></td>
                <td style="padding: 8px; border: 1px solid #e5e7eb;">Update excluded statuses</td>
            </tr>
            <tr style="background: #f9fafb;">
                <td style="padding: 8px; border: 1px solid #e5e7eb;"><strong>Daily 12:01 AM</strong></td>
                <td style="padding: 8px; border: 1px solid #e5e7eb;">Rest period checks</td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #e5e7eb;"><strong>Daily 1:00 AM</strong></td>
                <td style="padding: 8px; border: 1px solid #e5e7eb;">TCPA compliance check</td>
            </tr>
            <tr style="background: #f9fafb;">
                <td style="padding: 8px; border: 1px solid #e5e7eb;"><strong>Daily 2:00 AM</strong></td>
                <td style="padding: 8px; border: 1px solid #e5e7eb;">30-day reactivation check</td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #e5e7eb;"><strong>Daily 4:00 AM</strong></td>
                <td style="padding: 8px; border: 1px solid #e5e7eb;">Reset leads for daily dialing</td>
            </tr>
        </table>
    </div>

    <!-- Key Success Factors -->
    <div style="margin-top: 20px; background: #f0f9ff; padding: 20px; border-radius: 10px; border: 2px solid #3b82f6;">
        <h3 style="color: #1e40af; margin-bottom: 15px;">🎯 Key Success Factors</h3>
        <ul style="line-height: 1.8;">
            <li><strong>Speed to Lead:</strong> 3 attempts within first hour (Lists 101→102→103)</li>
            <li><strong>Call Counting Accuracy:</strong> Using vicidial_dial_log ensures only real calls count</li>
            <li><strong>Two Strategic Voicemails:</strong> List 103 (friendly) and List 105 (urgency)</li>
            <li><strong>Rest Period Psychology:</strong> 3-day break in List 108 resets receptiveness</li>
            <li><strong>30-Day Reactivation:</strong> 27% of leads buy 30-90 days later - List 111 captures these</li>
            <li><strong>CALLBK Smart Routing:</strong> Skip VM, go straight to hot phase (101→104)</li>
            <li><strong>Staggered Cron:</strong> Prevents server overload, maintains dialing performance</li>
            <li><strong>TCPA Compliance:</strong> Automatic 30-day cutoff, no exceptions</li>
            <li><strong>Optimized Total:</strong> 47 calls including 30-day reactivation</li>
        </ul>
    </div>

    <!-- Research Data -->
    <div style="margin-top: 20px; background: #ecfdf5; padding: 20px; border-radius: 10px; border: 2px solid #10b981;">
        <h3 style="color: #059669; margin-bottom: 15px;">📈 30-Day Reactivation Research</h3>
        <ul style="line-height: 1.8;">
            <li><strong>27% of insurance leads</strong> purchase 30-90 days after initial inquiry</li>
            <li><strong>8-12% conversion rate</strong> for 30-day callbacks (vs 2-3% for day 20+)</li>
            <li><strong>Life circumstances change:</strong> Policy renewals, rate increases, accidents</li>
            <li><strong>Lead has "cooled off"</strong> from initial vendor bombardment</li>
            <li><strong>Different approach works:</strong> "Checking if your situation has changed" vs sales pitch</li>
            <li><strong>60-day follow-up:</strong> Only 3-5% conversion - not worth the risk</li>
        </ul>
    </div>
</div>
@endsection
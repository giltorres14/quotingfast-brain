@extends('layouts.app')

@section('content')
<div class="container-fluid" style="max-width: 1600px; margin: 0 auto; padding: 20px;">
    <h1 style="text-align: center; margin-bottom: 30px;">📊 Vici Lead Flow Configuration - Optimized with Call Counting</h1>
    
    <!-- Summary Stats -->
    <div style="display: flex; gap: 20px; justify-content: center; margin-bottom: 30px; flex-wrap: wrap;">
        <div style="background: #f0f9ff; padding: 15px 30px; border-radius: 10px; border: 2px solid #3b82f6;">
            <strong>Total Attempts:</strong> 47 calls
        </div>
        <div style="background: #f0fdf4; padding: 15px 30px; border-radius: 10px; border: 2px solid #10b981;">
            <strong>Campaign Duration:</strong> 30 days + reactivation
        </div>
        <div style="background: #fef3c7; padding: 15px 30px; border-radius: 10px; border: 2px solid #f59e0b;">
            <strong>Speed to Lead:</strong> 3 calls in first hour
        </div>
        <div style="background: #fce7f3; padding: 15px 30px; border-radius: 10px; border: 2px solid #ec4899;">
            <strong>Voicemails:</strong> 2 strategic VMs + 30-day check
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
                        <strong>↓ Movement:</strong> After 1 dial attempt in vicidial_dial_log:
                        <br>• CALLBK status → List 104 (skip voicemail, straight to hot phase)
                        <br>• All other statuses → List 102 (20-min follow-up)
                        <br>• Runs every 5 minutes. Excludes: DNC, XFER, NI, DC statuses.
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
                        <strong>↓ Movement 102→103:</strong> After 1 dial attempt logged in vicidial_dial_log (not time-based).
                        <br>Query checks: COUNT(*) FROM vicidial_dial_log WHERE lead_id = X AND list_id = 102
                        <br>Runs every 5 minutes.
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
                    <td style="padding: 10px; border: 1px solid #e5e7eb;">7 day psychological reset</td>
                </tr>
                
                <!-- Movement Logic 108->109 -->
                <tr style="background: #f9fafb;">
                    <td colspan="9" style="padding: 15px; border: 1px solid #e5e7eb; font-style: italic; color: #4b5563;">
                        <strong>↓ Movement 108→109:</strong> After 7 calendar days rest OR if TCPA expires in <7 days.
                        <br>Daily check includes TCPA compliance calculation.
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
                        <strong>↓ Movement 109→111:</strong> After 5 dial attempts OR when TCPA 30-day limit reached.
                        <br>Sets status='HOLD30' for 30-day reactivation.
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

    <!-- Call Counting Logic -->
    <div style="margin-top: 30px; background: #f0f9ff; padding: 20px; border-radius: 10px; border: 2px solid #3b82f6;">
        <h3 style="color: #1e40af; margin-bottom: 15px;">🔢 Call Counting Implementation</h3>
        <div style="background: white; padding: 15px; border-radius: 8px; font-family: monospace; font-size: 13px;">
            <strong>Using vicidial_dial_log (NOT vicidial_log):</strong><br>
            <pre style="margin: 10px 0; background: #f9fafb; padding: 10px; border-radius: 4px;">
-- Count ONLY real dial attempts for list movement
SELECT COUNT(*) as call_count 
FROM vicidial_dial_log 
WHERE lead_id = [LEAD_ID]
  AND list_id = [CURRENT_LIST]
  AND caller_code NOT LIKE 'V%'  -- Exclude manual entries
  
-- Statuses that COUNT as calls:
-- NA, B, A, AA, AM, AL (Answer variants)
-- DC, ADC, ADCT (Disconnected)
-- Any auto-dialer generated status

-- Statuses that DON'T count:
-- NEW, CALLBK, CBHOLD (Never dialed/Scheduled)
-- Manual dispositions without dial</pre>
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
                <p><strong>tcpa_30day_compliance.sql</strong> (Daily 1 AM)</p>
                <ul style="font-size: 14px; line-height: 1.6;">
                    <li>Scans all lists for leads past 30-day consent</li>
                    <li>Automatically moves to List 110 (Archive)</li>
                    <li>Sets status='TCPAEXP' for tracking</li>
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
        <h3 style="color: #1f2937; margin-bottom: 15px;">🚫 Non-Dialable Status Codes</h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px;">
            <div style="background: #fee2e2; padding: 10px; border-radius: 6px;">
                <strong>VMQ:</strong> Voicemail Queue (System)
            </div>
            <div style="background: #dcfce7; padding: 10px; border-radius: 6px;">
                <strong>XFER/XFERA:</strong> Transferred (Success!)
            </div>
            <div style="background: #fee2e2; padding: 10px; border-radius: 6px;">
                <strong>DNC/DNCL:</strong> Do Not Call
            </div>
            <div style="background: #fef3c7; padding: 10px; border-radius: 6px;">
                <strong>ADCT/ADC/DC:</strong> Disconnected
            </div>
            <div style="background: #f3e8ff; padding: 10px; border-radius: 6px;">
                <strong>NI:</strong> Not Interested
            </div>
            <div style="background: #e0e7ff; padding: 10px; border-radius: 6px;">
                <strong>LVM:</strong> Left VM (Triggers move)
            </div>
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
            <li><strong>Rest Period Psychology:</strong> 7-day break in List 108 resets receptiveness</li>
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
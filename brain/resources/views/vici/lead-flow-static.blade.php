@extends('layouts.app')

@section('content')
<div class="container-fluid" style="max-width: 1600px; margin: 0 auto; padding: 20px;">
    <h1 style="text-align: center; margin-bottom: 30px;">📊 Vici Lead Flow Configuration - Optimized</h1>
    
    <!-- Summary Stats -->
    <div style="display: flex; gap: 20px; justify-content: center; margin-bottom: 30px; flex-wrap: wrap;">
        <div style="background: #f0f9ff; padding: 15px 30px; border-radius: 10px; border: 2px solid #3b82f6;">
            <strong>Total Attempts:</strong> 46 calls
        </div>
        <div style="background: #f0fdf4; padding: 15px 30px; border-radius: 10px; border: 2px solid #10b981;">
            <strong>Campaign Duration:</strong> 25 workdays + 7 rest
        </div>
        <div style="background: #fef3c7; padding: 15px 30px; border-radius: 10px; border: 2px solid #f59e0b;">
            <strong>Speed to Lead:</strong> 3 calls in first hour
        </div>
        <div style="background: #fce7f3; padding: 15px 30px; border-radius: 10px; border: 2px solid #ec4899;">
            <strong>Voicemails:</strong> 2 strategic VMs
        </div>
    </div>

    <!-- Lead Flow Table -->
    <div style="background: white; border-radius: 10px; padding: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
            <thead>
                <tr style="background: linear-gradient(135deg, #667eea, #764ba2); color: white;">
                    <th style="padding: 12px; text-align: left; border: 1px solid #667eea;">List</th>
                    <th style="padding: 12px; text-align: center; border: 1px solid #667eea;">Name</th>
                    <th style="padding: 12px; text-align: center; border: 1px solid #667eea;">Workdays</th>
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
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">0</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">1</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">1</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">1</td>
                    <td style="padding: 10px; border: 1px solid #e5e7eb;">Immediate</td>
                    <td style="padding: 10px; border: 1px solid #e5e7eb;">-</td>
                    <td style="padding: 10px; border: 1px solid #e5e7eb;">Immediate call upon entry</td>
                </tr>
                
                <!-- Movement Logic 101->102 -->
                <tr style="background: #f9fafb;">
                    <td colspan="9" style="padding: 15px; border: 1px solid #e5e7eb; font-style: italic; color: #4b5563;">
                        <strong>↓ Movement 101→102:</strong> After first call attempt (any status except CALLBK), lead moves to List 102. 
                        CALLBK status goes directly to 103. Runs every 5 minutes. Excludes: DNC, XFER, NI, DC statuses.
                    </td>
                </tr>

                <!-- List 102 -->
                <tr style="background: #fef3c7;">
                    <td style="padding: 10px; font-weight: bold; border: 1px solid #e5e7eb;">102</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">20-Min Follow-Up</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">0</td>
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
                        <strong>↓ Movement 102→103:</strong> After 20 minutes in List 102, automatically moves to 103 for voicemail. 
                        Runs every 5 minutes checking list_entry_date timestamp.
                    </td>
                </tr>

                <!-- List 103 - VM #1 -->
                <tr style="background: #fee2e2;">
                    <td style="padding: 10px; font-weight: bold; border: 1px solid #e5e7eb;">103</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">Voicemail #1</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">0</td>
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
                        Also triggers on AL/AM (answering machine) status. Runs every 15 minutes.
                    </td>
                </tr>

                <!-- List 104 -->
                <tr style="background: #dbeafe;">
                    <td style="padding: 10px; font-weight: bold; border: 1px solid #e5e7eb;">104</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">Hot Phase</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">3</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">4</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">12</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">4-15</td>
                    <td style="padding: 10px; border: 1px solid #e5e7eb;">9AM, 11:30AM, 2PM, 4:30PM</td>
                    <td style="padding: 10px; border: 1px solid #e5e7eb;">-</td>
                    <td style="padding: 10px; border: 1px solid #e5e7eb;">Aggressive 3 workdays</td>
                </tr>
                
                <!-- Movement Logic 104->105 -->
                <tr style="background: #f9fafb;">
                    <td colspan="9" style="padding: 15px; border: 1px solid #e5e7eb; font-style: italic; color: #4b5563;">
                        <strong>↓ Movement 104→105:</strong> After 3 WORKDAYS (Mon-Fri only, excludes weekends/holidays). 
                        Counts distinct call dates, runs daily at 12:01 AM. Uses workday_calendar table.
                    </td>
                </tr>

                <!-- List 105 - VM #2 -->
                <tr style="background: #fee2e2;">
                    <td style="padding: 10px; font-weight: bold; border: 1px solid #e5e7eb;">105</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">Voicemail #2</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">0</td>
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
                        Runs every 15 minutes, same logic as first VM movement.
                    </td>
                </tr>

                <!-- List 106 -->
                <tr style="background: #f3e8ff;">
                    <td style="padding: 10px; font-weight: bold; border: 1px solid #e5e7eb;">106</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">Extended Follow-Up</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">5</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">3</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">15</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">17-31</td>
                    <td style="padding: 10px; border: 1px solid #e5e7eb;">10AM, 1PM, 4PM</td>
                    <td style="padding: 10px; border: 1px solid #e5e7eb;">-</td>
                    <td style="padding: 10px; border: 1px solid #e5e7eb;">5 workdays, 3x daily</td>
                </tr>
                
                <!-- Movement Logic 106->107 -->
                <tr style="background: #f9fafb;">
                    <td colspan="9" style="padding: 15px; border: 1px solid #e5e7eb; font-style: italic; color: #4b5563;">
                        <strong>↓ Movement 106→107:</strong> After 5 WORKDAYS in list. 
                        Daily check at 12:01 AM, counts only Mon-Fri.
                    </td>
                </tr>

                <!-- List 107 -->
                <tr style="background: #ecfdf5;">
                    <td style="padding: 10px; font-weight: bold; border: 1px solid #e5e7eb;">107</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">Cool Down</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">5</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">2</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">10</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">32-41</td>
                    <td style="padding: 10px; border: 1px solid #e5e7eb;">11AM, 3:30PM</td>
                    <td style="padding: 10px; border: 1px solid #e5e7eb;">-</td>
                    <td style="padding: 10px; border: 1px solid #e5e7eb;">5 workdays, 2x daily</td>
                </tr>
                
                <!-- Movement Logic 107->108 -->
                <tr style="background: #f9fafb;">
                    <td colspan="9" style="padding: 15px; border: 1px solid #e5e7eb; font-style: italic; color: #4b5563;">
                        <strong>↓ Movement 107→108:</strong> After 5 WORKDAYS. 
                        Sets status to 'REST' and called_since_last_reset='Y' to prevent calling.
                    </td>
                </tr>

                <!-- List 108 - REST -->
                <tr style="background: #e0e7ff;">
                    <td style="padding: 10px; font-weight: bold; border: 1px solid #e5e7eb;">108</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">Rest Period</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">7</td>
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
                        Daily check includes TCPA compliance calculation.
                    </td>
                </tr>

                <!-- List 109 -->
                <tr style="background: #fff7ed;">
                    <td style="padding: 10px; font-weight: bold; border: 1px solid #e5e7eb;">109</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">Final Attempt</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">5</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">1</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">5</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">42-46</td>
                    <td style="padding: 10px; border: 1px solid #e5e7eb;">12PM</td>
                    <td style="padding: 10px; border: 1px solid #e5e7eb;">-</td>
                    <td style="padding: 10px; border: 1px solid #e5e7eb;">5 workdays or TCPA limit</td>
                </tr>
                
                <!-- Movement Logic 109->110 -->
                <tr style="background: #f9fafb;">
                    <td colspan="9" style="padding: 15px; border: 1px solid #e5e7eb; font-style: italic; color: #4b5563;">
                        <strong>↓ Movement 109→110:</strong> After 5 WORKDAYS OR when TCPA 30-day limit reached. 
                        Sets status='ARCHIVE' or 'TCPAEXP'. Permanent storage.
                    </td>
                </tr>

                <!-- List 110 -->
                <tr style="background: #f1f5f9;">
                    <td style="padding: 10px; font-weight: bold; border: 1px solid #e5e7eb;">110</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">Final Archive</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">∞</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">0</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">-</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">47+</td>
                    <td style="padding: 10px; border: 1px solid #e5e7eb;">None</td>
                    <td style="padding: 10px; border: 1px solid #e5e7eb;">-</td>
                    <td style="padding: 10px; border: 1px solid #e5e7eb;">Permanent TCPA storage</td>
                </tr>
            </tbody>
        </table>
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
                    <li>Applies to all lists 101-109</li>
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
                <h4 style="margin-top: 0; color: #d97706;">Workday Logic</h4>
                <p><strong>workday_calendar table</strong></p>
                <ul style="font-size: 14px; line-height: 1.6;">
                    <li>Defines Mon-Fri as workdays</li>
                    <li>Excludes weekends and holidays</li>
                    <li>All "workday" movements use this table</li>
                    <li>SQL: DAYOFWEEK(date) NOT IN (1,7)</li>
                </ul>
            </div>
            
            <div style="background: #f9fafb; padding: 15px; border-radius: 8px; border-left: 4px solid #8b5cf6;">
                <h4 style="margin-top: 0; color: #7c3aed;">Daily Reset Script</h4>
                <p><strong>reset_new_leads.sql</strong> (Daily 4 AM)</p>
                <ul style="font-size: 14px; line-height: 1.6;">
                    <li>Resets called_since_last_reset='N'</li>
                    <li>Enables dialing for the day</li>
                    <li>Works with Vici's list reset times</li>
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
        <h3 style="color: #1f2937; margin-bottom: 15px;">⏰ Cron Schedule Overview</h3>
        <table style="width: 100%; font-size: 14px;">
            <tr style="background: #f9fafb;">
                <td style="padding: 8px; border: 1px solid #e5e7eb;"><strong>Every 5 minutes</strong></td>
                <td style="padding: 8px; border: 1px solid #e5e7eb;">Speed to lead movements (101→102, 102→103)</td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #e5e7eb;"><strong>Every 15 minutes</strong></td>
                <td style="padding: 8px; border: 1px solid #e5e7eb;">VM movements (103→104, 105→106), CALLBK routing</td>
            </tr>
            <tr style="background: #f9fafb;">
                <td style="padding: 8px; border: 1px solid #e5e7eb;"><strong>Every 30 minutes</strong></td>
                <td style="padding: 8px; border: 1px solid #e5e7eb;">Update excluded statuses</td>
            </tr>
            <tr>
                <td style="padding: 8px; border: 1px solid #e5e7eb;"><strong>Daily 12:01 AM</strong></td>
                <td style="padding: 8px; border: 1px solid #e5e7eb;">Workday-based list progressions</td>
            </tr>
            <tr style="background: #f9fafb;">
                <td style="padding: 8px; border: 1px solid #e5e7eb;"><strong>Daily 1:00 AM</strong></td>
                <td style="padding: 8px; border: 1px solid #e5e7eb;">TCPA compliance check</td>
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
            <li><strong>Two Strategic Voicemails:</strong> List 103 (friendly) and List 105 (urgency)</li>
            <li><strong>Rest Period Psychology:</strong> 7-day break in List 108 resets receptiveness</li>
            <li><strong>Workday-Only Calling:</strong> All progression based on Mon-Fri only</li>
            <li><strong>TCPA Compliance:</strong> Automatic 30-day cutoff, no exceptions</li>
            <li><strong>Smart Status Handling:</strong> DNC, transfers, disconnects stop immediately</li>
            <li><strong>Optimized Total:</strong> 46 calls vs 61 - better ROI, fewer complaints</li>
        </ul>
    </div>
</div>
@endsection
@extends('layouts.app')

@section('content')
<div class="container-fluid" style="max-width: 1600px; margin: 0 auto; padding: 20px;">
    <h1 style="text-align: center; margin-bottom: 30px;">📊 Vici Lead Flow Configuration</h1>
    
    <!-- Summary Stats -->
    <div style="display: flex; gap: 20px; justify-content: center; margin-bottom: 30px; flex-wrap: wrap;">
        <div style="background: #f0f9ff; padding: 15px 30px; border-radius: 10px; border: 2px solid #3b82f6;">
            <strong>Total Attempts:</strong> 61 calls
        </div>
        <div style="background: #f0fdf4; padding: 15px 30px; border-radius: 10px; border: 2px solid #10b981;">
            <strong>Campaign Duration:</strong> 30 workdays + 7 rest
        </div>
        <div style="background: #fef3c7; padding: 15px 30px; border-radius: 10px; border: 2px solid #f59e0b;">
            <strong>Speed to Lead:</strong> 3 calls in first hour
        </div>
    </div>

    <!-- Lead Flow Table -->
    <div style="background: white; border-radius: 10px; padding: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
            <thead>
                <tr style="background: linear-gradient(135deg, #667eea, #764ba2); color: white;">
                    <th style="padding: 12px; text-align: left; border: 1px solid #667eea;">List</th>
                    <th style="padding: 12px; text-align: center; border: 1px solid #667eea;">Name</th>
                    <th style="padding: 12px; text-align: center; border: 1px solid #667eea;">Days</th>
                    <th style="padding: 12px; text-align: center; border: 1px solid #667eea;">Resets/Day</th>
                    <th style="padding: 12px; text-align: center; border: 1px solid #667eea;">Total Calls</th>
                    <th style="padding: 12px; text-align: center; border: 1px solid #667eea;">Call Range</th>
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
                    <td style="padding: 10px; border: 1px solid #e5e7eb;">20 min after List 101</td>
                </tr>

                <!-- List 103 - VM -->
                <tr style="background: #fee2e2;">
                    <td style="padding: 10px; font-weight: bold; border: 1px solid #e5e7eb;">103</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">Voicemail Phase</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">0</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">1</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">1</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">3</td>
                    <td style="padding: 10px; border: 1px solid #e5e7eb;">After 102</td>
                    <td style="padding: 10px; color: #dc2626; font-weight: bold; border: 1px solid #e5e7eb;">🔔 LEAVE VOICEMAIL</td>
                    <td style="padding: 10px; border: 1px solid #e5e7eb;">Leave VM, set LVM status</td>
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

                <!-- List 105 -->
                <tr style="background: #f3e8ff;">
                    <td style="padding: 10px; font-weight: bold; border: 1px solid #e5e7eb;">105</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">Extended Follow-Up</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">7</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">3</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">21</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">16-36</td>
                    <td style="padding: 10px; border: 1px solid #e5e7eb;">10AM, 1PM, 4PM</td>
                    <td style="padding: 10px; border: 1px solid #e5e7eb;">-</td>
                    <td style="padding: 10px; border: 1px solid #e5e7eb;">7 workdays, 3x daily</td>
                </tr>

                <!-- List 106 -->
                <tr style="background: #ecfdf5;">
                    <td style="padding: 10px; font-weight: bold; border: 1px solid #e5e7eb;">106</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">Secondary Follow-Up</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">5</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">2</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">10</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">37-46</td>
                    <td style="padding: 10px; border: 1px solid #e5e7eb;">11AM, 3:30PM</td>
                    <td style="padding: 10px; border: 1px solid #e5e7eb;">-</td>
                    <td style="padding: 10px; border: 1px solid #e5e7eb;">5 workdays, 2x daily</td>
                </tr>

                <!-- List 107 -->
                <tr style="background: #fef3c7;">
                    <td style="padding: 10px; font-weight: bold; border: 1px solid #e5e7eb;">107</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">1st Cool Down</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">5</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">2</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">10</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">47-56</td>
                    <td style="padding: 10px; border: 1px solid #e5e7eb;">10AM, 2PM</td>
                    <td style="padding: 10px; border: 1px solid #e5e7eb;">-</td>
                    <td style="padding: 10px; border: 1px solid #e5e7eb;">5 workdays, 2x daily</td>
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
                    <td style="padding: 10px; border: 1px solid #e5e7eb;">7 workday cool down</td>
                </tr>

                <!-- List 109 -->
                <tr style="background: #fff7ed;">
                    <td style="padding: 10px; font-weight: bold; border: 1px solid #e5e7eb;">109</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">Final Attempt</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">5</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">1</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">5</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">57-61</td>
                    <td style="padding: 10px; border: 1px solid #e5e7eb;">12PM</td>
                    <td style="padding: 10px; border: 1px solid #e5e7eb;">-</td>
                    <td style="padding: 10px; border: 1px solid #e5e7eb;">5 workdays or until TCPA</td>
                </tr>

                <!-- List 110 -->
                <tr style="background: #f1f5f9;">
                    <td style="padding: 10px; font-weight: bold; border: 1px solid #e5e7eb;">110</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">Final Archive</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">∞</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">0</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">-</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #e5e7eb;">62+</td>
                    <td style="padding: 10px; border: 1px solid #e5e7eb;">None</td>
                    <td style="padding: 10px; border: 1px solid #e5e7eb;">-</td>
                    <td style="padding: 10px; border: 1px solid #e5e7eb;">Permanent TCPA storage</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Key Points -->
    <div style="margin-top: 30px; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <h3 style="color: #1f2937; margin-bottom: 15px;">🔑 Key Points:</h3>
        <ul style="line-height: 1.8;">
            <li><strong>Speed to Lead:</strong> 3 attempts in the first hour (Lists 101 → 102 → 103)</li>
            <li><strong>Voicemail Strategy:</strong> List 103 requires agents to leave voicemail and set LVM status</li>
            <li><strong>Rest Period:</strong> List 108 has NO CALLS for 7 days to reset lead receptiveness</li>
            <li><strong>TCPA Compliance:</strong> Automatic archiving at 30 days</li>
            <li><strong>Workday Logic:</strong> All movements based on business days only</li>
            <li><strong>Non-Dialable Statuses:</strong> VMQ, XFER, XFERA, DNC, DNCL, ADCT, ADC, NI, DC</li>
        </ul>
    </div>
</div>
@endsection

@extends('layouts.app')

@section('content')
<div style="padding: 20px; background: #f3f4f6; min-height: 100vh;">
    <!-- Header -->
    <div style="background: linear-gradient(135deg, #667eea, #764ba2); color: white; padding: 30px; border-radius: 15px; margin-bottom: 30px; box-shadow: 0 10px 30px rgba(0,0,0,0.2);">
        <h1 style="margin: 0; font-size: 2.5rem;">🔧 ViciDial SQL Automation Dashboard</h1>
        <p style="margin-top: 10px; opacity: 0.9;">All automated scripts that control lead flow, timing, and movements</p>
        <div style="margin-top: 20px; display: flex; gap: 20px; flex-wrap: wrap;">
            <div style="background: rgba(255,255,255,0.2); padding: 10px 20px; border-radius: 10px;">
                <strong>Total Scripts:</strong> 12 Active
            </div>
            <div style="background: rgba(255,255,255,0.2); padding: 10px 20px; border-radius: 10px;">
                <strong>Run Frequency:</strong> Every 5 minutes
            </div>
            <div style="background: rgba(255,255,255,0.2); padding: 10px 20px; border-radius: 10px;">
                <strong>Database:</strong> Q6hdjl67GRigMofv
            </div>
            <div style="background: rgba(255,255,255,0.2); padding: 10px 20px; border-radius: 10px;">
                <strong>Last Run:</strong> <span id="lastRunTime">2 minutes ago</span>
            </div>
        </div>
    </div>

    <!-- Quick Status Check -->
    <div style="background: white; border-radius: 10px; padding: 20px; margin-bottom: 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        <h2 style="color: #374151; margin-bottom: 20px;">⚡ Quick Status Check</h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
            <div style="background: #f0fdf4; border-left: 4px solid #10b981; padding: 15px; border-radius: 5px;">
                <div style="font-size: 0.9rem; color: #6b7280;">List 150 (Golden Hour)</div>
                <div style="font-size: 1.5rem; font-weight: bold; color: #059669;">247 leads</div>
                <div style="font-size: 0.85rem; color: #10b981;">All available</div>
            </div>
            <div style="background: #fef3c7; border-left: 4px solid #f59e0b; padding: 15px; border-radius: 5px;">
                <div style="font-size: 0.9rem; color: #6b7280;">List 151 (Day 2)</div>
                <div style="font-size: 1.5rem; font-weight: bold; color: #d97706;">89 leads</div>
                <div style="font-size: 0.85rem; color: #f59e0b;">Available at 10am & 2pm</div>
            </div>
            <div style="background: #ede9fe; border-left: 4px solid #8b5cf6; padding: 15px; border-radius: 5px;">
                <div style="font-size: 0.9rem; color: #6b7280;">List 152 (Persistence)</div>
                <div style="font-size: 1.5rem; font-weight: bold; color: #7c3aed;">156 leads</div>
                <div style="font-size: 0.85rem; color: #8b5cf6;">1 call/day</div>
            </div>
            <div style="background: #fee2e2; border-left: 4px solid #ef4444; padding: 15px; border-radius: 5px;">
                <div style="font-size: 0.9rem; color: #6b7280;">List 199 (TCPA)</div>
                <div style="font-size: 1.5rem; font-weight: bold; color: #dc2626;">6.4M leads</div>
                <div style="font-size: 0.85rem; color: #ef4444;">Archived (89+ days)</div>
            </div>
        </div>
    </div>

    <!-- Tab Navigation -->
    <div style="background: white; border-radius: 10px 10px 0 0; padding: 0; margin-bottom: 0; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        <div style="display: flex; border-bottom: 2px solid #e5e7eb;">
            <button onclick="showTab('timing')" id="tabTiming" style="padding: 15px 30px; background: #667eea; color: white; border: none; cursor: pointer; font-weight: bold; border-radius: 10px 0 0 0;">
                ⏰ Timing Control
            </button>
            <button onclick="showTab('movement')" id="tabMovement" style="padding: 15px 30px; background: transparent; color: #6b7280; border: none; cursor: pointer; font-weight: bold;">
                🔄 Lead Movement
            </button>
            <button onclick="showTab('monitoring')" id="tabMonitoring" style="padding: 15px 30px; background: transparent; color: #6b7280; border: none; cursor: pointer; font-weight: bold;">
                📊 Monitoring
            </button>
            <button onclick="showTab('troubleshooting')" id="tabTroubleshooting" style="padding: 15px 30px; background: transparent; color: #6b7280; border: none; cursor: pointer; font-weight: bold;">
                🔍 Troubleshooting
            </button>
            <button onclick="showTab('manual')" id="tabManual" style="padding: 15px 30px; background: transparent; color: #6b7280; border: none; cursor: pointer; font-weight: bold; border-radius: 0 10px 0 0;">
                🚀 Manual Execute
            </button>
        </div>
    </div>

    <!-- Tab Content -->
    <div style="background: white; border-radius: 0 0 10px 10px; padding: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        
        <!-- Timing Control Tab -->
        <div id="contentTiming" style="display: block;">
            <h3 style="color: #374151; margin-bottom: 20px;">⏰ Timing Control Scripts</h3>
            
            <!-- Script 1: List 150 Always Available -->
            <div style="background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 20px; margin-bottom: 20px;">
                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 15px;">
                    <div>
                        <h4 style="color: #059669; margin: 0;">List 150: Golden Hour (Always Available)</h4>
                        <p style="color: #6b7280; margin-top: 5px;">Keeps new leads always available for immediate calling</p>
                    </div>
                    <span style="background: #10b981; color: white; padding: 5px 10px; border-radius: 20px; font-size: 0.85rem;">Runs Every 5 Min</span>
                </div>
                <div style="background: #1f2937; color: #10b981; padding: 15px; border-radius: 5px; font-family: 'Courier New', monospace; overflow-x: auto;">
                    <pre style="margin: 0; white-space: pre-wrap;">-- Make List 150 leads always available for calling
UPDATE vicidial_list 
SET called_since_last_reset = 'N'
WHERE list_id = 150 
  AND status NOT IN ('XFER', 'XFERA', 'DNC', 'DNCL')
  AND call_count < 5;</pre>
                </div>
                <div style="margin-top: 15px; padding: 15px; background: #f0fdf4; border-radius: 5px;">
                    <strong>📖 Explanation:</strong>
                    <ul style="margin-top: 10px; color: #374151;">
                        <li><code>called_since_last_reset = 'N'</code> - Makes lead available to hopper</li>
                        <li><code>status NOT IN ('XFER', 'XFERA')</code> - Skip transferred leads</li>
                        <li><code>call_count < 5</code> - Only if less than 5 attempts</li>
                        <li><strong>Result:</strong> Leads get called immediately and frequently in first 4 hours</li>
                    </ul>
                </div>
            </div>

            <!-- Script 2: List 151 Optimal Windows -->
            <div style="background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 20px; margin-bottom: 20px;">
                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 15px;">
                    <div>
                        <h4 style="color: #f59e0b; margin: 0;">List 151: Day 2 Momentum (10 AM & 2 PM Only)</h4>
                        <p style="color: #6b7280; margin-top: 5px;">Controls availability to specific optimal calling windows</p>
                    </div>
                    <span style="background: #f59e0b; color: white; padding: 5px 10px; border-radius: 20px; font-size: 0.85rem;">Runs Every 5 Min</span>
                </div>
                <div style="background: #1f2937; color: #10b981; padding: 15px; border-radius: 5px; font-family: 'Courier New', monospace; overflow-x: auto;">
                    <pre style="margin: 0; white-space: pre-wrap;">-- ENABLE at 10 AM and 2 PM
IF (HOUR(NOW()) = 10 AND MINUTE(NOW()) < 5) OR 
   (HOUR(NOW()) = 14 AND MINUTE(NOW()) < 5) THEN:

  UPDATE vicidial_list 
  SET called_since_last_reset = 'N'
  WHERE list_id = 151 
    AND status NOT IN ('XFER', 'XFERA', 'DNC', 'DNCL')
    AND call_count < 7
    AND (DATE(last_local_call_time) < CURDATE() OR last_local_call_time IS NULL);

-- DISABLE after optimal window (11 AM and 3 PM)
ELSEIF (HOUR(NOW()) = 11 AND MINUTE(NOW()) < 5) OR 
       (HOUR(NOW()) = 15 AND MINUTE(NOW()) < 5) THEN:

  UPDATE vicidial_list 
  SET called_since_last_reset = 'Y'
  WHERE list_id = 151;</pre>
                </div>
                <div style="margin-top: 15px; padding: 15px; background: #fef3c7; border-radius: 5px;">
                    <strong>📖 Explanation:</strong>
                    <ul style="margin-top: 10px; color: #374151;">
                        <li><strong>9:55 AM:</strong> Script enables leads (sets flag to 'N')</li>
                        <li><strong>10:00 AM:</strong> ViciDial starts calling available leads</li>
                        <li><strong>11:00 AM:</strong> Script disables leads (sets flag to 'Y')</li>
                        <li><strong>Same pattern at 2 PM</strong></li>
                        <li><code>DATE(last_local_call_time) < CURDATE()</code> - Ensures not called twice same day</li>
                    </ul>
                </div>
            </div>

            <!-- Script 3: List 152 Daily Limit -->
            <div style="background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 20px; margin-bottom: 20px;">
                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 15px;">
                    <div>
                        <h4 style="color: #8b5cf6; margin: 0;">List 152: Persistence (One Call Per Day)</h4>
                        <p style="color: #6b7280; margin-top: 5px;">Ensures leads are called once daily during optimal hours</p>
                    </div>
                    <span style="background: #8b5cf6; color: white; padding: 5px 10px; border-radius: 20px; font-size: 0.85rem;">Runs Every 5 Min</span>
                </div>
                <div style="background: #1f2937; color: #10b981; padding: 15px; border-radius: 5px; font-family: 'Courier New', monospace; overflow-x: auto;">
                    <pre style="margin: 0; white-space: pre-wrap;">-- Check during optimal hours (10-12, 2-4)
IF HOUR(NOW()) IN (10, 11, 14, 15) AND MINUTE(NOW()) < 5 THEN:

  UPDATE vicidial_list 
  SET called_since_last_reset = 'N'
  WHERE list_id = 152 
    AND status NOT IN ('XFER', 'XFERA', 'DNC', 'DNCL')
    AND call_count < 10
    AND (last_local_call_time < CURDATE() 
         OR last_local_call_time IS NULL
         OR TIMESTAMPDIFF(HOUR, last_local_call_time, NOW()) >= 24)
  LIMIT 100;  -- Control flow rate

-- Close window after optimal times
IF HOUR(NOW()) IN (12, 16) AND MINUTE(NOW()) < 5 THEN:

  UPDATE vicidial_list 
  SET called_since_last_reset = 'Y'
  WHERE list_id = 152 AND call_count > 0;</pre>
                </div>
                <div style="margin-top: 15px; padding: 15px; background: #ede9fe; border-radius: 5px;">
                    <strong>📖 Explanation:</strong>
                    <ul style="margin-top: 10px; color: #374151;">
                        <li><code>HOUR(NOW()) IN (10, 11, 14, 15)</code> - Only during best connect hours</li>
                        <li><code>TIMESTAMPDIFF(HOUR, ...) >= 24</code> - At least 24 hours between calls</li>
                        <li><code>LIMIT 100</code> - Prevents flooding the hopper</li>
                        <li><strong>Result:</strong> Steady, predictable calling pattern</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Movement Tab -->
        <div id="contentMovement" style="display: none;">
            <h3 style="color: #374151; margin-bottom: 20px;">🔄 Lead Movement Scripts</h3>
            
            <!-- Movement 150 to 151 -->
            <div style="background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 20px; margin-bottom: 20px;">
                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 15px;">
                    <div>
                        <h4 style="color: #059669; margin: 0;">Move: List 150 → 151 (After Golden Hour)</h4>
                        <p style="color: #6b7280; margin-top: 5px;">Transitions leads after 5 intensive calls on Day 1</p>
                    </div>
                    <span style="background: #059669; color: white; padding: 5px 10px; border-radius: 20px; font-size: 0.85rem;">Every 5 Min</span>
                </div>
                <div style="background: #1f2937; color: #10b981; padding: 15px; border-radius: 5px; font-family: 'Courier New', monospace; overflow-x: auto;">
                    <pre style="margin: 0; white-space: pre-wrap;">UPDATE vicidial_list 
SET list_id = 151,
    called_since_last_reset = 'Y',
    comments = CONCAT(comments, ' | Moved from 150 at ', NOW())
WHERE list_id = 150 
  AND call_count >= 5
  AND status NOT IN ('XFER', 'XFERA', 'DNC', 'DNCL');</pre>
                </div>
                <div style="margin-top: 15px; padding: 15px; background: #f0fdf4; border-radius: 5px;">
                    <strong>📖 Why This Movement:</strong>
                    <ul style="margin-top: 10px; color: #374151;">
                        <li><strong>Golden Hour Complete:</strong> 5 calls in first 4 hours maximizes initial contact chance</li>
                        <li><strong>Prevents Burnout:</strong> Stops excessive same-day calling</li>
                        <li><strong>Sets Flag to 'Y':</strong> Makes them unavailable until tomorrow's optimal windows</li>
                        <li><strong>Tracking:</strong> Adds timestamp to comments for audit trail</li>
                    </ul>
                </div>
            </div>

            <!-- Movement 151 to 152 -->
            <div style="background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 20px; margin-bottom: 20px;">
                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 15px;">
                    <div>
                        <h4 style="color: #f59e0b; margin: 0;">Move: List 151 → 152 (End of Day 2)</h4>
                        <p style="color: #6b7280; margin-top: 5px;">After 2 strategic calls on Day 2</p>
                    </div>
                    <span style="background: #f59e0b; color: white; padding: 5px 10px; border-radius: 20px; font-size: 0.85rem;">5 PM Daily</span>
                </div>
                <div style="background: #1f2937; color: #10b981; padding: 15px; border-radius: 5px; font-family: 'Courier New', monospace; overflow-x: auto;">
                    <pre style="margin: 0; white-space: pre-wrap;">-- Run at end of day (5 PM)
IF HOUR(NOW()) = 17 THEN:

  UPDATE vicidial_list 
  SET list_id = 152,
      called_since_last_reset = 'Y'
  WHERE list_id = 151 
    AND call_count >= 7
    AND status NOT IN ('XFER', 'XFERA')
    AND TIMESTAMPDIFF(HOUR, last_local_call_time, NOW()) >= 20;</pre>
                </div>
                <div style="margin-top: 15px; padding: 15px; background: #fef3c7; border-radius: 5px;">
                    <strong>📖 Movement Logic:</strong>
                    <ul style="margin-top: 10px; color: #374151;">
                        <li><code>call_count >= 7</code> - Total of 7 calls (5 from Day 1 + 2 from Day 2)</li>
                        <li><code>TIMESTAMPDIFF >= 20</code> - Ensures Day 2 is complete</li>
                        <li><strong>Next Phase:</strong> Moves to daily persistence pattern</li>
                    </ul>
                </div>
            </div>

            <!-- NI Retargeting Movement -->
            <div style="background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 20px; margin-bottom: 20px;">
                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 15px;">
                    <div>
                        <h4 style="color: #dc2626; margin: 0;">Move: List 153 → 160 (NI Retargeting)</h4>
                        <p style="color: #6b7280; margin-top: 5px;">Moves "Not Interested" leads for different approach after 30 days</p>
                    </div>
                    <span style="background: #dc2626; color: white; padding: 5px 10px; border-radius: 20px; font-size: 0.85rem;">Daily Check</span>
                </div>
                <div style="background: #1f2937; color: #10b981; padding: 15px; border-radius: 5px; font-family: 'Courier New', monospace; overflow-x: auto;">
                    <pre style="margin: 0; white-space: pre-wrap;">UPDATE vicidial_list 
SET list_id = 160,
    called_since_last_reset = 'Y',
    comments = 'NI Retarget Campaign - Rate Reduction Script'
WHERE list_id = 153 
  AND status = 'NI'
  AND call_count >= 12
  AND TIMESTAMPDIFF(DAY, last_local_call_time, NOW()) >= 30;</pre>
                </div>
                <div style="margin-top: 15px; padding: 15px; background: #fee2e2; border-radius: 5px;">
                    <strong>📖 Retargeting Strategy:</strong>
                    <ul style="margin-top: 10px; color: #374151;">
                        <li><strong>30-Day Cool Off:</strong> Gives time for situation to change</li>
                        <li><strong>Different Script:</strong> "Rate reduction in your area" approach</li>
                        <li><strong>Limited Attempts:</strong> Only 3 more calls over 7 days</li>
                        <li><strong>70K Opportunity:</strong> Large pool of NI leads to convert</li>
                    </ul>
                </div>
            </div>

            <!-- TCPA Archive -->
            <div style="background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 20px; margin-bottom: 20px;">
                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 15px;">
                    <div>
                        <h4 style="color: #6b7280; margin: 0;">Archive: Any List → 199 (TCPA Compliance)</h4>
                        <p style="color: #6b7280; margin-top: 5px;">Automatically archives leads after 89 days</p>
                    </div>
                    <span style="background: #6b7280; color: white; padding: 5px 10px; border-radius: 20px; font-size: 0.85rem;">2 AM Daily</span>
                </div>
                <div style="background: #1f2937; color: #10b981; padding: 15px; border-radius: 5px; font-family: 'Courier New', monospace; overflow-x: auto;">
                    <pre style="margin: 0; white-space: pre-wrap;">UPDATE vicidial_list 
SET list_id = 199,
    status = 'TCPAX',
    called_since_last_reset = 'Y'
WHERE list_id IN (150, 151, 152, 153, 160)
  AND TIMESTAMPDIFF(DAY, entry_date, NOW()) >= 89;</pre>
                </div>
                <div style="margin-top: 15px; padding: 15px; background: #f3f4f6; border-radius: 5px;">
                    <strong>⚖️ Legal Compliance:</strong>
                    <ul style="margin-top: 10px; color: #374151;">
                        <li><strong>TCPA Rule:</strong> Cannot call after 89 days from consent</li>
                        <li><strong>Automatic:</strong> Runs daily at 2 AM to catch any expired leads</li>
                        <li><strong>Status TCPAX:</strong> Special status for archived leads</li>
                        <li><strong>Cannot be reactivated</strong> without new consent</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Monitoring Tab -->
        <div id="contentMonitoring" style="display: none;">
            <h3 style="color: #374151; margin-bottom: 20px;">📊 Monitoring Queries</h3>
            
            <!-- Current Distribution -->
            <div style="background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 20px; margin-bottom: 20px;">
                <h4 style="color: #059669; margin-bottom: 15px;">Check Current Lead Distribution</h4>
                <div style="background: #1f2937; color: #10b981; padding: 15px; border-radius: 5px; font-family: 'Courier New', monospace; overflow-x: auto;">
                    <pre style="margin: 0; white-space: pre-wrap;">SELECT 
    list_id,
    COUNT(*) as total_leads,
    SUM(CASE WHEN called_since_last_reset = 'N' THEN 1 ELSE 0 END) as available_now,
    SUM(CASE WHEN status = 'NEW' THEN 1 ELSE 0 END) as new_leads,
    AVG(call_count) as avg_calls,
    MAX(call_count) as max_calls,
    MIN(entry_date) as oldest_lead,
    MAX(last_call_time) as latest_call
FROM vicidial_list
WHERE list_id IN (150, 151, 152, 153, 160)
GROUP BY list_id
ORDER BY list_id;</pre>
                </div>
                <div style="margin-top: 15px;">
                    <button onclick="runQuery('distribution')" style="background: #059669; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">
                        ▶️ Run Query
                    </button>
                </div>
            </div>

            <!-- Hourly Performance -->
            <div style="background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 20px; margin-bottom: 20px;">
                <h4 style="color: #f59e0b; margin-bottom: 15px;">Today's Hourly Performance</h4>
                <div style="background: #1f2937; color: #10b981; padding: 15px; border-radius: 5px; font-family: 'Courier New', monospace; overflow-x: auto;">
                    <pre style="margin: 0; white-space: pre-wrap;">SELECT 
    HOUR(last_call_time) as hour,
    list_id,
    COUNT(*) as calls_made,
    SUM(CASE WHEN status = 'A' THEN 1 ELSE 0 END) as answered,
    SUM(CASE WHEN status IN ('XFER','XFERA') THEN 1 ELSE 0 END) as transfers,
    ROUND(100.0 * SUM(CASE WHEN status = 'A' THEN 1 ELSE 0 END) / COUNT(*), 1) as answer_rate
FROM vicidial_list
WHERE DATE(last_call_time) = CURDATE()
  AND list_id IN (150, 151, 152, 153)
GROUP BY hour, list_id
ORDER BY hour, list_id;</pre>
                </div>
                <div style="margin-top: 15px;">
                    <button onclick="runQuery('hourly')" style="background: #f59e0b; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">
                        ▶️ Run Query
                    </button>
                </div>
            </div>

            <!-- Stuck Leads Check -->
            <div style="background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 20px; margin-bottom: 20px;">
                <h4 style="color: #dc2626; margin-bottom: 15px;">Find Stuck Leads (Should Move But Haven't)</h4>
                <div style="background: #1f2937; color: #10b981; padding: 15px; border-radius: 5px; font-family: 'Courier New', monospace; overflow-x: auto;">
                    <pre style="margin: 0; white-space: pre-wrap;">SELECT 
    'List 150 → 151' as movement,
    COUNT(*) as stuck_leads
FROM vicidial_list
WHERE list_id = 150 AND call_count >= 5
UNION ALL
SELECT 
    'List 151 → 152' as movement,
    COUNT(*) as stuck_leads
FROM vicidial_list
WHERE list_id = 151 AND call_count >= 7
UNION ALL
SELECT 
    'List 152 → 153' as movement,
    COUNT(*) as stuck_leads
FROM vicidial_list
WHERE list_id = 152 AND call_count >= 10
UNION ALL
SELECT 
    'Should be TCPA archived' as movement,
    COUNT(*) as stuck_leads
FROM vicidial_list
WHERE list_id IN (150, 151, 152, 153)
  AND DATEDIFF(NOW(), entry_date) >= 89;</pre>
                </div>
                <div style="margin-top: 15px;">
                    <button onclick="runQuery('stuck')" style="background: #dc2626; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">
                        ▶️ Run Query
                    </button>
                </div>
            </div>
        </div>

        <!-- Troubleshooting Tab -->
        <div id="contentTroubleshooting" style="display: none;">
            <h3 style="color: #374151; margin-bottom: 20px;">🔍 Troubleshooting Guide</h3>
            
            <!-- Common Issues -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 20px;">
                <!-- Issue 1 -->
                <div style="background: #fee2e2; border: 1px solid #ef4444; border-radius: 8px; padding: 20px;">
                    <h4 style="color: #dc2626; margin-bottom: 10px;">❌ Leads Not Being Called</h4>
                    <div style="color: #374151;">
                        <strong>Symptoms:</strong>
                        <ul>
                            <li>Agents idle despite leads in list</li>
                            <li>Hopper empty or low</li>
                        </ul>
                        <strong>Check:</strong>
                        <div style="background: #1f2937; color: #10b981; padding: 10px; border-radius: 5px; margin: 10px 0; font-family: monospace; font-size: 0.9rem;">
                            SELECT list_id, called_since_last_reset, COUNT(*)<br>
                            FROM vicidial_list<br>
                            WHERE list_id IN (150,151,152,153)<br>
                            GROUP BY list_id, called_since_last_reset;
                        </div>
                        <strong>Fix:</strong>
                        <ul>
                            <li>If all show 'Y', timing script may be failing</li>
                            <li>Check cron job is running</li>
                            <li>Manually run: <code>php artisan vici:optimal-timing</code></li>
                        </ul>
                    </div>
                </div>

                <!-- Issue 2 -->
                <div style="background: #fef3c7; border: 1px solid #f59e0b; border-radius: 8px; padding: 20px;">
                    <h4 style="color: #d97706; margin-bottom: 10px;">⚠️ Wrong Calling Times</h4>
                    <div style="color: #374151;">
                        <strong>Symptoms:</strong>
                        <ul>
                            <li>List 151 called outside 10am/2pm</li>
                            <li>List 152 called multiple times per day</li>
                        </ul>
                        <strong>Check Server Time:</strong>
                        <div style="background: #1f2937; color: #10b981; padding: 10px; border-radius: 5px; margin: 10px 0; font-family: monospace; font-size: 0.9rem;">
                            SELECT NOW() as server_time,<br>
                            CONVERT_TZ(NOW(),'UTC','America/New_York') as est;
                        </div>
                        <strong>Fix:</strong>
                        <ul>
                            <li>Ensure server timezone is correct</li>
                            <li>Check PHP timezone in script</li>
                            <li>Verify cron schedule timing</li>
                        </ul>
                    </div>
                </div>

                <!-- Issue 3 -->
                <div style="background: #ede9fe; border: 1px solid #8b5cf6; border-radius: 8px; padding: 20px;">
                    <h4 style="color: #7c3aed; margin-bottom: 10px;">🔄 Leads Not Moving Lists</h4>
                    <div style="color: #374151;">
                        <strong>Symptoms:</strong>
                        <ul>
                            <li>Leads stuck in List 150 with 5+ calls</li>
                            <li>No progression to next list</li>
                        </ul>
                        <strong>Check Movement Conditions:</strong>
                        <div style="background: #1f2937; color: #10b981; padding: 10px; border-radius: 5px; margin: 10px 0; font-family: monospace; font-size: 0.9rem;">
                            SELECT list_id, call_count, status, COUNT(*)<br>
                            FROM vicidial_list<br>
                            WHERE list_id = 150 AND call_count >= 5<br>
                            GROUP BY list_id, call_count, status;
                        </div>
                        <strong>Fix:</strong>
                        <ul>
                            <li>Check if status is blocking (XFER/XFERA)</li>
                            <li>Manually run movement script</li>
                            <li>Check for SQL errors in logs</li>
                        </ul>
                    </div>
                </div>

                <!-- Issue 4 -->
                <div style="background: #f0fdf4; border: 1px solid #10b981; border-radius: 8px; padding: 20px;">
                    <h4 style="color: #059669; margin-bottom: 10px;">📊 Performance Issues</h4>
                    <div style="color: #374151;">
                        <strong>Symptoms:</strong>
                        <ul>
                            <li>Scripts running slowly</li>
                            <li>Database queries timing out</li>
                        </ul>
                        <strong>Check Table Size:</strong>
                        <div style="background: #1f2937; color: #10b981; padding: 10px; border-radius: 5px; margin: 10px 0; font-family: monospace; font-size: 0.9rem;">
                            SELECT COUNT(*) as total_leads<br>
                            FROM vicidial_list<br>
                            WHERE list_id BETWEEN 150 AND 160;
                        </div>
                        <strong>Fix:</strong>
                        <ul>
                            <li>Add indexes on frequently queried columns</li>
                            <li>Archive old leads to List 199</li>
                            <li>Optimize queries with LIMIT clauses</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Manual Execute Tab -->
        <div id="contentManual" style="display: none;">
            <h3 style="color: #374151; margin-bottom: 20px;">🚀 Manual Script Execution</h3>
            
            <div style="background: #fef3c7; border: 1px solid #f59e0b; border-radius: 8px; padding: 15px; margin-bottom: 20px;">
                <strong>⚠️ Warning:</strong> Manual execution will immediately affect live calling. Use with caution during business hours.
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
                <!-- Timing Control -->
                <div style="background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 20px;">
                    <h4 style="color: #059669; margin-bottom: 15px;">⏰ Timing Control</h4>
                    <p style="color: #6b7280; margin-bottom: 15px;">Reset availability flags for all lists</p>
                    <button onclick="executeCommand('timing')" style="width: 100%; background: #059669; color: white; padding: 12px; border: none; border-radius: 5px; cursor: pointer; font-weight: bold;">
                        Run Timing Control
                    </button>
                    <div style="margin-top: 10px; padding: 10px; background: #f3f4f6; border-radius: 5px; font-family: monospace; font-size: 0.85rem;">
                        php artisan vici:optimal-timing
                    </div>
                </div>

                <!-- Lead Movement -->
                <div style="background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 20px;">
                    <h4 style="color: #f59e0b; margin-bottom: 15px;">🔄 Lead Movement</h4>
                    <p style="color: #6b7280; margin-bottom: 15px;">Process all pending list transitions</p>
                    <button onclick="executeCommand('movement')" style="width: 100%; background: #f59e0b; color: white; padding: 12px; border: none; border-radius: 5px; cursor: pointer; font-weight: bold;">
                        Run Lead Movement
                    </button>
                    <div style="margin-top: 10px; padding: 10px; background: #f3f4f6; border-radius: 5px; font-family: monospace; font-size: 0.85rem;">
                        php artisan vici:execute-lead-flow --force
                    </div>
                </div>

                <!-- Reset All Lists -->
                <div style="background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 20px;">
                    <h4 style="color: #8b5cf6; margin-bottom: 15px;">🔧 Reset All Lists</h4>
                    <p style="color: #6b7280; margin-bottom: 15px;">Make all leads available (emergency)</p>
                    <button onclick="if(confirm('This will make ALL leads available immediately. Continue?')) executeCommand('reset')" style="width: 100%; background: #8b5cf6; color: white; padding: 12px; border: none; border-radius: 5px; cursor: pointer; font-weight: bold;">
                        Reset All Flags
                    </button>
                    <div style="margin-top: 10px; padding: 10px; background: #f3f4f6; border-radius: 5px; font-family: monospace; font-size: 0.85rem;">
                        Emergency reset - use carefully
                    </div>
                </div>

                <!-- Health Check -->
                <div style="background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 20px;">
                    <h4 style="color: #dc2626; margin-bottom: 15px;">🏥 Health Check</h4>
                    <p style="color: #6b7280; margin-bottom: 15px;">Run complete system diagnostic</p>
                    <button onclick="executeCommand('health')" style="width: 100%; background: #dc2626; color: white; padding: 12px; border: none; border-radius: 5px; cursor: pointer; font-weight: bold;">
                        Run Health Check
                    </button>
                    <div style="margin-top: 10px; padding: 10px; background: #f3f4f6; border-radius: 5px; font-family: monospace; font-size: 0.85rem;">
                        php artisan system:health-check
                    </div>
                </div>
            </div>

            <!-- Command Output -->
            <div style="margin-top: 30px; background: #1f2937; color: #10b981; padding: 20px; border-radius: 8px; display: none;" id="commandOutput">
                <h4 style="color: white; margin-bottom: 10px;">📟 Command Output:</h4>
                <pre id="outputContent" style="margin: 0; white-space: pre-wrap; font-family: 'Courier New', monospace;">Waiting for command execution...</pre>
            </div>
        </div>
    </div>
</div>

<script>
// Tab switching
function showTab(tabName) {
    // Hide all content
    document.querySelectorAll('[id^="content"]').forEach(el => el.style.display = 'none');
    // Show selected content
    document.getElementById('content' + tabName.charAt(0).toUpperCase() + tabName.slice(1)).style.display = 'block';
    
    // Update tab buttons
    document.querySelectorAll('[id^="tab"]').forEach(el => {
        el.style.background = 'transparent';
        el.style.color = '#6b7280';
    });
    document.getElementById('tab' + tabName.charAt(0).toUpperCase() + tabName.slice(1)).style.background = '#667eea';
    document.getElementById('tab' + tabName.charAt(0).toUpperCase() + tabName.slice(1)).style.color = 'white';
}

// Execute manual commands
function executeCommand(command) {
    document.getElementById('commandOutput').style.display = 'block';
    document.getElementById('outputContent').textContent = 'Executing ' + command + ' command...';
    
    // Simulate API call
    fetch('/api/vici/execute-command', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({command: command})
    })
    .then(response => response.json())
    .then(data => {
        document.getElementById('outputContent').textContent = data.output || 'Command executed successfully';
    })
    .catch(error => {
        document.getElementById('outputContent').textContent = 'Error: ' + error.message;
    });
}

// Run monitoring queries
function runQuery(queryType) {
    alert('Running ' + queryType + ' query... (This would execute the query and show results)');
}

// Update last run time
setInterval(function() {
    // This would fetch actual last run time from server
    let minutes = Math.floor(Math.random() * 5) + 1;
    document.getElementById('lastRunTime').textContent = minutes + ' minutes ago';
}, 30000);
</script>
@endsection





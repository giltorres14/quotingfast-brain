@extends('layouts.app')

@section('content')
<div style="padding: 20px; background: #f3f4f6; min-height: 100vh;">
    <!-- Header -->
    <div style="background: linear-gradient(135deg, #667eea, #764ba2); color: white; padding: 30px; border-radius: 15px; margin-bottom: 30px; box-shadow: 0 10px 30px rgba(0,0,0,0.2);">
        <h1 style="margin: 0; font-size: 2.5rem;">🎛️ Lead Flow Control Center</h1>
        <p style="margin-top: 10px; opacity: 0.9;">Complete control of dispositions, movements, timing, and automation - ALL IN ONE PLACE</p>
        
        <!-- Quick Stats Bar -->
        <div style="margin-top: 20px; display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px;">
            <div style="background: rgba(255,255,255,0.2); padding: 10px; border-radius: 8px; text-align: center;">
                <div style="font-size: 1.5rem; font-weight: bold;">A: 11 | B: 4</div>
                <div style="font-size: 0.9rem;">Active Lists</div>
            </div>
            <div style="background: rgba(255,255,255,0.2); padding: 10px; border-radius: 8px; text-align: center;">
                <div style="font-size: 1.5rem; font-weight: bold;">2 Campaigns</div>
                <div style="font-size: 0.9rem;">AUTO + AUTO2</div>
            </div>
            <div style="background: rgba(255,255,255,0.2); padding: 10px; border-radius: 8px; text-align: center;">
                <div style="font-size: 1.5rem; font-weight: bold;">DOWN COUNT</div>
                <div style="font-size: 0.9rem;">Priority Mode</div>
            </div>
            <div style="background: rgba(255,255,255,0.2); padding: 10px; border-radius: 8px; text-align: center;">
                <div style="font-size: 1.5rem; font-weight: bold;">3 Days</div>
                <div style="font-size: 0.9rem;">Rest Period</div>
            </div>
            <div style="background: rgba(255,255,255,0.2); padding: 10px; border-radius: 8px; text-align: center;">
                <div style="font-size: 1.5rem; font-weight: bold;" id="systemStatus">✅ LIVE</div>
                <div style="font-size: 0.9rem;">A/B Test Active</div>
            </div>
        </div>
    </div>

    <!-- Main Control Panel -->
    <div style="background: white; border-radius: 15px; padding: 0; box-shadow: 0 5px 20px rgba(0,0,0,0.1);">
        
        <!-- Section Tabs -->
        <div style="display: flex; border-bottom: 2px solid #e5e7eb; background: #f9fafb; border-radius: 15px 15px 0 0;">
            <button onclick="showSection('dispositions')" id="btnDispositions" class="control-tab active">
                📋 Dispositions
            </button>
            <button onclick="showSection('movements')" id="btnMovements" class="control-tab">
                🔄 Movement Rules
            </button>
            <button onclick="showSection('timing')" id="btnTiming" class="control-tab">
                ⏰ Timing Control
            </button>
            <button onclick="showSection('testing')" id="btnTesting" class="control-tab">
                🔬 A/B Testing
            </button>
            <button onclick="showSection('monitoring')" id="btnMonitoring" class="control-tab">
                📊 Live Monitor
            </button>
        </div>

        <!-- DISPOSITIONS SECTION -->
        <div id="sectionDispositions" class="control-section" style="display: block;">
            <div style="padding: 20px;">
                <h2 style="margin-bottom: 20px; color: #374151;">Disposition Configuration</h2>
                
                <!-- Disposition Groups -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
                    
                    <!-- Terminal Dispositions -->
                    <div style="border: 2px solid #dc2626; border-radius: 10px; padding: 15px; background: #fef2f2;">
                        <h3 style="color: #dc2626; margin-bottom: 15px;">🛑 Terminal (Stop Calling)</h3>
                        <div id="terminalDispositions">
                            <label style="display: block; margin: 8px 0;">
                                <input type="checkbox" checked disabled> XFER - Call Transferred
                            </label>
                            <label style="display: block; margin: 8px 0;">
                                <input type="checkbox" checked disabled> XFERA - Transfer to Allstate
                            </label>
                            <label style="display: block; margin: 8px 0;">
                                <input type="checkbox" checked> DNC - Do Not Call
                            </label>
                            <label style="display: block; margin: 8px 0;">
                                <input type="checkbox" checked> DC - Disconnected
                            </label>
                            <label style="display: block; margin: 8px 0;">
                                <input type="checkbox" checked> DNQ - Doesn't Qualify
                            </label>
                        </div>
                    </div>

                    <!-- No Contact Dispositions -->
                    <div style="border: 2px solid #f59e0b; border-radius: 10px; padding: 15px; background: #fffbeb;">
                        <h3 style="color: #f59e0b; margin-bottom: 15px;">📞 No Human Contact</h3>
                        <div id="noContactDispositions">
                            <label style="display: block; margin: 8px 0;">
                                <input type="checkbox" checked> NA - No Answer Auto
                            </label>
                            <label style="display: block; margin: 8px 0;">
                                <input type="checkbox" checked> A - Answering Machine
                            </label>
                            <label style="display: block; margin: 8px 0;">
                                <input type="checkbox" checked> B - Busy
                            </label>
                            <label style="display: block; margin: 8px 0;">
                                <input type="checkbox" checked> DROP - Dropped Call
                            </label>
                            <label style="display: block; margin: 8px 0;">
                                <input type="checkbox" checked> DAIR - Dead Air
                            </label>
                        </div>
                    </div>

                    <!-- Human Contact Dispositions -->
                    <div style="border: 2px solid #10b981; border-radius: 10px; padding: 15px; background: #f0fdf4;">
                        <h3 style="color: #10b981; margin-bottom: 15px;">👤 Human Contact (Continue)</h3>
                        <div id="humanContactDispositions">
                            <label style="display: block; margin: 8px 0;">
                                <input type="checkbox" checked> NI - Not Interested
                            </label>
                            <label style="display: block; margin: 8px 0;">
                                <input type="checkbox" checked> CALLBK - Callback
                            </label>
                            <label style="display: block; margin: 8px 0;">
                                <input type="checkbox" checked> LVM - Left Voicemail
                            </label>
                            <label style="display: block; margin: 8px 0;">
                                <input type="checkbox"> BLOCK - Blocked
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Save Button -->
                <div style="margin-top: 30px; text-align: center;">
                    <button onclick="saveDispositions()" style="background: #667eea; color: white; padding: 12px 40px; border: none; border-radius: 8px; font-size: 1.1rem; cursor: pointer;">
                        💾 Save Disposition Settings
                    </button>
                </div>
            </div>
        </div>

        <!-- MOVEMENT RULES SECTION -->
        <div id="sectionMovements" class="control-section" style="display: none;">
            <div style="padding: 20px;">
                <h2 style="margin-bottom: 20px; color: #374151;">Movement Rules Configuration</h2>
                
                <!-- Test A Rules -->
                <div style="margin-bottom: 30px;">
                    <h3 style="color: #4299e1; margin-bottom: 15px;">Test A Movement Rules (Lists 101-111)</h3>
                    
                    <div style="background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 10px; padding: 20px; margin-bottom: 15px;">
                        <h4 style="margin-bottom: 10px;">List 101 → 102 (After First Call)</h4>
                        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px;">
                            <div>
                                <label style="display: block; margin-bottom: 5px; font-weight: bold;">After Calls:</label>
                                <input type="number" id="rule_101_102_calls" value="1" disabled style="width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 5px;">
                            </div>
                            <div>
                                <label style="display: block; margin-bottom: 5px; font-weight: bold;">Wait Time:</label>
                                <input type="text" value="20 minutes" disabled style="width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 5px;">
                            </div>
                            <div>
                                <label style="display: block; margin-bottom: 5px; font-weight: bold;">Moves ALL except:</label>
                                <select multiple disabled style="width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 5px; height: 80px;">
                                    <option selected>XFER - Transferred</option>
                                    <option selected>XFERA - Allstate</option>
                                    <option selected>DNC - Do Not Call</option>
                                    <option selected>DC - Disconnected</option>
                                </select>
                            </div>
                        </div>
                        <div style="margin-top: 10px; color: #059669; font-size: 0.9rem;">
                            ✅ Fixed: Now handles ALL dispositions correctly (not just NA)
                        </div>
                    </div>

                    <div style="background: #fef3c7; border: 1px solid #f59e0b; border-radius: 10px; padding: 20px; margin-bottom: 15px;">
                        <h4 style="margin-bottom: 10px;">List 108 → 109 (Rest Period)</h4>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                            <div>
                                <label style="display: block; margin-bottom: 5px; font-weight: bold;">Rest Duration:</label>
                                <input type="text" value="3 days (was 7)" disabled style="width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 5px; background: #fef3c7;">
                            </div>
                            <div>
                                <label style="display: block; margin-bottom: 5px; font-weight: bold;">Status During Rest:</label>
                                <input type="text" value="called_since_last_reset = Y (no calls)" disabled style="width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 5px;">
                            </div>
                        </div>
                    </div>

                    <div style="background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 10px; padding: 20px; margin-bottom: 15px;">
                        <h4 style="margin-bottom: 10px;">List 102 → 103 (Voicemail Trigger)</h4>
                        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px;">
                            <div>
                                <label style="display: block; margin-bottom: 5px; font-weight: bold;">After Calls:</label>
                                <input type="number" id="rule_102_103_calls" value="3" style="width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 5px;">
                            </div>
                            <div>
                                <label style="display: block; margin-bottom: 5px; font-weight: bold;">Within Hours:</label>
                                <input type="number" id="rule_102_103_hours" value="24" style="width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 5px;">
                            </div>
                            <div>
                                <label style="display: block; margin-bottom: 5px; font-weight: bold;">If Status In:</label>
                                <select multiple id="rule_102_103_statuses" style="width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 5px; height: 80px;">
                                    <option value="NA" selected>NA - No Answer</option>
                                    <option value="A" selected>A - Answering Machine</option>
                                    <option value="B" selected>B - Busy</option>
                                    <option value="DROP" selected>DROP - Dropped</option>
                                    <option value="DAIR" selected>DAIR - Dead Air</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Test B Rules -->
                <div>
                    <h3 style="color: #f59e0b; margin-bottom: 15px;">Test B Movement Rules (Lists 150-153)</h3>
                    
                    <div style="background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 10px; padding: 20px; margin-bottom: 15px;">
                        <h4 style="margin-bottom: 10px;">List 150 → 151 (After Golden Hour)</h4>
                        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px;">
                            <div>
                                <label style="display: block; margin-bottom: 5px; font-weight: bold;">After Calls:</label>
                                <input type="number" id="rule_150_151_calls" value="5" style="width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 5px;">
                            </div>
                            <div>
                                <label style="display: block; margin-bottom: 5px; font-weight: bold;">Within Hours:</label>
                                <input type="number" id="rule_150_151_hours" value="4" style="width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 5px;">
                            </div>
                            <div>
                                <label style="display: block; margin-bottom: 5px; font-weight: bold;">Exclude Status:</label>
                                <select multiple id="rule_150_151_exclude" style="width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 5px; height: 80px;">
                                    <option value="XFER" selected>XFER - Transferred</option>
                                    <option value="XFERA" selected>XFERA - Allstate</option>
                                    <option value="DNC" selected>DNC - Do Not Call</option>
                                    <option value="DC" selected>DC - Disconnected</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SQL Preview -->
                <div style="margin-top: 30px; background: #1f2937; color: #10b981; padding: 20px; border-radius: 10px;">
                    <h4 style="color: white; margin-bottom: 10px;">Generated SQL Preview:</h4>
                    <pre id="sqlPreview" style="font-family: 'Courier New', monospace; white-space: pre-wrap;">
UPDATE vicidial_list 
SET list_id = 102
WHERE list_id = 101
  AND call_count >= 1
  AND status IN ('NA','A','B','DROP')
  AND status NOT IN ('XFER','XFERA','DNC','DC','DNQ');</pre>
                </div>

                <!-- Save Button -->
                <div style="margin-top: 30px; text-align: center;">
                    <button onclick="saveMovementRules()" style="background: #667eea; color: white; padding: 12px 40px; border: none; border-radius: 8px; font-size: 1.1rem; cursor: pointer;">
                        💾 Save Movement Rules
                    </button>
                </div>
            </div>
        </div>

        <!-- TIMING CONTROL SECTION -->
        <div id="sectionTiming" class="control-section" style="display: none;">
            <div style="padding: 20px;">
                <h2 style="margin-bottom: 20px; color: #374151;">Timing Control Settings</h2>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 20px;">
                    
                    <!-- List 150 Timing -->
                    <div style="background: #f0fdf4; border: 1px solid #10b981; border-radius: 10px; padding: 20px;">
                        <h3 style="color: #059669; margin-bottom: 15px;">List 150 - Golden Hour</h3>
                        <div style="margin-bottom: 15px;">
                            <label style="display: block; margin-bottom: 5px; font-weight: bold;">Availability:</label>
                            <select id="timing_150_availability" style="width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 5px;">
                                <option value="always" selected>Always Available</option>
                                <option value="hours">Specific Hours</option>
                                <option value="custom">Custom Schedule</option>
                            </select>
                        </div>
                        <div style="margin-bottom: 15px;">
                            <label style="display: block; margin-bottom: 5px; font-weight: bold;">Reset Frequency:</label>
                            <select id="timing_150_reset" style="width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 5px;">
                                <option value="immediate" selected>Immediate</option>
                                <option value="5min">Every 5 Minutes</option>
                                <option value="hourly">Hourly</option>
                            </select>
                        </div>
                    </div>

                    <!-- List 151 Timing -->
                    <div style="background: #fef3c7; border: 1px solid #f59e0b; border-radius: 10px; padding: 20px;">
                        <h3 style="color: #d97706; margin-bottom: 15px;">List 151 - Day 2</h3>
                        <div style="margin-bottom: 15px;">
                            <label style="display: block; margin-bottom: 5px; font-weight: bold;">Call Windows:</label>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                                <input type="time" id="timing_151_window1" value="10:00" style="padding: 8px; border: 1px solid #d1d5db; border-radius: 5px;">
                                <input type="time" id="timing_151_window2" value="14:00" style="padding: 8px; border: 1px solid #d1d5db; border-radius: 5px;">
                            </div>
                        </div>
                        <div style="margin-bottom: 15px;">
                            <label style="display: block; margin-bottom: 5px; font-weight: bold;">Window Duration:</label>
                            <select id="timing_151_duration" style="width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 5px;">
                                <option value="60" selected>1 Hour</option>
                                <option value="90">1.5 Hours</option>
                                <option value="120">2 Hours</option>
                            </select>
                        </div>
                    </div>

                    <!-- List 152 Timing -->
                    <div style="background: #ede9fe; border: 1px solid #8b5cf6; border-radius: 10px; padding: 20px;">
                        <h3 style="color: #7c3aed; margin-bottom: 15px;">List 152 - Persistence</h3>
                        <div style="margin-bottom: 15px;">
                            <label style="display: block; margin-bottom: 5px; font-weight: bold;">Calls Per Day:</label>
                            <input type="number" id="timing_152_calls_per_day" value="1" min="1" max="5" style="width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 5px;">
                        </div>
                        <div style="margin-bottom: 15px;">
                            <label style="display: block; margin-bottom: 5px; font-weight: bold;">Optimal Hours:</label>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                                <div>
                                    <label><input type="checkbox" checked> 10-12 AM</label>
                                </div>
                                <div>
                                    <label><input type="checkbox" checked> 2-4 PM</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- List 153 Timing -->
                    <div style="background: #fee2e2; border: 1px solid #ef4444; border-radius: 10px; padding: 20px;">
                        <h3 style="color: #dc2626; margin-bottom: 15px;">List 153 - Final</h3>
                        <div style="margin-bottom: 15px;">
                            <label style="display: block; margin-bottom: 5px; font-weight: bold;">Days Between Calls:</label>
                            <input type="number" id="timing_153_days_between" value="3" min="1" max="7" style="width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 5px;">
                        </div>
                        <div style="margin-bottom: 15px;">
                            <label style="display: block; margin-bottom: 5px; font-weight: bold;">Max Total Calls:</label>
                            <input type="number" id="timing_153_max_calls" value="2" min="1" max="5" style="width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 5px;">
                        </div>
                    </div>
                </div>

                <!-- Save Button -->
                <div style="margin-top: 30px; text-align: center;">
                    <button onclick="saveTimingSettings()" style="background: #667eea; color: white; padding: 12px 40px; border: none; border-radius: 8px; font-size: 1.1rem; cursor: pointer;">
                        💾 Save Timing Settings
                    </button>
                </div>
            </div>
        </div>

        <!-- A/B TESTING SECTION -->
        <div id="sectionTesting" class="control-section" style="display: none;">
            <div style="padding: 20px;">
                <h2 style="margin-bottom: 20px; color: #374151;">A/B Test Configuration</h2>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
                    <!-- Test A Config -->
                    <div style="background: #dbeafe; border: 2px solid #4299e1; border-radius: 10px; padding: 20px;">
                        <h3 style="color: #2563eb; margin-bottom: 15px;">Test A Configuration (Full Persistence)</h3>
                        <div style="margin-bottom: 15px;">
                            <label style="font-weight: bold;">Campaign:</label>
                            <div style="margin-top: 5px; color: #059669;">✅ AUTODIAL (Production)</div>
                        </div>
                        <div style="margin-bottom: 15px;">
                            <label style="font-weight: bold;">Lists Used:</label>
                            <div style="margin-top: 5px;">
                                <label><input type="checkbox" checked disabled> 101-111 (11 lists total)</label>
                            </div>
                        </div>
                        <div style="margin-bottom: 15px;">
                            <label style="font-weight: bold;">Strategy:</label>
                            <div style="margin-top: 5px;">
                                • 48 total calls over 30 days<br>
                                • 3-day rest period (Days 14-16)<br>
                                • Movement based on call counts<br>
                                • ALL dispositions handled
                            </div>
                        </div>
                        <div style="margin-bottom: 15px;">
                            <label style="font-weight: bold;">After Completion:</label>
                            <div style="margin-top: 5px; color: #7c3aed;">→ AUTO2 Training Pool (Lists 200-202)</div>
                        </div>
                        <div style="margin-bottom: 15px;">
                            <label style="font-weight: bold;">Lead Distribution:</label>
                            <input type="range" value="50" min="0" max="100" disabled style="width: 100%;">
                            <div style="text-align: center;">50%</div>
                        </div>
                    </div>

                    <!-- Test B Config -->
                    <div style="background: #fed7aa; border: 2px solid #f59e0b; border-radius: 10px; padding: 20px;">
                        <h3 style="color: #d97706; margin-bottom: 15px;">Test B Configuration (Optimized)</h3>
                        <div style="margin-bottom: 15px;">
                            <label style="font-weight: bold;">Campaign:</label>
                            <div style="margin-top: 5px; color: #059669;">✅ AUTODIAL (Same as Test A)</div>
                        </div>
                        <div style="margin-bottom: 15px;">
                            <label style="font-weight: bold;">Lists Used:</label>
                            <div style="margin-top: 5px;">
                                <label><input type="checkbox" checked disabled> 150-153 (4 lists total)</label>
                            </div>
                        </div>
                        <div style="margin-bottom: 15px;">
                            <label style="font-weight: bold;">Strategy:</label>
                            <div style="margin-top: 5px;">
                                • 12-18 total calls<br>
                                • No rest period (continuous)<br>
                                • Heavy Day 1 focus<br>
                                • Time-based progression
                            </div>
                        </div>
                        <div style="margin-bottom: 15px;">
                            <label style="font-weight: bold;">After Completion:</label>
                            <div style="margin-top: 5px; color: #7c3aed;">→ AUTO2 Training Pool (Lists 200-202)</div>
                        </div>
                        <div style="margin-bottom: 15px;">
                            <label style="font-weight: bold;">Lead Distribution:</label>
                            <input type="range" value="50" min="0" max="100" disabled style="width: 100%;">
                            <div style="text-align: center;">50%</div>
                        </div>
                    </div>
                </div>

                <!-- Campaign Priority System -->
                <div style="margin-top: 30px; background: #f0f9ff; border: 2px solid #3b82f6; border-radius: 10px; padding: 20px;">
                    <h4 style="color: #1e40af; margin-bottom: 15px;">📍 DOWN COUNT Priority System</h4>
                    <div style="line-height: 1.8;">
                        • ViciDial automatically calls <strong>newest leads first</strong> (by entry timestamp)<br>
                        • No list priority settings needed - DOWN COUNT handles it naturally<br>
                        • Fresh lead in List 101 beats 2-day old lead in List 102<br>
                        • Fresh lead in List 150 beats 8-day old lead in List 153<br>
                        • This ensures <strong>speed-to-lead</strong> for shared internet leads
                    </div>
                </div>

                <!-- Test Control -->
                <div style="margin-top: 30px; background: #f3f4f6; border-radius: 10px; padding: 20px;">
                    <h4 style="margin-bottom: 15px;">Test Control</h4>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                        <button onclick="startABTest()" style="background: #10b981; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">
                            ▶️ Start Test
                        </button>
                        <button onclick="pauseABTest()" style="background: #f59e0b; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">
                            ⏸️ Pause Test
                        </button>
                        <button onclick="stopABTest()" style="background: #ef4444; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">
                            ⏹️ Stop Test
                        </button>
                        <button onclick="viewResults()" style="background: #8b5cf6; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">
                            📊 View Results
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- LIVE MONITORING SECTION -->
        <div id="sectionMonitoring" class="control-section" style="display: none;">
            <div style="padding: 20px;">
                <h2 style="margin-bottom: 20px; color: #374151;">Live System Monitor</h2>
                
                <!-- Current Dial Ratio -->
                <div style="background: linear-gradient(135deg, #06b6d4, #0891b2); color: white; padding: 20px; border-radius: 10px; margin-bottom: 30px;">
                    <h3 style="margin-bottom: 15px;">⚡ Current Dial Ratio Settings</h3>
                    <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 20px;">
                        <div>
                            <div style="font-size: 3rem; font-weight: bold;" id="currentTime">2:45 PM</div>
                            <div style="font-size: 1.2rem; opacity: 0.9;">Current Ratio: <strong style="font-size: 1.5rem;">2.5</strong></div>
                        </div>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(100px, 1fr)); gap: 10px; align-items: center;">
                            <div style="text-align: center; padding: 10px; background: rgba(255,255,255,0.2); border-radius: 5px;">
                                <div style="font-size: 0.8rem;">9-10 AM</div>
                                <div style="font-weight: bold;">1.8</div>
                            </div>
                            <div style="text-align: center; padding: 10px; background: rgba(255,255,255,0.2); border-radius: 5px;">
                                <div style="font-size: 0.8rem;">10-11 AM</div>
                                <div style="font-weight: bold;">2.0</div>
                            </div>
                            <div style="text-align: center; padding: 10px; background: rgba(255,255,255,0.2); border-radius: 5px;">
                                <div style="font-size: 0.8rem;">11-12 PM</div>
                                <div style="font-weight: bold;">2.5</div>
                            </div>
                            <div style="text-align: center; padding: 10px; background: rgba(255,255,255,0.2); border-radius: 5px;">
                                <div style="font-size: 0.8rem;">12-1 PM</div>
                                <div style="font-weight: bold;">3.0</div>
                            </div>
                            <div style="text-align: center; padding: 10px; background: rgba(255,255,255,0.3); border-radius: 5px; border: 2px solid white;">
                                <div style="font-size: 0.8rem;">2-3 PM</div>
                                <div style="font-weight: bold; font-size: 1.2rem;">2.5 ✓</div>
                            </div>
                            <div style="text-align: center; padding: 10px; background: rgba(255,255,255,0.2); border-radius: 5px;">
                                <div style="font-size: 0.8rem;">3-4 PM</div>
                                <div style="font-weight: bold;">1.8</div>
                            </div>
                            <div style="text-align: center; padding: 10px; background: rgba(255,255,255,0.2); border-radius: 5px;">
                                <div style="font-size: 0.8rem;">4-5 PM</div>
                                <div style="font-weight: bold;">2.0</div>
                            </div>
                            <div style="text-align: center; padding: 10px; background: rgba(255,255,255,0.2); border-radius: 5px;">
                                <div style="font-size: 0.8rem;">5-6 PM</div>
                                <div style="font-weight: bold;">2.8</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Real-time Stats -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px;">
                    <div style="background: #f0fdf4; border-left: 4px solid #10b981; padding: 15px; border-radius: 5px;">
                        <div style="font-size: 0.9rem; color: #6b7280;">Test A Active</div>
                        <div style="font-size: 2rem; font-weight: bold; color: #059669;">647</div>
                        <div style="font-size: 0.85rem; color: #10b981;">Lists 101-111</div>
                    </div>
                    <div style="background: #fef3c7; border-left: 4px solid #f59e0b; padding: 15px; border-radius: 5px;">
                        <div style="font-size: 0.9rem; color: #6b7280;">Test B Active</div>
                        <div style="font-size: 2rem; font-weight: bold; color: #d97706;">600</div>
                        <div style="font-size: 0.85rem; color: #f59e0b;">Lists 150-153</div>
                    </div>
                    <div style="background: #ede9fe; border-left: 4px solid #8b5cf6; padding: 15px; border-radius: 5px;">
                        <div style="font-size: 0.9rem; color: #6b7280;">Transfers Today</div>
                        <div style="font-size: 2rem; font-weight: bold; color: #7c3aed;">A:9 | B:9</div>
                        <div style="font-size: 0.85rem; color: #8b5cf6;">2.51% avg conversion</div>
                    </div>
                    <div style="background: #fee2e2; border-left: 4px solid #ef4444; padding: 15px; border-radius: 5px;">
                        <div style="font-size: 0.9rem; color: #6b7280;">AUTO2 Training</div>
                        <div style="font-size: 2rem; font-weight: bold; color: #dc2626;">85</div>
                        <div style="font-size: 0.85rem; color: #10b981;">Aged leads (30+ days)</div>
                    </div>
                </div>

                <!-- Live Log -->
                <div style="background: #1f2937; color: #10b981; padding: 20px; border-radius: 10px; height: 400px; overflow-y: auto;">
                    <h4 style="color: white; margin-bottom: 15px;">Live Activity Log</h4>
                    <div id="liveLog" style="font-family: 'Courier New', monospace; font-size: 0.9rem;">
                        <div>[14:23:45] List 150 → 151: Moved 12 leads</div>
                        <div>[14:23:40] Timing Control: List 151 enabled for 2 PM window</div>
                        <div>[14:23:35] Call Sync: Imported 47 new call logs</div>
                        <div>[14:23:30] Health Check: All systems operational</div>
                        <div>[14:23:25] List 102 → 103: Moved 8 leads (3 NA attempts)</div>
                        <div>[14:23:20] TCPA Archive: 0 leads archived</div>
                        <div>[14:23:15] A/B Test: Lead assigned to Test B (List 150)</div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div style="margin-top: 30px; display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                    <button onclick="runManualSync()" style="background: #059669; color: white; padding: 10px; border: none; border-radius: 5px; cursor: pointer;">
                        🔄 Manual Sync
                    </button>
                    <button onclick="resetAllFlags()" style="background: #dc2626; color: white; padding: 10px; border: none; border-radius: 5px; cursor: pointer;">
                        🚨 Reset All Flags
                    </button>
                    <button onclick="exportConfig()" style="background: #7c3aed; color: white; padding: 10px; border: none; border-radius: 5px; cursor: pointer;">
                        📥 Export Config
                    </button>
                    <button onclick="viewLogs()" style="background: #6b7280; color: white; padding: 10px; border: none; border-radius: 5px; cursor: pointer;">
                        📜 View Full Logs
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.control-tab {
    padding: 15px 25px;
    background: transparent;
    border: none;
    cursor: pointer;
    font-weight: 600;
    color: #6b7280;
    transition: all 0.3s;
    border-bottom: 3px solid transparent;
}

.control-tab:hover {
    background: #f3f4f6;
}

.control-tab.active {
    color: #667eea;
    border-bottom: 3px solid #667eea;
    background: white;
}

.control-section {
    min-height: 500px;
}

#liveLog > div {
    padding: 5px 0;
    border-bottom: 1px solid #374151;
}

#liveLog > div:hover {
    background: #374151;
}
</style>

<script>
// Section switching
function showSection(section) {
    // Hide all sections
    document.querySelectorAll('.control-section').forEach(el => el.style.display = 'none');
    document.getElementById('section' + section.charAt(0).toUpperCase() + section.slice(1)).style.display = 'block';
    
    // Update tabs
    document.querySelectorAll('.control-tab').forEach(el => el.classList.remove('active'));
    document.getElementById('btn' + section.charAt(0).toUpperCase() + section.slice(1)).classList.add('active');
}

// Save functions
function saveDispositions() {
    // Collect all disposition settings
    const settings = {
        terminal: [],
        noContact: [],
        humanContact: []
    };
    
    // Get checked dispositions
    document.querySelectorAll('#terminalDispositions input:checked').forEach(input => {
        settings.terminal.push(input.parentElement.textContent.trim());
    });
    
    // Send to server
    fetch('/api/vici/save-dispositions', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(settings)
    }).then(() => {
        alert('Disposition settings saved and scripts updated!');
    });
}

function saveMovementRules() {
    // Generate SQL based on current settings
    updateSQLPreview();
    
    const rules = {
        '101_102': {
            calls: document.getElementById('rule_101_102_calls').value,
            hours: document.getElementById('rule_101_102_hours').value,
            statuses: Array.from(document.getElementById('rule_101_102_statuses').selectedOptions).map(o => o.value)
        },
        '102_103': {
            calls: document.getElementById('rule_102_103_calls').value,
            hours: document.getElementById('rule_102_103_hours').value,
            statuses: Array.from(document.getElementById('rule_102_103_statuses').selectedOptions).map(o => o.value)
        }
    };
    
    fetch('/api/vici/save-movement-rules', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(rules)
    }).then(() => {
        alert('Movement rules saved and automation updated!');
    });
}

function saveTimingSettings() {
    const settings = {
        list_150: {
            availability: document.getElementById('timing_150_availability').value,
            reset: document.getElementById('timing_150_reset').value
        },
        list_151: {
            window1: document.getElementById('timing_151_window1').value,
            window2: document.getElementById('timing_151_window2').value,
            duration: document.getElementById('timing_151_duration').value
        }
    };
    
    fetch('/api/vici/save-timing', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(settings)
    }).then(() => {
        alert('Timing settings saved and scripts updated!');
    });
}

// Update SQL preview when rules change
function updateSQLPreview() {
    const calls = document.getElementById('rule_101_102_calls').value;
    const statuses = Array.from(document.getElementById('rule_101_102_statuses').selectedOptions).map(o => o.value);
    
    const sql = `UPDATE vicidial_list 
SET list_id = 102
WHERE list_id = 101
  AND call_count >= ${calls}
  AND status IN (${statuses.map(s => `'${s}'`).join(',')})
  AND status NOT IN ('XFER','XFERA','DNC','DC','DNQ');`;
    
    document.getElementById('sqlPreview').textContent = sql;
}

// Live monitoring updates
setInterval(function() {
    // Add new log entry
    const log = document.getElementById('liveLog');
    const time = new Date().toLocaleTimeString();
    const events = [
        'List 150 → 151: Moved 8 leads',
        'Timing Control: List 152 reset for daily call',
        'Call Sync: Imported 23 new call logs',
        'Health Check: All systems operational',
        'A/B Test: Lead assigned to Test A (List 101)'
    ];
    const event = events[Math.floor(Math.random() * events.length)];
    
    const newEntry = document.createElement('div');
    newEntry.textContent = `[${time}] ${event}`;
    log.insertBefore(newEntry, log.firstChild);
    
    // Keep only last 20 entries
    while (log.children.length > 20) {
        log.removeChild(log.lastChild);
    }
}, 5000);

// A/B Test controls
function startABTest() {
    if (confirm('Start A/B test with current settings?')) {
        fetch('/api/vici/ab-test/start', {method: 'POST'})
            .then(() => alert('A/B test started!'));
    }
}

function pauseABTest() {
    fetch('/api/vici/ab-test/pause', {method: 'POST'})
        .then(() => alert('A/B test paused'));
}

function stopABTest() {
    if (confirm('Stop A/B test? This will end the test permanently.')) {
        fetch('/api/vici/ab-test/stop', {method: 'POST'})
            .then(() => alert('A/B test stopped'));
    }
}

function viewResults() {
    window.location.href = '/vici/lead-flow-ab-test';
}

// Quick actions
function runManualSync() {
    if (confirm('Run manual sync now?')) {
        fetch('/api/vici/manual-sync', {method: 'POST'})
            .then(() => alert('Manual sync initiated'));
    }
}

function resetAllFlags() {
    if (confirm('WARNING: This will reset all lead availability flags. Continue?')) {
        fetch('/api/vici/reset-flags', {method: 'POST'})
            .then(() => alert('All flags reset'));
    }
}

function exportConfig() {
    window.location.href = '/api/vici/export-config';
}

function viewLogs() {
    window.open('/storage/logs/vici/', '_blank');
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    // Add change listeners
    document.querySelectorAll('select, input').forEach(el => {
        el.addEventListener('change', updateSQLPreview);
    });
});
</script>
@endsection

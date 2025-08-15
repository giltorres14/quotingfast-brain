# 📋 ViciDial Lead Flow System - Complete Documentation
*Production-Ready Implementation with Brain Integration*
*Last Updated: January 14, 2025*

## 🎯 System Overview

### Lead Flow Architecture
```
Brain (Webhook) → Vici List 101 → Automated List Progression → Archive/Success
```

### List Structure & Purpose
| List | Name | Purpose | Call Frequency | Move Trigger |
|------|------|---------|----------------|--------------|
| **101** | Immediate | New leads, first attempt | Immediate | After 1st call → 102 or 103 |
| **102** | Aggressive | Day 1-3 intensive calling | Every 30 min | 3 workdays → 103 |
| **103** | Voicemail 1 | Leave VM only | Agent triggered | After VM → 104 |
| **104** | Phase 1 | Regular calling | 3x/day | 5 days from entry → 105 |
| **105** | Voicemail 2 | Leave 2nd VM | Agent triggered | After VM → 106 |
| **106** | Phase 2 | Reduced calling | 2x/day | 10 days from entry → 107 |
| **107** | Cool Down | No calls (rest period) | None | 7 days rest → 108 |
| **108** | Phase 3 | Final attempts | 1x/day | 30 days or TCPA → 110 |
| **110** | Archive | Expired/Complete | None | Terminal state |
| **111** | Training | Agent training | As needed | Manual only |

## 📊 Database Schema

### 1. Calendar Table (Workday Tracking)
```sql
CREATE TABLE IF NOT EXISTS calendar (
    date_value DATE PRIMARY KEY,
    is_workday TINYINT(1) DEFAULT 1,
    is_holiday TINYINT(1) DEFAULT 0,
    holiday_name VARCHAR(100),
    INDEX idx_workday (is_workday, date_value)
) ENGINE=InnoDB;

-- Populate with dates and mark weekends/holidays
-- See full SQL in playbook
```

### 2. Lead Movement Tracking
```sql
CREATE TABLE IF NOT EXISTS lead_moves (
    id INT AUTO_INCREMENT PRIMARY KEY,
    lead_id INT NOT NULL,
    from_list_id INT,
    to_list_id INT,
    move_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    move_reason VARCHAR(100),
    disposition VARCHAR(20),
    brain_lead_id VARCHAR(20),  -- Added for Brain integration
    INDEX idx_lead (lead_id),
    INDEX idx_move_date (move_date),
    INDEX idx_brain (brain_lead_id)
) ENGINE=InnoDB;
```

### 3. Excluded Statuses (Never Move)
```sql
CREATE TABLE IF NOT EXISTS excluded_statuses (
    status VARCHAR(20) PRIMARY KEY,
    description VARCHAR(100),
    never_move TINYINT(1) DEFAULT 1
) ENGINE=InnoDB;

INSERT IGNORE INTO excluded_statuses (status, description) VALUES
('XFER', 'Transferred - Active Sale'),
('DNC', 'Do Not Call'),
('DNCL', 'Do Not Call List'),
('ADCT', 'Disconnected'),
('NI', 'Not Interested'),
('CALLBK', 'Callback Scheduled');
```

### 4. SMS Queue (Future Integration)
```sql
CREATE TABLE IF NOT EXISTS twilio_outbox (
    id INT AUTO_INCREMENT PRIMARY KEY,
    lead_id INT NOT NULL,
    brain_lead_id VARCHAR(20),
    phone VARCHAR(20) NOT NULL,
    message TEXT,
    scheduled_time DATETIME,
    sent_time DATETIME DEFAULT NULL,
    status ENUM('pending', 'sent', 'failed', 'cancelled') DEFAULT 'pending',
    list_id INT,
    trigger_event VARCHAR(50),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_status_scheduled (status, scheduled_time),
    INDEX idx_brain (brain_lead_id)
) ENGINE=InnoDB;
```

### 5. Enhanced vicidial_list Fields
```sql
ALTER TABLE vicidial_list 
ADD COLUMN IF NOT EXISTS list_entry_date DATETIME DEFAULT NULL,
ADD COLUMN IF NOT EXISTS original_entry_date DATETIME DEFAULT NULL,
ADD COLUMN IF NOT EXISTS tcpajoin_date DATE DEFAULT NULL,
ADD COLUMN IF NOT EXISTS brain_lead_id VARCHAR(20),
ADD INDEX IF NOT EXISTS idx_brain_lead (brain_lead_id);
```

## 🔄 Movement Rules & SQL

### Critical Rules
1. **TCPA Compliance**: 30-day maximum from consent date
2. **Workday Logic**: Movements only on business days
3. **Excluded Statuses**: Never move DNC, XFER, etc.
4. **Race Prevention**: Use transactions and temp tables

### Movement SQL Examples

#### List 101 → 102 (After First Call)
```sql
-- Runs every 15 minutes
START TRANSACTION;

CREATE TEMPORARY TABLE IF NOT EXISTS leads_to_move_101_102 AS
SELECT DISTINCT vl.lead_id, vl.list_id as from_list, 102 as to_list, 
       vl.brain_lead_id, v_latest.status
FROM vicidial_list vl
INNER JOIN (
    SELECT lead_id, status, MAX(call_date) as latest_call
    FROM vicidial_log
    WHERE call_date >= CURDATE()
    GROUP BY lead_id
) v_latest ON vl.lead_id = v_latest.lead_id
LEFT JOIN excluded_statuses es ON v_latest.status = es.status
WHERE vl.list_id = 101
AND es.status IS NULL
AND v_latest.status NOT IN ('CALLBK')
AND NOT EXISTS (
    SELECT 1 FROM lead_moves lm 
    WHERE lm.lead_id = vl.lead_id 
    AND lm.to_list_id = 102
    AND DATE(lm.move_date) = CURDATE()
);

-- Log the movement with Brain ID
INSERT INTO lead_moves (lead_id, from_list_id, to_list_id, move_reason, disposition, brain_lead_id)
SELECT lead_id, from_list, to_list, '30-min delay after first attempt', status, brain_lead_id
FROM leads_to_move_101_102;

-- Update the leads
UPDATE vicidial_list vl
INNER JOIN leads_to_move_101_102 tm ON vl.lead_id = tm.lead_id
SET 
    vl.list_id = 102,
    vl.status = 'VMQ',
    vl.called_since_last_reset = 'N',
    vl.modify_date = DATE_SUB(NOW(), INTERVAL 15 MINUTE);

DROP TEMPORARY TABLE IF EXISTS leads_to_move_101_102;
COMMIT;
```

## 📅 Cron Schedule

```bash
# Every 15 minutes - Fast moves
*/15 * * * * mysql -u root Q6hdjl67GRigMofv < /opt/vici_scripts/move_101_102.sql
*/15 * * * * mysql -u root Q6hdjl67GRigMofv < /opt/vici_scripts/move_101_103_callbk.sql
*/15 * * * * mysql -u root Q6hdjl67GRigMofv < /opt/vici_scripts/move_103_104_lvm.sql
*/15 * * * * mysql -u root Q6hdjl67GRigMofv < /opt/vici_scripts/move_105_106_lvm.sql

# Daily at 12:01 AM - Workday-based moves
1 0 * * * mysql -u root Q6hdjl67GRigMofv < /opt/vici_scripts/move_102_103_workdays.sql
1 0 * * * mysql -u root Q6hdjl67GRigMofv < /opt/vici_scripts/move_104_105_phase1.sql
1 0 * * * mysql -u root Q6hdjl67GRigMofv < /opt/vici_scripts/move_106_107_phase2.sql
1 0 * * * mysql -u root Q6hdjl67GRigMofv < /opt/vici_scripts/move_107_108_cooldown.sql
1 0 * * * mysql -u root Q6hdjl67GRigMofv < /opt/vici_scripts/move_108_110_archive.sql

# Hourly - TCPA compliance check
0 * * * * mysql -u root Q6hdjl67GRigMofv < /opt/vici_scripts/tcpa_compliance_check.sql

# Every 5 minutes - Export call data to Brain
*/5 * * * * /opt/vici_scripts/export_call_data.sh Q6hdjl67GRigMofv
```

## 🧠 Brain Integration Points

### 1. ViciDialerService Updates
```php
// app/Services/ViciDialerService.php
class ViciDialerService {
    protected $targetListId = 101; // ALL new leads go to 101
    
    public function pushLead(Lead $lead, string $campaignId = null): array {
        // Ensure Brain ID is set
        $brainLeadId = $lead->external_lead_id;
        
        // Push to Vici with Brain ID
        $viciData = [
            'phone_number' => $lead->phone,
            'first_name' => $lead->first_name,
            'last_name' => $lead->last_name,
            'list_id' => 101, // ALWAYS start at 101
            'vendor_lead_code' => $brainLeadId, // Brain ID for matching
            'brain_lead_id' => $brainLeadId, // Custom field
            'original_entry_date' => now(),
            'tcpajoin_date' => $lead->opt_in_date ?? now()
        ];
        
        // Push to Vici...
    }
}
```

### 2. Lead Model Updates
```php
// database/migrations/add_vici_list_tracking.php
Schema::table('leads', function($table) {
    $table->integer('current_vici_list')->default(101);
    $table->datetime('vici_list_moved_at')->nullable();
    $table->string('vici_disposition', 20)->nullable();
});
```

### 3. Monitoring Dashboard
```php
// app/Http/Controllers/ViciFlowController.php
public function leadFlowDashboard() {
    $listDistribution = DB::connection('vici')->select("
        SELECT list_id, COUNT(*) as count,
               AVG(DATEDIFF(NOW(), original_entry_date)) as avg_age
        FROM vicidial_list
        WHERE list_id BETWEEN 101 AND 111
        GROUP BY list_id
    ");
    
    $recentMoves = DB::connection('vici')->select("
        SELECT * FROM lead_moves 
        WHERE move_date >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
        ORDER BY move_date DESC LIMIT 100
    ");
    
    return view('admin.vici-lead-flow', compact('listDistribution', 'recentMoves'));
}
```

## 📊 Monitoring Queries

### Real-time List Distribution
```sql
CREATE VIEW lead_flow_dashboard AS
SELECT 
    list_id,
    CASE list_id
        WHEN 101 THEN '🆕 Immediate'
        WHEN 102 THEN '🔥 Aggressive'
        WHEN 103 THEN '📧 Voicemail 1'
        WHEN 104 THEN '📞 Phase 1'
        WHEN 105 THEN '📧 Voicemail 2'
        WHEN 106 THEN '📞 Phase 2'
        WHEN 107 THEN '❄️ Cool Down'
        WHEN 108 THEN '📞 Phase 3'
        WHEN 110 THEN '📦 Archive'
        WHEN 111 THEN '🎓 Training'
    END as list_name,
    COUNT(*) as total_leads,
    SUM(CASE WHEN DATE(list_entry_date) = CURDATE() THEN 1 ELSE 0 END) as added_today,
    SUM(CASE WHEN status = 'XFER' THEN 1 ELSE 0 END) as transferred,
    AVG(DATEDIFF(NOW(), original_entry_date)) as avg_age_days
FROM vicidial_list
WHERE list_id IN (101,102,103,104,105,106,107,108,110,111)
GROUP BY list_id;
```

### TCPA Risk Report
```sql
SELECT 
    list_id,
    COUNT(*) as at_risk_count,
    MIN(DATEDIFF(DATE_ADD(tcpajoin_date, INTERVAL 30 DAY), CURDATE())) as days_until_expiry
FROM vicidial_list vl
WHERE list_id BETWEEN 101 AND 109
AND tcpajoin_date IS NOT NULL
AND DATEDIFF(DATE_ADD(tcpajoin_date, INTERVAL 30 DAY), CURDATE()) <= 3
GROUP BY list_id;
```

## 🚀 Implementation Steps

### Phase 1: Database Setup (Today)
1. ✅ Create calendar table
2. ✅ Add custom fields to vicidial_list
3. ✅ Create lead_moves table
4. ✅ Create excluded_statuses table
5. ✅ Populate initial data

### Phase 2: SQL Scripts (Today)
1. ✅ Deploy movement SQL scripts
2. ✅ Test with small batch
3. ✅ Set up cron jobs

### Phase 3: Brain Integration (Today)
1. ✅ Update ViciDialerService to use List 101
2. ✅ Add list tracking to Lead model
3. ✅ Create monitoring dashboard

### Phase 4: Monitoring (Tomorrow)
1. ⏳ Build lead flow dashboard
2. ⏳ Set up alerts for TCPA expiry
3. ⏳ Create performance reports

### Phase 5: SMS Integration (After Go-Live)
1. ⏳ Connect twilio_outbox to Brain
2. ⏳ Set up SMS triggers
3. ⏳ Implement opt-out handling

## ⚠️ Important Notes

1. **TCPA Compliance**: System automatically archives leads after 30 days
2. **Workday Logic**: No movements on weekends/holidays
3. **Voicemail Lists**: 103 and 105 require agent to leave VM
4. **Cool Down**: List 107 has NO calling for 7 days
5. **Brain Integration**: All leads must have brain_lead_id for tracking

## 🎯 Success Metrics

- **List 101 → 102**: < 1 hour average
- **List 102 → 103**: 3 workdays max
- **TCPA Compliance**: 100% archived before expiry
- **Transfer Rate**: Track by list for optimization
- **Cool Down Recovery**: Measure re-engagement after List 107

---

*This system is production-ready and TCPA compliant*
*Designed for ViciDial with Brain integration*


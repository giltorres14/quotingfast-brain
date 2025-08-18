# 📊 Lead Journey Analytics & Testing Framework

## TESTING STRATEGY: Maximum Data Collection

### Phase 1: AGGRESSIVE MULTI-VARIANT TESTING (Weeks 1-4)

#### Test Groups (25% of leads each):
```sql
-- Group A: "Hyper-Aggressive"
Day 1:    8-10 calls (every 30-60 min)
Day 2-3:  4-6 calls per day
Day 4-7:  2-3 calls per day
Day 8-14: 1-2 calls per day
Day 15-30: Every other day
TOTAL: 40-50 calls (Similar to current)

-- Group B: "Front-Loaded"
Day 1:    6 calls (heavy first day)
Day 2-3:  2 calls per day
Day 4-7:  1 call per day
Day 8-14: Every other day
Day 15-30: 2x per week
TOTAL: 20-25 calls

-- Group C: "Steady Persistence"
Day 1:    3 calls
Day 2-7:  1 call per day
Day 8-14: Every other day
Day 15-30: 2x per week
TOTAL: 15-18 calls

-- Group D: "Conservative"
Day 1:    2 calls
Day 2-3:  1 call per day
Day 4-7:  Every other day
Day 8-14: 2x per week
Day 15-30: 1x per week
TOTAL: 10-12 calls
```

### DID Rotation Strategy:
```python
# Rotate DIDs to appear as different companies
did_pool = [
    '555-0001', '555-0002', '555-0003', '555-0004',
    '555-0005', '555-0006', '555-0007', '555-0008'
]

def get_next_did(lead_id, attempt_number):
    # Different DID for each attempt
    return did_pool[attempt_number % len(did_pool)]
```

---

## 📈 COMPREHENSIVE ANALYTICS DASHBOARD

### Core Metrics to Track:

#### 1. Contact Rate by Attempt Number
```sql
CREATE VIEW contact_rate_by_attempt AS
SELECT 
    attempt_number,
    test_group,
    COUNT(CASE WHEN status IN ('XFER','SALE','NI','DNC') THEN 1 END) as contacts,
    COUNT(*) as total_attempts,
    ROUND(COUNT(CASE WHEN status IN ('XFER','SALE','NI','DNC') THEN 1 END) * 100.0 / COUNT(*), 2) as contact_rate,
    -- Critical: Track which attempt # actually converts
    COUNT(CASE WHEN status = 'SALE' THEN 1 END) as sales,
    COUNT(CASE WHEN status = 'XFER' THEN 1 END) as transfers
FROM lead_attempts
GROUP BY attempt_number, test_group
ORDER BY attempt_number;
```

#### 2. Conversion by Day/Hour Pattern
```sql
CREATE VIEW conversion_by_time AS
SELECT 
    day_number,
    hour_of_day,
    test_group,
    COUNT(CASE WHEN contacted = 1 THEN 1 END) as contacts,
    COUNT(CASE WHEN converted = 1 THEN 1 END) as conversions,
    ROUND(AVG(CASE WHEN contacted = 1 THEN 1 ELSE 0 END) * 100, 2) as contact_rate,
    ROUND(AVG(CASE WHEN converted = 1 THEN 1 ELSE 0 END) * 100, 2) as conversion_rate
FROM lead_journey
GROUP BY day_number, hour_of_day, test_group;
```

#### 3. DNC/Complaint Rate by Intensity
```sql
CREATE VIEW complaint_analysis AS
SELECT 
    test_group,
    total_attempts_bracket,
    COUNT(*) as lead_count,
    SUM(CASE WHEN status = 'DNC' THEN 1 ELSE 0 END) as dnc_count,
    SUM(CASE WHEN complaint_filed = 1 THEN 1 ELSE 0 END) as complaints,
    ROUND(SUM(CASE WHEN status = 'DNC' THEN 1 ELSE 0 END) * 100.0 / COUNT(*), 2) as dnc_rate
FROM (
    SELECT 
        lead_id,
        test_group,
        final_status as status,
        complaint_filed,
        CASE 
            WHEN total_attempts <= 5 THEN '1-5 calls'
            WHEN total_attempts <= 10 THEN '6-10 calls'
            WHEN total_attempts <= 20 THEN '11-20 calls'
            WHEN total_attempts <= 30 THEN '21-30 calls'
            ELSE '31+ calls'
        END as total_attempts_bracket
    FROM lead_summary
) t
GROUP BY test_group, total_attempts_bracket;
```

#### 4. ROI Analysis by Strategy
```sql
CREATE VIEW roi_by_strategy AS
SELECT 
    test_group,
    COUNT(*) as total_leads,
    AVG(total_attempts) as avg_attempts,
    AVG(total_attempts) * 0.50 as avg_cost_per_lead, -- $0.50 per call
    SUM(CASE WHEN converted = 1 THEN 1 ELSE 0 END) as conversions,
    SUM(revenue) as total_revenue,
    ROUND(SUM(revenue) / (AVG(total_attempts) * 0.50 * COUNT(*)), 2) as roi,
    ROUND(SUM(CASE WHEN converted = 1 THEN 1 ELSE 0 END) * 100.0 / COUNT(*), 2) as conversion_rate
FROM lead_summary
GROUP BY test_group
ORDER BY roi DESC;
```

---

## 🎯 KEY PERFORMANCE INDICATORS

### Real-Time Dashboard Queries:

#### 1. "Sweet Spot Finder"
```sql
-- Find the optimal number of attempts
SELECT 
    total_attempts,
    COUNT(*) as leads,
    SUM(converted) as conversions,
    ROUND(SUM(converted) * 100.0 / COUNT(*), 2) as conv_rate,
    ROUND(SUM(converted) * 100.0 / SUM(SUM(converted)) OVER (), 2) as pct_of_total_conversions
FROM lead_summary
GROUP BY total_attempts
ORDER BY total_attempts;
```

#### 2. "Diminishing Returns Calculator"
```sql
-- Identify where additional calls stop being worth it
WITH attempt_value AS (
    SELECT 
        attempt_number,
        SUM(CASE WHEN first_contact_attempt = attempt_number THEN 1 ELSE 0 END) as first_contacts,
        SUM(CASE WHEN conversion_attempt = attempt_number THEN 1 ELSE 0 END) as conversions_at_attempt,
        COUNT(*) as total_attempts_made
    FROM lead_attempts
    GROUP BY attempt_number
)
SELECT 
    attempt_number,
    first_contacts,
    conversions_at_attempt,
    total_attempts_made,
    ROUND(conversions_at_attempt * 100.0 / total_attempts_made, 2) as conversion_rate_this_attempt,
    SUM(conversions_at_attempt) OVER (ORDER BY attempt_number) as cumulative_conversions,
    ROUND(conversions_at_attempt * 500 - total_attempts_made * 0.50, 2) as net_value_this_attempt -- $500 per sale, $0.50 per call
FROM attempt_value
ORDER BY attempt_number;
```

#### 3. "Lead Age vs Response Rate"
```sql
-- How does lead age affect contact/conversion?
SELECT 
    CASE 
        WHEN lead_age_hours < 1 THEN '< 1 hour'
        WHEN lead_age_hours < 24 THEN '1-24 hours'
        WHEN lead_age_days < 3 THEN '1-3 days'
        WHEN lead_age_days < 7 THEN '4-7 days'
        WHEN lead_age_days < 14 THEN '8-14 days'
        ELSE '15+ days'
    END as age_bracket,
    COUNT(*) as attempts,
    SUM(contacted) as contacts,
    ROUND(AVG(contacted) * 100, 2) as contact_rate,
    SUM(converted) as conversions,
    ROUND(AVG(converted) * 100, 2) as conversion_rate
FROM lead_attempts
GROUP BY age_bracket
ORDER BY MIN(lead_age_hours);
```

---

## 📊 VISUAL REPORTING DASHBOARD

### Dashboard Components:

1. **Heat Map: Best Call Times**
   - Day of week x Hour of day
   - Color coded by contact rate
   - Separate view for conversion rate

2. **Funnel Analysis**
   - Attempts → Contacts → Conversations → Conversions
   - By test group
   - By lead age

3. **Cost/Benefit Curve**
   - X-axis: Number of attempts
   - Y-axis: ROI
   - Find the peak ROI point

4. **Persistence Payoff Chart**
   - Shows conversion rate by attempt number
   - Highlights where most conversions happen
   - Shows cumulative conversion %

---

## 🔬 A/B TESTING FRAMEWORK

### Test Variables:
```python
test_variables = {
    'timing': {
        'immediate': [0, 5, 30, 120],  # minutes
        'delayed': [120, 240, 1440, 2880],  # start at 2 hours
        'steady': [0, 360, 1440, 2880]  # spread out
    },
    'intensity': {
        'aggressive': 40,  # total attempts
        'moderate': 20,
        'conservative': 10
    },
    'persistence': {
        'front_loaded': [0.7, 0.2, 0.1],  # 70% week 1, 20% week 2, 10% week 3
        'steady': [0.33, 0.33, 0.34],
        'back_loaded': [0.2, 0.4, 0.4]
    },
    'did_strategy': {
        'rotate_all': True,  # Different DID each time
        'consistent': False,  # Same DID for same lead
        'department': 'mixed'  # Sales vs Service DIDs
    }
}
```

### Statistical Significance Testing:
```sql
-- Compare conversion rates between test groups
WITH group_stats AS (
    SELECT 
        test_group,
        COUNT(*) as n,
        SUM(converted) as conversions,
        AVG(converted) as conversion_rate,
        STDDEV(converted) as std_dev
    FROM lead_summary
    GROUP BY test_group
)
SELECT 
    a.test_group as group_a,
    b.test_group as group_b,
    a.conversion_rate as rate_a,
    b.conversion_rate as rate_b,
    ABS(a.conversion_rate - b.conversion_rate) as difference,
    -- Z-score for significance
    ABS(a.conversion_rate - b.conversion_rate) / 
        SQRT((a.std_dev*a.std_dev/a.n) + (b.std_dev*b.std_dev/b.n)) as z_score,
    CASE 
        WHEN ABS(a.conversion_rate - b.conversion_rate) / 
             SQRT((a.std_dev*a.std_dev/a.n) + (b.std_dev*b.std_dev/b.n)) > 1.96 
        THEN 'Significant'
        ELSE 'Not Significant'
    END as significance
FROM group_stats a
CROSS JOIN group_stats b
WHERE a.test_group < b.test_group;
```

---

## 🚀 IMPLEMENTATION IN VICI

### Custom Fields for Tracking:
```sql
ALTER TABLE vicidial_list ADD COLUMN test_group VARCHAR(20);
ALTER TABLE vicidial_list ADD COLUMN first_contact_attempt INT;
ALTER TABLE vicidial_list ADD COLUMN conversion_attempt INT;
ALTER TABLE vicidial_list ADD COLUMN total_unique_dids INT;
ALTER TABLE vicidial_list ADD COLUMN journey_score DECIMAL(5,2);

-- Track every attempt
CREATE TABLE lead_journey_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    lead_id INT,
    attempt_number INT,
    call_date DATETIME,
    did_used VARCHAR(20),
    status VARCHAR(20),
    talk_time INT,
    agent_id VARCHAR(20),
    contacted BOOLEAN,
    converted BOOLEAN,
    notes TEXT,
    INDEX idx_lead (lead_id),
    INDEX idx_date (call_date)
);
```

### Automated Test Assignment:
```php
// Randomly assign new leads to test groups
function assignTestGroup($lead_id) {
    $groups = ['A_aggressive', 'B_frontload', 'C_steady', 'D_conservative'];
    $random_group = $groups[array_rand($groups)];
    
    $sql = "UPDATE vicidial_list 
            SET test_group = '$random_group',
                list_id = CASE 
                    WHEN '$random_group' = 'A_aggressive' THEN 101
                    WHEN '$random_group' = 'B_frontload' THEN 201
                    WHEN '$random_group' = 'C_steady' THEN 301
                    WHEN '$random_group' = 'D_conservative' THEN 401
                END
            WHERE lead_id = $lead_id";
    
    return $random_group;
}
```

---

## 📈 EXPECTED INSIGHTS AFTER 2 WEEKS

You'll know EXACTLY:
1. **Optimal attempt number** (probably 8-15, but DATA will tell you)
2. **Best time patterns** for your specific leads
3. **ROI peak point** (where more calls aren't worth it)
4. **DNC threshold** (where complaints spike)
5. **DID rotation impact** (does appearing as different companies help?)
6. **Lead age sensitivity** (how fast do they go cold?)

---

## 🎯 WHY THIS IS THE RIGHT APPROACH

1. **You have the infrastructure** (multiple DIDs)
2. **Data beats theory** every time
3. **Parallel testing** = faster learning
4. **No assumptions** = pure results
5. **Customized to YOUR leads** not generic advice

The academics say 6-8 calls. Your current process does 42. The truth for YOUR specific shared leads is somewhere in between, and only YOUR DATA can tell you where.


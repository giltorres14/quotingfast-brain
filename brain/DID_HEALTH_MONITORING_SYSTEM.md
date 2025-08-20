# 📞 DID HEALTH MONITORING SYSTEM
**Created: August 20, 2025**

## 🎯 OBJECTIVE
Monitor and maintain DID (phone number) health to prevent spam labeling and optimize answer rates.

## 🚨 KEY METRICS TO MONITOR

### 1. **Answer Rate Threshold**
- **Healthy:** > 15% answer rate
- **Warning:** 10-15% answer rate  
- **Critical:** < 10% answer rate (likely marked as spam)
- **Action:** Rest DID for 2-3 weeks when critical

### 2. **Daily Call Volume**
- **Target:** 40-45 calls per DID per day
- **Maximum:** 50 calls (hard limit)
- **Minimum:** 10 calls (underutilized)

### 3. **Performance Indicators**
```sql
-- Key metrics to track per DID
- Answer Rate (A, XFER, XFERA statuses)
- Voicemail Rate (AM, AL statuses)  
- No Answer Rate (NA status)
- Busy Rate (B status)
- Drop Rate (DROP, PDROP)
- Average Talk Time
- First Call Resolution Rate
```

## 📊 DATABASE SCHEMA FOR DID MONITORING

```sql
-- Create DID health tracking table
CREATE TABLE did_health_monitor (
    id SERIAL PRIMARY KEY,
    did_number VARCHAR(20) UNIQUE,
    area_code VARCHAR(5),
    state VARCHAR(2),
    campaign_id VARCHAR(20),
    
    -- Daily metrics
    date DATE,
    total_calls INT DEFAULT 0,
    answered_calls INT DEFAULT 0,
    answer_rate DECIMAL(5,2),
    avg_talk_time INT,
    
    -- Health scoring
    health_score INT DEFAULT 100, -- 0-100 scale
    spam_risk_level VARCHAR(20), -- LOW, MEDIUM, HIGH, CRITICAL
    last_spam_check TIMESTAMP,
    
    -- Rotation status
    status VARCHAR(20) DEFAULT 'ACTIVE', -- ACTIVE, WARNING, RESTING, RETIRED
    rest_start_date DATE,
    rest_end_date DATE,
    
    -- Historical tracking
    lifetime_calls INT DEFAULT 0,
    lifetime_answer_rate DECIMAL(5,2),
    first_used DATE,
    last_used DATE,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create daily DID performance log
CREATE TABLE did_daily_performance (
    id SERIAL PRIMARY KEY,
    did_number VARCHAR(20),
    date DATE,
    hour INT,
    calls_made INT,
    calls_answered INT,
    answer_rate DECIMAL(5,2),
    avg_ring_time INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    UNIQUE KEY unique_did_date_hour (did_number, date, hour)
);

-- Create DID rotation pool
CREATE TABLE did_rotation_pool (
    id SERIAL PRIMARY KEY,
    did_number VARCHAR(20) UNIQUE,
    area_code VARCHAR(5),
    state VARCHAR(2),
    provider VARCHAR(50),
    monthly_cost DECIMAL(10,2),
    
    pool_status VARCHAR(20), -- AVAILABLE, IN_USE, RESTING, NEEDS_REPLACEMENT
    assigned_campaign VARCHAR(20),
    
    spam_reports INT DEFAULT 0,
    last_spam_report DATE,
    replacement_priority INT DEFAULT 0,
    
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

## 🔄 DID ROTATION ALGORITHM

```php
/**
 * DID Health Scoring Algorithm
 * Score 0-100: 100 = Perfect, 0 = Dead DID
 */
function calculateDIDHealthScore($did_stats) {
    $score = 100;
    
    // Answer rate impact (40 points max)
    if ($did_stats['answer_rate'] < 5) {
        $score -= 40; // Critical - likely spam
    } elseif ($did_stats['answer_rate'] < 10) {
        $score -= 30; // Warning
    } elseif ($did_stats['answer_rate'] < 15) {
        $score -= 20; // Caution
    } elseif ($did_stats['answer_rate'] < 20) {
        $score -= 10; // Monitor
    }
    
    // Daily volume impact (20 points max)
    if ($did_stats['daily_calls'] > 50) {
        $score -= 20; // Overused
    } elseif ($did_stats['daily_calls'] > 45) {
        $score -= 10; // Near limit
    } elseif ($did_stats['daily_calls'] < 10) {
        $score -= 5; // Underused
    }
    
    // Trend impact (20 points max)
    if ($did_stats['answer_rate_trend'] < -5) {
        $score -= 20; // Rapid decline
    } elseif ($did_stats['answer_rate_trend'] < -2) {
        $score -= 10; // Declining
    }
    
    // Age impact (20 points max)
    $days_active = $did_stats['days_in_use'];
    if ($days_active > 90) {
        $score -= 20; // Too old, needs rest
    } elseif ($days_active > 60) {
        $score -= 10; // Getting old
    }
    
    return max(0, $score);
}

/**
 * DID Rotation Decision Engine
 */
function shouldRotateDID($health_score, $answer_rate, $days_active) {
    // Immediate rotation triggers
    if ($health_score < 30) return 'IMMEDIATE';
    if ($answer_rate < 5) return 'IMMEDIATE';
    if ($days_active > 90) return 'SCHEDULED';
    
    // Warning triggers
    if ($health_score < 50) return 'WARNING';
    if ($answer_rate < 10) return 'WARNING';
    
    return 'HEALTHY';
}
```

## 📈 MONITORING REPORTS

### 1. **Real-Time DID Dashboard**
```sql
-- Live DID health status
SELECT 
    did_number,
    area_code,
    state,
    health_score,
    answer_rate,
    total_calls as today_calls,
    CASE 
        WHEN health_score < 30 THEN '🔴 CRITICAL'
        WHEN health_score < 50 THEN '🟡 WARNING'
        WHEN health_score < 70 THEN '🟠 MONITOR'
        ELSE '🟢 HEALTHY'
    END as status,
    CASE
        WHEN answer_rate < 5 THEN 'LIKELY SPAM'
        WHEN answer_rate < 10 THEN 'POSSIBLE SPAM'
        ELSE 'CLEAN'
    END as spam_status
FROM did_health_monitor
WHERE date = CURRENT_DATE
ORDER BY health_score ASC;
```

### 2. **DID Rotation Schedule**
```sql
-- DIDs needing rotation
SELECT 
    did_number,
    rest_start_date,
    rest_end_date,
    DATEDIFF(rest_end_date, CURRENT_DATE) as days_until_return,
    'RESTING' as current_status
FROM did_health_monitor
WHERE status = 'RESTING'
    AND rest_end_date > CURRENT_DATE

UNION ALL

SELECT 
    did_number,
    CURRENT_DATE as rest_start_date,
    DATE_ADD(CURRENT_DATE, INTERVAL 21 DAY) as rest_end_date,
    -1 as days_until_return,
    'NEEDS REST' as current_status
FROM did_health_monitor
WHERE health_score < 30
    AND status = 'ACTIVE'
ORDER BY days_until_return;
```

### 3. **State/Area Code Coverage**
```sql
-- DID inventory by location
SELECT 
    state,
    area_code,
    COUNT(CASE WHEN status = 'ACTIVE' THEN 1 END) as active_dids,
    COUNT(CASE WHEN status = 'RESTING' THEN 1 END) as resting_dids,
    AVG(answer_rate) as avg_answer_rate,
    SUM(total_calls) as total_calls_today,
    COUNT(CASE WHEN health_score < 50 THEN 1 END) as unhealthy_dids
FROM did_health_monitor
WHERE date = CURRENT_DATE
GROUP BY state, area_code
HAVING active_dids < 5  -- Flag areas needing more DIDs
ORDER BY avg_answer_rate ASC;
```

## 🚨 ALERT SYSTEM

### Critical Alerts (Immediate Action)
1. **Spam Detection:** Answer rate drops below 5%
2. **Overuse:** DID exceeds 50 calls/day
3. **Coverage Gap:** Area has < 3 active DIDs

### Warning Alerts (Plan Action)
1. **Declining Performance:** Answer rate trend -5% over 3 days
2. **Near Limit:** DID approaching 45 calls/day
3. **Aging DID:** Active for > 60 days without rest

### Monitoring Alerts (Track)
1. **Underutilization:** DID < 10 calls/day
2. **Minor Decline:** Answer rate trend -2% over week

## 🔧 IMPLEMENTATION STEPS

### Phase 1: Data Collection (Week 1)
```php
// Cron job to collect DID metrics every hour
// File: brain/app/Console/Commands/CollectDIDMetrics.php

class CollectDIDMetrics extends Command {
    public function handle() {
        // 1. Query vicidial_log for last hour's calls
        // 2. Group by outbound CID (DID)
        // 3. Calculate answer rates
        // 4. Update did_health_monitor table
        // 5. Check thresholds and create alerts
    }
}
```

### Phase 2: Rotation System (Week 2)
```php
// Automated DID rotation
// File: brain/app/Console/Commands/RotateDIDs.php

class RotateDIDs extends Command {
    public function handle() {
        // 1. Identify DIDs needing rest (health < 30)
        // 2. Move to resting pool
        // 3. Activate rested DIDs (21+ days rest)
        // 4. Update campaign configurations
        // 5. Send notifications
    }
}
```

### Phase 3: Reporting UI (Week 3)
- Real-time dashboard at `/did/health`
- Historical trends at `/did/analytics`
- Rotation schedule at `/did/rotation`
- Alert center at `/did/alerts`

## 📱 CARRIER REPUTATION MANAGEMENT

### Best Practices:
1. **STIR/SHAKEN Compliance**
   - Register all DIDs with carriers
   - Maintain proper attestation levels

2. **Call Pattern Consistency**
   - Maintain steady call volumes
   - Avoid burst calling
   - Respect time zones

3. **Content Quality**
   - Proper caller ID name (CNAM)
   - Clear business identification
   - Consistent messaging

4. **Feedback Loop**
   - Monitor carrier feedback
   - Track consumer complaints
   - Respond to spam reports quickly

## 🎯 SUCCESS METRICS

### Target Goals:
- **Answer Rate:** Maintain > 20% across all DIDs
- **DID Lifespan:** 60-90 days active, 21 days rest
- **Coverage:** 5-10 active DIDs per major area code
- **Health Score:** 80% of DIDs above 70 score
- **Spam Rate:** < 5% of DIDs flagged as spam

### ROI Impact:
- **10% answer rate improvement** = 10% more opportunities
- **Proper rotation** = 50% longer DID lifespan
- **Local presence** = 15-30% better answer rates
- **Spam prevention** = Avoid 90% answer rate loss

## 🚀 QUICK START COMMANDS

```bash
# Check current DID health
php artisan did:health-check

# Force rotation check
php artisan did:rotate --force

# Generate DID report
php artisan did:report --date=today

# Add new DIDs to pool
php artisan did:add --area-code=305 --count=5

# Rest specific DID
php artisan did:rest --number=3055551234 --days=21
```

---

**Note:** This system requires integration with your carrier's API for real-time spam scoring and CNAM updates. Consider services like TrueCNAM, Neustar, or direct carrier APIs for reputation monitoring.

# Duplicate Lead Handling Strategy

## Current Situation
The Brain currently does NOT check for duplicate leads by phone number - it creates a new lead every time.

## Proposed Solution

### 1. Smart Duplicate Detection
```php
// Check for existing lead by phone (last 10 digits)
$existingLead = Lead::where('phone', 'LIKE', '%' . substr($cleanPhone, -10))
    ->orderBy('created_at', 'desc')
    ->first();

if ($existingLead) {
    // Check age of lead
    $daysSinceCreated = $existingLead->created_at->diffInDays(now());
    
    if ($daysSinceCreated < 30) {
        // Recent lead - update with new info
        $existingLead->update([
            'duplicate_count' => ($existingLead->duplicate_count ?? 0) + 1,
            'last_inquiry_at' => now(),
            // Update any new/better data
        ]);
        return $existingLead;
    } else {
        // Old lead - create new one but link them
        $newLead = Lead::create($leadData);
        $newLead->update([
            'parent_lead_id' => $existingLead->id,
            'is_reengagement' => true
        ]);
        return $newLead;
    }
}
```

### 2. Rules for Duplicates

**Within 30 days:**
- Update existing lead
- Increment duplicate counter
- Add note about re-inquiry
- Don't send to Vici again

**After 30 days:**
- Create new lead (re-engagement)
- Link to original lead
- Send to Vici as new opportunity
- Mark as "returning customer"

**After 90 days:**
- Treat as completely new lead

### 3. CSV Import Handling

For your 111,317 lead import:

1. **Phase 1: Analysis**
   - Count duplicates by phone
   - Identify date ranges
   - Group by month

2. **Phase 2: Import Strategy**
   - Import oldest leads first
   - Skip duplicates within 30 days
   - Create re-engagement leads for 30-90 days
   - Import as new after 90 days

### 4. Database Changes Needed

```sql
ALTER TABLE leads ADD COLUMN duplicate_count INT DEFAULT 0;
ALTER TABLE leads ADD COLUMN last_inquiry_at TIMESTAMP NULL;
ALTER TABLE leads ADD COLUMN parent_lead_id INT NULL;
ALTER TABLE leads ADD COLUMN is_reengagement BOOLEAN DEFAULT FALSE;
ALTER TABLE leads ADD INDEX idx_phone_last10 ((RIGHT(phone, 10)));
```

## CSV File Analysis

To analyze your CSV file:

1. **Upload to Google Drive** and share the link
2. **Or use a file sharing service** like WeTransfer
3. **Or send first 100 rows** as a sample

I can then:
- Analyze the structure
- Identify required field mappings
- Create custom import script
- Handle duplicates properly
